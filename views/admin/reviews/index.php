<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            Customer Review Moderation <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill">Total: <?= $counts['all'] ?? count($reviews) ?></span>
        </h4>
        <p class="text-muted small m-0">Review, verify, and approve customer submissions before they appear publicly</p>
    </div>
</div>

<!-- Tabs Filter -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-2">
    <ul class="nav nav-pills gap-2">
        <li class="nav-item">
            <a class="nav-link <?= $currentFilter === 'all' ? 'active' : '' ?>" href="<?= url('admin/reviews?status=all') ?>">
                All Reviews (<?= $counts['all'] ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentFilter === 'pending' ? 'active bg-warning text-dark' : 'text-warning' ?> fw-bold" href="<?= url('admin/reviews?status=pending') ?>">
                Pending Approval (<?= $counts['pending'] ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentFilter === 'approved' ? 'active bg-success' : 'text-success' ?>" href="<?= url('admin/reviews?status=approved') ?>">
                Approved (<?= $counts['approved'] ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentFilter === 'rejected' ? 'active bg-danger' : 'text-danger' ?>" href="<?= url('admin/reviews?status=rejected') ?>">
                Rejected (<?= $counts['rejected'] ?>)
            </a>
        </li>
        <li class="nav-item ms-auto">
            <form method="GET" action="<?= url('admin/reviews') ?>" class="d-flex align-items-center gap-1">
                <input type="hidden" name="status" value="<?= e($currentFilter) ?>">
                <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                    <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20 per page</option>
                    <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30 per page</option>
                    <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50 per page</option>
                    <option value="100" <?= ($perPage ?? 20) == 100 ? 'selected' : '' ?>>100 per page</option>
                </select>
            </form>
        </li>
    </ul>
</div>

<!-- Reviews Table -->
<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th style="width: 70px;">ID</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th style="width: 120px;">Rating</th>
                    <th>Review Content</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="width: 200px; text-align: right;">Moderation Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No reviews match this filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reviews as $idx => $rev): ?>
                        <tr class="<?= $rev['status'] === 'pending' ? 'table-warning-subtle' : '' ?>">
                            <td class="text-center text-muted fw-bold small"><?= (($page ?? 1) - 1) * ($perPage ?? 20) + $idx + 1 ?></td>
                            <td><strong>#<?= $rev['id'] ?></strong></td>
                            <td>
                                <strong class="d-block text-dark small"><?= e($rev['product_name']) ?></strong>
                                <a href="<?= url('product/' . $rev['product_slug']) ?>" target="_blank" class="small text-muted text-decoration-underline">View Product ↗</a>
                            </td>
                            <td>
                                <div class="fw-bold small"><?= e($rev['customer_name']) ?></div>
                                <small class="text-muted">+91 <?= e($rev['customer_mobile']) ?></small>
                            </td>
                            <td>
                                <div class="text-warning small"><?= str_repeat('★', (int)$rev['rating']) ?></div>
                                <small class="fw-bold"><?= (int)$rev['rating'] ?> of 5</small>
                            </td>
                            <td>
                                <strong class="d-block text-dark small mb-1"><?= e($rev['title']) ?></strong>
                                <p class="text-muted small m-0" style="max-width: 320px; line-height: 1.4;"><?= nl2br(e($rev['comment'])) ?></p>
                            </td>
                            <td>
                                <span class="status-pill status-<?= $rev['status'] ?>">
                                    <?= strtoupper($rev['status']) ?>
                                </span>
                            </td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($rev['created_at'])) ?></small></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <?php if ($rev['status'] !== 'approved'): ?>
                                        <form action="<?= url('admin/reviews/update-status') ?>" method="POST" class="d-inline form-toggle-confirm" data-confirm-title="Approve Review?" data-confirm-msg="Are you sure you want to approve this review? It will become visible on the public product page.">
                                            <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success fw-bold" title="Approve Review">Approve</button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($rev['status'] !== 'rejected'): ?>
                                        <form action="<?= url('admin/reviews/update-status') ?>" method="POST" class="d-inline form-toggle-confirm" data-confirm-title="Reject Review?" data-confirm-msg="Are you sure you want to reject this review? It will remain hidden from the storefront.">
                                            <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Reject Review">Reject</button>
                                        </form>
                                    <?php endif; ?>

                                    <form action="<?= url('admin/reviews/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Review?" data-confirm-msg="Are you sure you want to permanently delete this customer review?">
                                        <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Review"><i class="bi bi-trash"></i></button>
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
            <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> reviews)</small>
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
