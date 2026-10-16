<?php
/**
 * AFOREHEALTH — 2Factor.in SMS OTP Authentication Test Suite
 * 
 * Tests mobile number normalization, rate limiting, lockout mechanics,
 * session management, database persistence, and CSRF protection.
 * Provider calls are mocked; NO paid SMS are sent.
 * 
 * Run in CLI: php tests/twofactor_auth_test.php
 */

date_default_timezone_set('Asia/Kolkata');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Referral.php';
require_once __DIR__ . '/../services/TwoFactorService.php';

use Models\User;
use Services\TwoFactorService;

$pdo = getDbConnection();
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function testAssert(string $section, string $testName, bool $condition, string $details = ''): void {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo "  [PASS] {$testName}\n";
    } else {
        $failedTests++;
        echo "  [FAIL] {$testName} — {$details}\n";
    }
}

echo "\n=======================================================\n";
echo "   AFOREHEALTH — 2FACTOR SMS OTP INTEGRATION TEST SUITE\n";
echo "=======================================================\n\n";

// -----------------------------------------------------------------
// 1. PHONE NUMBER NORMALIZATION & VALIDATION TESTS
// -----------------------------------------------------------------
echo "--- 1. Phone Normalization & Validation ---\n";
$twoFactor = new TwoFactorService();

// Valid phone inputs
$res1 = $twoFactor->normalizePhone('9876543210');
testAssert("Phone Validation", "Standard 10-digit phone accepted", $res1['local'] === '9876543210' && $res1['twofactor'] === '919876543210');

$res2 = $twoFactor->normalizePhone('+91 98765 43210');
testAssert("Phone Validation", "Phone with +91 prefix and spaces normalized to 10 digits", $res2['local'] === '9876543210' && $res2['e164'] === '+919876543210');

$res3 = $twoFactor->normalizePhone('09876543210');
testAssert("Phone Validation", "Phone with leading 0 normalized", $res3['local'] === '9876543210');

$res4 = $twoFactor->normalizePhone('919876543210');
testAssert("Phone Validation", "Phone with 91 country code prefix normalized", $res4['local'] === '9876543210');

// Invalid inputs
$invalidCaught = 0;
$invalidNumbers = ['1234567890', '98765', '987654321000', 'abcdefghij', '5876543210'];
foreach ($invalidNumbers as $inv) {
    try {
        $twoFactor->normalizePhone($inv);
    } catch (\InvalidArgumentException $e) {
        $invalidCaught++;
    }
}
testAssert("Phone Validation", "All 5 invalid phone patterns rejected with InvalidArgumentException", $invalidCaught === 5);

// Phone masking
$masked = $twoFactor->maskPhone('9876543210');
testAssert("Phone Masking", "Phone is masked correctly without leaking full digits", $masked === '+91 98XXX XX210');

// -----------------------------------------------------------------
// 2. PROVIDER MOCK MODE & SECRET ISOLATION
// -----------------------------------------------------------------
echo "\n--- 2. Provider Mock Mode & Secret Isolation ---\n";
TwoFactorService::setMockMode(true);

$sendResult = $twoFactor->sendAutogenOtp('9876543210');
testAssert("Mock Provider", "Mock AUTOGEN OTP send returns success with session_id", $sendResult['success'] === true && !empty($sendResult['session_id']));

$mockSessionId = $sendResult['session_id'];
$verifySuccess = $twoFactor->verifyOtp($mockSessionId, '123456', '9876543210');
testAssert("Mock Provider", "Mock OTP verification with correct code succeeds", $verifySuccess['success'] === true);

$verifyFail = $twoFactor->verifyOtp($mockSessionId, '000000', '9876543210');
testAssert("Mock Provider", "Mock OTP verification with incorrect code fails", $verifyFail['success'] === false && $verifyFail['code'] === 'OTP_MISMATCH');

// Check .env.example does not leak real API keys
$envExample = file_get_contents(__DIR__ . '/../.env.example');
testAssert("Secret Isolation", ".env.example contains TWOFACTOR_API_KEY placeholder", strpos($envExample, 'TWOFACTOR_API_KEY=') !== false);
testAssert("Secret Isolation", ".env.example has empty placeholder (no real key)", preg_match('/TWOFACTOR_API_KEY=\s*$/m', $envExample) === 1);

// Check .gitignore excludes .env
$gitIgnore = file_get_contents(__DIR__ . '/../.gitignore');
testAssert("Secret Isolation", ".gitignore excludes .env", strpos($gitIgnore, '.env') !== false);

// -----------------------------------------------------------------
// 3. DATABASE PERSISTENCE & SESSION LIFECYCLE
// -----------------------------------------------------------------
echo "\n--- 3. Database Persistence & Session Lifecycle ---\n";
$userModel = new User();
$testPhone = '9999900001';
$testPurpose = 'login';
$testIp = '127.0.0.1';

// Clean test records
$pdo->prepare("DELETE FROM otp_verifications WHERE mobile = ?")->execute([$testPhone]);

// Record session
$recId = $userModel->recordTwoFactorSession($testPhone, $testPurpose, '2f-test-session-12345', $testIp);
testAssert("DB Persistence", "TwoFactor session recorded in database", $recId > 0);

// Verify that plain text OTP is NOT stored in DB
$row = $pdo->query("SELECT * FROM otp_verifications WHERE id = {$recId}")->fetch(PDO::FETCH_ASSOC);
testAssert("Security Zero Plaintext OTP", "otp_code is null or empty when using 2Factor AUTOGEN", empty($row['otp_code']));
testAssert("DB Persistence", "twofactor_session_id is saved correctly", $row['twofactor_session_id'] === '2f-test-session-12345');
testAssert("DB Persistence", "ip_address is stored for audit and rate-limiting", $row['ip_address'] === $testIp);

