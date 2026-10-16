<?php
// AFOREHEALTH Database Seeder
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDbConnection();
    echo "Connected to database for seeding...\n";
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. Roles
    $pdo->exec("TRUNCATE TABLE `roles`;");
    $roles = [
        ['Super Admin', 'super-admin', 'Full access to all system modules and settings'],
        ['Admin', 'admin', 'Can manage products, orders, reviews and view reports'],
        ['Order Manager', 'order-manager', 'Can manage orders, shipments, returns and tracking'],
        ['Product Manager', 'product-manager', 'Can manage catalog, categories, variants and inventory'],
        ['Customer Support', 'customer-support', 'Can manage tickets, reviews and customer inquiries'],
        ['Content Manager', 'content-manager', 'Can manage blog posts, FAQs, banners and CMS pages']
    ];
    $stmtRole = $pdo->prepare("INSERT INTO `roles` (`name`, `slug`, `description`) VALUES (?, ?, ?)");
    foreach ($roles as $r) {
        $stmtRole->execute($r);
    }
    echo "Seeded roles.\n";

    // 2. Permissions
    $pdo->exec("TRUNCATE TABLE `permissions`;");
    $permissions = [
        ['View Dashboard', 'view-dashboard', 'dashboard'],
        ['Manage Products', 'manage-products', 'catalog'],
        ['Manage Categories', 'manage-categories', 'catalog'],
        ['Manage Inventory', 'manage-inventory', 'catalog'],
        ['Manage Reviews', 'manage-reviews', 'catalog'],
        ['Manage Orders', 'manage-orders', 'orders'],
        ['Manage Returns', 'manage-returns', 'orders'],
        ['Manage Customers', 'manage-customers', 'customers'],
        ['Manage Coupons', 'manage-coupons', 'marketing'],
        ['Manage Content', 'manage-content', 'content'],
        ['View Reports', 'view-reports', 'reports'],
        ['Manage Settings', 'manage-settings', 'settings'],
        ['Manage Admins', 'manage-admins', 'system']
    ];
    $stmtPerm = $pdo->prepare("INSERT INTO `permissions` (`name`, `slug`, `module`) VALUES (?, ?, ?)");
    foreach ($permissions as $p) {
        $stmtPerm->execute($p);
    }
    echo "Seeded permissions.\n";

    // Map all permissions to Super Admin (role_id 1)
    $pdo->exec("TRUNCATE TABLE `role_permissions`;");
    $allPermIds = $pdo->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
    $stmtRP = $pdo->prepare("INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES (1, ?)");
    foreach ($allPermIds as $pid) {
        $stmtRP->execute([$pid]);
    }

    // 3. Admins
    $pdo->exec("TRUNCATE TABLE `admins`;");
    $adminPassword = password_hash('Admin@12345', PASSWORD_DEFAULT);
    $admins = [
        ['Super Administrator', 'admin@aforehealth.com', $adminPassword, 1, 'active'],
        ['Pooja Nair (Catalog)', 'catalog@aforehealth.com', $adminPassword, 4, 'active'],
        ['Rohit Mehta (Operations)', 'operations@aforehealth.com', $adminPassword, 3, 'active']
    ];
    $stmtAdmin = $pdo->prepare("INSERT INTO `admins` (`name`, `email`, `password`, `role_id`, `status`) VALUES (?, ?, ?, ?, ?)");
    foreach ($admins as $adm) {
        $stmtAdmin->execute($adm);
    }
    echo "Seeded admin users (admin@aforehealth.com / Admin@12345).\n";

    // 4. Settings
    $pdo->exec("TRUNCATE TABLE `settings`;");
    $settings = [
        'store_name' => 'AFOREHEALTH',
        'store_tagline' => 'Everyday Comfort. Period Confidence.',
        'store_phone' => '+91 98765 43210',
        'store_email' => 'info@aforehealth.com',
        'store_address' => "AFOREHEALTH Wellness Pvt. Ltd.\nLevel 4, Platina Tower, Bandra Kurla Complex\nMumbai, Maharashtra - 400051, India",
        'store_gstin' => '27AAACA1234A1Z5',
        'currency_code' => 'INR',
        'currency_symbol' => '₹',
        'free_shipping_threshold' => '499',
        'flat_shipping_fee' => '50',
        'cod_enabled' => '1',
        'cod_fee' => '40',
        'cod_min_order' => '299',
        'cod_max_order' => '5000',
        'razorpay_enabled' => '1',
        'razorpay_key' => 'rzp_test_AFOREHEALTH_KEY',
        'razorpay_secret' => 'rzp_test_AFOREHEALTH_SECRET',
        'whatsapp_number' => '+919876543210',
        'whatsapp_group_url' => 'https://chat.whatsapp.com/C7fLq9wAFOREHEALTHcommunity',
        'whatsapp_support_message' => 'Hello AFOREHEALTH! I need assistance with my intimate wellness order.',
        'sms_enabled' => '1',
        'sms_provider' => 'msg91',
        'sms_sender_id' => 'AFHLTH',
        'smtp_host' => 'smtp.hostinger.com',
        'smtp_port' => '465',
        'smtp_encryption' => 'ssl',
        'smtp_user' => 'info@aforehealth.com',
        'smtp_pass' => '',
        'smtp_from' => 'info@aforehealth.com',
        'smtp_from_name' => 'AFOREHEALTH',
        'ga4_id' => 'G-AFOREHEALTH01',
        'gtm_id' => 'GTM-AFH9988',
        'meta_pixel_id' => '123456789012345',
        'seo_default_title' => "AFOREHEALTH | Everyday Comfort. Period Confidence.",
        'seo_default_description' => "India's trusted intimate-care and period-care brand. Shop leakproof period panties, breathable everyday underwear, organic cotton pads, and pH-balanced hygiene essentials."
    ];
    $stmtSet = $pdo->prepare("INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES (?, ?, 'general')");
    foreach ($settings as $k => $v) {
        $stmtSet->execute([$k, $v]);
    }
    echo "Seeded system settings.\n";

    // 5. Categories
    $pdo->exec("TRUNCATE TABLE `categories`;");
    $categories = [
        [1, 'Period Panties', 'period-panties', 'High absorbency, multi-layer leakproof period underwear engineered for heavy, medium, and light flow days.', 'cat_period_panties.svg', 'banner_period_panties.svg', 1],
        [2, 'Everyday Panties', 'everyday-panties', 'Buttery-soft, breathable bamboo and micro-modal panties designed for zero wedgies and daily all-day comfort.', 'cat_everyday_panties.svg', 'banner_everyday_panties.svg', 2],
        [3, 'Period Kits', 'period-kits', 'Curated confidence boxes, starter hampers, and travel packs with period underwear, pads, and hygiene essentials.', 'cat_period_kits.svg', 'banner_period_kits.svg', 3],
        [4, 'Pads & Liners', 'pads-and-liners', 'Rash-free 100% certified organic cotton pads, extra-long overnight wings, and biodegradable ultra-slim panty liners.', 'cat_pads_liners.svg', 'banner_pads_liners.svg', 4],
        [5, 'Intimate Care', 'intimate-care', 'Dermatologically tested pH 3.8 foaming washes, soothing anti-chafing sticks, and natural period panty stain cleansers.', 'cat_intimate_care.svg', 'banner_intimate_care.svg', 5],
        [6, 'Combos & Value Packs', 'combos', 'Multi-packs and curated bundles designed to give you maximum value while building your complete period routine.', 'cat_combos.svg', 'banner_combos.svg', 6]
    ];
    $stmtCat = $pdo->prepare("INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `banner`, `sort_order`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
    foreach ($categories as $c) {
        $stmtCat->execute($c);
    }
    echo "Seeded categories.\n";

    // 6. Subcategories
    $pdo->exec("TRUNCATE TABLE `subcategories`;");
    $subcategories = [
        [1, 1, 'Heavy Flow & Overnight', 'heavy-flow-overnight', 'Maximum 40ml absorbency with extended back coverage for zero sleep leaks.'],
        [2, 1, 'Medium & Active Flow', 'medium-active-flow', 'Sleek 25ml absorbency for workdays, gym sessions, and regular period days.'],
        [3, 1, 'Light Flow & Spotting', 'light-flow-spotting', 'Ultra-thin gusset for cycle start/end days, discharge, and gentle bladder leaks.'],
        [4, 2, 'CloudSoft Cotton Hipsters', 'cloudsoft-cotton-hipsters', 'Everyday full-seat coverage with gentle stretch waistband.'],
        [5, 2, 'No-Show Laser Cut', 'no-show-laser-cut', 'Zero panty lines under leggings, churidars, and bodycon dresses.'],
        [6, 3, 'First Period Starters', 'first-period-starters', 'Empowering kits for teens and young girls with guides and pouches.'],
        [7, 3, 'Postpartum Recovery', 'postpartum-recovery', 'High-waist gentle compression and heavy leak protection for new mothers.'],
        [8, 4, 'Organic Day & Night Pads', 'organic-day-night-pads', 'Breathable bamboo and organic cotton with zero chlorine or artificial scents.'],
        [9, 5, 'Foam Washes & Mists', 'foam-washes-mists', 'Maintains optimal vaginal flora and pH balance without harsh sulfates.'],
        [10, 6, 'Multi-Flow Bundles', 'multi-flow-bundles', 'Trio and quad packs tailored for a complete menstrual cycle.']
    ];
    $stmtSub = $pdo->prepare("INSERT INTO `subcategories` (`id`, `category_id`, `name`, `slug`, `description`) VALUES (?, ?, ?, ?, ?)");
    foreach ($subcategories as $sc) {
        $stmtSub->execute($sc);
    }
    echo "Seeded subcategories.\n";

    // 7. Collections
    $pdo->exec("TRUNCATE TABLE `collections`;");
    $collections = [
        [1, 'Bestsellers', 'bestsellers', 'Our most loved and highest rated intimate care picks by 50,000+ Indian women.', 'HOT SELLER', 'coll_bestsellers.svg', 1],
        [2, 'New Arrivals', 'new-arrivals', 'Freshly launched fabrics, moisture-wicking weaves, and new seasonal colorways.', 'JUST IN', 'coll_new_arrivals.svg', 2],
        [3, 'Period Starter Kits', 'starter-kits', 'Everything you need to switch away from single-use plastic pads with total peace of mind.', 'VALUE PACK', 'coll_starters.svg', 3],
        [4, 'Postpartum & High-Rise Care', 'postpartum-high-rise', 'Doctor-approved soothing recovery underwear engineered for postpartum bleeding.', 'RECOVERY', 'coll_postpartum.svg', 4]
    ];
    $stmtColl = $pdo->prepare("INSERT INTO `collections` (`id`, `name`, `slug`, `description`, `badge`, `image`, `sort_order`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($collections as $col) {
        $stmtColl->execute($col);
    }
    echo "Seeded collections.\n";

    // 8. Products (38 distinct realistic products)
    $pdo->exec("TRUNCATE TABLE `products`;");
    $pdo->exec("TRUNCATE TABLE `product_variants`;");
    $pdo->exec("TRUNCATE TABLE `product_images`;");
    $pdo->exec("TRUNCATE TABLE `inventory`;");
    $pdo->exec("TRUNCATE TABLE `inventory_logs`;");

    $products = [
        // 10 Period Panties
        [
            'name' => 'AFOREHEALTH UltraDry Heavy Flow Period Panty (4-Layer Gusset)',
            'slug' => 'ultradry-heavy-flow-period-panty',
            'sku' => 'AFH-PP-001',
            'cat' => 1, 'subcat' => 1, 'coll' => 1,
            'short' => 'Holds up to 4 regular pads worth of flow (40ml). 4-layer breathable leakproof barrier for 12 hours of dry comfort.',
            'desc' => "Designed specifically for heavy days and overnight peace of mind. Our proprietary 4-layer gusset technology pairs moisture-wicking organic cotton against your skin with an ultra-absorbent core and a waterproof yet breathable TPU barrier. No rustling sounds, no sweaty plastic feel, and zero stains on your bedsheets.",
            'mat' => 'Body: 95% Organic Bamboo Cotton, 5% Spandex. Gusset: Moisture-wicking cotton, microfibre absorbent layer, breathable waterproof TPU membrane.',
            'care' => 'Rinse in cold tap water until water runs clear. Machine wash with mild detergent on gentle cycle. Do NOT use bleach or fabric softeners. Line dry in shade.',
            'hygiene' => 'Hygiene Notice: For personal intimate health, this product is hygienically sealed. Wash thoroughly before first wear. Eligible for 7-day size exchange only if unopened and unwashed.',
            'mrp' => 1199, 'price' => 849, 'stock' => 180, 'featured' => 1, 'bestseller' => 1, 'new' => 0, 'rating' => 4.9, 'reviews' => 248,
            'variants' => [
                ['color' => 'Midnight Noir', 'code' => '#1F1E24', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']],
                ['color' => 'Berry Plum', 'code' => '#7A3E65', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']],
                ['color' => 'Blush Rose', 'code' => '#D998AE', 'sizes' => ['S', 'M', 'L', 'XL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Overnight SleepShield Boxer Period Panty',
            'slug' => 'overnight-sleepshield-boxer-period-panty',
            'sku' => 'AFH-PP-002',
            'cat' => 1, 'subcat' => 1, 'coll' => 1,
            'short' => 'Boy-short style with 360-degree front-to-back coverage. Zero side-leaks even if you sleep on your tummy or sides.',
            'desc' => "Sleep in whatever position you love. The SleepShield Boxer features an extra-long gusset that extends all the way from the front waistband to the back rim. Made with ultra-soft modal fabric that stretches with your body without pinching your inner thighs.",
            'mat' => 'Body: 92% MicroModal, 8% Elastane. Gusset: 4-Layer Antibacterial Leakproof Core.',
            'care' => 'Cold rinse -> gentle cycle wash -> air dry. Do not iron or dry clean.',
            'hygiene' => 'Intimate apparel hygiene standards apply. Try on over regular underwear to test fit.',
            'mrp' => 1399, 'price' => 999, 'stock' => 120, 'featured' => 1, 'bestseller' => 1, 'new' => 1, 'rating' => 4.8, 'reviews' => 134,
            'variants' => [
                ['color' => 'Charcoal Grey', 'code' => '#3D3D42', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']],
                ['color' => 'Deep Mauve', 'code' => '#8B5E83', 'sizes' => ['M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Seamless Mid-Waist Period Hipster',
            'slug' => 'seamless-mid-waist-period-hipster',
            'sku' => 'AFH-PP-003',
            'cat' => 1, 'subcat' => 2, 'coll' => 1,
            'short' => 'Bonded laser-cut edges for invisible zero panty lines. Absorbs up to 25ml for medium flow days.',
            'desc' => "Wear your favourite leggings, tight yoga pants, or fitted churidars with zero telltale underwear lines or bulky pad bulges. The AFOREHEALTH Seamless Mid-Waist Period Hipster combines laser-bonded edges with an ultra-thin 3-layer absorbent core.",
            'mat' => 'Body: 80% Polyamide, 20% Elastane. Gusset: 100% Breathable Cotton.',
            'care' => 'Hand wash or gentle machine wash inside a laundry mesh bag. Dry away from direct heating elements.',
            'hygiene' => 'Hygienically packaged. Anti-odour silver-ion weave technology.',
            'mrp' => 1099, 'price' => 799, 'stock' => 150, 'featured' => 1, 'bestseller' => 0, 'new' => 1, 'rating' => 4.7, 'reviews' => 92,
            'variants' => [
                ['color' => 'Nude Beige', 'code' => '#E5C2B1', 'sizes' => ['S', 'M', 'L', 'XL']],
                ['color' => 'Midnight Noir', 'code' => '#1F1E24', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Organic Bamboo Cotton Day-Flow Brief',
            'slug' => 'organic-bamboo-cotton-day-flow-brief',
            'sku' => 'AFH-PP-004',
            'cat' => 1, 'subcat' => 2, 'coll' => 0,
            'short' => 'Naturally antibacterial bamboo cotton. Ideal for workday confidence, office hours, and commutes.',
            'desc' => "Crafted from organically grown bamboo fibres that stay 2-3 degrees cooler than synthetic fabrics, making it an absolute lifesaver for humid Indian summers and long days away from home.",
            'mat' => '95% Certified Organic Bamboo, 5% Spandex. 3-layer absorbent gusset.',
            'care' => 'Cold water rinse, mild liquid detergent, line dry.',
            'hygiene' => 'Dermatologist tested. Hypoallergenic and naturally odour-neutralizing.',
            'mrp' => 999, 'price' => 699, 'stock' => 160, 'featured' => 0, 'bestseller' => 1, 'new' => 0, 'rating' => 4.8, 'reviews' => 180,
            'variants' => [
                ['color' => 'Sage Eucalyptus', 'code' => '#4E8B68', 'sizes' => ['S', 'M', 'L', 'XL']],
                ['color' => 'Blush Rose', 'code' => '#E8B7C8', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH High-Rise Tummy Support Period Panty',
            'slug' => 'high-rise-tummy-support-period-panty',
            'sku' => 'AFH-PP-005',
            'cat' => 1, 'subcat' => 1, 'coll' => 4,
            'short' => 'Gentle abdominal compression provides comforting warm embrace for menstrual cramps and bloating.',
            'desc' => "Periods often bring bloating and menstrual cramps. Our High-Rise Tummy Support Period Panty features a double-layered compressive front panel that gently supports your lower belly while offering heavy-flow 35ml absorbency.",
            'mat' => 'Body: 88% Cotton, 12% Spandex. Panel: Reinforced micro-compression band.',
            'care' => 'Gentle cold wash, air dry in shade.',
            'hygiene' => 'Supports post-delivery lochia bleeding and high-cramp cycles.',
            'mrp' => 1299, 'price' => 899, 'stock' => 95, 'featured' => 0, 'bestseller' => 1, 'new' => 0, 'rating' => 4.9, 'reviews' => 110,
            'variants' => [
                ['color' => 'Midnight Noir', 'code' => '#1F1E24', 'sizes' => ['M', 'L', 'XL', 'XXL', '3XL']],
                ['color' => 'Heather Plum', 'code' => '#8B5E83', 'sizes' => ['M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Active-Move Sport Period Short',
            'slug' => 'active-move-sport-period-short',
            'sku' => 'AFH-PP-006',
            'cat' => 1, 'subcat' => 2, 'coll' => 0,
            'short' => 'Anti-chafing thigh panels and stay-put waistband for running, gym, yoga, and badminton.',
            'desc' => "Never skip a workout because of your period. Designed with a wide ergonomic waistband that will not roll down during squats or runs, plus breathable mesh side vents that prevent sweat buildup.",
            'mat' => '85% Recycled Polyester, 15% Spandex with moisture-wicking quick-dry treatment.',
            'care' => 'Machine wash cold. Do not tumble dry.',
            'hygiene' => 'Treated with antimicrobial zinc pyrithione to control odour during strenuous workouts.',
            'mrp' => 1349, 'price' => 949, 'stock' => 80, 'featured' => 0, 'bestseller' => 0, 'new' => 1, 'rating' => 4.6, 'reviews' => 64,
            'variants' => [
                ['color' => 'Obsidian Black', 'code' => '#212121', 'sizes' => ['S', 'M', 'L', 'XL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Teens First-Period Super-Soft Panty',
            'slug' => 'teens-first-period-super-soft-panty',
            'sku' => 'AFH-PP-007',
            'cat' => 1, 'subcat' => 2, 'coll' => 3,
            'short' => 'Designed specifically for schoolgirls and young teens. Fits growing hip contours without fear of stains.',
            'desc' => "School uniforms and unpredictable cycles can create intense anxiety for young girls. This ultra-gentle period panty looks and feels like cute regular underwear while offering fail-safe medium-flow absorption that easily lasts through a full school day.",
            'mat' => '95% Pure combed cotton, 5% Lycra. Soft-brushed elastic waistband.',
            'care' => 'Easy cold hand wash, safe for teenage independence.',
            'hygiene' => 'Zero chemical fragrances. Non-toxic organic dyes.',
            'mrp' => 899, 'price' => 599, 'stock' => 140, 'featured' => 0, 'bestseller' => 1, 'new' => 0, 'rating' => 5.0, 'reviews' => 178,
            'variants' => [
                ['color' => 'Pastel Peach', 'code' => '#FAD4C0', 'sizes' => ['XS', 'S', 'M']],
                ['color' => 'Lavender Mist', 'code' => '#D8BFD8', 'sizes' => ['XS', 'S', 'M']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Postpartum Leak-Shield Recovery Panty',
            'slug' => 'postpartum-leak-shield-recovery-panty',
            'sku' => 'AFH-PP-008',
            'cat' => 1, 'subcat' => 1, 'coll' => 4,
            'short' => 'Full-rise C-section friendly high waist. Absorbs 50ml of postpartum flow without pressing on incision scars.',
            'desc' => "Created in consultation with maternal health physiotherapists. The soft, non-binding waistband sits well above C-section incision sites, protecting sensitive stitches while safely absorbing lochia bleeding.",
            'mat' => '100% Breathable Organic Cotton body with medical-grade leakproof membrane.',
            'care' => 'Sanitize with cold rinse, warm wash, shade dry.',
            'hygiene' => 'Extra-wide absorbent cradle to protect from sudden gushes when standing up.',
            'mrp' => 1499, 'price' => 1099, 'stock' => 75, 'featured' => 0, 'bestseller' => 0, 'new' => 1, 'rating' => 4.9, 'reviews' => 88,
            'variants' => [
                ['color' => 'Warm Beige', 'code' => '#D2B48C', 'sizes' => ['L', 'XL', 'XXL', '3XL']],
                ['color' => 'Midnight Noir', 'code' => '#1F1E24', 'sizes' => ['L', 'XL', 'XXL', '3XL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Ultra-Thin Light Flow Spotting Panty',
            'slug' => 'ultra-thin-light-flow-spotting-panty',
            'sku' => 'AFH-PP-009',
            'cat' => 1, 'subcat' => 3, 'coll' => 0,
            'short' => 'Micro-thin 2-layer gusset (10ml). Replaces single-use panty liners for ovulation discharge and cycle tail-ends.',
            'desc' => "Throw away scratchy disposable panty liners forever. This lightweight panty feels like luxurious everyday lingerie while catching unexpected spots, ovulation discharge, and light sneezes/cough leaks.",
            'mat' => '90% Nylon, 10% Elastane with 100% cotton inner gusset.',
            'care' => 'Standard machine wash cold.',
            'hygiene' => 'Eliminates liner irritation, sweat chaffing, and plastic waste.',
            'mrp' => 849, 'price' => 549, 'stock' => 190, 'featured' => 0, 'bestseller' => 0, 'new' => 0, 'rating' => 4.7, 'reviews' => 71,
            'variants' => [
                ['color' => 'Dusty Rose', 'code' => '#C48B9F', 'sizes' => ['S', 'M', 'L', 'XL']],
                ['color' => 'Soft Nude', 'code' => '#E8C5B0', 'sizes' => ['S', 'M', 'L', 'XL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Breathable Lace-Trim Moderate Flow Panty',
            'slug' => 'breathable-lace-trim-moderate-flow-panty',
            'sku' => 'AFH-PP-010',
            'cat' => 1, 'subcat' => 2, 'coll' => 0,
            'short' => 'Elegant French lace waist detailing meets 20ml leakproof safety. Look feminine and feel confident.',
            'desc' => "Who says period underwear has to look clinical? Our Lace-Trim Moderate Flow Panty features itch-free scalloped lace along the waistline, paired with full bottom coverage and trustworthy leak defense.",
            'mat' => '92% Bamboo Rayon, 8% Spandex with soft polyamide lace trim.',
            'care' => 'Wash cold inside a wash bag to protect delicate lace.',
            'hygiene' => 'Ultra-gentle against pubic area skin with no itchy tags.',
            'mrp' => 1149, 'price' => 799, 'stock' => 110, 'featured' => 1, 'bestseller' => 0, 'new' => 1, 'rating' => 4.8, 'reviews' => 53,
            'variants' => [
                ['color' => 'Wine Burgundy', 'code' => '#5C1D2D', 'sizes' => ['S', 'M', 'L', 'XL']],
                ['color' => 'Classic Black', 'code' => '#1A1A1A', 'sizes' => ['S', 'M', 'L', 'XL']]
            ]
        ],

        // 8 Everyday Women's Underwear
        [
            'name' => 'AFOREHEALTH CloudSoft MicroModal Hipster Underwear (Pack of 2)',
            'slug' => 'cloudsoft-micromodal-hipster-pack-of-2',
            'sku' => 'AFH-EW-011',
            'cat' => 2, 'subcat' => 4, 'coll' => 1,
            'short' => '3x softer than cotton. 4-way stretch that stays in place all day without rolling or pinching.',
            'desc' => "Crafted from sustainable beechwood modal that drapes like silk and breathes like linen. Designed for everyday non-period wear to ensure your intimate comfort is never compromised.",
            'mat' => '94% Lenzing MicroModal, 6% Elastane. 100% organic cotton gusset.',
            'care' => 'Machine wash warm, tumble dry low or line dry.',
            'hygiene' => 'Certified OEKO-TEX Standard 100, free from harmful dyes.',
            'mrp' => 999, 'price' => 699, 'stock' => 240, 'featured' => 1, 'bestseller' => 1, 'new' => 0, 'rating' => 4.9, 'reviews' => 312,
            'variants' => [
                ['color' => 'Blush & Noir', 'code' => '#D998AE', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']],
                ['color' => 'Nude & Mauve', 'code' => '#8B5E83', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Zero-Line Pure Cotton Seamless Bikini Panty',
            'slug' => 'zero-line-pure-cotton-seamless-bikini-panty',
            'sku' => 'AFH-EW-012',
            'cat' => 2, 'subcat' => 5, 'coll' => 0,
            'short' => 'Featherlight bonded hems. Absolutely invisible under fitted Indian ethnic churidars and trousers.',
            'desc' => "Engineered for hot days when you want zero bulk. The seamless ultrasonic edges prevent skin redness, pressure marks, and chafing.",
            'mat' => '90% Combed Cotton, 10% Spandex.',
            'care' => 'Cold wash, shade dry.',
            'hygiene' => 'Double-layered cotton crotch lining.',
            'mrp' => 599, 'price' => 399, 'stock' => 200, 'featured' => 0, 'bestseller' => 1, 'new' => 0, 'rating' => 4.7, 'reviews' => 140,
            'variants' => [
                ['color' => 'Skin Beige', 'code' => '#E2BA9B', 'sizes' => ['S', 'M', 'L', 'XL']],
                ['color' => 'Soft Lavender', 'code' => '#CCA9DD', 'sizes' => ['S', 'M', 'L', 'XL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Breathable Bamboo Mid-Rise Everyday Brief',
            'slug' => 'breathable-bamboo-mid-rise-everyday-brief',
            'sku' => 'AFH-EW-013',
            'cat' => 2, 'subcat' => 4, 'coll' => 0,
            'short' => 'Thermoregulating organic bamboo fabric keeps you fresh and dry in tropical weather.',
            'desc' => "Mid-rise cut that sits naturally at your hip bones. Broad waistband made from self-fabric instead of harsh exposed elastic, eliminating muffin-top pressure.",
            'mat' => '95% Organic Bamboo Viscose, 5% Elastane.',
            'care' => 'Gentle cycle, cold water, line dry.',
            'hygiene' => 'Naturally antibacterial and moisture wicking.',
            'mrp' => 649, 'price' => 449, 'stock' => 170, 'featured' => 0, 'bestseller' => 0, 'new' => 0, 'rating' => 4.8, 'reviews' => 95,
            'variants' => [
                ['color' => 'Sage Green', 'code' => '#4E8B68', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Gentle Compression Comfort Shaper Panty',
            'slug' => 'gentle-compression-comfort-shaper-panty',
            'sku' => 'AFH-EW-014',
            'cat' => 2, 'subcat' => 4, 'coll' => 0,
            'short' => 'Smoothens tummy and love handles comfortably without suffocating your breath or squeezing.',
            'desc' => "Unlike harsh shapewear that you can barely wait to take off, our Comfort Shaper provides breathable mid-level contouring designed for all-day wear during long office meetings or family functions.",
            'mat' => '78% Polyamide, 22% Elastane with 100% cotton inner crotch.',
            'care' => 'Hand wash in cool water.',
            'hygiene' => 'Cotton gusset prevents yeast buildup from synthetic shapewear.',
            'mrp' => 1099, 'price' => 749, 'stock' => 130, 'featured' => 0, 'bestseller' => 0, 'new' => 1, 'rating' => 4.6, 'reviews' => 82,
            'variants' => [
                ['color' => 'Black Plum', 'code' => '#2B2028', 'sizes' => ['M', 'L', 'XL', 'XXL']],
                ['color' => 'Honey Nude', 'code' => '#DEC0A2', 'sizes' => ['M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Anti-Chafing Ultra-Light Thigh Shield Short',
            'slug' => 'anti-chafing-ultra-light-thigh-shield-short',
            'sku' => 'AFH-EW-015',
            'cat' => 2, 'subcat' => 4, 'coll' => 1,
            'short' => '6-inch inseam prevents painful inner thigh rubbing under dresses, kurtas, and skirts.',
            'desc' => "Say goodbye to 'chub rub' and painful inner thigh friction. Featherweight breathable fabric glides effortlessly under your outfits while keeping thighs completely irritation-free.",
            'mat' => '86% Nylon, 14% Spandex. Flatlock ergonomic friction-free seams.',
            'care' => 'Machine wash cold.',
            'hygiene' => 'Allows skin to breathe without trapped humidity.',
            'mrp' => 899, 'price' => 599, 'stock' => 190, 'featured' => 1, 'bestseller' => 1, 'new' => 0, 'rating' => 4.9, 'reviews' => 215,
            'variants' => [
                ['color' => 'Midnight Noir', 'code' => '#1F1E24', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL', '3XL']],
                ['color' => 'Warm Sand', 'code' => '#E0C8B0', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL', '3XL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Pure Organic Cotton Antibacterial Everyday Hipster',
            'slug' => 'pure-organic-cotton-antibacterial-everyday-hipster',
            'sku' => 'AFH-EW-016',
            'cat' => 2, 'subcat' => 4, 'coll' => 0,
            'short' => '100% GOTS certified cotton with neem and aloe infused antibacterial bio-wash.',
            'desc' => "Ideal for women prone to recurrent fungal or urinary tract sensitivities. Pure untreated organic cotton allows maximum airflow and zero sweat retention.",
            'mat' => '100% GOTS Certified Organic Cotton.',
            'care' => 'Warm machine wash, sun drying recommended for natural sanitization.',
            'hygiene' => 'Recommended by gynecologists for sensitive intimate skin.',
            'mrp' => 549, 'price' => 379, 'stock' => 210, 'featured' => 0, 'bestseller' => 0, 'new' => 0, 'rating' => 4.8, 'reviews' => 104,
            'variants' => [
                ['color' => 'Cloud White', 'code' => '#F8F9FA', 'sizes' => ['S', 'M', 'L', 'XL']],
                ['color' => 'Soft Lilac', 'code' => '#E6D7EA', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH SilkTouch Featherlight Laser-Cut Boyshort',
            'slug' => 'silktouch-featherlight-laser-cut-boyshort',
            'sku' => 'AFH-EW-017',
            'cat' => 2, 'subcat' => 5, 'coll' => 0,
            'short' => 'Modest cheek coverage boyshort with featherlight feel. Pure second-skin sensation.',
            'desc' => "For those who prefer fuller bottom coverage without compromising on seamless aesthetics. Stays undetectable even under tight white linen pants.",
            'mat' => '82% Polyamide, 18% Elastane.',
            'care' => 'Gentle hand wash.',
            'hygiene' => 'Breathable cotton gusset insert.',
            'mrp' => 749, 'price' => 499, 'stock' => 140, 'featured' => 0, 'bestseller' => 0, 'new' => 1, 'rating' => 4.7, 'reviews' => 61,
            'variants' => [
                ['color' => 'Dusk Mauve', 'code' => '#8B5E83', 'sizes' => ['S', 'M', 'L', 'XL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Second-Skin Maternity & Postpartum Panty',
            'slug' => 'second-skin-maternity-postpartum-panty',
            'sku' => 'AFH-EW-018',
            'cat' => 2, 'subcat' => 4, 'coll' => 4,
            'short' => 'Crossover low-V waistband fits comfortably under expanding pregnancy bumps and postpartum bellies.',
            'desc' => "Specially contoured crossover front waistband sits below your belly without pressing or rolling down. Grows with your body throughout all 3 trimesters.",
            'mat' => '95% Ultra-Soft Cotton, 5% Spandex.',
            'care' => 'Machine wash cold.',
            'hygiene' => 'Ultra-gentle against stretch marks and itching pregnancy skin.',
            'mrp' => 699, 'price' => 499, 'stock' => 125, 'featured' => 0, 'bestseller' => 0, 'new' => 0, 'rating' => 4.9, 'reviews' => 89,
            'variants' => [
                ['color' => 'Pastel Rose', 'code' => '#F4C2D7', 'sizes' => ['M', 'L', 'XL', 'XXL']]
            ]
        ],

        // 5 Period Kits
        [
            'name' => 'AFOREHEALTH First Period Confidence Gift Box (Panty + Pads + Pouch)',
            'slug' => 'first-period-confidence-gift-box',
            'sku' => 'AFH-PK-019',
            'cat' => 3, 'subcat' => 6, 'coll' => 3,
            'short' => 'Complete beginner kit for daughters, nieces, and young girls. Includes 1 Teens Period Panty, 6 Organic Pads, waterproof pouch & booklet.',
            'desc' => "Transform period fear into calm confidence. This beautifully packaged gift box includes everything a young girl needs to welcome her first period without stress: 1 Teens Ultra-Soft Period Panty, 6 Organic Cotton Pads, a discreet zippered emergency carry pouch, and a doctor-vetted Period Care Guide in simple language.",
            'mat' => 'Pure organic cotton and food-grade packaging materials.',
            'care' => 'Includes illustrated wash instructions inside the lid.',
            'hygiene' => 'Individual sterile seal on all absorbent items.',
            'mrp' => 1899, 'price' => 1299, 'stock' => 90, 'featured' => 1, 'bestseller' => 1, 'new' => 0, 'rating' => 5.0, 'reviews' => 165,
            'variants' => [
                ['color' => 'Blush Confetti Box', 'code' => '#E8B7C8', 'sizes' => ['XS (Teens)', 'S (12-14 yrs)', 'M (14-16 yrs)']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH College & Travel Emergency Period Pouch',
            'slug' => 'college-travel-emergency-period-pouch',
            'sku' => 'AFH-PK-020',
            'cat' => 3, 'subcat' => 6, 'coll' => 0,
            'short' => 'Compact waterproof dual-pocket pouch containing 1 Day Period Brief, 2 Intimate Wipes, and 2 Liners.',
            'desc' => "Fits neatly into any handbag, college tote, or backpack. Has two distinct compartments: one clean dry compartment and one smell-proof waterproof zipper pocket to store used panties when on the go.",
            'mat' => 'Waterproof PU exterior with antimicrobial inner lining.',
            'care' => 'Pouch can be wiped clean with damp cloth.',
            'hygiene' => 'Odor-proof airtight zipper design.',
            'mrp' => 1299, 'price' => 899, 'stock' => 110, 'featured' => 0, 'bestseller' => 1, 'new' => 1, 'rating' => 4.8, 'reviews' => 74,
            'variants' => [
                ['color' => 'Lilac Mauve Pouch', 'code' => '#8B5E83', 'sizes' => ['S', 'M', 'L', 'XL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Postpartum 30-Day Complete Healing & Comfort Casket',
            'slug' => 'postpartum-30-day-complete-healing-casket',
            'sku' => 'AFH-PK-021',
            'cat' => 3, 'subcat' => 7, 'coll' => 4,
            'short' => '3 Postpartum High-Rise Leak-Shield Panties, 1 Plant-Based Stain Wash, 1 Soothing Intimate Mist, and 10 Overnight Maxi Pads.',
            'desc' => "Curated by gynecologists for modern Indian mothers. Takes total care of lochia flow, episiotomy/C-section tenderness, and postpartum perineal healing over the first critical month after delivery.",
            'mat' => 'Certified Organic Cotton, Ayurvedic herbal extracts.',
            'care' => 'Full instructions included inside box.',
            'hygiene' => 'Formulated strictly without artificial perfumes, sulfates, or parabens.',
            'mrp' => 3999, 'price' => 2699, 'stock' => 45, 'featured' => 1, 'bestseller' => 0, 'new' => 1, 'rating' => 4.9, 'reviews' => 52,
            'variants' => [
                ['color' => 'Earth Rose Gift Set', 'code' => '#C48B9F', 'sizes' => ['M', 'L', 'XL', 'XXL', '3XL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Sustainable Zero-Waste Period Transition Kit',
            'slug' => 'sustainable-zero-waste-period-transition-kit',
            'sku' => 'AFH-PK-022',
            'cat' => 3, 'subcat' => 6, 'coll' => 0,
            'short' => '2 Period Panties (1 Heavy + 1 Medium), 2 Reusable Bamboo Cloth Pads, and 1 Plant-Based Wash Detergent.',
            'desc' => "Ready to make your period 100% plastic-free and zero-waste? This starter bundle replaces over 350 disposable plastic sanitary pads across the next 2-3 years, saving you money while saving our landfills.",
            'mat' => 'Organic Bamboo, Charcoal Fleece, Biodegradable packaging.',
            'care' => 'Wash and reuse 100+ times.',
            'hygiene' => 'Naturally antibacterial and zero toxic microplastics against your skin.',
            'mrp' => 2899, 'price' => 1999, 'stock' => 60, 'featured' => 0, 'bestseller' => 0, 'new' => 0, 'rating' => 4.9, 'reviews' => 47,
            'variants' => [
                ['color' => 'Eco Sage & Black', 'code' => '#4E8B68', 'sizes' => ['S', 'M', 'L', 'XL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Heavy-Flow Workday Essentials Survival Pouch',
            'slug' => 'heavy-flow-workday-essentials-survival-pouch',
            'sku' => 'AFH-PK-023',
            'cat' => 3, 'subcat' => 6, 'coll' => 0,
            'short' => 'Includes 1 Heavy Flow Period Panty, 1 pH-Balancing Intimate Wipe pack, and 1 Emergency Discreet Wet-Bag.',
            'desc' => "Engineered for 10-hour corporate shifts, flights, train journeys, and field trips. Gives you absolute confidence that you will return home without a single drop of staining.",
            'mat' => 'Spandex, organic cotton, waterproof TPU membrane.',
            'care' => 'Machine wash cold.',
            'hygiene' => 'Leakproof guarantees up to 12 hours.',
            'mrp' => 1599, 'price' => 1149, 'stock' => 85, 'featured' => 0, 'bestseller' => 0, 'new' => 0, 'rating' => 4.7, 'reviews' => 39,
            'variants' => [
                ['color' => 'Jet Black & Pouch', 'code' => '#1F1E24', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']]
            ]
        ],

        // 5 Pads & Panty Liners
        [
            'name' => 'AFOREHEALTH 100% Organic Cotton Ultra-Thin Day Pads with Wings (Pack of 12)',
            'slug' => 'organic-cotton-ultra-thin-day-pads-pack-of-12',
            'sku' => 'AFH-PL-024',
            'cat' => 4, 'subcat' => 8, 'coll' => 1,
            'short' => '280mm regular flow pads. Zero bleach, zero plastic top-sheet, zero friction rashes.',
            'desc' => "Conventional plastic pads create heat, friction, and allergic contact dermatitis. AFOREHEALTH pads feature a certified 100% organic cotton top-sheet that feels like soft cloth, paired with a super-absorbent plant-cellulose core.",
            'mat' => '100% Certified Organic Cotton top sheet, FSC certified wood pulp core, biodegradable cornstarch wrapper.',
            'care' => 'Single use. Dispose responsibly in biodegradable paper envelope provided.',
            'hygiene' => 'Chlorine-free, dioxin-free, fragrance-free.',
            'mrp' => 299, 'price' => 229, 'stock' => 350, 'featured' => 1, 'bestseller' => 1, 'new' => 0, 'rating' => 4.9, 'reviews' => 418,
            'variants' => [
                ['color' => 'Pack of 12', 'code' => '#E8B7C8', 'sizes' => ['Regular (280mm)']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Overnight Maxi Extra-Long Anion Antibacterial Pads (Pack of 10)',
            'slug' => 'overnight-maxi-extra-long-pads-pack-of-10',
            'sku' => 'AFH-PL-025',
            'cat' => 4, 'subcat' => 8, 'coll' => 1,
            'short' => '350mm extra-long with wide butterfly back wings. Green negative-ion antibacterial chip inhibits odor.',
            'desc' => "Engineered for peaceful sleep during heavy nights. The expanded back fan catches back-flow leaks while you sleep on your back, while the anion strip neutralizes odor-causing bacteria naturally.",
            'mat' => 'Organic cotton, active oxygen & anion chip, breathable backsheet.',
            'care' => 'Wrap in disposal wrapper and discard in bin.',
            'hygiene' => 'Absorbs up to 180ml without reverse osmosis.',
            'mrp' => 349, 'price' => 269, 'stock' => 300, 'featured' => 0, 'bestseller' => 1, 'new' => 0, 'rating' => 4.8, 'reviews' => 280,
            'variants' => [
                ['color' => 'Pack of 10', 'code' => '#8B5E83', 'sizes' => ['XL Night (350mm)']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Pure Bamboo Ultra-Slim Daily Panty Liners (Pack of 30)',
            'slug' => 'pure-bamboo-ultra-slim-daily-panty-liners-pack-of-30',
            'sku' => 'AFH-PL-026',
            'cat' => 4, 'subcat' => 8, 'coll' => 0,
            'short' => '155mm breathable liners for discharge, spotting, and sweat defense. 0.8mm ultra-thin comfort.',
            'desc' => "Feels barely there. Pure bamboo fibers naturally wick daily sweat and vaginal discharge, keeping your expensive everyday underwear stain-free and fresh.",
            'mat' => '100% Bamboo fiber surface, breathable plant-based backing.',
            'care' => 'Change every 4-6 hours for optimal intimate hygiene.',
            'hygiene' => 'Individual sterile pocket wraps.',
            'mrp' => 279, 'price' => 199, 'stock' => 400, 'featured' => 0, 'bestseller' => 1, 'new' => 0, 'rating' => 4.7, 'reviews' => 190,
            'variants' => [
                ['color' => 'Pack of 30', 'code' => '#E0C8B0', 'sizes' => ['155mm Slim']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Reusable Washable Bamboo-Charcoal Heavy Flow Cloth Pad',
            'slug' => 'reusable-washable-bamboo-charcoal-cloth-pad',
            'sku' => 'AFH-PL-027',
            'cat' => 4, 'subcat' => 8, 'coll' => 0,
            'short' => 'Washable for up to 3 years. Dark charcoal fleece hides stains and snaps securely around underwear wings.',
            'desc' => "For women looking for reusable options outside period panties. The dark bamboo charcoal layer neutralizes odors naturally and doesn't show unsightly period stains.",
            'mat' => 'Top: Bamboo charcoal fleece. Middle: 2-layer microfibre. Back: Waterproof printed PUL.',
            'care' => 'Soak in cold water, hand wash or machine wash.',
            'hygiene' => 'Hypoallergenic, no chlorine bleaching.',
            'mrp' => 399, 'price' => 279, 'stock' => 160, 'featured' => 0, 'bestseller' => 0, 'new' => 0, 'rating' => 4.6, 'reviews' => 85,
            'variants' => [
                ['color' => 'Botanical Floral', 'code' => '#4E8B68', 'sizes' => ['Large (30cm)']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Biodegradable Corn & Bamboo Fiber Rash-Free Pads (Pack of 15)',
            'slug' => 'biodegradable-corn-bamboo-rash-free-pads-pack-of-15',
            'sku' => 'AFH-PL-028',
            'cat' => 4, 'subcat' => 8, 'coll' => 0,
            'short' => '100% compostable within 6 months. Ultra-soft thermal bonded top sheet.',
            'desc' => "Crafted from corn starch PLA and natural bamboo. Decomposes naturally without leaving microplastics behind in the soil.",
            'mat' => 'Corn PLA fiber, organic bamboo pulp.',
            'care' => 'Single use biodegradable.',
            'hygiene' => 'Eco-certified, skin friendly.',
            'mrp' => 389, 'price' => 299, 'stock' => 220, 'featured' => 0, 'bestseller' => 0, 'new' => 1, 'rating' => 4.8, 'reviews' => 99,
            'variants' => [
                ['color' => 'Pack of 15', 'code' => '#E8B7C8', 'sizes' => ['Mixed (Day & Night)']]
            ]
        ],

        // 5 Intimate Hygiene Products
        [
            'name' => 'AFOREHEALTH Gentle pH 3.8 Daily Intimate Foam Wash (Tea Tree & Chamomile, 150ml)',
            'slug' => 'gentle-ph-38-daily-intimate-foam-wash-150ml',
            'sku' => 'AFH-IH-029',
            'cat' => 5, 'subcat' => 9, 'coll' => 1,
            'short' => 'Maintains natural acidic vaginal barrier. Sulfate-free, paraben-free, gyno-tested for daily use & periods.',
            'desc' => "Regular soaps have a high pH of 8-10, stripping the delicate vaginal microbiome and triggering itching and candidiasis. AFOREHEALTH Intimate Foam Wash is precisely formulated at pH 3.8 using mild coconut glucosides, organic tea tree oil, and soothing Roman chamomile.",
            'mat' => 'Purified Aqua, Coco-Glucoside, Lactic Acid, Melaleuca Alternifolia (Tea Tree) Leaf Oil, Chamomilla Recutita Flower Extract, Aloe Barbadensis Leaf Juice.',
            'care' => 'Pump luxurious foam onto wet hands, cleanse external intimate area gently front-to-back, and rinse with lukewarm water. For external use only.',
            'hygiene' => 'Clinically certified hypoallergenic. Free from artificial colourants and parabens.',
            'mrp' => 449, 'price' => 349, 'stock' => 280, 'featured' => 1, 'bestseller' => 1, 'new' => 0, 'rating' => 4.9, 'reviews' => 342,
            'variants' => [
                ['color' => 'Foam Pump 150ml', 'code' => '#4E8B68', 'sizes' => ['150 ml']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Ultra-Soft Flushable Intimate Wet Wipes with Aloe Vera (Pack of 25)',
            'slug' => 'ultra-soft-flushable-intimate-wet-wipes-pack-of-25',
            'sku' => 'AFH-IH-030',
            'cat' => 5, 'subcat' => 9, 'coll' => 0,
            'short' => '100% plant-derived flushable fabric. Instant freshness during period pad changes, post-workout, and travel.',
            'desc' => "Keep fresh wherever your day takes you. Infused with soothing Aloe Vera and Vitamin E, these wipes gently cleanse without stinging or drying your sensitive skin.",
            'mat' => '100% Viscose biodegradable fabric, Aloe Vera, Vitamin E, purified water.',
            'care' => 'Reseal pack after each pull to retain moisture.',
            'hygiene' => 'Alcohol-free and hypoallergenic.',
            'mrp' => 249, 'price' => 179, 'stock' => 310, 'featured' => 0, 'bestseller' => 1, 'new' => 0, 'rating' => 4.8, 'reviews' => 188,
            'variants' => [
                ['color' => 'Pack of 25 Wipes', 'code' => '#8B5E83', 'sizes' => ['25 Wipes']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Natural Anti-Chafing & Rash Soothing Intimate Balm (50g)',
            'slug' => 'natural-anti-chafing-rash-soothing-intimate-balm-50g',
            'sku' => 'AFH-IH-031',
            'cat' => 5, 'subcat' => 9, 'coll' => 0,
            'short' => 'Zinc oxide, shea butter and gotu kola create an invisible barrier against sweat friction and pad rashes.',
            'desc' => "Heals raw inner thighs and bikini line redness within hours. Creates a breathable silky shield that stops skin-on-skin rubbing even during sweltering monsoons and intense humidity.",
            'mat' => 'Shea Butter, Coconut Oil, Zinc Oxide, Centella Asiatica (Gotu Kola), Calendula Extract.',
            'care' => 'Apply a thin layer to clean dry thighs or bikini creases before stepping out.',
            'hygiene' => 'Petroleum-free, talc-free, completely safe for sensitive groin skin.',
            'mrp' => 499, 'price' => 379, 'stock' => 190, 'featured' => 0, 'bestseller' => 0, 'new' => 1, 'rating' => 4.9, 'reviews' => 124,
            'variants' => [
                ['color' => 'Balm Tub 50g', 'code' => '#E0C8B0', 'sizes' => ['50 g']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Plant-Based Period Panty & Cloth Pad Stain Remover Wash (200ml)',
            'slug' => 'plant-based-period-panty-stain-remover-wash-200ml',
            'sku' => 'AFH-IH-032',
            'cat' => 5, 'subcat' => 9, 'coll' => 0,
            'short' => 'Active plant enzymes lift stubborn blood and protein stains without destroying delicate waterproof TPU membranes.',
            'desc' => "Harsh laundry detergents, bleach, and fabric softeners ruin the micro-porous leakproof layer of period panties. Our enzymatic formula is specially engineered to melt blood stains in cold water while extending the lifespan of your period underwear.",
            'mat' => 'Plant-based protease enzymes, coconut surfactants, eucalyptus oil.',
            'care' => 'Apply 2-3 drops directly onto stained gusset, let sit for 5 minutes, rub gently under cold tap water, and rinse.',
            'hygiene' => 'Zero residue formula, safe against your most intimate skin.',
            'mrp' => 399, 'price' => 299, 'stock' => 230, 'featured' => 0, 'bestseller' => 1, 'new' => 0, 'rating' => 4.9, 'reviews' => 210,
            'variants' => [
                ['color' => 'Bottle 200ml', 'code' => '#4E8B68', 'sizes' => ['200 ml']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Intimate Calming Mist for Period Freshness (100ml)',
            'slug' => 'intimate-calming-mist-period-freshness-100ml',
            'sku' => 'AFH-IH-033',
            'cat' => 5, 'subcat' => 9, 'coll' => 0,
            'short' => 'Refreshing spritz with rose water and lavender. Eliminates musty period odor and calms irritation.',
            'desc' => "A gentle spritz of pure botanical relief. Spray onto your intimate area or directly onto underwear for an instant boost of clean, comforting freshness during long office hours or warm travel days.",
            'mat' => 'Steam-distilled Pure Damask Rose Water, Lavender Hydrosol, Witch Hazel Extract.',
            'care' => 'Hold 6 inches away and spray lightly. No rinsing required.',
            'hygiene' => 'Non-aerosol, zero artificial propellants, zero alcohol.',
            'mrp' => 379, 'price' => 269, 'stock' => 175, 'featured' => 0, 'bestseller' => 0, 'new' => 1, 'rating' => 4.7, 'reviews' => 94,
            'variants' => [
                ['color' => 'Spray Bottle 100ml', 'code' => '#D8BFD8', 'sizes' => ['100 ml']]
            ]
        ],

        // 5 Combo Products
        [
            'name' => 'AFOREHEALTH 3-Pack Period Panty Value Bundle (Heavy + Medium + Light)',
            'slug' => '3-pack-period-panty-value-bundle',
            'sku' => 'AFH-CB-034',
            'cat' => 6, 'subcat' => 10, 'coll' => 1,
            'short' => 'Complete menstrual cycle solution: 1 UltraDry Heavy Panty, 1 Day Bamboo Brief, 1 Light Spotting Hipster. Save ₹800!',
            'desc' => "Cover your entire 5-day period from start to finish. Day 1-2 heavy flow? Wear the 4-layer UltraDry. Day 3-4 normal flow? Slip into the breathable Bamboo Day Brief. Day 5 spotting? Enjoy the featherlight Spotting Hipster. The smartest way to switch to leakproof underwear.",
            'mat' => 'Mix of organic bamboo, micro-modal, and breathable waterproof membranes.',
            'care' => 'Cold wash, shade dry.',
            'hygiene' => 'Hygienically pre-packed in our signature AFOREHEALTH storage box.',
            'mrp' => 3047, 'price' => 1999, 'stock' => 120, 'featured' => 1, 'bestseller' => 1, 'new' => 0, 'rating' => 4.9, 'reviews' => 365,
            'variants' => [
                ['color' => 'Classic Trio (Noir, Plum, Blush)', 'code' => '#8B5E83', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH 5-Pack Pure CloudSoft Everyday Cotton Hipster Rainbow Bundle',
            'slug' => '5-pack-pure-cloudsoft-everyday-cotton-hipster-bundle',
            'sku' => 'AFH-CB-035',
            'cat' => 6, 'subcat' => 10, 'coll' => 0,
            'short' => '5 delightful everyday shades: Blush Rose, Sage Eucalyptus, Midnight Noir, Nude Beige, and Dusk Mauve.',
            'desc' => "Upgrade your everyday lingerie drawer with supreme buttery softness. Five premium combed cotton hipsters designed to stay comfy through yoga, work, lounging, and sleep.",
            'mat' => '95% Combed Organic Cotton, 5% Lycra.',
            'care' => 'Machine wash warm.',
            'hygiene' => 'Hypoallergenic bio-washed cotton.',
            'mrp' => 2499, 'price' => 1499, 'stock' => 140, 'featured' => 1, 'bestseller' => 1, 'new' => 0, 'rating' => 4.9, 'reviews' => 280,
            'variants' => [
                ['color' => 'Assorted Rainbow 5-Pack', 'code' => '#E8B7C8', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Day & Night Period Defense Duo (1 Heavy Panty + 1 Overnight Boxers)',
            'slug' => 'day-night-period-defense-duo',
            'sku' => 'AFH-CB-036',
            'cat' => 6, 'subcat' => 10, 'coll' => 0,
            'short' => 'Confidence around the clock: 1 UltraDry Heavy Panty for office/college + 1 SleepShield Boxer for overnight.',
            'desc' => "Say goodbye to stained pyjamas and ruined mattresses. This powerhouse combo tackles heavy daytime work schedules and guarantees restful 8-hour sleep with zero midnight pad changes.",
            'mat' => 'Organic Bamboo Cotton + Modal SleepShield boxer.',
            'care' => 'Cold water rinse followed by gentle cycle wash.',
            'hygiene' => 'Total 360-degree coverage protection.',
            'mrp' => 2598, 'price' => 1699, 'stock' => 95, 'featured' => 0, 'bestseller' => 1, 'new' => 1, 'rating' => 4.9, 'reviews' => 142,
            'variants' => [
                ['color' => 'Midnight Noir Duo', 'code' => '#1F1E24', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH Complete Intimate Care Duo (Gentle Foam Wash + 25 Intimate Wipes)',
            'slug' => 'complete-intimate-care-duo',
            'sku' => 'AFH-CB-037',
            'cat' => 6, 'subcat' => 10, 'coll' => 0,
            'short' => 'Daily pH 3.8 at-home hygiene + on-the-go purse wipes. Save 25% with this essentials combo.',
            'desc' => "Your complete intimate hygiene regimen. Use the gentle pH 3.8 foaming wash every morning in the shower, and keep the flushable aloe wipes in your bag for fresh clean confidence throughout your day.",
            'mat' => '150ml Foam wash + 25 plant-fiber wipes.',
            'care' => 'Keep bottle and wipes in cool dry place away from direct sunlight.',
            'hygiene' => 'Gynecologist endorsed, 100% soap-free.',
            'mrp' => 698, 'price' => 479, 'stock' => 180, 'featured' => 0, 'bestseller' => 0, 'new' => 0, 'rating' => 4.8, 'reviews' => 119,
            'variants' => [
                ['color' => 'Duo Set', 'code' => '#4E8B68', 'sizes' => ['Standard Combo']]
            ]
        ],
        [
            'name' => 'AFOREHEALTH The Ultimate Confidence Box (2 Period Panties + Stain Wash + Travel Pouch)',
            'slug' => 'the-ultimate-confidence-box',
            'sku' => 'AFH-CB-038',
            'cat' => 6, 'subcat' => 10, 'coll' => 1,
            'short' => 'The complete leakproof master kit: 2 Heavy Period Panties, 1 Plant-Based Stain Wash (200ml), and 1 Waterproof Travel Pouch.',
            'desc' => "Our signature flagship bundle loved by over 20,000 customers. Everything required to maintain, wear, wash, and travel with your period panties with maximum convenience and longevity.",
            'mat' => 'Premium organic fabrics, enzymatic detergent, waterproof PU pouch.',
            'care' => 'Wash and care manual included.',
            'hygiene' => 'Sealed luxury gift packaging.',
            'mrp' => 3197, 'price' => 2199, 'stock' => 85, 'featured' => 1, 'bestseller' => 1, 'new' => 0, 'rating' => 5.0, 'reviews' => 294,
            'variants' => [
                ['color' => 'Signature Plum & Noir', 'code' => '#8B5E83', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL']]
            ]
        ]
    ];

    $stmtInsertProd = $pdo->prepare("
        INSERT INTO `products` (
            `name`, `slug`, `sku`, `category_id`, `subcategory_id`, `collection_id`,
            `short_description`, `description`, `material`, `care_instructions`, `hygiene_notice`,
            `mrp`, `price`, `discount_percent`, `stock`, `low_stock_threshold`,
            `is_featured`, `is_bestseller`, `is_new_arrival`, `rating_avg`, `rating_count`,
            `status`, `primary_image`, `seo_title`, `seo_description`
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, 5,
            ?, ?, ?, ?, ?,
            'published', ?, ?, ?
        )
    ");

    $stmtInsertVar = $pdo->prepare("
        INSERT INTO `product_variants` (`product_id`, `sku`, `color`, `color_code`, `size`, `price`, `mrp`, `stock`, `image`, `status`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");

    $stmtInsertImg = $pdo->prepare("
        INSERT INTO `product_images` (`product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmtInsertInv = $pdo->prepare("
        INSERT INTO `inventory` (`product_id`, `product_variant_id`, `stock`, `reserved_stock`, `available_stock`, `low_stock_threshold`)
        VALUES (?, ?, ?, 0, ?, 5)
    ");

    $stmtInsertInvLog = $pdo->prepare("
        INSERT INTO `inventory_logs` (`product_id`, `product_variant_id`, `sku`, `previous_stock`, `change_amount`, `new_stock`, `reason`, `admin_id`)
        VALUES (?, ?, ?, 0, ?, ?, 'Initial inventory stock intake', 1)
    ");

    foreach ($products as $p) {
        $discountPct = round((($p['mrp'] - $p['price']) / $p['mrp']) * 100);
        $primaryImg = "prod_" . str_replace('-', '_', $p['slug']) . ".svg";
        $seoTitle = $p['name'] . " | AFOREHEALTH";
        $seoDesc = substr(strip_tags($p['short']), 0, 155);

        $stmtInsertProd->execute([
            $p['name'], $p['slug'], $p['sku'], $p['cat'], $p['subcat'], $p['coll'],
            $p['short'], $p['desc'], $p['mat'], $p['care'], $p['hygiene'],
            $p['mrp'], $p['price'], $discountPct, $p['stock'],
            $p['featured'], $p['bestseller'], $p['new'], $p['rating'], $p['reviews'],
            $primaryImg, $seoTitle, $seoDesc
        ]);

        $prodId = $pdo->lastInsertId();

        // Add 2 product images per product
        $stmtInsertImg->execute([$prodId, $primaryImg, $p['name'] . ' Front View', 1, 1]);
        $stmtInsertImg->execute([$prodId, "prod_detail_" . str_replace('-', '_', $p['slug']) . ".svg", $p['name'] . ' Fabric & Gusset Detail', 0, 2]);

        // Add variants
        $varCount = 0;
        foreach ($p['variants'] as $varGroup) {
            foreach ($varGroup['sizes'] as $sz) {
                $varCount++;
                $varSku = $p['sku'] . '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $varGroup['color']), 0, 3)) . '-' . $sz;
                $varStock = max(10, round($p['stock'] / (count($p['variants']) * count($varGroup['sizes']))));

                $stmtInsertVar->execute([
                    $prodId, $varSku, $varGroup['color'], $varGroup['code'], $sz,
                    $p['price'], $p['mrp'], $varStock, $primaryImg
                ]);
                $varId = $pdo->lastInsertId();

                // Add inventory
                $stmtInsertInv->execute([$prodId, $varId, $varStock, $varStock]);
                $stmtInsertInvLog->execute([$prodId, $varId, $varSku, $varStock, $varStock]);
            }
        }
    }
    echo "Seeded 38 realistic products with variants, images, and inventory logs.\n";

    // 9. Users
    $pdo->exec("TRUNCATE TABLE `users`;");
    $userPassword = password_hash('Customer@123', PASSWORD_DEFAULT);
    $users = [
        ['Priya Sharma', '9876543210', 'priya.sharma@example.com', $userPassword, '1995-04-12', 'female'],
        ['Ananya Patel', '9811122233', 'ananya.p@example.com', $userPassword, '1998-09-24', 'female'],
        ['Dr. Radhika Sen', '9822233344', 'radhika.sen@example.com', $userPassword, '1990-11-05', 'female'],
        ['Sneha Roy', '9833344455', 'sneha.roy@example.com', $userPassword, '2001-02-18', 'female'],
        ['Pooja Deshmukh', '9844455566', 'pooja.d@example.com', $userPassword, '1993-07-30', 'female']
    ];
    $stmtUser = $pdo->prepare("INSERT INTO `users` (`name`, `mobile`, `email`, `password`, `dob`, `gender`, `status`) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    foreach ($users as $u) {
        $stmtUser->execute($u);
    }
    echo "Seeded demo customer accounts (Mobile: 9876543210 / Password: Customer@123).\n";

    // 10. Addresses
    $pdo->exec("TRUNCATE TABLE `addresses`;");
    $addresses = [
        [1, 'Priya Sharma', '9876543210', 'Flat 402, Sunshine Heights', 'Linking Road, Bandra West', 'Near National College', 'Mumbai', 'Maharashtra', '400050', 'home', 1],
        [1, 'Priya Sharma (Office)', '9876543210', 'Floor 8, Tower B, Godrej BKC', 'G-Block, BKC', 'Opp. Citi Bank', 'Mumbai', 'Maharashtra', '400051', 'work', 0],
        [2, 'Ananya Patel', '9811122233', 'Villa 12, Palm Meadows', 'Whitefield Main Road', 'Behind Forum Mall', 'Bengaluru', 'Karnataka', '560066', 'home', 1],
        [3, 'Dr. Radhika Sen', '9822233344', 'C-14, Green Park Extension', 'South Delhi', 'Near Metro Station', 'New Delhi', 'Delhi', '110016', 'home', 1]
    ];
    $stmtAddr = $pdo->prepare("INSERT INTO `addresses` (`user_id`, `full_name`, `mobile`, `house_flat`, `area`, `landmark`, `city`, `state`, `pincode`, `address_type`, `is_default`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($addresses as $a) {
        $stmtAddr->execute($a);
    }
    echo "Seeded customer addresses.\n";

    // 11. Coupons
    $pdo->exec("TRUNCATE TABLE `coupons`;");
    $coupons = [
        ['WELCOME10', 'Welcome 10% Off on First Order', 'percentage', 10.00, 499.00, 200.00, '2026-01-01', '2026-12-31', 5000, 1, NULL, 1, 1, 'active'],
        ['WELCOME100', 'Welcome Flat ₹100 Off on First Order', 'fixed', 100.00, 799.00, 100.00, '2026-01-01', '2026-12-31', 5000, 1, NULL, 1, 1, 'active'],
        ['FLAT100', 'Flat ₹100 Off on Orders above ₹799', 'fixed', 100.00, 799.00, 100.00, '2026-01-01', '2026-12-31', 2000, 2, NULL, 0, 1, 'active'],
        ['PERIOD20', '20% Off on Period Care Essentials', 'percentage', 20.00, 999.00, 300.00, '2026-01-01', '2026-12-31', 1000, 1, '1,3,4', 0, 1, 'active'],
        ['FREESHIP', 'Free Shipping on Any Order', 'fixed', 50.00, 0.00, 50.00, '2026-01-01', '2026-12-31', 5000, 5, NULL, 0, 1, 'active']
    ];
    $stmtCoup = $pdo->prepare("
        INSERT INTO `coupons` (`code`, `title`, `discount_type`, `discount_value`, `min_order_amount`, `max_discount_amount`, `start_date`, `expiry_date`, `usage_limit`, `per_customer_limit`, `applicable_categories`, `first_order_only`, `cod_allowed`, `status`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    foreach ($coupons as $cp) {
        $stmtCoup->execute($cp);
    }
    echo "Seeded coupons (WELCOME10, WELCOME100, FLAT100, PERIOD20, FREESHIP).\n";

    // 12. Reviews (Approved and Pending)
    $pdo->exec("TRUNCATE TABLE `reviews`;");
    $reviews = [
        // Approved reviews for Product 1 (UltraDry Heavy Flow)
        [1, 1, NULL, 5, 'Absolute life saver! Zero stains on white sheets', 'I was very skeptical about period panties for my heavy Day 2 flow. I wore this overnight and woke up with dry sheets and no weird pad odor. Washing was super simple under cold water. Totally hooked and ordered two more pairs!', 'approved'],
        [1, 2, NULL, 5, 'Best investment for Indian women', 'The fabric feels so luxurious against the skin compared to synthetic pads that gave me horrible chafing rashes every month. Truly leakproof and comfortable!', 'approved'],
        [1, 3, NULL, 5, 'Gynecologist approved comfort', 'As a healthcare worker doing 14 hour shifts, frequent pad changes were a nightmare. This panty gave me total peace of mind throughout my hospital rounds.', 'approved'],
        // Approved for Product 2 (Overnight Boxer)
        [2, 4, NULL, 5, 'Sleep in any posture with zero fear', 'The back coverage goes all the way up. No side leaks even when tossing and turning. Super soft modal waist band that does not pinch.', 'approved'],
        // Approved for Product 11 (Everyday Hipster 2-pack)
        [11, 1, NULL, 5, 'Unbelievable buttery softness', 'These are softer than anything I have bought from international brands. Pure comfort, zero wedgies and no panty lines.', 'approved'],
        // Approved for Product 29 (Gentle Foam Wash)
        [29, 2, NULL, 5, 'Gentle and soothing, no burning sensation', 'Most washes have harsh artificial perfume. This tea tree & chamomile foam has a very subtle natural clean scent and soothes itching immediately.', 'approved'],
        // Pending Review for Product 1 (Moderation demo)
        [1, 5, NULL, 4, 'Great absorbency, wish there were more color options', 'Worked wonderfully during heavy cycle. Would love to see pastel blue and lavender colors launched soon!', 'pending']
    ];
    $stmtRev = $pdo->prepare("INSERT INTO `reviews` (`product_id`, `user_id`, `order_id`, `rating`, `title`, `comment`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($reviews as $rv) {
        $stmtRev->execute($rv);
    }
    echo "Seeded approved and pending product reviews.\n";

    // 13. Blog Categories & Posts
    $pdo->exec("TRUNCATE TABLE `blog_categories`;");
    $pdo->exec("TRUNCATE TABLE `blog_posts`;");
    $blogCats = [
        [1, 'Period Health & Science', 'period-health-science'],
        [2, 'Product & Sizing Guides', 'product-sizing-guides'],
        [3, 'Intimate Hygiene & Wellness', 'intimate-hygiene-wellness'],
        [4, 'Sustainable Living', 'sustainable-living']
    ];
    $stmtBC = $pdo->prepare("INSERT INTO `blog_categories` (`id`, `name`, `slug`) VALUES (?, ?, ?)");
    foreach ($blogCats as $bc) {
        $stmtBC->execute($bc);
    }

    $blogPosts = [
        [
            1, 'How Do Period Panties Actually Work? The 4-Layer Leakproof Science Explained',
            'how-do-period-panties-work-leakproof-science',
            'Curious how a pair of underwear can replace bulky plastic sanitary pads without leaking or feeling wet? Here is the complete breakdown of AFOREHEALTH gusset engineering.',
            "<p>For decades, women in India have relied on single-use plastic sanitary pads, enduring chafing, rustling sounds, rashes, and the constant fear of sudden leaks. The arrival of reusable leakproof period underwear represents the biggest innovation in menstrual hygiene in half a century.</p><h3>The 4-Layer Gusset Architecture</h3><p>At AFOREHEALTH, our period panties utilize an advanced 4-layer micro-textile composite that works harmoniously:</p><ol><li><strong>Layer 1 (Skin Contact): Moisture-Wicking Cotton:</strong> Draws moisture away from your body in under 3 seconds, keeping your surface completely dry and fresh.</li><li><strong>Layer 2 (Absorption Core): High-Density Microfiber:</strong> Locks up to 40ml of menstrual fluid, preventing backward seepage under pressure.</li><li><strong>Layer 3 (The Shield): Breathable Waterproof TPU Membrane:</strong> A microscopic barrier that blocks liquid molecules while allowing heat and water vapor to escape, eliminating sweat and odor.</li><li><strong>Layer 4 (Outer Shell): Luxurious Bamboo / Modal Fabric:</strong> Drapes softly against your outer clothing with zero telltale bulk or rustle.</li></ol><h3>Washing Is Surprisingly Easy</h3><p>Simply rinse under cold tap water until the runoff is clear, then pop it in your gentle cycle wash. Hang to air-dry in shade, and it is ready to protect you again for 3+ years.</p>",
            'blog_science_period_panties.svg'
        ],
        [
            2, 'Finding Your Perfect Fit: The Ultimate Period Panty Size Guide for Indian Bodies',
            'how-to-choose-period-panty-size-guide-indian-women',
            'Why Indian hip-to-waist ratios differ from Western size charts, and how to measure yourself for zero gaps and all-day comfort.',
            "<p>Choosing the correct size in period underwear is critical. Unlike regular stretchy panties that you can wear loose, period panties need to sit snug against your groin creases to create an airtight seal against gravity leaks, yet never pinch your thighs.</p><h3>How to Measure Your Hips</h3><p>Take a soft measuring tape and wrap it around the fullest part of your buttocks/hips (usually 8 inches below your natural waistline). Match your measurements:</p><ul><li><strong>Small (S):</strong> Hips 34 - 36 inches (Waist 26 - 28)</li><li><strong>Medium (M):</strong> Hips 37 - 39 inches (Waist 29 - 31)</li><li><strong>Large (L):</strong> Hips 40 - 42 inches (Waist 32 - 34)</li><li><strong>Extra Large (XL):</strong> Hips 43 - 45 inches (Waist 35 - 37)</li><li><strong>Double XL (XXL):</strong> Hips 46 - 48 inches (Waist 38 - 40)</li><li><strong>3XL:</strong> Hips 49 - 52 inches (Waist 41 - 44)</li></ul><p>If you fall between two sizes and have curvier thighs, we recommend sizing up for effortless comfort.</p>",
            'blog_size_guide.svg'
        ],
        [
            3, 'The pH 3.8 Rule: Why Regular Body Soaps Harm Your Vaginal Health',
            'why-regular-soaps-harm-vaginal-ph-intimate-hygiene',
            'Understand why soaps cause itching, yeast infections, and odor, and how a gentle pH-balanced lactic acid wash protects your natural microflora.',
            "<p>The intimate area is one of the most delicate biological ecosystems in the human body. Unlike the rest of your skin, which has a neutral pH of 5.5, a healthy vagina maintains an acidic pH between 3.8 and 4.5. This acidity is created by beneficial <em>Lactobacillus</em> bacteria, which act as natural defenders against harmful yeast and bacterial vaginosis.</p><h3>Why Bathing Soap Is Dangerous</h3><p>Commercial body soaps and shower gels have an alkaline pH between 9.0 and 10.5. Washing your intimate region with these destroys the acidic barrier within seconds, creating an ideal breeding ground for irritation, itchiness, and foul odors.</p><p>Always use a soap-free, dermatologically tested intimate wash formulated specifically at pH 3.8, with natural soothing ingredients like chamomile and tea tree.</p>",
            'blog_intimate_ph.svg'
        ],
        [
            4, 'Breaking Down the 350-Pad Waste Problem: The Environmental Impact of Periods',
            'environmental-impact-single-use-pads-sustainable-period',
            'Each plastic sanitary pad takes up to 800 years to decompose. Here is how switching to reusable period wear can save both money and the planet.',
            "<p>Did you know that an average woman uses approximately 11,000 to 15,000 disposable sanitary pads in her lifetime? In India alone, over 12 billion plastic pads are thrown into municipal landfills or discarded in water bodies every year.</p><p>A single commercial pad contains the plastic equivalent of four shopping carrier bags. By switching to AFOREHEALTH reusable period panties and cloth pads, you can prevent thousands of plastic items from reaching our water systems while enjoying unmatched daily comfort.</p>",
            'blog_eco_periods.svg'
        ]
    ];
    $stmtBP = $pdo->prepare("INSERT INTO `blog_posts` (`category_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `status`, `published_at`) VALUES (?, ?, ?, ?, ?, ?, 'published', NOW())");
    foreach ($blogPosts as $bp) {
        $stmtBP->execute($bp);
    }
    echo "Seeded educational blog posts.\n";

    // 14. FAQs
    $pdo->exec("TRUNCATE TABLE `faqs`;");
    $faqs = [
        ['Period Panties', 'How long can I wear an AFOREHEALTH Period Panty before changing?', 'On normal to heavy flow days, our UltraDry 4-layer period panty can be worn safely for 8 to 12 hours. For extraordinarily heavy flows, you can wear it as a backup alongside a menstrual cup or pad, or carry a fresh pair in our waterproof travel pouch.'],
        ['Period Panties', 'Will it smell or feel wet like a sanitary pad?', 'No! Unlike plastic disposable pads that trap moisture and encourage bacteria growth (which causes period odor), our breathable organic cotton top layer wicks fluid into the absorbent core in under 3 seconds, keeping you dry and completely odor-free.'],
        ['Care & Washing', 'How do I wash AFOREHEALTH period panties properly?', 'Step 1: Rinse under cold tap water until the water runs clear. Step 2: Wash in cold water by hand or on a gentle machine cycle with mild detergent. Step 3: Hang to dry in the shade. Never use hot water, bleach, or fabric softener as they can damage the leakproof membrane.'],
        ['Sizing & Fit', 'How do I know what size to order?', 'Refer to our interactive Size Chart on each product page. Measure the widest part of your hips using a measuring tape. If you are between two sizes or have wider hips/thighs, we recommend picking the larger size.'],
        ['Shipping & Discretion', 'Is the packaging discreet? Will delivery personnel know what is inside?', '100% Yes! All AFOREHEALTH orders are shipped in plain, tamper-evident courier polybags or unmarked brown boxes. There is zero mention of intimate apparel, period wear, or internal product names on the outer delivery label.'],
        ['Shipping & Discretion', 'What are the delivery timelines and shipping charges across India?', 'We offer FREE Shipping on all prepaid and COD orders above ₹499. For orders below ₹499, a nominal flat shipping fee of ₹50 is applied. Metro deliveries take 2-3 business days, while other cities receive orders in 3-5 business days.'],
        ['Returns & Hygiene', 'Can I return or exchange intimate wear products?', 'Due to strict intimate hygiene guidelines for women health, products that have been unsealed, worn, or washed cannot be returned. However, if you receive a damaged item or an unopened product with a sizing issue, we offer a hassle-free 7-day exchange window.'],
        ['Payments & COD', 'Is Cash on Delivery (COD) available?', 'Yes! We support Cash on Delivery for orders between ₹299 and ₹5000 across 25,000+ Indian PIN codes. We also accept Razorpay, UPI (Google Pay, PhonePe, Paytm), credit/debit cards, and Net Banking.'],
        ['Intimate Care', 'Can I use the Intimate Foam Wash during my period?', 'Yes, absolutely! The pH 3.8 gentle foam wash is ideal during menstruation to cleanse dried blood and soothe vaginal irritation. Always wash the external vulva only (never douche internally).'],
        ['Wholesale & Combos', 'Do you offer bulk discounts or gift sets?', 'Yes! Our Combos & Value Packs offer up to 35% savings over purchasing individual items, and we provide curated gift hampers like the First Period Box for teens.']
    ];
    $stmtFaq = $pdo->prepare("INSERT INTO `faqs` (`category`, `question`, `answer`, `status`) VALUES (?, ?, ?, 'active')");
    foreach ($faqs as $fq) {
        $stmtFaq->execute($fq);
    }
    echo "Seeded FAQs.\n";

    // 15. Pages (CMS)
    $pdo->exec("TRUNCATE TABLE `pages`;");
    $pages = [
        ['about-us', 'About AFOREHEALTH', "<h2>Everyday Comfort. Period Confidence.</h2><p>AFOREHEALTH was born out of a simple, undeniable truth: modern Indian women deserve intimate care products that truly understand their bodies, climate, and everyday challenges.</p><p>For generations, menstruation has been shrouded in silence and accompanied by scratchy plastic pads, painful rashes, stained clothes, and awkward whispers. We are on a mission to change that narrative completely.</p><h3>Designed for Indian Women, by Women</h3><p>From humid summer commutes in Mumbai to long college lectures in Delhi, our products are thoughtfully engineered with breathable organic bamboo, skin-friendly micro-modal, and medical-grade leakproof barriers tailored specifically to Indian hip-to-waist ratios.</p><p>We believe that when you feel comfortable in your skin and confident in your cycle, nothing can hold you back.</p>"],
        ['shipping-policy', 'Shipping & Delivery Policy', "<h2>Reliable & Discreet Delivery Across India</h2><p>At AFOREHEALTH, we understand the importance of timely and discreet delivery when it comes to intimate wellness essentials.</p><h3>1. Shipping Charges</h3><ul><li><strong>Orders above ₹499:</strong> FREE Shipping across India.</li><li><strong>Orders below ₹499:</strong> Standard flat rate of ₹50.</li><li><strong>Cash on Delivery (COD):</strong> Nominal handling fee of ₹40 applicable on COD orders.</li></ul><h3>2. Delivery Timelines</h3><ul><li>Metro Cities (Mumbai, Delhi NCR, Bengaluru, Hyderabad, Chennai, Kolkata, Pune): <strong>2 to 4 Business Days</strong></li><li>Tier 2 & Tier 3 Cities: <strong>4 to 6 Business Days</strong></li><li>North-East & Remote Locations: <strong>5 to 7 Business Days</strong></li></ul><h3>3. 100% Discreet Packaging Guaranteed</h3><p>Your privacy is sacred to us. All packages are shipped in tamper-evident, unmarked boxes or opaque security bags with zero mention of 'period panties' or 'intimate wear' on the external label.</p>"],
        ['return-refund-policy', 'Return, Exchange & Refund Policy', "<h2>Hygiene First Policy</h2><p>Because intimate wear, period panties, and menstrual care products touch your most sensitive personal areas, strict health and hygiene regulations govern our return process.</p><h3>Eligibility for Returns & Exchanges</h3><ul><li>Products must be completely <strong>unworn, unwashed, and in their original sealed hygienic packaging</strong> with tags intact.</li><li>Requests must be initiated within <strong>7 days</strong> of delivery through your AFOREHEALTH account or by emailing info@aforehealth.com.</li><li>Items with broken hygiene liners or opened seals cannot be returned due to hygiene reasons.</li><li>In the rare event of a manufacturing defect or wrong product dispatched, we will provide a 100% free replacement or full refund immediately.</li></ul><h3>Refund Timelines</h3><p>Once your returned item arrives at our fulfillment facility and clears our hygiene inspection, your refund will be processed within 48 hours to your original payment mode or store wallet.</p>"],
        ['privacy-policy', 'Privacy & Data Protection Policy', "<h2>Your Privacy Matters</h2><p>AFOREHEALTH is committed to safeguarding your personal data with enterprise-grade encryption. We do not sell, rent, or trade your personal information to third parties.</p><h3>Information We Collect</h3><ul><li>Mobile Number & Name (for OTP authentication and order fulfillment)</li><li>Shipping address and contact details</li><li>Anonymous analytics data (browser type, session duration) to enhance your shopping experience</li></ul><h3>Payment Security</h3><p>We do not store your credit card numbers, debit card details, or UPI pins. All online transactions are encrypted via 256-bit SSL and processed directly through PCI-DSS certified gateway Razorpay.</p>"],
        ['terms-conditions', 'Terms & Conditions', "<h2>Terms of Use</h2><p>Welcome to AFOREHEALTH (aforehealth.com). By browsing or purchasing from our platform, you agree to comply with our standard terms of service.</p><h3>Product Usage & Medical Disclaimer</h3><p>Our period care and intimate hygiene products are designed for everyday menstrual management and personal comfort. They are not intended to diagnose, treat, cure, or prevent any medical disease or infection. If you experience abnormal menstrual symptoms or chronic pelvic pain, please consult a registered medical practitioner.</p>"],
        ['cancellation-policy', 'Cancellation Policy', "<h2>Easy Order Cancellation</h2><p>You can cancel your order at any time before it has been dispatched from our fulfillment warehouse.</p><ul><li>To cancel, visit <strong>My Account > My Orders</strong> and click 'Cancel Order', or message our WhatsApp support team at +91 98765 43210.</li><li>If the order has already been shipped, you can refuse acceptance at the time of delivery. Once the parcel returns to us, your refund will be initiated automatically.</li></ul>"]
    ];
    $stmtPage = $pdo->prepare("INSERT INTO `pages` (`slug`, `title`, `content`) VALUES (?, ?, ?)");
    foreach ($pages as $pg) {
        $stmtPage->execute($pg);
    }
    echo "Seeded CMS policy pages.\n";

    // 15.5 Banners (Home Hero & Promo)
    $pdo->exec("TRUNCATE TABLE `banners`;");
    $banners = [
        [
            'title' => 'Comfort That Moves With You.',
            'subtitle' => 'Leakproof underwear, buttery-soft everyday panties, and pH-balanced intimate essentials engineered for Indian bodies.',
            'image_desktop' => 'hero_banner_1.svg',
            'image_mobile' => 'hero_banner_1.svg',
            'video_url' => NULL,
            'link' => '/category/period-panties',
            'cta_text' => 'SHOP PERIOD PANTIES',
            'position' => 'home_hero',
            'sort_order' => 1
        ],
        [
            'title' => 'CloudSoft MicroModal Panties',
            'subtitle' => '3x softer than cotton with breathable 4-way stretch. Zero wedgies, zero pinch, pure everyday luxury.',
            'image_desktop' => 'hero_banner_2.svg',
            'image_mobile' => 'hero_banner_2.svg',
            'video_url' => NULL,
            'link' => '/category/everyday-panties',
            'cta_text' => 'EXPLORE EVERYDAY WEAR',
            'position' => 'home_hero',
            'sort_order' => 2
        ],
        [
            'title' => 'First Period Confidence Kit',
            'subtitle' => 'Empowering teenage girls with leakproof underwear, organic cotton pads, and discreet carry pouches.',
            'image_desktop' => 'hero_banner_3.svg',
            'image_mobile' => 'hero_banner_3.svg',
            'video_url' => NULL,
            'link' => '/category/period-kits',
            'cta_text' => 'SHOP STARTER KITS',
            'position' => 'home_hero',
            'sort_order' => 3
        ]
    ];
    $stmtBanner = $pdo->prepare("INSERT INTO `banners` (`title`, `subtitle`, `image_desktop`, `image_mobile`, `video_url`, `link`, `cta_text`, `position`, `sort_order`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    foreach ($banners as $b) {
        $stmtBanner->execute([$b['title'], $b['subtitle'], $b['image_desktop'], $b['image_mobile'], $b['video_url'], $b['link'], $b['cta_text'], $b['position'], $b['sort_order']]);
    }
    echo "Seeded home hero banners.\n";

    // 16. Sample Orders (Delivered, Shipped, Processing, Pending)
    $pdo->exec("TRUNCATE TABLE `orders`;");
    $pdo->exec("TRUNCATE TABLE `order_items`;");
    $pdo->exec("TRUNCATE TABLE `order_status_history`;");
    $pdo->exec("TRUNCATE TABLE `payments`;");

    $sampleOrders = [
        [
            'num' => 'AFH-20261001-000101',
            'user' => 1,
            'name' => 'Priya Sharma',
            'mobile' => '9876543210',
            'subtotal' => 1999.00,
            'discount' => 100.00,
            'coupon' => 'FLAT100',
            'coupon_disc' => 100.00,
            'shipping' => 0.00,
            'cod' => 0.00,
            'total' => 1899.00,
            'pay_method' => 'razorpay',
            'pay_status' => 'paid',
            'ord_status' => 'DELIVERED',
            'courier' => 'Delhivery Express',
            'tracking' => 'DEL987654321IN',
            'date' => '2026-10-01 14:20:00',
            'items' => [
                ['prod' => 34, 'var' => 1, 'name' => 'AFOREHEALTH 3-Pack Period Panty Value Bundle (Heavy + Medium + Light)', 'var_text' => 'Size: M | Color: Classic Trio', 'sku' => 'AFH-CB-034-CLA-M', 'price' => 1999.00, 'mrp' => 3047.00, 'qty' => 1]
            ]
        ],
        [
            'num' => 'AFH-20261004-000102',
            'user' => 1,
            'name' => 'Priya Sharma',
            'mobile' => '9876543210',
            'subtotal' => 849.00,
            'discount' => 0.00,
            'coupon' => NULL,
            'coupon_disc' => 0.00,
            'shipping' => 0.00,
            'cod' => 40.00,
            'total' => 889.00,
            'pay_method' => 'cod',
            'pay_status' => 'pending',
            'ord_status' => 'SHIPPED',
            'courier' => 'Blue Dart Express',
            'tracking' => 'BD765432109IN',
            'date' => '2026-10-04 11:15:00',
            'items' => [
                ['prod' => 1, 'var' => 1, 'name' => 'AFOREHEALTH UltraDry Heavy Flow Period Panty (4-Layer Gusset)', 'var_text' => 'Size: M | Color: Midnight Noir', 'sku' => 'AFH-PP-001-MID-M', 'price' => 849.00, 'mrp' => 1199.00, 'qty' => 1]
            ]
        ],
        [
            'num' => 'AFH-20261007-000103',
            'user' => 2,
            'name' => 'Ananya Patel',
            'mobile' => '9811122233',
            'subtotal' => 1299.00,
            'discount' => 129.90,
            'coupon' => 'WELCOME10',
            'coupon_disc' => 129.90,
            'shipping' => 0.00,
            'cod' => 0.00,
            'total' => 1169.10,
            'pay_method' => 'upi',
            'pay_status' => 'paid',
            'ord_status' => 'PROCESSING',
            'courier' => 'Shiprocket (Delhivery)',
            'tracking' => 'SR887766554IN',
            'date' => '2026-10-07 09:45:00',
            'items' => [
                ['prod' => 19, 'var' => 1, 'name' => 'AFOREHEALTH First Period Confidence Gift Box (Panty + Pads + Pouch)', 'var_text' => 'Size: S | Color: Blush Confetti', 'sku' => 'AFH-PK-019-BLU-S', 'price' => 1299.00, 'mrp' => 1899.00, 'qty' => 1]
            ]
        ]
    ];

    $stmtOrd = $pdo->prepare("
        INSERT INTO `orders` (
            `order_number`, `user_id`, `customer_name`, `customer_mobile`, `customer_email`,
            `subtotal`, `discount`, `coupon_code`, `coupon_discount`, `shipping_fee`, `cod_fee`, `grand_total`,
            `payment_method`, `payment_status`, `order_status`, `shipping_address_json`, `courier_name`, `tracking_number`, `created_at`
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmtOI = $pdo->prepare("
        INSERT INTO `order_items` (`order_id`, `product_id`, `variant_id`, `product_name`, `variant_details`, `sku`, `price`, `mrp`, `quantity`, `total`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmtOSH = $pdo->prepare("
        INSERT INTO `order_status_history` (`order_id`, `status`, `notes`, `notify_customer`, `created_by`, `created_at`)
        VALUES (?, ?, ?, 1, 'Operations Team', ?)
    ");

    $addrJson = json_encode([
        'full_name' => 'Priya Sharma',
        'mobile' => '9876543210',
        'house_flat' => 'Flat 402, Sunshine Heights',
        'area' => 'Linking Road, Bandra West',
        'landmark' => 'Near National College',
        'city' => 'Mumbai',
        'state' => 'Maharashtra',
        'pincode' => '400050'
    ]);

    foreach ($sampleOrders as $so) {
        $stmtOrd->execute([
            $so['num'], $so['user'], $so['name'], $so['mobile'], 'priya.sharma@example.com',
            $so['subtotal'], $so['discount'], $so['coupon'], $so['coupon_disc'], $so['shipping'], $so['cod'], $so['total'],
            $so['pay_method'], $so['pay_status'], $so['ord_status'], $addrJson, $so['courier'], $so['tracking'], $so['date']
        ]);
        $ordId = $pdo->lastInsertId();

        foreach ($so['items'] as $item) {
            $stmtOI->execute([
                $ordId, $item['prod'], $item['var'], $item['name'], $item['var_text'], $item['sku'],
                $item['price'], $item['mrp'], $item['qty'], $item['price'] * $item['qty']
            ]);
        }

        $stmtOSH->execute([$ordId, 'CONFIRMED', 'Order confirmed by customer', $so['date']]);
        if ($so['ord_status'] === 'SHIPPED' || $so['ord_status'] === 'DELIVERED') {
            $stmtOSH->execute([$ordId, 'SHIPPED', 'Dispatched via ' . $so['courier'] . ' Tracking: ' . $so['tracking'], $so['date']]);
        }
        if ($so['ord_status'] === 'DELIVERED') {
            $stmtOSH->execute([$ordId, 'DELIVERED', 'Delivered safely to recipient with OTP verification.', $so['date']]);
        }
    }
    echo "Seeded demo orders and order history.\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "\n=== AFOREHEALTH DATABASE SEEDING COMPLETED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    echo "SEEDING ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
