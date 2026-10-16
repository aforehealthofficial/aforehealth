<?php
require_once __DIR__ . '/../helpers/env.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/functions.php';
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/Product.php';

echo "=== Running Video Feature Test ===\n";

// Test 1: Helper function parsing
echo "1. Testing parse_video_source()...\n";

$ytTest = parse_video_source("https://www.youtube.com/watch?v=dQw4w9WgXcQ");
assert($ytTest !== null, "YouTube parse failed");
assert($ytTest['type'] === 'youtube', "YouTube type incorrect");
assert(str_contains($ytTest['src'], 'embed/dQw4w9WgXcQ'), "YouTube embed src incorrect");
assert(str_contains($ytTest['thumbnail'], 'dQw4w9WgXcQ'), "YouTube thumbnail incorrect");
echo "  ✓ YouTube parse passed\n";

$shortsTest = parse_video_source("https://www.youtube.com/shorts/abcdef12345");
assert($shortsTest !== null && $shortsTest['type'] === 'youtube', "YouTube shorts parse failed");
echo "  ✓ YouTube shorts parse passed\n";

$vimeoTest = parse_video_source("https://vimeo.com/76979871");
assert($vimeoTest !== null && $vimeoTest['type'] === 'vimeo', "Vimeo parse failed");
echo "  ✓ Vimeo parse passed\n";

$fileTest = parse_video_source(null, "sample_video_123.mp4");
assert($fileTest !== null && $fileTest['type'] === 'file', "File video parse failed");
assert(str_contains($fileTest['src'], 'sample_video_123.mp4'), "File video src incorrect");
echo "  ✓ Uploaded video file parse passed\n";

// Test 2: Database column checks
echo "2. Testing database columns...\n";
$pdo = getDbConnection();
$col1 = $pdo->query("SHOW COLUMNS FROM products LIKE 'video_url'")->fetch();
assert(!empty($col1), "products.video_url column missing");

$col2 = $pdo->query("SHOW COLUMNS FROM products LIKE 'video_file'")->fetch();
assert(!empty($col2), "products.video_file column missing");

$col3 = $pdo->query("SHOW COLUMNS FROM banners LIKE 'video_url'")->fetch();
assert(!empty($col3), "banners.video_url column missing");

$col4 = $pdo->query("SHOW COLUMNS FROM blog_posts LIKE 'video_url'")->fetch();
assert(!empty($col4), "blog_posts.video_url column missing");
echo "  ✓ Database columns verified successfully\n";

// Test 3: Model retrieval
echo "3. Testing Product model retrieval with video...\n";
$prodModel = new \Models\Product();
$firstProd = $pdo->query("SELECT id, slug FROM products LIMIT 1")->fetch();
if ($firstProd) {
    // Temporarily attach video_url to test product
    $pdo->prepare("UPDATE products SET video_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' WHERE id = ?")->execute([$firstProd['id']]);
    $pData = $prodModel->findBySlug($firstProd['slug']);
    assert(!empty($pData['video']), "Product video metadata not parsed in findBySlug");
    assert($pData['video']['type'] === 'youtube', "Product video type not youtube");
    echo "  ✓ Product model attaches parsed video correctly\n";
    // Reset video_url
    $pdo->prepare("UPDATE products SET video_url = NULL WHERE id = ?")->execute([$firstProd['id']]);
}

echo "\nALL VIDEO TESTS PASSED SUCCESSFULLY! ✓\n";
