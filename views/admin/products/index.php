<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            Catalog Products <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill">Total: <?= $total ?? count($products) ?></span>
        </h4>
        <p class="text-muted small m-0">Manage all AFOREHEALTH products, variants, and stock</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/products/create') ?>" class="btn btn-afh-primary fw-bold">
            <i class="bi bi-plus-lg me-1"></i> Add New Product
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form method="GET" action="<?= url('admin/products') ?>" class="row g-2 align-items-center">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by product name or SKU..." value="<?= e($search) ?>">
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $currentCategory == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20 per page</option>
                <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30 per page</option>
                <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50 per page</option>
                <option value="100" <?= ($perPage ?? 20) == 100 ? 'selected' : '' ?>>100 per page</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-dark btn-sm w-100">Filter</button>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th style="width: 50px;">Image</th>
                    <th>Product &amp; SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Sales Intelligence</th>
                    <th>Status</th>
                    <th style="width: 150px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No products found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $idx => $p): ?>
                        <tr>
                            <td class="text-center text-muted fw-bold small"><?= (($page ?? 1) - 1) * ($perPage ?? 20) + $idx + 1 ?></td>
                            <td>
                                <img src="<?= upload_url('products/' . ($p['primary_image'] ?: 'cat_period_panties.svg')) ?>" alt="" class="rounded" style="width: 44px; height: 44px; object-fit: cover;">
                            </td>
                            <td>
                                <strong class="d-block text-dark small"><?= e($p['name']) ?></strong>
                                <small class="text-muted">SKU: <code><?= e($p['sku']) ?></code> • <?= (int)$p['variant_count'] ?> variant(s)</small>
                            </td>
                            <td><span class="badge bg-light text-dark"><?= e($p['category_name']) ?></span></td>
                            <td>
                                <strong>₹<?= number_format($p['price'], 0) ?></strong>
                                <?php if ($p['mrp'] > $p['price']): ?>
                                    <small class="text-muted d-block text-decoration-line-through">₹<?= number_format($p['mrp'], 0) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['stock'] <= $p['low_stock_threshold']): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Low: <?= (int)$p['stock'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success"><?= (int)$p['stock'] ?> in stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="fw-bold text-dark"><?= (int)$p['units_sold'] ?> sold</span>
                                    <span class="text-muted">• ₹<?= number_format($p['total_revenue'], 0) ?></span>
                                </div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    <i class="bi bi-heart text-danger"></i> <?= (int)$p['wishlist_count'] ?> wishlists
                                    • <i class="bi bi-star text-warning"></i> <?= (int)$p['approved_reviews_count'] ?> reviews
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $p['status'] === 'published' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?= url('admin/products/edit/' . $p['id']) ?>" class="btn btn-sm btn-outline-dark" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <a href="<?= url('admin/products/duplicate/' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Duplicate"><i class="bi bi-copy"></i></a>
                                    <form action="<?= url('admin/products/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Product?" data-confirm-msg="Are you sure you want to delete product '<?= e($p['name']) ?>'? This will remove variants and stock associations.">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light rounded-bottom-4">
            <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> products)</small>
            <nav>
                <ul class="pagination pagination-sm m-0">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">«</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a></li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">»</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
