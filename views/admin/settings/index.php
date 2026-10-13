<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="row g-4">
    <div class="col-12">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2">
            <div>
                <h4 class="fw-bold mb-1 text-dark">Store &amp; System Configuration</h4>
                <p class="text-muted small mb-0">Manage business details, payment gateways, WhatsApp community, shipping, and integrations.</p>
            </div>
            <div>
                <a href="<?= url('admin/settings/backup') ?>" class="btn btn-outline-dark fw-bold d-flex align-items-center gap-2 rounded-pill shadow-sm" data-confirm-title="Download Database Backup?" data-confirm-msg="Are you sure you want to generate and download a complete SQL backup of the database?" data-confirm-type="info">
                    <i class="bi bi-database-down text-primary"></i> Download Full Database Backup (.sql)
                </a>
            </div>
        </div>

        <form action="<?= url('admin/settings/update') ?>" method="POST">
            <!-- Navigation Tabs -->
            <ul class="nav nav-pills mb-4 gap-2" id="settingsTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active rounded-pill px-4 fw-bold" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button">
                        <i class="bi bi-shop me-1"></i> General &amp; Business
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="shipping-tab" data-bs-toggle="pill" data-bs-target="#shipping" type="button">
                        <i class="bi bi-truck me-1"></i> Shipping &amp; COD
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="payment-tab" data-bs-toggle="pill" data-bs-target="#payment" type="button">
                        <i class="bi bi-credit-card me-1"></i> Razorpay &amp; Payments
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="delivery-tab" data-bs-toggle="pill" data-bs-target="#delivery" type="button">
                        <i class="bi bi-clock-history me-1"></i> Delivery &amp; SLAs
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="loyalty-tab" data-bs-toggle="pill" data-bs-target="#loyalty" type="button">
                        <i class="bi bi-coin me-1"></i> Loyalty &amp; Referrals
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="firstorder-tab" data-bs-toggle="pill" data-bs-target="#firstorder" type="button">
                        <i class="bi bi-stars me-1"></i> First-Order Offer
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="whatsapp-tab" data-bs-toggle="pill" data-bs-target="#whatsapp" type="button">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp Community
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="sms-tab" data-bs-toggle="pill" data-bs-target="#sms" type="button">
                        <i class="bi bi-chat-dots me-1"></i> SMS Gateway
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 fw-bold" id="analytics-tab" data-bs-toggle="pill" data-bs-target="#analytics" type="button">
                        <i class="bi bi-graph-up me-1"></i> Analytics &amp; Pixels
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="settingsTabContent">
                <!-- General Tab -->
                <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">Brand &amp; Contact Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Store Brand Name</label>
                                <input type="text" name="store_name" class="form-control" value="<?= e($settings['store_name'] ?? 'AFOREHEALTH') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Brand Tagline</label>
                                <input type="text" name="store_tagline" class="form-control" value="<?= e($settings['store_tagline'] ?? 'Everyday Comfort. Period Confidence.') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Support Email</label>
                                <input type="email" name="store_email" class="form-control" value="<?= e($settings['store_email'] ?? 'care@aforehealth.com') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Official Support Phone</label>
                                <input type="text" name="store_phone" class="form-control" value="<?= e($settings['store_phone'] ?? '+91 98765 43210') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Company GSTIN</label>
                                <input type="text" name="store_gstin" class="form-control" value="<?= e($settings['store_gstin'] ?? '07AAAAA0000A1Z5') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Registered Office Address</label>
                                <input type="text" name="store_address" class="form-control" value="<?= e($settings['store_address'] ?? 'Plot 42, Udyog Vihar Phase 4, Gurugram, Haryana - 122015') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Tab -->
                <div class="tab-pane fade" id="shipping" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">Shipping &amp; Cash on Delivery (COD) Rules</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Free Shipping Minimum Threshold (₹)</label>
                                <input type="number" name="free_shipping_threshold" class="form-control" value="<?= e($settings['free_shipping_threshold'] ?? '499') ?>">
                                <div class="form-text">Orders equal to or above this value qualify for free domestic shipping.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Standard Flat Shipping Fee (₹)</label>
                                <input type="number" name="flat_shipping_fee" class="form-control" value="<?= e($settings['flat_shipping_fee'] ?? '60') ?>">
                                <div class="form-text">Applied when order total is below threshold.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Cash On Delivery (COD) Enabled?</label>
                                <select name="cod_enabled" class="form-select">
                                    <option value="1" <?= ($settings['cod_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                                    <option value="0" <?= ($settings['cod_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">COD Handling Fee (₹)</label>
                                <input type="number" name="cod_fee" class="form-control" value="<?= e($settings['cod_fee'] ?? '40') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Minimum Order for COD (₹)</label>
                                <input type="number" name="cod_min_order" class="form-control" value="<?= e($settings['cod_min_order'] ?? '299') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Maximum Order for COD (₹)</label>
                                <input type="number" name="cod_max_order" class="form-control" value="<?= e($settings['cod_max_order'] ?? '3000') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Tab -->
                <div class="tab-pane fade" id="payment" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">Razorpay Payment Gateway</h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Razorpay Online Payment Mode</label>
                                <select name="razorpay_enabled" class="form-select">
                                    <option value="1" <?= ($settings['razorpay_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled (Cards, UPI, Netbanking, Wallets)</option>
                                    <option value="0" <?= ($settings['razorpay_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Razorpay Key ID</label>
                                <input type="text" name="razorpay_key" class="form-control" value="<?= e($settings['razorpay_key'] ?? 'rzp_test_placeholder') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Razorpay Key Secret</label>
                                <input type="password" name="razorpay_secret" class="form-control" value="<?= e($settings['razorpay_secret'] ?? '••••••••••••••••') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery & SLAs Tab -->
                <div class="tab-pane fade" id="delivery" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">Dynamic Delivery Estimation Rules (Asia/Kolkata)</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Default Min Delivery Days</label>
                                <input type="number" name="delivery_default_min_days" class="form-control" value="<?= e($settings['delivery_default_min_days'] ?? '3') ?>" min="1" max="15">
                                <div class="form-text">Minimum business days if no PIN override is set.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Default Max Delivery Days</label>
                                <input type="number" name="delivery_default_max_days" class="form-control" value="<?= e($settings['delivery_default_max_days'] ?? '5') ?>" min="1" max="20">
                                <div class="form-text">Maximum business days for SLA range.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Weekend Handling</label>
                                <select name="delivery_skip_weekends" class="form-select">
                                    <option value="1" <?= ($settings['delivery_skip_weekends'] ?? '1') == '1' ? 'selected' : '' ?>>Skip Sundays in Delivery SLA</option>
                                    <option value="0" <?= ($settings['delivery_skip_weekends'] ?? '1') == '0' ? 'selected' : '' ?>>Count All 7 Calendar Days</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Configured Holiday Dates (Non-working delivery days)</label>
                                <input type="text" name="delivery_holiday_dates" class="form-control" placeholder="2026-10-02, 2026-10-24, 2026-11-01" value="<?= e($settings['delivery_holiday_dates'] ?? '') ?>">
                                <div class="form-text">Comma-separated YYYY-MM-DD dates to exclude when calculating estimated arrival dates.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Loyalty & Referrals Tab -->
                <div class="tab-pane fade" id="loyalty" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">AFORE Points &amp; Rewards Ledger System</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Loyalty System Status</label>
                                <select name="loyalty_enabled" class="form-select">
                                    <option value="1" <?= ($settings['loyalty_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                                    <option value="0" <?= ($settings['loyalty_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Points Earned per ₹100 Spent</label>
                                <input type="number" name="loyalty_points_per_100_inr" class="form-control" value="<?= e($settings['loyalty_points_per_100_inr'] ?? '5') ?>" min="1">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Point Redemption Value (₹ per point)</label>
                                <input type="number" name="loyalty_point_redemption_value" class="form-control" value="<?= e($settings['loyalty_point_redemption_value'] ?? '1.00') ?>" step="0.25" min="0.25">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Minimum Points for Redemption</label>
                                <input type="number" name="loyalty_min_redemption_points" class="form-control" value="<?= e($settings['loyalty_min_redemption_points'] ?? '50') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Maximum Points Redeemable per Order</label>
                                <input type="number" name="loyalty_max_redemption_points" class="form-control" value="<?= e($settings['loyalty_max_redemption_points'] ?? '500') ?>">
                            </div>
                        </div>

                        <h5 class="fw-bold mt-4 mb-3 border-bottom pb-2 text-dark">Referral Program Controls</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Referral Program Status</label>
                                <select name="referral_enabled" class="form-select">
                                    <option value="1" <?= ($settings['referral_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                                    <option value="0" <?= ($settings['referral_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Referrer Reward (Points)</label>
                                <input type="number" name="referral_reward_points" class="form-control" value="<?= e($settings['referral_reward_points'] ?? '100') ?>">
                                <div class="form-text">Credited to customer who shared link.</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Friend First Order Discount (₹)</label>
                                <input type="number" name="referral_friend_discount" class="form-control" value="<?= e($settings['referral_friend_discount'] ?? '100') ?>">
                                <div class="form-text">Given to invited customer on first purchase.</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Min Order Value to Qualify (₹)</label>
                                <input type="number" name="referral_min_order" class="form-control" value="<?= e($settings['referral_min_order'] ?? '499') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- First-Order Offer Tab -->
                <div class="tab-pane fade" id="firstorder" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">First Order Welcome Promotion</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Enable Welcome Popup / Offer</label>
                                <select name="first_order_offer_enabled" class="form-select">
                                    <option value="1" <?= ($settings['first_order_offer_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                                    <option value="0" <?= ($settings['first_order_offer_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Welcome Coupon Code</label>
                                <input type="text" name="first_order_coupon_code" class="form-control text-uppercase" value="<?= e($settings['first_order_coupon_code'] ?? 'WELCOME10') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Discount Amount (₹)</label>
                                <input type="number" name="first_order_discount_value" class="form-control" value="<?= e($settings['first_order_discount_value'] ?? '100') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Min Order Value (₹)</label>
                                <input type="number" name="first_order_min_order" class="form-control" value="<?= e($settings['first_order_min_order'] ?? '399') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Tab -->
                <div class="tab-pane fade" id="whatsapp" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">WhatsApp Community &amp; Direct Support</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">WhatsApp Business Number (with country code)</label>
                                <input type="text" name="whatsapp_number" class="form-control" placeholder="919876543210" value="<?= e($settings['whatsapp_number'] ?? '919876543210') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Official WhatsApp Community / Group Invite URL</label>
                                <input type="url" name="whatsapp_group_url" class="form-control" placeholder="https://chat.whatsapp.com/..." value="<?= e($settings['whatsapp_group_url'] ?? 'https://chat.whatsapp.com/AFOREHEALTH_COMMUNITY') ?>">
                                <div class="form-text">Shown prominently on Order Success page for voluntary opt-in.</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Default Support Greeting Text</label>
                                <textarea name="whatsapp_support_message" class="form-control" rows="2"><?= e($settings['whatsapp_support_message'] ?? 'Hi AFOREHEALTH Care, I have an inquiry about intimate care products or my order.') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SMS Tab -->
                <div class="tab-pane fade" id="sms" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">SMS &amp; Indian OTP Gateway Settings</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">SMS Gateway Status</label>
                                <select name="sms_enabled" class="form-select">
                                    <option value="1" <?= ($settings['sms_enabled'] ?? '1') == '1' ? 'selected' : '' ?>>Enabled</option>
                                    <option value="0" <?= ($settings['sms_enabled'] ?? '1') == '0' ? 'selected' : '' ?>>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Provider</label>
                                <select name="sms_provider" class="form-select">
                                    <option value="msg91" <?= ($settings['sms_provider'] ?? 'msg91') == 'msg91' ? 'selected' : '' ?>>MSG91 (India DLT Compliant)</option>
                                    <option value="twilio" <?= ($settings['sms_provider'] ?? '') == 'twilio' ? 'selected' : '' ?>>Twilio</option>
                                    <option value="generic" <?= ($settings['sms_provider'] ?? '') == 'generic' ? 'selected' : '' ?>>Generic HTTP Gateway</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">DLT Header / Sender ID</label>
                                <input type="text" name="sms_sender_id" class="form-control" value="<?= e($settings['sms_sender_id'] ?? 'AFRHLT') ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">API Auth Key</label>
                                <input type="text" name="sms_api_key" class="form-control" value="<?= e($settings['sms_api_key'] ?? 'MSG91_KEY_DEMO_AFH') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics Tab -->
                <div class="tab-pane fade" id="analytics" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark">Marketing Analytics &amp; Tracking Pixels</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Google Analytics 4 (GA4 Measurement ID)</label>
                                <input type="text" name="ga4_id" class="form-control" placeholder="G-XXXXXXXXXX" value="<?= e($settings['ga4_id'] ?? 'G-AFOREHLTH01') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Google Tag Manager (GTM ID)</label>
                                <input type="text" name="gtm_id" class="form-control" placeholder="GTM-XXXXXXX" value="<?= e($settings['gtm_id'] ?? 'GTM-AFH998') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Meta / Facebook Pixel ID</label>
                                <input type="text" name="meta_pixel_id" class="form-control" placeholder="123456789012345" value="<?= e($settings['meta_pixel_id'] ?? '8849204910294') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-5">
                <button type="submit" class="btn btn-afh-primary btn-lg px-5 fw-bold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Save All Settings
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
