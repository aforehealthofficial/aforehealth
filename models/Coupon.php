<?php
namespace Models;

use PDO;

class Coupon extends BaseModel {
    protected string $table = 'coupons';

    public function validateCoupon(string $code, float $orderAmount, ?int $userId = null): array {
        $code = strtoupper(trim($code));
        $stmt = $this->db->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Invalid or expired coupon code.'];
        }

        $today = date('Y-m-d');
        if ($today < $coupon['start_date'] || $today > $coupon['expiry_date']) {
            return ['valid' => false, 'message' => 'This coupon has expired.'];
        }

        if ($coupon['usage_limit'] && $coupon['usage_count'] >= $coupon['usage_limit']) {
            return ['valid' => false, 'message' => 'Coupon usage limit has been reached.'];
        }

        if ($orderAmount < (float)$coupon['min_order_amount']) {
            return [
                'valid' => false,
                'message' => 'Minimum order amount of ₹' . number_format($coupon['min_order_amount'], 0) . ' required for this coupon.'
            ];
        }

        // First order only check
        if ($coupon['first_order_only'] && $userId) {
            $prevOrderStmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND order_status NOT IN ('CANCELLED', 'FAILED')");
            $prevOrderStmt->execute([$userId]);
            if ((int)$prevOrderStmt->fetchColumn() > 0) {
                return ['valid' => false, 'message' => 'This coupon is valid on first order only.'];
            }
        }

        // Per customer usage limit
        if ($userId && $coupon['per_customer_limit']) {
            $userUsageStmt = $this->db->prepare("SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ? AND user_id = ?");
            $userUsageStmt->execute([$coupon['id'], $userId]);
            if ((int)$userUsageStmt->fetchColumn() >= $coupon['per_customer_limit']) {
                return ['valid' => false, 'message' => 'You have already used this coupon maximum allowed times.'];
            }
        }

        // Calculate discount
        $discount = 0;
        if ($coupon['discount_type'] === 'percentage') {
            $discount = round(($orderAmount * (float)$coupon['discount_value']) / 100, 2);
            if (!empty($coupon['max_discount_amount']) && $discount > (float)$coupon['max_discount_amount']) {
                $discount = (float)$coupon['max_discount_amount'];
            }
        } else {
            $discount = min((float)$coupon['discount_value'], $orderAmount);
        }

        return [
            'valid' => true,
            'message' => 'Coupon applied successfully! You saved ₹' . number_format($discount, 0),
            'coupon' => $coupon,
            'discount' => $discount
        ];
    }
}
