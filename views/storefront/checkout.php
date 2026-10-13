<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('cart') ?>">Bag</a></li>
            <li class="breadcrumb-item active">Checkout</li>
        </ol>
    </nav>

    <h1 class="h2 fw-bold text-dark mb-4">Secure Checkout</h1>

    <div class="row g-4">
        
        <!-- Left: Checkout Forms -->
        <div class="col-lg-8">
            <form id="checkoutForm" onsubmit="event.preventDefault(); processCheckout(this);">
                <input type="hidden" name="idempotency_token" value="<?= e($idempotencyToken ?? '') ?>">
                
                <!-- 1. Customer Details & Address -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold m-0"><span class="badge bg-primary rounded-circle me-2">1</span> Delivery Address</h5>
                        <?php if (!is_authenticated()): ?>
                            <a href="<?= url('login') ?>" class="small text-primary fw-semibold">Already have an account? Login</a>
                        <?php endif; ?>
                    </div>

                    <!-- Saved Addresses for Logged In Customer -->
                    <?php if (!empty($savedAddresses)): ?>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Select Saved Address:</label>
                            <div class="row g-2">
                                <?php foreach ($savedAddresses as $idx => $addr): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 rounded-3 border address-select-card cursor-pointer <?= $idx === 0 ? 'border-primary bg-light' : '' ?>" onclick="useSavedAddress(<?= htmlspecialchars(json_encode($addr)) ?>, this)">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong class="small"><?= e($addr['full_name']) ?></strong>
                                                <span class="badge bg-secondary text-dark small text-uppercase"><?= e($addr['address_type']) ?></span>
                                            </div>
                                            <div class="text-muted small" style="line-height: 1.4;">
                                                <?= e($addr['house_flat']) ?>, <?= e($addr['area']) ?><br>
                                                <?= e($addr['city']) ?>, <?= e($addr['state']) ?> - <?= e($addr['pincode']) ?><br>
                                                Ph: <?= e($addr['mobile']) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-2 text-end">
                                <button type="button" class="btn btn-sm btn-link text-primary p-0" onclick="clearAddressFields()">+ Enter New Address</button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Address Input Fields -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Full Name *</label>
                            <input type="text" name="full_name" id="chkFullName" class="form-control" required value="<?= e($user['name'] ?? '') ?>" placeholder="e.g. Priya Sharma">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile Number (10 Digits) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">+91</span>
                                <input type="tel" name="mobile" id="chkMobile" class="form-control" maxlength="10" pattern="[0-9]{10}" required value="<?= e($user['mobile'] ?? '') ?>" placeholder="9876543210">
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Email Address (Optional for Invoice &amp; Tracking)</label>
                            <input type="email" name="email" id="chkEmail" class="form-control" value="<?= e($user['email'] ?? '') ?>" placeholder="care@example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Flat / House No. / Building *</label>
                            <input type="text" name="house_flat" id="chkHouse" class="form-control" required placeholder="e.g. Flat 402, Sunshine Heights">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Street Address / Area *</label>
                            <input type="text" name="area" id="chkArea" class="form-control" required placeholder="e.g. Linking Road, Bandra West">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Landmark (Optional)</label>
                            <input type="text" name="landmark" id="chkLandmark" class="form-control" placeholder="e.g. Near National College">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">PIN Code (6 Digits) *</label>
                            <input type="text" name="pincode" id="chkPincode" class="form-control" maxlength="6" pattern="[0-9]{6}" required placeholder="400050">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">City *</label>
                            <input type="text" name="city" id="chkCity" class="form-control" required placeholder="Mumbai">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">State *</label>
                            <input type="text" name="state" id="chkState" class="form-control" required placeholder="Maharashtra">
                        </div>

                        <?php if (is_authenticated()): ?>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="save_address" value="1" id="saveAddressCheck" checked>
                                    <label class="form-check-label small text-muted" for="saveAddressCheck">
                                        Save this address to my profile for future orders
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Payment Method -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-3"><span class="badge bg-primary rounded-circle me-2">2</span> Payment Method</h5>
                    
                    <div class="d-flex flex-column gap-3">
                        <!-- Razorpay Online -->
                        <label class="p-3 rounded-3 border d-flex align-items-center justify-content-between cursor-pointer payment-option-card border-primary bg-light">
                            <div class="d-flex align-items-center gap-3">
                                <input type="radio" name="payment_method" value="razorpay" class="form-check-input mt-0" checked onchange="toggleCodNotice(false)">
                                <div>
                                    <strong class="d-block text-dark">Pay Online (UPI, Cards, NetBanking, Wallets)</strong>
                                    <small class="text-muted">Instant confirmation via Razorpay. Recommended &amp; Free Shipping.</small>
                                </div>
                            </div>
                            <span class="badge bg-success-subtle text-success fw-bold">EXTRA FAST</span>
                        </label>

                        <!-- Cash on Delivery (COD) -->
                        <?php if ($codEnabled && $cart['subtotal'] >= $codMin && $cart['subtotal'] <= $codMax): ?>
                            <label class="p-3 rounded-3 border d-flex align-items-center justify-content-between cursor-pointer payment-option-card">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="radio" name="payment_method" value="cod" class="form-check-input mt-0" onchange="toggleCodNotice(true)">
                                    <div>
                                        <strong class="d-block text-dark">Cash on Delivery (COD)</strong>
                                        <small class="text-muted">Pay cash at the time of delivery (₹<?= (int)$codFee ?> handling fee applies).</small>
                                    </div>
                                </div>
                                <span class="badge bg-secondary text-dark">₹<?= (int)$codFee ?> FEE</span>
                            </label>
                        <?php endif; ?>
                    </div>

                    <div id="codNotice" class="alert alert-warning small mt-3 mb-0" style="display: none;">
                        <i class="bi bi-info-circle me-1"></i> A nominal handling fee of ₹<?= (int)$codFee ?> is added to COD orders. Please keep exact change ready upon delivery.
                    </div>
                </div>

                <!-- Discreet Packaging Guarantee Banner -->
                <div class="p-3 rounded-4 mb-4 d-flex align-items-center gap-3" style="background: #FFF9FA; border: 1px solid var(--afh-border);">
                    <i class="bi bi-shield-check text-success fs-2"></i>
                    <div>
                        <strong class="d-block text-dark">100% Discreet Packaging Guarantee</strong>
                        <small class="text-muted">Your privacy is sacred. All orders are packed in unmarked, opaque courier boxes with zero sensitive labels.</small>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-afh-primary btn-lg py-3 fw-bold shadow-sm" id="placeOrderBtn">
                        <i class="bi bi-lock-fill me-1"></i> PLACE ORDER NOW (₹<span id="btnTotalText"><?= number_format($cart['grand_total'], 0) ?></span>)
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Order Summary Sticky -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 120px;">
                <h5 class="fw-bold mb-3">Items in Bag (<?= $cart['item_count'] ?>)</h5>

                <!-- Items Mini List -->
                <div class="d-flex flex-column gap-2 mb-3 max-vh-50 overflow-y-auto">
                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                            <img src="<?= upload_url('products/' . $item['image']) ?>" alt="" class="rounded" style="width: 44px; height: 44px; object-fit: cover;">
                            <div class="flex-grow-1 small">
                                <div class="fw-bold text-truncate" style="max-width: 170px;"><?= e($item['name']) ?></div>
                                <div class="text-muted"><?= e($item['size'] ?? '') ?> × <?= $item['quantity'] ?></div>
                            </div>
                            <div class="fw-bold small">₹<?= number_format($item['line_total'], 0) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Price Breakdown -->
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
                        <span>Coupon (<?= e($cart['coupon_code']) ?>):</span>
                        <span class="fw-bold">-₹<?= number_format($cart['coupon_discount'], 0) ?></span>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Shipping:</span>
                    <span class="fw-bold"><?= $cart['shipping_fee'] > 0 ? '₹' . number_format($cart['shipping_fee'], 0) : '<span class="text-success">FREE</span>' ?></span>
                </div>

                <div class="d-flex justify-content-between mb-2 small" id="codFeeSummaryRow" style="display: none;">
                    <span class="text-muted">COD Fee:</span>
                    <span class="fw-bold text-dark">+₹<?= (int)$codFee ?></span>
                </div>

                <div class="d-flex justify-content-between border-top pt-3 mt-2">
                    <span class="fs-5 fw-bold">Total Amount:</span>
                    <span class="fs-4 fw-extrabold text-primary" id="summaryGrandTotal">₹<?= number_format($cart['grand_total'], 0) ?></span>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const baseTotal = <?= (float)$cart['grand_total'] ?>;
