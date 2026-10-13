<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            Bundle &amp; Kit Builder <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill">Total: <?= count($bundles) ?></span>
        </h4>
        <small class="text-muted">Configure custom bundles like "Build Your Period Kit" with multi-step item selections and server-side bundle discounts.</small>
    </div>
    <div class="d-flex align-items-center gap-3">
        <form method="GET" class="d-flex align-items-center gap-2 m-0">
            <label class="small text-muted text-nowrap m-0">Show:</label>
            <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="10" <?= ($perPage ?? 20) == 10 ? 'selected' : '' ?>>10</option>
                <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20</option>
                <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30</option>
                <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50</option>
            </select>
        </form>
        <a href="<?= url('admin/bundles/create') ?>" class="btn btn-afh-primary btn-sm fw-bold text-nowrap">
            <i class="bi bi-plus-lg me-1"></i> Create New Bundle
        </a>
    </div>
</div>

<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th>ID</th>
                    <th>Bundle Name</th>
                    <th>Categories Included</th>
                    <th>Min. Items</th>
                    <th>Bundle Discount</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bundles)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No custom bundles found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bundles as $idx => $b): ?>
                        <tr>
                            <td class="text-center text-muted fw-bold small"><?= $idx + 1 ?></td>
                            <td><strong>#<?= $b['id'] ?></strong></td>
                            <td>
                                <strong class="text-dark fs-6"><?= e($b['name']) ?></strong>
                                <small class="text-muted d-block">/build-your-kit (<?= e($b['slug']) ?>)</small>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php if (!empty($b['categories'])): ?>
                                        <?php foreach ($b['categories'] as $c): ?>
                                            <span class="badge bg-light text-dark border"><?= e($c['category_name']) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted small">None</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary border"><?= (int)$b['min_items'] ?> items min</span></td>
                            <td>
                                <strong class="text-success fs-6">
                                    <?= $b['discount_type'] === 'percentage' ? (int)$b['discount_value'] . '% OFF' : '₹' . (int)$b['discount_value'] . ' OFF' ?>
                                </strong>
                            </td>
                            <td>
                                <span class="badge bg-<?= $b['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($b['status']) ?>
                                </span>
                            </td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($b['created_at'])) ?></small></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?= url('build-your-kit') ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Preview Front-end">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <form action="<?= url('admin/bundles/toggle') ?>" method="POST" class="d-inline form-toggle-confirm" data-confirm-title="Toggle Bundle Status?" data-confirm-msg="Are you sure you want to <?= $b['status'] === 'active' ? 'disable' : 'enable' ?> bundle '<?= e($b['name']) ?>'?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Toggle Status">
                                            <?= $b['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                    <form action="<?= url('admin/bundles/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Bundle?" data-confirm-msg="Are you sure you want to permanently delete bundle '<?= e($b['name']) ?>'?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
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
            <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> bundles)</small>
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
