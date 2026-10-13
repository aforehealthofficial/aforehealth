<?php
require_once __DIR__ . '/Router.php';

// Public Storefront Routes
Router::get('/', 'HomeController@index');
Router::get('/shop', 'ShopController@index');
Router::get('/category/{slug}', 'ShopController@category');
Router::get('/combos', 'ShopController@combos');
Router::get('/offers', 'ShopController@offers');
Router::get('/search', 'ShopController@search');
Router::get('/product/{slug}', 'ProductController@show');

// Cart & AJAX
Router::get('/cart', 'CartController@index');
Router::post('/cart/add', 'CartController@add');
Router::post('/cart/update', 'CartController@update');
Router::post('/cart/remove', 'CartController@remove');
Router::post('/cart/coupon', 'CartController@applyCoupon');
Router::post('/cart/remove-coupon', 'CartController@removeCoupon');
Router::get('/cart/drawer', 'CartController@drawerData');

// Checkout & Orders
Router::get('/checkout', 'CheckoutController@index');
Router::post('/checkout/process', 'CheckoutController@process');
Router::post('/checkout/payment-verify', 'CheckoutController@verifyPayment');
Router::post('/checkout/payment-failed', 'CheckoutController@paymentFailed');
Router::post('/webhook/razorpay', 'CheckoutController@webhook');
Router::get('/order-success', 'OrderController@success');
Router::any('/track-order', 'OrderController@track');
Router::get('/order/invoice/{number}', 'OrderController@invoice');

// Authentication (Mobile Based)
Router::get('/login', 'AuthController@showLogin', ['guest']);
Router::post('/login', 'AuthController@login', ['guest']);
Router::get('/register', 'AuthController@showRegister', ['guest']);
Router::post('/register', 'AuthController@register', ['guest']);
Router::get('/otp-verify', 'AuthController@showOtpVerify', ['guest']);
Router::post('/otp/send', 'AuthController@sendOtp');
Router::post('/otp/verify', 'AuthController@verifyOtp');
Router::get('/logout', 'AuthController@logout');

// Customer Account
Router::get('/account', 'AccountController@dashboard', ['auth']);
Router::get('/account/orders', 'AccountController@orders', ['auth']);
Router::get('/account/orders/{number}', 'AccountController@orderDetail', ['auth']);
Router::get('/account/wishlist', 'AccountController@wishlist', ['auth']);
Router::post('/account/wishlist/toggle', 'AccountController@toggleWishlist', ['auth']);
Router::get('/account/addresses', 'AccountController@addresses', ['auth']);
Router::post('/account/addresses/save', 'AccountController@saveAddress', ['auth']);
Router::post('/account/addresses/delete', 'AccountController@deleteAddress', ['auth']);
Router::get('/account/profile', 'AccountController@profile', ['auth']);
Router::post('/account/profile/update', 'AccountController@updateProfile', ['auth']);
Router::post('/account/change-password', 'AccountController@changePassword', ['auth']);
Router::get('/account/change-mobile', 'AccountController@changeMobile', ['auth']);
Router::get('/account/reviews', 'AccountController@reviews', ['auth']);

// Reviews
Router::post('/review/submit', 'ReviewController@submit', ['auth']);

// Blog & Guides
Router::get('/blog', 'BlogController@index');
Router::get('/blog/{slug}', 'BlogController@show');

// CMS Pages
Router::get('/about-us', function() { (new \Controllers\PageController())->page('about-us'); });
Router::get('/shipping-policy', function() { (new \Controllers\PageController())->page('shipping-policy'); });
Router::get('/return-refund-policy', function() { (new \Controllers\PageController())->page('return-refund-policy'); });
Router::get('/privacy-policy', function() { (new \Controllers\PageController())->page('privacy-policy'); });
Router::get('/terms-conditions', function() { (new \Controllers\PageController())->page('terms-conditions'); });
Router::get('/cancellation-policy', function() { (new \Controllers\PageController())->page('cancellation-policy'); });
Router::get('/faq', 'PageController@faq');
Router::get('/contact-us', 'PageController@contact');
Router::post('/contact-us/submit', 'PageController@submitContact');
Router::post('/newsletter/subscribe', 'PageController@subscribeNewsletter');

