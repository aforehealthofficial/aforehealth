<?php
namespace Controllers\Admin;

use PDO;

class AdminSettingController {
    public function index(): void {
        $pdo = getDbConnection();
        $settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

        view('admin.settings.index', [
            'pageTitle' => 'System & Store Settings - AFOREHEALTH Admin',
            'settings' => $settings
        ]);
    }

    public function update(): void {
        $pdo = getDbConnection();
        $allowedKeys = [
            'store_name', 'store_tagline', 'store_phone', 'store_email', 'store_address', 'store_gstin',
            'free_shipping_threshold', 'flat_shipping_fee', 'cod_enabled', 'cod_fee', 'cod_min_order', 'cod_max_order',
            'razorpay_enabled', 'razorpay_key', 'razorpay_secret',
            'whatsapp_number', 'whatsapp_group_url', 'whatsapp_support_message', 'whatsapp_business_hours',
            'sms_enabled', 'sms_provider', 'sms_sender_id', 'sms_api_key',
            'ga4_id', 'gtm_id', 'meta_pixel_id',
            'delivery_default_min_days', 'delivery_default_max_days', 'delivery_skip_weekends', 'delivery_holiday_dates',
            'first_order_offer_enabled', 'first_order_discount_type', 'first_order_discount_value', 'first_order_coupon_code', 'first_order_min_order',
            'loyalty_enabled', 'loyalty_points_per_100_inr', 'loyalty_point_redemption_value', 'loyalty_min_redemption_points', 'loyalty_max_redemption_points',
            'referral_enabled', 'referral_reward_points', 'referral_friend_discount', 'referral_min_order',
            'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_user', 'smtp_pass', 'smtp_from', 'smtp_from_name'
        ];

        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

        foreach ($allowedKeys as $key) {
            if (isset($_POST[$key])) {
                $val = trim($_POST[$key]);
                // If smtp_pass is empty on submit, do not overwrite existing stored password unless user explicitly entered a space
                if ($key === 'smtp_pass' && $val === '') {
                    continue;
                }
                $stmt->execute([$key, $val]);
            }
        }

        set_flash('success', 'System settings saved successfully.');
        redirect('/admin/settings');
    }

    public function testEmail(): void {
        $recipient = trim($_POST['test_email'] ?? '');
        if (empty($recipient) || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            set_flash('danger', 'Please enter a valid recipient email address to test.');
            redirect('/admin/settings#email');
        }

        $emailService = new \Services\EmailService();
        $res = $emailService->sendTestEmail($recipient);

        if ($res['success']) {
            if (!empty($res['simulated'])) {
                set_flash('warning', 'Notice: ' . $res['message']);
            } else {
                set_flash('success', 'Success! Test email sent to ' . htmlspecialchars($recipient) . ' from info@aforehealth.com via Hostinger SMTP.');
            }
        } else {
            set_flash('danger', 'SMTP Error: ' . htmlspecialchars($res['message']));
        }

        redirect('/admin/settings#email');
    }

    public function backupDatabase(): void {
        $backupDir = dirname(__DIR__, 2) . '/storage/backups';
        if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);

        $filename = 'aforehealth_backup_' . date('Y_m_d_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        // Perform schema & data export
        $pdo = getDbConnection();
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        $sqlContent = "-- AFOREHEALTH Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $t) {
            $createTable = $pdo->query("SHOW CREATE TABLE `{$t}`")->fetch(PDO::FETCH_ASSOC)['Create Table'];
            $sqlContent .= "DROP TABLE IF EXISTS `{$t}`;\n" . $createTable . ";\n\n";

            $rows = $pdo->query("SELECT * FROM `{$t}`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $cols = array_keys($rows[0]);
                $colStr = '`' . implode('`, `', $cols) . '`';
                $sqlContent .= "INSERT INTO `{$t}` ({$colStr}) VALUES\n";
                $valLines = [];
                foreach ($rows as $row) {
                    $escapedVals = array_map(function ($val) use ($pdo) {
                        return $val === null ? 'NULL' : $pdo->quote($val);
                    }, array_values($row));
                    $valLines[] = "(" . implode(', ', $escapedVals) . ")";
                }
                $sqlContent .= implode(",\n", $valLines) . ";\n\n";
            }
        }

        $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($filepath, $sqlContent);

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        readfile($filepath);
        exit;
    }
}
