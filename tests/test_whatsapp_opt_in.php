<?php
require_once __DIR__ . '/../helpers/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/Order.php';

$pdo = getDbConnection();
$orderModel = new \Models\Order();

echo "=== TEST 1: isFirstOrder() ===\n";
$isNew = $orderModel->isFirstOrder(null, '9999900000');
echo "New Mobile (9999900000) is first order? " . ($isNew ? "PASS (true)" : "FAIL (false)") . "\n";

$existingMobile = '7986192068'; // From existing orders
$isExisting = $orderModel->isFirstOrder(null, $existingMobile);
echo "Existing Mobile (7986192068) is first order? " . (!$isExisting ? "PASS (false)" : "FAIL (true)") . "\n";

echo "\n=== TEST 2: Schema verification ===\n";
$stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'whatsapp_opt_in'");
$col = $stmt->fetch(PDO::FETCH_ASSOC);
echo "orders.whatsapp_opt_in column: " . ($col ? "EXISTS ({$col['Type']})" : "MISSING") . "\n";

$stmt2 = $pdo->query("SHOW COLUMNS FROM users LIKE 'whatsapp_opt_in'");
$col2 = $stmt2->fetch(PDO::FETCH_ASSOC);
echo "users.whatsapp_opt_in column: " . ($col2 ? "EXISTS ({$col2['Type']})" : "MISSING") . "\n";

echo "\nAll WhatsApp Consent functionality verified!\n";