// SEO Sitemap & Robots
Router::get('/sitemap.xml', function() {
    header('Content-Type: application/xml; charset=utf-8');
    $pdo = getDbConnection();
    $products = $pdo->query("SELECT slug, updated_at FROM products WHERE status = 'published'")->fetchAll();
    $categories = $pdo->query("SELECT slug FROM categories WHERE status = 'active'")->fetchAll();
    $posts = $pdo->query("SELECT slug, updated_at FROM blog_posts WHERE status = 'published'")->fetchAll();

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    echo '<url><loc>' . url('/') . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>';
    echo '<url><loc>' . url('/shop') . '</loc><changefreq>daily</changefreq><priority>0.9</priority></url>';
    echo '<url><loc>' . url('/combos') . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
    echo '<url><loc>' . url('/blog') . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
    
    foreach ($categories as $cat) {
        echo '<url><loc>' . url('/category/' . $cat['slug']) . '</loc><changefreq>weekly</changefreq><priority>0.8</priority></url>';
    }
    foreach ($products as $p) {
        echo '<url><loc>' . url('/product/' . $p['slug']) . '</loc><lastmod>' . date('Y-m-d', strtotime($p['updated_at'])) . '</lastmod><changefreq>daily</changefreq><priority>0.9</priority></url>';
    }
    foreach ($posts as $post) {
        echo '<url><loc>' . url('/blog/' . $post['slug']) . '</loc><lastmod>' . date('Y-m-d', strtotime($post['updated_at'])) . '</lastmod><changefreq>monthly</changefreq><priority>0.7</priority></url>';
    }
    echo '</urlset>';
    exit;
});

Router::get('/robots.txt', function() {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\n";
    echo "Disallow: /admin/\n";
    echo "Disallow: /account/\n";
    echo "Disallow: /cart/\n";
    echo "Disallow: /checkout/\n";
    echo "Allow: /\n\n";
    echo "Sitemap: " . url('/sitemap.xml') . "\n";
    exit;
});

// Admin Authentication
Router::get('/admin/login', 'Admin\AdminAuthController@showLogin');
Router::post('/admin/login', 'Admin\AdminAuthController@login');
Router::get('/admin/logout', 'Admin\AdminAuthController@logout');

// Admin Panel Protected Routes
Router::get('/admin', 'Admin\AdminDashboardController@index', ['admin']);

// Admin Catalog
Router::get('/admin/products', 'Admin\AdminProductController@index', ['admin']);
Router::get('/admin/products/create', 'Admin\AdminProductController@create', ['admin']);
Router::post('/admin/products/store', 'Admin\AdminProductController@store', ['admin']);
Router::get('/admin/products/edit/{id}', 'Admin\AdminProductController@edit', ['admin']);
Router::post('/admin/products/update/{id}', 'Admin\AdminProductController@update', ['admin']);
Router::get('/admin/products/duplicate/{id}', 'Admin\AdminProductController@duplicate', ['admin']);
Router::post('/admin/products/delete', 'Admin\AdminProductController@delete', ['admin']);

Router::get('/admin/categories', 'Admin\AdminCategoryController@index', ['admin']);
Router::post('/admin/categories/store', 'Admin\AdminCategoryController@store', ['admin']);
Router::post('/admin/categories/delete', 'Admin\AdminCategoryController@delete', ['admin']);

// Admin Orders
Router::get('/admin/orders', 'Admin\AdminOrderController@index', ['admin']);
Router::get('/admin/orders/{number}', 'Admin\AdminOrderController@show', ['admin']);
Router::post('/admin/orders/update-status', 'Admin\AdminOrderController@updateStatus', ['admin']);

// Admin Review Moderation
Router::get('/admin/reviews', 'Admin\AdminReviewController@index', ['admin']);
Router::post('/admin/reviews/update-status', 'Admin\AdminReviewController@updateStatus', ['admin']);
Router::post('/admin/reviews/delete', 'Admin\AdminReviewController@delete', ['admin']);

// Admin Marketing & Coupons
Router::get('/admin/coupons', 'Admin\AdminCouponController@index', ['admin']);
Router::post('/admin/coupons/store', 'Admin\AdminCouponController@store', ['admin']);
Router::post('/admin/coupons/toggle', 'Admin\AdminCouponController@toggleStatus', ['admin']);
Router::post('/admin/coupons/delete', 'Admin\AdminCouponController@delete', ['admin']);

// Admin Customers
Router::get('/admin/customers', 'Admin\AdminCustomerController@index', ['admin']);
Router::get('/admin/customers/{id}', 'Admin\AdminCustomerController@show', ['admin']);

// Admin Inventory
Router::get('/admin/inventory', 'Admin\AdminInventoryController@index', ['admin']);
Router::post('/admin/inventory/adjust', 'Admin\AdminInventoryController@adjustStock', ['admin']);

// Admin Reports
Router::get('/admin/reports/sales', 'Admin\AdminReportController@sales', ['admin']);
Router::get('/admin/reports/products', 'Admin\AdminReportController@products', ['admin']);

