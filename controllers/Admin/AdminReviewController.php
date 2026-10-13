<?php
namespace Controllers\Admin;

use Models\Review;
use PDO;

class AdminReviewController {
    public function index(): void {
        $pdo = getDbConnection();
        $statusFilter = $_GET['status'] ?? 'all';

        $where = "1=1";
        $params = [];
        if (in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $where = "r.status = ?";
            $params[] = $statusFilter;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews r WHERE {$where}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare("
            SELECT r.*, p.name as product_name, p.slug as product_slug, p.primary_image,
                   u.name as customer_name, u.mobile as customer_mobile
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            JOIN users u ON r.user_id = u.id
            WHERE {$where}
            ORDER BY (r.status = 'pending') DESC, r.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        $reviews = $stmt->fetchAll();

        // Counts for tabs
        $pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
        $approvedCount = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'approved'")->fetchColumn();
        $rejectedCount = (int)$pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'rejected'")->fetchColumn();

        view('admin.reviews.index', [
            'pageTitle' => 'Review Moderation (' . $pendingCount . ' Pending) - AFOREHEALTH Admin',
            'reviews' => $reviews,
            'currentFilter' => $statusFilter,
            'counts' => [
                'all' => $pendingCount + $approvedCount + $rejectedCount,
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount
            ],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'total' => $total
        ]);
    }

    public function updateStatus(): void {
        $reviewId = (int)($_POST['review_id'] ?? $_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['approved', 'rejected', 'pending'])) {
            set_flash('danger', 'Invalid status.');
            redirect('/admin/reviews');
        }

        $reviewModel = new Review();
        $reviewModel->updateStatus($reviewId, $status);

        set_flash('success', "Review #{$reviewId} marked as " . strtoupper($status) . ".");
        redirect('/admin/reviews');
    }

    public function delete(): void {
        $reviewId = (int)($_POST['review_id'] ?? $_POST['id'] ?? 0);
        $reviewModel = new Review();
        $rev = $reviewModel->find($reviewId);

        if ($rev) {
            $reviewModel->delete($reviewId);
            $reviewModel->recalculateProductRating($rev['product_id']);
            set_flash('success', 'Review deleted successfully.');
        }

        redirect('/admin/reviews');
    }
}
