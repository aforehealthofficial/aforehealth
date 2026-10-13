<?php
// AFOREHEALTH Front Controller

// Autoload helpers & config
require_once dirname(__DIR__) . '/helpers/env.php';
loadEnv();
require_once dirname(__DIR__) . '/helpers/functions.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $file = dirname(__DIR__) . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load routes
require_once dirname(__DIR__) . '/routes/web.php';

// Dispatch request
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

Router::dispatch($uri, $method);
