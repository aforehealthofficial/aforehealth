<?php
namespace Models;

use PDO;
use Exception;

class Loyalty extends BaseModel {
    protected string $table = 'loyalty_transactions';

    /**
     * Get current verified balance from ledger
     */
    public function getBalance(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(points), 0) FROM loyalty_transactions WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get ledger transaction history
     */
    public function getHistory(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT * FROM loyalty_transactions 
            WHERE user_id = ? 
            ORDER BY created_at DESC, id DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Add earned points
     */
    public function addPoints(int $userId, int $points, string $type, string $description, ?int $orderId = null): int {
        if ($points <= 0) return $this->getBalance($userId);

        $curBalance = $this->getBalance($userId);
        $newBalance = $curBalance + $points;

        $stmt = $this->db->prepare("
            INSERT INTO loyalty_transactions (user_id, order_id, points, transaction_type, description, balance_after)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $orderId, $points, $type, $description, $newBalance]);

        // Keep users.wallet_balance in sync if utilized
        $this->db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?")->execute([$newBalance, $userId]);

        return $newBalance;
    }

    /**
     * Redeem points (strict balance check)
     */
    public function redeemPoints(int $userId, int $pointsToRedeem, int $orderId): bool {
        if ($pointsToRedeem <= 0) return false;

        $curBalance = $this->getBalance($userId);
        if ($curBalance < $pointsToRedeem) {
            throw new Exception("Insufficient AFORE Points balance ({$curBalance} available).");
        }

        $minRedeem = (int)get_setting('loyalty_min_redemption_points', 50);
        $maxRedeem = (int)get_setting('loyalty_max_redemption_points', 500);

        if ($pointsToRedeem < $minRedeem) {
            throw new Exception("Minimum {$minRedeem} points required for redemption.");
        }
        if ($pointsToRedeem > $maxRedeem) {
            throw new Exception("Maximum {$maxRedeem} points can be redeemed per order.");
        }

        $newBalance = $curBalance - $pointsToRedeem;
        $stmt = $this->db->prepare("
            INSERT INTO loyalty_transactions (user_id, order_id, points, transaction_type, description, balance_after)
            VALUES (?, ?, ?, 'redeemed', ?, ?)
        ");
        $stmt->execute([$userId, $orderId, -$pointsToRedeem, "Redeemed on Order #{$orderId}", $newBalance]);

        $this->db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?")->execute([$newBalance, $userId]);
        return true;
    }

    /**
     * Reverse points on order cancellation or refund
     */
    public function reversePointsForOrder(int $orderId): void {
        $stmt = $this->db->prepare("
            SELECT * FROM loyalty_transactions WHERE order_id = ? AND transaction_type = 'earned'
        ");
        $stmt->execute([$orderId]);
        $transactions = $stmt->fetchAll();

        foreach ($transactions as $tx) {
            $userId = (int)$tx['user_id'];
            $earnedPoints = (int)$tx['points'];
            $curBalance = $this->getBalance($userId);
            $newBalance = max(0, $curBalance - $earnedPoints);

            $stmtRev = $this->db->prepare("
                INSERT INTO loyalty_transactions (user_id, order_id, points, transaction_type, description, balance_after)
                VALUES (?, ?, ?, 'reversed', 'Reversed due to cancelled order', ?)
            ");
            $stmtRev->execute([$userId, $orderId, -$earnedPoints, $newBalance]);

            $this->db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?")->execute([$newBalance, $userId]);
        }
    }
}
