<?php
// Generates clean, elegant SVG graphics for AFOREHEALTH branding, categories, and products

$imgDir = __DIR__ . '/public/assets/images';
$prodUploadDir = __DIR__ . '/public/uploads/products';
$blogUploadDir = __DIR__ . '/public/uploads/blog';

if (!is_dir($imgDir)) mkdir($imgDir, 0777, true);
if (!is_dir($prodUploadDir)) mkdir($prodUploadDir, 0777, true);
if (!is_dir($blogUploadDir)) mkdir($blogUploadDir, 0777, true);

// 1. Logo SVG
$logoSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 280 60" width="280" height="60">
  <defs>
    <linearGradient id="afhGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#8B5E83" />
      <stop offset="100%" stop-color="#693D62" />
    </linearGradient>
  </defs>
  <!-- Lotus Petal / Crescent Shield Icon -->
  <g transform="translate(6, 8)">
    <path d="M 22 2 C 10 14 6 26 12 36 C 18 46 32 46 38 36 C 44 26 40 14 28 2 Z" fill="#E8B7C8" opacity="0.6"/>
    <path d="M 25 8 C 17 18 15 28 20 36 C 25 42 33 42 37 36 C 41 29 37 18 29 8 Z" fill="url(#afhGrad)"/>
    <circle cx="25" cy="22" r="4" fill="#FFF9FA"/>
  </g>
  <!-- Wordmark -->
  <text x="62" y="34" font-family="'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif" font-weight="800" font-size="24" letter-spacing="2.5" fill="#252126">AFORE<tspan fill="#8B5E83">HEALTH</tspan></text>
  <text x="63" y="48" font-family="'Plus Jakarta Sans', 'Inter', -apple-system, sans-serif" font-weight="500" font-size="8.5" letter-spacing="1.2" fill="#777777">COMFORT &amp; PERIOD CONFIDENCE</text>
</svg>
SVG;
file_put_contents($imgDir . '/logo.svg', $logoSvg);
file_put_contents($imgDir . '/logo-white.svg', str_replace(['fill="#252126"', 'fill="#777777"'], ['fill="#FFFFFF"', 'fill="#E8B7C8"'], $logoSvg));

// Favicon
$favSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64">
  <rect width="64" height="64" rx="16" fill="#8B5E83"/>
  <path d="M 32 8 C 18 22 14 36 21 48 C 28 58 44 58 51 48 C 57 36 52 22 38 8 Z" fill="#E8B7C8" opacity="0.8"/>
  <circle cx="32" cy="32" r="6" fill="#FFF9FA"/>
</svg>
SVG;
file_put_contents($imgDir . '/favicon.svg', $favSvg);

// Hero Banners
$hero1 = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600" width="1200" height="600">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFF9FA" />
      <stop offset="50%" stop-color="#F7E9F0" />
      <stop offset="100%" stop-color="#EED5E3" />
    </linearGradient>
  </defs>
  <rect width="1200" height="600" fill="url(#bgGrad)"/>
  <circle cx="950" cy="300" r="220" fill="#E8B7C8" opacity="0.3"/>
  <circle cx="1020" cy="240" r="150" fill="#8B5E83" opacity="0.15"/>
  <!-- Elegant vector artwork -->
  <g transform="translate(800, 160)">
    <rect x="0" y="40" width="280" height="200" rx="30" fill="#FFFFFF" filter="drop-shadow(0 15px 30px rgba(139,94,131,0.15))"/>
    <path d="M 40 80 Q 140 130 240 80 Q 220 180 140 210 Q 60 180 40 80 Z" fill="#8B5E83" opacity="0.85"/>
    <path d="M 80 100 Q 140 140 200 100 Q 180 170 140 190 Q 100 170 80 100 Z" fill="#E8B7C8" opacity="0.5"/>
    <rect x="50" y="260" width="180" height="18" rx="9" fill="#8B5E83" opacity="0.2"/>
  </g>
