<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDbConnection();
$coupons = $pdo->query("SELECT id, code, title, discount_type, discount_value, min_order_amount, max_discount_amount, first_order_only, status FROM coupons")->fetchAll(PDO::FETCH_ASSOC);
echo "=== CURRENT COUPONS IN DB ===\n";
foreach ($coupons as $c) {
    echo "{$c['code']} | {$c['title']} | {$c['discount_type']} | val:{$c['discount_value']} | min:{$c['min_order_amount']} | max:{$c['max_discount_amount']} | first_order:{$c['first_order_only']} | status:{$c['status']}\n";
}

$settings = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE '%first_order%' OR setting_key LIKE '%coupon%'")->fetchAll(PDO::FETCH_ASSOC);
echo "\n=== SETTINGS IN DB ===\n";
foreach ($settings as $s) {
    echo "{$s['setting_key']} = {$s['setting_value']}\n";
}
