<?php
namespace Models;

use PDO;

class User extends BaseModel {
    protected string $table = 'users';

    public function findByMobile(string $mobile): ?array {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE mobile = ? LIMIT 1");
        $stmt->execute([$cleanMobile]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function register(array $data): int {
        $cleanMobile = preg_replace('/[^0-9]/', '', $data['mobile']);
        return $this->create([
            'name' => trim($data['name']),
            'mobile' => $cleanMobile,
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'email' => !empty($data['email']) ? trim($data['email']) : null,
            'dob' => !empty($data['dob']) ? $data['dob'] : null,
            'gender' => $data['gender'] ?? 'female',
            'status' => 'active'
        ]);
    }

    public function createOtp(string $mobile, string $purpose = 'login'): string {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);

        // Rate limiting: Max 3 OTP requests in 10 minutes
        $stmtThrott = $this->db->prepare("
            SELECT COUNT(*) FROM otp_verifications 
            WHERE mobile = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)
        ");
        $stmtThrott->execute([$cleanMobile]);
        if ((int)$stmtThrott->fetchColumn() >= 3) {
            throw new \Exception("Too many OTP requests. Please wait 10 minutes before requesting another code.");
        }

        // Generate 6 digit OTP
        $otp = (string)random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Invalidate older active OTPs for same mobile and purpose without erasing rate limit history
        $this->db->prepare("UPDATE otp_verifications SET expires_at = NOW() WHERE mobile = ? AND purpose = ? AND verified_at IS NULL")->execute([$cleanMobile, $purpose]);

        $stmt = $this->db->prepare("
            INSERT INTO otp_verifications (mobile, otp_code, purpose, attempts, expires_at)
            VALUES (?, ?, ?, 0, ?)
        ");
        $stmt->execute([$cleanMobile, $otp, $purpose, $expiresAt]);

        return $otp;
    }

    public function verifyOtp(string $mobile, string $otp, string $purpose = 'login'): bool {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
        $stmt = $this->db->prepare("
            SELECT id, otp_code, attempts FROM otp_verifications
            WHERE mobile = ? AND purpose = ? AND expires_at > NOW() AND verified_at IS NULL
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$cleanMobile, $purpose]);
        $rec = $stmt->fetch();

        if (!$rec) {
            return false;
        }

        // Brute force protection: Max 5 failed attempts per OTP code
        if ((int)$rec['attempts'] >= 5) {
            throw new \Exception("Too many incorrect attempts. This OTP has been locked for security.");
        }

        if (hash_equals((string)$rec['otp_code'], (string)$otp)) {
            $this->db->prepare("UPDATE otp_verifications SET verified_at = NOW() WHERE id = ?")->execute([$rec['id']]);
            return true;
        } else {
            $this->db->prepare("UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = ?")->execute([$rec['id']]);
            return false;
        }
    }

    public function getAddresses(int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function addAddress(int $userId, array $data): int {
        if (!empty($data['is_default'])) {
            $this->db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
        }

        $stmt = $this->db->prepare("
            INSERT INTO addresses (user_id, full_name, mobile, house_flat, area, landmark, city, state, pincode, address_type, is_default)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $data['full_name'],
            $data['mobile'],
            $data['house_flat'],
            $data['area'],
            $data['landmark'] ?? null,
            $data['city'],
            $data['state'],
            $data['pincode'],
            $data['address_type'] ?? 'home',
            !empty($data['is_default']) ? 1 : 0
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function deleteAddress(int $id, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
}
