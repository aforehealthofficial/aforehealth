<?php
namespace Controllers\Admin;

use Models\User;
use PDO;

class AdminCustomerController {
    public function index(): void {
        $pdo = getDbConnection();
        $search = trim($_GET['q'] ?? '');
        $segment = $_GET['segment'] ?? 'all';

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(u.name LIKE ? OR u.mobile LIKE ? OR u.email LIKE ?)";
            $s = "%{$search}%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        switch ($segment) {
            case 'new':
                $where[] = "u.created_at >= (NOW() - INTERVAL 30 DAY)";
                break;
            case 'repeat':
                $where[] = "(SELECT COUNT(*) FROM orders WHERE user_id = u.id) >= 2";
                break;
            case 'high_value':
                $where[] = "(SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE user_id = u.id) >= 3000";
                break;
            case 'inactive':
                $where[] = "(SELECT COUNT(*) FROM orders WHERE user_id = u.id AND created_at > NOW() - INTERVAL 60 DAY) = 0";
                break;
            case 'cod':
                $where[] = "EXISTS (SELECT 1 FROM orders WHERE user_id = u.id AND payment_method = 'cod')";
                break;
            case 'prepaid':
                $where[] = "EXISTS (SELECT 1 FROM orders WHERE user_id = u.id AND payment_method != 'cod')";
                break;
        }

        $whereSql = implode(' AND ', $where);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT u.*,
                   (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as orders_count,
                   (SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE user_id = u.id) as total_spent,
                   (SELECT MAX(created_at) FROM orders WHERE user_id = u.id) as last_order_date
            FROM users u
            WHERE {$whereSql}
            ORDER BY u.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $customers = $stmt->fetchAll();

        // Counts for segment tabs
        $counts = [
            'all' => (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'new' => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= (NOW() - INTERVAL 30 DAY)")->fetchColumn(),
            'repeat' => (int)$pdo->query("SELECT COUNT(*) FROM users u WHERE (SELECT COUNT(*) FROM orders WHERE user_id = u.id) >= 2")->fetchColumn(),
            'high_value' => (int)$pdo->query("SELECT COUNT(*) FROM users u WHERE (SELECT COALESCE(SUM(grand_total), 0) FROM orders WHERE user_id = u.id) >= 3000")->fetchColumn(),
            'inactive' => (int)$pdo->query("SELECT COUNT(*) FROM users u WHERE (SELECT COUNT(*) FROM orders WHERE user_id = u.id AND created_at > NOW() - INTERVAL 60 DAY) = 0")->fetchColumn(),
            'cod' => (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM orders WHERE payment_method = 'cod'")->fetchColumn(),
            'prepaid' => (int)$pdo->query("SELECT COUNT(DISTINCT user_id) FROM orders WHERE payment_method != 'cod'")->fetchColumn()
        ];

        view('admin.customers.index', [
            'pageTitle' => 'Customers & Segments - AFOREHEALTH Admin',
            'customers' => $customers,
            'search' => $search,
            'currentSegment' => $segment,
            'counts' => $counts,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    public function show(int $id): void {
        $userModel = new User();
        $customer = $userModel->find($id);

        if (!$customer) {
            set_flash('danger', 'Customer not found.');
            redirect('/admin/customers');
        }

        $pdo = getDbConnection();
        $orders = $pdo->query("SELECT * FROM orders WHERE user_id = {$id} ORDER BY created_at DESC")->fetchAll();
        $addresses = $userModel->getAddresses($id);

        $totalSpent = array_sum(array_column($orders, 'grand_total'));
        $ordersCount = count($orders);
        $aov = $ordersCount > 0 ? ($totalSpent / $ordersCount) : 0;
        $firstOrder = !empty($orders) ? end($orders) : null;
        $lastOrder = !empty($orders) ? reset($orders) : null;

        $reviews = $pdo->query("
            SELECT r.*, p.name as product_name 
            FROM reviews r 
            JOIN products p ON r.product_id = p.id 
            WHERE r.user_id = {$id} 
            ORDER BY r.created_at DESC
        ")->fetchAll();

        view('admin.customers.show', [
            'pageTitle' => 'Customer Profile: ' . e($customer['name']) . ' - AFOREHEALTH Admin',
            'customer' => $customer,
            'orders' => $orders,
            'addresses' => $addresses,
            'reviews' => $reviews,
            'stats' => [
                'total_orders' => $ordersCount,
                'total_spent' => $totalSpent,
                'aov' => $aov,
                'first_order_date' => $firstOrder ? $firstOrder['created_at'] : null,
                'last_order_date' => $lastOrder ? $lastOrder['created_at'] : null
            ]
        ]);
    }
}
