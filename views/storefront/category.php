<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- Category Hero Banner -->
<section class="py-4 py-lg-5" style="background: linear-gradient(135deg, #FFF9FA 0%, #F5E6EE 100%); border-bottom: 1px solid #EEDCE6;">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('shop') ?>">Shop</a></li>
                <li class="breadcrumb-item active"><?= e($category['name']) ?></li>
            </ol>
        </nav>
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold text-dark mb-2"><?= e($category['name']) ?></h1>
                <p class="lead text-muted mb-0" style="font-size: 1.05rem;">
                    <?= e($category['description']) ?>
                </p>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">
    <!-- Subcategory Pills if present -->
    <?php if (!empty($category['subcategories'])): ?>
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="<?= url('category/' . $category['slug']) ?>" class="btn btn-sm <?= empty($filters['subcategory']) ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">
                All <?= e($category['name']) ?>
            </a>
            <?php foreach ($category['subcategories'] as $sc): ?>
                <a href="<?= url('category/' . $category['slug'] . '?subcategory=' . $sc['slug']) ?>" class="btn btn-sm <?= ($filters['subcategory'] ?? '') === $sc['slug'] ? 'btn-primary' : 'btn-outline-secondary' ?> rounded-pill px-3">
                    <?= e($sc['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div class="small text-muted">Showing <strong><?= count($products) ?></strong> of <strong><?= $total ?></strong> products</div>
        
        <form method="GET" class="d-flex align-items-center gap-2">
            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="popularity" <?= ($filters['sort'] ?? '') === 'popularity' ? 'selected' : '' ?>>Popularity</option>
                <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                <option value="price_low" <?= ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_high" <?= ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
            </select>
        </form>
    </div>

    <!-- Product Grid -->
    <div class="row g-3 g-md-4">
        <?php foreach ($products as $product): ?>
            <div class="col-lg-3 col-md-4 col-6 product-grid-col">
                <?php require __DIR__ . '/../components/product-card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
