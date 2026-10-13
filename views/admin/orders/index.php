<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            Store Orders <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill">Total: <?= $total ?? count($orders) ?></span>
        </h4>
        <p class="text-muted small m-0">View, fulfill, track, and generate invoices for customer orders</p>
    </div>

    <!-- Filters & Search Form -->
    <form method="GET" action="<?= url('admin/orders') ?>" class="d-flex flex-wrap gap-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Statuses</option>
            <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
            <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Processing</option>
            <option value="shipped" <?= $statusFilter === 'shipped' ? 'selected' : '' ?>>Shipped</option>
            <option value="delivered" <?= $statusFilter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
            <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>

        <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
            <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20 per page</option>
            <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30 per page</option>
            <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50 per page</option>
            <option value="100" <?= ($perPage ?? 20) == 100 ? 'selected' : '' ?>>100 per page</option>
        </select>

        <div class="input-group input-group-sm" style="width: 240px;">
            <input type="text" name="q" class="form-control" placeholder="Search order/mobile..." value="<?= e($search) ?>">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th>Order Number</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th>Courier</th>
                    <th style="width: 140px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No orders match the selected filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $idx => $ord): ?>
                        <tr>
                            <td class="text-center text-muted fw-bold small"><?= (($page ?? 1) - 1) * ($perPage ?? 20) + $idx + 1 ?></td>
                            <td>
                                <strong><?= e($ord['order_number']) ?></strong><br>
                                <small class="text-muted"><?= (int)$ord['item_count'] ?> item(s)</small>
                            </td>
                            <td>
                                <strong class="text-dark small d-block"><?= e($ord['customer_name']) ?></strong>
                                <small class="text-muted">+91 <?= e($ord['customer_mobile']) ?></small>
                            </td>
                            <td><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></td>
                            <td class="fw-bold">₹<?= number_format($ord['grand_total'], 0) ?></td>
                            <td>
                                <span class="badge bg-light text-dark"><?= strtoupper($ord['payment_method']) ?></span><br>
                                <small class="text-<?= $ord['payment_status'] === 'paid' ? 'success' : 'warning' ?> fw-semibold"><?= strtoupper($ord['payment_status']) ?></small>
                            </td>
                            <td>
                                <span class="status-pill status-<?= strtolower($ord['order_status']) ?>"><?= e($ord['order_status']) ?></span>
                            </td>
                            <td>
                                <small class="text-muted"><?= e($ord['courier_name'] ?: 'Pending dispatch') ?></small>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="<?= url('admin/orders/' . $ord['order_number']) ?>" class="btn btn-sm btn-outline-dark fw-bold">Manage</a>
                                    <a href="<?= url('order/invoice/' . $ord['order_number']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Invoice"><i class="bi bi-printer"></i></a>
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
            <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> orders)</small>
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
