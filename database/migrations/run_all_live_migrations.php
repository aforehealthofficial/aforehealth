<?php
/**
 * Safe All-in-One Live Migration Runner
 * Safe to run multiple times without duplicate column errors
 */
require_once __DIR__ . '/../../helpers/env.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDbConnection();
    echo "=== Starting Database Migrations ===\n\n";

    // Helper to safely add column if not exists
    $safeAddColumn = function($table, $column, $definition) use ($pdo) {
        $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
            echo "✔ Added `{$column}` to `{$table}`.\n";
        } else {
            echo "ℹ `{$column}` already exists in `{$table}` (Skipped).\n";
        }
    };

    // 1. Products table
    $safeAddColumn('products', 'video_url', 'VARCHAR(500) NULL');
    $safeAddColumn('products', 'video_file', 'VARCHAR(255) NULL');

    // 2. Banners table
    $safeAddColumn('banners', 'video_url', 'VARCHAR(500) NULL');

    // 3. Blog posts table
    $safeAddColumn('blog_posts', 'video_url', 'VARCHAR(500) NULL');

    // 4. Carts table
    $safeAddColumn('carts', 'customer_name', 'VARCHAR(150) NULL');
    $safeAddColumn('carts', 'customer_mobile', 'VARCHAR(20) NULL');
    $safeAddColumn('carts', 'customer_email', 'VARCHAR(150) NULL');
    $safeAddColumn('carts', 'recovery_token', 'VARCHAR(64) NULL');
    $safeAddColumn('carts', 'is_recovered', 'TINYINT(1) DEFAULT 0');

    // Carts index
    $idxStmt = $pdo->query("SHOW INDEX FROM `carts` WHERE Key_name = 'idx_recovery_token'");
    if (!$idxStmt->fetch()) {
        $pdo->exec("ALTER TABLE `carts` ADD INDEX `idx_recovery_token` (`recovery_token`)");
        echo "✔ Added index `idx_recovery_token` to `carts`.\n";
    } else {
        echo "ℹ Index `idx_recovery_token` already exists (Skipped).\n";
    }

    // 5. Coupons table (RECOVER10)
    $couponStmt = $pdo->query("SELECT id FROM coupons WHERE code = 'RECOVER10'");
    if (!$couponStmt->fetch()) {
        $pdo->exec("
            INSERT INTO coupons (code, title, discount_type, discount_value, min_order_amount, max_discount_amount, start_date, expiry_date, usage_limit, usage_count, per_customer_limit, cod_allowed, status, created_at)
            VALUES ('RECOVER10', 'Exclusive Cart Recovery Offer - Extra 10% OFF', 'percentage', 10.00, 399.00, 200.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 10000, 0, 1, 1, 'active', NOW())
        ");
        echo "✔ Inserted `RECOVER10` recovery coupon.\n";
    } else {
        echo "ℹ Coupon `RECOVER10` already exists (Skipped).\n";
    }

    // 6. Users table
    $safeAddColumn('users', 'two_factor_code', 'VARCHAR(10) NULL');
    $safeAddColumn('users', 'two_factor_expires_at', 'DATETIME NULL');

    echo "\n✔ All database updates completed successfully!\n";
} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
