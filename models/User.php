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

    /**
     * Rate limiting: Max 3 sends per phone number in 10 minutes, and max 15 per IP
     */
    public function checkSendRateLimit(string $mobile, ?string $ip = null): void {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);

        // Phone-based limit: max 3 sends in 10 minutes
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM otp_verifications 
            WHERE mobile = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)
        ");
        $stmt->execute([$cleanMobile]);
        $phoneCount = (int)$stmt->fetchColumn();

        if ($phoneCount >= 3) {
            throw new \Exception("Too many OTP requests for this mobile number. Maximum 3 sends allowed per 10 minutes. Please wait before trying again.");
        }

        // IP-based limit: max 15 sends per IP in 10 minutes
        if (!empty($ip)) {
            $ipStmt = $this->db->prepare("
                SELECT COUNT(*) FROM otp_verifications 
                WHERE ip_address = ? AND created_at > (NOW() - INTERVAL 10 MINUTE)
            ");
            $ipStmt->execute([$ip]);
            if ((int)$ipStmt->fetchColumn() >= 15) {
                throw new \Exception("Too many requests from your network. Please wait a few minutes before trying again.");
            }
        }
    }

    /**
     * Record 2Factor.in provider session ID for subsequent server-side verification
     */
    public function recordTwoFactorSession(string $mobile, string $purpose, string $sessionId, ?string $ip = null): int {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
        $this->checkSendRateLimit($cleanMobile, $ip);

        // Invalidate older pending OTP sessions for same mobile and purpose
        $this->db->prepare("
            UPDATE otp_verifications 
            SET expires_at = NOW() 
            WHERE mobile = ? AND purpose = ? AND verified_at IS NULL
        ")->execute([$cleanMobile, $purpose]);

        $stmt = $this->db->prepare("
            INSERT INTO otp_verifications (mobile, twofactor_session_id, purpose, attempts, ip_address, expires_at)
            VALUES (?, ?, ?, 0, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))
        ");
        $stmt->execute([$cleanMobile, $sessionId, $purpose, $ip]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Retrieve the active 2Factor session for verification
     */
    public function getActiveTwoFactorSession(string $mobile, string $purpose = 'login'): ?array {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
        $stmt = $this->db->prepare("
            SELECT id, mobile, twofactor_session_id, attempts, expires_at, created_at
            FROM otp_verifications
            WHERE mobile = ? AND purpose = ? AND expires_at > NOW() AND verified_at IS NULL
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$cleanMobile, $purpose]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    /**
     * Record a failed verification attempt atomically and lock if 5 attempts reached
     */
    public function recordFailedAttempt(int $otpId): int {
        $this->db->prepare("UPDATE otp_verifications SET attempts = attempts + 1 WHERE id = ?")->execute([$otpId]);
        $stmt = $this->db->prepare("SELECT attempts FROM otp_verifications WHERE id = ?");
        $stmt->execute([$otpId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Mark OTP session verified
     */
    public function markOtpSessionVerified(int $otpId): bool {
        $stmt = $this->db->prepare("UPDATE otp_verifications SET verified_at = NOW() WHERE id = ?");
        return $stmt->execute([$otpId]);
    }

    public function createOtp(string $mobile, string $purpose = 'login'): string {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
        $this->checkSendRateLimit($cleanMobile);

        // Generate 6 digit OTP for fallback / testing
        $otp = (string)random_int(100000, 999999);

        // Invalidate older active OTPs for same mobile and purpose
        $this->db->prepare("UPDATE otp_verifications SET expires_at = NOW() WHERE mobile = ? AND purpose = ? AND verified_at IS NULL")->execute([$cleanMobile, $purpose]);

        $stmt = $this->db->prepare("
            INSERT INTO otp_verifications (mobile, otp_code, purpose, attempts, expires_at)
            VALUES (?, ?, ?, 0, DATE_ADD(NOW(), INTERVAL 15 MINUTE))
        ");
        $stmt->execute([$cleanMobile, $otp, $purpose]);

        return $otp;
    }

    public function getLatestActiveOtp(string $mobile, string $purpose = 'login'): ?string {
        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
        $stmt = $this->db->prepare("
            SELECT otp_code FROM otp_verifications
            WHERE mobile = ? AND purpose = ? AND expires_at > NOW() AND verified_at IS NULL
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$cleanMobile, $purpose]);
        $val = $stmt->fetchColumn();
        return $val ?: null;
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

        if (hash_equals((string)($rec['otp_code'] ?? ''), (string)$otp)) {
            $this->db->prepare("UPDATE otp_verifications SET verified_at = NOW() WHERE id = ?")->execute([$rec['id']]);
            return true;
        } else {
            $this->recordFailedAttempt((int)$rec['id']);
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
