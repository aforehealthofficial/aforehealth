<?php
namespace Controllers;

use PDO;

class SearchApiController {
    public function suggestions(): void {
        $q = trim($_GET['q'] ?? '');
        if (mb_strlen($q) < 2) {
            json_response(['categories' => [], 'products' => [], 'tags' => []]);
        }

        $pdo = getDbConnection();
        $param = "%{$q}%";

        // 1. Matching Categories
        $stmtCat = $pdo->prepare("
            SELECT id, name, slug 
            FROM categories 
            WHERE name LIKE ? AND status = 'active' 
            LIMIT 4
        ");
        $stmtCat->execute([$param]);
        $categories = $stmtCat->fetchAll();

        // 2. Matching Products
        $stmtProd = $pdo->prepare("
            SELECT id, name, slug, price, mrp, primary_image, is_bestseller
            FROM products
            WHERE (name LIKE ? OR sku LIKE ? OR tags LIKE ? OR short_description LIKE ?) 
              AND status = 'published'
            ORDER BY is_bestseller DESC, id DESC
            LIMIT 6
        ");
        $stmtProd->execute([$param, $param, $param, $param]);
        $products = $stmtProd->fetchAll();

        foreach ($products as &$p) {
            $p['url'] = url('product/' . $p['slug']);
            $p['image_url'] = upload_url('products/' . ($p['primary_image'] ?? 'placeholder.svg'));
            $p['formatted_price'] = '₹' . number_format($p['price'], 0);
            $p['formatted_mrp'] = '₹' . number_format($p['mrp'], 0);
        }

        json_response([
            'query' => $q,
            'categories' => $categories,
            'products' => $products
        ]);
    }
}
