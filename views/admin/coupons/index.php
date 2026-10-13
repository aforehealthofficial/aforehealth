<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="row g-4">
    <!-- Coupons List -->
    <div class="col-lg-8">
        <div class="admin-table-card">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                    Coupon &amp; Discount Codes <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill">Total: <?= count($coupons) ?></span>
                </h5>
                <form method="GET" class="d-flex align-items-center gap-2 m-0">
                    <label class="small text-muted text-nowrap m-0">Show:</label>
                    <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                        <option value="10" <?= ($perPage ?? 20) == 10 ? 'selected' : '' ?>>10</option>
                        <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20</option>
                        <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30</option>
                        <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50</option>
                    </select>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">#</th>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Discount</th>
                            <th>Min. Order</th>
                            <th>Usage</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($coupons)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">No coupons found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($coupons as $idx => $cp): ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold small"><?= $idx + 1 ?></td>
                                    <td><code class="fs-6 fw-bold text-primary"><?= e($cp['code']) ?></code></td>
                                    <td><small><?= e($cp['title']) ?></small></td>
                                    <td>
                                        <strong><?= $cp['discount_type'] === 'percentage' ? (int)$cp['discount_value'] . '%' : '₹' . (int)$cp['discount_value'] ?></strong>
                                        <?php if ($cp['max_discount_amount']): ?>
                                            <small class="text-muted d-block">(Max ₹<?= (int)$cp['max_discount_amount'] ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>₹<?= number_format($cp['min_order_amount'], 0) ?></td>
                                    <td><small class="text-muted"><?= (int)$cp['usage_count'] ?> / <?= (int)$cp['usage_limit'] ?></small></td>
                                    <td>
                                        <span class="badge bg-<?= $cp['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= ucfirst($cp['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <form action="<?= url('admin/coupons/toggle') ?>" method="POST" class="d-inline form-toggle-confirm" data-confirm-title="Toggle Coupon Status?" data-confirm-msg="Are you sure you want to <?= $cp['status'] === 'active' ? 'disable' : 'enable' ?> coupon '<?= e($cp['code']) ?>'?">
                                                <input type="hidden" name="id" value="<?= $cp['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Toggle Active">
                                                    <?= $cp['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                                </button>
                                            </form>
                                            <form action="<?= url('admin/coupons/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Coupon?" data-confirm-msg="Are you sure you want to permanently delete coupon '<?= e($cp['code']) ?>'?">
                                                <input type="hidden" name="id" value="<?= $cp['id'] ?>">
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
                    <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> coupons)</small>
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
    </div>

    <!-- Create Coupon Box -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold mb-3 border-bottom pb-2">Create New Coupon</h5>
            <form action="<?= url('admin/coupons/store') ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Coupon Code *</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. FESTIVE25" style="text-transform: uppercase;" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Promo Title *</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. 25% Off Diwali Special" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Discount Type</label>
                        <select name="discount_type" class="form-select">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Flat (₹)</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Value *</label>
                        <input type="number" step="0.5" name="discount_value" class="form-control" placeholder="10" required>
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Min Order (₹)</label>
                        <input type="number" name="min_order_amount" class="form-control" placeholder="499" value="499">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Max Discount (₹)</label>
                        <input type="number" name="max_discount_amount" class="form-control" placeholder="300">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control" value="<?= date('Y-12-31') ?>" required>
                </div>
                <button type="submit" class="btn btn-afh-primary w-100 fw-bold">Create Coupon</button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
