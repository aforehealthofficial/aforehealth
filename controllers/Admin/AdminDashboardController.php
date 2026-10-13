<?php
namespace Controllers\Admin;

use PDO;

class AdminDashboardController {
    public function index(): void {
        $pdo = getDbConnection();

        // 1. Stats
        $totalSales = (float)$pdo->query("SELECT SUM(grand_total) FROM orders WHERE payment_status = 'paid' OR order_status NOT IN ('CANCELLED', 'FAILED')")->fetchColumn();
        $todaySales = (float)$pdo->query("SELECT SUM(grand_total) FROM orders WHERE (payment_status = 'paid' OR order_status NOT IN ('CANCELLED', 'FAILED')) AND DATE(created_at) = CURDATE()")->fetchColumn();
        $totalOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status NOT IN ('CANCELLED', 'FAILED')")->fetchColumn();
        $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('PENDING', 'PROCESSING', 'CONFIRMED')")->fetchColumn();
        $totalCustomers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $totalProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE status = 'published'")->fetchColumn();
        $lowStockCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE stock <= low_stock_threshold")->fetchColumn();
        $pendingReviews = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
        $pendingReturns = (int)$pdo->query("SELECT COUNT(*) FROM returns WHERE status = 'pending'")->fetchColumn();
        $pendingQuestions = (int)$pdo->query("SELECT COUNT(*) FROM product_questions WHERE status = 'pending'")->fetchColumn();

        // AOV & Repeat Customers Rate
        $aov = $totalOrders > 0 ? round($totalSales / $totalOrders, 2) : 0;
        $repeatCount = (int)$pdo->query("SELECT COUNT(*) FROM users u WHERE (SELECT COUNT(*) FROM orders WHERE user_id = u.id AND order_status != 'CANCELLED') >= 2")->fetchColumn();
        $repeatCustomerPct = $totalCustomers > 0 ? round(($repeatCount / $totalCustomers) * 100, 1) : 0;

        // 2. Recent Orders
        $recentOrders = $pdo->query("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
            FROM orders o
            ORDER BY o.created_at DESC
            LIMIT 8
        ")->fetchAll();

        // 3. Low stock alerts
        $lowStockProducts = $pdo->query("
            SELECT id, name, sku, stock, low_stock_threshold, primary_image
            FROM products
            WHERE stock <= low_stock_threshold
            ORDER BY stock ASC
            LIMIT 5
        ")->fetchAll();

        // 4. Sales & Orders Chart Data (Last 7 days)
        $chartDates = [];
        $chartSales = [];
        $chartOrders = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $chartDates[] = date('d M', strtotime($date));
            
            $stmt = $pdo->prepare("SELECT SUM(grand_total) FROM orders WHERE DATE(created_at) = ? AND order_status NOT IN ('CANCELLED', 'FAILED')");
            $stmt->execute([$date]);
            $chartSales[] = (float)($stmt->fetchColumn() ?: 0);

            $stmtO = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = ? AND order_status NOT IN ('CANCELLED', 'FAILED')");
            $stmtO->execute([$date]);
            $chartOrders[] = (int)($stmtO->fetchColumn() ?: 0);
        }

        // 5. Category distribution
        $catRevenue = $pdo->query("
            SELECT c.name, COUNT(p.id) as count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id, c.name
            ORDER BY count DESC
        ")->fetchAll();

        // 6. Payment method distribution
        $paymentMethods = $pdo->query("
            SELECT payment_method, COUNT(*) as count, SUM(grand_total) as revenue
            FROM orders
            WHERE order_status != 'CANCELLED'
            GROUP BY payment_method
        ")->fetchAll();

        // 7. Top Bestselling products
        $topProducts = $pdo->query("
            SELECT oi.product_name, SUM(oi.quantity) as sold, SUM(oi.total) as revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.order_status != 'CANCELLED'
            GROUP BY oi.product_id, oi.product_name
            ORDER BY sold DESC
            LIMIT 5
        ")->fetchAll();

        view('admin.dashboard', [
            'pageTitle' => 'Admin Dashboard - AFOREHEALTH',
            'stats' => [
                'total_sales' => $totalSales,
                'today_sales' => $todaySales,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'total_customers' => $totalCustomers,
                'total_products' => $totalProducts,
                'low_stock' => $lowStockCount,
                'pending_reviews' => $pendingReviews,
                'pending_returns' => $pendingReturns,
                'pending_questions' => $pendingQuestions,
                'aov' => $aov,
                'repeat_customer_pct' => $repeatCustomerPct
            ],
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
            'topProducts' => $topProducts,
            'paymentMethods' => $paymentMethods,
            'chartDates' => json_encode($chartDates),
            'chartSales' => json_encode($chartSales),
            'chartOrders' => json_encode($chartOrders),
            'catRevenue' => $catRevenue
        ]);
    }
}
