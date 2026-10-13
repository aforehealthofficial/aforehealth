<?php
namespace Controllers\Admin;

use PDO;

class AdminNotificationController {
    public function index(): void {
        $pdo = getDbConnection();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $total = (int)$pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            ORDER BY created_at DESC 
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute();
        $notifications = $stmt->fetchAll();

        $unreadCount = (int)$pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();

        view('admin.notifications.index', [
            'pageTitle' => 'Admin Notifications Center - AFOREHEALTH Admin',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages
        ]);
    }

    public function markAllRead(): void {
        $pdo = getDbConnection();
        $pdo->exec("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
        set_flash('success', 'All notifications marked as read.');
        redirect('/admin/notifications');
    }
}
