<?php
/**
 * AFOREHEALTH - Production Data Cleanup & Safety Audit Tool
 * 
 * Safely identifies production-unsafe test records without deleting real production data.
 * Run in CLI: php tests/cleanup_production_data.php [--dry-run|--execute]
 */

date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';

$isExecute = in_array('--execute', $argv ?? []);

echo "=========================================================\n";
echo "   AFOREHEALTH — PRE-PRODUCTION TEST DATA SCANNER        \n";
echo "=========================================================\n";
echo "Mode: " . ($isExecute ? "EXECUTE (Permanent Clean)" : "DRY RUN / AUDIT ONLY (Safe Scan)") . "\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

$pdo = getDbConnection();
$findings = [];
$totalTestRecords = 0;

// 1. Identify Demo Customers
$testUsersStmt = $pdo->query("
    SELECT id, name, email, mobile, status, created_at 
    FROM users 
    WHERE email LIKE '%test%' 
       OR email LIKE '%example%' 
       OR email LIKE '%dummy%'
       OR mobile IN ('9999999999', '9876543210', '9888888888', '9123456789')
       OR name LIKE '%Test%'
       OR name LIKE '%Demo%'
    ORDER BY id ASC
");
$testUsers = $testUsersStmt->fetchAll();
$testUserIds = array_column($testUsers, 'id');
$findings['test_customers'] = [
    'count' => count($testUsers),
    'records' => $testUsers,
    'description' => 'Users with test email domains, placeholder phone numbers, or test names'
];
$totalTestRecords += count($testUsers);

// 2. Identify Demo Orders
$testOrders = [];
if (!empty($testUserIds)) {
    $placeholders = implode(',', array_fill(0, count($testUserIds), '?'));
    $testOrdersStmt = $pdo->prepare("
        SELECT id, order_number, user_id, grand_total, payment_method, payment_status, created_at 
        FROM orders 
        WHERE user_id IN ($placeholders)
           OR order_number LIKE 'TEST%'
           OR customer_email LIKE '%test%'
           OR customer_email LIKE '%example%'
           OR customer_mobile IN ('9999999999', '9876543210')
        ORDER BY id DESC
    ");
    $testOrdersStmt->execute($testUserIds);
    $testOrders = $testOrdersStmt->fetchAll();
} else {
    $testOrdersStmt = $pdo->query("
        SELECT id, order_number, user_id, grand_total, payment_method, payment_status, created_at 
        FROM orders 
        WHERE order_number LIKE 'TEST%'
           OR customer_email LIKE '%test%'
           OR customer_email LIKE '%example%'
           OR customer_mobile IN ('9999999999', '9876543210')
        ORDER BY id DESC
    ");
    $testOrders = $testOrdersStmt->fetchAll();
}
$testOrderIds = array_column($testOrders, 'id');
$findings['test_orders'] = [
    'count' => count($testOrders),
    'records' => $testOrders,
    'description' => 'Orders generated during automated tests or with test customer accounts'
];
$totalTestRecords += count($testOrders);

// 3. Identify Demo Reviews
$testReviewsStmt = $pdo->query("
    SELECT id, product_id, user_id, rating, title, comment, status, created_at 
    FROM reviews 
    WHERE title LIKE '%Test%' 
       OR title LIKE '%Demo%'
       OR comment LIKE '%test review%'
       OR comment LIKE '%Testing automated%'
    ORDER BY id DESC
");
$testReviews = $testReviewsStmt->fetchAll();
$findings['test_reviews'] = [
    'count' => count($testReviews),
    'records' => $testReviews,
    'description' => 'Reviews submitted during test runs or containing dummy feedback'
];
$totalTestRecords += count($testReviews);

// 4. Identify Demo / Temporary Coupons
$testCouponsStmt = $pdo->query("
    SELECT id, code, title, discount_type, discount_value, status, created_at 
    FROM coupons 
    WHERE code LIKE 'TEST%' 
       OR code LIKE 'DEMO%' 
       OR code LIKE 'TMP%'
    ORDER BY id DESC
");
$testCoupons = $testCouponsStmt->fetchAll();
$findings['test_coupons'] = [
    'count' => count($testCoupons),
    'records' => $testCoupons,
    'description' => 'Coupons created specifically for test automation or debugging'
];
$totalTestRecords += count($testCoupons);

// 5. Identify Test Loyalty Points
$testLoyalty = [];
if (!empty($testUserIds)) {
    $placeholders = implode(',', array_fill(0, count($testUserIds), '?'));
    $testLoyaltyStmt = $pdo->prepare("
        SELECT id, user_id, points, transaction_type, description, created_at 
        FROM loyalty_transactions 
        WHERE user_id IN ($placeholders)
        ORDER BY id DESC
    ");
    $testLoyaltyStmt->execute($testUserIds);
    $testLoyalty = $testLoyaltyStmt->fetchAll();
}
$findings['test_loyalty_transactions'] = [
    'count' => count($testLoyalty),
    'records' => $testLoyalty,
    'description' => 'Loyalty transactions associated with dummy customer accounts'
];
$totalTestRecords += count($testLoyalty);

// 6. Identify Test Referral Logs
$testReferrals = [];
if (!empty($testUserIds)) {
    $placeholders = implode(',', array_fill(0, count($testUserIds), '?'));
    $testRefStmt = $pdo->prepare("
        SELECT id, referrer_user_id, referred_user_id, status, created_at 
        FROM referrals 
        WHERE referrer_user_id IN ($placeholders) OR referred_user_id IN ($placeholders)
        ORDER BY id DESC
    ");
    $testRefStmt->execute(array_merge($testUserIds, $testUserIds));
    $testReferrals = $testRefStmt->fetchAll();
}
$findings['test_referrals'] = [
    'count' => count($testReferrals),
    'records' => $testReferrals,
    'description' => 'Referral links created by or attributed to test user IDs'
];
$totalTestRecords += count($testReferrals);

// 7. Identify Test Notifications
$testNotifStmt = $pdo->query("
    SELECT id, title, type, created_at 
    FROM notifications 
    WHERE title LIKE '%Test%' 
       OR message LIKE '%Test%'
    ORDER BY id DESC
");
$testNotifs = $testNotifStmt->fetchAll();
$findings['test_notifications'] = [
    'count' => count($testNotifs),
    'records' => $testNotifs,
    'description' => 'Notifications triggered during developer or test runs'
];
$totalTestRecords += count($testNotifs);

// Display Audit Summary
echo "AUDIT SUMMARY OF DETECTED TEST RECORDS:\n";
echo "---------------------------------------------------------\n";
foreach ($findings as $key => $info) {
    printf("%-26s : %4d records (%s)\n", strtoupper($key), $info['count'], $info['description']);
}
echo "---------------------------------------------------------\n";
echo "TOTAL TEST RECORDS IDENTIFIED : " . $totalTestRecords . "\n\n";

if ($totalTestRecords === 0) {
    echo "No test/demo records detected. Database is clean!\n";
    exit(0);
}

// Show detailed table of test users
if (!empty($testUsers)) {
    echo "DETAILED TEST USERS FOUND:\n";
    foreach ($testUsers as $u) {
        echo "  [ID: {$u['id']}] {$u['name']} | {$u['email']} | {$u['mobile']} | Status: {$u['status']}\n";
    }
    echo "\n";
}

// If --execute was specified, prompt or clean
if ($isExecute) {
    echo "ATTENTION: Executing permanent cleanup of test records...\n";
    $pdo->beginTransaction();
    try {
        // Delete dependent test records first
        if (!empty($testOrderIds)) {
            $orderPlaceholders = implode(',', array_fill(0, count($testOrderIds), '?'));
            $pdo->prepare("DELETE FROM order_items WHERE order_id IN ($orderPlaceholders)")->execute($testOrderIds);
            $pdo->prepare("DELETE FROM order_status_history WHERE order_id IN ($orderPlaceholders)")->execute($testOrderIds);
            $pdo->prepare("DELETE FROM orders WHERE id IN ($orderPlaceholders)")->execute($testOrderIds);
            echo "✔ Cleaned " . count($testOrderIds) . " test orders and order items.\n";
        }

        if (!empty($testReviews)) {
            $revIds = array_column($testReviews, 'id');
            $revPlaceholders = implode(',', array_fill(0, count($revIds), '?'));
            $pdo->prepare("DELETE FROM reviews WHERE id IN ($revPlaceholders)")->execute($revIds);
            echo "✔ Cleaned " . count($revIds) . " test reviews.\n";
        }

        if (!empty($testCoupons)) {
            $cpIds = array_column($testCoupons, 'id');
            $cpPlaceholders = implode(',', array_fill(0, count($cpIds), '?'));
            $pdo->prepare("DELETE FROM coupons WHERE id IN ($cpPlaceholders)")->execute($cpIds);
            echo "✔ Cleaned " . count($cpIds) . " test coupons.\n";
        }

        if (!empty($testLoyalty)) {
            $loyIds = array_column($testLoyalty, 'id');
            $loyPlaceholders = implode(',', array_fill(0, count($loyIds), '?'));
            $pdo->prepare("DELETE FROM loyalty_transactions WHERE id IN ($loyPlaceholders)")->execute($loyIds);
            echo "✔ Cleaned " . count($loyIds) . " test loyalty entries.\n";
        }

        if (!empty($testNotifs)) {
            $notifIds = array_column($testNotifs, 'id');
            $notifPlaceholders = implode(',', array_fill(0, count($notifIds), '?'));
            $pdo->prepare("DELETE FROM notifications WHERE id IN ($notifPlaceholders)")->execute($notifIds);
            echo "✔ Cleaned " . count($notifIds) . " test notifications.\n";
        }

        if (!empty($testUserIds)) {
            // Delete customer cart, wishlist, addresses
            $userPlaceholders = implode(',', array_fill(0, count($testUserIds), '?'));
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id IN (SELECT id FROM carts WHERE user_id IN ($userPlaceholders))")->execute($testUserIds);
            $pdo->prepare("DELETE FROM carts WHERE user_id IN ($userPlaceholders)")->execute($testUserIds);
            $pdo->prepare("DELETE FROM wishlist_items WHERE wishlist_id IN (SELECT id FROM wishlists WHERE user_id IN ($userPlaceholders))")->execute($testUserIds);
            $pdo->prepare("DELETE FROM wishlists WHERE user_id IN ($userPlaceholders)")->execute($testUserIds);
            $pdo->prepare("DELETE FROM addresses WHERE user_id IN ($userPlaceholders)")->execute($testUserIds);
            $pdo->prepare("DELETE FROM users WHERE id IN ($userPlaceholders)")->execute($testUserIds);
            echo "✔ Cleaned " . count($testUserIds) . " test users and related records.\n";
        }

        $pdo->commit();
        echo "\n Cleanup successfully completed.\n";
    } catch (\Throwable $e) {
        $pdo->rollBack();
        echo "\n Cleanup failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "ℹ SAFETY NOTICE: This script ran in DRY-RUN audit mode.\n";
    echo "No records were modified or deleted.\n";
    echo "To perform cleanup safely in staging/pre-prod, execute: php tests/cleanup_production_data.php --execute\n";
}
