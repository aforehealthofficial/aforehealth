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

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 pb-2 border-bottom">
        <div class="small text-muted">Showing <strong><?= count($products) ?></strong> of <strong><?= $total ?></strong> combo packs</div>
        
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="small text-muted text-nowrap d-none d-sm-inline" for="combosSort">Sort:</label>
            <select name="sort" id="combosSort" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="popularity" <?= ($_GET['sort'] ?? '') === 'popularity' ? 'selected' : '' ?>>Popularity</option>
                <option value="price_low" <?= ($_GET['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                <option value="price_high" <?= ($_GET['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                <option value="rating" <?= ($_GET['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
            </select>

            <label class="small text-muted text-nowrap d-none d-sm-inline" for="combosPerPage">Show:</label>
            <select name="per_page" id="combosPerPage" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20 per page</option>
                <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30 per page</option>
                <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50 per page</option>
            </select>
        </form>
    </div>

    <div class="row g-3 g-md-4">
        <?php foreach ($products as $product): ?>
            <div class="col-lg-3 col-md-4 col-6 product-grid-col">
                <?php require __DIR__ . '/../components/product-card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 1) > 1): ?>
        <nav aria-label="Combos pagination" class="mt-5 d-flex justify-content-center">
            <ul class="pagination pagination-sm m-0">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">« Prev</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next »</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
