<?php
namespace Models;

use PDO;

class Review extends BaseModel {
    protected string $table = 'reviews';

    public function getApprovedForProduct(int $productId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as customer_name
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.status = 'approved'
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function getHomepageReviews(int $limit = 6): array {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as customer_name, p.name as product_name, p.slug as product_slug, p.primary_image
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN products p ON r.product_id = p.id
            WHERE r.status = 'approved' AND r.rating >= 4
            ORDER BY r.rating DESC, r.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getRatingDistribution(int $productId): array {
        $stmt = $this->db->prepare("
            SELECT rating, COUNT(*) as count
            FROM reviews
            WHERE product_id = ? AND status = 'approved'
            GROUP BY rating
        ");
        $stmt->execute([$productId]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $totalApproved = array_sum($rows);
        $distribution = [];
        for ($star = 5; $star >= 1; $star--) {
            $cnt = $rows[$star] ?? 0;
            $percent = $totalApproved > 0 ? round(($cnt / $totalApproved) * 100) : 0;
            $distribution[$star] = [
                'count' => $cnt,
                'percent' => $percent
            ];
        }

        return [
            'distribution' => $distribution,
            'total' => $totalApproved
        ];
    }

    public function hasPurchased(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.order_status NOT IN ('CANCELLED', 'FAILED')
        ");
        $stmt->execute([$userId, $productId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function hasReviewed(int $userId, int $productId): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function submitReview(array $data): array {
        // Validation
        if (!$this->hasPurchased($data['user_id'], $data['product_id'])) {
            return ['success' => false, 'message' => 'Only verified purchasers can submit a review for this product.'];
        }

        if ($this->hasReviewed($data['user_id'], $data['product_id'])) {
            return ['success' => false, 'message' => 'You have already submitted a review for this product.'];
        }

        // Enforce order ownership if order_id is supplied
        if (!empty($data['order_id'])) {
            $stmtOrder = $this->db->prepare("SELECT user_id FROM orders WHERE id = ?");
            $stmtOrder->execute([(int)$data['order_id']]);
            $orderOwner = $stmtOrder->fetchColumn();
            if ($orderOwner === false || (int)$orderOwner !== (int)$data['user_id']) {
                return ['success' => false, 'message' => 'Unauthorized: You cannot review an order belonging to another customer.'];
            }
        }

        $id = $this->create([
            'product_id' => $data['product_id'],
            'user_id' => $data['user_id'],
            'order_id' => $data['order_id'] ?? null,
            'rating' => max(1, min(5, (int)$data['rating'])),
            'title' => trim($data['title']),
            'comment' => trim($data['comment']),
            'status' => 'pending' // STRICTLY PENDING until admin approves
        ]);

        try {
            $this->db->prepare("
                INSERT INTO notifications (user_id, title, message, type, link)
                VALUES (NULL, 'New Product Review Pending', ?, 'review', '/admin/reviews')
            ")->execute(["Review for Product #{$data['product_id']} ({$data['rating']} stars)"]);
        } catch (\Exception $e) {}

        return [
            'success' => true,
            'message' => 'Thank you! Your review has been submitted and will appear after moderation approval.',
            'review_id' => $id
        ];
    }

    public function updateStatus(int $reviewId, string $status): bool {
        if (!in_array($status, ['approved', 'rejected', 'pending'])) return false;
        
        $res = $this->update($reviewId, ['status' => $status]);

        // Recalculate product rating average if approved/rejected
        $rev = $this->find($reviewId);
        if ($rev) {
            $this->recalculateProductRating($rev['product_id']);
        }

        return $res;
    }

    public function recalculateProductRating(int $productId): void {
        $stmt = $this->db->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as cnt
            FROM reviews
            WHERE product_id = ? AND status = 'approved'
        ");
        $stmt->execute([$productId]);
        $stats = $stmt->fetch();

        $avg = $stats['avg_rating'] ? round((float)$stats['avg_rating'], 1) : 5.0;
        $cnt = (int)$stats['cnt'];

        $this->db->prepare("UPDATE products SET rating_avg = ?, rating_count = ? WHERE id = ?")
            ->execute([$avg, $cnt, $productId]);
    }
}
