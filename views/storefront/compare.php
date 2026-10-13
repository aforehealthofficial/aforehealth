<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="py-5" style="background-color: var(--afh-bg);">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Compare Products</li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Compare Products</h1>
                <p class="text-muted small mb-0">Evaluate comfort, absorption levels, fabric, and sizing side-by-side.</p>
            </div>
            <?php if (!empty($products)): ?>
                <div>
                    <a href="<?= url('compare/clear') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-confirm="Are you sure you want to clear all compared products?" data-confirm-title="Clear Comparison?" data-confirm-type="delete">
                        <i class="bi bi-trash3 me-1"></i> Clear Comparison (<?= count($products) ?>)
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($products)): ?>
            <div class="card border-0 shadow-sm rounded-4 text-center py-5 px-3 bg-white">
                <div class="py-4">
                    <div class="rounded-circle bg-light d-inline-flex p-4 mb-3 text-muted">
                        <i class="bi bi-layout-split fs-1 text-primary"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">No Products in Comparison</h4>
                    <p class="text-muted small mx-auto mb-4" style="max-width: 450px;">
                        Explore our leakproof period panties, everyday breathable briefs, and intimate care essentials, then click <strong>"+ Compare"</strong> to view them side-by-side.
                    </p>
                    <a href="<?= url('shop') ?>" class="btn btn-afh-primary rounded-pill px-5 fw-bold">Explore All Products</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 compare-table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 200px; min-width: 180px;" class="p-3 text-muted small fw-bold text-uppercase">Attributes</th>
                                <?php foreach ($products as $p): ?>
                                    <th style="min-width: 250px; max-width: 280px;" class="p-3 text-center bg-white position-relative">
                                        <form action="<?= url('compare/remove') ?>" method="POST" class="position-absolute top-0 end-0 m-2 form-delete-confirm" data-confirm-title="Remove from Comparison?" data-confirm-msg="Remove '<?= e($p['name']) ?>' from comparison?">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-light rounded-circle border shadow-sm p-1" title="Remove">
                                                <i class="bi bi-x fs-6"></i>
                                            </button>
                                        </form>

                                        <img src="<?= upload_url('products/' . ($p['primary_image'] ?? 'placeholder.svg')) ?>" 
                                             class="rounded-3 mb-2 border p-1" style="width: 120px; height: 120px; object-fit: contain;" 
                                             alt="<?= e($p['name']) ?>">

                                        <h6 class="fw-bold mb-1">
                                            <a href="<?= url('product/' . $p['slug']) ?>" class="text-dark text-decoration-none">
                                                <?= e($p['name']) ?>
                                            </a>
                                        </h6>
                                        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                            <span class="fw-bold text-primary fs-5">₹<?= number_format($p['price'], 0) ?></span>
                                            <?php if ($p['mrp'] > $p['price']): ?>
                                                <span class="text-muted text-decoration-line-through small">₹<?= number_format($p['mrp'], 0) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?= url('product/' . $p['slug']) ?>" class="btn btn-afh-primary btn-sm w-100 rounded-pill fw-bold">
                                            View Details
                                        </a>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-muted small p-3">Customer Rating</td>
                                <?php foreach ($products as $p): ?>
                                    <td class="text-center p-3">
                                        <div class="d-inline-flex align-items-center gap-1 bg-success text-white px-2 py-1 rounded-pill small fw-bold">
                                            <span><?= number_format($p['rating_avg'], 1) ?></span> <i class="bi bi-star-fill" style="font-size: 0.75rem;"></i>
                                        </div>
                                        <small class="text-muted d-block mt-1">(<?= (int)$p['rating_count'] ?> verified reviews)</small>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted small p-3">Category</td>
                                <?php foreach ($products as $p): ?>
                                    <td class="text-center p-3 small text-muted"><?= e($p['category_name']) ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted small p-3">Available Sizes</td>
                                <?php foreach ($products as $p): ?>
                                    <td class="text-center p-3">
                                        <?php if (!empty($p['sizes'])): ?>
                                            <div class="d-flex flex-wrap justify-content-center gap-1">
                                                <?php foreach ($p['sizes'] as $sz): ?>
                                                    <span class="badge bg-light text-dark border"><?= e($sz) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">Standard / Free Size</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted small p-3">Fabric &amp; Material</td>
                                <?php foreach ($products as $p): ?>
                                    <td class="text-center p-3 small"><?= e($p['material'] ?? 'Organic Cotton Blend') ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted small p-3">Hygiene &amp; Washing</td>
                                <?php foreach ($products as $p): ?>
                                    <td class="text-center p-3 small text-muted"><?= e($p['care_instructions'] ?? 'Rinse in cold water, machine wash gentle, hang dry.') ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted small p-3">Return Eligibility</td>
                                <?php foreach ($products as $p): ?>
                                    <td class="text-center p-3 small">
                                        <span class="badge bg-light text-dark border">
                                            <i class="bi bi-shield-check text-success me-1"></i> <?= e($p['hygiene_notice'] ? 'Hygiene Protected' : '7-Day Returnable') ?>
                                        </span>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted small p-3">Availability</td>
                                <?php foreach ($products as $p): ?>
                                    <td class="text-center p-3">
                                        <?php if ($p['stock'] > 0): ?>
                                            <span class="badge bg-success-subtle text-success fw-bold px-3 py-1">In Stock</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger fw-bold px-3 py-1">Out of Stock</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
