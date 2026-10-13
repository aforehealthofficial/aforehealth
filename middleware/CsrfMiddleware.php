<?php
namespace Middleware;

class CsrfMiddleware {
    public function handle(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
            if (!verify_csrf_token($token)) {
                http_response_code(419);
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    json_response(['success' => false, 'message' => 'CSRF token mismatch. Please reload page.'], 419);
                }
                die("Page expired or invalid security token (419). Please refresh and try again.");
            }
        }
    }
}
