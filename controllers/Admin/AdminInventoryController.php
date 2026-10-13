<?php
namespace Controllers\Admin;

use PDO;

class AdminInventoryController {
    public function index(): void {
        $pdo = getDbConnection();

        $items = $pdo->query("
            SELECT p.id as product_id, p.name as product_name, p.sku, p.stock, p.low_stock_threshold,
                   c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.stock ASC
        ")->fetchAll();

        // Logs
        $logs = $pdo->query("
            SELECT il.*, p.name as product_name
            FROM inventory_logs il
            JOIN products p ON il.product_id = p.id
            ORDER BY il.created_at DESC
            LIMIT 50
        ")->fetchAll();

        view('admin.inventory.index', [
            'pageTitle' => 'Inventory & Stock Movements - AFOREHEALTH Admin',
            'items' => $items,
            'logs' => $logs
        ]);
    }

    public function adjustStock(): void {
        $productId = (int)($_POST['product_id'] ?? 0);
        $change = (int)($_POST['change_amount'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Manual stock adjustment');

        if (!$productId || $change === 0) {
            set_flash('danger', 'Invalid adjustment parameters.');
            redirect('/admin/inventory');
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT sku, stock FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $prod = $stmt->fetch();

        if ($prod) {
            $curStock = (int)$prod['stock'];
            $newStock = max(0, $curStock + $change);

            $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")->execute([$newStock, $productId]);

            $logStmt = $pdo->prepare("
                INSERT INTO inventory_logs (product_id, sku, previous_stock, change_amount, new_stock, reason, admin_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $logStmt->execute([
                $productId, $prod['sku'], $curStock, $change, $newStock, $reason, auth_admin()['id'] ?? 1
            ]);

            set_flash('success', "Stock updated for SKU {$prod['sku']} from {$curStock} to {$newStock}.");
        }

        redirect('/admin/inventory');
    }
}
