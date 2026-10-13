<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">My Account</a></li>
            <li class="breadcrumb-item active">Wishlist</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">My Wishlist (<?= count($products) ?>)</h1>
            <p class="text-muted small m-0">Your saved period and intimate comfort favorites</p>
        </div>
        <a href="<?= url('shop') ?>" class="btn btn-outline-primary btn-sm fw-bold">Explore More</a>
    </div>

    <?php if (empty($products)): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm p-4">
            <i class="bi bi-heart display-4 text-muted mb-2 d-block"></i>
            <h4 class="fw-bold">Your Wishlist is Empty</h4>
            <p class="text-muted small">Save items you love so you can easily review and purchase later.</p>
            <a href="<?= url('shop') ?>" class="btn btn-afh-primary btn-sm px-4">Browse Catalog</a>
        </div>
    <?php else: ?>
        <div class="row g-3 g-md-4">
            <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-4 col-6 product-grid-col">
                    <?php require __DIR__ . '/../components/product-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
