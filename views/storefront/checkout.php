<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="checkout-page-wrap py-4 py-md-5" style="background: #FDF9FB; min-height: 85vh;">
    <div class="container" style="max-width: 1140px;">
        
        <!-- Modern Stepper & Trust Header -->
        <div class="mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb small m-0">
                        <li class="breadcrumb-item"><a href="<?= url() ?>" class="text-decoration-none text-muted">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('cart') ?>" class="text-decoration-none text-muted">Shopping Bag</a></li>
                        <li class="breadcrumb-item active text-primary fw-bold">Secure Checkout</li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <i class="bi bi-shield-lock-fill text-success fs-6"></i>
                    <span class="fw-semibold text-dark">256-Bit SSL Encrypted &amp; PCI-DSS Compliant</span>
                </div>
            </div>

            <!-- Stepper Pills -->
            <div class="d-flex align-items-center justify-content-center gap-2 gap-md-4 py-2 px-3 bg-white rounded-pill shadow-xs border" style="max-width: 580px; margin: 0 auto;">
                <div class="d-flex align-items-center gap-2 text-success fw-bold small">
                    <span class="badge bg-success rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-check"></i></span>
                    <span class="d-none d-sm-inline">1. Bag</span>
                </div>
                <div class="text-muted small" style="opacity: 0.4;">───</div>
                <div class="d-flex align-items-center gap-2 text-primary fw-extrabold small">
                    <span class="badge bg-afh-primary text-white rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center;">2</span>
                    <span>2. Delivery &amp; Payment</span>
                </div>
                <div class="text-muted small" style="opacity: 0.4;">───</div>
                <div class="d-flex align-items-center gap-2 text-muted fw-semibold small">
                    <span class="badge bg-light text-muted border rounded-circle p-1" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center;">3</span>
                    <span class="d-none d-sm-inline">3. Confirmation</span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- Left Column: Checkout Forms -->
            <div class="col-lg-7 col-xl-8">
                <form id="checkoutForm" onsubmit="event.preventDefault(); processCheckout(this);">
                    <input type="hidden" name="idempotency_token" value="<?= e($idempotencyToken ?? '') ?>">
                    
                    <!-- Section 1: Delivery Address -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4 bg-white mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                                <span class="badge bg-afh-primary text-white rounded-circle p-1" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem;">1</span>
                                Shipping &amp; Delivery Details
                            </h5>
                            <?php if (!is_authenticated()): ?>
                                <a href="<?= url('login') ?>" class="small text-primary fw-bold text-decoration-none">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Login for Saved Addresses
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Saved Addresses for Logged In Customer -->
                        <?php if (!empty($savedAddresses)): ?>
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted text-uppercase d-flex justify-content-between">
                                    <span>Select from your saved addresses:</span>
                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 fw-bold text-decoration-none" onclick="clearAddressFields()">+ Enter New Address</button>
                                </label>
                                <div class="row g-2">
                                    <?php foreach ($savedAddresses as $idx => $addr): ?>
                                        <div class="col-md-6">
                                            <div class="p-3 rounded-4 border address-select-card cursor-pointer h-100 transition-all <?= $idx === 0 ? 'border-primary bg-light-brand' : 'bg-white' ?>" 
                                                 onclick="useSavedAddress(<?= htmlspecialchars(json_encode($addr)) ?>, this)"
                                                 style="border-width: 2px !important;">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <strong class="text-dark small"><?= e($addr['full_name']) ?></strong>
                                                    <span class="badge bg-secondary-subtle text-dark small text-uppercase" style="font-size: 0.65rem;"><?= e($addr['address_type']) ?></span>
                                                </div>
                                                <div class="text-muted small" style="line-height: 1.4; font-size: 0.8rem;">
                                                    <?= e($addr['house_flat']) ?>, <?= e($addr['area']) ?><br>
                                                    <?= e($addr['city']) ?>, <?= e($addr['state']) ?> - <strong><?= e($addr['pincode']) ?></strong><br>
                                                    <i class="bi bi-telephone text-primary me-1"></i> +91 <?= e($addr['mobile']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Address Input Form -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Full Name *</label>
                                <input type="text" name="full_name" id="chkFullName" class="form-control rounded-3 py-2" required value="<?= e($user['name'] ?? '') ?>" placeholder="e.g. Priya Sharma">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">10-Digit Mobile Number (For Delivery Updates) *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted small fw-bold">+91</span>
                                    <input type="tel" name="mobile" id="chkMobile" class="form-control rounded-end-3 py-2" maxlength="10" pattern="[0-9]{10}" required value="<?= e($user['mobile'] ?? '') ?>" placeholder="9876543210">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Email Address (Optional for Invoice &amp; Courier Tracking)</label>
                                <input type="email" name="email" id="chkEmail" class="form-control rounded-3 py-2" value="<?= e($user['email'] ?? '') ?>" placeholder="e.g. priya@example.com">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">Flat / House No. / Building / Society *</label>
                                <input type="text" name="house_flat" id="chkHouse" class="form-control rounded-3 py-2" required placeholder="e.g. Flat 402, Sunshine Heights">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Street / Colony / Area *</label>
                                <input type="text" name="area" id="chkArea" class="form-control rounded-3 py-2" required placeholder="e.g. Linking Road, Bandra West">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark">Landmark (Optional)</label>
                                <input type="text" name="landmark" id="chkLandmark" class="form-control rounded-3 py-2" placeholder="e.g. Near National College">
                            </div>
                            
                            <!-- PIN Code with Instant City/State Auto-Detection -->
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark d-flex justify-content-between">
                                    <span>PIN Code *</span>
                                    <span id="pinAutoStatus" class="text-success small" style="display: none; font-size: 0.725rem;"><i class="bi bi-check-circle"></i> Verified</span>
                                </label>
                                <input type="text" name="pincode" id="chkPincode" class="form-control rounded-3 py-2" maxlength="6" pattern="[0-9]{6}" required placeholder="e.g. 400050" oninput="autoLookupPincode(this.value)">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">City *</label>
                                <input type="text" name="city" id="chkCity" class="form-control rounded-3 py-2" required placeholder="City">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">State *</label>
                                <input type="text" name="state" id="chkState" class="form-control rounded-3 py-2" required placeholder="State">
                            </div>

                            <?php if (is_authenticated()): ?>
                                <div class="col-12 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="save_address" value="1" id="saveAddressCheck" checked>
                                        <label class="form-check-label small text-muted cursor-pointer" for="saveAddressCheck">
                                            Save this address to my profile for faster 1-click reorders
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Section 2: Payment Method Selection -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4 bg-white mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                                <span class="badge bg-afh-primary text-white rounded-circle p-1" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem;">2</span>
                                Select Payment Method
                            </h5>
                            <span class="badge bg-light text-success border"><i class="bi bi-shield-check me-1"></i>100% Safe &amp; Secure</span>
                        </div>
                        
                        <div class="d-flex flex-column gap-3">
                            
                            <!-- Payment Option A: Razorpay Online -->
                            <label class="p-3 p-md-4 rounded-4 border cursor-pointer payment-option-card transition-all active-payment-card" 
                                   id="labelPayOnline" 
                                   style="border-width: 2px !important; border-color: var(--afh-primary) !important; background: #FFF9FA;">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="d-flex gap-3">
                                        <input type="radio" name="payment_method" value="razorpay" class="form-check-input mt-1" checked onchange="handlePaymentChange('razorpay')">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <strong class="text-dark fs-6">Pay Online (UPI, Google Pay, PhonePe, Cards, NetBanking)</strong>
                                                <span class="badge bg-success-subtle text-success small fw-bold">RECOMMENDED</span>
                                            </div>
                                            <div class="text-muted small mb-2" style="font-size: 0.8rem;">
                                                Instant zero-contact order dispatch. Guaranteed zero extra handling fees.
                                            </div>
                                            <!-- Payment Logos Pill -->
                                            <div class="d-flex flex-wrap align-items-center gap-2 pt-1">
                                                <span class="badge bg-white text-dark border px-2 py-1"><i class="bi bi-qr-code text-primary me-1"></i> UPI / QR</span>
                                                <span class="badge bg-white text-dark border px-2 py-1">Google Pay</span>
                                                <span class="badge bg-white text-dark border px-2 py-1">PhonePe</span>
                                                <span class="badge bg-white text-dark border px-2 py-1">Paytm</span>
                                                <span class="badge bg-white text-dark border px-2 py-1"><i class="bi bi-credit-card-2-front text-secondary me-1"></i> Visa / Master / RuPay</span>
                                                <span class="badge bg-white text-dark border px-2 py-1">NetBanking (50+ Banks)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <i class="bi bi-check-circle-fill text-primary fs-5 d-none d-sm-block"></i>
                                </div>
                            </label>

                            <!-- Payment Option B: Cash on Delivery -->
                            <?php if ($codEnabled && $cart['subtotal'] >= $codMin && $cart['subtotal'] <= $codMax): ?>
                                <label class="p-3 p-md-4 rounded-4 border cursor-pointer payment-option-card transition-all" 
                                       id="labelPayCod" 
                                       style="border-width: 2px !important; border-color: #EFE8EC; background: #FFFFFF;">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="d-flex gap-3">
                                            <input type="radio" name="payment_method" value="cod" class="form-check-input mt-1" onchange="handlePaymentChange('cod')">
                                            <div>
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <strong class="text-dark fs-6">Cash on Delivery (COD)</strong>
                                                    <span class="badge bg-secondary-subtle text-dark small">₹<?= (int)$codFee ?> Handling Fee</span>
                                                </div>
                                                <div class="text-muted small" style="font-size: 0.8rem;">
                                                    Pay cash to the delivery partner when your package arrives at your doorstep.
                                                </div>
                                            </div>
                                        </div>
                                        <i class="bi bi-cash-stack text-muted fs-4 d-none d-sm-block"></i>
                                    </div>
                                </label>
                            <?php endif; ?>

                        </div>

                        <!-- COD Fee Notice Alert -->
                        <div id="codNotice" class="alert alert-warning border-0 rounded-3 small mt-3 mb-0 p-3" style="display: none; background: #FFF9E6; border: 1px solid #FFE082 !important;">
                            <i class="bi bi-info-circle-fill text-warning me-1"></i> 
                            A nominal courier collection fee of <strong>₹<?= (int)$codFee ?></strong> applies to Cash on Delivery shipments. Please keep exact cash ready upon arrival.
                        </div>
                    </div>

                    <!-- Discreet Packaging Guarantee Banner -->
                    <div class="p-3 p-md-4 rounded-4 mb-4 d-flex align-items-center gap-3 bg-white border shadow-xs" style="border-left: 4px solid var(--afh-primary) !important;">
                        <div class="rounded-circle bg-light-brand p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 54px; height: 54px;">
                            <i class="bi bi-shield-check text-primary fs-3"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark mb-1">100% Discreet Plain Box Packaging Guarantee</strong>
                            <small class="text-muted" style="line-height: 1.4;">
                                Your comfort &amp; privacy are our top priority. Every shipment is delivered in a completely plain, tamper-evident courier box with zero brand or intimate labels.
                            </small>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-afh-primary btn-lg py-3 fw-bold rounded-4 shadow-sm" id="placeOrderBtn" style="font-size: 1.05rem; letter-spacing: 0.02em;">
                            <i class="bi bi-lock-fill me-2"></i> PLACE ORDER NOW (₹<span id="btnTotalText"><?= number_format($cart['grand_total'], 0) ?></span>)
                        </button>
                    </div>

                    <div class="text-center text-muted small" style="font-size: 0.75rem;">
                        By placing this order, you agree to AFOREHEALTH's 
                        <a href="<?= url('terms-conditions') ?>" class="text-muted text-decoration-underline" target="_blank">Terms of Service</a> and 
                        <a href="<?= url('privacy-policy') ?>" class="text-muted text-decoration-underline" target="_blank">Privacy Policy</a>.
                    </div>
                </form>
            </div>

            <!-- Right Column: Sticky Order Summary & Coupon -->
            <div class="col-lg-5 col-xl-4">
                <div class="position-sticky" style="top: 100px;">
                    
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h5 class="fw-bold m-0 text-dark">Order Summary</h5>
                            <span class="badge bg-light text-primary border rounded-pill px-2 py-1"><?= $cart['item_count'] ?> items</span>
                        </div>

                        <!-- Mini Items Scrollable List -->
                        <div class="d-flex flex-column gap-2 mb-3 pe-1" style="max-height: 240px; overflow-y: auto;">
                            <?php foreach ($cart['items'] as $item): ?>
                                <div class="d-flex align-items-center gap-2 py-2 border-bottom">
                                    <div class="rounded-3 border overflow-hidden bg-light flex-shrink-0" style="width: 48px; height: 48px;">
                                        <img src="<?= upload_url('products/' . $item['image']) ?>" alt="<?= e($item['name']) ?>" class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <div class="flex-grow-1 small" style="min-width: 0;">
                                        <div class="fw-bold text-dark text-truncate"><?= e($item['name']) ?></div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <?= !empty($item['size']) ? 'Size: ' . e($item['size']) . ' • ' : '' ?>Qty: <?= $item['quantity'] ?>
                                        </div>
                                    </div>
                                    <div class="fw-bold text-dark small text-nowrap">₹<?= number_format($item['line_total'], 0) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- In-Checkout Promo Code Box -->
                        <div class="mb-3 p-2 rounded-3 bg-light border">
                            <label class="form-label small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.7rem;">Have a Coupon Code?</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="chkCouponInput" class="form-control text-uppercase font-monospace" placeholder="e.g. WELCOME10" value="<?= e($cart['coupon_code'] ?? '') ?>">
                                <button type="button" class="btn btn-afh-primary fw-bold px-3" onclick="applyCheckoutCoupon()">Apply</button>
                            </div>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Bag Subtotal:</span>
                            <span class="fw-bold text-dark">₹<?= number_format($cart['subtotal'], 0) ?></span>
                        </div>

                        <?php if (!empty($cart['bundle_discount']) && $cart['bundle_discount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2 small text-success">
                                <span><i class="bi bi-tag-fill me-1"></i> Bundle Savings:</span>
                                <span class="fw-bold">-₹<?= number_format($cart['bundle_discount'], 0) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($cart['coupon_discount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2 small text-success">
                                <span><i class="bi bi-percent me-1"></i> Coupon (<?= e($cart['coupon_code']) ?>):</span>
                                <span class="fw-bold">-₹<?= number_format($cart['coupon_discount'], 0) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Standard Courier Delivery:</span>
                            <span class="fw-bold"><?= $cart['shipping_fee'] > 0 ? '₹' . number_format($cart['shipping_fee'], 0) : '<span class="text-success fw-bold">FREE</span>' ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-2 small" id="codFeeSummaryRow" style="display: none;">
                            <span class="text-muted">COD Collection Handling:</span>
                            <span class="fw-bold text-dark">+₹<?= (int)$codFee ?></span>
                        </div>

                        <div class="d-flex justify-content-between border-top pt-3 mt-2">
                            <div>
                                <span class="fs-6 fw-bold text-dark d-block">Grand Total:</span>
                                <small class="text-muted" style="font-size: 0.725rem;">Inclusive of all Indian taxes</small>
                            </div>
                            <span class="fs-4 fw-extrabold text-primary" id="summaryGrandTotal">₹<?= number_format($cart['grand_total'], 0) ?></span>
                        </div>

                        <?php if ($cart['total_savings'] > 0): ?>
                            <div class="alert alert-success border-0 rounded-3 text-center small py-2 mt-3 mb-0" style="background: #EBF7ED; color: #1E6B38; font-weight: 700;">
                                🎉 You are saving ₹<?= number_format($cart['total_savings'], 0) ?> on this order!
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Trust Cards Under Summary -->
                    <div class="d-flex justify-content-around text-center p-3 rounded-4 bg-white border shadow-xs text-muted" style="font-size: 0.75rem;">
                        <div>
                            <i class="bi bi-arrow-repeat fs-4 text-primary d-block mb-1"></i>
                            <span>Hassle-Free<br>Exchanges</span>
                        </div>
                        <div class="border-start"></div>
                        <div>
                            <i class="bi bi-box2-heart fs-4 text-primary d-block mb-1"></i>
                            <span>100% Discreet<br>Packaging</span>
                        </div>
                        <div class="border-start"></div>
                        <div>
                            <i class="bi bi-patch-check fs-4 text-primary d-block mb-1"></i>
                            <span>Certified<br>Quality</span>
                        </div>
                    </div>

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

function handlePaymentChange(method) {
    const isCod = (method === 'cod');
    const labelOnline = document.getElementById('labelPayOnline');
    const labelCod = document.getElementById('labelPayCod');
    
    if (isCod) {
        if (labelOnline) {
            labelOnline.style.borderColor = '#EFE8EC';
            labelOnline.style.background = '#FFFFFF';
        }
        if (labelCod) {
            labelCod.style.borderColor = 'var(--afh-primary)';
            labelCod.style.background = '#FFF9FA';
        }
    } else {
        if (labelOnline) {
            labelOnline.style.borderColor = 'var(--afh-primary)';
            labelOnline.style.background = '#FFF9FA';
        }
        if (labelCod) {
            labelCod.style.borderColor = '#EFE8EC';
            labelCod.style.background = '#FFFFFF';
        }
    }

    document.getElementById('codNotice').style.display = isCod ? 'block' : 'none';
    document.getElementById('codFeeSummaryRow').style.display = isCod ? 'flex' : 'none';
    const total = isCod ? (baseTotal + codFee) : baseTotal;
    document.getElementById('btnTotalText').textContent = Math.round(total).toLocaleString('en-IN');
    document.getElementById('summaryGrandTotal').textContent = '₹' + Math.round(total).toLocaleString('en-IN');

    if (window.trackAnalytics) {
        window.trackAnalytics('add_payment_info', { payment_type: method });
    }
}

function autoLookupPincode(pin) {
    const cleanPin = pin.trim().replace(/[^0-9]/g, '');
    const statusEl = document.getElementById('pinAutoStatus');
    if (cleanPin.length === 6) {
        fetch('/api/pincode/check?pincode=' + cleanPin)
            .then(res => res.json())
            .then(data => {
                if (data.serviceable) {
                    if (data.city) document.getElementById('chkCity').value = data.city;
                    if (data.state) document.getElementById('chkState').value = data.state;
                    if (statusEl) {
                        statusEl.style.display = 'inline';
                        statusEl.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i> ' + data.city + ', ' + data.state;
                    }
                } else if (statusEl) {
                    statusEl.style.display = 'inline';
                    statusEl.innerHTML = '<span class="text-danger">Unserviceable</span>';
                }
            })
            .catch(() => {});
    } else if (statusEl) {
        statusEl.style.display = 'none';
    }
}

function useSavedAddress(addr, el) {
    document.querySelectorAll('.address-select-card').forEach(c => {
        c.style.borderColor = '#EFE8EC';
        c.style.background = '#FFFFFF';
    });
    el.style.borderColor = 'var(--afh-primary)';
    el.style.background = '#FFF9FA';

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
    document.querySelectorAll('.address-select-card').forEach(c => {
        c.style.borderColor = '#EFE8EC';
        c.style.background = '#FFFFFF';
    });
    document.getElementById('chkHouse').value = '';
    document.getElementById('chkArea').value = '';
    document.getElementById('chkLandmark').value = '';
    document.getElementById('chkPincode').value = '';
    document.getElementById('chkCity').value = '';
    document.getElementById('chkState').value = '';
    document.getElementById('chkHouse').focus();
}

function applyCheckoutCoupon() {
    const code = document.getElementById('chkCouponInput').value.trim();
    if (!code) {
        showToast('Please enter a coupon code.', 'warning');
        return;
    }
    const formData = new FormData();
    formData.append('code', code);
    fetch('/cart/coupon', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 400);
        } else {
            showToast(data.message || 'Invalid coupon code.', 'danger');
        }
    })
    .catch(() => showToast('Could not apply coupon.', 'danger'));
}

function processCheckout(form) {
    const btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Securing Order...';

    const formData = new FormData(form);

    fetch('/checkout/process', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            showToast(data.message || 'Checkout failed. Please check your form details.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i> PLACE ORDER NOW';
            return;
        }

        // Online Gateway Payment
        if (data.requires_payment) {
            const options = {
                key: data.razorpay_key,
                amount: data.amount,
                currency: data.currency || 'INR',
                name: 'AFOREHEALTH',
                description: 'Order #' + data.order_number,
                order_id: data.razorpay_order_id,
                handler: function(response) {
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verifying Payment...';
                    
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
                            btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i> PLACE ORDER NOW';
                        }
                    })
                    .catch(() => {
                        showToast('Error verifying payment. Contact support.', 'danger');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i> PLACE ORDER NOW';
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
                        showToast('Payment window closed. You can retry anytime.', 'warning');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i> PLACE ORDER NOW';
                    }
                },
                prefill: {
                    name: data.customer_name,
                    email: data.customer_email,
                    contact: data.customer_mobile
                },
                theme: { color: '#8B5E83' }
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
                btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i> PLACE ORDER NOW';
            });
            rzp.open();
            return;
        }

        // COD / Immediate Redirect
        if (data.redirect_url) {
            window.location.href = data.redirect_url;
        }
    })
    .catch(() => {
        showToast('Connection error. Please try again.', 'danger');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i> PLACE ORDER NOW';
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
