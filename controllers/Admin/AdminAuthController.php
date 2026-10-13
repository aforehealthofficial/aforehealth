<?php
namespace Controllers\Admin;

use PDO;

class AdminAuthController {
    public function showLogin(): void {
        if (is_admin()) {
            redirect('/admin');
        }

        view('admin.login', [
            'pageTitle' => 'Admin Login - AFOREHEALTH Control Panel'
        ]);
    }

    public function login(): void {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            set_flash('danger', 'Please enter your admin email and password.');
            redirect('/admin/login');
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT a.*, r.name as role_name, r.slug as role_slug FROM admins a JOIN roles r ON a.role_id = r.id WHERE a.email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($password, $admin['password'])) {
            // Log failed attempt
            $logStmt = $pdo->prepare("INSERT INTO login_logs (user_type, identifier, ip_address, status) VALUES ('admin', ?, ?, 'failed')");
            $logStmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

            set_flash('danger', 'Invalid administrator credentials.');
            redirect('/admin/login');
        }

        if ($admin['status'] !== 'active') {
            set_flash('danger', 'Your administrator access is inactive.');
            redirect('/admin/login');
        }

        // Establish admin session
        $_SESSION['admin'] = [
            'id' => $admin['id'],
            'name' => $admin['name'],
            'email' => $admin['email'],
            'role_id' => $admin['role_id'],
            'role_name' => $admin['role_name'],
            'role_slug' => $admin['role_slug']
        ];

        // Update last login
        $pdo->prepare("UPDATE admins SET last_login_at = NOW() WHERE id = ?")->execute([$admin['id']]);

        // Log success
        $logStmt = $pdo->prepare("INSERT INTO login_logs (user_type, identifier, ip_address, status) VALUES ('admin', ?, ?, 'success')");
        $logStmt->execute([$email, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);

        $intended = $_SESSION['admin_intended_url'] ?? '/admin';
        unset($_SESSION['admin_intended_url']);
        redirect($intended);
    }

    public function logout(): void {
        unset($_SESSION['admin']);
        set_flash('info', 'You have been logged out of the admin panel.');
        redirect('/admin/login');
    }
}