const codFee = <?= (float)$codFee ?>;

document.addEventListener('DOMContentLoaded', function() {
    if (window.trackAnalytics) {
        window.trackAnalytics('begin_checkout', {
            value: baseTotal,
            coupon: <?= json_encode($cart['coupon_code'] ?? '') ?>
        });
    }
});

function toggleCodNotice(isCod) {
    document.getElementById('codNotice').style.display = isCod ? 'block' : 'none';
    document.getElementById('codFeeSummaryRow').style.display = isCod ? 'flex' : 'none';
    const total = isCod ? (baseTotal + codFee) : baseTotal;
    document.getElementById('btnTotalText').textContent = Math.round(total);
    document.getElementById('summaryGrandTotal').textContent = '₹' + Math.round(total);
    if (window.trackAnalytics) {
        window.trackAnalytics('add_payment_info', {
            payment_type: isCod ? 'cod' : 'razorpay'
        });
    }
}

function useSavedAddress(addr, el) {
    document.querySelectorAll('.address-select-card').forEach(c => c.classList.remove('border-primary', 'bg-light'));
    el.classList.add('border-primary', 'bg-light');
    document.getElementById('chkFullName').value = addr.full_name;
    document.getElementById('chkMobile').value = addr.mobile;
    document.getElementById('chkHouse').value = addr.house_flat;
    document.getElementById('chkArea').value = addr.area;
    document.getElementById('chkLandmark').value = addr.landmark || '';
    document.getElementById('chkPincode').value = addr.pincode;
    document.getElementById('chkCity').value = addr.city;
    document.getElementById('chkState').value = addr.state;
}

