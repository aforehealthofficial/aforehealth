<?php
namespace Models;

use PDO;
use Exception;

class Order extends BaseModel {
    protected string $table = 'orders';

    public function findByOrderNumber(string $orderNumber): ?array {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();
        if (!$order) return null;

        $order['items'] = $this->getOrderItems($order['id']);
        $order['history'] = $this->getStatusHistory($order['id']);
        $order['shipping_address'] = json_decode($order['shipping_address_json'], true);
        $order['billing_address'] = !empty($order['billing_address_json']) ? json_decode($order['billing_address_json'], true) : $order['shipping_address'];
        return $order;
    }

    public function getOrderItems(int $orderId): array {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getStatusHistory(int $orderId): array {
        $stmt = $this->db->prepare("SELECT * FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getUserOrders(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count,
                   (SELECT product_name FROM order_items WHERE order_id = o.id LIMIT 1) as first_item_name
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Create order from active cart with pessimistic inventory locking & transaction protection
     */
    public function createFromCart(array $checkoutData): array {
        $cartModel = new Cart();
        $cart = $cartModel->getCartDetails();

        if (empty($cart['items'])) {
            throw new Exception('Your cart is empty.');
        }

        $userId = auth_user()['id'] ?? null;
        $orderNumber = generate_order_number();

        $subtotal = (float)$cart['subtotal'];
        $couponDiscount = (float)($cart['coupon_discount'] ?? 0);
        $bundleDiscount = (float)($cart['bundle_discount'] ?? 0);
        $totalDiscount = $couponDiscount + $bundleDiscount;
        $couponCode = $cart['coupon_code'];
        $shippingFee = (float)$cart['shipping_fee'];

        $paymentMethod = $checkoutData['payment_method'] ?? 'razorpay';
        $codFee = ($paymentMethod === 'cod') ? (float)get_setting('cod_fee', 40) : 0.00;
        $grandTotal = max(0, $subtotal - $totalDiscount + $shippingFee + $codFee);

        // COD orders are confirmed right away; online payments start as PENDING awaiting gateway verification
        $initialOrderStatus = ($paymentMethod === 'cod') ? 'CONFIRMED' : 'PENDING';
        $initialPaymentStatus = 'pending';

        $this->db->beginTransaction();

        try {
            // 1. Insert order record
            $sql = "
                INSERT INTO orders (
                    order_number, user_id, customer_name, customer_mobile, customer_email,
                    subtotal, discount, coupon_code, coupon_discount, shipping_fee, cod_fee, grand_total,
                    payment_method, payment_status, order_status, shipping_address_json, notes
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?
                )
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $orderNumber,
                $userId,
                $checkoutData['full_name'],
                $checkoutData['mobile'],
                $checkoutData['email'] ?? null,
                $subtotal,
                $totalDiscount,
                $couponCode,
                $couponDiscount,
                $shippingFee,
                $codFee,
                $grandTotal,
                $paymentMethod,
                $initialPaymentStatus,
                $initialOrderStatus,
                json_encode($checkoutData['address']),
                $checkoutData['notes'] ?? null
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // 2. Insert order items & atomically reduce stock with pessimistic locking
            $stmtItem = $this->db->prepare("
                INSERT INTO order_items (
                    order_id, product_id, variant_id, product_name, variant_details, sku, price, mrp, quantity, total
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtInvLog = $this->db->prepare("
                INSERT INTO inventory_logs (
                    product_id, product_variant_id, sku, previous_stock, change_amount, new_stock, reason
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($cart['items'] as $item) {
                $qty = (int)$item['quantity'];
                $variantDetails = trim(($item['color'] ? "Color: {$item['color']} " : "") . ($item['size'] ? "| Size: {$item['size']}" : ""));
                
                $stmtItem->execute([
                    $orderId,
                    $item['product_id'],
                    $item['variant_id'],
                    $item['name'],
                    $variantDetails,
                    $item['sku'],
                    $item['price'],
                    $item['mrp'],
                    $qty,
                    $item['line_total']
                ]);

                // Concurrency control: Lock inventory row & check available stock
                if ($item['variant_id']) {
                    $stockStmt = $this->db->prepare("SELECT stock FROM product_variants WHERE id = ? FOR UPDATE");
                    $stockStmt->execute([$item['variant_id']]);
                    $curStock = (int)$stockStmt->fetchColumn();

                    if ($curStock < $qty) {
                        throw new Exception("Insufficient stock for '{$item['name']}' ({$item['size']}). Only {$curStock} left.");
                    }

                    $newStock = $curStock - $qty;
                    $upd = $this->db->prepare("UPDATE product_variants SET stock = stock - ? WHERE id = ? AND stock >= ?");
                    $upd->execute([$qty, $item['variant_id'], $qty]);

                    if ($upd->rowCount() === 0) {
                        throw new Exception("Inventory concurrency conflict for '{$item['name']}'. Please retry.");
                    }

                    $stmtInvLog->execute([
                        $item['product_id'], $item['variant_id'], $item['sku'], $curStock, -$qty, $newStock, "Order #{$orderNumber}"
                    ]);
                } else {
                    $stockStmt = $this->db->prepare("SELECT stock FROM products WHERE id = ? FOR UPDATE");
                    $stockStmt->execute([$item['product_id']]);
                    $curStock = (int)$stockStmt->fetchColumn();

                    if ($curStock < $qty) {
                        throw new Exception("Insufficient stock for '{$item['name']}'. Only {$curStock} left.");
                    }

                    $newStock = $curStock - $qty;
                    $upd = $this->db->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                    $upd->execute([$qty, $item['product_id'], $qty]);

                    if ($upd->rowCount() === 0) {
                        throw new Exception("Inventory concurrency conflict for '{$item['name']}'. Please retry.");
                    }

                    $stmtInvLog->execute([
                        $item['product_id'], null, $item['sku'], $curStock, -$qty, $newStock, "Order #{$orderNumber}"
                    ]);
                }
            }

            // 3. Status history
            $stmtHist = $this->db->prepare("INSERT INTO order_status_history (order_id, status, notes, notify_customer, created_by) VALUES (?, ?, ?, 1, 'System')");
            $histNotes = ($paymentMethod === 'cod') ? 'Order confirmed via Cash on Delivery' : 'Order placed, awaiting online payment';
            $stmtHist->execute([$orderId, $initialOrderStatus, $histNotes]);

            // 4. Coupon usage recording
            if (!empty($couponCode)) {
                $couponStmt = $this->db->prepare("SELECT id FROM coupons WHERE code = ?");
                $couponStmt->execute([$couponCode]);
                $cId = $couponStmt->fetchColumn();
                if ($cId) {
                    $this->db->prepare("INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_applied) VALUES (?, ?, ?, ?)")
                        ->execute([$cId, $userId, $orderId, $couponDiscount]);
                    $this->db->prepare("UPDATE coupons SET usage_count = usage_count + 1 WHERE id = ?")->execute([$cId]);
                }
            }

            // For COD orders, clear cart & grant rewards immediately
            if ($paymentMethod === 'cod') {
                $cartModel->clearCart();

                // Loyalty Points Earning
                if ($userId && (bool)get_setting('loyalty_enabled', 1)) {
                    $pointsRate = (int)get_setting('loyalty_points_per_100_inr', 5);
                    $earnedPoints = (int)floor(($grandTotal / 100) * $pointsRate);
                    if ($earnedPoints > 0) {
                        $loyaltyModel = new Loyalty();
                        $loyaltyModel->addPoints($userId, $earnedPoints, 'earned', "Points earned for Order #{$orderNumber}", $orderId);
                    }
                }

                // Referral Reward Check (if first order)
                if ($userId) {
                    $referralModel = new Referral();
                    $referralModel->rewardOnFirstOrder($userId, $orderId, $grandTotal);
                }

                // Admin Notification
                try {
                    $this->db->prepare("
                        INSERT INTO notifications (user_id, title, message, type, link)
                        VALUES (NULL, 'New COD Order Placed', ?, 'order', ?)
                    ")->execute(["Order #{$orderNumber} (₹" . number_format($grandTotal, 2) . ") placed by " . $checkoutData['full_name'], "/admin/orders/{$orderNumber}"]);
                } catch (\Exception $e) {}
            }

            $this->db->commit();

            return [
                'order_id' => $orderId,
                'order_number' => $orderNumber,
                'grand_total' => $grandTotal,
                'payment_method' => $paymentMethod,
                'initial_status' => $initialOrderStatus
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Complete online payment verification and confirm order
     */
    public function confirmOnlinePayment(string $orderNumber, array $paymentData): bool {
        $order = $this->findByOrderNumber($orderNumber);
        if (!$order) return false;

        // Idempotency: if already paid, return true without double rewarding
        if ($order['payment_status'] === 'paid') {
            return true;
        }

        $this->db->beginTransaction();
        try {
            // Update order status
            $this->db->prepare("
                UPDATE orders 
                SET payment_status = 'paid', order_status = 'CONFIRMED', updated_at = NOW() 
                WHERE id = ?
            ")->execute([$order['id']]);

            // Record in payments table (idempotent / record unique payment)
            $stmtPay = $this->db->prepare("
                INSERT INTO payments (
                    order_id, payment_method, transaction_id, razorpay_order_id, razorpay_payment_id,
                    razorpay_signature, amount, currency, status, raw_response
                ) VALUES (?, 'razorpay', ?, ?, ?, ?, ?, 'INR', 'success', ?)
            ");
            $stmtPay->execute([
                $order['id'],
                $paymentData['razorpay_payment_id'],
                $paymentData['razorpay_order_id'] ?? null,
                $paymentData['razorpay_payment_id'],
                $paymentData['razorpay_signature'] ?? null,
                $order['grand_total'],
                json_encode($paymentData)
            ]);

            // Status history
            $stmtHist = $this->db->prepare("INSERT INTO order_status_history (order_id, status, notes, notify_customer, created_by) VALUES (?, 'CONFIRMED', 'Online payment verified successfully via Razorpay', 1, 'Payment Gateway')");
            $stmtHist->execute([$order['id']]);

            // Clear Cart
            $cartModel = new Cart();
            $cartModel->clearCart();

            // Loyalty points
            if ($order['user_id'] && (bool)get_setting('loyalty_enabled', 1)) {
                $pointsRate = (int)get_setting('loyalty_points_per_100_inr', 5);
                $earnedPoints = (int)floor(($order['grand_total'] / 100) * $pointsRate);
                if ($earnedPoints > 0) {
                    $loyaltyModel = new Loyalty();
                    $loyaltyModel->addPoints($order['user_id'], $earnedPoints, 'earned', "Points earned for Order #{$orderNumber}", $order['id']);
                }
            }

            // Referral reward
            if ($order['user_id']) {
                $referralModel = new Referral();
                $referralModel->rewardOnFirstOrder($order['user_id'], $order['id'], $order['grand_total']);
            }

            // Notification
            try {
                $this->db->prepare("
                    INSERT INTO notifications (user_id, title, message, type, link)
                    VALUES (NULL, 'Online Payment Confirmed', ?, 'order', ?)
                ")->execute(["Order #{$orderNumber} paid ₹" . number_format($order['grand_total'], 2), "/admin/orders/{$orderNumber}"]);
            } catch (\Exception $e) {}

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Handle failed or cancelled payment: restores reserved stock
     */
    public function failOnlinePayment(string $orderNumber, string $reason = 'Payment failed or cancelled'): bool {
        $order = $this->findByOrderNumber($orderNumber);
        if (!$order || $order['payment_status'] === 'paid') return false;

        $this->db->beginTransaction();
        try {
            $this->db->prepare("
                UPDATE orders 
                SET payment_status = 'failed', order_status = 'FAILED', notes = CONCAT(IFNULL(notes,''), ' | ', ?) 
                WHERE id = ?
            ")->execute([$reason, $order['id']]);

            // Restore reserved stock
            $items = $this->getOrderItems($order['id']);
            $stmtInvLog = $this->db->prepare("
                INSERT INTO inventory_logs (
                    product_id, product_variant_id, sku, previous_stock, change_amount, new_stock, reason
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                $qty = (int)$item['quantity'];
                if (!empty($item['variant_id'])) {
                    $cur = (int)$this->db->query("SELECT stock FROM product_variants WHERE id = {$item['variant_id']}")->fetchColumn();
                    $newStock = $cur + $qty;
                    $this->db->prepare("UPDATE product_variants SET stock = ? WHERE id = ?")->execute([$newStock, $item['variant_id']]);
                    $stmtInvLog->execute([$item['product_id'], $item['variant_id'], $item['sku'], $cur, $qty, $newStock, "Payment Failed Restore #{$orderNumber}"]);
                } else {
                    $cur = (int)$this->db->query("SELECT stock FROM products WHERE id = {$item['product_id']}")->fetchColumn();
                    $newStock = $cur + $qty;
                    $this->db->prepare("UPDATE products SET stock = ? WHERE id = ?")->execute([$newStock, $item['product_id']]);
                    $stmtInvLog->execute([$item['product_id'], null, $item['sku'], $cur, $qty, $newStock, "Payment Failed Restore #{$orderNumber}"]);
                }
            }

            // Status history
            $stmtHist = $this->db->prepare("INSERT INTO order_status_history (order_id, status, notes, notify_customer, created_by) VALUES (?, 'FAILED', ?, 0, 'System')");
            $stmtHist->execute([$order['id'], $reason]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $orderId, string $status, ?string $notes = null, ?string $courier = null, ?string $tracking = null): bool {
        $updateFields = ["order_status = ?"];
        $params = [$status];

        if ($courier !== null) {
            $updateFields[] = "courier_name = ?";
            $params[] = $courier;
        }
        if ($tracking !== null) {
            $updateFields[] = "tracking_number = ?";
            $params[] = $tracking;
        }

        $params[] = $orderId;
        $sql = "UPDATE orders SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute($params);

        // Record history
        $adminName = auth_admin()['name'] ?? 'Admin';
        $historyStmt = $this->db->prepare("INSERT INTO order_status_history (order_id, status, notes, notify_customer, created_by) VALUES (?, ?, ?, 1, ?)");
        $historyStmt->execute([$orderId, $status, $notes ?: "Order status changed to {$status}", $adminName]);

        return $res;
    }

    /**
     * Buy Again: Reorder products from a past order
     * Validates that products & variants still exist, are active/published,
     * and uses the CURRENT price, never historical price.
     */
    public function reorder(int $orderId): array {
        $order = $this->find($orderId);
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found.'];
        }

        $items = $this->getOrderItems($orderId);
        if (empty($items)) {
            return ['success' => false, 'message' => 'No items found in this order.'];
        }

        $cartModel = new Cart();
        $addedCount = 0;
        $failedMessages = [];

        foreach ($items as $item) {
            $productId = (int)$item['product_id'];
            $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;
            $qty = (int)$item['quantity'];

            // Check product status
            $prod = $this->db->query("SELECT id, name, status, price, stock FROM products WHERE id = {$productId}")->fetch();
            if (!$prod || $prod['status'] !== 'published') {
                $failedMessages[] = "Product '{$item['product_name']}' is no longer available.";
                continue;
            }

            // Check variant and stock
            if ($variantId) {
                $variant = $this->db->query("SELECT id, stock, status FROM product_variants WHERE id = {$variantId}")->fetch();
                if (!$variant || $variant['status'] !== 'active') {
                    $failedMessages[] = "Selected variant of '{$item['product_name']}' is discontinued.";
                    continue;
                }
                if ($variant['stock'] <= 0) {
                    $failedMessages[] = "Variant of '{$item['product_name']}' is currently out of stock.";
                    continue;
                }
                $qtyToAdd = min($qty, (int)$variant['stock']);
            } else {
                if ($prod['stock'] <= 0) {
                    $failedMessages[] = "'{$item['product_name']}' is currently out of stock.";
                    continue;
                }
                $qtyToAdd = min($qty, (int)$prod['stock']);
            }

            // Add to cart at CURRENT price
            try {
                $cartModel->addItem($productId, $variantId, $qtyToAdd);
                $addedCount++;
            } catch (Exception $e) {
                $failedMessages[] = $e->getMessage();
            }
        }

        if ($addedCount === 0) {
            return [
                'success' => false,
                'message' => !empty($failedMessages) ? implode(' ', $failedMessages) : 'Sorry, items from this order are currently unavailable.'
            ];
        }

        $msg = "{$addedCount} item(s) re-added to your bag at current prices.";
        if (!empty($failedMessages)) {
            $msg .= " Note: " . implode(' ', $failedMessages);
        }

        return [
            'success' => true,
            'added_count' => $addedCount,
            'message' => $msg
        ];
    }

    /**
     * Request a product return with eligibility and window validation
     */
    public function requestReturn(int $orderId, int $userId, int $productId, string $reason, string $description, ?array $images = null): array {
        $order = $this->find($orderId);
        if (!$order || (int)$order['user_id'] !== $userId) {
            return ['success' => false, 'message' => 'Order not found or unauthorized.'];
        }

        // Only delivered orders can be returned
        if ($order['order_status'] !== 'DELIVERED') {
            return ['success' => false, 'message' => 'Returns can only be requested for delivered orders.'];
        }

        // Check 7-day return window from last update / delivery
        $deliveredTime = strtotime($order['updated_at'] ?: $order['created_at']);
        $daysSinceDelivery = (time() - $deliveredTime) / 86400;
        if ($daysSinceDelivery > 7) {
            return ['success' => false, 'message' => 'The 7-day return window for this order has expired.'];
        }

        // Check if return already requested for this item
        $stmtChk = $this->db->prepare("SELECT id, status FROM returns WHERE order_id = ? AND product_id = ? AND user_id = ?");
        $stmtChk->execute([$orderId, $productId, $userId]);
        $existing = $stmtChk->fetch();
        if ($existing) {
            return ['success' => false, 'message' => "A return request is already {$existing['status']} for this item."];
        }

        // Calculate item refund amount strictly server-side
        $stmtItem = $this->db->prepare("SELECT total, quantity FROM order_items WHERE order_id = ? AND product_id = ? LIMIT 1");
        $stmtItem->execute([$orderId, $productId]);
        $item = $stmtItem->fetch();
        if (!$item) {
            return ['success' => false, 'message' => 'Product was not found in this order.'];
        }

        $refundAmount = (float)$item['total'];
        $returnNumber = 'RET-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $stmtIns = $this->db->prepare("
            INSERT INTO returns (
                return_number, order_id, user_id, product_id, reason, description, images_json, status, refund_amount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
        ");
        $stmtIns->execute([
            $returnNumber,
            $orderId,
            $userId,
            $productId,
            trim($reason),
            trim($description),
            $images ? json_encode($images) : null,
            $refundAmount
        ]);

        $returnId = (int)$this->db->lastInsertId();

        // Update order status
        $this->updateStatus($orderId, 'RETURN_REQUESTED', "Return requested for Product #{$productId}: {$reason}");

        return [
            'success' => true,
            'message' => 'Return request submitted successfully. Our team will review within 24-48 business hours.',
            'return_id' => $returnId,
            'return_number' => $returnNumber,
            'refund_amount' => $refundAmount
        ];
    }

    /**
     * Admin: Update return request status (approved, rejected)
     */
    public function updateReturnStatus(int $returnId, string $status, ?string $adminNotes = null): array {
        if (!in_array($status, ['approved', 'rejected', 'returned', 'refunded'])) {
            return ['success' => false, 'message' => 'Invalid return status.'];
        }

        $stmt = $this->db->prepare("SELECT * FROM returns WHERE id = ?");
        $stmt->execute([$returnId]);
        $ret = $stmt->fetch();
        if (!$ret) {
            return ['success' => false, 'message' => 'Return request not found.'];
        }

        $upd = $this->db->prepare("UPDATE returns SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?");
        $upd->execute([$status, $adminNotes, $returnId]);

        $orderStatus = ($status === 'approved') ? 'RETURN_APPROVED' : (($status === 'rejected') ? 'CONFIRMED' : 'RETURNED');
        $this->updateStatus((int)$ret['order_id'], $orderStatus, "Return #{$ret['return_number']} {$status}. " . ($adminNotes ? "Note: {$adminNotes}" : ""));

        return ['success' => true, 'message' => "Return request has been {$status}."];
    }

    /**
     * Process Refund: restores inventory and reverses loyalty points
     */
    public function processRefund(int $returnId, bool $restoreStock = true): array {
        $stmt = $this->db->prepare("SELECT * FROM returns WHERE id = ?");
        $stmt->execute([$returnId]);
        $ret = $stmt->fetch();
        if (!$ret) {
            return ['success' => false, 'message' => 'Return request not found.'];
        }

        // Duplicate refund protection
        if ($ret['status'] === 'refunded') {
            return ['success' => false, 'message' => 'This return request has already been refunded.'];
        }

        $this->db->beginTransaction();
        try {
            $refundAmount = (float)$ret['refund_amount'];
            $orderId = (int)$ret['order_id'];
            $productId = (int)$ret['product_id'];
            $userId = (int)$ret['user_id'];

            // 1. Mark return as refunded
            $this->db->prepare("UPDATE returns SET status = 'refunded', updated_at = NOW() WHERE id = ?")->execute([$returnId]);

            // 2. Mark order as REFUNDED
            $this->db->prepare("UPDATE orders SET payment_status = 'refunded', order_status = 'REFUNDED', updated_at = NOW() WHERE id = ?")->execute([$orderId]);

            // 3. Inventory restoration if restockable
            if ($restoreStock) {
                // Find order item quantity & variant
                $stmtItem = $this->db->prepare("SELECT variant_id, quantity, sku FROM order_items WHERE order_id = ? AND product_id = ? LIMIT 1");
                $stmtItem->execute([$orderId, $productId]);
                $item = $stmtItem->fetch();

                if ($item) {
                    $qty = (int)$item['quantity'];
                    $variantId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;

                    if ($variantId) {
                        $cur = (int)$this->db->query("SELECT stock FROM product_variants WHERE id = {$variantId}")->fetchColumn();
                        $newStock = $cur + $qty;
                        $this->db->prepare("UPDATE product_variants SET stock = ? WHERE id = ?")->execute([$newStock, $variantId]);
                        $this->db->prepare("INSERT INTO inventory_logs (product_id, product_variant_id, sku, previous_stock, change_amount, new_stock, reason) VALUES (?, ?, ?, ?, ?, ?, ?)")
                            ->execute([$productId, $variantId, $item['sku'], $cur, $qty, $newStock, "Return #{$ret['return_number']} Restocked"]);
                    } else {
                        $cur = (int)$this->db->query("SELECT stock FROM products WHERE id = {$productId}")->fetchColumn();
                        $newStock = $cur + $qty;
                        $this->db->prepare("UPDATE products SET stock = ? WHERE id = ?")->execute([$newStock, $productId]);
                        $this->db->prepare("INSERT INTO inventory_logs (product_id, product_variant_id, sku, previous_stock, change_amount, new_stock, reason) VALUES (?, ?, ?, ?, ?, ?, ?)")
                            ->execute([$productId, null, $item['sku'], $cur, $qty, $newStock, "Return #{$ret['return_number']} Restocked"]);
                    }
                }
            }

            // 4. Reverse Loyalty Points
            if ($userId && (bool)get_setting('loyalty_enabled', 1)) {
                $pointsRate = (int)get_setting('loyalty_points_per_100_inr', 5);
                $pointsToReverse = (int)floor(($refundAmount / 100) * $pointsRate);
                if ($pointsToReverse > 0) {
                    $loyaltyModel = new Loyalty();
                    $loyaltyModel->addPoints($userId, -$pointsToReverse, 'reversed', "Points reversed for Refund on Return #{$ret['return_number']}", $orderId);
                }
            }

            // 5. Status history
            $stmtHist = $this->db->prepare("INSERT INTO order_status_history (order_id, status, notes, notify_customer, created_by) VALUES (?, 'REFUNDED', ?, 1, 'Admin')");
            $stmtHist->execute([$orderId, "Refund of ₹" . number_format($refundAmount, 2) . " processed successfully for Return #{$ret['return_number']}"]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Refund processed successfully, inventory restocked, and loyalty points adjusted.',
                'refund_amount' => $refundAmount
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get unique products previously ordered by user that are currently active & in-stock
     */
    public function getBuyAgainProducts(int $userId, int $limit = 6): array {
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.*, c.name as category_name
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            WHERE o.user_id = ? AND p.status = 'published' AND p.stock > 0
            ORDER BY o.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
