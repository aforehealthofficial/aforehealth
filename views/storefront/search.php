<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item active">Search</li>
        </ol>
    </nav>

    <!-- Search Header -->
    <div class="mb-4 pb-3 border-bottom">
        <h1 class="h3 fw-bold text-dark mb-2">Search Results for "<?= e($query) ?>"</h1>
        <p class="text-muted small m-0">Found <strong><?= $total ?></strong> products matching your query</p>
    </div>

    <!-- Product Grid or Empty State -->
    <?php if (empty($products)): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm p-5 my-4">
            <div class="display-1 text-muted mb-3">🔍</div>
            <h3 class="fw-bold text-dark">No products found for "<?= e($query) ?>"</h3>
            <p class="text-muted">Check your spelling or explore popular categories like Period Panties or Combos.</p>
            <div class="d-flex justify-content-center gap-2 mt-3">
                <a href="<?= url('category/period-panties') ?>" class="btn btn-outline-primary">Period Panties</a>
                <a href="<?= url('combos') ?>" class="btn btn-outline-primary">Combos &amp; Bundles</a>
                <a href="<?= url('shop') ?>" class="btn btn-afh-primary">Browse All Shop</a>
            </div>
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
