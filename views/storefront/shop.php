<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Shop All</li>
        </ol>
    </nav>

    <!-- Header & Sort Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
        <div>
            <h1 class="h2 fw-bold text-dark m-0">All Intimate &amp; Period Care</h1>
            <p class="text-muted small m-0">Showing <strong><?= count($products) ?></strong> of <strong><?= $total ?></strong> essentials</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Grid Layout Switcher (Desktop & Tablet) -->
            <div class="btn-group btn-group-sm d-none d-md-inline-flex me-2" role="group" aria-label="Product Grid Layout">
                <button type="button" class="btn btn-outline-secondary layout-toggle-btn" id="btnGrid2" onclick="setProductGridLayout(2)" title="2 in a row">
                    <i class="bi bi-pause-fill fs-6"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary layout-toggle-btn active" id="btnGrid3" onclick="setProductGridLayout(3)" title="3 in a row">
                    <i class="bi bi-grid-3x2-gap-fill fs-6"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary layout-toggle-btn" id="btnGrid4" onclick="setProductGridLayout(4)" title="4 in a row">
                    <i class="bi bi-grid-3x3-gap-fill fs-6"></i>
                </button>
            </div>

            <!-- Mobile Filters Trigger -->
            <button class="btn btn-outline-secondary d-lg-none d-flex align-items-center gap-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#filtersOffcanvas">
                <i class="bi bi-funnel"></i> Filters
            </button>

            <!-- Sort and Per Page dropdown -->
            <form method="GET" action="<?= url('shop') ?>" id="sortForm" class="d-flex align-items-center gap-2">
                <?php foreach ($filters as $k => $v): if ($k !== 'sort' && !empty($v)): ?>
                    <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                <?php endif; endforeach; ?>

                <label class="small text-muted text-nowrap d-none d-sm-inline" for="sortSelect">Sort:</label>
                <select name="sort" id="sortSelect" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="popularity" <?= ($filters['sort'] ?? '') === 'popularity' ? 'selected' : '' ?>>Popularity</option>
                    <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
                    <option value="price_low" <?= ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_high" <?= ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                    <option value="rating" <?= ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                    <option value="discount" <?= ($filters['sort'] ?? '') === 'discount' ? 'selected' : '' ?>>Highest Discount</option>
                </select>

                <label class="small text-muted text-nowrap d-none d-sm-inline" for="perPageSelect">Show:</label>
                <select name="per_page" id="perPageSelect" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20</option>
                    <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30</option>
                    <option value="48" <?= ($perPage ?? 20) == 48 ? 'selected' : '' ?>>48</option>
                </select>
            </form>
        </div>
    </div>

    <div class="row g-4">
        
        <!-- Desktop Filters Sidebar -->
        <div class="col-lg-3 d-none d-lg-block">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 130px; background: #FFFFFF;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold m-0"><i class="bi bi-funnel me-1"></i> Filters</h5>
                    <a href="<?= url('shop') ?>" class="small text-muted text-decoration-underline">Clear All</a>
                </div>

                <form method="GET" action="<?= url('shop') ?>">
                    <!-- Hidden sort -->
                    <input type="hidden" name="sort" value="<?= e($filters['sort'] ?? 'popularity') ?>">

                    <!-- Category Filter -->
                    <div class="mb-4">
                        <label class="fw-bold small text-dark text-uppercase mb-2">Category</label>
                        <div class="d-flex flex-column gap-1">
                            <label class="form-check-label small d-flex justify-content-between align-items-center py-1">
                                <span>
                                    <input type="radio" name="category" value="" onchange="this.form.submit()" <?= empty($filters['category']) ? 'checked' : '' ?> class="form-check-input me-2">
                                    All Categories
                                </span>
                            </label>
                            <?php foreach ($categories as $cat): ?>
                                <label class="form-check-label small d-flex justify-content-between align-items-center py-1">
                                    <span>
                                        <input type="radio" name="category" value="<?= $cat['id'] ?>" onchange="this.form.submit()" <?= ($filters['category'] ?? '') == $cat['id'] ? 'checked' : '' ?> class="form-check-input me-2">
                                        <?= e($cat['name']) ?>
                                    </span>
                                    <span class="text-muted" style="font-size: 0.75rem;">(<?= (int)$cat['product_count'] ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Size Filter -->
                    <div class="mb-4 border-top pt-3">
                        <label class="fw-bold small text-dark text-uppercase mb-2">Size</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'] as $sz): ?>
                                <button type="submit" name="size" value="<?= $sz ?>" class="btn btn-sm <?= ($filters['size'] ?? '') === $sz ? 'btn-primary' : 'btn-outline-secondary' ?>" style="min-width: 42px;">
                                    <?= $sz ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Price Filter -->
                    <div class="mb-4 border-top pt-3">
                        <label class="fw-bold small text-dark text-uppercase mb-2">Price Range (₹)</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="number" name="min_price" class="form-control form-control-sm" placeholder="Min" value="<?= e($filters['min_price'] ?? '') ?>">
                            <span>-</span>
                            <input type="number" name="max_price" class="form-control form-control-sm" placeholder="Max" value="<?= e($filters['max_price'] ?? '') ?>">
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-dark w-100">Apply Price</button>
                    </div>

                    <!-- Rating Filter -->
                    <div class="mb-4 border-top pt-3">
                        <label class="fw-bold small text-dark text-uppercase mb-2">Minimum Rating</label>
                        <div class="d-flex flex-column gap-1">
                            <label class="small py-1">
                                <input type="radio" name="rating" value="4.8" onchange="this.form.submit()" <?= ($filters['rating'] ?? '') == '4.8' ? 'checked' : '' ?> class="form-check-input me-2">
                                <span class="text-warning">★★★★★</span> 4.8 &amp; above
                            </label>
                            <label class="small py-1">
                                <input type="radio" name="rating" value="4.5" onchange="this.form.submit()" <?= ($filters['rating'] ?? '') == '4.5' ? 'checked' : '' ?> class="form-check-input me-2">
                                <span class="text-warning">★★★★☆</span> 4.5 &amp; above
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Listing Grid -->
        <div class="col-lg-9">
            <?php if (empty($products)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm p-5">
                    <div class="display-1 text-muted mb-3">🔍</div>
                    <h3 class="fw-bold text-dark">No Products Found</h3>
                    <p class="text-muted">No items matched your current filter criteria. Try resetting your filters.</p>
                    <a href="<?= url('shop') ?>" class="btn btn-afh-primary mt-2">Clear All Filters</a>
                </div>
            <?php else: ?>
                <div class="row g-3 g-md-4" id="productGridContainer">
                    <?php foreach ($products as $product): ?>
                        <div class="product-grid-col col-6 col-md-6 col-lg-4">
                            <?php require __DIR__ . '/../components/product-card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-5 d-flex justify-content-center">
                        <ul class="pagination">
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

    </div>
</div>

<!-- Mobile Filters Offcanvas -->
<div class="offcanvas offcanvas-bottom rounded-top-4" tabindex="-1" id="filtersOffcanvas" style="height: 75vh;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold">Filter Products</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-4">
        <form method="GET" action="<?= url('shop') ?>">
            <div class="mb-3">
                <label class="fw-bold small">Category</label>
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($filters['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="fw-bold small">Size</label>
                <select name="size" class="form-select">
                    <option value="">All Sizes</option>
                    <?php foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'] as $sz): ?>
                        <option value="<?= $sz ?>" <?= ($filters['size'] ?? '') === $sz ? 'selected' : '' ?>><?= $sz ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" class="btn btn-afh-primary">Apply Filters</button>
                <a href="<?= url('shop') ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<script>
function setProductGridLayout(cols) {
    const container = document.getElementById('productGridContainer');
    if (!container) return;
    const items = container.querySelectorAll('.product-grid-col');
    
    document.querySelectorAll('.layout-toggle-btn').forEach(b => {
        b.classList.remove('active', 'btn-primary');
        b.classList.add('btn-outline-secondary');
    });
    const activeBtn = document.getElementById('btnGrid' + cols);
    if (activeBtn) {
        activeBtn.classList.add('active', 'btn-primary');
        activeBtn.classList.remove('btn-outline-secondary');
    }

    items.forEach(el => {
        el.className = 'product-grid-col';
        if (cols === 4) {
            el.classList.add('col-6', 'col-md-4', 'col-lg-3');
        } else if (cols === 2) {
            el.classList.add('col-12', 'col-md-6', 'col-lg-6');
        } else {
            // default 3
            el.classList.add('col-6', 'col-md-6', 'col-lg-4');
        }
    });

    try { localStorage.setItem('afh_shop_grid', cols); } catch (e) {}
}

document.addEventListener('DOMContentLoaded', function() {
    try {
        const saved = localStorage.getItem('afh_shop_grid');
        if (saved) {
            setProductGridLayout(parseInt(saved));
        }
    } catch (e) {}
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