// Active session retrieval
$active = $userModel->getActiveTwoFactorSession($testPhone, $testPurpose);
testAssert("Session Retrieval", "Active session retrieved within 10-minute window", $active !== null && (int)$active['id'] === $recId);

// Failed attempts increment & lockout
$att1 = $userModel->recordFailedAttempt($recId);
$att2 = $userModel->recordFailedAttempt($recId);
$att3 = $userModel->recordFailedAttempt($recId);
$att4 = $userModel->recordFailedAttempt($recId);
$att5 = $userModel->recordFailedAttempt($recId);
testAssert("Brute-Force Protection", "Failed attempts counter accurately increments to 5", $att5 === 5);

// Check that attempts in DB is 5
$dbAttempts = (int)$pdo->query("SELECT attempts FROM otp_verifications WHERE id = {$recId}")->fetchColumn();
testAssert("Brute-Force Counter", "Database row reflects 5 failed attempts", $dbAttempts === 5);

// Check that session reflects 5 failed attempts for lockout handling
$activeAfter5 = $userModel->getActiveTwoFactorSession($testPhone, $testPurpose);
testAssert("Brute-Force Lockout", "Session reflects 5 failed attempts for lockout enforcement", $activeAfter5 !== null && (int)$activeAfter5['attempts'] === 5);

// Mark session verified
$userModel->markOtpSessionVerified($recId);
$activeAfterVerify = $userModel->getActiveTwoFactorSession($testPhone, $testPurpose);
testAssert("Session Invalidation", "Verified session cannot be re-used (returns null)", $activeAfterVerify === null);

// -----------------------------------------------------------------
// 4. RATE LIMITING ENGINE TESTS
// -----------------------------------------------------------------
echo "\n--- 4. Rate Limiting Engine ---\n";
$rateLimitPhone = '9999900002';
$pdo->prepare("DELETE FROM otp_verifications WHERE mobile = ?")->execute([$rateLimitPhone]);

// First 3 sends should succeed
$userModel->recordTwoFactorSession($rateLimitPhone, 'login', 'sess-1', '127.0.0.1');
$userModel->recordTwoFactorSession($rateLimitPhone, 'login', 'sess-2', '127.0.0.1');
$userModel->recordTwoFactorSession($rateLimitPhone, 'login', 'sess-3', '127.0.0.1');

$rateLimitBlocked = false;
$rateLimitMessage = '';
try {
    $userModel->checkSendRateLimit($rateLimitPhone, '127.0.0.1');
} catch (\Exception $e) {
    $rateLimitBlocked = true;
    $rateLimitMessage = $e->getMessage();
}
testAssert("Rate Limiter", "4th OTP send within 10 minutes is blocked (max 3/10min)", $rateLimitBlocked);
testAssert("Rate Limiter Message", "Rate limit message is informative and professional", strpos($rateLimitMessage, 'Too many OTP requests') !== false);

// -----------------------------------------------------------------
// 5. CSRF & SESSION FIXATION PROTECTION
// -----------------------------------------------------------------
echo "\n--- 5. CSRF & Session Fixation Protection ---\n";
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$csrfToken = csrf_token();
testAssert("CSRF Protection", "CSRF token is generated with 64-character hex string", strlen($csrfToken) === 64);
testAssert("CSRF Protection", "Valid CSRF token passes verify_csrf_token()", verify_csrf_token($csrfToken) === true);
testAssert("CSRF Protection", "Tampered CSRF token is rejected", verify_csrf_token('fake_token_123') === false);
testAssert("CSRF Protection", "Empty CSRF token is rejected", verify_csrf_token('') === false);
testAssert("CSRF Protection", "Null CSRF token is rejected", verify_csrf_token(null) === false);

// -----------------------------------------------------------------
// 6. REGISTRATION & LOGIN CONFLICT RESOLUTION
// -----------------------------------------------------------------
echo "\n--- 6. Registration & Duplicate Phone Handling ---\n";
$existingUserPhone = '9999900003';
$pdo->prepare("DELETE FROM users WHERE mobile = ?")->execute([$existingUserPhone]);
$pdo->prepare("DELETE FROM otp_verifications WHERE mobile = ?")->execute([$existingUserPhone]);

// Create user
$newUserId = $userModel->register([
    'name' => 'OTP Test User',
    'mobile' => $existingUserPhone,
    'password' => 'secret123',
    'status' => 'active'
]);
testAssert("User Model", "Test user registered successfully", $newUserId > 0);

// Check duplicate detection
$dupUser = $userModel->findByMobile($existingUserPhone);
testAssert("Duplicate Check", "Existing user detected by mobile number", $dupUser !== null && (int)$dupUser['id'] === $newUserId);

// Clean up test records
$pdo->prepare("DELETE FROM users WHERE mobile = ?")->execute([$existingUserPhone]);
$pdo->prepare("DELETE FROM otp_verifications WHERE mobile IN (?, ?, ?)")->execute([$testPhone, $rateLimitPhone, $existingUserPhone]);

// -----------------------------------------------------------------
// SUMMARY
// -----------------------------------------------------------------
echo "\n=======================================================\n";
echo "   TEST RESULTS: {$passedTests} PASSED, {$failedTests} FAILED (TOTAL: {$totalTests})\n";
echo "=======================================================\n\n";

if ($failedTests > 0) {
    exit(1);
}
exit(0);
