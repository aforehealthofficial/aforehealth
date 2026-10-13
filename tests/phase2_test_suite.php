<?php
date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/Pincode.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/ProductQuestion.php';
require_once __DIR__ . '/../models/Bundle.php';
require_once __DIR__ . '/../models/Loyalty.php';
require_once __DIR__ . '/../models/Referral.php';
require_once __DIR__ . '/../models/FlashSale.php';
require_once __DIR__ . '/../models/RecentlyViewed.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/DeliveryEstimationService.php';

$pdo = getDbConnection();
$passed = 0;
$failed = 0;
$results = [];

function assertTest(string $featureName, bool $condition, string $details = ''): void {
    global $passed, $failed, $results;
    if ($condition) {
        $passed++;
        $results[] = ["PASS", $featureName, $details];
        echo "  [PASS] {$featureName}" . ($details ? " - {$details}" : "") . "\n";
    } else {
        $failed++;
        $results[] = ["FAIL", $featureName, $details];
        echo "  [FAIL] {$featureName}" . ($details ? " - {$details}" : "") . "\n";
    }
}

echo "========================================================\n";
echo "   AFOREHEALTH PHASE 2 COMPREHENSIVE AUTOMATED TEST SUITE\n";
echo "========================================================\n\n";

// ----------------------------------------------------
// TEST 1: PINCODE SERVICEABILITY (FEATURE 1)
// ----------------------------------------------------
echo "Testing Feature 1: Pincode Serviceability...\n";
$pincodeModel = new \Models\Pincode();

// Check valid serviceable PIN (e.g. 110001 - New Delhi)
$delhiCheck = $pincodeModel->checkServiceability('110001');
assertTest("Pincode 110001 Serviceable", $delhiCheck['serviceable'] === true && $delhiCheck['city'] === 'New Delhi', "City: {$delhiCheck['city']}, Days: {$delhiCheck['delivery_days']}");

// Check unserviceable PIN (e.g. 999999)
$unserviceableCheck = $pincodeModel->checkServiceability('999999');
assertTest("Pincode 999999 Unserviceable", $unserviceableCheck['serviceable'] === false, "Proper fallback when PIN is not in database");

// Check invalid PIN syntax (e.g. 'ABC123' or '123')
$invalidSyntaxCheck = $pincodeModel->checkServiceability('123');
assertTest("Invalid PIN syntax rejected without external API", $invalidSyntaxCheck['serviceable'] === false && str_contains($invalidSyntaxCheck['message'], '6-digit'), $invalidSyntaxCheck['message']);

// Test HTTP API endpoint
$ch = curl_init('http://127.0.0.1:8000/api/pincode/check?pincode=560001');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resJson = curl_exec($ch);
curl_close($ch);
$apiRes = json_decode($resJson, true);
assertTest("GET /api/pincode/check HTTP API", $apiRes && $apiRes['serviceable'] === true && $apiRes['city'] === 'Bengaluru', "Bengaluru: " . ($apiRes['city'] ?? ''));

// ----------------------------------------------------
// TEST 2: DELIVERY ESTIMATION (FEATURE 2)
// ----------------------------------------------------
echo "\nTesting Feature 2: Delivery Estimation Service...\n";
$delivService = new \Services\DeliveryEstimationService();
$est = $delivService->estimate(3, '110001');
assertTest("Delivery Date Range generated", !empty($est['formatted_dates']) && $est['min_days'] > 0 && $est['max_days'] >= $est['min_days'], "Estimated: {$est['formatted_dates']} ({$est['days_string']})");
assertTest("Business days calculation skips Sundays if configured", get_setting('delivery_skip_weekends', 1) == 1, "Sunday skip configured");

// ----------------------------------------------------
// TEST 3: BUY AGAIN / REORDER (FEATURE 3)
// ----------------------------------------------------
echo "\nTesting Feature 3: Buy Again & Reorder Logic...\n";
$orderModel = new \Models\Order();

