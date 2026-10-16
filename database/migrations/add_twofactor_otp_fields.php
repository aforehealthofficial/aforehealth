<?php
require_once __DIR__ . '/../../helpers/env.php';
require_once __DIR__ . '/../../config/database.php';

try {
    $pdo = getDbConnection();

    // 1. Add twofactor_session_id if missing
    $stmt = $pdo->query("SHOW COLUMNS FROM otp_verifications LIKE 'twofactor_session_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE otp_verifications ADD COLUMN `twofactor_session_id` VARCHAR(100) NULL AFTER `otp_code`, ADD INDEX `idx_otp_twofactor_session` (`twofactor_session_id`)");
        echo "Added twofactor_session_id column to otp_verifications.\n";
    } else {
        echo "twofactor_session_id already exists in otp_verifications.\n";
    }

    // 2. Allow otp_code to be NULL (since 2Factor AUTOGEN manages OTP on provider side)
    $pdo->exec("ALTER TABLE otp_verifications MODIFY COLUMN `otp_code` VARCHAR(10) NULL DEFAULT NULL");
    echo "Modified otp_code to allow NULL for provider-managed OTPs.\n";

    // 3. Add ip_address if missing
    $stmt2 = $pdo->query("SHOW COLUMNS FROM otp_verifications LIKE 'ip_address'");
    if (!$stmt2->fetch()) {
        $pdo->exec("ALTER TABLE otp_verifications ADD COLUMN `ip_address` VARCHAR(45) NULL AFTER `attempts`");
        echo "Added ip_address column to otp_verifications.\n";
    } else {
        echo "ip_address already exists in otp_verifications.\n";
    }

    echo "2Factor additive migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
