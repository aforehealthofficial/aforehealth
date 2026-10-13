<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item active">Shopping Bag</li>
        </ol>
    </nav>

    <h1 class="h2 fw-bold text-dark mb-4">Your Shopping Bag (<?= $cart['item_count'] ?>)</h1>

    <?php if (empty($cart['items'])): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm p-5">
            <div class="display-1 text-muted mb-3">🛍️</div>
            <h3 class="fw-bold text-dark">Your Shopping Bag is Empty</h3>
            <p class="text-muted">Explore our leakproof period underwear and comfort essentials.</p>
            <a href="<?= url('shop') ?>" class="btn btn-afh-primary px-4 py-2 mt-2">Explore Catalog →</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            
            <!-- Items Table / List -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    
                    <!-- Free Shipping Meter -->
                    <div class="p-3 rounded-3 mb-4" style="background: #FFF9FA; border: 1px solid var(--afh-border);">
                        <div class="small fw-semibold text-center mb-1">
                            <?php if ($cart['needed_for_free_shipping'] > 0): ?>
                                Add <strong>₹<?= number_format($cart['needed_for_free_shipping'], 0) ?></strong> more to unlock <strong>FREE SHIPPING!</strong>
                            <?php else: ?>
                                <span class="text-success fw-bold">🎉 Congratulations! You have unlocked FREE Shipping!</span>
                            <?php endif; ?>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?= $cart['free_shipping_percent'] ?>%"></div>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($cart['items'] as $item): ?>
                            <div class="d-flex gap-3 pb-3 border-bottom align-items-center">
                                <a href="<?= url('product/' . $item['slug']) ?>" class="flex-shrink-0 rounded-3 overflow-hidden border" style="width: 80px; height: 80px;">
                                    <img src="<?= upload_url('products/' . $item['image']) ?>" alt="<?= e($item['name']) ?>" class="w-100 h-100 object-fit-cover">
                                </a>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1">
                                        <a href="<?= url('product/' . $item['slug']) ?>" class="text-dark"><?= e($item['name']) ?></a>
                                    </h6>
                                    <div class="small text-muted mb-2">
                                        <?= e(($item['color'] ? "Color: {$item['color']} " : "") . ($item['size'] ? "| Size: {$item['size']}" : "")) ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="qty-stepper">
                                            <button type="button" class="qty-btn" onclick="CartDrawer.updateQty(<?= $item['item_id'] ?>, <?= $item['quantity'] - 1 ?>); setTimeout(()=>location.reload(), 200);">-</button>
                                            <span class="qty-display"><?= $item['quantity'] ?></span>
                                            <button type="button" class="qty-btn" onclick="CartDrawer.updateQty(<?= $item['item_id'] ?>, <?= $item['quantity'] + 1 ?>); setTimeout(()=>location.reload(), 200);">+</button>
                                        </div>
                                        <button type="button" onclick="CartDrawer.removeItem(<?= $item['item_id'] ?>, () => location.reload());" class="btn btn-link text-muted p-0 small text-decoration-underline">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold fs-6 text-dark">₹<?= number_format($item['line_total'], 0) ?></div>
                                    <?php if ($item['line_mrp_total'] > $item['line_total']): ?>
                                        <small class="text-muted text-decoration-line-through">₹<?= number_format($item['line_mrp_total'], 0) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="<?= url('shop') ?>" class="small text-primary fw-bold">← Continue Shopping</a>
                    </div>
                </div>
            </div>

            <!-- Order Summary Column -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 120px;">
                    <h5 class="fw-bold mb-3">Order Summary</h5>

                    <!-- Coupon Code Box -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Have a Coupon Code?</label>
                        <?php if (!empty($cart['coupon_code'])): ?>
                            <div class="p-2 rounded bg-light border d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-success small"><i class="bi bi-tag-fill me-1"></i> <?= e($cart['coupon_code']) ?> (-₹<?= number_format($cart['coupon_discount'], 0) ?>)</span>
                                <button type="button" onclick="CartDrawer.removeCoupon(); setTimeout(()=>location.reload(), 200);" class="btn btn-sm btn-link text-danger p-0 text-decoration-none fw-bold">Remove</button>
                            </div>
                        <?php else: ?>
                            <div class="input-group">
                                <input type="text" id="cartPageCouponInput" class="form-control" placeholder="e.g. WELCOME10" style="text-transform: uppercase;">
                                <button class="btn btn-outline-secondary fw-bold" type="button" onclick="CartDrawer.applyCoupon(document.getElementById('cartPageCouponInput').value); setTimeout(()=>location.reload(), 300);">Apply</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold">₹<?= number_format($cart['subtotal'], 0) ?></span>
                    </div>

                    <?php if (!empty($cart['bundle_discount']) && $cart['bundle_discount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Bundle Savings (<?= e($cart['bundle_name'] ?? 'Kit Discount') ?>):</span>
                            <span class="fw-bold">-₹<?= number_format($cart['bundle_discount'], 0) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($cart['coupon_discount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Coupon Discount:</span>
                            <span class="fw-bold">-₹<?= number_format($cart['coupon_discount'], 0) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Shipping Fee:</span>
                        <span class="fw-bold"><?= $cart['shipping_fee'] > 0 ? '₹' . number_format($cart['shipping_fee'], 0) : '<span class="text-success">FREE</span>' ?></span>
                    </div>

                    <?php if ($cart['total_savings'] > 0): ?>
                        <div class="alert alert-success py-2 px-3 small my-2 rounded-3 text-center fw-semibold">
                            You are saving ₹<?= number_format($cart['total_savings'], 0) ?> on this order!
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between border-top pt-3 mb-4">
                        <span class="fs-5 fw-bold">Grand Total:</span>
                        <span class="fs-4 fw-extrabold text-primary">₹<?= number_format($cart['grand_total'], 0) ?></span>
                    </div>

                    <a href="<?= url('checkout') ?>" class="btn btn-afh-primary w-100 py-3 fw-bold text-center">
                        PROCEED TO CHECKOUT →
                    </a>

                    <div class="text-center mt-3">
                        <small class="text-muted"><i class="bi bi-shield-check text-success"></i> 256-Bit SSL Encrypted Checkout</small>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.trackAnalytics) {
        window.trackAnalytics('view_cart', {
            value: <?= (float)$cart['subtotal'] ?>,
            item_count: <?= (int)$cart['item_count'] ?>
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
