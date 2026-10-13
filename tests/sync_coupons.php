<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDbConnection();

// Check if WELCOME100 exists
$stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = 'WELCOME100'");
$stmt->execute();
if (!$stmt->fetchColumn()) {
    $ins = $pdo->prepare("
        INSERT INTO coupons (
            code, title, discount_type, discount_value, min_order_amount, max_discount_amount,
            start_date, expiry_date, usage_limit, per_customer_limit, applicable_categories, first_order_only, cod_allowed, status
        ) VALUES (
            'WELCOME100', 'Welcome Flat ₹100 Off on First Order', 'fixed', 100.00, 799.00, 100.00,
            '2026-01-01', '2026-12-31', 5000, 1, NULL, 1, 1, 'active'
        )
    ");
    $ins->execute();
    echo "Added WELCOME100 to coupons table.\n";
} else {
    echo "WELCOME100 already exists.\n";
}

// Display all coupons
$all = $pdo->query("SELECT code, title, discount_type, discount_value, min_order_amount, first_order_only, status FROM coupons")->fetchAll(PDO::FETCH_ASSOC);
foreach ($all as $c) {
    echo "- {$c['code']}: {$c['title']} ({$c['discount_type']} {$c['discount_value']}, min ₹{$c['min_order_amount']}, first_order: {$c['first_order_only']})\n";
}
