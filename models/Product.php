<?php
namespace Models;

use PDO;

class Product extends BaseModel {
    protected string $table = 'products';

    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.slug as category_slug,
                   sc.name as subcategory_name, sc.slug as subcategory_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
            WHERE p.slug = ? AND p.status = 'published'
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $prod = $stmt->fetch();
        if (!$prod) return null;

        $prod['images'] = $this->getImages($prod['id']);
        $prod['variants'] = $this->getVariants($prod['id']);
        $prod['colors'] = $this->getDistinctColors($prod['id']);
        $prod['sizes'] = $this->getDistinctSizes($prod['id']);
        $prod['video'] = parse_video_source($prod['video_url'] ?? null, $prod['video_file'] ?? null);
        return $prod;
    }

    public function getImages(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getVariants(int $productId): array {
        $stmt = $this->db->prepare("SELECT * FROM product_variants WHERE product_id = ? AND status = 'active' ORDER BY size ASC");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getDistinctColors(int $productId): array {
        $stmt = $this->db->prepare("SELECT DISTINCT color, color_code FROM product_variants WHERE product_id = ? AND color IS NOT NULL AND status = 'active'");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getDistinctSizes(int $productId): array {
        $stmt = $this->db->prepare("SELECT DISTINCT size FROM product_variants WHERE product_id = ? AND size IS NOT NULL AND status = 'active'");
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getBestsellers(int $limit = 8): array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'published' AND p.is_bestseller = 1
            ORDER BY p.rating_count DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getFeatured(int $limit = 8): array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'published' AND p.is_featured = 1
            ORDER BY p.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getNewArrivals(int $limit = 8): array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'published' AND p.is_new_arrival = 1
            ORDER BY p.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRelatedProducts(int $categoryId, int $currentProductId, int $limit = 4): array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'published' AND p.category_id = ? AND p.id != ?
            ORDER BY p.rating_count DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(2, $currentProductId, PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Filter and Paginate Products for Shop & Categories
     */
    public function filterProducts(array $filters = [], int $page = 1, int $perPage = 12): array {
        $where = ["p.status = 'published'"];
        $params = [];

        // Category filter (slug or id)
        if (!empty($filters['category'])) {
            $where[] = "(c.slug = ? OR p.category_id = ?)";
            $params[] = $filters['category'];
            $params[] = is_numeric($filters['category']) ? (int)$filters['category'] : 0;
        }

        // Subcategory filter
        if (!empty($filters['subcategory'])) {
            $where[] = "(sc.slug = ? OR p.subcategory_id = ?)";
            $params[] = $filters['subcategory'];
            $params[] = is_numeric($filters['subcategory']) ? (int)$filters['subcategory'] : 0;
        }

        // Search query
        if (!empty($filters['q'])) {
            $searchTerm = '%' . trim($filters['q']) . '%';
            $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.short_description LIKE ? OR p.material LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Price range
        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $where[] = "p.price >= ?";
            $params[] = (float)$filters['min_price'];
        }
        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $where[] = "p.price <= ?";
            $params[] = (float)$filters['max_price'];
        }

        // Size filter
        if (!empty($filters['size'])) {
            $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.size = ?)";
            $params[] = $filters['size'];
        }

        // Color filter
        if (!empty($filters['color'])) {
            $where[] = "EXISTS (SELECT 1 FROM product_variants pv WHERE pv.product_id = p.id AND pv.color LIKE ?)";
            $params[] = '%' . $filters['color'] . '%';
        }

        // Rating filter
        if (!empty($filters['rating']) && is_numeric($filters['rating'])) {
            $where[] = "p.rating_avg >= ?";
            $params[] = (float)$filters['rating'];
        }

        // Discount filter
        if (!empty($filters['discount']) && is_numeric($filters['discount'])) {
            $where[] = "p.discount_percent >= ?";
            $params[] = (int)$filters['discount'];
        }

        $whereClause = implode(' AND ', $where);

        // Sorting
        $sort = $filters['sort'] ?? 'popularity';
        $orderBy = match ($sort) {
            'price_low' => 'p.price ASC',
            'price_high' => 'p.price DESC',
            'newest' => 'p.id DESC',
            'rating' => 'p.rating_avg DESC, p.rating_count DESC',
            'discount' => 'p.discount_percent DESC',
            default => 'p.is_bestseller DESC, p.rating_count DESC, p.id DESC' // popularity
        };

        // Total count
        $countSql = "
            SELECT COUNT(*) FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
            WHERE {$whereClause}
        ";
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        // Paginated results
        $offset = ($page - 1) * $perPage;
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($sql);
        
        $idx = 1;
        foreach ($params as $param) {
            $stmt->bindValue($idx++, $param);
        }
        $stmt->bindValue($idx++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($idx++, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        return [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int)ceil($total / $perPage)
        ];
    }
}
