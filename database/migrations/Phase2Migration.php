<?php
require_once dirname(__DIR__, 2) . '/config/database.php';

$pdo = getDbConnection();
echo "=== RUNNING AFOREHEALTH PHASE 2 DATABASE MIGRATION ===\n\n";

// 1. PINCODES TABLE
$pdo->exec("
CREATE TABLE IF NOT EXISTS `pincodes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `pincode` VARCHAR(6) NOT NULL UNIQUE,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(100) NOT NULL,
    `delivery_days` INT NOT NULL DEFAULT 4,
    `cod_available` TINYINT(1) NOT NULL DEFAULT 1,
    `prepaid_available` TINYINT(1) NOT NULL DEFAULT 1,
    `shipping_charge` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` VARCHAR(20) NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pincode` (`pincode`),
    INDEX `idx_state` (`state`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Table 'pincodes' ready.\n";

// 2. PRODUCT QUESTIONS TABLE
$pdo->exec("
CREATE TABLE IF NOT EXISTS `product_questions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `question` TEXT NOT NULL,
    `answer` TEXT NULL,
    `answered_by` INT UNSIGNED NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pq_product` (`product_id`),
    INDEX `idx_pq_user` (`user_id`),
    INDEX `idx_pq_status` (`status`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Table 'product_questions' ready.\n";

// 3. BUNDLES TABLE
$pdo->exec("
CREATE TABLE IF NOT EXISTS `bundles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `discount_type` ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 20.00,
    `min_items` INT NOT NULL DEFAULT 3,
    `featured_image` VARCHAR(255) DEFAULT 'bundle_period_starter.svg',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_bundle_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS `bundle_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `bundle_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NULL,
    `product_id` INT UNSIGNED NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_bi_bundle` (`bundle_id`),
    FOREIGN KEY (`bundle_id`) REFERENCES `bundles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Tables 'bundles' and 'bundle_items' ready.\n";

// 4. LOYALTY TRANSACTIONS TABLE
$pdo->exec("
CREATE TABLE IF NOT EXISTS `loyalty_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NULL,
    `points` INT NOT NULL,
    `transaction_type` ENUM('earned', 'redeemed', 'expired', 'reversed') NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `balance_after` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_lt_user` (`user_id`),
    INDEX `idx_lt_order` (`order_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Table 'loyalty_transactions' ready.\n";

// 5. REFERRALS TABLE & USER REFERRAL CODE
$cols = $pdo->query("DESCRIBE `users`")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('referral_code', $cols)) {
    $pdo->exec("ALTER TABLE `users` ADD COLUMN `referral_code` VARCHAR(50) UNIQUE AFTER `gender`");
    echo "✓ Added 'referral_code' to 'users'.\n";
}

$pdo->exec("
CREATE TABLE IF NOT EXISTS `referrals` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `referrer_user_id` INT UNSIGNED NOT NULL,
    `referred_user_id` INT UNSIGNED NOT NULL,
    `referral_code` VARCHAR(50) NOT NULL,
    `order_id` INT UNSIGNED NULL,
    `reward_points` INT NOT NULL DEFAULT 100,
    `status` ENUM('pending', 'rewarded', 'cancelled') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ref_referrer` (`referrer_user_id`),
    INDEX `idx_ref_referred` (`referred_user_id`),
    INDEX `idx_ref_code` (`referral_code`),
    FOREIGN KEY (`referrer_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`referred_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Table 'referrals' ready.\n";

// 6. FLASH SALES & PRODUCTS
$pdo->exec("
CREATE TABLE IF NOT EXISTS `flash_sales` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 20.00,
    `banner_text` VARCHAR(255) DEFAULT 'Weekend Flash Sale • Limited Stock',
    `start_at` DATETIME NOT NULL,
    `end_at` DATETIME NOT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_fs_dates` (`start_at`, `end_at`),
    INDEX `idx_fs_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

$pdo->exec("
CREATE TABLE IF NOT EXISTS `flash_sale_products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `flash_sale_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `discount_price` DECIMAL(10,2) NULL,
    `max_quantity` INT NOT NULL DEFAULT 50,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_fsp_sale` (`flash_sale_id`),
    INDEX `idx_fsp_product` (`product_id`),
    FOREIGN KEY (`flash_sale_id`) REFERENCES `flash_sales`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Tables 'flash_sales' and 'flash_sale_products' ready.\n";

// 7. RECENTLY VIEWED TABLE
$pdo->exec("
CREATE TABLE IF NOT EXISTS `recently_viewed` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `session_id` VARCHAR(100) NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `viewed_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_rv_user` (`user_id`),
    INDEX `idx_rv_session` (`session_id`),
    INDEX `idx_rv_product` (`product_id`),
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "✓ Table 'recently_viewed' ready.\n";

// 8. SEED DEFAULT SETTINGS
$settingsToSeed = [
    'delivery_default_min_days' => '3',
    'delivery_default_max_days' => '5',
    'delivery_skip_weekends' => '1',
    'delivery_holiday_dates' => '',
    'first_order_offer_enabled' => '1',
    'first_order_discount_type' => 'percentage',
    'first_order_discount_value' => '10',
    'first_order_coupon_code' => 'WELCOME10',
    'first_order_min_order' => '499',
    'loyalty_enabled' => '1',
    'loyalty_points_per_100_inr' => '5',
    'loyalty_point_redemption_value' => '1', // 1 point = 1 INR
    'loyalty_min_redemption_points' => '50',
    'loyalty_max_redemption_points' => '500',
    'referral_enabled' => '1',
    'referral_reward_points' => '100',
    'referral_friend_discount' => '100',
    'referral_min_order' => '599',
    'whatsapp_business_hours' => 'Mon–Sat: 9 AM – 8 PM IST'
];

$stmtSet = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
foreach ($settingsToSeed as $k => $v) {
    $stmtSet->execute([$k, $v]);
}
echo "✓ Settings populated.\n";

// 9. SEED REALISTIC INDIAN PINCODES
$pincodes = [
    ['110001', 'New Delhi', 'Delhi', 3, 1, 1, 0.00],
    ['110016', 'South Delhi', 'Delhi', 3, 1, 1, 0.00],
    ['400001', 'Mumbai', 'Maharashtra', 3, 1, 1, 0.00],
    ['400050', 'Bandra, Mumbai', 'Maharashtra', 3, 1, 1, 0.00],
    ['411001', 'Pune', 'Maharashtra', 3, 1, 1, 0.00],
    ['560001', 'Bengaluru', 'Karnataka', 2, 1, 1, 0.00],
    ['560038', 'Indiranagar, Bengaluru', 'Karnataka', 2, 1, 1, 0.00],
    ['560102', 'HSR Layout, Bengaluru', 'Karnataka', 2, 1, 1, 0.00],
    ['500001', 'Hyderabad', 'Telangana', 3, 1, 1, 0.00],
    ['600001', 'Chennai', 'Tamil Nadu', 3, 1, 1, 0.00],
    ['700001', 'Kolkata', 'West Bengal', 4, 1, 1, 0.00],
    ['160001', 'Chandigarh', 'Chandigarh', 3, 1, 1, 0.00],
    ['380001', 'Ahmedabad', 'Gujarat', 3, 1, 1, 0.00],
    ['302001', 'Jaipur', 'Rajasthan', 4, 1, 1, 0.00],
    ['226001', 'Lucknow', 'Uttar Pradesh', 4, 1, 1, 0.00],
    ['201301', 'Noida', 'Uttar Pradesh', 3, 1, 1, 0.00],
    ['122001', 'Gurugram', 'Haryana', 2, 1, 1, 0.00],
    ['682001', 'Kochi', 'Kerala', 4, 1, 1, 0.00],
    ['781001', 'Guwahati', 'Assam', 5, 0, 1, 40.00], // Non-COD remote pincode
    ['190001', 'Srinagar', 'Jammu and Kashmir', 6, 0, 1, 50.00]
];

$stmtPin = $pdo->prepare("
    INSERT INTO pincodes (pincode, city, state, delivery_days, cod_available, prepaid_available, shipping_charge, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
    ON DUPLICATE KEY UPDATE city=VALUES(city), state=VALUES(state), delivery_days=VALUES(delivery_days), cod_available=VALUES(cod_available)
");
foreach ($pincodes as $pin) {
    $stmtPin->execute($pin);
}
echo "✓ " . count($pincodes) . " Indian pincodes seeded.\n";

// 10. SEED SAMPLE BUNDLES
$stmtBundle = $pdo->prepare("
    INSERT INTO bundles (name, slug, description, discount_type, discount_value, min_items, featured_image, status)
    VALUES (?, ?, ?, 'percentage', 20.00, 3, 'bundle_period_starter.svg', 'active')
    ON DUPLICATE KEY UPDATE name=VALUES(name)
");
$stmtBundle->execute([
    'Custom Period Care Starter Kit',
    'custom-period-care-starter-kit',
    'Build your ultimate customized period routine: Pick 1 Period Panty, 1 Pad Pack, and 1 Intimate Wash & enjoy an instant 20% bundle discount.'
]);
$bundleId = (int)$pdo->lastInsertId();
if (!$bundleId) {
    $bundleId = (int)$pdo->query("SELECT id FROM bundles WHERE slug = 'custom-period-care-starter-kit'")->fetchColumn();
}

$pdo->prepare("DELETE FROM bundle_items WHERE bundle_id = ?")->execute([$bundleId]);
$stmtBi = $pdo->prepare("INSERT INTO bundle_items (bundle_id, category_id, is_required) VALUES (?, ?, ?)");
foreach ([1, 4, 5] as $cId) { // Period panties, Pads & liners, Intimate care
    $stmtBi->execute([$bundleId, $cId, 1]);
}
echo "✓ Custom Period Care bundle configured.\n";

// 11. SEED SAMPLE FLASH SALE (Active now)
$pdo->exec("
    INSERT INTO flash_sales (title, slug, discount_percent, banner_text, start_at, end_at, status)
    VALUES ('Weekend Comfort Flash Sale', 'weekend-comfort-sale', 25.00, '🔥 Weekend Comfort Sale: Extra 25% OFF on Select Period Essentials!', NOW() - INTERVAL 1 HOUR, NOW() + INTERVAL 72 HOUR, 'active')
    ON DUPLICATE KEY UPDATE end_at = NOW() + INTERVAL 72 HOUR, status='active'
");
$flashSaleId = (int)$pdo->query("SELECT id FROM flash_sales WHERE slug = 'weekend-comfort-sale'")->fetchColumn();
$prods = $pdo->query("SELECT id FROM products LIMIT 6")->fetchAll(PDO::FETCH_COLUMN);
$pdo->prepare("DELETE FROM flash_sale_products WHERE flash_sale_id = ?")->execute([$flashSaleId]);
$stmtFsp = $pdo->prepare("INSERT INTO flash_sale_products (flash_sale_id, product_id) VALUES (?, ?)");
foreach ($prods as $pid) {
    $stmtFsp->execute([$flashSaleId, $pid]);
}
echo "✓ Weekend Comfort Flash Sale seeded with " . count($prods) . " products.\n";

// 12. SEED SAMPLE QUESTIONS & ANSWERS
$existingQuestions = (int)$pdo->query("SELECT COUNT(*) FROM product_questions")->fetchColumn();
if ($existingQuestions === 0) {
    $pId = (int)$pdo->query("SELECT id FROM products LIMIT 1")->fetchColumn();
    $uId = (int)$pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();
    $stmtQ = $pdo->prepare("
        INSERT INTO product_questions (product_id, user_id, question, answer, answered_by, status)
        VALUES (?, ?, ?, ?, 1, 'approved')
    ");
    $stmtQ->execute([
        $pId, $uId,
        'Can I wear these period panties overnight on my heaviest day without any pads?',
        'Yes! Our UltraDry panties are engineered with a 4-layer breathable absorption gusset designed to hold up to 4 regular pads worth of liquid. They provide 360° leak guard even when sleeping on your side or back.'
    ]);
    $stmtQ->execute([
        $pId, $uId,
        'How do I wash them to maintain the leakproof barrier?',
        'Rinse immediately with cold water until the water runs clear, then machine or hand wash with gentle detergent. Avoid fabric softeners, bleach, or hot water tumble drying.'
    ]);
    echo "✓ Sample approved questions & answers seeded.\n";
}

// 13. SEED REFERRAL CODE FOR EXISTING USERS
$users = $pdo->query("SELECT id, name FROM users WHERE referral_code IS NULL")->fetchAll();
$stmtUpdRef = $pdo->prepare("UPDATE users SET referral_code = ? WHERE id = ?");
foreach ($users as $u) {
    $codeName = strtoupper(preg_replace('/[^A-Za-z]/', '', $u['name']));
    $code = 'AFH-' . substr($codeName, 0, 6) . $u['id'];
    $stmtUpdRef->execute([$code, $u['id']]);
}
echo "✓ User referral codes generated.\n";

echo "\n=== PHASE 2 DATABASE MIGRATION COMPLETED SUCCESSFULLY ===\n";
