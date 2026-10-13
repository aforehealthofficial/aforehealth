<?php
namespace Models;

use PDO;

class ProductQuestion extends BaseModel {
    protected string $table = 'product_questions';

    /**
     * Get approved Q&As for a product
     */
    public function getApprovedForProduct(int $productId): array {
        $stmt = $this->db->prepare("
            SELECT pq.*, u.name as customer_name
            FROM product_questions pq
            JOIN users u ON pq.user_id = u.id
            WHERE pq.product_id = ? AND pq.status = 'approved'
            ORDER BY pq.created_at DESC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    /**
     * Submit a customer question (strictly defaults to 'pending')
     */
    public function submitQuestion(int $productId, int $userId, string $question): array {
        $question = trim(strip_tags($question));

        if (mb_strlen($question) < 10) {
            return ['success' => false, 'message' => 'Please ask a clear question with at least 10 characters.'];
        }

        if (mb_strlen($question) > 500) {
            return ['success' => false, 'message' => 'Question is too long (maximum 500 characters).'];
        }

        // Rate abuse protection: Max 3 questions per user in 1 hour
        $stmtRate = $this->db->prepare("
            SELECT COUNT(*) FROM product_questions 
            WHERE user_id = ? AND created_at > (NOW() - INTERVAL 1 HOUR)
        ");
        $stmtRate->execute([$userId]);
        if ((int)$stmtRate->fetchColumn() >= 5) {
            return ['success' => false, 'message' => 'You have submitted multiple questions recently. Please wait before asking again.'];
        }

        $id = $this->create([
            'product_id' => $productId,
            'user_id' => $userId,
            'question' => $question,
            'status' => 'pending'
        ]);

        // Insert notification for admin
        try {
            $this->db->prepare("
                INSERT INTO notifications (user_id, title, message, type, link)
                VALUES (NULL, 'New Product Question Pending', ?, 'question', '/admin/product-questions')
            ")->execute([mb_substr($question, 0, 100)]);
        } catch (\Exception $e) {}

        return [
            'success' => true,
            'message' => 'Thank you! Your question has been submitted for moderation and will be answered shortly by our care team.',
            'question_id' => $id
        ];
    }

    public function getAdminList(?string $status = null, int $page = 1, int $limit = 20): array {
        $offset = ($page - 1) * $limit;
        $where = "1=1";
        $params = [];

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $where = "pq.status = ?";
            $params[] = $status;
        }

        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM product_questions pq WHERE {$where}");
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT pq.*, p.name as product_name, p.slug as product_slug, u.name as user_name, u.mobile as user_mobile
            FROM product_questions pq
            JOIN products p ON pq.product_id = p.id
            JOIN users u ON pq.user_id = u.id
            WHERE {$where}
            ORDER BY (pq.status = 'pending') DESC, pq.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $allParams = array_merge($params, [$limit, $offset]);
        foreach ($allParams as $idx => $val) {
            $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ];
    }

    public function answerQuestion(int $questionId, string $answer, int $adminId): bool {
        $answer = trim(strip_tags($answer));
        $stmt = $this->db->prepare("
            UPDATE product_questions 
            SET answer = ?, answered_by = ?, status = 'approved', updated_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([$answer, $adminId, $questionId]);
    }
}
