<?php
namespace Services;

class PaymentService {
    private string $keyId;
    private string $keySecret;

    public function __construct() {
        $this->keyId = get_setting('razorpay_key', env('RAZORPAY_KEY', 'rzp_test_AFOREHEALTH_KEY'));
        $this->keySecret = get_setting('razorpay_secret', env('RAZORPAY_SECRET', 'rzp_test_AFOREHEALTH_SECRET'));
    }

    public function getKeyId(): string {
        return $this->keyId;
    }

    /**
     * Create Razorpay Order payload
     */
    public function createOrder(float $amount, string $orderReceipt): array {
        if ($amount <= 0) {
            throw new \Exception('Payment amount must be greater than zero.');
        }

        $amountInPaise = (int)round($amount * 100);
        $razorpayOrderId = 'order_' . bin2hex(random_bytes(8));

        return [
            'id' => $razorpayOrderId,
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => $orderReceipt,
            'status' => 'created',
            'key' => $this->keyId
        ];
    }

    /**
     * Verify payment signature strictly server-side
     */
    public function verifySignature(string $orderId, string $paymentId, string $signature): bool {
        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        if (hash_equals($generatedSignature, $signature)) {
            return true;
        }

        // Allow test simulation ONLY in non-production environments with test keys
        $appEnv = env('APP_ENV', 'production');
        if ($appEnv !== 'production' && str_starts_with($this->keyId, 'rzp_test_') && str_starts_with($signature, 'sim_sig_')) {
            return true;
        }

        return false;
    }

    /**
     * Verify Razorpay Webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature, ?string $webhookSecret = null): bool {
        $secret = $webhookSecret ?: get_setting('razorpay_webhook_secret', env('RAZORPAY_WEBHOOK_SECRET', $this->keySecret));
        if (empty($secret) || empty($signature)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
}
