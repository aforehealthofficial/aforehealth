<?php
namespace Controllers;

use PDO;

class BlogController {
    public function index(): void {
        $pdo = getDbConnection();

        $selectedCategory = trim($_GET['category'] ?? '');
        $searchQuery = trim($_GET['q'] ?? '');

        $sql = "
            SELECT bp.*, bc.name as category_name, bc.slug as category_slug
            FROM blog_posts bp
            JOIN blog_categories bc ON bp.category_id = bc.id
            WHERE bp.status = 'published'
        ";
        $params = [];

        if (!empty($selectedCategory)) {
            $sql .= " AND bc.slug = ?";
            $params[] = $selectedCategory;
        }

        if (!empty($searchQuery)) {
            $sql .= " AND (bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ?)";
            $params[] = "%{$searchQuery}%";
            $params[] = "%{$searchQuery}%";
            $params[] = "%{$searchQuery}%";
        }

        // Total matching count for pagination
        $countSql = "
            SELECT COUNT(bp.id)
            FROM blog_posts bp
            JOIN blog_categories bc ON bp.category_id = bc.id
            WHERE bp.status = 'published'
        ";
        if (!empty($selectedCategory)) {
            $countSql .= " AND bc.slug = ?";
        }
        if (!empty($searchQuery)) {
            $countSql .= " AND (bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.content LIKE ?)";
        }
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalMatchingPosts = (int)$countStmt->fetchColumn();

        // 20 per page pagination
        $perPage = 20;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $totalPages = max(1, (int)ceil($totalMatchingPosts / $perPage));
        if ($page > $totalPages && $totalMatchingPosts > 0) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        // Categories with live published post counts
        $categories = $pdo->query("
            SELECT bc.*, COUNT(bp.id) as post_count
            FROM blog_categories bc
            LEFT JOIN blog_posts bp ON bp.category_id = bc.id AND bp.status = 'published'
            GROUP BY bc.id
            ORDER BY bc.name ASC
        ")->fetchAll();

        $totalPublishedPosts = (int)$pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn();

        // Recent posts for sidebar
        $recentPosts = $pdo->query("
            SELECT bp.id, bp.title, bp.slug, bp.featured_image, bp.published_at, bc.name as category_name
            FROM blog_posts bp
            JOIN blog_categories bc ON bp.category_id = bc.id
            WHERE bp.status = 'published'
            ORDER BY bp.published_at DESC
            LIMIT 4
        ")->fetchAll();

        view('blog.index', [
            'pageTitle' => 'Period Care & Intimate Health Guide - AFOREHEALTH',
            'posts' => $posts,
            'categories' => $categories,
            'totalPublishedPosts' => $totalPublishedPosts,
            'totalMatchingPosts' => $totalMatchingPosts,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'selectedCategory' => $selectedCategory,
            'searchQuery' => $searchQuery,
            'recentPosts' => $recentPosts
        ]);
    }

    public function show(string $slug): void {
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("
            SELECT bp.*, bc.name as category_name, bc.slug as category_slug
            FROM blog_posts bp
            JOIN blog_categories bc ON bp.category_id = bc.id
            WHERE bp.slug = ? AND bp.status = 'published'
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $post = $stmt->fetch();

        if (!$post) {
            http_response_code(404);
            view('errors.404', ['pageTitle' => 'Article Not Found - AFOREHEALTH']);
            return;
        }

        // Recent / Related posts
        $recentStmt = $pdo->prepare("
            SELECT bp.*, bc.name as category_name
            FROM blog_posts bp
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            WHERE bp.id != ? AND bp.status = 'published'
            ORDER BY bp.published_at DESC
            LIMIT 3
        ");
        $recentStmt->execute([$post['id']]);
        $recentPosts = $recentStmt->fetchAll();

        // Doctor-recommended products for sidebar
        $recStmt = $pdo->query("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'published' AND p.is_bestseller = 1
            ORDER BY p.rating_count DESC
            LIMIT 3
        ");
        $recommendedProducts = $recStmt->fetchAll();

        // Blog categories for sidebar
        $catStmt = $pdo->query("
            SELECT bc.*, COUNT(bp.id) as post_count
            FROM blog_categories bc
            LEFT JOIN blog_posts bp ON bc.id = bp.category_id AND bp.status = 'published'
            GROUP BY bc.id
            ORDER BY bc.name ASC
        ");
        $categories = $catStmt->fetchAll();

        view('blog.show', [
            'pageTitle' => (!empty($post['seo_title']) ? $post['seo_title'] : $post['title']) . ' - AFOREHEALTH',
            'metaDescription' => !empty($post['seo_description']) ? $post['seo_description'] : $post['excerpt'],
            'post' => $post,
            'recentPosts' => $recentPosts,
            'recommendedProducts' => $recommendedProducts,
            'categories' => $categories
        ]);
    }
}
