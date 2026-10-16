<?php
namespace Controllers;

use Models\User;
use Models\Order;
use Models\Product;
use Models\Review;
use Models\Cart;
use Models\Loyalty;
use Models\Referral;
use PDO;

class AccountController {
    public function dashboard(): void {
        $userId = auth_user()['id'];
        $userModel = new User();
        $orderModel = new Order();
        $loyaltyModel = new Loyalty();

        $user = $userModel->find($userId);
        $allOrders = $orderModel->getUserOrders($userId);
        $recentOrders = array_slice($allOrders, 0, 5);
        $addresses = $userModel->getAddresses($userId);

        $totalSpent = array_sum(array_column($allOrders, 'grand_total'));
        $pointsBalance = $loyaltyModel->getBalance($userId);
        $wishlistCount = get_wishlist_count();
        $activeOrdersCount = count(array_filter($allOrders, fn($o) => in_array($o['order_status'], ['CONFIRMED', 'PROCESSING', 'PACKED', 'SHIPPED', 'OUT_FOR_DELIVERY'])));

        // Buy Again items
        $buyAgainProducts = $orderModel->getBuyAgainProducts($userId, 4);

        view('account.dashboard', [
            'pageTitle' => 'My Account - AFOREHEALTH',
            'user' => $user,
            'recentOrders' => $recentOrders,
            'addresses' => $addresses,
            'buyAgainProducts' => $buyAgainProducts,
            'stats' => [
                'total_orders' => count($allOrders),
                'total_spent' => $totalSpent,
                'points_balance' => $pointsBalance,
                'wishlist_count' => $wishlistCount,
                'active_orders_count' => $activeOrdersCount
            ]
        ]);
    }

    public function orders(): void {
        $userId = auth_user()['id'];
        $orderModel = new Order();
        $orders = $orderModel->getUserOrders($userId);

        view('account.orders', [
            'pageTitle' => 'My Orders - AFOREHEALTH',
            'orders' => $orders
        ]);
    }

    public function orderDetail(string $orderNumber): void {
        $userId = auth_user()['id'];
        $orderModel = new Order();
        $order = $orderModel->findByOrderNumber($orderNumber);

        if (!$order || $order['user_id'] != $userId) {
            http_response_code(404);
            view('errors.404', ['pageTitle' => 'Order Not Found - AFOREHEALTH']);
            return;
        }

        view('account.order-detail', [
            'pageTitle' => 'Order Details #' . $order['order_number'] . ' - AFOREHEALTH',
            'order' => $order
        ]);
    }

    public function reorder(): void {
        $orderNumber = trim($_POST['order_number'] ?? '');
        $userId = auth_user()['id'];

        $orderModel = new Order();
        $order = $orderModel->findByOrderNumber($orderNumber);

        if (!$order || $order['user_id'] != $userId) {
            set_flash('danger', 'Order not found.');
            redirect('/account/orders');
        }

        $result = $orderModel->reorder($order['id']);

        if ($result['success']) {
            set_flash('success', $result['message']);
            redirect('/cart');
        } else {
            set_flash('warning', $result['message']);
            redirect('/account/orders/' . $orderNumber);
        }
    }

    public function buyAgain(): void {
        $userId = auth_user()['id'];
        $orderModel = new Order();
        $products = $orderModel->getBuyAgainProducts($userId, 16);

        view('account.buy-again', [
            'pageTitle' => 'Buy Again - Repurchase Essentials | AFOREHEALTH',
            'products' => $products
        ]);
    }

    public function points(): void {
        $userId = auth_user()['id'];
        $loyaltyModel = new Loyalty();

        $balance = $loyaltyModel->getBalance($userId);
        $history = $loyaltyModel->getHistory($userId, 30);

        view('account.points', [
            'pageTitle' => 'AFORE Points & Rewards Ledger - AFOREHEALTH',
            'balance' => $balance,
            'history' => $history
        ]);
    }

