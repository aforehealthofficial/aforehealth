<?php
namespace Controllers;

use Models\Cart;
use Models\Order;
use Models\User;
use Services\PaymentService;
use Services\EmailService;
use Services\SmsService;
use Exception;

class CheckoutController {
    public function index(): void {
        $cartModel = new Cart();
        $cart = $cartModel->getCartDetails();

        if (empty($cart['items'])) {
            set_flash('info', 'Your bag is empty. Please add items before proceeding to checkout.');
            redirect('/shop');
        }

        $userModel = new User();
        $savedAddresses = [];
        $user = auth_user();

        if ($user) {
            $savedAddresses = $userModel->getAddresses($user['id']);
            $cartModel->updateLead($cartModel->getActiveCartId(), $user['name'] ?? null, $user['mobile'] ?? null, $user['email'] ?? null);
        }

        $paymentService = new PaymentService();
        $razorpayKey = $paymentService->getKeyId();

        $codEnabled = (bool)get_setting('cod_enabled', 1);
        $codFee = (float)get_setting('cod_fee', 40);
        $codMin = (float)get_setting('cod_min_order', 299);
        $codMax = (float)get_setting('cod_max_order', 5000);

        // Generate Idempotency Token
        if (empty($_SESSION['checkout_idempotency_token'])) {
            $_SESSION['checkout_idempotency_token'] = bin2hex(random_bytes(16));
        }

        // Check if customer is placing their first order
        $orderModel = new Order();
        $isFirstOrder = true;
        if ($user) {
            $isFirstOrder = $orderModel->isFirstOrder($user['id'], $user['mobile'] ?? '');
        }

        view('storefront.checkout', [
            'pageTitle' => 'Secure Checkout - AFOREHEALTH',
            'cart' => $cart,
            'user' => $user,
            'savedAddresses' => $savedAddresses,
            'isFirstOrder' => $isFirstOrder,
            'razorpayKey' => $razorpayKey,
            'codEnabled' => $codEnabled,
            'codFee' => $codFee,
            'codMin' => $codMin,
            'codMax' => $codMax,
            'idempotencyToken' => $_SESSION['checkout_idempotency_token']
        ]);
    }

