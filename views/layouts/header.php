<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'AFOREHEALTH | Everyday Comfort. Period Confidence.') ?></title>
    <meta name="description" content="<?= e($metaDescription ?? 'India\'s trusted women\'s intimate-care and period-care e-commerce brand. Discover leakproof period panties, breathable cotton underwear, organic pads, and pH 3.8 intimate washes.') ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    
    <!-- Clean Canonical URL without tracking query parameters -->
    <?php
        $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
        $canonicalPath = parse_url($rawUri, PHP_URL_PATH) ?: '/';
    ?>
    <link rel="canonical" href="<?= e(url(ltrim($canonicalPath, '/'))) ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="AFOREHEALTH">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_IN">
    <meta property="og:title" content="<?= e($pageTitle ?? 'AFOREHEALTH') ?>">
    <meta property="og:description" content="<?= e($metaDescription ?? 'Everyday Comfort. Period Confidence. India-focused leakproof underwear, organic pads, and intimate care essentials.') ?>">
    <meta property="og:image" content="<?= asset('images/logo.svg') ?>">
    <meta property="og:url" content="<?= e(url(ltrim($canonicalPath, '/'))) ?>">
    <meta name="theme-color" content="#8B5E83">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle ?? 'AFOREHEALTH') ?>">
    <meta name="twitter:description" content="<?= e($metaDescription ?? 'Everyday Comfort. Period Confidence. India-focused leakproof underwear, organic pads, and intimate care essentials.') ?>">
    <meta name="twitter:image" content="<?= asset('images/logo.svg') ?>">

    <!-- Schema.org JSON-LD (Organization & WebSite with Sitelinks Searchbox) -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "<?= url('/#organization') ?>",
          "name": "AFOREHEALTH",
          "url": "<?= url() ?>",
          "logo": "<?= asset('images/logo.svg') ?>",
          "description": "India-focused women intimate-care and period-care brand.",
          "sameAs": [
            "https://www.instagram.com/aforehealth",
            "https://www.facebook.com/aforehealth"
          ]
        },
        {
          "@type": "WebSite",
          "@id": "<?= url('/#website') ?>",
          "url": "<?= url() ?>",
          "name": "AFOREHEALTH",
          "publisher": { "@id": "<?= url('/#organization') ?>" },
          "potentialAction": {
            "@type": "SearchAction",
            "target": "<?= url('search?q={search_term_string}') ?>",
            "query-input": "required name=search_term_string"
          }
        }
      ]
    }
    </script>

    <!-- Favicon & PWA -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="AFOREHEALTH">

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Design System -->
    <link rel="stylesheet" href="<?= asset('css/main.css') ?>">

    <!-- Analytics dataLayer for GTM / GA4 (Feature 21) -->
    <script>
        window.dataLayer = window.dataLayer || [];
        window.trackAnalytics = function(eventName, payload) {
            payload = payload || {};
            window.dataLayer.push({
                event: eventName,
                ...payload
            });
        };
    </script>
