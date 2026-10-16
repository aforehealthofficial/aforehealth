<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin Panel - AFOREHEALTH') ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom Admin Styles -->
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <style>
    /* Guaranteed Fixed Sidebar on Desktop - Immune to any page scrolling */
    @media (min-width: 992px) {
        .admin-sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            height: 100vh !important;
            max-height: 100vh !important;
            width: 270px !important;
            z-index: 1040 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow-x: hidden !important;
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        .admin-main-wrap {
            margin-left: 270px !important;
            width: calc(100% - 270px) !important;
            min-height: 100vh !important;
            overflow-x: hidden !important;
            transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1), width 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        /* Collapsed Sidebar Strict Immunity */
        .admin-sidebar.collapsed {
            width: 76px !important;
        }
        .admin-sidebar.collapsed + .admin-main-wrap {
            margin-left: 76px !important;
            width: calc(100% - 76px) !important;
        }
        .admin-sidebar.collapsed .sidebar-heading,
        .admin-sidebar.collapsed .sidebar-label,
        .admin-sidebar.collapsed .sidebar-badge,
        .admin-sidebar.collapsed .sidebar-signout-card .text-truncate,
        .admin-sidebar.collapsed .sidebar-signout-card a span,
        .admin-sidebar.collapsed .sidebar-brand a img {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
        .admin-sidebar.collapsed .sidebar-brand {
            justify-content: center !important;
            padding: 18px 4px !important;
        }
        .admin-sidebar.collapsed .sidebar-brand a::after {
            content: 'AFH' !important;
            color: #FFFFFF !important;
            font-weight: 800 !important;
            font-size: 1.15rem !important;
            letter-spacing: 1px !important;
            display: block !important;
        }
        .admin-sidebar.collapsed .sidebar-menu {
            padding: 12px 6px !important;
            overflow-x: hidden !important;
        }
        .admin-sidebar.collapsed .sidebar-item a {
            justify-content: center !important;
            padding: 12px 0 !important;
            gap: 0 !important;
        }
        .admin-sidebar.collapsed .sidebar-item a i {
            font-size: 1.35rem !important;
            margin: 0 !important;
        }
        .admin-sidebar.collapsed .sidebar-signout-card {
            padding: 10px 6px !important;
        }
        .admin-sidebar.collapsed .sidebar-signout-card .d-flex {
            justify-content: center !important;
        }
        .admin-sidebar.collapsed .sidebar-signout-card a.btn {
            padding: 6px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 0 !important;
        }
        .admin-sidebar.collapsed .sidebar-signout-card a.btn i {
            font-size: 1.15rem !important;
            margin: 0 !important;
        }
    }

    /* Perfect Geometrical Circles for Topbar Buttons */
    .admin-topbar-circle-btn {
        width: 40px !important;
        height: 40px !important;
        min-width: 40px !important;
        min-height: 40px !important;
        max-width: 40px !important;
        max-height: 40px !important;
        aspect-ratio: 1 / 1 !important;
        border-radius: 50% !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #FFFFFF !important;
        border: 1px solid rgba(0, 0, 0, 0.1) !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
        color: #374151 !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        line-height: 1 !important;
        flex-shrink: 0 !important;
    }
    .admin-topbar-circle-btn:hover {
        background: #F8FAFC !important;
        border-color: rgba(0, 0, 0, 0.18) !important;
        color: var(--admin-primary) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08) !important;
    }
    .admin-topbar-circle-btn i {
        font-size: 1.15rem !important;
        line-height: 1 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
    }
    </style>
