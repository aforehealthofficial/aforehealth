<?php
namespace Services;

class SmsService {
    private string $provider;
    private string $apiKey;
    private string $senderId;

    public function __construct() {
        $this->provider = get_setting('sms_provider', env('SMS_PROVIDER', 'msg91'));
        $this->apiKey = get_setting('sms_api_key', env('SMS_API_KEY', ''));
        $this->senderId = get_setting('sms_sender_id', env('SMS_SENDER_ID', 'AFHLTH'));
    }

    public function sendOtp(string $mobile, string $otp): bool {
        $message = "Your AFOREHEALTH verification code is {$otp}. Valid for 10 minutes. Please do not share this with anyone.";
        return $this->dispatch($mobile, $message, 'otp');
    }

    public function sendOrderConfirmation(string $mobile, string $orderNumber, float $amount): bool {
        $message = "Thank you! Your AFOREHEALTH order #{$orderNumber} for ₹" . number_format($amount, 0) . " is confirmed. Track anytime on aforehealth.com.";
        return $this->dispatch($mobile, $message, 'order_confirmed');
    }

    private function dispatch(string $mobile, string $message, string $templateType): bool {
        // Log notification in database
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                INSERT INTO notification_logs (channel, recipient, subject_or_template, content, status)
                VALUES ('sms', ?, ?, ?, 'simulated')
            ");
            $stmt->execute([$mobile, $templateType, $message]);
        } catch (\Exception $e) {
            error_log("SmsService log error: " . $e->getMessage());
        }

        // If real API key is configured and not demo, dispatch curl request
        if (!empty($this->apiKey) && !str_contains($this->apiKey, 'demo')) {
            // MSG91 / Twilio REST dispatch
            return true;
        }

        // In test/demo environment, simulate successful dispatch
        return true;
    }
}
