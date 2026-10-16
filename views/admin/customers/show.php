<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('admin/customers') ?>" class="small text-muted text-decoration-none">← Back to Customers</a>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold m-0 text-dark"><?= e($customer['name']) ?></h4>
            <?php if (!empty($customer['whatsapp_opt_in'])): ?>
                <span class="badge bg-success-subtle text-success border border-success fw-bold">
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp Community Member
                </span>
            <?php endif; ?>
        </div>
        <small class="text-muted">+91 <?= e($customer['mobile']) ?> • <?= e($customer['email'] ?: 'No email') ?></small>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Total Orders</div>
            <div class="h4 fw-extrabold text-primary m-0"><?= (int)$stats['total_orders'] ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Total Spent</div>
            <div class="h4 fw-extrabold text-dark m-0">₹<?= number_format($stats['total_spent'], 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Average Order (AOV)</div>
            <div class="h4 fw-extrabold text-success m-0">₹<?= number_format($stats['aov'], 0) ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
            <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Customer Since</div>
            <div class="small fw-bold text-dark m-0 mt-1"><?= date('d M Y', strtotime($customer['created_at'])) ?></div>
            <small class="text-muted" style="font-size: 0.7rem;">First: <?= $stats['first_order_date'] ? date('d M Y', strtotime($stats['first_order_date'])) : 'None' ?></small>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Customer Orders -->
    <div class="col-lg-8">
        <div class="admin-table-card mb-4">
            <div class="p-3 border-bottom">
                <h6 class="fw-bold m-0">Orders History (<?= count($orders) ?>)</h6>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">#</th>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No orders placed by this customer.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $idx => $ord): ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold small"><?= $idx + 1 ?></td>
                                    <td><strong><?= e($ord['order_number']) ?></strong></td>
                                    <td><?= date('d M Y', strtotime($ord['created_at'])) ?></td>
                                    <td class="fw-bold">₹<?= number_format($ord['grand_total'], 0) ?></td>
                                    <td><span class="badge bg-light text-dark"><?= strtoupper($ord['payment_method']) ?></span></td>
                                    <td><span class="status-pill status-<?= strtolower($ord['order_status']) ?>"><?= e($ord['order_status']) ?></span></td>
                                    <td class="text-end">
                                        <a href="<?= url('admin/orders/' . $ord['order_number']) ?>" class="btn btn-sm btn-outline-dark">Manage</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Customer Reviews -->
        <div class="admin-table-card">
            <div class="p-3 border-bottom">
                <h6 class="fw-bold m-0">Customer Reviews &amp; Feedback (<?= count($reviews) ?>)</h6>
            </div>
            <?php if (empty($reviews)): ?>
                <div class="p-4 text-center text-muted small">No product reviews submitted by this customer yet.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($reviews as $rev): ?>
                        <div class="list-group-item p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-dark small"><?= e($rev['product_name']) ?></strong>
                                <div>
                                    <span class="text-warning small">
                                        <?= str_repeat('★', (int)$rev['rating']) ?><?= str_repeat('☆', 5 - (int)$rev['rating']) ?>
                                    </span>
                                    <span class="badge bg-<?= $rev['status'] === 'approved' ? 'success' : 'warning' ?> ms-2"><?= ucfirst($rev['status']) ?></span>
                                </div>
                            </div>
                            <p class="small text-muted m-0"><?= e($rev['comment']) ?></p>
                            <small class="text-muted" style="font-size: 0.72rem;"><?= date('d M Y', strtotime($rev['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Saved Addresses -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="fw-bold mb-3 border-bottom pb-2">Saved Addresses (<?= count($addresses) ?>)</h6>
            <?php if (empty($addresses)): ?>
                <div class="text-muted small">No saved addresses on file.</div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($addresses as $addr): ?>
                        <div class="p-3 rounded-3 bg-light border small">
                            <strong><?= e($addr['full_name']) ?></strong> <span class="badge bg-secondary text-dark"><?= e($addr['address_type']) ?></span><br>
                            <?= e($addr['house_flat']) ?>, <?= e($addr['area']) ?><br>
                            <?= e($addr['city']) ?>, <?= e($addr['state']) ?> - <?= e($addr['pincode']) ?><br>
                            Phone: <?= e($addr['mobile']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
