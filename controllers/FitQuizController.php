<?php
namespace Controllers;

use Models\Product;
use PDO;

class FitQuizController {
    public function index(): void {
        $pdo = getDbConnection();
        $sampleProducts = $pdo->query("SELECT * FROM products WHERE status = 'published' ORDER BY is_bestseller DESC, rating_count DESC LIMIT 6")->fetchAll();

        view('storefront.fit-quiz', [
            'pageTitle' => 'Find Your Perfect Fit & Flow Quiz - AFOREHEALTH',
            'metaDescription' => 'Take our 60-second period panty quiz to find your ideal absorbency, size, and style engineered for your body.',
            'products' => $sampleProducts
        ]);
    }

    public function recommend(): void {
        $flow = $_POST['flow'] ?? 'heavy';
        $style = $_POST['style'] ?? 'high_waist';
        $size = strtoupper(trim($_POST['size'] ?? 'M'));

        $pdo = getDbConnection();

        // 1. Determine recommended category and search keyword
        $queryKeyword = '%period%';
        $absorbencyText = '4-Layer Core (Absorbs up to 35-40ml / ~4 regular pads or tampons)';
        $absorbencyBadge = 'Heavy Flow / Overnight';

        if ($flow === 'light') {
            $queryKeyword = '%light%pant%';
            $absorbencyText = '2-Layer Breathable Gusset (Absorbs up to 10-15ml / Spotting & Daily Discharge)';
            $absorbencyBadge = 'Light Flow & Everyday';
        } elseif ($flow === 'medium') {
            $queryKeyword = '%everyday%pant%' ;
            $absorbencyText = '3-Layer Moderate Core (Absorbs up to 20-25ml / Medium Days)';
            $absorbencyBadge = 'Medium / Moderate Flow';
        }

        // Try to match best product
        $stmt = $pdo->prepare("
            SELECT * FROM products 
            WHERE status = 'published' AND (name LIKE ? OR short_description LIKE ? OR category_id = 1)
            ORDER BY is_bestseller DESC, rating_count DESC
            LIMIT 1
        ");
        $stmt->execute([$queryKeyword, $queryKeyword]);
        $recommendedProd = $stmt->fetch();

        if (!$recommendedProd) {
            $recommendedProd = $pdo->query("SELECT * FROM products WHERE status = 'published' ORDER BY id ASC LIMIT 1")->fetch();
        }

        // Check if size variant exists
        $varStmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? AND size = ? AND status = 'active' LIMIT 1");
        $varStmt->execute([$recommendedProd['id'], $size]);
        $matchedVariant = $varStmt->fetch();

        json_response([
            'success' => true,
            'flow' => $flow,
            'style' => $style,
            'size' => $size,
            'absorbency_badge' => $absorbencyBadge,
            'absorbency_text' => $absorbencyText,
            'product' => [
                'id' => $recommendedProd['id'],
                'name' => $recommendedProd['name'],
                'slug' => $recommendedProd['slug'],
                'price' => (float)$recommendedProd['price'],
                'mrp' => (float)$recommendedProd['mrp'],
                'image_url' => upload_url('products/' . ($recommendedProd['primary_image'] ?: 'cat_period_panties.svg')),
                'variant_id' => $matchedVariant['id'] ?? null,
                'variant_sku' => $matchedVariant['sku'] ?? $recommendedProd['sku']
            ]
        ]);
    }
}
