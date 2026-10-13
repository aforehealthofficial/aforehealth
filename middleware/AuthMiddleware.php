<?php
namespace Middleware;

class AuthMiddleware {
    public function handle(): void {
        if (!is_authenticated()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/account';
            set_flash('danger', 'Please login with your mobile number to access this page.');
            redirect('/login');
        }
    }
}
