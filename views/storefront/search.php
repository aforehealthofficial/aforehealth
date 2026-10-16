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
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h3 fw-bold text-dark mb-1">Search Results for "<?= e($query) ?>"</h1>
            <p class="text-muted small m-0">Found <strong><?= $total ?></strong> products matching your query</p>
        </div>

        <?php if (!empty($products)): ?>
            <form method="GET" action="<?= url('search') ?>" class="d-flex align-items-center gap-2">
                <input type="hidden" name="q" value="<?= e($query) ?>">
                <label class="small text-muted text-nowrap" for="searchPerPage">Show:</label>
                <select name="per_page" id="searchPerPage" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20 per page</option>
                    <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30 per page</option>
                    <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50 per page</option>
                </select>
            </form>
        <?php endif; ?>
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

        <!-- Pagination -->
        <?php if (($totalPages ?? 1) > 1): ?>
            <nav aria-label="Search pagination" class="mt-5 d-flex justify-content-center">
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
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
