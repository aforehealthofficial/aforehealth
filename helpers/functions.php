<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/env.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate full or relative URL
 */
function url(string $path = ''): string {
    $config = require __DIR__ . '/../config/app.php';
    $baseUrl = $config['url'];
    
    // Auto-detect host if in development or matching request
    if (isset($_SERVER['HTTP_HOST'])) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
        $currentHostUrl = rtrim($protocol . $_SERVER['HTTP_HOST'], '/');
        // If request is made to 127.0.0.1 or localhost, use current host
        if (str_contains($_SERVER['HTTP_HOST'], '127.0.0.1') || str_contains($_SERVER['HTTP_HOST'], 'localhost') || str_contains($_SERVER['HTTP_HOST'], '.test')) {
            $baseUrl = $currentHostUrl;
        }
    }

    $path = ltrim($path, '/');
    return $path === '' ? $baseUrl : $baseUrl . '/' . $path;
}

/**
 * Asset URL helper
 */
function asset(string $path): string {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Uploaded file URL helper
 */
function upload_url(string $path): string {
    return url('uploads/' . ltrim($path, '/'));
}

/**
 * HTML Escaping
 */
function e(?string $string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format Price in INR
 */
function format_price($amount, bool $showSymbol = true): string {
    $amount = (float)$amount;
    $symbol = $showSymbol ? '₹' : '';
    // Indian currency comma grouping or standard 2-decimal formatting
    return $symbol . number_format($amount, 0);
}

/**
 * CSRF Protection
 */
function csrf_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf_token(?string $token): bool {
    if (!$token || empty($_SESSION['_csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['_csrf_token'], $token);
}

/**
 * Flash messaging
 */
function set_flash(string $type, string $message): void {
    if (!isset($_SESSION['_flash'])) {
        $_SESSION['_flash'] = [];
    }
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array {
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

/**
 * Authentication Helpers
 */
function auth_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function is_authenticated(): bool {
    return !empty($_SESSION['user']['id']);
}

function auth_admin(): ?array {
    return $_SESSION['admin'] ?? null;
}

function is_admin(): bool {
    return !empty($_SESSION['admin']['id']);
}

/**
 * Database Settings Helper
 */
function get_setting(string $key, $default = null): string {
    static $settingsCache = null;

    if ($settingsCache === null) {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
            $settingsCache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            $settingsCache = [];
        }
    }

    return $settingsCache[$key] ?? (string)$default;
}

/**
 * JSON Response helper
 */
function json_response(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Redirect Helper
 */
function redirect(string $path, int $status = 302): void {
    if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
        $path = url($path);
    }
    header("Location: {$path}", true, $status);
    exit;
}

/**
 * Slug generator
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * View Renderer
 */
function view(string $viewPath, array $data = []): void {
    extract($data);
    $fullPath = dirname(__DIR__) . '/views/' . str_replace('.', '/', $viewPath) . '.php';

    if (!file_exists($fullPath)) {
        http_response_code(500);
        die("View [{$viewPath}] not found at {$fullPath}");
    }

    require $fullPath;
}

/**
 * Live Cart count
 */
function get_cart_count(): int {
    static $count = null;
    if ($count !== null) return $count;

    try {
        $pdo = getDbConnection();
        $userId = auth_user()['id'] ?? null;
        $sessionId = session_id();

        if ($userId) {
            $stmt = $pdo->prepare("
                SELECT SUM(ci.quantity) FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE c.user_id = ?
            ");
            $stmt->execute([$userId]);
        } else {
            $stmt = $pdo->prepare("
                SELECT SUM(ci.quantity) FROM cart_items ci
                JOIN carts c ON ci.cart_id = c.id
                WHERE c.session_id = ?
            ");
            $stmt->execute([$sessionId]);
        }
        $count = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $count = 0;
    }

    return $count;
}

/**
 * Live Wishlist count
 */
function get_wishlist_count(): int {
    if (!is_authenticated()) return 0;
    static $wCount = null;
    if ($wCount !== null) return $wCount;

    try {
        $pdo = getDbConnection();
        $userId = auth_user()['id'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
        $stmt->execute([$userId]);
        $wCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        $wCount = 0;
    }

    return $wCount;
}

/**
 * Generate unique order number
 * Format: AFH-YYYYMMDD-XXXXXX
 */
function generate_order_number(): string {
    $date = date('Ymd');
    $random = str_pad((string)mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
    return "AFH-{$date}-{$random}";
}
