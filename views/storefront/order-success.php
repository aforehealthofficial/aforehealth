<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5" style="max-width: 780px;">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center">
        
        <!-- Success Icon -->
        <div class="mx-auto mb-3" style="width: 76px; height: 76px; border-radius: 50%; background: #E8F5EE; color: #2E7D52; display: flex; align-items: center; justify-content: center; font-size: 2.2rem;">
            <i class="bi bi-check-lg"></i>
        </div>

        <h1 class="h2 fw-extrabold text-dark mb-2">Thank you for shopping with AFOREHEALTH ❤️</h1>
        <p class="text-muted lead mb-4" style="font-size: 1.1rem;">
            Your order <strong>#<?= e($order['order_number']) ?></strong> has been placed successfully.
        </p>

        <!-- Order Summary Card -->
        <div class="p-4 rounded-3 text-start mb-4" style="background: #FFF9FA; border: 1px solid var(--afh-border);">
            <div class="row g-3">
                <div class="col-sm-6">
                    <small class="text-muted text-uppercase fw-bold">Order Reference:</small>
                    <div class="fw-bold text-dark fs-6"><?= e($order['order_number']) ?></div>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted text-uppercase fw-bold">Amount Paid / Due:</small>
                    <div class="fw-bold text-primary fs-6">₹<?= number_format($order['grand_total'], 0) ?> (<?= strtoupper($order['payment_method']) ?>)</div>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted text-uppercase fw-bold">Customer Name:</small>
                    <div class="fw-bold text-dark"><?= e($order['customer_name']) ?></div>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted text-uppercase fw-bold">Delivery Status:</small>
                    <div class="fw-bold text-success"><i class="bi bi-box-seam me-1"></i> Packing in Discreet Packaging</div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Community Sisterhood Box -->
        <?php if (!empty($order['whatsapp_opt_in'])): ?>
            <div class="p-4 rounded-4 mb-4 text-center" style="background: linear-gradient(135deg, #E8F5EE 0%, #DCF2E4 100%); border: 2px solid #25D366; box-shadow: 0 4px 15px rgba(37,211,102,0.15);">
                <span class="badge bg-success text-white px-3 py-1 rounded-pill mb-2 fw-bold" style="font-size: 0.75rem;">
                    <i class="bi bi-check-circle-fill me-1"></i> OPTED-IN DURING CHECKOUT
                </span>
                <i class="bi bi-whatsapp text-success fs-1 mb-2 d-block"></i>
                <h4 class="fw-bold text-dark mb-1">Welcome to the AFOREHEALTH Sisterhood!</h4>
                <p class="text-muted small mb-3" style="max-width: 500px; margin: 0 auto;">
                    Thank you for consenting to join! Tap the button below for instant 1-tap entry into our confidential WhatsApp group.
                </p>
                <a href="<?= e($whatsappCommunityUrl) ?>" target="_blank" class="btn btn-success px-4 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="bi bi-whatsapp me-2 fs-5"></i> TAP HERE TO ENTER WHATSAPP GROUP
                </a>
                <div class="mt-2 text-muted" style="font-size: 0.75rem;">
                    (Direct invite link &bull; Connect with 25,000+ Indian women &bull; 100% spam-free)
                </div>
            </div>
        <?php else: ?>
            <div class="p-3 rounded-4 mb-4 text-center bg-light border">
                <p class="text-muted small mb-2">
                    <i class="bi bi-whatsapp text-success me-1"></i> Want to join our WhatsApp community later?
                </p>
                <a href="<?= e($whatsappCommunityUrl) ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill fw-bold px-3">
                    View WhatsApp Community Group
                </a>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?= url('track-order?order_number=' . $order['order_number']) ?>" class="btn btn-outline-dark fw-bold px-4 py-2">
                <i class="bi bi-geo-alt me-1"></i> Track Order
            </a>
            <a href="<?= url('order/invoice/' . $order['order_number']) ?>" target="_blank" class="btn btn-outline-secondary fw-bold px-4 py-2">
                <i class="bi bi-printer me-1"></i> Download Invoice
            </a>
            <a href="<?= url('shop') ?>" class="btn btn-afh-primary fw-bold px-4 py-2">
                Continue Shopping →
            </a>
        </div>

        <!-- WhatsApp Support Link -->
        <div class="mt-4 pt-3 border-top">
            <p class="text-muted small mb-1">Need help or want to modify your order address?</p>
            <a href="<?= e($whatsappSupportUrl) ?>" target="_blank" class="fw-bold text-success text-decoration-none">
                <i class="bi bi-chat-dots-fill me-1"></i> Chat with us on WhatsApp Support (+91 98765 43210)
            </a>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.trackAnalytics) {
        window.trackAnalytics('purchase', {
            transaction_id: <?= json_encode($order['order_number']) ?>,
            value: <?= (float)$order['grand_total'] ?>,
            shipping: <?= (float)($order['shipping_fee'] ?? 0) ?>,
            payment_type: <?= json_encode($order['payment_method']) ?>,
            currency: 'INR'
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
