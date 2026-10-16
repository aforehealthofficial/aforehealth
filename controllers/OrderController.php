<?php
namespace Controllers;

use Models\Order;
use Services\InvoiceService;

class OrderController {
    public function success(): void {
        $orderNumber = $_GET['order'] ?? '';
        $orderModel = new Order();
        $order = null;

        if (!empty($orderNumber)) {
            $order = $orderModel->findByOrderNumber($orderNumber);
        }

        if (!$order && !empty($_SESSION['last_order'])) {
            $order = $_SESSION['last_order'];
        }

        if (!$order) {
            redirect('/shop');
        }

        $whatsappCommunityUrl = get_setting('whatsapp_group_url', 'https://chat.whatsapp.com/C7fLq9wAFOREHEALTHcommunity');
        $whatsappSupportUrl = 'https://wa.me/' . preg_replace('/[^0-9]/', '', get_setting('whatsapp_number', '919654504052')) . '?text=' . urlencode("Hi AFOREHEALTH! I have a question regarding order #" . $order['order_number']);

        view('storefront.order-success', [
            'pageTitle' => 'Order Placed Successfully! - AFOREHEALTH',
            'order' => $order,
            'whatsappCommunityUrl' => $whatsappCommunityUrl,
            'whatsappSupportUrl' => $whatsappSupportUrl
        ]);
    }

    public function track(): void {
        $order = null;
        $error = null;
        $orderNumber = trim($_GET['order_number'] ?? $_POST['order_number'] ?? '');
        $mobile = trim($_GET['mobile'] ?? $_POST['mobile'] ?? '');

        if (!empty($orderNumber)) {
            $orderModel = new Order();
            $order = $orderModel->findByOrderNumber($orderNumber);

            if ($order && !empty($mobile)) {
                $cleanInput = preg_replace('/[^0-9]/', '', $mobile);
                $cleanOrdMobile = preg_replace('/[^0-9]/', '', $order['customer_mobile']);
                if ($cleanInput !== $cleanOrdMobile) {
                    $order = null;
                    $error = 'The mobile number does not match this order number.';
                }
            } else if (!$order) {
                $error = 'Order not found. Please check your order reference number.';
            }
        }

        view('storefront.order-track', [
            'pageTitle' => 'Track Your Order - AFOREHEALTH',
            'order' => $order,
            'error' => $error,
            'orderNumber' => $orderNumber,
            'mobile' => $mobile
        ]);
    }

    public function invoice(string $orderNumber): void {
        $orderModel = new Order();
        $order = $orderModel->findByOrderNumber($orderNumber);

        if (!$order) {
            http_response_code(404);
            die('Invoice not found.');
        }

        // Allow owner or admin to view invoice
        if (!is_admin()) {
            if (!is_authenticated() || auth_user()['id'] != $order['user_id']) {
                // Check if last_order in session matches
                if (empty($_SESSION['last_order']) || $_SESSION['last_order']['order_number'] !== $orderNumber) {
                    http_response_code(403);
                    die('Unauthorized invoice access.');
                }
            }
        }

        $invoiceService = new InvoiceService();
        echo $invoiceService->renderInvoiceHtml($order);
        exit;
    }
}
