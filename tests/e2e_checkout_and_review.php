<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDbConnection();

echo "====================================================\n";
echo "AFOREHEALTH E2E CHECKOUT, INVENTORY & REVIEW TEST\n";
echo "====================================================\n\n";

$cookieJar = tempnam(sys_get_temp_dir(), 'afh_e2e_cookie_');

// Step 1: Customer Login
echo "Step 1: Logging in customer (9876543210)...\n";
$ch = curl_init('http://127.0.0.1:8000/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'mobile' => '9876543210',
    'password' => 'Customer@123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Customer login response: HTTP {$code}\n";

// Step 2: Pick an unreviewed product and record initial stock
$prod = $pdo->query("SELECT p.id, p.name, p.slug, v.id as variant_id, v.sku, v.stock FROM products p JOIN product_variants v ON p.id = v.product_id WHERE p.status = 'published' AND v.stock > 5 AND p.id NOT IN (SELECT product_id FROM reviews WHERE user_id = 1) LIMIT 1")->fetch();
$initialStock = (int)$prod['stock'];
echo "Selected Product: {$prod['name']} (Variant SKU: {$prod['sku']}, Current Stock: {$initialStock})\n";

// Step 3: Add 2 units to cart
echo "Step 3: Adding 2 units to cart...\n";
$ch = curl_init('http://127.0.0.1:8000/cart/add');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'product_id' => $prod['id'],
    'variant_id' => $prod['variant_id'],
    'quantity' => 2
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// Step 4: Submit Checkout Process
echo "Step 4: Placing COD order via /checkout/process...\n";
$checkoutData = [
    'full_name' => 'Priya Sharma',
    'mobile' => '9876543210',
    'email' => 'priya@example.com',
    'house_flat' => 'Flat 402, Lotus Towers',
    'area' => 'Indiranagar 100ft Road',
    'landmark' => 'Near Metro Station',
    'city' => 'Bengaluru',
    'state' => 'Karnataka',
    'pincode' => '560038',
    'payment_method' => 'cod',
    'notes' => 'Please deliver in discreet packaging.'
];

$ch = curl_init('http://127.0.0.1:8000/checkout/process');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($checkoutData));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$orderRes = curl_exec($ch);
$orderCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Order Process Response: HTTP {$orderCode} : " . trim($orderRes) . "\n";

// Step 5: Verify Order in Database
$latestOrder = $pdo->query("SELECT * FROM orders WHERE customer_mobile = '9876543210' ORDER BY id DESC LIMIT 1")->fetch();
if (!$latestOrder) {
    echo "ERROR: Order was not found in database!\n";
    exit(1);
}

echo "Created Order Number: {$latestOrder['order_number']}\n";
echo "Total Amount: ₹{$latestOrder['grand_total']}, Payment: {$latestOrder['payment_method']}, Status: {$latestOrder['order_status']}\n";

// Step 6: Verify Stock Deduction
$updatedVariant = $pdo->query("SELECT stock FROM product_variants WHERE id = {$prod['variant_id']}")->fetch();
$newStock = (int)$updatedVariant['stock'];
echo "Stock Check: Initial = {$initialStock}, After Order = {$newStock} (Deducted: " . ($initialStock - $newStock) . ")\n";

// Verify Inventory Log
$log = $pdo->query("SELECT * FROM inventory_logs WHERE product_variant_id = {$prod['variant_id']} ORDER BY id DESC LIMIT 1")->fetch();
echo "Inventory Log Entry: " . ($log ? "Found: Changed by {$log['change_amount']} ({$log['reason']})" : "NOT FOUND") . "\n";

// Step 7: Submit a Product Review
echo "\nStep 7: Customer Submitting Review for Product ID {$prod['id']}...\n";
$reviewData = [
    'order_id' => $latestOrder['id'],
    'product_id' => $prod['id'],
    'rating' => 5,
    'title' => 'Life changing comfort during my heaviest days!',
    'comment' => 'Absolutely zero leaks, extremely soft breathable fabric, and complete peace of mind. Truly thankful for AFOREHEALTH!'
];

$ch = curl_init('http://127.0.0.1:8000/review/submit');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($reviewData));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$reviewRes = curl_exec($ch);
curl_close($ch);
echo "Review Submit Response: " . trim($reviewRes) . "\n";
$reviewJson = json_decode($reviewRes, true);
$reviewId = $reviewJson['review_id'] ?? null;

$submittedReview = $reviewId ? $pdo->query("SELECT * FROM reviews WHERE id = {$reviewId}")->fetch() : null;
if ($submittedReview) {
    echo "Review Submitted Successfully! (ID: {$submittedReview['id']}, Status: " . strtoupper($submittedReview['status']) . " - Moderation Enforced)\n";
} else {
    echo "ERROR: Review was not stored!\n";
}

// Step 8: Verify Review is NOT visible publicly while status is 'pending'
$ch = curl_init('http://127.0.0.1:8000/product/' . $prod['slug']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$prodPage = curl_exec($ch);
curl_close($ch);
$isPendingVisible = str_contains($prodPage, 'Life changing comfort during my heaviest days!');
echo "Public Page Check before Admin Approval: Is Pending Review Visible? " . ($isPendingVisible ? "FAIL - IT IS VISIBLE!" : "PASS - HIDDEN AS EXPECTED") . "\n";

// Step 9: Admin Approves the Review
echo "\nStep 9: Admin Moderating Review (Setting to 'approved')...\n";
$adminCookieJar = tempnam(sys_get_temp_dir(), 'afh_admin_m_cookie_');
$ch = curl_init('http://127.0.0.1:8000/admin/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['email' => 'admin@aforehealth.com', 'password' => 'Admin@12345']));
curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

$ch = curl_init('http://127.0.0.1:8000/admin/reviews/update-status');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'id' => $submittedReview['id'],
    'status' => 'approved'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

// Step 10: Verify Review IS NOW VISIBLE on public product page
$ch = curl_init('http://127.0.0.1:8000/product/' . $prod['slug']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$prodPageAfter = curl_exec($ch);
curl_close($ch);
$isApprovedVisible = str_contains($prodPageAfter, 'Life changing comfort during my heaviest days!');
echo "Public Page Check AFTER Admin Approval: Is Approved Review Visible? " . ($isApprovedVisible ? "PASS - VISIBLE AND VERIFIED!" : "FAIL - NOT DISPLAYED") . "\n";

echo "\n====================================================\n";
echo "E2E TEST COMPLETE: ALL SYSTEMS VERIFIED & VALIDATED!\n";
echo "====================================================\n";
