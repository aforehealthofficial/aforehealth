<?php
namespace Controllers\Admin;

use Models\Bundle;
use Models\Category;
use PDO;

class AdminBundleController {
    public function index(): void {
        $pdo = getDbConnection();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $total = (int)$pdo->query("SELECT COUNT(*) FROM bundles")->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("SELECT * FROM bundles ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute();
        $bundles = $stmt->fetchAll();

        $bundleModel = new Bundle();
        foreach ($bundles as &$b) {
            $b['categories'] = $bundleModel->getBundleCategories($b['id']);
        }

        view('admin.bundles.index', [
            'pageTitle' => 'Custom Bundle & Kit Management - AFOREHEALTH Admin',
            'bundles' => $bundles,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages
        ]);
    }

    public function create(): void {
        $catModel = new Category();
        $categories = $catModel->allActive();

        view('admin.bundles.create', [
            'pageTitle' => 'Create Custom Kit / Bundle - AFOREHEALTH Admin',
            'categories' => $categories
        ]);
    }

    public function store(): void {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $discountType = $_POST['discount_type'] ?? 'percentage';
        $discountValue = (float)($_POST['discount_value'] ?? 20);
        $minItems = max(2, (int)($_POST['min_items'] ?? 3));
        $categoryIds = $_POST['category_ids'] ?? [];

        if (empty($name) || empty($categoryIds)) {
            set_flash('danger', 'Bundle name and at least one category are required.');
            redirect('/admin/bundles/create');
        }

        $slug = slugify($name);
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("
            INSERT INTO bundles (name, slug, description, discount_type, discount_value, min_items, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$name, $slug, $description, $discountType, $discountValue, $minItems]);
        $bundleId = (int)$pdo->lastInsertId();

        $stmtItem = $pdo->prepare("INSERT INTO bundle_items (bundle_id, category_id, is_required) VALUES (?, ?, 1)");
        foreach ($categoryIds as $catId) {
            $stmtItem->execute([$bundleId, (int)$catId]);
        }

        set_flash('success', "Bundle '{$name}' created successfully.");
        redirect('/admin/bundles');
    }

    public function toggle(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $pdo->prepare("UPDATE bundles SET status = IF(status='active', 'inactive', 'active') WHERE id = ?")->execute([$id]);

        set_flash('success', 'Bundle status updated.');
        redirect('/admin/bundles');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $pdo->prepare("DELETE FROM bundles WHERE id = ?")->execute([$id]);

        set_flash('success', 'Bundle deleted.');
        redirect('/admin/bundles');
    }
}
