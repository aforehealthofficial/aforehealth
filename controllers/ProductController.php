<?php
namespace Controllers;

use Models\Product;
use Models\Review;
use Models\ProductQuestion;
use Models\RecentlyViewed;
use Models\FlashSale;

class ProductController {
    public function show(string $slug): void {
        $productModel = new Product();
        $reviewModel = new Review();
        $questionModel = new ProductQuestion();
        $recentModel = new RecentlyViewed();
        $flashModel = new FlashSale();

        $product = $productModel->findBySlug($slug);
        if (!$product) {
            http_response_code(404);
            view('errors.404', ['pageTitle' => 'Product Not Found - AFOREHEALTH']);
            return;
        }

        $userId = is_authenticated() ? (int)auth_user()['id'] : null;
        $sessionId = session_id();

        // 1. Record Recently Viewed
        $recentModel->record($product['id'], $userId, $sessionId);

        // 2. Approved reviews & Rating distribution
        $reviews = $reviewModel->getApprovedForProduct($product['id']);
        $ratingDistribution = $reviewModel->getRatingDistribution($product['id']);
        $relatedProducts = $productModel->getRelatedProducts($product['category_id'], $product['id'], 4);

        // 3. Approved Questions & Answers
        $questions = $questionModel->getApprovedForProduct($product['id']);

        // 4. Recently Viewed Products (excluding current)
        $recentlyViewed = $recentModel->getProducts($userId, $sessionId, 4, $product['id']);

        // 5. Active Flash Sale check
        $flashSale = $flashModel->getProductFlashDiscount($product['id']);

        // 6. Review eligibility
        $canReview = false;
        $hasReviewed = false;
        if ($userId) {
            $canReview = $reviewModel->hasPurchased($userId, $product['id']);
            $hasReviewed = $reviewModel->hasReviewed($userId, $product['id']);
        }

        // 7. Comparison state
        $inCompare = in_array($product['id'], $_SESSION['compare_ids'] ?? []);

        view('storefront.product', [
            'pageTitle' => e($product['name']) . ' - AFOREHEALTH',
            'product' => $product,
            'reviews' => $reviews,
            'ratingStats' => $ratingDistribution,
            'relatedProducts' => $relatedProducts,
            'questions' => $questions,
            'recentlyViewed' => $recentlyViewed,
            'flashSale' => $flashSale,
            'canReview' => $canReview,
            'hasReviewed' => $hasReviewed,
            'inCompare' => $inCompare
        ]);
    }
}
