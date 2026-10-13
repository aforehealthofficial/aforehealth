<?php
namespace Models;

use PDO;

class Cart extends BaseModel {
    protected string $table = 'carts';

    public function getActiveCartId(): int {
        $userId = auth_user()['id'] ?? null;
        $sessionId = session_id();

        if ($userId) {
            $stmt = $this->db->prepare("SELECT id FROM carts WHERE user_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$userId]);
            $cartId = $stmt->fetchColumn();
            if ($cartId) return (int)$cartId;

            // Check if there was a session cart to associate
            $stmt = $this->db->prepare("SELECT id FROM carts WHERE session_id = ? AND user_id IS NULL ORDER BY id DESC LIMIT 1");
            $stmt->execute([$sessionId]);
            $cartId = $stmt->fetchColumn();
            if ($cartId) {
                $this->db->prepare("UPDATE carts SET user_id = ? WHERE id = ?")->execute([$userId, $cartId]);
                return (int)$cartId;
            }

            return $this->create(['user_id' => $userId, 'session_id' => $sessionId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM carts WHERE session_id = ? AND user_id IS NULL ORDER BY id DESC LIMIT 1");
            $stmt->execute([$sessionId]);
            $cartId = $stmt->fetchColumn();
            if ($cartId) return (int)$cartId;

            return $this->create(['user_id' => null, 'session_id' => $sessionId]);
        }
    }

    public function getCartDetails(): array {
        $cartId = $this->getActiveCartId();

        $stmt = $this->db->prepare("
            SELECT ci.id as item_id, ci.quantity, ci.variant_id,
                   p.id as product_id, p.name, p.slug, p.sku, p.price as base_price, p.mrp as base_mrp,
                   p.primary_image, p.stock as product_stock,
                   pv.color, pv.size, pv.sku as variant_sku, pv.stock as variant_stock,
                   pv.price as variant_price, pv.mrp as variant_mrp
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_variants pv ON ci.variant_id = pv.id
            WHERE ci.cart_id = ?
            ORDER BY ci.id DESC
        ");
        $stmt->execute([$cartId]);
        $rawItems = $stmt->fetchAll();

        $items = [];
        $subtotal = 0;
        $mrpTotal = 0;
        $itemCount = 0;

        foreach ($rawItems as $item) {
            $price = !empty($item['variant_price']) ? (float)$item['variant_price'] : (float)$item['base_price'];
            $mrp = !empty($item['variant_mrp']) ? (float)$item['variant_mrp'] : (float)$item['base_mrp'];
            $maxStock = !empty($item['variant_id']) ? (int)$item['variant_stock'] : (int)$item['product_stock'];

            $lineTotal = $price * (int)$item['quantity'];
            $lineMrpTotal = $mrp * (int)$item['quantity'];

            $subtotal += $lineTotal;
            $mrpTotal += $lineMrpTotal;
            $itemCount += (int)$item['quantity'];

            $items[] = [
                'item_id' => (int)$item['item_id'],
                'product_id' => (int)$item['product_id'],
                'variant_id' => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                'name' => $item['name'],
                'slug' => $item['slug'],
                'sku' => $item['variant_sku'] ?: $item['sku'],
                'color' => $item['color'],
                'size' => $item['size'],
                'image' => $item['primary_image'],
                'price' => $price,
                'mrp' => $mrp,
                'quantity' => (int)$item['quantity'],
                'max_stock' => $maxStock,
                'line_total' => $lineTotal,
                'line_mrp_total' => $lineMrpTotal
            ];
        }

        // Coupon calculation from session
        $couponData = $_SESSION['applied_coupon'] ?? null;
        $couponDiscount = 0;
        $couponCode = null;

        if ($couponData && $subtotal >= (float)($couponData['min_order_amount'] ?? 0)) {
            $couponCode = $couponData['code'];
            if ($couponData['discount_type'] === 'percentage') {
                $couponDiscount = round(($subtotal * (float)$couponData['discount_value']) / 100, 2);
                if (!empty($couponData['max_discount_amount']) && $couponDiscount > (float)$couponData['max_discount_amount']) {
                    $couponDiscount = (float)$couponData['max_discount_amount'];
                }
            } else {
                $couponDiscount = min((float)$couponData['discount_value'], $subtotal);
            }
        } else if ($couponData) {
            // Invalidate coupon if below min order
            unset($_SESSION['applied_coupon']);
        }

        // Bundle calculation from session
        $bundleData = $_SESSION['applied_bundle'] ?? null;
        $bundleDiscount = 0;
        $bundleName = null;
        if ($bundleData && !empty($bundleData['variant_ids'])) {
            // Verify all bundle variants are present in the cart
            $cartVariantIds = array_filter(array_map(fn($it) => (int)($it['variant_id'] ?? 0), $items));
            $allPresent = true;
            foreach ($bundleData['variant_ids'] as $vId) {
                if (!in_array((int)$vId, $cartVariantIds)) {
                    $allPresent = false;
                    break;
                }
            }
            if ($allPresent) {
                $bundleDiscount = (float)($bundleData['discount_amount'] ?? 0);
                $bundleName = $bundleData['bundle_name'] ?? 'Bundle Discount';
            } else {
                unset($_SESSION['applied_bundle']);
            }
        }

        // Shipping calculation
        $freeShippingThreshold = (float)get_setting('free_shipping_threshold', 499);
        $flatShippingFee = (float)get_setting('flat_shipping_fee', 50);

        $shippingFee = ($subtotal >= $freeShippingThreshold || $subtotal == 0) ? 0 : $flatShippingFee;
        $neededForFreeShipping = max(0, $freeShippingThreshold - $subtotal);
        $freeShippingPercent = min(100, round(($subtotal / $freeShippingThreshold) * 100));

        $grandTotal = max(0, $subtotal - $couponDiscount - $bundleDiscount + $shippingFee);
        $totalSavings = ($mrpTotal - $subtotal) + $couponDiscount + $bundleDiscount;

        return [
            'cart_id' => $cartId,
            'items' => $items,
            'item_count' => $itemCount,
            'subtotal' => $subtotal,
            'mrp_total' => $mrpTotal,
            'coupon_code' => $couponCode,
            'coupon_discount' => $couponDiscount,
            'bundle_name' => $bundleName,
            'bundle_discount' => $bundleDiscount,
            'shipping_fee' => $shippingFee,
            'free_shipping_threshold' => $freeShippingThreshold,
            'needed_for_free_shipping' => $neededForFreeShipping,
            'free_shipping_percent' => $freeShippingPercent,
            'grand_total' => $grandTotal,
            'total_savings' => $totalSavings
        ];
    }

    public function addItem(int $productId, ?int $variantId, int $quantity = 1): array {
        $cartId = $this->getActiveCartId();

        // Check if item already exists in cart
        if ($variantId) {
            $stmt = $this->db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND variant_id = ? LIMIT 1");
            $stmt->execute([$cartId, $productId, $variantId]);
        } else {
            $stmt = $this->db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? AND variant_id IS NULL LIMIT 1");
            $stmt->execute([$cartId, $productId]);
        }

        $existing = $stmt->fetch();

        // Validate stock
        if ($variantId) {
            $stockStmt = $this->db->prepare("SELECT stock FROM product_variants WHERE id = ?");
            $stockStmt->execute([$variantId]);
            $availableStock = (int)$stockStmt->fetchColumn();
        } else {
            $stockStmt = $this->db->prepare("SELECT stock FROM products WHERE id = ?");
            $stockStmt->execute([$productId]);
            $availableStock = (int)$stockStmt->fetchColumn();
        }

        if ($availableStock <= 0) {
            return ['success' => false, 'message' => 'Sorry, this item is currently out of stock.'];
        }

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            if ($newQty > $availableStock) {
                $newQty = $availableStock;
            }
            $updateStmt = $this->db->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$newQty, $existing['id']]);
        } else {
            $qtyToAdd = min($quantity, $availableStock);
            $insertStmt = $this->db->prepare("INSERT INTO cart_items (cart_id, product_id, variant_id, quantity) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$cartId, $productId, $variantId, $qtyToAdd]);
        }

        return ['success' => true, 'message' => 'Item added to your bag.'];
    }

    public function updateQuantity(int $itemId, int $quantity): array {
        $cartId = $this->getActiveCartId();

        if ($quantity <= 0) {
            return $this->removeItem($itemId);
        }

        // Verify ownership and stock
        $stmt = $this->db->prepare("
            SELECT ci.id, ci.product_id, ci.variant_id,
                   p.stock as product_stock, pv.stock as variant_stock
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            LEFT JOIN product_variants pv ON ci.variant_id = pv.id
            WHERE ci.id = ? AND ci.cart_id = ?
        ");
        $stmt->execute([$itemId, $cartId]);
        $item = $stmt->fetch();

        if (!$item) {
            return ['success' => false, 'message' => 'Cart item not found.'];
        }

        $maxStock = !empty($item['variant_id']) ? (int)$item['variant_stock'] : (int)$item['product_stock'];
        $finalQty = min($quantity, $maxStock);

        $update = $this->db->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$finalQty, $itemId]);

        return ['success' => true, 'message' => 'Quantity updated.'];
    }

    public function removeItem(int $itemId): array {
        $cartId = $this->getActiveCartId();
        $stmt = $this->db->prepare("DELETE FROM cart_items WHERE id = ? AND cart_id = ?");
        $stmt->execute([$itemId, $cartId]);
        return ['success' => true, 'message' => 'Item removed from bag.'];
    }

    public function clearCart(): void {
        $cartId = $this->getActiveCartId();
        $this->db->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cartId]);
        unset($_SESSION['applied_coupon']);
        unset($_SESSION['applied_bundle']);
    }
}
