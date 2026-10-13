<?php
require_once __DIR__ . '/../helpers/env.php';
loadEnv();

return [
    'name' => env('APP_NAME', 'AFOREHEALTH'),
    'tagline' => env('APP_TAGLINE', 'Everyday Comfort. Period Confidence.'),
    'env' => env('APP_ENV', 'development'),
    'debug' => env('APP_DEBUG', true),
    'url' => rtrim(env('APP_URL', 'http://aforehealth.test'), '/'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Kolkata'),
    'currency_symbol' => env('APP_CURRENCY_SYMBOL', '₹'),
    'currency_code' => env('APP_CURRENCY_CODE', 'INR'),
    'session_lifetime' => 86400 * 30, // 30 days
];
