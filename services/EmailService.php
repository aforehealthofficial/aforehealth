<?php
namespace Services;

class EmailService {
    public function sendOrderConfirmation(string $toEmail, array $order): bool {
        $subject = "Order Confirmed: #{$order['order_number']} - AFOREHEALTH";
        $html = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #f0e6ed; border-radius: 8px;'>
                <div style='text-align: center; padding-bottom: 20px; border-bottom: 2px solid #8B5E83;'>
                    <h2 style='color: #8B5E83; margin: 0;'>AFOREHEALTH</h2>
                    <p style='color: #777; font-size: 13px; margin: 5px 0 0;'>Everyday Comfort. Period Confidence.</p>
                </div>
                <div style='padding: 20px 0;'>
                    <h3 style='color: #252126;'>Thank you for your order, " . e($order['customer_name']) . "!</h3>
                    <p style='color: #555; line-height: 1.6;'>Your order <strong>#" . e($order['order_number']) . "</strong> has been placed successfully and is being packed with love and 100% discreet packaging.</p>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                        <tr style='background: #FFF9FA;'><td style='padding: 10px; font-weight: bold;'>Grand Total</td><td style='padding: 10px; text-align: right; font-weight: bold; color: #8B5E83;'>₹" . number_format($order['grand_total'], 0) . "</td></tr>
                        <tr><td style='padding: 10px;'>Payment Mode</td><td style='padding: 10px; text-align: right;'>" . strtoupper($order['payment_method']) . "</td></tr>
                        <tr><td style='padding: 10px;'>Order Status</td><td style='padding: 10px; text-align: right; color: #4E8B68; font-weight: bold;'>" . e($order['order_status']) . "</td></tr>
                    </table>
                </div>
                <div style='text-align: center; padding: 20px 0; background: #FFF9FA; border-radius: 6px;'>
                    <p style='color: #252126; margin: 0 0 10px; font-size: 14px;'>Join our sisterhood community for period tips and early access deals:</p>
                    <a href='" . e(get_setting('whatsapp_group_url')) . "' style='background: #25D366; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 20px; font-weight: bold; display: inline-block;'>Join AFOREHEALTH WhatsApp Community</a>
                </div>
            </div>
        ";

        return $this->logAndSend($toEmail, $subject, $html);
    }

    public function sendAdminNewOrderAlert(array $order): bool {
        $adminEmail = get_setting('store_email', 'admin@aforehealth.com');
        $subject = "New Order Received - " . $order['order_number'];
        $html = "
            <div style='font-family: Arial, sans-serif; padding: 20px;'>
                <h3 style='color: #8B5E83;'>New Order Alert: " . e($order['order_number']) . "</h3>
                <p><strong>Customer:</strong> " . e($order['customer_name']) . " (" . e($order['customer_mobile']) . ")</p>
                <p><strong>Amount:</strong> ₹" . number_format($order['grand_total'], 0) . "</p>
                <p><strong>Payment:</strong> " . strtoupper($order['payment_method']) . "</p>
                <p><a href='" . url('admin/orders') . "'>View Order in Admin Panel</a></p>
            </div>
        ";

        return $this->logAndSend($adminEmail, $subject, $html);
    }

    private function logAndSend(string $to, string $subject, string $html): bool {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                INSERT INTO notification_logs (channel, recipient, subject_or_template, content, status)
                VALUES ('email', ?, ?, ?, 'simulated')
            ");
            $stmt->execute([$to, $subject, $html]);
        } catch (\Exception $e) {
            error_log("EmailService log error: " . $e->getMessage());
        }

        return true;
    }
}
