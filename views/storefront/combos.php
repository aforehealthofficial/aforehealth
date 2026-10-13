<?php require __DIR__ . '/../layouts/header.php'; ?>

<section class="py-5" style="background: linear-gradient(135deg, #FFF9FA 0%, #F5E8F0 100%); border-bottom: 1px solid #EEDCE6;">
    <div class="container text-center" style="max-width: 800px;">
        <span class="badge bg-secondary text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">Maximum Value</span>
        <h1 class="display-5 fw-bold text-dark mb-2">Combo &amp; Value Packs</h1>
        <p class="lead text-muted mb-0">
            Curated cycle bundles and multi-packs designed to cover your entire menstrual routine while saving you up to 35%.
        </p>
    </div>
</section>

<div class="container py-5">
    <div class="row g-3 g-md-4">
        <?php foreach ($products as $product): ?>
            <div class="col-lg-3 col-md-4 col-6 product-grid-col">
                <?php require __DIR__ . '/../components/product-card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
