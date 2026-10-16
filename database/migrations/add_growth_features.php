<?php
require_once __DIR__ . '/../../helpers/env.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDbConnection();

    // 1. Add recovery columns to carts table
    $stmt1 = $pdo->query("SHOW COLUMNS FROM carts LIKE 'customer_mobile'");
    if (!$stmt1->fetch()) {
        $pdo->exec("ALTER TABLE carts 
            ADD COLUMN `customer_name` VARCHAR(150) NULL AFTER `session_id`,
            ADD COLUMN `customer_mobile` VARCHAR(20) NULL AFTER `customer_name`,
            ADD COLUMN `customer_email` VARCHAR(150) NULL AFTER `customer_mobile`,
            ADD COLUMN `recovery_token` VARCHAR(64) NULL AFTER `customer_email`,
            ADD COLUMN `is_recovered` TINYINT(1) DEFAULT 0 AFTER `recovery_token`,
            ADD INDEX `idx_cart_recovery_token` (`recovery_token`),
            ADD INDEX `idx_cart_mobile` (`customer_mobile`)"
        );
        echo "Added recovery columns to carts table.\n";
    } else {
        echo "Carts recovery columns already exist.\n";
    }

    // 2. Add default RECOVER10 coupon if missing
    $stmt2 = $pdo->query("SELECT id FROM coupons WHERE code = 'RECOVER10'");
    if (!$stmt2->fetch()) {
        $pdo->exec("INSERT INTO coupons (
            code, title, discount_type, discount_value, min_order_amount, 
            max_discount_amount, start_date, expiry_date, usage_limit, per_customer_limit, status
        ) VALUES (
            'RECOVER10', 'Exclusive Cart Recovery Offer - Extra 10% OFF', 'percentage', 10.00, 399.00,
            200.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), 10000, 1, 'active'
        )");
        echo "Created RECOVER10 coupon for abandoned cart recoveries.\n";
    }

    echo "Growth features migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
