<?php
namespace Controllers;

use Models\Product;
use Models\Category;

class ShopController {
    public function index(): void {
        $productModel = new Product();
        $categoryModel = new Category();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 48, 50]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $filters = [
            'category' => $_GET['category'] ?? null,
            'subcategory' => $_GET['subcategory'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'size' => $_GET['size'] ?? null,
            'color' => $_GET['color'] ?? null,
            'rating' => $_GET['rating'] ?? null,
            'discount' => $_GET['discount'] ?? null,
            'sort' => $_GET['sort'] ?? 'popularity',
            'q' => $_GET['q'] ?? null,
        ];

        $results = $productModel->filterProducts($filters, $page, $perPage);
        $categories = $categoryModel->allActive();

        view('storefront.shop', [
            'pageTitle' => 'Shop All Products - AFOREHEALTH',
            'products' => $results['products'],
            'total' => $results['total'],
            'page' => $results['page'],
            'perPage' => $perPage,
            'totalPages' => $results['total_pages'],
            'filters' => $filters,
            'categories' => $categories,
            'currentCategory' => null
        ]);
    }

    public function category(string $slug): void {
        $productModel = new Product();
        $categoryModel = new Category();

        $category = $categoryModel->findBySlug($slug);
        if (!$category) {
            http_response_code(404);
            view('errors.404', ['pageTitle' => 'Category Not Found - AFOREHEALTH']);
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 48, 50]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $filters = [
            'category' => $category['id'],
            'subcategory' => $_GET['subcategory'] ?? null,
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'size' => $_GET['size'] ?? null,
            'color' => $_GET['color'] ?? null,
            'rating' => $_GET['rating'] ?? null,
            'discount' => $_GET['discount'] ?? null,
            'sort' => $_GET['sort'] ?? 'popularity'
        ];

        $results = $productModel->filterProducts($filters, $page, $perPage);
        $categories = $categoryModel->allActive();

        view('storefront.category', [
            'pageTitle' => e($category['name']) . ' - AFOREHEALTH',
            'category' => $category,
            'products' => $results['products'],
            'total' => $results['total'],
            'page' => $results['page'],
            'perPage' => $perPage,
            'totalPages' => $results['total_pages'],
            'filters' => $filters,
            'categories' => $categories
        ]);
    }

    public function combos(): void {
        $productModel = new Product();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 48, 50]) ? (int)($_GET['per_page'] ?? 20) : 20;

        // Category 6 is Combos & Value Packs
        $filters = [
            'category' => 6,
            'sort' => $_GET['sort'] ?? 'popularity'
        ];

        $results = $productModel->filterProducts($filters, $page, $perPage);

        view('storefront.combos', [
            'pageTitle' => 'Combo & Value Packs - AFOREHEALTH',
            'products' => $results['products'],
            'total' => $results['total'],
            'page' => $results['page'],
            'perPage' => $perPage,
            'totalPages' => $results['total_pages']
        ]);
    }

    public function offers(): void {
        $productModel = new Product();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 48, 50]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $filters = [
            'discount' => 15, // Items with at least 15% discount
            'sort' => 'discount'
        ];

        $results = $productModel->filterProducts($filters, $page, $perPage);

        // Fetch coupons
        $pdo = getDbConnection();
        $coupons = $pdo->query("SELECT * FROM coupons WHERE status = 'active' ORDER BY discount_value DESC")->fetchAll();

        view('storefront.offers', [
            'pageTitle' => 'Exclusive Offers & Deals - AFOREHEALTH',
            'products' => $results['products'],
            'total' => $results['total'],
            'page' => $results['page'],
            'perPage' => $perPage,
            'totalPages' => $results['total_pages'],
            'coupons' => $coupons
        ]);
    }

    public function search(): void {
        $productModel = new Product();
        $query = trim($_GET['q'] ?? '');

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 48, 50]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $filters = [
            'q' => $query,
            'sort' => $_GET['sort'] ?? 'popularity'
        ];

        $results = $productModel->filterProducts($filters, $page, $perPage);

        view('storefront.search', [
            'pageTitle' => 'Search Results for "' . e($query) . '" - AFOREHEALTH',
            'query' => $query,
            'products' => $results['products'],
            'total' => $results['total'],
            'page' => $results['page'],
            'perPage' => $perPage,
            'totalPages' => $results['total_pages']
        ]);
    }
}
