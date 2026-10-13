<?php
namespace Models;

use PDO;

class RecentlyViewed extends BaseModel {
    protected string $table = 'recently_viewed';

    public function record(int $productId, ?int $userId = null, ?string $sessionId = null): void {
        if (!$userId && !$sessionId) return;

        // Check if already logged recently
        if ($userId) {
            $stmt = $this->db->prepare("SELECT id FROM recently_viewed WHERE user_id = ? AND product_id = ? LIMIT 1");
            $stmt->execute([$userId, $productId]);
            $existingId = $stmt->fetchColumn();

            if ($existingId) {
                $this->db->prepare("UPDATE recently_viewed SET viewed_at = NOW() WHERE id = ?")->execute([$existingId]);
                return;
            }

            $stmtIns = $this->db->prepare("
                INSERT INTO recently_viewed (user_id, product_id, viewed_at) VALUES (?, ?, NOW())
            ");
            $stmtIns->execute([$userId, $productId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM recently_viewed WHERE session_id = ? AND product_id = ? LIMIT 1");
            $stmt->execute([$sessionId, $productId]);
            $existingId = $stmt->fetchColumn();

            if ($existingId) {
                $this->db->prepare("UPDATE recently_viewed SET viewed_at = NOW() WHERE id = ?")->execute([$existingId]);
                return;
            }

            $stmtIns = $this->db->prepare("
                INSERT INTO recently_viewed (session_id, product_id, viewed_at) VALUES (?, ?, NOW())
            ");
            $stmtIns->execute([$sessionId, $productId]);
        }
    }

    public function getProducts(?int $userId = null, ?string $sessionId = null, int $limit = 6, ?int $excludeProductId = null): array {
        if (!$userId && !$sessionId) return [];

        $where = $userId ? "rv.user_id = ?" : "rv.session_id = ?";
        $param = $userId ?: $sessionId;

        $excludeSql = "";
        $params = [$param];
        if ($excludeProductId) {
            $excludeSql = "AND rv.product_id != ?";
            $params[] = $excludeProductId;
        }

        $params[] = $limit;

        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name
            FROM recently_viewed rv
            JOIN products p ON rv.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE {$where} AND p.status = 'published' {$excludeSql}
            ORDER BY rv.viewed_at DESC
            LIMIT ?
        ");
        foreach ($params as $idx => $val) {
            $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
