<?php
namespace Controllers;

use Models\Cart;
use Models\Coupon;

class CartController {
    public function index(): void {
        $cartModel = new Cart();
        $cart = $cartModel->getCartDetails();

        view('storefront.cart', [
            'pageTitle' => 'Shopping Bag (' . $cart['item_count'] . ') - AFOREHEALTH',
            'cart' => $cart
        ]);
    }

    public function add(): void {
        $cartModel = new Cart();
        $productId = (int)($_POST['product_id'] ?? 0);
        $variantId = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if (!$productId) {
            json_response(['success' => false, 'message' => 'Invalid product.'], 400);
        }

        $res = $cartModel->addItem($productId, $variantId, $quantity);
        $cart = $cartModel->getCartDetails();
        $res['cart'] = $cart;
        $res['cart_count'] = $cart['item_count'];

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            json_response($res);
        }

        if ($res['success']) {
            set_flash('success', $res['message']);
        } else {
            set_flash('danger', $res['message']);
        }
        redirect('/cart');
    }

    public function update(): void {
        $cartModel = new Cart();
        $itemId = (int)($_POST['item_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        $res = $cartModel->updateQuantity($itemId, $quantity);
        $cart = $cartModel->getCartDetails();
        $res['cart'] = $cart;
        $res['cart_count'] = $cart['item_count'];

        json_response($res);
    }

    public function remove(): void {
        $cartModel = new Cart();
        $itemId = (int)($_POST['item_id'] ?? 0);

        $res = $cartModel->removeItem($itemId);
        $cart = $cartModel->getCartDetails();
        $res['cart'] = $cart;
        $res['cart_count'] = $cart['item_count'];

        json_response($res);
    }

    public function applyCoupon(): void {
        $code = trim($_POST['code'] ?? '');
        if (empty($code)) {
            json_response(['success' => false, 'message' => 'Please enter a coupon code.'], 400);
        }

        $cartModel = new Cart();
        $cart = $cartModel->getCartDetails();

        $couponModel = new Coupon();
        $userId = auth_user()['id'] ?? null;
        $result = $couponModel->validateCoupon($code, (float)$cart['subtotal'], $userId);

        if (!$result['valid']) {
            json_response(['success' => false, 'message' => $result['message']], 400);
        }

        $_SESSION['applied_coupon'] = $result['coupon'];
        $updatedCart = $cartModel->getCartDetails();

        json_response([
            'success' => true,
            'message' => $result['message'],
            'cart' => $updatedCart
        ]);
    }

    public function removeCoupon(): void {
        unset($_SESSION['applied_coupon']);
        $cartModel = new Cart();
        $cart = $cartModel->getCartDetails();

        json_response([
            'success' => true,
            'message' => 'Coupon removed.',
            'cart' => $cart
        ]);
    }

    public function drawerData(): void {
        $cartModel = new Cart();
        $cart = $cartModel->getCartDetails();
        json_response(['success' => true, 'cart' => $cart]);
    }

    public function recover(): void {
        $token = trim($_GET['token'] ?? '');
        if (empty($token)) {
            redirect('/cart');
        }

        $cartModel = new Cart();
        $restored = $cartModel->restoreByToken($token);

        if ($restored) {
            set_flash('success', '✨ Welcome back! Your cart items have been restored, and an EXTRA 10% OFF coupon (RECOVER10) has been auto-applied!');
            redirect('/checkout');
        } else {
            set_flash('warning', 'Cart recovery link has expired or is invalid.');
            redirect('/cart');
        }
    }
}