</svg>
SVG;
file_put_contents($imgDir . '/hero_banner_1.svg', $hero1);

// Generate SVGs for all categories
$catList = [
    'cat_period_panties' => ['Period Panties', '#8B5E83', '#E8B7C8', '4-Layer Leakproof'],
    'cat_everyday_panties' => ['Everyday Panties', '#7A5274', '#F4D4DF', 'CloudSoft MicroModal'],
    'cat_period_kits' => ['Period Kits', '#5E3C57', '#DFB0C2', 'Confidence Boxes'],
    'cat_pads_liners' => ['Pads & Liners', '#4E8B68', '#D2ECD9', '100% Organic Cotton'],
    'cat_intimate_care' => ['Intimate Care', '#3B6B50', '#C2E5CD', 'pH 3.8 Balanced'],
    'cat_combos' => ['Combos & Bundles', '#8B5E83', '#E8B7C8', 'Save up to 35%']
];

foreach ($catList as $cKey => $cData) {
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400" width="400" height="400">
  <rect width="400" height="400" rx="24" fill="#FFF9FA"/>
  <circle cx="200" cy="180" r="130" fill="{$cData[2]}" opacity="0.35"/>
  <circle cx="200" cy="180" r="95" fill="{$cData[1]}" opacity="0.12"/>
  <g transform="translate(130, 110)">
    <path d="M 20 40 Q 70 85 120 40 Q 110 120 70 140 Q 30 120 20 40 Z" fill="{$cData[1]}" opacity="0.9"/>
    <path d="M 45 55 Q 70 75 95 55 Q 90 105 70 120 Q 50 105 45 55 Z" fill="{$cData[2]}" opacity="0.6"/>
  </g>
  <rect x="110" y="270" width="180" height="28" rx="14" fill="#FFFFFF" filter="drop-shadow(0 4px 10px rgba(0,0,0,0.06))"/>
  <text x="200" y="289" font-family="'Plus Jakarta Sans', sans-serif" font-size="11" font-weight="700" letter-spacing="1" fill="{$cData[1]}" text-anchor="middle">{$cData[3]}</text>
  <text x="200" y="340" font-family="'Plus Jakarta Sans', sans-serif" font-size="19" font-weight="800" fill="#252126" text-anchor="middle">{$cData[0]}</text>
</svg>
SVG;
    file_put_contents($imgDir . '/' . $cKey . '.svg', $svg);
    file_put_contents($prodUploadDir . '/' . $cKey . '.svg', $svg);
}

// Generate SVGs for all 38 products
require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();
$prods = $pdo->query("SELECT id, name, slug, price, mrp, category_id, is_bestseller, is_featured FROM products")->fetchAll();

$colors = [
    1 => ['#8B5E83', '#F3D9E3', 'LEAKPROOF 4-LAYER'],
    2 => ['#7A5274', '#F7E2EB', 'CLOUDTOUCH SOFT'],
    3 => ['#5E3C57', '#E8C5D3', 'COMPLETE KIT'],
    4 => ['#4E8B68', '#D7EFE0', '100% ORGANIC'],
    5 => ['#3B6B50', '#C9EBD2', 'pH 3.8 BALANCED'],
    6 => ['#8B5E83', '#E8B7C8', 'VALUE PACK']
];

