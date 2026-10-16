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
                <a href="<?= url('admin') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
            </li>

            <li class="sidebar-heading">Catalog &amp; Inventory</li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/products') ? 'active' : '' ?>">
                <a href="<?= url('admin/products') ?>">
                    <i class="bi bi-box-seam"></i> Products
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['products'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/categories') ? 'active' : '' ?>">
                <a href="<?= url('admin/categories') ?>">
                    <i class="bi bi-tags"></i> Categories
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['categories'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/inventory') ? 'active' : '' ?>">
                <a href="<?= url('admin/inventory') ?>">
                    <i class="bi bi-boxes"></i> Inventory &amp; Logs
                    <?php if ($sbCounts['inventory_low'] > 0): ?>
                        <span class="sidebar-badge sidebar-badge-warning" title="<?= $sbCounts['inventory_low'] ?> items low on stock">Low: <?= $sbCounts['inventory_low'] ?></span>
                    <?php else: ?>
                        <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['inventory'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/reviews') ? 'active' : '' ?>">
                <a href="<?= url('admin/reviews') ?>">
                    <i class="bi bi-star"></i> Product Reviews
                    <?php if ($sbCounts['reviews_pending'] > 0): ?>
                        <span class="sidebar-badge sidebar-badge-alert" title="<?= $sbCounts['reviews_pending'] ?> pending moderation"><?= $sbCounts['reviews_pending'] ?></span>
                    <?php else: ?>
                        <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['reviews_total'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/product-questions') ? 'active' : '' ?>">
                <a href="<?= url('admin/product-questions') ?>">
                    <i class="bi bi-chat-dots"></i> Product Q&amp;A
                    <?php if ($sbCounts['questions_pending'] > 0): ?>
                        <span class="sidebar-badge sidebar-badge-warning" title="<?= $sbCounts['questions_pending'] ?> pending moderation"><?= $sbCounts['questions_pending'] ?></span>
                    <?php else: ?>
                        <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['questions_total'] ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/bundles') ? 'active' : '' ?>">
                <a href="<?= url('admin/bundles') ?>">
                    <i class="bi bi-collection"></i> Bundles &amp; Kits
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['bundles'] ?></span>
                </a>
            </li>

            <li class="sidebar-heading">Orders &amp; Customers</li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/orders') ? 'active' : '' ?>">
                <a href="<?= url('admin/orders') ?>">
                    <i class="bi bi-receipt"></i> Orders
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['orders'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/abandoned-carts') ? 'active' : '' ?>">
                <a href="<?= url('admin/abandoned-carts') ?>">
                    <i class="bi bi-cart-x text-danger"></i> Abandoned Carts
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/customers') ? 'active' : '' ?>">
                <a href="<?= url('admin/customers') ?>">
                    <i class="bi bi-people"></i> Customers
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['customers'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/shipping/pincodes') ? 'active' : '' ?>">
                <a href="<?= url('admin/shipping/pincodes') ?>">
                    <i class="bi bi-geo-alt"></i> Shipping Pincodes
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['pincodes'] ?></span>
                </a>
            </li>

            <li class="sidebar-heading">Marketing &amp; Content</li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/coupons') ? 'active' : '' ?>">
                <a href="<?= url('admin/coupons') ?>">
                    <i class="bi bi-ticket-perforated"></i> Coupons
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['coupons'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/flash-sales') ? 'active' : '' ?>">
                <a href="<?= url('admin/flash-sales') ?>">
                    <i class="bi bi-lightning-charge"></i> Flash Sales
                    <?php if ($sbCounts['flash_sales'] > 0): ?>
                        <span class="sidebar-badge sidebar-badge-alert"><?= $sbCounts['flash_sales'] ?></span>
                    <?php else: ?>
                        <span class="sidebar-badge sidebar-badge-neutral">0</span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/banners') ? 'active' : '' ?>">
                <a href="<?= url('admin/banners') ?>">
                    <i class="bi bi-images"></i> Home Banners
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['banners'] ?></span>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/blog') ? 'active' : '' ?>">
                <a href="<?= url('admin/blog') ?>">
                    <i class="bi bi-journal-text"></i> Wellness Blog
                    <span class="sidebar-badge sidebar-badge-neutral"><?= $sbCounts['blog'] ?></span>
                </a>
            </li>

            <li class="sidebar-heading">Analytics &amp; System</li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/notifications') ? 'active' : '' ?>">
                <a href="<?= url('admin/notifications') ?>">
                    <i class="bi bi-bell"></i> Notifications
                    <?php if ($sbCounts['notifications'] > 0): ?>
                        <span class="sidebar-badge sidebar-badge-alert"><?= $sbCounts['notifications'] ?></span>
                    <?php else: ?>
                        <span class="sidebar-badge sidebar-badge-neutral">0</span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/reports/sales') ? 'active' : '' ?>">
                <a href="<?= url('admin/reports/sales') ?>"><i class="bi bi-graph-up"></i> Sales Reports</a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/reports/products') ? 'active' : '' ?>">
                <a href="<?= url('admin/reports/products') ?>"><i class="bi bi-bar-chart"></i> Top Products</a>
            </li>
            <li class="sidebar-item <?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/settings') ? 'active' : '' ?>">
                <a href="<?= url('admin/settings') ?>"><i class="bi bi-gear"></i> System Settings</a>
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
            <a href="<?= url('admin/logout') ?>" class="btn btn-outline-danger btn-sm w-100 fw-bold rounded-pill" data-confirm-logout>
                <i class="bi bi-box-arrow-right me-1"></i> Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="admin-main-wrap">
        
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="d-flex align-items-center gap-2 gap-sm-3">
                <button class="btn btn-light d-lg-none p-1" id="admin-sidebar-toggle-top" aria-label="Toggle Navigation"><i class="bi bi-list fs-4"></i></button>
                <button class="btn btn-light d-none d-lg-flex p-2 rounded-circle border shadow-xs" id="admin-sidebar-collapse-btn" title="Collapse / Expand Sidebar"><i class="bi bi-layout-sidebar-inset fs-5"></i></button>
                <h5 class="m-0 fw-bold text-dark d-none d-sm-block"><?= e($pageTitle ?? 'AFOREHEALTH Admin') ?></h5>
            </div>
            <div class="d-flex align-items-center gap-2 gap-sm-3">
                <a href="<?= url('admin/notifications') ?>" class="btn btn-light position-relative p-2 rounded-circle border shadow-xs" title="Notifications">
                    <i class="bi bi-bell fs-5 text-dark"></i>
                    <?php if (($sbCounts['notifications'] ?? 0) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
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
