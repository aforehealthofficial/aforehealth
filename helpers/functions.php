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
    $cleanPath = ltrim($path, '/');
    $filePath = dirname(__DIR__) . '/public/assets/' . $cleanPath;
    $version = file_exists($filePath) ? filemtime($filePath) : '2.1';
    return url('assets/' . $cleanPath . '?v=' . $version);
}

/**
 * Uploaded file URL helper
 */
function upload_url(string $path): string {
    return url('uploads/' . ltrim($path, '/'));
}

/**
 * Handle single file upload safely
 */
function upload_file(array $file, string $subfolder = 'products', array $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif']): ?string {
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        return null;
    }

    $uploadDir = dirname(__DIR__) . '/public/uploads/' . trim($subfolder, '/');
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $cleanBase = slugify(pathinfo($file['name'], PATHINFO_FILENAME));
    if (empty($cleanBase)) {
        $cleanBase = 'file';
    }
    $filename = $cleanBase . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
    $targetPath = $uploadDir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $filename;
    }

    return null;
}

/**
 * Handle multiple files upload safely
 */
function upload_multiple_files(array $files, string $subfolder = 'products', array $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif']): array {
    $uploaded = [];
    if (empty($files['name']) || !is_array($files['name'])) {
        return $uploaded;
    }

    $count = count($files['name']);
    for ($i = 0; $i < $count; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK && is_uploaded_file($files['tmp_name'][$i])) {
            $single = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];
            $saved = upload_file($single, $subfolder, $allowedExts);
            if ($saved) {
                $uploaded[] = $saved;
            }
        }
    }

    return $uploaded;
}

/**
 * Handle video file upload safely (MP4, WebM, MOV, OGG, M4V)
 */
function upload_video(array $file, string $subfolder = 'products', array $allowedExts = ['mp4', 'webm', 'mov', 'ogg', 'm4v']): ?string {
    return upload_file($file, $subfolder, $allowedExts);
}

/**
 * Parse and normalize video sources (Uploaded file, YouTube, Vimeo, direct MP4)
 */
function parse_video_source(?string $videoUrl, ?string $videoFile = null): ?array {
    $videoUrl = trim((string)$videoUrl);
    $videoFile = trim((string)$videoFile);

    // 1. If an uploaded video file exists
    if (!empty($videoFile)) {
        return [
            'type' => 'file',
            'src' => upload_url('products/' . $videoFile),
            'filename' => $videoFile,
            'is_embed' => false,
            'thumbnail' => null
        ];
    }

    if (empty($videoUrl)) {
        return null;
    }

    // 2. Direct video file URL (e.g. https://domain.com/video.mp4)
    if (preg_match('/\.(mp4|webm|ogg|mov|m4v)(\?.*)?$/i', $videoUrl)) {
        return [
            'type' => 'direct',
            'src' => $videoUrl,
            'is_embed' => false,
            'thumbnail' => null
        ];
    }

    // 3. YouTube: watch?v=, embed/, shorts/, youtu.be/
    if (preg_match('~(?:youtube\.com\/(?:watch\?v=|embed\/|v\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})~i', $videoUrl, $matches)) {
        $ytId = $matches[1];
        return [
            'type' => 'youtube',
            'id' => $ytId,
            'src' => "https://www.youtube.com/embed/{$ytId}?rel=0&modestbranding=1&enablejsapi=1",
            'thumbnail' => "https://img.youtube.com/vi/{$ytId}/hqdefault.jpg",
            'is_embed' => true
        ];
    }

    // 4. Vimeo
    if (preg_match('~vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)~i', $videoUrl, $matches)) {
        $vimeoId = end($matches);
        return [
            'type' => 'vimeo',
            'id' => $vimeoId,
            'src' => "https://player.vimeo.com/video/{$vimeoId}?autoplay=1",
            'thumbnail' => null,
            'is_embed' => true
        ];
    }

    // 5. Fallback URL
    return [
        'type' => 'external',
        'src' => $videoUrl,
        'is_embed' => str_starts_with($videoUrl, 'http'),
        'thumbnail' => null
    ];
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
    if (empty($_SESSION['user']['id'])) {
        return null;
    }
    if (empty($_SESSION['user']['name']) || empty($_SESSION['user']['mobile'])) {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT id, name, mobile, email, role FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user']['id']]);
            $u = $stmt->fetch();
            if ($u) {
                $_SESSION['user'] = array_merge($_SESSION['user'], $u);
            }
        } catch (\Throwable $e) {}
    }
    return $_SESSION['user'];
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
    if (!is_authenticated()) {
        return count($_SESSION['guest_wishlist'] ?? []);
    }
    static $wCount = null;
    if ($wCount !== null) return $wCount;

    try {
        $pdo = getDbConnection();
        $user = auth_user();
        if ($user) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ?");
            $stmt->execute([(int)$user['id']]);
            $wCount = (int)$stmt->fetchColumn();
        } else {
            $wCount = 0;
        }
    } catch (\Throwable $e) {
        $wCount = 0;
    }

    return $wCount;
}

/**
 * Check if product is in current user/guest wishlist
 */
function is_in_wishlist(int $productId): bool {
    static $userWishlistIds = null;
    if ($userWishlistIds === null) {
        if (!is_authenticated()) {
            $userWishlistIds = array_map('intval', $_SESSION['guest_wishlist'] ?? []);
        } else {
            try {
                $pdo = getDbConnection();
                $user = auth_user();
                if ($user) {
                    $stmt = $pdo->prepare("SELECT product_id FROM wishlists WHERE user_id = ?");
                    $stmt->execute([(int)$user['id']]);
                    $userWishlistIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
                } else {
                    $userWishlistIds = [];
                }
            } catch (\Throwable $e) {
                $userWishlistIds = [];
            }
        }
    }
    return in_array($productId, $userWishlistIds, true);
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

/**
 * Human-readable time ago
 */
function time_ago(int|string $timestamp): string {
    if (!is_numeric($timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return round($diff / 60) . ' mins ago';
    if ($diff < 86400) return round($diff / 3600) . ' hours ago';
    if ($diff < 604800) return round($diff / 86400) . ' days ago';
    return date('d M Y', $timestamp);
}
