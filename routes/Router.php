<?php

class Router {
    private static array $routes = [];
    private static array $middlewares = [
        'auth' => \Middleware\AuthMiddleware::class,
        'guest' => \Middleware\GuestMiddleware::class,
        'admin' => \Middleware\AdminMiddleware::class,
        'csrf' => \Middleware\CsrfMiddleware::class,
    ];

    public static function get(string $path, string|callable $handler, array $middlewares = []): void {
        self::addRoute('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, string|callable $handler, array $middlewares = []): void {
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    public static function any(string $path, string|callable $handler, array $middlewares = []): void {
        self::addRoute('GET', $path, $handler, $middlewares);
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    private static function addRoute(string $method, string $path, string|callable $handler, array $middlewares): void {
        $path = '/' . trim($path, '/');
        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public static function dispatch(string $uri, string $requestMethod): void {
        // Strip query string
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');
        $requestMethod = strtoupper($requestMethod);

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Convert route path to regex (e.g. /product/{slug} -> /product/(?P<slug>[^/]+))
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                // Execute Middlewares
                foreach ($route['middlewares'] as $mw) {
                    if (isset(self::$middlewares[$mw])) {
                        $mwClass = self::$middlewares[$mw];
                        $instance = new $mwClass();
                        $instance->handle();
                    }
                }

                // Filter named parameters
                $params = array_values(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));

                // Execute handler
                if (is_callable($route['handler'])) {
                    call_user_func_array($route['handler'], $params);
                    return;
                }

                if (is_string($route['handler']) && str_contains($route['handler'], '@')) {
                    list($controllerName, $action) = explode('@', $route['handler'], 2);
                    
                    // Support subfolders like Admin\ProductController
                    $fullControllerClass = str_starts_with($controllerName, 'Admin\\')
                        ? "Controllers\\Admin\\" . substr($controllerName, 6)
                        : "Controllers\\" . $controllerName;

                    if (!class_exists($fullControllerClass)) {
                        // Check if file exists in controllers
                        $filePath = dirname(__DIR__) . '/' . str_replace('\\', '/', $fullControllerClass) . '.php';
                        if (file_exists($filePath)) {
                            require_once $filePath;
                        }
                    }

                    if (class_exists($fullControllerClass)) {
                        $controllerInstance = new $fullControllerClass();
                        if (method_exists($controllerInstance, $action)) {
                            call_user_func_array([$controllerInstance, $action], $params);
                            return;
                        }
                    }

                    http_response_code(500);
                    die("Controller action [{$fullControllerClass}@{$action}] not found.");
                }
            }
        }

        // Route Not Found -> 404
        http_response_code(404);
        view('errors.404', [
            'pageTitle' => 'Page Not Found - AFOREHEALTH'
        ]);
        exit;
    }
}
