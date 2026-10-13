<?php
namespace Controllers;

use PDO;

class BlogController {
    public function index(): void {
        $pdo = getDbConnection();

        $posts = $pdo->query("
            SELECT bp.*, bc.name as category_name, bc.slug as category_slug
            FROM blog_posts bp
            JOIN blog_categories bc ON bp.category_id = bc.id
            WHERE bp.status = 'published'
            ORDER BY bp.published_at DESC
        ")->fetchAll();

        $categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll();

        view('blog.index', [
            'pageTitle' => 'Period Care & Intimate Health Guide - AFOREHEALTH',
            'posts' => $posts,
            'categories' => $categories
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

        // Recent posts
        $recentStmt = $pdo->prepare("
            SELECT * FROM blog_posts WHERE id != ? AND status = 'published' ORDER BY published_at DESC LIMIT 3
        ");
        $recentStmt->execute([$post['id']]);
        $recentPosts = $recentStmt->fetchAll();

        view('blog.show', [
            'pageTitle' => e($post['title']) . ' - AFOREHEALTH',
            'post' => $post,
            'recentPosts' => $recentPosts
        ]);
    }
}
