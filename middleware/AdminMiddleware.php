<?php
namespace Middleware;

class AdminMiddleware {
    public function handle(): void {
        if (!is_admin()) {
            $_SESSION['admin_intended_url'] = $_SERVER['REQUEST_URI'] ?? '/admin';
            redirect('/admin/login');
        }
    }
}