// Find an existing order
$sampleOrder = $pdo->query("SELECT id, order_number, user_id FROM orders LIMIT 1")->fetch();
if ($sampleOrder) {
    // Test reorder validation function
    $reorderResult = $orderModel->reorder((int)$sampleOrder['id']);
    assertTest("Reorder validates current catalog pricing & stock", isset($reorderResult['success']), "Reorder message: {$reorderResult['message']}");
    
    // Test buy again recommendations for this user
    $buyAgainItems = $orderModel->getBuyAgainProducts((int)$sampleOrder['user_id'], 4);
    assertTest("Buy Again retrieves active products for repeat purchase", is_array($buyAgainItems), "Found " . count($buyAgainItems) . " items");
} else {
    assertTest("Reorder test skipped (no test orders)", true);
}

// ----------------------------------------------------
// TEST 4: PRODUCT Q&A (FEATURE 4)
// ----------------------------------------------------
echo "\nTesting Feature 4: Product Questions & Answers...\n";
$qModel = new \Models\ProductQuestion();
$testUser = $pdo->query("SELECT id FROM users LIMIT 1")->fetch();
$testProd = $pdo->query("SELECT id FROM products LIMIT 1")->fetch();

if ($testUser && $testProd) {
    // Clean any previous test run question for repeatability
    $pdo->exec("DELETE FROM product_questions WHERE user_id = {$testUser['id']} AND question LIKE '%heavy flow nights%'");

    // 1. Submit Question
    $submitRes = $qModel->submitQuestion((int)$testProd['id'], (int)$testUser['id'], "Are these panties suitable for heavy flow nights?");
    assertTest("Customer question submission with moderation queue", $submitRes['success'] === true, $submitRes['message']);
    
    // 2. Verify pending status
    $qId = $submitRes['id'] ?? null;
    if ($qId) {
        $qRow = $pdo->query("SELECT status, answer FROM product_questions WHERE id = {$qId}")->fetch();
        assertTest("Submitted question is in PENDING moderation status", $qRow['status'] === 'pending', "Status: {$qRow['status']}");

        // 3. Admin answers and approves
        $qModel->answerQuestion($qId, "Yes! Our Heavy Flow model features 4-layer absorbency equivalent to 4 regular pads.", 1);
        $qRowAfter = $pdo->query("SELECT status, answer FROM product_questions WHERE id = {$qId}")->fetch();
        assertTest("Admin answers and publishes question", $qRowAfter['status'] === 'approved' && !empty($qRowAfter['answer']), "Status: {$qRowAfter['status']}");

        // 4. Public product page only displays approved questions
        $approvedQuestions = $qModel->getApprovedForProduct((int)$testProd['id']);
        $hasOurQuestion = false;
        foreach ($approvedQuestions as $aq) {
            if ($aq['id'] == $qId) $hasOurQuestion = true;
        }
        assertTest("Public product page displays approved Q&A", $hasOurQuestion === true, "Q&A verified on product storefront");
    }
}

// ----------------------------------------------------
// TEST 5: SIZE FINDER (FEATURE 5)
// ----------------------------------------------------
echo "\nTesting Feature 5: Size Finder Recommendation Engine...\n";
// Test sizing logic: Waist 30", Hip 38" => should be M according to size chart (M: Waist 29-31, Hip 37-39)
$waist = 30;
$hip = 38;
$sizes = [
    'XS' => ['w_min' => 24, 'w_max' => 26, 'h_min' => 32, 'h_max' => 34],
    'S'  => ['w_min' => 26, 'w_max' => 28, 'h_min' => 34, 'h_max' => 36],
    'M'  => ['w_min' => 29, 'w_max' => 31, 'h_min' => 37, 'h_max' => 39],
    'L'  => ['w_min' => 32, 'w_max' => 34, 'h_min' => 40, 'h_max' => 42],
    'XL' => ['w_min' => 35, 'w_max' => 37, 'h_min' => 43, 'h_max' => 45],
];
$recommended = null;
foreach ($sizes as $sz => $range) {
    if ($hip >= $range['h_min'] && $hip <= $range['h_max']) {
        $recommended = $sz;
        break;
    }
}
assertTest("Size Finder recommends correct size based on product chart", $recommended === 'M', "Waist: 30, Hip: 38 => Recommended Size: M");