// Admin Settings & Backup
Router::get('/admin/settings', 'Admin\AdminSettingController@index', ['admin']);
Router::post('/admin/settings/update', 'Admin\AdminSettingController@update', ['admin']);
Router::get('/admin/settings/backup', 'Admin\AdminSettingController@backupDatabase', ['admin']);

// Admin Blog
Router::get('/admin/blog', 'Admin\AdminBlogController@index', ['admin']);
Router::get('/admin/blog/create', 'Admin\AdminBlogController@create', ['admin']);
Router::post('/admin/blog/store', 'Admin\AdminBlogController@store', ['admin']);
Router::post('/admin/blog/delete', 'Admin\AdminBlogController@delete', ['admin']);

// ==========================================
// PHASE 2 FEATURE EXPANSION ROUTES
// ==========================================

// Pincode & Delivery Availability API
Router::get('/api/pincode/check', 'PincodeController@check');
Router::post('/api/pincode/check', 'PincodeController@check');

// Product Comparison
Router::get('/compare', 'CompareController@index');
Router::post('/compare/add', 'CompareController@add');
Router::post('/compare/remove', 'CompareController@remove');
Router::get('/compare/clear', 'CompareController@clear');

// Custom Bundle Builder
Router::get('/bundles', 'BundleController@index');
Router::get('/build-your-kit', 'BundleController@index');
Router::get('/bundle/{slug}', 'BundleController@show');
Router::post('/api/bundle/calculate', 'BundleController@calculate');
Router::post('/bundle/add-to-cart', 'BundleController@addToCart');

// Smart Search Suggestions API
Router::get('/api/search/suggestions', 'SearchApiController@suggestions');

// Product Questions (Q&A)
Router::post('/question/submit', 'QuestionController@submit', ['auth']);

// Customer Account Phase 2 Additions
Router::get('/account/buy-again', 'AccountController@buyAgain', ['auth']);
Router::post('/account/orders/reorder', 'AccountController@reorder', ['auth']);
Router::get('/account/points', 'AccountController@points', ['auth']);
Router::get('/account/referral', 'AccountController@referral', ['auth']);
Router::get('/account/questions', 'AccountController@questions', ['auth']);

// Admin Phase 2 Modules
Router::get('/admin/shipping/pincodes', 'Admin\AdminPincodeController@index', ['admin']);
Router::post('/admin/shipping/pincodes/store', 'Admin\AdminPincodeController@store', ['admin']);
Router::post('/admin/shipping/pincodes/toggle', 'Admin\AdminPincodeController@toggle', ['admin']);
Router::post('/admin/shipping/pincodes/delete', 'Admin\AdminPincodeController@delete', ['admin']);
Router::get('/admin/shipping/pincodes/export', 'Admin\AdminPincodeController@exportCsv', ['admin']);
Router::post('/admin/shipping/pincodes/import', 'Admin\AdminPincodeController@importCsv', ['admin']);

Router::get('/admin/product-questions', 'Admin\AdminQuestionController@index', ['admin']);
Router::post('/admin/product-questions/answer', 'Admin\AdminQuestionController@answer', ['admin']);
Router::post('/admin/product-questions/update-status', 'Admin\AdminQuestionController@updateStatus', ['admin']);
Router::post('/admin/product-questions/status', 'Admin\AdminQuestionController@updateStatus', ['admin']);
Router::post('/admin/product-questions/delete', 'Admin\AdminQuestionController@delete', ['admin']);

Router::get('/admin/bundles', 'Admin\AdminBundleController@index', ['admin']);
Router::get('/admin/bundles/create', 'Admin\AdminBundleController@create', ['admin']);
Router::post('/admin/bundles/store', 'Admin\AdminBundleController@store', ['admin']);
Router::post('/admin/bundles/toggle', 'Admin\AdminBundleController@toggle', ['admin']);
Router::post('/admin/bundles/delete', 'Admin\AdminBundleController@delete', ['admin']);

Router::get('/admin/flash-sales', 'Admin\AdminFlashSaleController@index', ['admin']);
Router::post('/admin/flash-sales/store', 'Admin\AdminFlashSaleController@store', ['admin']);
Router::post('/admin/flash-sales/toggle', 'Admin\AdminFlashSaleController@toggle', ['admin']);
Router::post('/admin/flash-sales/delete', 'Admin\AdminFlashSaleController@delete', ['admin']);

Router::get('/admin/notifications', 'Admin\AdminNotificationController@index', ['admin']);
Router::any('/admin/notifications/mark-read', 'Admin\AdminNotificationController@markAllRead', ['admin']);
Router::any('/admin/notifications/mark-all-read', 'Admin\AdminNotificationController@markAllRead', ['admin']);
