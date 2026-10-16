<?php
namespace Services;

use Exception;
use InvalidArgumentException;

/**
 * TwoFactorService
 * Handles communication with 2Factor.in SMS OTP Gateway
 * Uses the documented AUTOGEN and VERIFY endpoints
 */
class TwoFactorService {
    private string $apiKey;
    private string $templateName;
    private string $baseUrl;

    // Static mock controls for automated test suites
    private static bool $mockMode = false;
    private static ?string $mockValidOtp = '123456';
    private static ?string $lastMockSessionId = null;

    public function __construct() {
        $this->apiKey = trim(env('TWOFACTOR_API_KEY', get_setting('twofactor_api_key', '')));
        $templateEnv = env('TWOFACTOR_TEMPLATE_NAME', get_setting('twofactor_template_name', null));
        $this->templateName = $templateEnv !== null ? trim($templateEnv) : 'AFOREHEALTH_LOGIN_OTP';
        $this->baseUrl = rtrim(env('TWOFACTOR_BASE_URL', 'https://2factor.in/API/V1'), '/');
    }

    /**
     * Enable or disable mock mode (for automated tests)
     */
    public static function setMockMode(bool $enabled, ?string $mockValidOtp = '123456'): void {
        self::$mockMode = $enabled;
        self::$mockValidOtp = $mockValidOtp;
    }

    public static function isMockMode(): bool {
        return self::$mockMode || env('TWOFACTOR_MOCK', false) === true;
    }

    public static function getLastMockSessionId(): ?string {
        return self::$lastMockSessionId;
    }

    /**
     * Normalize Indian mobile number to 10-digit standard for DB, and 91XXXXXXXXXX for 2Factor
     */
    public function normalizePhone(string $phone): array {
        $digits = preg_replace('/[^0-9]/', '', $phone);

        // Strip leading 0
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }

