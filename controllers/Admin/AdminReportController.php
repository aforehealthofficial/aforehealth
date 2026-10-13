<?php
namespace Controllers\Admin;

use PDO;

class AdminReportController {
    public function sales(): void {
        $pdo = getDbConnection();
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        $stmt = $pdo->prepare("
            SELECT DATE(created_at) as order_date,
                   COUNT(id) as total_orders,
                   SUM(subtotal) as gross_sales,
                   SUM(discount) as total_discount,
                   SUM(shipping_fee) as total_shipping,
                   SUM(grand_total) as net_sales
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ? AND order_status != 'CANCELLED'
            GROUP BY DATE(created_at)
            ORDER BY order_date DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        $reportData = $stmt->fetchAll();

        // Export CSV check
        if (!empty($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="sales_report_' . $startDate . '_to_' . $endDate . '.csv"');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Date', 'Total Orders', 'Gross Sales (INR)', 'Discount', 'Shipping', 'Net Sales (INR)']);
            foreach ($reportData as $row) {
                fputcsv($output, [$row['order_date'], $row['total_orders'], $row['gross_sales'], $row['total_discount'], $row['total_shipping'], $row['net_sales']]);
            }
            fclose($output);
            exit;
        }

        view('admin.reports.sales', [
            'pageTitle' => 'Sales Analytics Report - AFOREHEALTH Admin',
            'reportData' => $reportData,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function products(): void {
        $pdo = getDbConnection();

        $topProducts = $pdo->query("
            SELECT oi.product_name, oi.sku,
                   SUM(oi.quantity) as total_sold,
                   SUM(oi.total) as total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.order_status != 'CANCELLED'
            GROUP BY oi.product_id, oi.product_name, oi.sku
            ORDER BY total_sold DESC
            LIMIT 25
        ")->fetchAll();

        view('admin.reports.products', [
            'pageTitle' => 'Top Products Report - AFOREHEALTH Admin',
            'topProducts' => $topProducts
        ]);
    }
}