</head>
<body>

    <!-- 1. Top Announcement Bar -->
    <div class="announcement-bar">
        <div class="container text-center">
            <span>✨ <strong>Free Shipping</strong> on orders above ₹499 | 100% Discreet Packaging Guaranteed</span>
            <a href="<?= url('offers') ?>">View Offers</a>
        </div>
    </div>

    <!-- 2. Main Sticky Header -->
    <header class="header-main">
        <div class="container py-3">
            <div class="d-flex align-items-center justify-content-between gap-3">
                
                <!-- Left: Mobile Menu Toggle & Brand Logo -->
                <div class="d-flex align-items-center gap-2">
                    <button class="btn d-lg-none p-0 border-0 me-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenuOffcanvas" aria-label="Open Navigation">
                        <i class="bi bi-list fs-2 text-dark"></i>
                    </button>
                    <a href="<?= url() ?>" class="header-brand">
                        <img src="<?= asset('images/logo.svg') ?>" alt="AFOREHEALTH" height="46">
                    </a>
                </div>

                <!-- Center: Smart Search Bar with AJAX Suggestions (Desktop) -->
                <div class="d-none d-lg-block flex-grow-1 mx-4 position-relative">
                    <form action="<?= url('search') ?>" method="GET" class="header-search-form mx-auto" id="headerSearchForm">
                        <i class="bi bi-search header-search-icon"></i>
                        <input type="text" name="q" id="headerSearchInput" class="header-search-input" placeholder="Search period panties, organic pads, combos..." value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off" oninput="handleHeaderSearch(this.value)" onfocus="if(this.value.length>=2) document.getElementById('headerSearchSuggestions').style.display='block'">
                    </form>
                    <div id="headerSearchSuggestions" class="card border-0 shadow-lg rounded-4 p-3 position-absolute bg-white" style="display: none; top: 100%; left: 0; right: 0; z-index: 1050; max-height: 420px; overflow-y: auto;"></div>
                </div>

                <!-- Right: Action Icons (Account, Wishlist, Compare, Cart) -->
                <div class="d-flex align-items-center gap-2">
                    <!-- Search Icon (Mobile) -->
                    <a href="<?= url('search') ?>" class="nav-icon-link d-lg-none" aria-label="Search">
                        <i class="bi bi-search fs-5"></i>
                    </a>

                    <!-- Account Dropdown with Profile & Logout (Desktop & Tablet) -->
                    <div class="dropdown d-none d-md-block">
                        <?php if (is_authenticated()): 
                            $uInfo = auth_user() ?? [];
                            $uName = $uInfo['name'] ?? 'Account';
                            $uMobile = $uInfo['mobile'] ?? '';
                            $uFirst = explode(' ', trim($uName))[0] ?? 'Account';
                        ?>
                            <a href="#" class="nav-icon-link dropdown-toggle d-flex align-items-center gap-1 text-decoration-none" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Account Menu">
                                <i class="bi bi-person-check-fill fs-5 text-primary"></i>
                                <span class="d-none d-xl-inline small fw-bold text-dark text-truncate" style="max-width: 100px;"><?= e($uFirst) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 p-2 border-0 mt-2" aria-labelledby="userMenuDropdown" style="min-width: 220px; z-index: 1060;">
                                <li class="px-3 py-2 border-bottom mb-1">
                                    <div class="fw-bold text-dark small text-truncate"><?= e($uName) ?></div>
                                    <?php if (!empty($uMobile)): ?>
                                        <div class="text-muted" style="font-size: 0.75rem;">+91 <?= e($uMobile) ?></div>
                                    <?php endif; ?>
                                </li>
                                <li><a class="dropdown-item rounded-3 py-2 small" href="<?= url('account') ?>"><i class="bi bi-speedometer2 me-2 text-primary"></i> My Dashboard</a></li>
                                <li><a class="dropdown-item rounded-3 py-2 small" href="<?= url('account/profile') ?>"><i class="bi bi-person me-2 text-primary"></i> My Profile</a></li>
                                <li><a class="dropdown-item rounded-3 py-2 small" href="<?= url('account/orders') ?>"><i class="bi bi-bag-check me-2 text-primary"></i> My Orders</a></li>
                                <li><a class="dropdown-item rounded-3 py-2 small" href="<?= url('account/wishlist') ?>"><i class="bi bi-heart me-2 text-primary"></i> Wishlist</a></li>
                                <li><a class="dropdown-item rounded-3 py-2 small" href="<?= url('account/points') ?>"><i class="bi bi-coin me-2 text-primary"></i> AFORE Points</a></li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item rounded-3 py-2 small text-danger fw-bold" href="<?= url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        <?php else: ?>
                            <a href="#" class="nav-icon-link dropdown-toggle text-decoration-none" id="guestMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Login / Register">
                                <i class="bi bi-person fs-5"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 p-3 border-0 mt-2" aria-labelledby="guestMenuDropdown" style="min-width: 220px; z-index: 1060;">
                                <li class="text-muted small mb-2">Welcome to AFOREHEALTH</li>
                                <li><a class="btn btn-afh-primary btn-sm w-100 fw-bold mb-2 text-center" href="<?= url('login') ?>"><i class="bi bi-box-arrow-in-right me-1"></i> Customer Login</a></li>
                                <li><a class="btn btn-outline-secondary btn-sm w-100 text-center" href="<?= url('register') ?>"><i class="bi bi-person-plus me-1"></i> Register</a></li>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Wishlist (Desktop & Tablet) -->
                    <a href="<?= url('account/wishlist') ?>" class="nav-icon-link position-relative d-none d-sm-flex" title="Wishlist" aria-label="Wishlist">
                        <i class="bi bi-heart fs-5"></i>
                        <?php $wCount = get_wishlist_count(); ?>
                        <span class="badge-count wishlist-badge" style="<?= $wCount > 0 ? '' : 'display:none;' ?>"><?= $wCount ?></span>
                    </a>

                    <!-- Compare (Desktop) -->
                    <a href="<?= url('compare') ?>" class="nav-icon-link position-relative d-none d-md-flex" title="Compare Products" aria-label="Compare">
                        <i class="bi bi-layout-split fs-5"></i>
                        <?php $cmpCount = count($_SESSION['compare_ids'] ?? []); ?>
                        <span class="badge-count compare-badge-count" style="<?= $cmpCount > 0 ? '' : 'display:none;' ?>"><?= $cmpCount ?></span>
                    </a>

                    <!-- Cart Drawer Trigger -->
                    <a href="<?= url('cart') ?>" class="nav-icon-link btn-open-cart-drawer position-relative" title="Shopping Bag" aria-label="Cart">
                        <i class="bi bi-bag fs-5"></i>
                        <?php $cCount = get_cart_count(); ?>
                        <span class="badge-count cart-badge-count" style="<?= $cCount > 0 ? '' : 'display:none;' ?>"><?= $cCount ?></span>
                    </a>
                </div>

            </div>
        </div>

        <!-- 3. Sub Navigation Bar (Desktop) -->
        <?php
            $currPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            $currQuery = $_SERVER['QUERY_STRING'] ?? '';
            $isCat = fn($slug) => str_contains($currPath, '/category/' . $slug);
        ?>
        <nav class="nav-bar-sub d-none d-lg-block">
            <div class="container-fluid px-xl-5 px-lg-3">
                <ul class="nav-main-menu">
                    <li class="nav-main-item"><a href="<?= url('category/period-panties') ?>" class="<?= $isCat('period-panties') ? 'active' : '' ?>">PERIOD PANTIES</a></li>
                    <li class="nav-main-item"><a href="<?= url('category/everyday-panties') ?>" class="<?= $isCat('everyday-panties') ? 'active' : '' ?>">EVERYDAY PANTIES</a></li>
                    <li class="nav-main-item"><a href="<?= url('category/period-kits') ?>" class="<?= $isCat('period-kits') ? 'active' : '' ?>">PERIOD KITS</a></li>
                    <li class="nav-main-item"><a href="<?= url('category/pads-and-liners') ?>" class="<?= $isCat('pads-and-liners') ? 'active' : '' ?>">PADS &amp; LINERS</a></li>
                    <li class="nav-main-item"><a href="<?= url('category/intimate-care') ?>" class="<?= $isCat('intimate-care') ? 'active' : '' ?>">INTIMATE CARE</a></li>
                    <li class="nav-main-item"><a href="<?= url('combos') ?>" class="<?= str_contains($currPath, '/combos') ? 'active' : '' ?>">COMBOS &amp; SAVE</a></li>
                    <li class="nav-main-item"><a href="<?= url('shop?sort=popularity') ?>" class="<?= (str_contains($currPath, '/shop') && str_contains($currQuery, 'popularity')) ? 'active' : '' ?>">BESTSELLERS</a></li>
                    <li class="nav-main-item"><a href="<?= url('offers') ?>" class="<?= str_contains($currPath, '/offers') ? 'active' : '' ?>">OFFERS <span class="badge-sale-tag">SAVE 35%</span></a></li>
                    <li class="nav-main-item"><a href="<?= url('build-your-kit') ?>" class="text-primary fw-bold <?= str_contains($currPath, '/build-your-kit') ? 'active' : '' ?>"><i class="bi bi-gift me-1"></i>BUILD YOUR KIT</a></li>
                    <li class="nav-main-item"><a href="<?= url('blog') ?>" class="<?= str_contains($currPath, '/blog') ? 'active' : '' ?>">WELLNESS BLOG</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <!-- 4. Mobile Menu Offcanvas -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenuOffcanvas" aria-labelledby="mobileMenuLabel">
        <div class="offcanvas-header border-bottom">
            <img src="<?= asset('images/logo.svg') ?>" alt="AFOREHEALTH" height="38">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <!-- Mobile Search -->
            <div class="p-3 bg-light border-bottom">
                <form action="<?= url('search') ?>" method="GET">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control rounded-start-pill" placeholder="Search products..." value="<?= e($_GET['q'] ?? '') ?>">
                        <button class="btn btn-afh-primary rounded-end-pill px-3" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>

            <!-- Mobile Navigation Links -->
            <div class="list-group list-group-flush py-2">
                <a href="<?= url('shop') ?>" class="list-group-item list-group-item-action fw-bold py-3"><i class="bi bi-grid me-2 text-primary"></i> Shop All Collections</a>
                <a href="<?= url('category/period-panties') ?>" class="list-group-item list-group-item-action fw-semibold py-3">Period Panties (4-Layer Leakproof)</a>
                <a href="<?= url('category/everyday-panties') ?>" class="list-group-item list-group-item-action fw-semibold py-3">Everyday Comfort Underwear</a>
                <a href="<?= url('category/period-kits') ?>" class="list-group-item list-group-item-action fw-semibold py-3">Period &amp; First Period Kits</a>
                <a href="<?= url('category/pads-and-liners') ?>" class="list-group-item list-group-item-action fw-semibold py-3">100% Organic Cotton Pads &amp; Liners</a>
                <a href="<?= url('category/intimate-care') ?>" class="list-group-item list-group-item-action fw-semibold py-3">Intimate Hygiene &amp; pH 3.8 Care</a>
                <a href="<?= url('combos') ?>" class="list-group-item list-group-item-action fw-bold text-primary py-3"><i class="bi bi-tag-fill me-2"></i> Value Bundles &amp; Combos</a>
                <a href="<?= url('offers') ?>" class="list-group-item list-group-item-action fw-bold text-danger py-3"><i class="bi bi-percent me-2"></i> Exclusive Offers</a>
                <a href="<?= url('blog') ?>" class="list-group-item list-group-item-action py-3"><i class="bi bi-book me-2"></i> Period Care &amp; Health Guides</a>
            </div>

            <div class="p-4 border-top mt-2 bg-light">
                <?php if (is_authenticated()): 
                    $mInfo = auth_user() ?? [];
                    $mName = $mInfo['name'] ?? 'Account';
                    $mMobile = $mInfo['mobile'] ?? '';
                ?>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <div class="fw-bold"><?= e($mName) ?></div>
                            <?php if (!empty($mMobile)): ?>
                                <small class="text-muted">+91 <?= e($mMobile) ?></small>
                            <?php endif; ?>
                        </div>
                        <a href="<?= url('logout') ?>" class="btn btn-sm btn-outline-danger fw-bold"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="<?= url('account') ?>" class="btn btn-afh-primary btn-sm fw-bold"><i class="bi bi-speedometer2 me-1"></i> My Dashboard</a>
                        <a href="<?= url('account/profile') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-person me-1"></i> My Profile</a>
                        <a href="<?= url('account/orders') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-bag-check me-1"></i> My Orders</a>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-2">
                        <a href="<?= url('login') ?>" class="btn btn-afh-primary">Customer Login</a>
                        <a href="<?= url('register') ?>" class="btn btn-afh-secondary">New Customer? Register</a>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4 pt-3 border-top text-center">
                    <a href="<?= e(get_setting('whatsapp_group_url')) ?>" target="_blank" class="btn btn-sm text-success fw-bold">
                        <i class="bi bi-whatsapp me-1"></i> Join AFOREHEALTH Community
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerts Container -->
    <?php require __DIR__ . '/../components/alerts.php'; ?>
