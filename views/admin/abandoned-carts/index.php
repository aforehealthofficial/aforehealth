<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold m-0 text-dark"><i class="bi bi-cart-x-fill text-danger me-2"></i> Abandoned Cart Recovery</h4>
        <small class="text-muted">Track incomplete checkout drop-offs and re-engage customers via WhatsApp recovery links</small>
    </div>
    <div class="d-flex gap-2">
        <span class="badge bg-success-subtle text-success border px-3 py-2 fs-6 rounded-pill">
            <i class="bi bi-tag-fill me-1"></i> Auto-Code: <strong>RECOVER10</strong> (10% OFF)
        </span>
    </div>
</div>

<!-- 1. Recovery Metrics KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-danger-subtle text-danger p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                    <i class="bi bi-cart-x"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Abandoned Carts</div>
                    <div class="h3 fw-bold text-dark m-0"><?= number_format($totalCarts) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-warning-subtle text-warning p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                    <i class="bi bi-currency-rupee"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Recoverable Pipeline</div>
                    <div class="h3 fw-bold text-dark m-0">₹<?= number_format($totalRevenue, 0) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 bg-success-subtle text-success p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 58px; height: 58px;">
                    <i class="bi bi-whatsapp"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">With WhatsApp Contact</div>
                    <div class="h3 fw-bold text-dark m-0"><?= number_format($cartsWithContact) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Abandoned Carts Table -->
<div class="admin-table-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white">
        <h5 class="fw-bold m-0 text-dark">
            Incomplete Orders &amp; Drops <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill"><?= $totalCarts ?></span>
        </h5>
        <small class="text-muted">Click WhatsApp button to send tailored recovery offer</small>
    </div>
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Customer / Lead</th>
                    <th>Mobile &amp; Email</th>
                    <th>Items in Cart</th>
                    <th>Value</th>
                    <th>Last Active</th>
                    <th class="text-end">Recovery Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($carts)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-bag-check fs-1 d-block mb-2 text-success"></i>
                            <p class="mb-1 fw-bold">No abandoned carts right now!</p>
                            <small>All recent customer checkouts have successfully completed.</small>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($carts as $idx => $c): ?>
                        <tr>
                            <td class="text-muted fw-bold small"><?= $idx + 1 ?></td>
                            <td>
                                <?php if (!empty($c['display_name'])): ?>
                                    <strong class="text-dark d-block"><?= e($c['display_name']) ?></strong>
                                    <?php if (!empty($c['user_id'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.65rem;">Registered User</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">Guest Lead</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Anonymous Visitor</span>
                                    <div class="small text-muted font-monospace" style="font-size: 0.7rem;">Sess: <?= substr($c['session_id'], 0, 10) ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($c['display_mobile'])): ?>
                                    <div class="fw-bold text-dark"><i class="bi bi-telephone text-primary me-1"></i>+91 <?= e($c['display_mobile']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>

                                <?php if (!empty($c['display_email'])): ?>
                                    <div class="text-muted small text-truncate" style="max-width: 180px;"><i class="bi bi-envelope me-1"></i><?= e($c['display_email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border mb-1"><?= (int)$c['item_count'] ?> item(s)</span>
                                <div class="small text-muted text-truncate" style="max-width: 280px;" title="<?= e($c['items_summary']) ?>">
                                    <?= e($c['items_summary']) ?>
                                </div>
                            </td>
                            <td>
                                <strong class="text-dark fs-6">₹<?= number_format((float)$c['cart_value'], 0) ?></strong>
                            </td>
                            <td>
                                <div class="small text-dark fw-semibold"><?= date('d M, h:i A', strtotime($c['updated_at'])) ?></div>
                                <div class="text-muted" style="font-size: 0.725rem;"><?= time_ago(strtotime($c['updated_at'])) ?></div>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <?php if (!empty($c['whatsapp_url'])): ?>
                                        <a href="<?= e($c['whatsapp_url']) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" title="Send WhatsApp Recovery Offer">
                                            <i class="bi bi-whatsapp"></i> WhatsApp
                                        </a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" onclick="copyRecoveryLink('<?= e($c['recovery_url']) ?>')" title="Copy Recovery Link">
                                        <i class="bi bi-link-45deg"></i>
                                    </button>
                                    <form action="<?= url('admin/abandoned-carts/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Cart?" data-confirm-msg="Are you sure you want to remove this cart record?">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Delete Cart"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function copyRecoveryLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        showToast('Recovery link copied with auto-applied RECOVER10 coupon!', 'success');
    }).catch(() => {
        prompt('Copy recovery link:', url);
    });
}
</script>

<?php require __DIR__ . '/../layout-footer.php'; ?>