    public function referral(): void {
        $userId = auth_user()['id'];
        $referralModel = new Referral();

        $code = $referralModel->getReferralCode($userId);
        $referrals = $referralModel->getUserReferrals($userId);

        $referralLink = url('register?ref=' . $code);
        $whatsappMsg = urlencode("Hey! I love AFOREHEALTH's leakproof period panties and intimate care essentials. Sign up using my link {$referralLink} and get flat ₹100 off your first order!");

        view('account.referral', [
            'pageTitle' => 'Refer & Earn AFORE Points - AFOREHEALTH',
            'code' => $code,
            'referralLink' => $referralLink,
            'whatsappUrl' => "https://api.whatsapp.com/send?text={$whatsappMsg}",
            'referrals' => $referrals
        ]);
    }

    public function questions(): void {
        $userId = auth_user()['id'];
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("
            SELECT pq.*, p.name as product_name, p.slug as product_slug, p.primary_image
            FROM product_questions pq
            JOIN products p ON pq.product_id = p.id
            WHERE pq.user_id = ?
            ORDER BY pq.created_at DESC
        ");
        $stmt->execute([$userId]);
        $questions = $stmt->fetchAll();

        view('account.questions', [
            'pageTitle' => 'My Product Questions - AFOREHEALTH',
            'questions' => $questions
        ]);
    }

    public function wishlist(): void {
        $user = auth_user();
        $pdo = getDbConnection();
        $products = [];

        if ($user) {
            $stmt = $pdo->prepare("
                SELECT p.*, c.name as category_name
                FROM wishlists w
                JOIN products p ON w.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                WHERE w.user_id = ?
                ORDER BY w.created_at DESC
            ");
            $stmt->execute([$user['id']]);
            $products = $stmt->fetchAll();
        } else {
            $guestWishlist = $_SESSION['guest_wishlist'] ?? [];
            if (!empty($guestWishlist)) {
                $placeholders = implode(',', array_fill(0, count($guestWishlist), '?'));
                $stmt = $pdo->prepare("
                    SELECT p.*, c.name as category_name
                    FROM products p
                    JOIN categories c ON p.category_id = c.id
                    WHERE p.id IN ($placeholders)
                ");
                $stmt->execute($guestWishlist);
                $products = $stmt->fetchAll();
            }
        }

        view('account.wishlist', [
            'pageTitle' => 'My Wishlist - AFOREHEALTH',
            'products' => $products,
            'items' => $products
        ]);
    }

    public function toggleWishlist(): void {
        $productId = (int)($_POST['product_id'] ?? 0);
        $user = auth_user();

        if (!$user) {
            if (!isset($_SESSION['guest_wishlist'])) {
                $_SESSION['guest_wishlist'] = [];
            }
            if (in_array($productId, $_SESSION['guest_wishlist'])) {
                $_SESSION['guest_wishlist'] = array_values(array_diff($_SESSION['guest_wishlist'], [$productId]));
                $inWishlist = false;
                $msg = 'Product removed from your wishlist.';
            } else {
                $_SESSION['guest_wishlist'][] = $productId;
                $inWishlist = true;
                $msg = 'Product saved to your wishlist.';
            }
            $count = count($_SESSION['guest_wishlist']);
        } else {
            $userId = (int)$user['id'];
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $exists = $stmt->fetch();

            if ($exists) {
                $del = $pdo->prepare("DELETE FROM wishlists WHERE id = ?");
                $del->execute([$exists['id']]);
                $inWishlist = false;
                $msg = 'Product removed from your wishlist.';
            } else {
                $ins = $pdo->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
                $ins->execute([$userId, $productId]);
                $inWishlist = true;
                $msg = 'Product saved to your wishlist.';
            }
            $count = get_wishlist_count();
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))) {
            json_response([
                'success' => true,
                'added' => $inWishlist,
                'in_wishlist' => $inWishlist,
                'message' => $msg,
                'wishlist_count' => $count
            ]);
        }

        set_flash('info', $msg);
        redirect($_SERVER['HTTP_REFERER'] ?? '/account/wishlist');
    }

    public function addresses(): void {
        $userId = auth_user()['id'];
        $userModel = new User();
        $addresses = $userModel->getAddresses($userId);

        view('account.addresses', [
            'pageTitle' => 'Saved Addresses - AFOREHEALTH',
            'addresses' => $addresses
        ]);
    }

    public function saveAddress(): void {
        $userId = auth_user()['id'];
        $fullName = trim($_POST['full_name'] ?? '');
        $mobile = preg_replace('/[^0-9]/', '', $_POST['mobile'] ?? '');
        $house = trim($_POST['house_flat'] ?? '');
        $area = trim($_POST['area'] ?? '');
        $landmark = trim($_POST['landmark'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');
        $type = $_POST['address_type'] ?? 'home';

        if (empty($fullName) || strlen($mobile) !== 10 || empty($house) || empty($city) || empty($state) || strlen($pincode) !== 6) {
            set_flash('danger', 'Please provide a valid Indian shipping address with a 10-digit mobile and 6-digit PIN code.');
            redirect('/account/addresses');
        }

        $userModel = new User();
        $userModel->addAddress($userId, [
            'full_name' => $fullName,
            'mobile' => $mobile,
            'house_flat' => $house,
            'area' => $area,
            'landmark' => $landmark,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode,
            'address_type' => $type,
            'is_default' => !empty($_POST['is_default']) ? 1 : 0
        ]);

        set_flash('success', 'Address saved successfully.');
        redirect('/account/addresses');
    }

    public function deleteAddress(): void {
        $userId = auth_user()['id'];
        $addressId = (int)($_POST['address_id'] ?? 0);

        if ($addressId > 0) {
            $userModel = new User();
            $userModel->deleteAddress($addressId, $userId);
            set_flash('success', 'Address deleted successfully.');
        }

        redirect('/account/addresses');
    }

    public function profile(): void {
        $userId = auth_user()['id'];
        $userModel = new User();
        $user = $userModel->find($userId);

        view('account.profile', [
            'pageTitle' => 'My Profile & Settings - AFOREHEALTH',
            'user' => $user
        ]);
    }

    public function updateProfile(): void {
        $userId = auth_user()['id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $dob = $_POST['dob'] ?? null;
        $gender = $_POST['gender'] ?? 'female';

        if (empty($name)) {
            set_flash('danger', 'Name is required.');
            redirect('/account/profile');
        }

        $userModel = new User();
        $userModel->update($userId, [
            'name' => $name,
            'email' => !empty($email) ? $email : null,
            'dob' => !empty($dob) ? $dob : null,
            'gender' => $gender
        ]);

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;

        set_flash('success', 'Profile updated successfully.');
        redirect('/account/profile');
    }

    public function changePassword(): void {
        $userId = auth_user()['id'];
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        $userModel = new User();
        $user = $userModel->find($userId);

        if (!password_verify($currentPass, $user['password'])) {
            set_flash('danger', 'Current password is incorrect.');
            redirect('/account/profile');
        }

        if (strlen($newPass) < 6) {
            set_flash('danger', 'New password must be at least 6 characters.');
            redirect('/account/profile');
        }

        if ($newPass !== $confirmPass) {
            set_flash('danger', 'Confirm password does not match.');
            redirect('/account/profile');
        }

        $userModel->update($userId, [
            'password' => password_hash($newPass, PASSWORD_DEFAULT)
        ]);

        set_flash('success', 'Password changed successfully.');
        redirect('/account/profile');
    }

    public function changeMobile(): void {
        $userId = auth_user()['id'];
        $userModel = new User();
        $user = $userModel->find($userId);

        view('account.change-mobile', [
            'pageTitle' => 'Update Mobile Number - AFOREHEALTH',
            'user' => $user
        ]);
    }

    public function reviews(): void {
        $userId = auth_user()['id'];
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("
            SELECT r.*, p.name as product_name, p.slug as product_slug, p.primary_image
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$userId]);
        $reviews = $stmt->fetchAll();

        view('account.reviews', [
            'pageTitle' => 'My Reviews - AFOREHEALTH',
            'reviews' => $reviews
        ]);
    }
}
