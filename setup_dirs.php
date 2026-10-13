<?php
$dirs = [
    'config',
    'controllers',
    'controllers/Admin',
    'models',
    'views/layouts',
    'views/components',
    'views/storefront',
    'views/account',
    'views/auth',
    'views/blog',
    'views/pages',
    'views/errors',
    'views/admin',
    'views/admin/products',
    'views/admin/orders',
    'views/admin/categories',
    'views/admin/reviews',
    'views/admin/coupons',
    'views/admin/customers',
    'views/admin/inventory',
    'views/admin/reports',
    'views/admin/settings',
    'views/admin/blog',
    'routes',
    'helpers',
    'middleware',
    'services',
    'public/assets/css',
    'public/assets/js',
    'public/assets/images',
    'public/uploads',
    'public/uploads/products',
    'public/uploads/reviews',
    'public/uploads/banners',
    'public/uploads/blog',
    'database',
    'storage/backups',
    'logs'
];

foreach ($dirs as $dir) {
    if (!is_dir(__DIR__ . '/' . $dir)) {
        mkdir(__DIR__ . '/' . $dir, 0777, true);
    }
}
echo "All directories created successfully\n";
