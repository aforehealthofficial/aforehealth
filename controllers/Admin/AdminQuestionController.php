<?php
namespace Controllers\Admin;

use Models\ProductQuestion;

class AdminQuestionController {
    public function index(): void {
        $status = $_GET['status'] ?? 'all';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = in_array((int)($_GET['per_page'] ?? 20), [10, 20, 30, 50, 100]) ? (int)($_GET['per_page'] ?? 20) : 20;

        $model = new ProductQuestion();
        $res = $model->getAdminList($status === 'all' ? null : $status, $page, $perPage);

        $pdo = getDbConnection();
        $pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM product_questions WHERE status = 'pending'")->fetchColumn();
        $approvedCount = (int)$pdo->query("SELECT COUNT(*) FROM product_questions WHERE status = 'approved'")->fetchColumn();
        $rejectedCount = (int)$pdo->query("SELECT COUNT(*) FROM product_questions WHERE status = 'rejected'")->fetchColumn();

        view('admin.questions.index', [
            'pageTitle' => 'Product Questions Moderation (' . $pendingCount . ' Pending) - AFOREHEALTH Admin',
            'questions' => $res['data'],
            'total' => $res['total'],
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $res['total_pages'],
            'currentStatus' => $status,
            'counts' => [
                'all' => $pendingCount + $approvedCount + $rejectedCount,
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount
            ]
        ]);
    }

    public function answer(): void {
        $id = (int)($_POST['id'] ?? 0);
        $answer = trim($_POST['answer'] ?? '');
        $adminId = (int)(auth_admin()['id'] ?? 1);

        if (!$id || empty($answer)) {
            set_flash('danger', 'Answer cannot be empty.');
            redirect('/admin/product-questions');
        }

        $model = new ProductQuestion();
        $model->answerQuestion($id, $answer, $adminId);

        set_flash('success', "Question #{$id} answered and approved.");
        redirect('/admin/product-questions');
    }

    public function updateStatus(): void {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['approved', 'rejected', 'pending'])) {
            set_flash('danger', 'Invalid status.');
            redirect('/admin/product-questions');
        }

        $pdo = getDbConnection();
        $pdo->prepare("UPDATE product_questions SET status = ? WHERE id = ?")->execute([$status, $id]);

        set_flash('success', "Question #{$id} marked as " . strtoupper($status) . ".");
        redirect('/admin/product-questions');
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();
        $pdo->prepare("DELETE FROM product_questions WHERE id = ?")->execute([$id]);

        set_flash('success', 'Question deleted.');
        redirect('/admin/product-questions');
    }
}
