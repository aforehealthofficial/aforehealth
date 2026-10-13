<?php
namespace Controllers\Admin;

use Models\Product;
use Models\Category;
use PDO;

class AdminProductController {
    public function index(): void {
        $pdo = getDbConnection();
        $catFilter = $_GET['category'] ?? 'all';
        $search = trim($_GET['q'] ?? '');

        $where = ["1=1"];
        $params = [];

        if ($catFilter !== 'all' && is_numeric($catFilter)) {
            $where[] = "p.category_id = ?";
            $params[] = (int)$catFilter;
        }

        if (!empty($search)) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
        }

        $whereClause = implode(' AND ', $where);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;

        // Total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name,
                   (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) as variant_count,
                   (SELECT COALESCE(SUM(quantity), 0) FROM order_items WHERE product_id = p.id) as units_sold,
                   (SELECT COALESCE(SUM(total), 0) FROM order_items WHERE product_id = p.id) as total_revenue,
                   (SELECT COUNT(*) FROM wishlists WHERE product_id = p.id) as wishlist_count,
                   (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND status = 'approved') as approved_reviews_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY p.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        $categoryModel = new Category();
        $categories = $categoryModel->allActive();

        view('admin.products.index', [
            'pageTitle' => 'Catalog Products - AFOREHEALTH Admin',
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $catFilter,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    public function create(): void {
        $categoryModel = new Category();
        $categories = $categoryModel->allActive();

        view('admin.products.create', [
            'pageTitle' => 'Add New Product - AFOREHEALTH Admin',
            'categories' => $categories
        ]);
    }

    public function store(): void {
        $name = trim($_POST['name'] ?? '');
        $sku = strtoupper(trim($_POST['sku'] ?? ''));
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $price = (float)($_POST['price'] ?? 0);
        $mrp = (float)($_POST['mrp'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $shortDesc = trim($_POST['short_description'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $material = trim($_POST['material'] ?? '');
        $care = trim($_POST['care_instructions'] ?? '');
        $hygiene = trim($_POST['hygiene_notice'] ?? '');

        if (empty($name) || empty($sku) || $price <= 0) {
            set_flash('danger', 'Please provide product name, unique SKU, and price.');
            redirect('/admin/products/create');
        }

        $slug = slugify($name);
        $discountPct = $mrp > $price ? round((($mrp - $price) / $mrp) * 100) : 0;

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO products (
                name, slug, sku, category_id, price, mrp, discount_percent, stock,
                short_description, description, material, care_instructions, hygiene_notice,
                primary_image, status, is_featured, is_bestseller, is_new_arrival
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                'cat_period_panties.svg', 'published', ?, ?, ?
            )
        ");
        $stmt->execute([
            $name, $slug, $sku, $categoryId, $price, $mrp, $discountPct, $stock,
            $shortDesc, $desc, $material, $care, $hygiene,
            !empty($_POST['is_featured']) ? 1 : 0,
            !empty($_POST['is_bestseller']) ? 1 : 0,
            !empty($_POST['is_new_arrival']) ? 1 : 0
        ]);

        $prodId = $pdo->lastInsertId();

        // Add standard sizes variants if period wear or underwear
        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
        $stmtVar = $pdo->prepare("INSERT INTO product_variants (product_id, sku, color, color_code, size, price, mrp, stock) VALUES (?, ?, 'Classic Black', '#1F1E24', ?, ?, ?, ?)");
        foreach ($sizes as $sz) {
            $varSku = $sku . '-BLK-' . $sz;
            $varStock = max(5, round($stock / count($sizes)));
            $stmtVar->execute([$prodId, $varSku, $sz, $price, $mrp, $varStock]);
        }

        set_flash('success', "Product '{$name}' created with variants.");
        redirect('/admin/products');
    }

    public function edit(int $id): void {
        $productModel = new Product();
        $product = $productModel->find($id);

        if (!$product) {
            set_flash('danger', 'Product not found.');
            redirect('/admin/products');
        }

        $categoryModel = new Category();
        $categories = $categoryModel->allActive();
        $variants = $productModel->getVariants($id);

        view('admin.products.edit', [
            'pageTitle' => 'Edit Product #' . $id . ' - AFOREHEALTH Admin',
            'product' => $product,
            'categories' => $categories,
            'variants' => $variants
        ]);
    }

    public function update(int $id): void {
        $name = trim($_POST['name'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $mrp = (float)($_POST['mrp'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 0);
        $discountPct = $mrp > $price ? round((($mrp - $price) / $mrp) * 100) : 0;

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            UPDATE products SET
                name = ?, category_id = ?, price = ?, mrp = ?, discount_percent = ?, stock = ?,
                short_description = ?, description = ?, material = ?, care_instructions = ?, hygiene_notice = ?,
                status = ?, is_featured = ?, is_bestseller = ?, is_new_arrival = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $name,
            (int)$_POST['category_id'],
            $price,
            $mrp,
            $discountPct,
            $stock,
            trim($_POST['short_description'] ?? ''),
            trim($_POST['description'] ?? ''),
            trim($_POST['material'] ?? ''),
            trim($_POST['care_instructions'] ?? ''),
            trim($_POST['hygiene_notice'] ?? ''),
            $_POST['status'] ?? 'published',
            !empty($_POST['is_featured']) ? 1 : 0,
            !empty($_POST['is_bestseller']) ? 1 : 0,
            !empty($_POST['is_new_arrival']) ? 1 : 0,
            $id
        ]);

        set_flash('success', 'Product updated successfully.');
        redirect('/admin/products');
    }

    public function duplicate(int $id): void {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $orig = $stmt->fetch();

        if ($orig) {
            $newName = $orig['name'] . ' (Copy)';
            $newSku = $orig['sku'] . '-COPY-' . mt_rand(10, 99);
            $newSlug = slugify($newName) . '-' . mt_rand(100, 999);

            $insStmt = $pdo->prepare("
                INSERT INTO products (
                    name, slug, sku, category_id, price, mrp, discount_percent, stock,
                    short_description, description, material, care_instructions, hygiene_notice,
                    primary_image, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')
            ");
            $insStmt->execute([
                $newName, $newSlug, $newSku, $orig['category_id'], $orig['price'], $orig['mrp'], $orig['discount_percent'], $orig['stock'],
                $orig['short_description'], $orig['description'], $orig['material'], $orig['care_instructions'], $orig['hygiene_notice'],
                $orig['primary_image']
            ]);

            set_flash('success', 'Product duplicated as draft.');
        }

        redirect('/admin/products');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $productModel = new Product();
        $productModel->delete($id);
        set_flash('success', 'Product deleted.');
        redirect('/admin/products');
    }
}
