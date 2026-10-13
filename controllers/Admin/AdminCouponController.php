<?php
namespace Controllers\Admin;

use Models\Coupon;
use PDO;

class AdminCouponController {
    public function index(): void {
        $pdo = getDbConnection();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $total = (int)$pdo->query("SELECT COUNT(*) FROM coupons")->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("SELECT * FROM coupons ORDER BY id DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute();
        $coupons = $stmt->fetchAll();

        view('admin.coupons.index', [
            'pageTitle' => 'Coupon & Discount Codes - AFOREHEALTH Admin',
            'coupons' => $coupons,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages
        ]);
    }

    public function store(): void {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $title = trim($_POST['title'] ?? '');
        $type = $_POST['discount_type'] ?? 'percentage';
        $val = (float)($_POST['discount_value'] ?? 0);
        $min = (float)($_POST['min_order_amount'] ?? 0);
        $max = !empty($_POST['max_discount_amount']) ? (float)$_POST['max_discount_amount'] : null;
        $expiry = $_POST['expiry_date'] ?? date('Y-12-31');

        if (empty($code) || $val <= 0) {
            set_flash('danger', 'Please enter valid code and discount value.');
            redirect('/admin/coupons');
        }

        $couponModel = new Coupon();
        $couponModel->create([
            'code' => $code,
            'title' => $title,
            'discount_type' => $type,
            'discount_value' => $val,
            'min_order_amount' => $min,
            'max_discount_amount' => $max,
            'start_date' => date('Y-m-d'),
            'expiry_date' => $expiry,
            'status' => 'active'
        ]);

        set_flash('success', "Coupon '{$code}' created successfully.");
        redirect('/admin/coupons');
    }

    public function toggleStatus(): void {
        $id = (int)($_POST['id'] ?? 0);
        $couponModel = new Coupon();
        $coupon = $couponModel->find($id);

        if ($coupon) {
            $newStatus = $coupon['status'] === 'active' ? 'inactive' : 'active';
            $couponModel->update($id, ['status' => $newStatus]);
            set_flash('success', "Coupon status changed to {$newStatus}.");
        }

        redirect('/admin/coupons');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $couponModel = new Coupon();
        $couponModel->delete($id);
        set_flash('success', 'Coupon deleted.');
        redirect('/admin/coupons');
    }
}
