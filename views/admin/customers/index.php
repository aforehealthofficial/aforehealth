<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            Registered Customers <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill">Total: <?= $total ?? count($customers) ?></span>
        </h4>
        <p class="text-muted small m-0">Customer accounts, order frequencies, and address records</p>
    </div>
    <form method="GET" action="<?= url('admin/customers') ?>" class="d-flex gap-2">
        <input type="hidden" name="segment" value="<?= e($currentSegment ?? 'all') ?>">
        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search customer name or mobile..." value="<?= e($search) ?>" style="width: 240px;">
        <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
            <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20 per page</option>
            <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30 per page</option>
            <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50 per page</option>
            <option value="100" <?= ($perPage ?? 20) == 100 ? 'selected' : '' ?>>100 per page</option>
        </select>
        <button class="btn btn-outline-dark btn-sm" type="submit">Filter</button>
    </form>
</div>

<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th>Customer Name</th>
                    <th>Mobile (Login ID)</th>
                    <th>Email</th>
                    <th>Gender</th>
                    <th>Orders Placed</th>
                    <th>Total Spent</th>
                    <th>Joined Date</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No customers found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $idx => $c): ?>
                        <tr>
                            <td class="text-center text-muted fw-bold small"><?= (($page ?? 1) - 1) * ($perPage ?? 20) + $idx + 1 ?></td>
                            <td>
                                <strong><?= e($c['name']) ?></strong>
                            </td>
                            <td><code>+91 <?= e($c['mobile']) ?></code></td>
                            <td><small class="text-muted"><?= e($c['email'] ?: 'Not provided') ?></small></td>
                            <td><small class="text-muted"><?= ucfirst($c['gender'] ?: 'Female') ?></small></td>
                            <td><span class="badge bg-light text-dark"><?= (int)$c['orders_count'] ?> orders</span></td>
                            <td class="fw-bold text-primary">₹<?= number_format((float)$c['total_spent'], 0) ?></td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($c['created_at'])) ?></small></td>
                            <td class="text-end">
                                <a href="<?= url('admin/customers/' . $c['id']) ?>" class="btn btn-sm btn-outline-dark">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light rounded-bottom-4">
            <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> customers)</small>
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
