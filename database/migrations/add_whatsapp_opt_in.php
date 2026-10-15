<?php
require_once __DIR__ . '/../../helpers/env.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDbConnection();
    
    // Check if column exists in orders
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'whatsapp_opt_in'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN `whatsapp_opt_in` TINYINT(1) DEFAULT 0 AFTER `payment_method`");
        echo "Added whatsapp_opt_in column to orders table.\n";
    } else {
        echo "whatsapp_opt_in already exists in orders table.\n";
    }

    // Check if column exists in users
    $stmt2 = $pdo->query("SHOW COLUMNS FROM users LIKE 'whatsapp_opt_in'");
    if (!$stmt2->fetch()) {
        $pdo->exec("ALTER TABLE users ADD COLUMN `whatsapp_opt_in` TINYINT(1) DEFAULT 0 AFTER `status`");
        echo "Added whatsapp_opt_in column to users table.\n";
    } else {
        echo "whatsapp_opt_in already exists in users table.\n";
    }

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
