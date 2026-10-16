<!-- Slide-in Cart Drawer Backdrop -->
<div class="cart-drawer-backdrop"></div>

<!-- Slide-in Cart Drawer Panel -->
<div class="cart-drawer" id="cartDrawerPanel">
    
    <!-- Drawer Header -->
    <div class="cart-drawer-header">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-bag text-primary fs-5"></i>
            <h5 class="m-0 fw-bold text-nowrap">Shopping Bag (<span class="cart-drawer-item-count"><?= get_cart_count() ?></span>)</h5>
        </div>
        <button type="button" class="cart-drawer-close" aria-label="Close Shopping Bag">&times;</button>
    </div>

    <!-- Free Shipping Progress Meter -->
    <div class="free-shipping-meter-wrap">
        <div class="free-shipping-meter-text small fw-semibold text-center mb-1">
            Loading shipping details...
        </div>
        <div class="meter-bar-outer">
            <div class="meter-bar-fill" style="width: 0%;"></div>
        </div>
    </div>

    <!-- Scrollable Items Container -->
    <div class="cart-items-scrollable">
        <div class="text-center py-5 text-muted">
            <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
            <div>Loading your bag...</div>
        </div>
    </div>

    <!-- Drawer Footer -->
    <div class="cart-drawer-footer" style="display: none;">
        <!-- Coupon Form in Drawer -->
        <div class="mb-3">
            <div class="input-group">
                <input type="text" id="drawerCouponInput" class="form-control form-control-sm" placeholder="Apply Coupon Code (e.g. WELCOME10)" style="text-transform: uppercase;">
                <button class="btn btn-outline-secondary btn-sm fw-bold" type="button" onclick="CartDrawer.applyCoupon(document.getElementById('drawerCouponInput').value)">Apply</button>
            </div>
        </div>

        <!-- Totals summary -->
        <div class="d-flex justify-content-between mb-1 small">
            <span class="text-muted">Subtotal:</span>
            <span class="fw-bold cart-subtotal-val">₹0</span>
        </div>
        <div class="justify-content-between mb-1 small text-success cart-discount-row" style="display: none;">
            <span>Coupon Discount:</span>
            <span class="fw-bold cart-discount-val">-₹0</span>
        </div>
        <div class="d-flex justify-content-between mb-2 small">
            <span class="text-muted">Shipping:</span>
            <span class="fw-bold cart-shipping-val">FREE</span>
        </div>
        <div class="d-flex justify-content-between mb-3 border-top pt-2">
            <span class="fw-bold fs-6">Estimated Total:</span>
            <span class="fw-bold fs-5 text-primary cart-grand-total-val">₹0</span>
        </div>

        <div class="d-grid gap-2">
            <a href="<?= url('checkout') ?>" class="btn btn-afh-primary text-center py-2 fw-bold">Proceed to Checkout →</a>
            <a href="<?= url('cart') ?>" class="btn btn-sm btn-outline-secondary text-center">View Full Shopping Bag</a>
        </div>
    </div>

</div>
