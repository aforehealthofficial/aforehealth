<?php
namespace Models;

use PDO;
use Services\DeliveryEstimationService;

class Pincode extends BaseModel {
    protected string $table = 'pincodes';

    /**
     * Check serviceability for a 6-digit Indian PIN code
     */
    public function checkServiceability(string $pincode): array {
        $pincode = trim($pincode);

        // Syntax validation: Must be exactly 6 numeric digits, Indian PIN codes start with 1-9
        if (!preg_match('/^[1-9][0-9]{5}$/', $pincode)) {
            return [
                'serviceable' => false,
                'message' => 'Please enter a valid 6-digit Indian PIN code.',
                'data' => null
            ];
        }

        $stmt = $this->db->prepare("SELECT * FROM pincodes WHERE pincode = ? AND status = 'active' LIMIT 1");
        $stmt->execute([$pincode]);
        $row = $stmt->fetch();

        if (!$row) {
            return [
                'serviceable' => false,
                'message' => 'Sorry, delivery is currently unavailable to this PIN code.',
                'pincode' => $pincode,
                'data' => null
            ];
        }

        $deliveryService = new DeliveryEstimationService();
        $estimate = $deliveryService->estimate((int)$row['delivery_days'], $pincode);

        return [
            'serviceable' => true,
            'message' => 'Delivery available',
            'city' => $row['city'],
            'state' => $row['state'],
            'delivery_days' => (int)$row['delivery_days'],
            'estimated_dates' => $estimate['formatted_dates'],
            'estimated_days_text' => $estimate['days_string'],
            'cod_available' => (bool)$row['cod_available'],
            'prepaid_available' => (bool)$row['prepaid_available'],
            'shipping_charge' => (float)$row['shipping_charge'],
            'data' => $row
        ];
    }

    public function searchPincodes(string $q = '', ?string $state = null, ?string $cod = null, int $page = 1, int $limit = 20): array {
        $offset = ($page - 1) * $limit;
        $where = ["1=1"];
        $params = [];

        if (!empty($q)) {
            $where[] = "(pincode LIKE ? OR city LIKE ? OR state LIKE ?)";
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
            $params[] = "%{$q}%";
        }

        if (!empty($state)) {
            $where[] = "state = ?";
            $params[] = $state;
        }

        if ($cod !== null && $cod !== '') {
            $where[] = "cod_available = ?";
            $params[] = (int)$cod;
        }

        $whereSql = implode(' AND ', $where);

        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM pincodes WHERE {$whereSql}");
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        $stmt = $this->db->prepare("SELECT * FROM pincodes WHERE {$whereSql} ORDER BY pincode ASC LIMIT ? OFFSET ?");
        $allParams = array_merge($params, [$limit, $offset]);
        foreach ($allParams as $idx => $val) {
            $stmt->bindValue($idx + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return [
            'data' => $rows,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
}