function clearAddressFields() {
    document.querySelectorAll('.address-select-card').forEach(c => c.classList.remove('border-primary', 'bg-light'));
    document.getElementById('chkHouse').value = '';
    document.getElementById('chkArea').value = '';
    document.getElementById('chkLandmark').value = '';
    document.getElementById('chkPincode').value = '';
    document.getElementById('chkCity').value = '';
    document.getElementById('chkState').value = '';
}

function processCheckout(form) {
    const btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing Order...';

    const formData = new FormData(form);

    fetch('/checkout/process', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            showToast(data.message || 'Checkout failed. Please check form inputs.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> PLACE ORDER NOW';
            return;
        }

        // Case A: Online Gateway Payment
        if (data.requires_payment) {
            const options = {
                key: data.razorpay_key,
                amount: data.amount,
                currency: data.currency || 'INR',
                name: 'AFOREHEALTH',
                description: 'Order #' + data.order_number,
                order_id: data.razorpay_order_id,
                handler: function(response) {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying Payment Signature...';
                    
                    const verifyBody = new URLSearchParams({
                        order_number: data.order_number,
                        razorpay_order_id: response.razorpay_order_id || data.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature
                    });

                    fetch('/checkout/payment-verify', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: verifyBody
                    })
                    .then(r => r.json())
                    .then(verRes => {
                        if (verRes.success && verRes.redirect_url) {
                            window.location.href = verRes.redirect_url;
                        } else {
                            showToast(verRes.message || 'Payment verification failed.', 'danger');
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> PLACE ORDER NOW';
                        }
                    })
                    .catch(() => {
                        showToast('Error verifying payment. Please contact support.', 'danger');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> PLACE ORDER NOW';
                    });
                },
                modal: {
                    ondismiss: function() {
                        fetch('/checkout/payment-failed', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: new URLSearchParams({
                                order_number: data.order_number,
                                reason: 'Payment window closed by user'
                            })
                        });
                        showToast('Payment window cancelled. You can retry anytime.', 'warning');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> PLACE ORDER NOW';
                    }
                },
                prefill: {
                    name: data.customer_name,
                    email: data.customer_email,
                    contact: data.customer_mobile
                },
                theme: {
                    color: '#8B5E83'
                }
            };

            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function(resp) {
                fetch('/checkout/payment-failed', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        order_number: data.order_number,
                        reason: resp.error.description || 'Payment failed'
                    })
                });
                showToast(resp.error.description || 'Payment failed.', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> PLACE ORDER NOW';
            });
            rzp.open();
            return;
        }

        // Case B: COD / Immediate Redirect
        if (data.redirect_url) {
            window.location.href = data.redirect_url;
        }
    })
    .catch(() => {
        showToast('Connection error. Please try again.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> PLACE ORDER NOW';
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
