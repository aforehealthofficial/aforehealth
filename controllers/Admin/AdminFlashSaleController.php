<?php
namespace Controllers\Admin;

use Models\FlashSale;
use Models\Product;
use PDO;

class AdminFlashSaleController {
    public function index(): void {
        $pdo = getDbConnection();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $total = (int)$pdo->query("SELECT COUNT(*) FROM flash_sales")->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT fs.*, 
                   (SELECT COUNT(*) FROM flash_sale_products WHERE flash_sale_id = fs.id) as product_count,
                   (CASE 
                       WHEN NOW() < fs.start_at THEN 'UPCOMING'
                       WHEN NOW() BETWEEN fs.start_at AND fs.end_at THEN 'RUNNING'
                       ELSE 'EXPIRED'
                   END) as live_status
            FROM flash_sales fs
            ORDER BY fs.id DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute();
        $sales = $stmt->fetchAll();

        $prodModel = new Product();
        $products = $prodModel->all('name', 'ASC');

        view('admin.flash-sales.index', [
            'pageTitle' => 'Flash Sales & Countdown Events - AFOREHEALTH Admin',
            'sales' => $sales,
            'products' => $products,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages
        ]);
    }

    public function store(): void {
        $title = trim($_POST['title'] ?? '');
        $discountPercent = (float)($_POST['discount_percent'] ?? 20);
        $startAt = $_POST['start_at'] ?? '';
        $endAt = $_POST['end_at'] ?? '';
        $bannerText = trim($_POST['banner_text'] ?? 'Special Flash Sale');
        $productIds = $_POST['product_ids'] ?? [];

        if (empty($title) || empty($startAt) || empty($endAt) || empty($productIds)) {
            set_flash('danger', 'Please provide sale title, start date, end date, and select products.');
            redirect('/admin/flash-sales');
        }

        $slug = slugify($title);
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("
            INSERT INTO flash_sales (title, slug, discount_percent, banner_text, start_at, end_at, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$title, $slug, $discountPercent, $bannerText, $startAt, $endAt]);
        $saleId = (int)$pdo->lastInsertId();

        $stmtP = $pdo->prepare("INSERT INTO flash_sale_products (flash_sale_id, product_id) VALUES (?, ?)");
        foreach ($productIds as $pid) {
            $stmtP->execute([$saleId, (int)$pid]);
        }

        set_flash('success', "Flash sale '{$title}' created successfully.");
        redirect('/admin/flash-sales');
    }

    public function toggle(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $pdo->prepare("UPDATE flash_sales SET status = IF(status='active', 'inactive', 'active') WHERE id = ?")->execute([$id]);

        set_flash('success', 'Flash sale status updated.');
        redirect('/admin/flash-sales');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $pdo->prepare("DELETE FROM flash_sales WHERE id = ?")->execute([$id]);

        set_flash('success', 'Flash sale deleted.');
        redirect('/admin/flash-sales');
    }
}
