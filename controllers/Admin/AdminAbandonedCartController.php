<?php
namespace Controllers\Admin;

use PDO;

class AdminAbandonedCartController {
    public function index(): void {
        $pdo = getDbConnection();

        // 1. Fetch carts with items that haven't been completed/recovered
        $sql = "
            SELECT c.*, 
                   COALESCE(u.name, c.customer_name) as display_name,
                   COALESCE(u.mobile, c.customer_mobile) as display_mobile,
                   COALESCE(u.email, c.customer_email) as display_email,
                   COUNT(ci.id) as item_count,
                   SUM(ci.quantity * COALESCE(pv.price, p.price)) as cart_value,
                   GROUP_CONCAT(CONCAT(p.name, ' (x', ci.quantity, ')') SEPARATOR ', ') as items_summary,
                   c.updated_at
            FROM carts c
            JOIN cart_items ci ON c.id = ci.cart_id
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_variants pv ON ci.variant_id = pv.id
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.is_recovered = 0
            GROUP BY c.id
            HAVING item_count > 0
            ORDER BY c.updated_at DESC
        ";

        $carts = $pdo->query($sql)->fetchAll();

        // Calculate summary metrics
        $totalCarts = count($carts);
        $totalRevenue = 0;
        $cartsWithContact = 0;

        foreach ($carts as &$c) {
            $totalRevenue += (float)$c['cart_value'];
            if (!empty($c['display_mobile']) || !empty($c['display_email'])) {
                $cartsWithContact++;
            }

            // Ensure recovery token
            if (empty($c['recovery_token'])) {
                $token = bin2hex(random_bytes(16));
                $pdo->prepare("UPDATE carts SET recovery_token = ? WHERE id = ?")->execute([$token, $c['id']]);
                $c['recovery_token'] = $token;
            }

            // Generate WhatsApp Recovery URL
            if (!empty($c['display_mobile'])) {
                $cleanMob = preg_replace('/[^0-9]/', '', $c['display_mobile']);
                if (strlen($cleanMob) === 10) {
                    $cleanMob = '91' . $cleanMob;
                }
                $cName = explode(' ', trim($c['display_name'] ?? 'there'))[0];
                $recUrl = url('cart/recover?token=' . $c['recovery_token']);
                $msg = "Hi {$cName}! 🌸 We noticed you left some comfort essentials in your AFOREHEALTH shopping bag. Complete your order today and get an EXTRA 10% OFF with code *RECOVER10* + 100% Discreet Packaging: {$recUrl}";
                $c['whatsapp_url'] = "https://wa.me/{$cleanMob}?text=" . rawurlencode($msg);
            } else {
                $c['whatsapp_url'] = null;
            }

            $c['recovery_url'] = url('cart/recover?token=' . $c['recovery_token']);
        }
        unset($c);

        view('admin.abandoned-carts.index', [
            'pageTitle' => 'Abandoned Cart Recovery - AFOREHEALTH Admin',
            'carts' => $carts,
            'totalCarts' => $totalCarts,
            'totalRevenue' => $totalRevenue,
            'cartsWithContact' => $cartsWithContact
        ]);
    }

    public function delete(): void {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $pdo = getDbConnection();
            $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM carts WHERE id = ?")->execute([$id]);
            set_flash('success', 'Abandoned cart removed.');
        }
        redirect('/admin/abandoned-carts');
    }
}
