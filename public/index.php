<?php
// AFOREHEALTH Front Controller

// Autoload helpers & config
require_once dirname(__DIR__) . '/helpers/env.php';
loadEnv();
date_default_timezone_set('Asia/Kolkata');
require_once dirname(__DIR__) . '/helpers/functions.php';

// Autoload classes



spl_autoload_register(function (string $class) {
    $projectRoot = dirname(__DIR__);

    $prefixes = [
        'Controllers\\' => $projectRoot . '/controllers/',
        'Models\\'      => $projectRoot . '/models/',
        'Middleware\\'  => $projectRoot . '/middleware/',
        'Services\\'    => $projectRoot . '/services/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require_once $file;
        }

        return;
    }
});



// Load routes
require_once dirname(__DIR__) . '/routes/web.php';

// Dispatch request
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

Router::dispatch($uri, $method);
