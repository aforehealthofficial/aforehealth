<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            Flash Sales &amp; Promotional Events <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill">Total: <?= count($sales) ?></span>
        </h4>
        <small class="text-muted">Create time-limited promotions with countdown timers, server-side expiry validation, and product bundles.</small>
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
        <button class="btn btn-afh-primary btn-sm fw-bold text-nowrap" type="button" data-bs-toggle="modal" data-bs-target="#createFlashSaleModal">
            <i class="bi bi-lightning-charge-fill me-1"></i> New Flash Sale
        </button>
    </div>
</div>

<!-- Flash Sales Table -->
<div class="admin-table-card mb-4">
    <div class="table-responsive">
        <table class="table admin-table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th>Event Title</th>
                    <th>Discount</th>
                    <th>Products Included</th>
                    <th>Start Date / Time</th>
                    <th>End Date / Time</th>
                    <th>Live Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No flash sales scheduled.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $now = date('Y-m-d H:i:s');
                    foreach ($sales as $idx => $fs): 
                        $isOngoing = ($fs['status'] === 'active' && $fs['start_at'] <= $now && $fs['end_at'] >= $now);
                        $isExpired = ($fs['end_at'] < $now);
                        $isUpcoming = ($fs['start_at'] > $now);
                    ?>
                        <tr>
                            <td class="text-center text-muted fw-bold small"><?= $idx + 1 ?></td>
                            <td>
                                <strong class="text-dark fs-6"><?= e($fs['title']) ?></strong>
                                <small class="text-muted d-block"><?= e($fs['banner_text']) ?></small>
                            </td>
                            <td><span class="badge bg-danger fs-6"><?= (int)$fs['discount_percent'] ?>% OFF</span></td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= (int)($fs['product_count'] ?? 0) ?> Products</span>
                            </td>
                            <td><small><?= date('d M Y, h:i A', strtotime($fs['start_at'])) ?></small></td>
                            <td><small><?= date('d M Y, h:i A', strtotime($fs['end_at'])) ?></small></td>
                            <td>
                                <?php if ($isOngoing): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-broadcast me-1"></i> LIVE NOW</span>
                                <?php elseif ($isUpcoming): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-clock me-1"></i> Upcoming</span>
                                <?php elseif ($isExpired): ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Expired</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Disabled</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <form action="<?= url('admin/flash-sales/toggle') ?>" method="POST" class="d-inline form-toggle-confirm" data-confirm-title="Toggle Flash Sale?" data-confirm-msg="Are you sure you want to <?= $fs['status'] === 'active' ? 'disable' : 'enable' ?> flash sale '<?= e($fs['title']) ?>'?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $fs['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Toggle Status">
                                            <?= $fs['status'] === 'active' ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                    <form action="<?= url('admin/flash-sales/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Flash Sale?" data-confirm-msg="Are you sure you want to permanently delete flash sale '<?= e($fs['title']) ?>'?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $fs['id'] ?>">
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
            <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> flash sales)</small>
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

<!-- Modal: Create Flash Sale -->
<div class="modal fade" id="createFlashSaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Create Flash Sale Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('admin/flash-sales/store') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-dark">Sale Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Weekend Comfort Sale" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Discount (%) <span class="text-danger">*</span></label>
                            <input type="number" name="discount_percent" class="form-control" value="20" min="1" max="90" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Storefront Banner Catchphrase</label>
                        <input type="text" name="banner_text" class="form-control" placeholder="e.g. Extra 20% OFF — Limited Time Period Comfort Flash Deal" value="Limited Time Period Comfort Flash Deal">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Starts At (Asia/Kolkata) <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="start_at" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Ends At (Asia/Kolkata) <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="end_at" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime('+3 days')) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Select Participating Products <span class="text-danger">*</span></label>
                        <div class="p-3 bg-light rounded-3 overflow-auto" style="max-height: 200px;">
                            <div class="row g-2">
                                <?php foreach ($products as $p): ?>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="product_ids[]" value="<?= $p['id'] ?>" id="p_<?= $p['id'] ?>">
                                            <label class="form-check-label small text-dark" for="p_<?= $p['id'] ?>">
                                                <?= e($p['name']) ?> (₹<?= number_format($p['price'], 0) ?>)
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-afh-primary fw-bold px-3">Schedule Flash Sale</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