    public function process(): void {
        $cartModel = new Cart();
        $cart = $cartModel->getCartDetails();

        if (empty($cart['items'])) {
            json_response(['success' => false, 'message' => 'Your shopping bag is empty.'], 400);
        }

        // Idempotency verification: prevent double-clicks & duplicate submissions
        $idempotencyToken = $_POST['idempotency_token'] ?? '';
        if (!empty($idempotencyToken) && !empty($_SESSION['processed_idempotency_tokens'][$idempotencyToken])) {
            $prevOrderNumber = $_SESSION['processed_idempotency_tokens'][$idempotencyToken];
            json_response([
                'success' => true,
                'order_number' => $prevOrderNumber,
                'redirect_url' => url('order-success?order=' . $prevOrderNumber)
            ]);
        }

        // Validate Input
        $fullName = trim($_POST['full_name'] ?? '');
        $mobile = preg_replace('/[^0-9]/', '', $_POST['mobile'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $house = trim($_POST['house_flat'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $landmark = trim($_POST['landmark'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? 'razorpay';
        $whatsappOptIn = !empty($_POST['whatsapp_opt_in']) ? 1 : 0;

        if (empty($fullName) || strlen($mobile) !== 10 || empty($house) || empty($city) || empty($state) || strlen($pincode) !== 6) {
            json_response(['success' => false, 'message' => 'Please provide a valid Indian shipping address with 10-digit mobile and 6-digit PIN code.'], 400);
        }

        $address = [
            'full_name' => $fullName,
            'mobile' => $mobile,
            'house_flat' => $house,
            'area' => $area,
            'landmark' => $landmark,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode
        ];

        // Save address for logged in customer if requested
        if (is_authenticated() && !empty($_POST['save_address'])) {
            $userModel = new User();
            $userModel->addAddress(auth_user()['id'], array_merge($address, ['address_type' => 'home', 'is_default' => 0]));
        }

        $checkoutData = [
            'full_name' => $fullName,
            'mobile' => $mobile,
            'email' => $email,
            'address' => $address,
            'payment_method' => $paymentMethod,
            'whatsapp_opt_in' => $whatsappOptIn,
            'notes' => $_POST['notes'] ?? null
        ];

        // Capture lead on cart for recovery if payment/order drops
        $cartModel->updateLead($cartModel->getActiveCartId(), $fullName, $mobile, $email);

        try {
            $orderModel = new Order();
            $orderResult = $orderModel->createFromCart($checkoutData);
            $orderNumber = $orderResult['order_number'];

            // Mark cart as successfully converted
            $cartModel->markRecovered($cartModel->getActiveCartId());

            // Store idempotency token
            if (!empty($idempotencyToken)) {
                if (!isset($_SESSION['processed_idempotency_tokens'])) {
                    $_SESSION['processed_idempotency_tokens'] = [];
                }
                $_SESSION['processed_idempotency_tokens'][$idempotencyToken] = $orderNumber;
                unset($_SESSION['checkout_idempotency_token']);
            }

            // Case A: COD Orders
            if ($paymentMethod === 'cod') {
                $order = $orderModel->findByOrderNumber($orderNumber);
                $emailService = new EmailService();
                $smsService = new SmsService();

                if (!empty($email)) {
                    $emailService->sendOrderConfirmation($email, $order);
                }
                $emailService->sendAdminNewOrderAlert($order);
                $smsService->sendOrderConfirmation($mobile, $orderNumber, $order['grand_total']);

                $_SESSION['last_order'] = $order;

                json_response([
                    'success' => true,
                    'order_number' => $orderNumber,
                    'redirect_url' => url('order-success?order=' . $orderNumber)
                ]);
            }

            // Case B: Online Gateway (Razorpay)
            $paymentService = new PaymentService();
            $rzpOrder = $paymentService->createOrder((float)$orderResult['grand_total'], $orderNumber);

            json_response([
                'success' => true,
                'requires_payment' => true,
                'razorpay_order_id' => $rzpOrder['id'],
                'razorpay_key' => $paymentService->getKeyId(),
                'amount' => $rzpOrder['amount'], // paise
                'currency' => 'INR',
                'order_number' => $orderNumber,
                'customer_name' => $fullName,
                'customer_email' => $email,
                'customer_mobile' => $mobile
            ]);

        } catch (Exception $e) {
            json_response(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()], 422);
        }
    }

    /**
     * Server-side cryptographic signature verification of Razorpay payment
     */
    public function verifyPayment(): void {
        $orderNumber = trim($_POST['order_number'] ?? '');
        $razorpayOrderId = trim($_POST['razorpay_order_id'] ?? '');
        $razorpayPaymentId = trim($_POST['razorpay_payment_id'] ?? '');
        $razorpaySignature = trim($_POST['razorpay_signature'] ?? '');

        if (empty($orderNumber) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
            json_response(['success' => false, 'message' => 'Incomplete payment credentials received.'], 400);
        }

        $orderModel = new Order();
        $order = $orderModel->findByOrderNumber($orderNumber);

        if (!$order) {
            json_response(['success' => false, 'message' => 'Referenced order could not be found.'], 404);
        }

        // Idempotency: If already paid, return success immediately
        if ($order['payment_status'] === 'paid') {
            $_SESSION['last_order'] = $order;
            json_response([
                'success' => true,
                'order_number' => $orderNumber,
                'redirect_url' => url('order-success?order=' . $orderNumber)
            ]);
        }

        // Cryptographic server-side HMAC validation
        $paymentService = new PaymentService();
        $isValid = $paymentService->verifySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

        if (!$isValid) {
            // Record failed payment and restore stock
            $orderModel->failOnlinePayment($orderNumber, "Signature verification failed for payment {$razorpayPaymentId}");
            json_response(['success' => false, 'message' => 'Payment signature verification failed. Your card was not charged.'], 400);
        }

        // Signature is cryptographically verified: confirm order and persist payment
        $orderModel->confirmOnlinePayment($orderNumber, [
            'razorpay_order_id' => $razorpayOrderId,
            'razorpay_payment_id' => $razorpayPaymentId,
            'razorpay_signature' => $razorpaySignature
        ]);

        $updatedOrder = $orderModel->findByOrderNumber($orderNumber);
        $_SESSION['last_order'] = $updatedOrder;

        // Dispatch notifications
        $emailService = new EmailService();
        $smsService = new SmsService();
        if (!empty($updatedOrder['customer_email'])) {
            $emailService->sendOrderConfirmation($updatedOrder['customer_email'], $updatedOrder);
        }
        $emailService->sendAdminNewOrderAlert($updatedOrder);
        $smsService->sendOrderConfirmation($updatedOrder['customer_mobile'], $orderNumber, $updatedOrder['grand_total']);

        json_response([
            'success' => true,
            'order_number' => $orderNumber,
            'redirect_url' => url('order-success?order=' . $orderNumber)
        ]);
    }

    /**
     * Handle payment dismissal or failure from client side
     */
    public function paymentFailed(): void {
        $orderNumber = trim($_POST['order_number'] ?? '');
        $reason = trim($_POST['reason'] ?? 'Payment cancelled or failed');

        if (!empty($orderNumber)) {
            $orderModel = new Order();
            $orderModel->failOnlinePayment($orderNumber, $reason);
        }

        json_response(['success' => true, 'message' => 'Payment failure recorded and stock released.']);
    }

    /**
     * Webhook endpoint for asynchronous Razorpay notifications
     */
    public function webhook(): void {
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        $paymentService = new PaymentService();
        if (!$paymentService->verifyWebhookSignature($payload, $signature)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid webhook signature.']);
            exit;
        }

        $data = json_decode($payload, true);
        $event = $data['event'] ?? '';

        if ($event === 'payment.captured') {
            $payment = $data['payload']['payment']['entity'] ?? [];
            $orderId = $payment['order_id'] ?? '';
            $notes = $payment['notes'] ?? [];
            $orderNumber = $notes['order_number'] ?? ($payment['description'] ?? '');

            if ($orderNumber) {
                $orderModel = new Order();
                $orderModel->confirmOnlinePayment($orderNumber, [
                    'razorpay_order_id' => $orderId,
                    'razorpay_payment_id' => $payment['id'] ?? '',
                    'razorpay_signature' => 'webhook_captured'
                ]);
            }
        } elseif ($event === 'payment.failed') {
            $payment = $data['payload']['payment']['entity'] ?? [];
            $notes = $payment['notes'] ?? [];
            $orderNumber = $notes['order_number'] ?? ($payment['description'] ?? '');
            if ($orderNumber) {
                $orderModel = new Order();
                $orderModel->failOnlinePayment($orderNumber, $payment['error_description'] ?? 'Webhook reported payment failure');
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
        exit;
    }
}