</head>
<body class="admin-body">

    <!-- Admin Sidebar -->
    <?php
        $sbDb = getDbConnection();
        $safeCount = function(string $sql) use ($sbDb): int {
            try {
                $res = $sbDb->query($sql);
                return $res ? (int)$res->fetchColumn() : 0;
            } catch (\Throwable $e) {
                return 0;
            }
        };

        $sbCounts = [
            'products' => $safeCount("SELECT COUNT(*) FROM products"),
            'categories' => $safeCount("SELECT COUNT(*) FROM categories"),
            'inventory' => $safeCount("SELECT COUNT(*) FROM products"),
            'inventory_low' => $safeCount("SELECT COUNT(*) FROM products WHERE stock <= low_stock_threshold"),
            'reviews_pending' => $safeCount("SELECT COUNT(*) FROM reviews WHERE status = 'pending'"),
            'reviews_total' => $safeCount("SELECT COUNT(*) FROM reviews"),
            'questions_pending' => $safeCount("SELECT COUNT(*) FROM product_questions WHERE status = 'pending'"),
            'questions_total' => $safeCount("SELECT COUNT(*) FROM product_questions"),
            'bundles' => $safeCount("SELECT COUNT(*) FROM bundles"),
            'orders' => $safeCount("SELECT COUNT(*) FROM orders"),
            'customers' => $safeCount("SELECT COUNT(*) FROM users"),
            'pincodes' => $safeCount("SELECT COUNT(*) FROM pincodes WHERE status = 'active'"),
            'coupons' => $safeCount("SELECT COUNT(*) FROM coupons WHERE status = 'active'"),
            'flash_sales' => $safeCount("SELECT COUNT(*) FROM flash_sales WHERE status = 'active'"),
            'banners' => $safeCount("SELECT COUNT(*) FROM banners"),
            'blog' => $safeCount("SELECT COUNT(*) FROM blog_posts"),
            'notifications' => $safeCount("SELECT COUNT(*) FROM notifications WHERE is_read = 0"),
        ];
    ?>
    <aside class="admin-sidebar">
        <div class="sidebar-brand d-flex align-items-center justify-content-between">
            <a href="<?= url('admin') ?>" class="text-decoration-none">
                <img src="<?= asset('images/logo-white.svg') ?>" alt="AFOREHEALTH Admin" height="34">
            </a>
            <button class="btn btn-sm text-white-50 d-lg-none" id="admin-sidebar-toggle">&times;</button>
        </div>

        <ul class="sidebar-menu">
            <li class="sidebar-heading">Core</li>
            <li class="sidebar-item <?= str_ends_with($_SERVER['REQUEST_URI'] ?? '', '/admin') ? 'active' : '' ?>">
                <a href="<?= url('admin') ?>" title="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span class="sidebar-label">Dashboard</span>
                </a>
            </li>

            <li class="sidebar-heading">Catalog &amp; Inventory</li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/products') ? 'active' : '' ?>">
                <a href="<?= url('admin/products') ?>" title="Products">
                    <i class="bi bi-box-seam"></i>
                    <span class="sidebar-label">Products</span>
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['products'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/categories') ? 'active' : '' ?>">
                <a href="<?= url('admin/categories') ?>" title="Categories">
                    <i class="bi bi-tags"></i>
                    <span class="sidebar-label">Categories</span>
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['categories'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/inventory') ? 'active' : '' ?>">
                <a href="<?= url('admin/inventory') ?>" title="Inventory & Logs">
                    <i class="bi bi-boxes"></i>
                    <span class="sidebar-label">Inventory &amp; Logs</span>
                    <?php if ($sbCounts['inventory_low'] > 0): ?>
                        <span class="sidebar-badge sidebar-badge-warning" title="<?= $sbCounts['inventory_low'] ?> items low on stock">Low: <?= $sbCounts['inventory_low'] ?></span>
                    <?php else: ?>
                        <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['inventory'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/reviews') ? 'active' : '' ?>">
                <a href="<?= url('admin/reviews') ?>" title="Product Reviews">
                    <i class="bi bi-star"></i>
                    <span class="sidebar-label">Product Reviews</span>
                    <?php if ($sbCounts['reviews_pending'] > 0): ?>
                        <span class="sidebar-badge sidebar-badge-alert" title="<?= $sbCounts['reviews_pending'] ?> pending moderation"><?= $sbCounts['reviews_pending'] ?></span>
                    <?php else: ?>
                        <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['reviews_total'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/product-questions') ? 'active' : '' ?>">
                <a href="<?= url('admin/product-questions') ?>" title="Product Q&A">
                    <i class="bi bi-chat-dots"></i>
                    <span class="sidebar-label">Product Q&amp;A</span>
                    <?php if ($sbCounts['questions_pending'] > 0): ?>
                        <span class="sidebar-badge sidebar-badge-warning" title="<?= $sbCounts['questions_pending'] ?> pending moderation"><?= $sbCounts['questions_pending'] ?></span>
                    <?php else: ?>
                        <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['questions_total'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/bundles') ? 'active' : '' ?>">
                <a href="<?= url('admin/bundles') ?>" title="Bundles & Kits">
                    <i class="bi bi-collection"></i>
                    <span class="sidebar-label">Bundles &amp; Kits</span>
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['bundles'] ?></span>
                </a>
            </li>

            <li class="sidebar-heading">Orders &amp; Customers</li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/orders') ? 'active' : '' ?>">
                <a href="<?= url('admin/orders') ?>" title="Orders">
                    <i class="bi bi-receipt"></i>
                    <span class="sidebar-label">Orders</span>
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['orders'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/abandoned-carts') ? 'active' : '' ?>">
                <a href="<?= url('admin/abandoned-carts') ?>" title="Abandoned Carts">
                    <i class="bi bi-cart-x text-danger"></i>
                    <span class="sidebar-label">Abandoned Carts</span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/customers') ? 'active' : '' ?>">
                <a href="<?= url('admin/customers') ?>" title="Customers">
                    <i class="bi bi-people"></i>
                    <span class="sidebar-label">Customers</span>
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['customers'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/shipping/pincodes') ? 'active' : '' ?>">
                <a href="<?= url('admin/shipping/pincodes') ?>" title="Shipping Pincodes">
                    <i class="bi bi-geo-alt"></i>
                    <span class="sidebar-label">Shipping Pincodes</span>
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['pincodes'] ?></span>
                </a>
            </li>

            <li class="sidebar-heading">Marketing &amp; Content</li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/coupons') ? 'active' : '' ?>">
                <a href="<?= url('admin/coupons') ?>" title="Coupons">
                    <i class="bi bi-ticket-perforated"></i>
                    <span class="sidebar-label">Coupons</span>
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['coupons'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/flash-sales') ? 'active' : '' ?>">
                <a href="<?= url('admin/flash-sales') ?>" title="Flash Sales">
                    <i class="bi bi-lightning-charge"></i>
                    <span class="sidebar-label">Flash Sales</span>
                    <?php if ($sbCounts['flash_sales'] > 0): ?>
                        <span class="sidebar-badge sidebar-badge-alert"><?= $sbCounts['flash_sales'] ?></span>
                    <?php else: ?>
                        <span class="sidebar-badge sidebar-badge-neutral">0</span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/banners') ? 'active' : '' ?>">
                <a href="<?= url('admin/banners') ?>" title="Home Banners">
                    <i class="bi bi-images"></i>
                    <span class="sidebar-label">Home Banners</span>
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['banners'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/blog') ? 'active' : '' ?>">
                <a href="<?= url('admin/blog') ?>" title="Wellness Blog">
                    <i class="bi bi-journal-text"></i>
                    <span class="sidebar-label">Wellness Blog</span>
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['blog'] ?></span>
                </a>
            </li>

            <li class="sidebar-heading">Analytics &amp; System</li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/notifications') ? 'active' : '' ?>">
                <a href="<?= url('admin/notifications') ?>" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="sidebar-label">Notifications</span>
                    <?php if ($sbCounts['notifications'] > 0): ?>
                        <span class="sidebar-badge sidebar-badge-alert"><?= $sbCounts['notifications'] ?></span>
                    <?php else: ?>
                        <span class="sidebar-badge sidebar-badge-neutral">0</span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/reports/sales') ? 'active' : '' ?>">
                <a href="<?= url('admin/reports/sales') ?>" title="Sales Reports">
                    <i class="bi bi-graph-up"></i>
                    <span class="sidebar-label">Sales Reports</span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/reports/products') ? 'active' : '' ?>">
                <a href="<?= url('admin/reports/products') ?>" title="Top Products">
                    <i class="bi bi-bar-chart"></i>
                    <span class="sidebar-label">Top Products</span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/settings') ? 'active' : '' ?>">
                <a href="<?= url('admin/settings') ?>" title="System Settings">
                    <i class="bi bi-gear"></i>
                    <span class="sidebar-label">System Settings</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-signout-card">
            <div class="d-flex align-items-center gap-2 mb-2 px-1">
                <div style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #8B263E, #A63A50); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; box-shadow: 0 2px 8px rgba(139,38,62,0.4);">
                    <?= strtoupper(substr(auth_admin()['name'] ?? 'A', 0, 1)) ?>
                </div>
                <div class="text-truncate" style="max-width: 170px;">
                    <small class="d-block text-white fw-bold text-truncate" style="font-size: 0.8125rem;"><?= e(auth_admin()['name'] ?? 'Admin') ?></small>
                    <small class="text-white-50" style="font-size: 0.71875rem;"><?= e(auth_admin()['role_name'] ?? 'Super Admin') ?></small>
                </div>
            </div>
            <a href="<?= url('admin/logout') ?>" class="btn btn-outline-danger btn-sm w-100 fw-bold rounded-pill" data-confirm-logout title="Sign Out">
                <i class="bi bi-box-arrow-right me-1"></i> <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main-wrap">
        
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-2 gap-sm-3">
                <button class="btn admin-topbar-circle-btn d-lg-none" id="admin-sidebar-toggle-top" aria-label="Toggle Navigation"><i class="bi bi-list"></i></button>
                <button class="btn admin-topbar-circle-btn d-none d-lg-inline-flex" id="admin-sidebar-collapse-btn" title="Collapse / Expand Sidebar"><i class="bi bi-layout-sidebar-inset"></i></button>
                <h5 class="m-0 fw-bold text-dark d-none d-sm-block"><?= e($pageTitle ?? 'AFOREHEALTH Admin') ?></h5>
            </div>
            <div class="d-flex align-items-center gap-2 gap-sm-3">
                <a href="<?= url('admin/notifications') ?>" class="btn admin-topbar-circle-btn position-relative" title="Notifications">
                    <i class="bi bi-bell"></i>
                    <?php if (($sbCounts['notifications'] ?? 0) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 2px 6px;">
                            <?= $sbCounts['notifications'] ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="<?= url() ?>" target="_blank" class="btn btn-outline-secondary btn-sm fw-bold rounded-pill d-none d-md-inline-flex align-items-center gap-1">
                    <i class="bi bi-box-arrow-up-right"></i> Storefront
                </a>
                
                <!-- Admin Profile Dropdown with Logout -->
                <div class="dropdown">
                    <button class="btn p-1 border-0 d-flex align-items-center gap-2 dropdown-toggle bg-transparent" type="button" id="adminUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #8B263E, #A63A50); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem; box-shadow: 0 2px 8px rgba(139,38,62,0.25);">
                            <?= strtoupper(substr(auth_admin()['name'] ?? 'A', 0, 1)) ?>
                        </div>
                        <div class="d-none d-md-block text-start small me-1">
                            <strong class="d-block text-dark"><?= e(auth_admin()['name'] ?? 'Super Administrator') ?></strong>
                            <span class="text-muted" style="font-size: 0.75rem;"><?= e(auth_admin()['role_name'] ?? 'Super Admin') ?></span>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg rounded-4 p-2 border-0 mt-2" aria-labelledby="adminUserDropdown" style="min-width: 220px; z-index: 1060;">
                        <li class="px-3 py-2 border-bottom mb-1">
                            <div class="fw-bold text-dark small text-truncate"><?= e(auth_admin()['name'] ?? 'Super Administrator') ?></div>
                            <div class="text-muted" style="font-size: 0.75rem;"><?= e(auth_admin()['email'] ?? 'admin@aforehealth.com') ?></div>
                            <span class="badge bg-primary-subtle text-primary rounded-pill mt-1" style="font-size: 0.65rem;"><?= e(auth_admin()['role_name'] ?? 'Super Admin') ?></span>
                        </li>
                        <li><a class="dropdown-item rounded-3 py-2 small" href="<?= url('admin/settings') ?>"><i class="bi bi-gear me-2 text-primary"></i> System Settings</a></li>
                        <li><a class="dropdown-item rounded-3 py-2 small" href="<?= url('admin/notifications') ?>"><i class="bi bi-bell me-2 text-primary"></i> Notifications</a></li>
                        <li><a class="dropdown-item rounded-3 py-2 small" href="<?= url() ?>" target="_blank"><i class="bi bi-box-arrow-up-right me-2 text-primary"></i> View Storefront</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item rounded-3 py-2 small text-danger fw-bold" href="<?= url('admin/logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Flash alerts -->
        <?php require __DIR__ . '/../components/alerts.php'; ?>

        <!-- Page Dynamic Content -->
        <main class="admin-page-content">
            <!-- Content injected here in views -->
