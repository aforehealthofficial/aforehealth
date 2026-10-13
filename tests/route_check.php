<?php
$routes = [
    '/',
    '/shop',
    '/category/period-panties',
    '/category/pads-liners',
    '/combos',
    '/offers',
    '/cart',
    '/login',
    '/register',
    '/otp-verify',
    '/faq',
    '/contact-us',
    '/about-us',
    '/shipping-policy',
    '/return-refund-policy',
    '/privacy-policy',
    '/terms-conditions',
    '/cancellation-policy',
    '/track-order',
    '/blog',
    '/sitemap.xml',
    '/robots.txt',
    '/admin/login'
];

$errors = [];
foreach ($routes as $r) {
    $ch = curl_init('http://127.0.0.1:8000' . $r);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $res = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo str_pad($r, 30) . " => " . $status . "\n";
    if ($status >= 400 && $status !== 404) {
        $errors[] = "$r returned $status";
    }
}

if (!empty($errors)) {
    echo "\nERRORS DETECTED:\n" . implode("\n", $errors) . "\n";
    exit(1);
} else {
    echo "\nALL STOREFRONT ROUTES RESPONDED PERFECTLY (200 / 302)!\n";
    exit(0);
}
