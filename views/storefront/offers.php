<?php require __DIR__ . '/../layouts/header.php'; ?>

<section class="py-5" style="background: linear-gradient(135deg, #FFF9FA 0%, #FAEDF3 100%); border-bottom: 1px solid #EEDCE6;">
    <div class="container text-center" style="max-width: 800px;">
        <span class="badge bg-danger text-white px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">Exclusive Savings</span>
        <h1 class="display-5 fw-bold text-dark mb-2">Offers &amp; Promo Codes</h1>
        <p class="lead text-muted mb-0">
            Use these active coupon codes at checkout to unlock instant discounts and free delivery across India.
        </p>
    </div>
</section>

<div class="container py-5">
    
    <!-- Active Coupons Grid -->
    <div class="row g-3 mb-5">
        <?php foreach ($coupons as $cp): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100" style="background: #FFFFFF; border: 2px dashed #8B5E83 !important;">
                    <span class="badge bg-light text-primary fw-bold text-uppercase mx-auto mb-2"><?= $cp['discount_type'] === 'percentage' ? (int)$cp['discount_value'] . '% OFF' : '₹' . (int)$cp['discount_value'] . ' FLAT OFF' ?></span>
                    <h3 class="fw-extrabold text-dark tracking-wide mb-1" style="letter-spacing: 2px;"><?= e($cp['code']) ?></h3>
                    <p class="small text-muted mb-3"><?= e($cp['title']) ?></p>
                    <button type="button" class="btn btn-sm btn-outline-dark w-100 mt-auto" onclick="navigator.clipboard.writeText('<?= e($cp['code']) ?>'); showToast('Coupon code copied to clipboard!', 'success');">
                        Copy Code
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- On Sale Products -->
    <div class="mb-4">
        <h3 class="fw-bold">Discounted Essentials</h3>
        <p class="text-muted">High performance intimate essentials at special seasonal prices</p>
    </div>

    <div class="row g-3 g-md-4">
        <?php foreach ($products as $product): ?>
            <div class="col-lg-3 col-md-4 col-6 product-grid-col">
                <?php require __DIR__ . '/../components/product-card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
