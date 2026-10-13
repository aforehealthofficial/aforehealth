<?php
namespace Models;

use PDO;
use Exception;

class Referral extends BaseModel {
    protected string $table = 'referrals';

    /**
     * Get or generate referral code for user
     */
    public function getReferralCode(int $userId): string {
        $stmt = $this->db->prepare("SELECT referral_code, name FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $u = $stmt->fetch();
        if (!$u) return '';

        if (!empty($u['referral_code'])) {
            return $u['referral_code'];
        }

        $codeName = strtoupper(preg_replace('/[^A-Za-z]/', '', $u['name']));
        $code = 'AFH-' . substr($codeName, 0, 6) . $userId;
        $this->db->prepare("UPDATE users SET referral_code = ? WHERE id = ?")->execute([$code, $userId]);
        return $code;
    }

    public function findByCode(string $code): ?array {
        $code = trim(strtoupper($code));
        $stmt = $this->db->prepare("SELECT * FROM users WHERE referral_code = ? LIMIT 1");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Record referral when a new user registers with a referral code
     */
    public function recordReferral(int $referrerUserId, int $newUserId, string $code): bool {
        // Prevent self-referral
        if ($referrerUserId === $newUserId) return false;

        // Check if referral already recorded for this user
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM referrals WHERE referred_user_id = ?");
        $stmtCheck->execute([$newUserId]);
        if ((int)$stmtCheck->fetchColumn() > 0) return false;

        $rewardPoints = (int)get_setting('referral_reward_points', 100);

        $stmt = $this->db->prepare("
            INSERT INTO referrals (referrer_user_id, referred_user_id, referral_code, reward_points, status)
            VALUES (?, ?, ?, ?, 'pending')
        ");
        return $stmt->execute([$referrerUserId, $newUserId, $code, $rewardPoints]);
    }

    /**
     * Complete referral reward upon first order placement
     */
    public function rewardOnFirstOrder(int $referredUserId, int $orderId, float $orderAmount): bool {
        $minOrder = (float)get_setting('referral_min_order', 499);
        if ($orderAmount < $minOrder) return false;

        $stmt = $this->db->prepare("
            SELECT * FROM referrals 
            WHERE referred_user_id = ? AND status = 'pending' 
            LIMIT 1
        ");
        $stmt->execute([$referredUserId]);
        $ref = $stmt->fetch();
        if (!$ref) return false;

        // Mark referral as rewarded
        $stmtUpd = $this->db->prepare("
            UPDATE referrals 
            SET status = 'rewarded', order_id = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmtUpd->execute([$orderId, $ref['id']]);

        // Credit loyalty points to the referrer
        $loyalty = new Loyalty();
        $loyalty->addPoints(
            (int)$ref['referrer_user_id'],
            (int)$ref['reward_points'],
            'earned',
            "Referral Reward for inviting friend (Order #{$orderId})",
            $orderId
        );

        return true;
    }

    public function getUserReferrals(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as friend_name, u.created_at as joined_date
            FROM referrals r
            JOIN users u ON r.referred_user_id = u.id
            WHERE r.referrer_user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
