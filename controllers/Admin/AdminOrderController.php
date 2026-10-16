<?php
namespace Controllers\Admin;

use Models\Order;
use Services\InvoiceService;
use PDO;

class AdminOrderController {
    public function index(): void {
        $pdo = getDbConnection();

        $statusFilter = $_GET['status'] ?? 'all';
        $paymentFilter = $_GET['payment'] ?? 'all';
        $search = trim($_GET['q'] ?? '');

        $where = ["1=1"];
        $params = [];

        if ($statusFilter !== 'all') {
            $where[] = "o.order_status = ?";
            $params[] = strtoupper($statusFilter);
        }

        if ($paymentFilter !== 'all') {
            $where[] = "o.payment_status = ?";
            $params[] = strtolower($paymentFilter);
        }

        if (!empty($search)) {
            $where[] = "(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_mobile LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $whereClause = implode(' AND ', $where);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders o WHERE {$whereClause}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT o.*,
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
            FROM orders o
            WHERE {$whereClause}
            ORDER BY o.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        view('admin.orders.index', [
            'pageTitle' => 'Order Management - AFOREHEALTH Admin',
            'orders' => $orders,
            'statusFilter' => $statusFilter,
            'paymentFilter' => $paymentFilter,
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    public function show(string $orderNumber): void {
        $orderModel = new Order();
        $order = $orderModel->findByOrderNumber($orderNumber);

        if (!$order) {
            set_flash('danger', 'Order not found.');
            redirect('/admin/orders');
        }

        view('admin.orders.show', [
            'pageTitle' => 'Order #' . $order['order_number'] . ' - AFOREHEALTH Admin',
            'order' => $order
        ]);
    }

    public function updateStatus(): void {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $orderNumber = $_POST['order_number'] ?? '';
        $newStatus = $_POST['status'] ?? '';
        $courier = trim($_POST['courier_name'] ?? '');
        $tracking = trim($_POST['tracking_number'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $orderModel = new Order();
        $orderModel->updateStatus($orderId, $newStatus, $notes, $courier ?: null, $tracking ?: null);

        // Send email update to customer from info@aforehealth.com
        $order = $orderModel->findByOrderNumber($orderNumber);
        if ($order && !empty($order['customer_email'])) {
            try {
                $emailService = new \Services\EmailService();
                $emailService->sendOrderStatusUpdate(
                    $order['customer_email'],
                    $order,
                    $newStatus,
                    $courier ?: null,
                    $tracking ?: null,
                    $notes ?: null
                );
            } catch (\Exception $e) {
                error_log("Failed to send order status email: " . $e->getMessage());
            }
        }

        set_flash('success', "Order #{$orderNumber} status updated to {$newStatus}.");
        redirect('/admin/orders/' . $orderNumber);
    }

    public function shippingLabel(string $orderNumber): void {
        $orderModel = new Order();
        $order = $orderModel->findByOrderNumber($orderNumber);

        if (!$order) {
            set_flash('danger', 'Order not found.');
            redirect('/admin/orders');
        }

        view('admin.orders.shipping-label', [
            'pageTitle' => 'Shipping Label - ' . $order['order_number'],
            'order' => $order
        ]);
    }
}
