<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark">Pincode Serviceability</h4>
        <small class="text-muted">Manage serviceable Indian PIN codes, city/state mappings, COD availability, and delivery SLA.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/shipping/pincodes/export-csv') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
        <button class="btn btn-outline-primary btn-sm fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#importCard">
            <i class="bi bi-upload me-1"></i> Import CSV
        </button>
        <button class="btn btn-afh-primary btn-sm fw-bold" type="button" data-bs-toggle="modal" data-bs-target="#addPincodeModal">
            <i class="bi bi-plus-lg me-1"></i> Add Pincode
        </button>
    </div>
</div>

<!-- CSV Import Collapse -->
<div class="collapse mb-4" id="importCard">
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Bulk CSV Import</h6>
        <p class="small text-muted mb-3">Upload a CSV file containing: <code>pincode, city, state, delivery_days, cod_available, prepaid_available, shipping_charge</code></p>
        <form action="<?= url('admin/shipping/pincodes/import-csv') ?>" method="POST" enctype="multipart/form-data" class="d-flex flex-column flex-md-row gap-3 align-items-md-end">
            <?= csrf_field() ?>
            <div class="flex-grow-1">
                <label class="form-label small fw-bold text-dark mb-1">Select CSV File</label>
                <input type="file" name="csv_file" class="form-control form-control-sm" accept=".csv" required>
            </div>
            <div>
                <button type="submit" class="btn btn-sm btn-success fw-bold px-4">
                    <i class="bi bi-cloud-arrow-up me-1"></i> Upload &amp; Sync
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Filter Bar -->
<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
    <form method="GET" action="<?= url('admin/shipping/pincodes') ?>" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="q" class="form-control border-start-0" placeholder="Search 6-digit PIN, city or state..." value="<?= e($q) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="state" class="form-select form-select-sm">
                <option value="">All States / UTs</option>
                <?php foreach ($states as $st): ?>
                    <option value="<?= e($st) ?>" <?= $stateFilter === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="cod" class="form-select form-select-sm">
                <option value="">All Payment Modes</option>
                <option value="1" <?= $codFilter === '1' ? 'selected' : '' ?>>COD Available</option>
                <option value="0" <?= $codFilter === '0' ? 'selected' : '' ?>>Prepaid Only (No COD)</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20 per page</option>
                <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30 per page</option>
                <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50 per page</option>
                <option value="100" <?= ($perPage ?? 20) == 100 ? 'selected' : '' ?>>100 per page</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-dark fw-bold w-100">Filter</button>
            <?php if ($q || $stateFilter || $codFilter !== null): ?>
                <a href="<?= url('admin/shipping/pincodes') ?>" class="btn btn-sm btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Pincodes Table -->
<div class="admin-table-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            Serviceable PIN Codes <span class="badge bg-secondary-subtle text-dark rounded-pill">Total: <?= number_format($total) ?></span>
        </h6>
        <span class="small text-muted">Showing <strong><?= count($pincodes) ?></strong> on this page</span>
    </div>
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th>PIN Code</th>
                    <th>City / District</th>
                    <th>State</th>
                    <th>Est. Delivery</th>
                    <th>COD</th>
                    <th>Prepaid</th>
                    <th>Shipping Charge</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pincodes)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            No serviceable pincodes found matching your filter criteria.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pincodes as $idx => $pin): ?>
                        <tr>
                            <td class="text-center text-muted fw-bold small"><?= (($page ?? 1) - 1) * ($perPage ?? 20) + $idx + 1 ?></td>
                            <td><strong class="font-monospace fs-6 text-primary"><?= e($pin['pincode']) ?></strong></td>
                            <td class="fw-semibold text-dark"><?= e($pin['city']) ?></td>
                            <td class="small text-muted"><?= e($pin['state']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= (int)$pin['delivery_days'] ?> Days</span></td>
                            <td>
                                <?php if ($pin['cod_available']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">No</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($pin['prepaid_available']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <?= $pin['shipping_charge'] > 0 ? '₹' . number_format($pin['shipping_charge'], 0) : '<span class="text-success fw-bold">FREE</span>' ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $pin['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= ucfirst($pin['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <form action="<?= url('admin/shipping/pincodes/toggle') ?>" method="POST" class="d-inline form-toggle-confirm" data-confirm-title="Toggle Pincode Status?" data-confirm-msg="Are you sure you want to <?= $pin['status'] === 'active' ? 'disable' : 'enable' ?> pincode '<?= e($pin['pincode']) ?>'?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $pin['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Toggle Status">
                                            <?= $pin['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                    <form action="<?= url('admin/shipping/pincodes/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Pincode?" data-confirm-msg="Are you sure you want to permanently delete pincode '<?= e($pin['pincode']) ?>'?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $pin['id'] ?>">
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

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="p-3 border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">Page <?= $page ?> of <?= $totalPages ?></small>
            <div class="d-flex gap-1">
                <?php if ($page > 1): ?>
                    <a href="<?= url('admin/shipping/pincodes?page=' . ($page - 1) . '&q=' . urlencode($q) . '&state=' . urlencode($stateFilter) . '&cod=' . urlencode($codFilter ?? '')) ?>" class="btn btn-sm btn-outline-secondary">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?= url('admin/shipping/pincodes?page=' . ($page + 1) . '&q=' . urlencode($q) . '&state=' . urlencode($stateFilter) . '&cod=' . urlencode($codFilter ?? '')) ?>" class="btn btn-sm btn-outline-secondary">Next</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Add / Edit Pincode -->
<div class="modal fade" id="addPincodeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Add Serviceable Indian PIN Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('admin/shipping/pincodes/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">6-Digit PIN Code <span class="text-danger">*</span></label>
                        <input type="text" name="pincode" class="form-control" placeholder="e.g. 110001" pattern="^[1-9][0-9]{5}$" maxlength="6" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">City / District <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. New Delhi" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">State / UT <span class="text-danger">*</span></label>
                            <input type="text" name="state" class="form-control" placeholder="e.g. Delhi" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Est. Delivery SLA (Days)</label>
                            <input type="number" name="delivery_days" class="form-control" value="3" min="1" max="15" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Custom Shipping Fee (₹)</label>
                            <input type="number" name="shipping_charge" class="form-control" value="0.00" step="1" min="0">
                        </div>
                    </div>
                    <div class="d-flex gap-4 p-2 bg-light rounded-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="cod_available" id="codSwitch" checked>
                            <label class="form-check-label small fw-semibold" for="codSwitch">COD Available</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="prepaid_available" id="prepaidSwitch" checked>
                            <label class="form-check-label small fw-semibold" for="prepaidSwitch">Prepaid Available</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-afh-primary fw-bold px-3">Save PIN Code</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
