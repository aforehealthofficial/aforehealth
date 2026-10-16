<?php
require_once __DIR__ . '/../../helpers/env.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDbConnection();

    // 1. Add video_url and video_file to products table
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'video_url'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE products ADD COLUMN `video_url` VARCHAR(500) NULL AFTER `primary_image`");
        echo "Added `video_url` to products table.\n";
    } else {
        echo "`video_url` already exists in products table.\n";
    }

    $stmt2 = $pdo->query("SHOW COLUMNS FROM products LIKE 'video_file'");
    if (!$stmt2->fetch()) {
        $pdo->exec("ALTER TABLE products ADD COLUMN `video_file` VARCHAR(255) NULL AFTER `video_url`");
        echo "Added `video_file` to products table.\n";
    } else {
        echo "`video_file` already exists in products table.\n";
    }

    // 2. Add video_url to banners table
    $stmt3 = $pdo->query("SHOW COLUMNS FROM banners LIKE 'video_url'");
    if (!$stmt3->fetch()) {
        $pdo->exec("ALTER TABLE banners ADD COLUMN `video_url` VARCHAR(500) NULL AFTER `image_mobile`");
        echo "Added `video_url` to banners table.\n";
    } else {
        echo "`video_url` already exists in banners table.\n";
    }

    // 3. Add video_url to blog_posts table
    $stmt4 = $pdo->query("SHOW COLUMNS FROM blog_posts LIKE 'video_url'");
    if (!$stmt4->fetch()) {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN `video_url` VARCHAR(500) NULL AFTER `featured_image`");
        echo "Added `video_url` to blog_posts table.\n";
    } else {
        echo "`video_url` already exists in blog_posts table.\n";
    }

    echo "Video support migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