        // Strip leading 91 if 12 digits
        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $local = substr($digits, 2);
        } else {
            $local = $digits;
        }

        // Validate standard Indian mobile number (10 digits starting with 6, 7, 8, or 9)
        if (strlen($local) !== 10 || !preg_match('/^[6-9][0-9]{9}$/', $local)) {
            throw new InvalidArgumentException('Please enter a valid 10-digit Indian mobile number.');
        }

        return [
            'local' => $local,               // 10 digits for local DB (e.g. 9876543210)
            'international' => '91' . $local,// prefixed with 91 for 2Factor API
            'twofactor' => '91' . $local,
            'e164' => '+91' . $local
        ];
    }

    /**
     * Mask phone number for security in logs and views (e.g. +91 98XXX XX210)
     */
    public function maskPhone(string $phone): string {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            $clean = substr($clean, 2);
        }
        if (strlen($clean) !== 10) {
            return '******' . substr($clean, -4);
        }
        return '+91 ' . substr($clean, 0, 2) . 'XXX XX' . substr($clean, -3);
    }

    /**
     * Send OTP via 2Factor AUTOGEN API
     * Endpoint: GET https://2factor.in/API/V1/{API_KEY}/SMS/{PHONE_NUMBER}/AUTOGEN/{TEMPLATE_NAME}
     */
    public function sendAutogenOtp(string $rawPhone): array {
        $phoneData = $this->normalizePhone($rawPhone);
        $providerPhone = $phoneData['international'];
        $localPhone = $phoneData['local'];

        // Mock mode for automated testing without sending live paid SMS
        if (self::isMockMode()) {
            $mockSession = 'mock-session-' . bin2hex(random_bytes(16));
            self::$lastMockSessionId = $mockSession;
            $this->logSmsAudit($localPhone, 'otp_autogen', 'Mock 2Factor OTP session generated', 'mocked');

            return [
                'success' => true,
                'session_id' => $mockSession,
                'phone' => $localPhone,
                'masked_phone' => $this->maskPhone($localPhone),
                'mock' => true,
                'message' => 'OTP has been sent to your mobile number.'
            ];
        }

        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'SMS Gateway is not configured. Please check server settings or contact support.',
                'code' => 'API_KEY_MISSING'
            ];
        }

        // URL encode parameters safely (supports both custom template and default system template)
        if (!empty($this->templateName)) {
            $url = sprintf(
                '%s/%s/SMS/%s/AUTOGEN/%s',
                $this->baseUrl,
                rawurlencode($this->apiKey),
                rawurlencode($providerPhone),
                rawurlencode($this->templateName)
            );
        } else {
            $url = sprintf(
                '%s/%s/SMS/%s/AUTOGEN',
                $this->baseUrl,
                rawurlencode($this->apiKey),
                rawurlencode($providerPhone)
            );
        }

        $response = $this->executeCurl($url);

        if (!$response['success']) {
            $this->logSmsAudit($localPhone, 'otp_autogen', '2Factor HTTP error: ' . $response['error'], 'failed', $response['error']);
            return [
                'success' => false,
                'message' => 'Unable to send verification OTP at this moment. Please try again shortly.',
                'code' => 'GATEWAY_CONNECTION_ERROR'
            ];
        }

        $body = $response['body'];
        $data = json_decode($body, true);

        if (!is_array($data) || !isset($data['Status'])) {
            $this->logSmsAudit($localPhone, 'otp_autogen', '2Factor malformed response', 'failed', substr($body, 0, 300));
            return [
                'success' => false,
                'message' => 'SMS Gateway returned an invalid response. Please try again.',
                'code' => 'GATEWAY_MALFORMED_RESPONSE'
            ];
        }

        if (strcasecmp($data['Status'], 'Success') === 0 && !empty($data['Details'])) {
            $sessionId = (string)$data['Details'];
            $this->logSmsAudit($localPhone, 'otp_autogen', '2Factor OTP dispatched. Session ID: ' . $sessionId, 'sent', $body);

            return [
                'success' => true,
                'session_id' => $sessionId,
                'phone' => $localPhone,
                'masked_phone' => $this->maskPhone($localPhone),
                'message' => 'OTP has been sent to your mobile number.'
            ];
        }

        // Handle provider error details safely without leaking credentials
        $details = $data['Details'] ?? 'Unknown error';
        $userMsg = 'Unable to send OTP. Please verify your mobile number or try again later.';

        if (stripos($details, 'balance') !== false) {
            $userMsg = 'SMS Gateway quota exhausted. Please contact AFOREHEALTH support.';
        } elseif (stripos($details, 'template') !== false) {
            $userMsg = 'SMS template mismatch. Please contact administrator.';
        } elseif (stripos($details, 'block') !== false || stripos($details, 'rate') !== false) {
            $userMsg = 'Too many requests. Please wait a few minutes before trying again.';
        }

        $this->logSmsAudit($localPhone, 'otp_autogen', '2Factor rejected request: ' . $details, 'failed', $body);

        return [
            'success' => false,
            'message' => $userMsg,
            'code' => 'PROVIDER_REJECTED'
        ];
    }

    /**
     * Verify OTP with 2Factor.in Verification API
     * Endpoint: GET https://2factor.in/API/V1/{API_KEY}/SMS/VERIFY/{SESSION_ID}/{OTP_VALUE}
     */
    public function verifyOtp(string $sessionId, string $otpInput, string $rawPhone = ''): array {
        $sessionId = trim($sessionId);
        $otp = trim($otpInput);

        if (empty($sessionId)) {
            return [
                'success' => false,
                'message' => 'Verification session expired. Please request a new OTP.',
                'code' => 'INVALID_SESSION'
            ];
        }

        if (!preg_match('/^[0-9]{4,8}$/', $otp)) {
            return [
                'success' => false,
                'message' => 'Please enter a valid numeric OTP code.',
                'code' => 'INVALID_FORMAT'
            ];
        }

        // Mock mode verification for automated testing
        if (self::isMockMode()) {
            if ($otp === self::$mockValidOtp || $otp === '123456') {
                return [
                    'success' => true,
                    'message' => 'OTP Matched successfully.',
                    'mock' => true
                ];
            }
            return [
                'success' => false,
                'message' => 'Invalid OTP code entered. Please try again.',
                'code' => 'OTP_MISMATCH',
                'mock' => true
            ];
        }

        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'message' => 'SMS Gateway is not configured.',
                'code' => 'API_KEY_MISSING'
            ];
        }

        $url = sprintf(
            '%s/%s/SMS/VERIFY/%s/%s',
            $this->baseUrl,
            rawurlencode($this->apiKey),
            rawurlencode($sessionId),
            rawurlencode($otp)
        );

        $response = $this->executeCurl($url);

        if (!$response['success']) {
            return [
                'success' => false,
                'message' => 'Unable to verify OTP due to a network error. Please try again.',
                'code' => 'NETWORK_ERROR'
            ];
        }

        $data = json_decode($response['body'], true);

        if (!is_array($data) || !isset($data['Status'])) {
            return [
                'success' => false,
                'message' => 'Invalid verification response from SMS gateway.',
                'code' => 'MALFORMED_RESPONSE'
            ];
        }

        if (strcasecmp($data['Status'], 'Success') === 0) {
            $details = (string)($data['Details'] ?? '');
            if (stripos($details, 'Match') !== false || stripos($details, 'Success') !== false) {
                return [
                    'success' => true,
                    'message' => 'OTP Matched successfully.'
                ];
            }
        }

        $details = (string)($data['Details'] ?? '');
        $errMsg = 'Incorrect OTP code entered. Please check and try again.';
        $errCode = 'OTP_MISMATCH';

        if (stripos($details, 'Expire') !== false) {
            $errMsg = 'This OTP has expired. Please request a new code.';
            $errCode = 'OTP_EXPIRED';
        } elseif (stripos($details, 'Not Found') !== false || stripos($details, 'Invalid') !== false) {
            $errMsg = 'Invalid or expired OTP session. Please request a new code.';
            $errCode = 'SESSION_NOT_FOUND';
        }

        return [
            'success' => false,
            'message' => $errMsg,
            'code' => $errCode
        ];
    }

    /**
     * Safe cURL execution with strict timeouts and error capture
     */
    private function executeCurl(string $url): array {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: AFOREHEALTH-Client/1.0'
            ]
        ]);

        $body = curl_exec($ch);
        $errNo = curl_errno($ch);
        $errMsg = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errNo !== 0) {
            return [
                'success' => false,
                'error' => "cURL error ({$errNo}): {$errMsg}",
                'http_code' => $httpCode,
                'body' => null
            ];
        }

        return [
            'success' => true,
            'http_code' => $httpCode,
            'body' => $body
        ];
    }

    /**
     * Audit log for SMS operations in notification_logs table (masks phone and API keys)
     */
    private function logSmsAudit(string $phone, string $template, string $content, string $status, ?string $rawResponse = null): void {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                INSERT INTO notification_logs (channel, recipient, subject_or_template, content, status, response)
                VALUES ('sms', ?, ?, ?, ?, ?)
            ");
            $maskedPhone = $this->maskPhone($phone);
            // Redact any UUID/API key patterns from response before saving
            $sanitized = $rawResponse !== null ? preg_replace('/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/i', '[MASKED_KEY]', $rawResponse) : null;
            $stmt->execute([$maskedPhone, $template, $content, $status, $sanitized]);
        } catch (\Throwable $e) {
            // Fail silently so database log issues never disrupt user flow
        }
    }
}