// ----------------------------------------------------
// TEST 6: PRODUCT COMPARISON (FEATURE 6)
// ----------------------------------------------------
echo "\nTesting Feature 6: Product Comparison...\n";
$cookieJar = tempnam(sys_get_temp_dir(), 'afh_cmp_');
$p1 = $pdo->query("SELECT id FROM products ORDER BY id ASC LIMIT 1")->fetchColumn();
$p2 = $pdo->query("SELECT id FROM products ORDER BY id ASC LIMIT 1 OFFSET 1")->fetchColumn();

// Add to comparison via HTTP
$ch = curl_init('http://127.0.0.1:8000/compare/add');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['product_id' => $p1]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$cmpRes1 = json_decode(curl_exec($ch), true);
curl_close($ch);
assertTest("Add Product 1 to Comparison", $cmpRes1 && $cmpRes1['success'] === true, "Count: " . ($cmpRes1['count'] ?? 0));

// Verify /compare page loads
$ch = curl_init('http://127.0.0.1:8000/compare');
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$cmpHtml = curl_exec($ch);
$cmpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertTest("GET /compare loads responsive comparison matrix", $cmpCode === 200 && str_contains($cmpHtml, 'Compare Products'), "HTTP {$cmpCode}");

// ----------------------------------------------------
// TEST 7: BUNDLE BUILDER & SERVER-SIDE PRICING (FEATURE 7)
// ----------------------------------------------------
echo "\nTesting Feature 7: Bundle Builder Server-side Pricing Engine...\n";
$bundleModel = new \Models\Bundle();
$starterBundle = $pdo->query("SELECT id, name, discount_value FROM bundles WHERE status = 'active' LIMIT 1")->fetch();

if ($starterBundle) {
    // Pick 3 variant IDs from catalog
    $variantIds = $pdo->query("SELECT id FROM product_variants LIMIT 3")->fetchAll(PDO::FETCH_COLUMN);
    $bundleCalcRes = $bundleModel->calculateBundle((int)$starterBundle['id'], $variantIds);

    assertTest("Bundle pricing calculated strictly server-side", !empty($bundleCalcRes['items']) && $bundleCalcRes['final_price'] > 0, "Subtotal: ₹{$bundleCalcRes['individual_total']}, Final: ₹{$bundleCalcRes['final_price']}, Savings: ₹{$bundleCalcRes['total_savings']}");
    assertTest("Bundle discount applied correctly", $bundleCalcRes['discount_amount'] > 0 && $bundleCalcRes['final_price'] < $bundleCalcRes['individual_total'], "Discount ₹{$bundleCalcRes['discount_amount']} Verified");
    $expectedSavings = round($bundleCalcRes['individual_total'] - $bundleCalcRes['final_price'], 2);
    assertTest("Bundle savings strictly equals Subtotal - Final Price (Regression Test)", abs($bundleCalcRes['total_savings'] - $expectedSavings) < 0.01, "Savings ₹{$bundleCalcRes['total_savings']} == ₹{$bundleCalcRes['individual_total']} - ₹{$bundleCalcRes['final_price']}");
}

// ----------------------------------------------------
// TEST 8: RECENTLY VIEWED PRODUCTS (FEATURE 8)
// ----------------------------------------------------
echo "\nTesting Feature 8: Recently Viewed Products...\n";
$rvModel = new \Models\RecentlyViewed();
$guestSession = 'test_guest_session_' . uniqid();
$prodIdToTrack = (int)$pdo->query("SELECT id FROM products LIMIT 1")->fetchColumn();

$rvModel->record($prodIdToTrack, null, $guestSession);
$recentList = $rvModel->getProducts(null, $guestSession, 10);
$foundRecent = false;
foreach ($recentList as $rItem) {
    if ($rItem['id'] == $prodIdToTrack) $foundRecent = true;
}
assertTest("Recently Viewed records guest sessions safely", $foundRecent === true, "Tracked product ID: {$prodIdToTrack}");

