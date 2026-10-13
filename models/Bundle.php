<?php
namespace Models;

use PDO;
use Exception;

class Bundle extends BaseModel {
    protected string $table = 'bundles';

    public function getActiveBundles(): array {
        $stmt = $this->db->query("SELECT * FROM bundles WHERE status = 'active' ORDER BY id ASC");
        $bundles = $stmt->fetchAll();
        foreach ($bundles as &$b) {
            $b['categories'] = $this->getBundleCategories($b['id']);
        }
        return $bundles;
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM bundles WHERE slug = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$slug]);
        $bundle = $stmt->fetch();
        if (!$bundle) return null;

        $bundle['categories'] = $this->getBundleCategories($bundle['id']);
        return $bundle;
    }

    public function getBundleCategories(int $bundleId): array {
        $stmt = $this->db->prepare("
            SELECT bi.*, c.name as category_name, c.slug as category_slug
            FROM bundle_items bi
            JOIN categories c ON bi.category_id = c.id
            WHERE bi.bundle_id = ?
        ");
        $stmt->execute([$bundleId]);
        $rows = $stmt->fetchAll();

        $prodModel = new Product();
        foreach ($rows as &$row) {
            // Fetch eligible published products in this category with variants
            $stmtP = $this->db->prepare("
                SELECT id, name, slug, price, mrp, primary_image 
                FROM products 
                WHERE category_id = ? AND status = 'published' AND stock > 0
                LIMIT 8
            ");
            $stmtP->execute([$row['category_id']]);
            $prods = $stmtP->fetchAll();
            foreach ($prods as &$p) {
                $p['variants'] = $prodModel->getVariants($p['id']);
            }
            $row['products'] = $prods;
        }
        return $rows;
    }

    /**
     * Calculate bundle price strictly server-side
     */
    public function calculateBundle(int $bundleId, array $variantIds): array {
        $bundle = $this->find($bundleId);
        if (!$bundle || $bundle['status'] !== 'active') {
            throw new Exception('Selected bundle is inactive or does not exist.');
        }

        $minItems = (int)$bundle['min_items'];
        $cleanVariantIds = array_filter(array_map('intval', $variantIds));

        if (count($cleanVariantIds) < $minItems) {
            throw new Exception("Please select at least {$minItems} items for this bundle.");
        }

        $placeholders = implode(',', array_fill(0, count($cleanVariantIds), '?'));
        $stmt = $this->db->prepare("
            SELECT pv.id as variant_id, pv.sku, pv.color, pv.size, pv.stock,
                   p.id as product_id, p.name as product_name, p.slug as product_slug,
                   p.price, p.mrp, p.primary_image, p.status as product_status
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            WHERE pv.id IN ({$placeholders})
        ");
        $stmt->execute($cleanVariantIds);
        $items = $stmt->fetchAll();

        if (count($items) !== count($cleanVariantIds)) {
            throw new Exception('One or more selected products/variants could not be found.');
        }

        $individualTotal = 0;
        $mrpTotal = 0;

        foreach ($items as $item) {
            if ($item['product_status'] !== 'published' || $item['stock'] < 1) {
                throw new Exception("Product '{$item['product_name']}' is currently out of stock.");
            }
            $individualTotal += (float)$item['price'];
            $mrpTotal += (float)$item['mrp'];
        }

        // Apply bundle discount
        $discountValue = (float)$bundle['discount_value'];
        if ($bundle['discount_type'] === 'percentage') {
            $discountAmount = round(($individualTotal * ($discountValue / 100)), 2);
        } else {
            $discountAmount = min($individualTotal, $discountValue);
        }

        $finalPrice = max(0, $individualTotal - $discountAmount);
        $totalSavings = round($individualTotal - $finalPrice, 2);

        return [
            'bundle' => $bundle,
            'items' => $items,
            'item_count' => count($items),
            'mrp_total' => $mrpTotal,
            'individual_total' => $individualTotal,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'total_savings' => $totalSavings
        ];
    }
}
