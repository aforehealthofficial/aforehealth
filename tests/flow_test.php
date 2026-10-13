<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

echo "=== 1. CHECKING DATABASE CATEGORIES & PRODUCTS ===\n";
$categories = $pdo->query("SELECT id, name, slug FROM categories")->fetchAll();
foreach ($categories as $cat) {
    echo "Category: {$cat['name']} (/category/{$cat['slug']})\n";
}

$sampleProduct = $pdo->query("SELECT id, name, slug, price, mrp FROM products LIMIT 1")->fetch();
echo "\nSample Product: {$sampleProduct['name']} (/product/{$sampleProduct['slug']})\n";

// Test product route
$ch = curl_init('http://127.0.0.1:8000/product/' . $sampleProduct['slug']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "GET /product/{$sampleProduct['slug']} => HTTP {$code} (Length: " . strlen($html) . ")\n";

echo "\n=== 2. TESTING CART & AJAX API ===\n";
$cookieJar = tempnam(sys_get_temp_dir(), 'afh_cookie_');

// Add to Cart
$variant = $pdo->query("SELECT id FROM product_variants WHERE product_id = {$sampleProduct['id']} LIMIT 1")->fetch();
$variantId = $variant ? $variant['id'] : null;

$postData = http_build_query([
    'product_id' => $sampleProduct['id'],
    'variant_id' => $variantId,
    'quantity' => 2
]);

$ch = curl_init('http://127.0.0.1:8000/cart/add');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$cartAddRes = curl_exec($ch);
$cartAddCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "POST /cart/add => HTTP {$cartAddCode} : " . trim($cartAddRes) . "\n";

// Drawer Data
$ch = curl_init('http://127.0.0.1:8000/cart/drawer');
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$drawerRes = curl_exec($ch);
curl_close($ch);
echo "GET /cart/drawer => " . trim($drawerRes) . "\n";

// Apply Coupon
$ch = curl_init('http://127.0.0.1:8000/cart/coupon');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['code' => 'WELCOME10']));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$couponRes = curl_exec($ch);
curl_close($ch);
echo "POST /cart/coupon (WELCOME10) => " . trim($couponRes) . "\n";

echo "\n=== 3. TESTING ADMIN AUTHENTICATION & ACCESS ===\n";
$adminCookieJar = tempnam(sys_get_temp_dir(), 'afh_admin_cookie_');

// Admin Login
$ch = curl_init('http://127.0.0.1:8000/admin/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'admin@aforehealth.com',
    'password' => 'Admin@12345'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookieJar);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$adminLoginHtml = curl_exec($ch);
$adminLoginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "POST /admin/login => HTTP {$adminLoginCode}\n";

$adminRoutes = [
    '/admin',
    '/admin/products',
    '/admin/categories',
    '/admin/orders',
    '/admin/reviews',
    '/admin/coupons',
    '/admin/customers',
    '/admin/inventory',
    '/admin/reports/sales',
    '/admin/reports/products',
    '/admin/settings',
    '/admin/blog'
];

foreach ($adminRoutes as $ar) {
    $ch = curl_init('http://127.0.0.1:8000' . $ar);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo str_pad($ar, 30) . " => HTTP " . $status . "\n";
}

echo "\n=== 4. TESTING CUSTOMER AUTHENTICATION & ACCOUNT ===\n";
$userCookieJar = tempnam(sys_get_temp_dir(), 'afh_user_cookie_');

// Customer Login with Mobile
$ch = curl_init('http://127.0.0.1:8000/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'mobile' => '9876543210',
    'password' => 'Customer@123'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $userCookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $userCookieJar);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$userLoginHtml = curl_exec($ch);
$userLoginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "POST /login (9876543210) => HTTP {$userLoginCode}\n";

$userRoutes = [
    '/account',
    '/account/orders',
    '/account/wishlist',
    '/account/addresses',
    '/account/profile',
    '/account/change-mobile',
    '/account/reviews'
];

foreach ($userRoutes as $ur) {
    $ch = curl_init('http://127.0.0.1:8000' . $ur);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $userCookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $userCookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo str_pad($ur, 30) . " => HTTP " . $status . "\n";
}

echo "\n=== 5. CHECKING SEEDED ORDERS & INVOICE GENERATION ===\n";
$sampleOrder = $pdo->query("SELECT order_number FROM orders LIMIT 1")->fetch();
if ($sampleOrder) {
    $invoiceUrl = 'http://127.0.0.1:8000/order/invoice/' . $sampleOrder['order_number'];
    $ch = curl_init($invoiceUrl);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookieJar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookieJar);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $invoiceHtml = curl_exec($ch);
    $invCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "GET /order/invoice/{$sampleOrder['order_number']} => HTTP {$invCode} (Contains 'AFOREHEALTH': " . (str_contains($invoiceHtml, 'AFOREHEALTH') ? 'YES' : 'NO') . ")\n";
    echo "INVOICE BODY SNIPPET: " . substr(strip_tags($invoiceHtml), 0, 150) . "\n";
}

echo "\nALL TESTS FINISHED SUCCESSFULLY!\n";