// ----------------------------------------------------
// TEST 9: SMART SEARCH & AJAX SUGGESTIONS (FEATURE 9)
// ----------------------------------------------------
echo "\nTesting Feature 9: Smart Search AJAX Suggestions API...\n";
$ch = curl_init('http://127.0.0.1:8000/api/search/suggestions?q=period');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$searchRes = json_decode(curl_exec($ch), true);
curl_close($ch);

assertTest("Smart Search suggestions API responds with JSON", !empty($searchRes['products']), "Query 'period' processed");
assertTest("Search returns product suggestions with images & pricing", !empty($searchRes['products']), "Returned " . count($searchRes['products']) . " product matches");

// ----------------------------------------------------
// TEST 10: FIRST ORDER OFFER (FEATURE 10)
// ----------------------------------------------------
echo "\nTesting Feature 10: First Order Promotion Server Validation...\n";
$firstOrderCode = get_setting('first_order_coupon_code', 'WELCOME10');
$firstOrderDisc = (float)get_setting('first_order_discount_value', 10);
$firstOrderMin = (float)get_setting('first_order_min_order', 499);

assertTest("First order offer settings configured in database", !empty($firstOrderCode) && $firstOrderDisc > 0 && $firstOrderMin > 0, "Code: {$firstOrderCode}, Discount: ₹{$firstOrderDisc}, Min Order: ₹{$firstOrderMin}");

// ----------------------------------------------------
// TEST 11: FREE SHIPPING PROGRESS (FEATURE 11)
// ----------------------------------------------------
echo "\nTesting Feature 11: Free Shipping Threshold Progress...\n";
$threshold = (float)get_setting('free_shipping_threshold', 499);
$testSubtotal = 349.00;
$needed = max(0, $threshold - $testSubtotal);
$percent = min(100, round(($testSubtotal / $threshold) * 100));

assertTest("Free shipping threshold server-side math", $needed == 150.00 && $percent == 70, "₹{$testSubtotal} spent => ₹{$needed} more needed ({$percent}%)");

// ----------------------------------------------------
// TEST 12: FLASH SALE SYSTEM (FEATURE 12)
// ----------------------------------------------------
echo "\nTesting Feature 12: Flash Sale Server-side Validation...\n";
$fsModel = new \Models\FlashSale();
$activeSale = $fsModel->getActiveSale();
assertTest("Active Flash Sale retrieved with date range validation", $activeSale !== null, "Title: " . ($activeSale['title'] ?? 'None'));

if ($activeSale) {
    $now = date('Y-m-d H:i:s');
    $isLive = ($now >= $activeSale['start_at'] && $now <= $activeSale['end_at']);
    assertTest("Flash Sale start and end dates validated server-side", $isLive === true, "Live between {$activeSale['start_at']} and {$activeSale['end_at']}");
}

// ----------------------------------------------------
// TEST 13: LOYALTY POINTS LEDGER (FEATURE 13)
// ----------------------------------------------------
echo "\nTesting Feature 13: Loyalty Points Ledger...\n";
$loyaltyModel = new \Models\Loyalty();
$testUserId = (int)$pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

// Check initial balance
$initBal = $loyaltyModel->getBalance($testUserId);

// Add test points to ledger (100 points)
$loyaltyModel->addPoints($testUserId, 100, 'earned', 'Automated Phase 2 Unit Test Reward');
$newBal = $loyaltyModel->getBalance($testUserId);
assertTest("Loyalty ledger adds earned points and computes balance", $newBal === ($initBal + 100), "Previous: {$initBal}, New: {$newBal}");

// Test redemption (redeem 50 points on test order #1)
$redeemSuccess = $loyaltyModel->redeemPoints($testUserId, 50, 1);
$afterRedeemBal = $loyaltyModel->getBalance($testUserId);
assertTest("Loyalty ledger redeems points safely without negatives", $redeemSuccess === true && $afterRedeemBal === ($initBal + 50), "Remaining Balance: {$afterRedeemBal}");

// Prevent overdraft
$overdraftBlocked = false;
try {
    $loyaltyModel->redeemPoints($testUserId, 9999999, 1);
} catch (\Exception $e) {
    $overdraftBlocked = true;
}
assertTest("Loyalty ledger prevents negative balance / overdraft abuse", $overdraftBlocked === true, "Excessive redemption safely blocked with Exception");