foreach ($prods as $p) {
    $cid = $p['category_id'] ?? 1;
    $theme = $colors[$cid] ?? $colors[1];
    $cleanSlug = str_replace('-', '_', $p['slug']);
    $shortTitle = htmlspecialchars(substr($p['name'], 0, 32));
    if (strlen($p['name']) > 32) $shortTitle .= '...';

    $pSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" width="600" height="600">
  <defs>
    <linearGradient id="pGrad{$p['id']}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFFBFD" />
      <stop offset="100%" stop-color="#F7EDF2" />
    </linearGradient>
  </defs>
  <rect width="600" height="600" rx="30" fill="url(#pGrad{$p['id']})"/>
  
  <!-- Decorative backdrop aura -->
  <circle cx="300" cy="270" r="190" fill="{$theme[1]}" opacity="0.6"/>
  <circle cx="300" cy="270" r="140" fill="{$theme[0]}" opacity="0.12"/>
  
  <!-- Silhouette Art -->
  <g transform="translate(170, 140)">
    <path d="M 30 50 Q 130 110 230 50 Q 210 180 130 215 Q 50 180 30 50 Z" fill="{$theme[0]}" opacity="0.9" filter="drop-shadow(0 15px 25px rgba(37,33,38,0.12))"/>
    <path d="M 65 70 Q 130 100 195 70 Q 180 160 130 190 Q 80 160 65 70 Z" fill="{$theme[1]}" opacity="0.75"/>
    <!-- Center gusset / seal contour -->
    <path d="M 100 100 Q 130 120 160 100 Q 150 155 130 175 Q 110 155 100 100 Z" fill="#FFF9FA" opacity="0.9"/>
  </g>

  <!-- Tag Pill -->
  <rect x="180" y="420" width="240" height="34" rx="17" fill="#FFFFFF" filter="drop-shadow(0 4px 12px rgba(139,94,131,0.12))"/>
  <text x="300" y="442" font-family="'Plus Jakarta Sans', sans-serif" font-size="12" font-weight="800" letter-spacing="1.5" fill="{$theme[0]}" text-anchor="middle">{$theme[2]}</text>
  
  <!-- Product Label text -->
  <text x="300" y="495" font-family="'Plus Jakarta Sans', sans-serif" font-size="17" font-weight="700" fill="#252126" text-anchor="middle">{$shortTitle}</text>
  <text x="300" y="530" font-family="'Plus Jakarta Sans', sans-serif" font-size="20" font-weight="800" fill="{$theme[0]}" text-anchor="middle">₹{$p['price']} <tspan font-size="14" font-weight="500" fill="#888888" text-decoration="line-through">₹{$p['mrp']}</tspan></text>
</svg>
SVG;

    $detailSvg = str_replace('Silouette Art', 'Detail View Gusset Layer', $pSvg);

    file_put_contents($prodUploadDir . '/prod_' . $cleanSlug . '.svg', $pSvg);
    file_put_contents($prodUploadDir . '/prod_detail_' . $cleanSlug . '.svg', $detailSvg);
    file_put_contents($imgDir . '/prod_' . $cleanSlug . '.svg', $pSvg);
}

// Blog images
$blogImgs = [
    'blog_science_period_panties.svg' => 'The Science of Leakproof Layers',
    'blog_size_guide.svg' => 'Period Panty Fit & Sizing Guide',
    'blog_intimate_ph.svg' => 'Why pH 3.8 Protects Intimate Health',
    'blog_eco_periods.svg' => 'Sustainable Menstrual Solutions'
];
foreach ($blogImgs as $bFile => $bTitle) {
    $bSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 450" width="800" height="450">
  <rect width="800" height="450" rx="20" fill="#FFF9FA"/>
  <circle cx="650" cy="225" r="180" fill="#E8B7C8" opacity="0.3"/>
  <circle cx="680" cy="180" r="120" fill="#8B5E83" opacity="0.15"/>
  <text x="60" y="210" font-family="'Plus Jakarta Sans', sans-serif" font-size="14" font-weight="800" letter-spacing="2" fill="#8B5E83">AFOREHEALTH WELLNESS</text>
  <text x="60" y="260" font-family="'Plus Jakarta Sans', sans-serif" font-size="26" font-weight="800" fill="#252126">{$bTitle}</text>
  <text x="60" y="300" font-family="'Plus Jakarta Sans', sans-serif" font-size="15" fill="#777777">Doctor &amp; Textile Engineer Reviewed Guide</text>
</svg>
SVG;
    file_put_contents($blogUploadDir . '/' . $bFile, $bSvg);
    file_put_contents($imgDir . '/' . $bFile, $bSvg);
}

echo "All SVG visual assets generated successfully.\n";
