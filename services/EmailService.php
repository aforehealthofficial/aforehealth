<?php
namespace Services;

use PDO;
use Exception;

class EmailService {
    private string $host;
    private int $port;
    private string $encryption;
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;

    public function __construct() {
        $this->host = get_setting('smtp_host', env('MAIL_HOST', 'smtp.hostinger.com')) ?: 'smtp.hostinger.com';
        $this->port = (int)(get_setting('smtp_port', env('MAIL_PORT', 465)) ?: 465);
        $this->encryption = strtolower(get_setting('smtp_encryption', env('MAIL_ENCRYPTION', 'ssl')) ?: 'ssl');
        $this->username = get_setting('smtp_user', env('MAIL_USERNAME', 'info@aforehealth.com')) ?: 'info@aforehealth.com';
        $this->password = get_setting('smtp_pass', env('MAIL_PASSWORD', '')) ?: '';
        // Sender MUST always be info@aforehealth.com as requested
        $this->fromEmail = get_setting('smtp_from', env('MAIL_FROM_ADDRESS', 'info@aforehealth.com')) ?: 'info@aforehealth.com';
        $this->fromName = get_setting('smtp_from_name', env('MAIL_FROM_NAME', 'AFOREHEALTH')) ?: 'AFOREHEALTH';
    }

    /**
     * Send email using direct Hostinger SMTP socket connection
     * Returns array with status, message, and diagnostic logs
     */
    public function send(string $toEmail, string $subject, string $htmlBody, array $options = []): array {
        $toEmail = trim($toEmail);
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            $this->logNotification($toEmail, $subject, $htmlBody, 'failed', 'Invalid recipient email format.');
            return ['success' => false, 'message' => 'Invalid recipient email format.', 'logs' => []];
        }

        $logs = [];
        $fromEmail = $this->fromEmail ?: 'info@aforehealth.com';
        $fromName = $this->fromName ?: 'AFOREHEALTH';
        $replyTo = $options['reply_to'] ?? $fromEmail;

        // Wrapped HTML Template
        $fullHtml = $this->wrapEmailTemplate($subject, $htmlBody);

        // If no SMTP password configured yet, log as simulated/pending
        if (empty($this->password)) {
            $msg = "SMTP password for {$this->username} is not configured yet in Admin Settings. Email saved to logs.";
            $logs[] = $msg;
            $this->logNotification($toEmail, $subject, $fullHtml, 'simulated', $msg);
            return [
                'success' => true,
                'simulated' => true,
                'message' => $msg,
                'logs' => $logs
            ];
        }

        $timeout = 15;
        $socket = null;
        $connectionPrefix = ($this->encryption === 'ssl' || $this->port === 465) ? 'ssl://' : 'tcp://';