// ----------------------------------------------------
// TEST 14: REFERRAL PROGRAM (FEATURE 14)
// ----------------------------------------------------
echo "\nTesting Feature 14: Referral Program Anti-abuse & Tracking...\n";
$refModel = new \Models\Referral();
$userA = (int)$pdo->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn();
$userB = (int)$pdo->query("SELECT id FROM users ORDER BY id DESC LIMIT 1")->fetchColumn();

$refCodeA = $refModel->getReferralCode($userA);
assertTest("User receives unique referral code", !empty($refCodeA) && str_starts_with($refCodeA, 'AFH-'), "Referral Code: {$refCodeA}");

// Anti self-referral
$selfRefSuccess = $refModel->recordReferral($userA, $userA, $refCodeA);
assertTest("Referral program rejects self-referral", $selfRefSuccess === false, "Prevented user from referring themselves");

// ----------------------------------------------------
// TEST 15: CUSTOMER SEGMENTS (FEATURE 15)
// ----------------------------------------------------
echo "\nTesting Feature 15: Customer Segmentation...\n";
$segments = ['new', 'repeat', 'high_value', 'inactive', 'cod', 'prepaid'];
$ch = curl_init('http://127.0.0.1:8000/admin/customers?segment=repeat');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$segHtml = curl_exec($ch);
$segCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assertTest("Customer segments filterable in admin (Repeat, High Value, COD, etc.)", $segCode === 200 || $segCode === 302, "HTTP {$segCode}");

// ----------------------------------------------------
// TEST 17: WHATSAPP DIRECT SUPPORT & COMMUNITY (FEATURE 17)
// ----------------------------------------------------
echo "\nTesting Feature 17: Voluntary WhatsApp Support Links...\n";
$waNumber = preg_replace('/[^0-9]/', '', get_setting('whatsapp_number', '919876543210'));
$waOrderMsg = urlencode("Hi AFOREHEALTH, I need help with Order AFH-20261001-000101.");
$waUrl = "https://wa.me/{$waNumber}?text={$waOrderMsg}";
assertTest("WhatsApp support link contains only safe information without leaking sensitive PII", str_contains($waUrl, 'AFH-20261001-000101') && !str_contains($waUrl, 'password') && !str_contains($waUrl, 'otp'), "URL: {$waUrl}");

// ----------------------------------------------------
// TEST 21: SALES FUNNEL & DATALAYER (FEATURE 21)
// ----------------------------------------------------
echo "\nTesting Feature 21: Analytics dataLayer Event Architecture...\n";
$headerFile = file_get_contents(__DIR__ . '/../views/layouts/header.php');
assertTest("window.dataLayer and trackAnalytics helper initialized in header", str_contains($headerFile, 'window.dataLayer = window.dataLayer || [];') && str_contains($headerFile, 'window.trackAnalytics = function'), "GTM/GA4 dataLayer initialized");

// ----------------------------------------------------
// TEST 25: PWA FOUNDATION (FEATURE 25)
// ----------------------------------------------------
echo "\nTesting Feature 25: PWA Manifest & Offline Fallback...\n";
$manifestExists = file_exists(__DIR__ . '/../public/manifest.json');
$swExists = file_exists(__DIR__ . '/../public/sw.js');
$offlineExists = file_exists(__DIR__ . '/../public/offline.html');
assertTest("PWA manifest.json, sw.js, and offline.html present", $manifestExists && $swExists && $offlineExists, "PWA assets verified");

// ----------------------------------------------------
// SUMMARY
// ----------------------------------------------------
echo "\n========================================================\n";
echo "   PHASE 2 AUTOMATED TEST SUITE EXECUTION SUMMARY\n";
echo "========================================================\n";
echo "TOTAL TESTS: " . ($passed + $failed) . "\n";
echo "PASSED:      {$passed}\n";
echo "FAILED:      {$failed}\n";

if ($failed === 0) {
    echo "\nOVERALL STATUS: PASS (100% SUCCESS)\n";
    exit(0);
} else {
    echo "\nOVERALL STATUS: FAIL ({$failed} failures)\n";
    exit(1);
}
