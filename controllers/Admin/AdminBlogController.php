<?php
namespace Controllers\Admin;

use PDO;

class AdminBlogController {
    public function index(): void {
        $pdo = getDbConnection();

        $posts = $pdo->query("
            SELECT bp.*, bc.name as category_name
            FROM blog_posts bp
            JOIN blog_categories bc ON bp.category_id = bc.id
            ORDER BY bp.id DESC
        ")->fetchAll();

        view('admin.blog.index', [
            'pageTitle' => 'Blog Posts Management - AFOREHEALTH Admin',
            'posts' => $posts
        ]);
    }

    public function create(): void {
        $pdo = getDbConnection();
        $categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll();

        view('admin.blog.create', [
            'pageTitle' => 'Write New Guide / Post - AFOREHEALTH Admin',
            'categories' => $categories
        ]);
    }

    public function store(): void {
        $title = trim($_POST['title'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (empty($title) || empty($content)) {
            set_flash('danger', 'Title and content are required.');
            redirect('/admin/blog/create');
        }

        $slug = slugify($title);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO blog_posts (category_id, title, slug, excerpt, content, featured_image, status, published_at)
            VALUES (?, ?, ?, ?, ?, 'blog_science_period_panties.svg', 'published', NOW())
        ");
        $stmt->execute([$categoryId, $title, $slug, $excerpt, $content]);

        set_flash('success', "Post '{$title}' published successfully.");
        redirect('/admin/blog');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
        set_flash('success', 'Blog post deleted.');
        redirect('/admin/blog');
    }
}