        try {
            $errno = 0;
            $errstr = '';
            $socket = @stream_socket_client(
                "{$connectionPrefix}{$this->host}:{$this->port}",
                $errno,
                $errstr,
                $timeout,
                STREAM_CLIENT_CONNECT
            );

            if (!$socket) {
                throw new Exception("Unable to connect to {$this->host}:{$this->port} - {$errstr} ({$errno})");
            }

            stream_set_timeout($socket, $timeout);

            // Read banner
            $banner = $this->readResponse($socket, $logs);
            if (!str_starts_with($banner, '220')) {
                throw new Exception("Invalid greeting banner from SMTP server: {$banner}");
            }

            // EHLO
            $this->sendCommand($socket, "EHLO aforehealth.com", $logs);
            $ehloRes = $this->readResponse($socket, $logs);
            if (!str_starts_with($ehloRes, '250')) {
                throw new Exception("EHLO rejected: {$ehloRes}");
            }

            // STARTTLS if port 587 or tls
            if ($this->encryption === 'tls' && $this->port !== 465) {
                $this->sendCommand($socket, "STARTTLS", $logs);
                $tlsRes = $this->readResponse($socket, $logs);
                if (!str_starts_with($tlsRes, '220')) {
                    throw new Exception("STARTTLS negotiation failed: {$tlsRes}");
                }
                $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if (!$crypto) {
                    throw new Exception("TLS encryption handshake failed.");
                }
                // Re-send EHLO after TLS
                $this->sendCommand($socket, "EHLO aforehealth.com", $logs);
                $this->readResponse($socket, $logs);
            }

            // Authentication (AUTH LOGIN)
            $this->sendCommand($socket, "AUTH LOGIN", $logs);
            $authRes = $this->readResponse($socket, $logs);
            if (!str_starts_with($authRes, '334')) {
                throw new Exception("AUTH LOGIN not accepted: {$authRes}");
            }

            // Username
            $this->sendCommand($socket, base64_encode($this->username), $logs, true);
            $userRes = $this->readResponse($socket, $logs);
            if (!str_starts_with($userRes, '334')) {
                throw new Exception("Username rejected: {$userRes}");
            }

            // Password
            $this->sendCommand($socket, base64_encode($this->password), $logs, true);
            $passRes = $this->readResponse($socket, $logs);
            if (!str_starts_with($passRes, '235')) {
                throw new Exception("Authentication failed for {$this->username}: {$passRes}");
            }

            // MAIL FROM
            $this->sendCommand($socket, "MAIL FROM:<{$fromEmail}>", $logs);
            $mfRes = $this->readResponse($socket, $logs);
            if (!str_starts_with($mfRes, '250')) {
                throw new Exception("MAIL FROM rejected: {$mfRes}");
            }

            // RCPT TO
            $this->sendCommand($socket, "RCPT TO:<{$toEmail}>", $logs);
            $rcptRes = $this->readResponse($socket, $logs);
            if (!str_starts_with($rcptRes, '250')) {
                throw new Exception("RCPT TO rejected for {$toEmail}: {$rcptRes}");
            }

            // DATA
            $this->sendCommand($socket, "DATA", $logs);
            $dataRes = $this->readResponse($socket, $logs);
            if (!str_starts_with($dataRes, '354')) {
                throw new Exception("DATA command rejected: {$dataRes}");
            }

            // Build RFC-compliant multipart/alternative MIME message
            $boundary = "----=_Part_" . md5(uniqid((string)mt_rand(), true));
            $encodedSubject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
            $encodedFromName = "=?UTF-8?B?" . base64_encode($fromName) . "?=";
            $msgId = "<" . gmdate('YmdHis') . "." . bin2hex(random_bytes(6)) . "@aforehealth.com>";
            $date = date('r');
            $plainText = $this->htmlToPlainText($fullHtml);

            $headers = [
                "Date: {$date}",
                "From: {$encodedFromName} <{$fromEmail}>",
                "Reply-To: {$encodedFromName} <{$replyTo}>",
                "To: <{$toEmail}>",
                "Subject: {$encodedSubject}",
                "Message-ID: {$msgId}",
                "MIME-Version: 1.0",
                "Content-Type: multipart/alternative; boundary=\"{$boundary}\"",
                "X-Mailer: AFOREHEALTH Mailer",
                "X-Sender: {$fromEmail}"
            ];

            $messagePayload = implode("\r\n", $headers) . "\r\n\r\n"
                . "--{$boundary}\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: 8bit\r\n\r\n"
                . $plainText . "\r\n\r\n"
                . "--{$boundary}\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($fullHtml)) . "\r\n"
                . "--{$boundary}--\r\n"
                . ".\r\n";

            fputs($socket, $messagePayload);
            fflush($socket);

            $sendRes = $this->readResponse($socket, $logs);
            if (!str_starts_with($sendRes, '250')) {
                throw new Exception("Message content rejected by SMTP server: {$sendRes}");
            }

            // QUIT
            $this->sendCommand($socket, "QUIT", $logs);
            $this->readResponse($socket, $logs);
            @fclose($socket);

            $this->logNotification($toEmail, $subject, $fullHtml, 'sent', "Sent successfully via Hostinger SMTP. Server response: {$sendRes}");

            return [
                'success' => true,
                'message' => "Email sent successfully from {$fromEmail} to {$toEmail}.",
                'logs' => $logs
            ];

        } catch (Exception $e) {
            if ($socket) {
                @fputs($socket, "QUIT\r\n");
                @fclose($socket);
            }

            $errMsg = $e->getMessage();
            $logs[] = "ERROR: " . $errMsg;
            $this->logNotification($toEmail, $subject, $fullHtml, 'failed', $errMsg . "\n" . implode("\n", $logs));

            return [
                'success' => false,
                'message' => $errMsg,
                'logs' => $logs
            ];
        }
    }

    private function sendCommand($socket, string $cmd, array &$logs, bool $masked = false): void {
        $logs[] = "CLIENT: " . ($masked ? '******' : $cmd);
        fputs($socket, $cmd . "\r\n");
        fflush($socket);
    }

    private function readResponse($socket, array &$logs): string {
        $res = '';
        while ($str = fgets($socket, 515)) {
            $res .= $str;
            if (substr($str, 3, 1) === ' ') {
                break;
            }
        }
        $trimmed = trim($res);
        $logs[] = "SERVER: " . $trimmed;
        return $trimmed;
    }

    /**
     * Send order confirmation email to customer
     */
    public function sendOrderConfirmation(string $toEmail, array $order): bool {
        $subject = "Order Confirmed: #{$order['order_number']} - AFOREHEALTH";
        
        $customerName = e($order['customer_name'] ?? 'Valued Customer');
        $orderNumber = e($order['order_number']);
        $totalFormatted = number_format((float)($order['grand_total'] ?? 0), 2);
        $paymentMethod = strtoupper($order['payment_method'] ?? 'ONLINE');
        $orderStatus = e($order['order_status'] ?? 'CONFIRMED');
        $communityUrl = e(get_setting('whatsapp_group_url', 'https://chat.whatsapp.com/C7fLq9wAFOREHEALTHcommunity'));
        $trackUrl = url("track-order?order_number={$orderNumber}&mobile=" . ($order['customer_mobile'] ?? ''));

        $html = "
            <h2 style='color: #252126; font-size: 20px; margin-top: 0; margin-bottom: 8px;'>Thank you for your order, {$customerName}!</h2>
            <p style='color: #555; line-height: 1.6; font-size: 15px; margin-bottom: 20px;'>
                Your order <strong>#{$orderNumber}</strong> has been received and confirmed. We are carefully packing your comfort essentials in <strong>100% discreet, tamper-proof packaging</strong>.
            </p>

            <div style='background: #FFF9FA; border: 1px solid #F5E6ED; border-radius: 12px; padding: 18px; margin-bottom: 22px;'>
                <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                    <tr>
                        <td style='padding: 8px 0; color: #777;'>Order Number:</td>
                        <td style='padding: 8px 0; text-align: right; font-weight: bold; color: #252126;'>#{$orderNumber}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #777;'>Order Status:</td>
                        <td style='padding: 8px 0; text-align: right; font-weight: bold; color: #2E7D32;'>{$orderStatus}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #777;'>Payment Mode:</td>
                        <td style='padding: 8px 0; text-align: right; font-weight: bold; color: #252126;'>{$paymentMethod}</td>
                    </tr>
                    <tr style='border-top: 1px dashed #E0D3DB;'>
                        <td style='padding: 12px 0 4px; font-weight: bold; font-size: 16px; color: #252126;'>Grand Total:</td>
                        <td style='padding: 12px 0 4px; text-align: right; font-weight: bold; font-size: 18px; color: #8B5E83;'>₹{$totalFormatted}</td>
                    </tr>
                </table>
            </div>

            <div style='text-align: center; margin: 25px 0;'>
                <a href='{$trackUrl}' style='background: #8B5E83; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block; font-size: 14px; box-shadow: 0 4px 12px rgba(139,94,131,0.25);'>Track Your Order Live</a>
            </div>

            <div style='background: #FDF4E3; border: 1px solid #F3DFC1; border-radius: 10px; padding: 14px; margin-top: 25px; text-align: center;'>
                <p style='color: #6D4C00; margin: 0 0 10px; font-size: 13px; font-weight: 600;'>Join our VIP Community for member perks & private discounts:</p>
                <a href='{$communityUrl}' style='background: #25D366; color: #fff; padding: 8px 18px; text-decoration: none; border-radius: 20px; font-size: 13px; font-weight: bold; display: inline-block;'>Join AFOREHEALTH Community</a>
            </div>
        ";

        $res = $this->send($toEmail, $subject, $html);
        return $res['success'];
    }

    /**
     * Send notification to admin on new order
     */
    public function sendAdminNewOrderAlert(array $order): bool {
        $adminEmail = get_setting('store_email', 'info@aforehealth.com');
        $subject = "🚨 New Order Received: #{$order['order_number']} - ₹" . number_format((float)($order['grand_total'] ?? 0), 2);
        
        $orderNumber = e($order['order_number']);
        $name = e($order['customer_name'] ?? '');
        $mobile = e($order['customer_mobile'] ?? '');
        $email = e($order['customer_email'] ?? 'N/A');
        $total = number_format((float)($order['grand_total'] ?? 0), 2);
        $method = strtoupper($order['payment_method'] ?? 'ONLINE');
        $adminOrderUrl = url('admin/orders/' . $orderNumber);

        $html = "
            <h3 style='color: #8B5E83; margin-top: 0;'>New Order Placed on AFOREHEALTH Store</h3>
            <p style='color: #444; font-size: 14px;'>A new order has been received and requires processing:</p>
            <table style='width: 100%; border-collapse: collapse; font-size: 14px; background: #FFF9FA; border: 1px solid #f0e6ed; border-radius: 8px;'>
                <tr><td style='padding: 10px; font-weight: bold;'>Order Number</td><td style='padding: 10px;'>#{$orderNumber}</td></tr>
                <tr><td style='padding: 10px; font-weight: bold;'>Customer Name</td><td style='padding: 10px;'>{$name}</td></tr>
                <tr><td style='padding: 10px; font-weight: bold;'>Customer Mobile</td><td style='padding: 10px;'>{$mobile}</td></tr>
                <tr><td style='padding: 10px; font-weight: bold;'>Customer Email</td><td style='padding: 10px;'>{$email}</td></tr>
                <tr><td style='padding: 10px; font-weight: bold;'>Total Amount</td><td style='padding: 10px; color: #8B5E83; font-weight: bold;'>₹{$total}</td></tr>
                <tr><td style='padding: 10px; font-weight: bold;'>Payment Mode</td><td style='padding: 10px;'>{$method}</td></tr>
            </table>
            <div style='text-align: center; margin-top: 20px;'>
                <a href='{$adminOrderUrl}' style='background: #252126; color: #fff; padding: 10px 22px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; font-size: 13px;'>View Order in Admin Panel</a>
            </div>
        ";

        $res = $this->send($adminEmail, $subject, $html);
        return $res['success'];
    }

    /**
     * Send order status update to customer (Dispatched, Delivered, etc.)
     */
    public function sendOrderStatusUpdate(string $toEmail, array $order, string $newStatus, ?string $courier = null, ?string $tracking = null, ?string $notes = null): bool {
        $subject = "Order Update: #{$order['order_number']} is now {$newStatus} - AFOREHEALTH";
        
        $customerName = e($order['customer_name'] ?? 'Customer');
        $orderNumber = e($order['order_number']);
        $trackUrl = url("track-order?order_number={$orderNumber}&mobile=" . ($order['customer_mobile'] ?? ''));

        $courierInfo = "";
        if (!empty($courier) || !empty($tracking)) {
            $courierInfo = "
                <div style='background: #F0F7FF; border: 1px solid #D0E4FF; border-radius: 8px; padding: 12px; margin: 15px 0;'>
                    <p style='margin: 0; font-size: 13px; color: #1E40AF;'><strong>Courier:</strong> " . e($courier ?? 'Express Logistics') . "</p>
                    " . (!empty($tracking) ? "<p style='margin: 5px 0 0; font-size: 13px; color: #1E40AF;'><strong>Tracking ID / AWB:</strong> " . e($tracking) . "</p>" : "") . "
                </div>
            ";
        }

        $notesInfo = !empty($notes) ? "<p style='color: #666; font-size: 13px; font-style: italic;'><strong>Note from dispatch team:</strong> " . e($notes) . "</p>" : "";

        $html = "
            <h2 style='color: #252126; font-size: 18px; margin-top: 0;'>Dear {$customerName},</h2>
            <p style='color: #555; line-height: 1.6; font-size: 14px;'>
                The status of your order <strong>#{$orderNumber}</strong> has been updated to: <span style='display: inline-block; padding: 3px 10px; background: #8B5E83; color: #fff; border-radius: 12px; font-weight: bold; font-size: 12px;'>{$newStatus}</span>
            </p>
            {$courierInfo}
            {$notesInfo}
            <div style='text-align: center; margin: 25px 0;'>
                <a href='{$trackUrl}' style='background: #8B5E83; color: #fff; padding: 10px 24px; text-decoration: none; border-radius: 30px; font-weight: bold; display: inline-block; font-size: 13px;'>Track Shipment Status</a>
            </div>
        ";

        $res = $this->send($toEmail, $subject, $html);
        return $res['success'];
    }

    /**
     * Send contact form inquiry alert to admin and acknowledgment to customer
     */
    public function sendContactFormNotification(array $contact): bool {
        $adminEmail = get_setting('store_email', 'info@aforehealth.com');
        $subject = "💬 New Contact Inquiry: " . ($contact['subject'] ?? 'General Inquiry');
        
        $name = e($contact['name'] ?? '');
        $mobile = e($contact['mobile'] ?? '');
        $email = e($contact['email'] ?? 'Not provided');
        $subj = e($contact['subject'] ?? 'General Inquiry');
        $msg = nl2br(e($contact['message'] ?? ''));

        $html = "
            <h3 style='color: #8B5E83; margin-top: 0;'>New Contact Inquiry from Website</h3>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Mobile:</strong> {$mobile}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Subject:</strong> {$subj}</p>
            <div style='background: #FFF9FA; border-left: 3px solid #8B5E83; padding: 12px; margin-top: 10px;'>
                <strong>Message:</strong><br>{$msg}
            </div>
        ";

        $this->send($adminEmail, $subject, $html, [
            'reply_to' => !empty($contact['email']) ? $contact['email'] : $adminEmail
        ]);

        // Acknowledgment back to customer if email was given
        if (!empty($contact['email']) && filter_var($contact['email'], FILTER_VALIDATE_EMAIL)) {
            $ackSubject = "We received your message - AFOREHEALTH Support";
            $ackHtml = "
                <h3 style='color: #252126; margin-top: 0;'>Hello {$name},</h3>
                <p style='color: #555; line-height: 1.6;'>Thank you for reaching out to AFOREHEALTH. We have received your inquiry regarding <strong>{$subj}</strong>.</p>
                <p style='color: #555; line-height: 1.6;'>Our support team typically responds within 24 hours. If your query is urgent, feel free to reach our WhatsApp support team directly.</p>
                <div style='margin-top: 20px; text-align: center;'>
                    <a href='" . e(get_setting('whatsapp_support_url', 'https://wa.me/919876543210')) . "' style='background: #25D366; color: #fff; padding: 10px 20px; border-radius: 25px; text-decoration: none; font-weight: bold; display: inline-block;'>Chat on WhatsApp</a>
                </div>
            ";
            $this->send($contact['email'], $ackSubject, $ackHtml);
        }

        return true;
    }

    /**
     * Send test verification email with detailed diagnostics
     */
    public function sendTestEmail(string $toEmail): array {
        $subject = "Order Confirmed (Sample): #AFH-SAMPLE-0101 - AFOREHEALTH";
        $storeUrl = url('/');
        $whatsappUrl = e(get_setting('whatsapp_group_url', 'https://chat.whatsapp.com/C7fLq9wAFOREHEALTHcommunity'));

        $html = "
            <h2 style='color: #252126; font-size: 20px; margin-top: 0;'>Thank you for your order!</h2>
            <p style='color: #555; line-height: 1.6; font-size: 15px;'>
                This is a sample order confirmation from <strong>AFOREHEALTH</strong> confirming your outgoing email system is operating perfectly.
            </p>
            <div style='background: #FFF9FA; border: 1px solid #F5E6ED; border-radius: 12px; padding: 18px; margin: 20px 0;'>
                <table style='width: 100%; border-collapse: collapse; font-size: 14px;'>
                    <tr><td style='padding: 6px 0; color: #666;'>Order Number:</td><td style='padding: 6px 0; text-align: right; font-weight: bold;'>#AFH-SAMPLE-0101</td></tr>
                    <tr><td style='padding: 6px 0; color: #666;'>Items:</td><td style='padding: 6px 0; text-align: right;'>AFOREHEALTH Comfort Essentials (Pack of 3)</td></tr>
                    <tr><td style='padding: 6px 0; color: #666;'>Payment Status:</td><td style='padding: 6px 0; text-align: right; font-weight: bold; color: #2e7d32;'>Confirmed</td></tr>
                    <tr><td style='padding: 6px 0; color: #666;'>Packaging:</td><td style='padding: 6px 0; text-align: right;'>100% Discreet &amp; Tamper-Proof</td></tr>
                    <tr style='border-top: 1px dashed #E0D3DB;'><td style='padding: 10px 0 0; font-weight: bold;'>Total Amount:</td><td style='padding: 10px 0 0; text-align: right; font-weight: bold; color: #8B5E83; font-size: 16px;'>₹1,499.00</td></tr>
                </table>
            </div>
            <div style='text-align: center; margin: 25px 0;'>
                <a href='{$storeUrl}' style='background: #8B5E83; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block; font-size: 14px;'>Visit AFOREHEALTH Store</a>
            </div>
            <p style='color: #777; font-size: 13px; line-height: 1.6; margin-top: 20px;'>
                If you have questions, reply directly to this email at <a href='mailto:info@aforehealth.com' style='color: #8B5E83; text-decoration: none;'>info@aforehealth.com</a>.
            </p>
        ";

        return $this->send($toEmail, $subject, $html);
    }

    /**
     * Convert HTML to clean plain text for multipart MIME
     */
    private function htmlToPlainText(string $html): string {
        $text = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $text = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $text);
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<\/p>/i', "\n\n", $text);
        $text = preg_replace('/<\/tr>/i', "\n", $text);
        $text = preg_replace('/<\/li>/i', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s*\n+/', "\n\n", $text);
        return trim($text);
    }

    /**
     * Wrap content in the official AFOREHEALTH branded responsive email template
     */
    private function wrapEmailTemplate(string $title, string $innerHtml): string {
        $storeName = e($this->fromName);
        $supportEmail = e($this->fromEmail);
        $phone = e(get_setting('store_phone', '+91 98765 43210'));
        $address = e(get_setting('store_address', 'AFOREHEALTH Wellness Pvt. Ltd., Mumbai, India'));
        $logoUrl = asset('images/logo.png');
        $siteUrl = url('/');

        return "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>" . e($title) . "</title>
</head>
<body style='margin: 0; padding: 0; background-color: #F8F5F7; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;'>
    <table role='presentation' width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #F8F5F7; padding: 30px 10px;'>
        <tr>
            <td align='center'>
                <table role='presentation' width='600' border='0' cellspacing='0' cellpadding='0' style='max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #EFE8ED;'>
                    <!-- Brand Header -->
                    <tr>
                        <td align='center' style='padding: 28px 24px; background: linear-gradient(135deg, #8B5E83 0%, #6E4567 100%); text-align: center;'>
                            <a href='{$siteUrl}' style='text-decoration: none;'>
                                <span style='font-size: 26px; font-weight: 800; letter-spacing: 2px; color: #ffffff; text-transform: uppercase; font-family: \"Outfit\", sans-serif; display: inline-block;'>AFOREHEALTH</span>
                            </a>
                            <div style='color: #F5E6ED; font-size: 13px; font-weight: 500; margin-top: 4px; letter-spacing: 0.5px;'>
                                Everyday Comfort &amp; Confidence
                            </div>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 32px 28px; color: #252126; font-size: 14px; line-height: 1.6;'>
                            {$innerHtml}
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style='padding: 0 28px;'>
                            <div style='height: 1px; background: #EFE8ED; width: 100%;'></div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='padding: 24px 28px; background-color: #FCFAFB; text-align: center; color: #7A7479; font-size: 12px; line-height: 1.7;'>
                            <p style='margin: 0 0 6px;'>
                                Need assistance? Email our concierge team at <a href='mailto:{$supportEmail}' style='color: #8B5E83; font-weight: bold; text-decoration: none;'>{$supportEmail}</a> or call <span style='color: #252126;'>{$phone}</span>.
                            </p>
                            <p style='margin: 0 0 10px; color: #A09A9F;'>
                                {$address}
                            </p>
                            <p style='margin: 0; color: #B3ACB2; font-size: 11px;'>
                                &copy; " . date('Y') . " {$storeName}. 100% Discreet Packaging &bull; Dermatologist Tested &bull; Made with Love in India.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>";
    }

    /**
     * Log email to notification_logs table
     */
    private function logNotification(string $to, string $subject, string $html, string $status, ?string $response = null): void {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                INSERT INTO notification_logs (channel, recipient, subject_or_template, content, status, response, created_at)
                VALUES ('email', ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$to, $subject, $html, $status, $response]);
        } catch (Exception $e) {
            error_log("EmailService log error: " . $e->getMessage());
        }
    }
}
