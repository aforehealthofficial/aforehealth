<?php
namespace Controllers\Admin;

use PDO;

class AdminBlogController {
    public function index(): void {
        $pdo = getDbConnection();

        $search = trim($_GET['q'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $where = ["1=1"];
        $params = [];
        if (!empty($search)) {
            $where[] = "(bp.title LIKE ? OR bp.excerpt LIKE ? OR bp.author LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }
        $whereClause = implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM blog_posts bp WHERE {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT bp.*, bc.name as category_name
            FROM blog_posts bp
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id
            WHERE {$whereClause}
            ORDER BY bp.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        view('admin.blog.index', [
            'pageTitle' => 'Blog & SEO Articles Management - AFOREHEALTH Admin',
            'posts' => $posts,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    public function create(): void {
        $pdo = getDbConnection();
        $categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll();

        view('admin.blog.create', [
            'pageTitle' => 'Write New SEO Blog Article - AFOREHEALTH Admin',
            'categories' => $categories
        ]);
    }

    public function store(): void {
        $title = trim($_POST['title'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $author = trim($_POST['author'] ?? 'AFOREHEALTH Wellness Team');
        $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
        $seoTitle = trim($_POST['seo_title'] ?? '') ?: $title;
        $seoDesc = trim($_POST['seo_description'] ?? '') ?: $excerpt;

        if (empty($title) || empty($content)) {
            set_flash('danger', 'Article Title and Content are required.');
            redirect('/admin/blog/create');
        }

        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = slugify($title);
        } else {
            $slug = slugify($slug);
        }

        // Handle Image Upload
        $featuredImage = 'blog_science_period_panties.svg';
        if (!empty($_FILES['featured_image']['tmp_name'])) {
            $uploaded = upload_file($_FILES['featured_image'], 'blog');
            if ($uploaded) {
                $featuredImage = $uploaded;
            }
        }

        $pdo = getDbConnection();

        // Check unique slug
        $check = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ? LIMIT 1");
        $check->execute([$slug]);
        if ($check->fetch()) {
            $slug = $slug . '-' . time();
        }

        $videoUrl = trim($_POST['video_url'] ?? '');

        $stmt = $pdo->prepare("
            INSERT INTO blog_posts (category_id, title, slug, author, excerpt, content, featured_image, video_url, status, published_at, seo_title, seo_description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $publishedAt = ($status === 'published') ? date('Y-m-d H:i:s') : null;
        $stmt->execute([
            $categoryId,
            $title,
            $slug,
            $author,
            $excerpt,
            $content,
            $featuredImage,
            $videoUrl ?: null,
            $status,
            $publishedAt,
            $seoTitle,
            $seoDesc
        ]);

        set_flash('success', "Article '{$title}' created successfully.");
        redirect('/admin/blog');
    }

    public function edit(string $id): void {
        $id = (int)$id;
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $post = $stmt->fetch();

        if (!$post) {
            set_flash('danger', 'Blog article not found.');
            redirect('/admin/blog');
        }

        $categories = $pdo->query("SELECT * FROM blog_categories ORDER BY name ASC")->fetchAll();

        view('admin.blog.edit', [
            'pageTitle' => 'Edit Article: ' . $post['title'] . ' - AFOREHEALTH Admin',
            'post' => $post,
            'categories' => $categories
        ]);
    }

    public function update(): void {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $author = trim($_POST['author'] ?? 'AFOREHEALTH Wellness Team');
        $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
        $seoTitle = trim($_POST['seo_title'] ?? '') ?: $title;
        $seoDesc = trim($_POST['seo_description'] ?? '') ?: $excerpt;

        if (!$id || empty($title) || empty($content)) {
            set_flash('danger', 'Title and Content cannot be empty.');
            redirect($id ? '/admin/blog/edit/' . $id : '/admin/blog');
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            set_flash('danger', 'Article not found.');
            redirect('/admin/blog');
        }

        $slug = trim($_POST['slug'] ?? '');
        if (empty($slug)) {
            $slug = slugify($title);
        } else {
            $slug = slugify($slug);
        }

        // Check if slug taken by another post
        $check = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ? LIMIT 1");
        $check->execute([$slug, $id]);
        if ($check->fetch()) {
            $slug = $slug . '-' . time();
        }

        $featuredImage = $existing['featured_image'];
        if (!empty($_FILES['featured_image']['tmp_name'])) {
            $uploaded = upload_file($_FILES['featured_image'], 'blog');
            if ($uploaded) {
                $featuredImage = $uploaded;
            }
        }

        $publishedAt = $existing['published_at'];
        if ($status === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        $videoUrl = trim($_POST['video_url'] ?? '');

        $upd = $pdo->prepare("
            UPDATE blog_posts 
            SET category_id = ?, title = ?, slug = ?, author = ?, excerpt = ?, content = ?, 
                featured_image = ?, video_url = ?, status = ?, published_at = ?, seo_title = ?, seo_description = ?
            WHERE id = ?
        ");
        $upd->execute([
            $categoryId,
            $title,
            $slug,
            $author,
            $excerpt,
            $content,
            $featuredImage,
            $videoUrl ?: null,
            $status,
            $publishedAt,
            $seoTitle,
            $seoDesc,
            $id
        ]);

        set_flash('success', "Article '{$title}' updated successfully.");
        redirect('/admin/blog');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
        set_flash('success', 'Blog article deleted successfully.');
        redirect('/admin/blog');
    }
}
