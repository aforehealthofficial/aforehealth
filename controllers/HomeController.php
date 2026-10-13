<?php
namespace Controllers;

use Models\Product;
use Models\Category;
use Models\Review;
use PDO;

class HomeController {
    public function index(): void {
        $productModel = new Product();
        $categoryModel = new Category();
        $reviewModel = new Review();
        $pdo = getDbConnection();

        $categories = $categoryModel->allActive();
        $bestsellers = $productModel->getBestsellers(8);
        $featured = $productModel->getFeatured(4);
        $newArrivals = $productModel->getNewArrivals(8);
        $homepageReviews = $reviewModel->getHomepageReviews(6);

        // Fetch FAQs for homepage accordion
        $faqs = $pdo->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY sort_order ASC LIMIT 6")->fetchAll();

        view('storefront.home', [
            'pageTitle' => 'AFOREHEALTH | Everyday Comfort. Period Confidence.',
            'categories' => $categories,
            'bestsellers' => $bestsellers,
            'featured' => $featured,
            'newArrivals' => $newArrivals,
            'reviews' => $homepageReviews,
            'faqs' => $faqs
        ]);
    }
}
