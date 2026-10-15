<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            Product Questions &amp; Answers (Q&amp;A) <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill">Total: <?= $counts['all'] ?? count($questions) ?></span>
        </h4>
        <p class="text-muted small m-0">Moderate customer questions, submit official answers, and approve for public storefront display.</p>
    </div>
</div>

<!-- Tabs Filter & Per Page -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-2">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <ul class="nav nav-pills gap-2 m-0">
            <li class="nav-item">
                <a class="nav-link <?= $currentStatus === 'all' ? 'active' : '' ?>" href="<?= url('admin/product-questions?status=all&per_page=' . ($perPage ?? 20)) ?>">
                    All Questions (<?= $counts['all'] ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentStatus === 'pending' ? 'active bg-warning text-dark' : 'text-warning' ?> fw-bold" href="<?= url('admin/product-questions?status=pending&per_page=' . ($perPage ?? 20)) ?>">
                    Pending Moderation (<?= $counts['pending'] ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentStatus === 'approved' ? 'active bg-success' : 'text-success' ?>" href="<?= url('admin/product-questions?status=approved&per_page=' . ($perPage ?? 20)) ?>">
                    Approved &amp; Published (<?= $counts['approved'] ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $currentStatus === 'rejected' ? 'active bg-danger' : 'text-danger' ?>" href="<?= url('admin/product-questions?status=rejected&per_page=' . ($perPage ?? 20)) ?>">
                    Rejected (<?= $counts['rejected'] ?>)
                </a>
            </li>
        </ul>
        <form method="GET" class="d-flex align-items-center gap-2 m-0 pe-2">
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
            <label class="small text-muted text-nowrap m-0">Show:</label>
            <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="10" <?= ($perPage ?? 20) == 10 ? 'selected' : '' ?>>10</option>
                <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20</option>
                <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30</option>
                <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50</option>
            </select>
        </form>
    </div>
</div>

<!-- Questions Table -->
<div class="admin-table-card">
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th style="width: 60px;">ID</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Question</th>
                    <th>Official Answer</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end" style="width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($questions)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No questions found matching this filter.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($questions as $idx => $q): ?>
                        <tr class="<?= $q['status'] === 'pending' ? 'table-warning-subtle' : '' ?>">
                            <td class="text-center text-muted fw-bold small"><?= (($page ?? 1) - 1) * ($perPage ?? 20) + $idx + 1 ?></td>
                            <td><strong>#<?= $q['id'] ?></strong></td>
                            <td>
                                <strong class="small text-dark"><?= e($q['product_name']) ?></strong>
                            </td>
                            <td>
                                <span class="small fw-semibold"><?= e($q['customer_name'] ?? 'Guest') ?></span>
                                <small class="text-muted d-block"><?= e($q['customer_mobile'] ?? '') ?></small>
                            </td>
                            <td style="max-width: 260px;">
                                <div class="small fw-semibold text-dark"><?= e($q['question']) ?></div>
                            </td>
                            <td style="max-width: 260px;">
                                <?php if (!empty($q['answer'])): ?>
                                    <div class="small text-muted bg-light p-2 rounded border"><?= e($q['answer']) ?></div>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">Unanswered</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($q['status'] === 'approved'): ?>
                                    <span class="badge bg-success">Approved</span>
                                <?php elseif ($q['status'] === 'pending'): ?>
                                    <span class="badge bg-warning text-dark">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($q['created_at'])) ?></small></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button class="btn btn-sm btn-outline-primary" type="button" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#answerModal<?= $q['id'] ?>" title="Answer">
                                        <i class="bi bi-chat-dots"></i> Answer
                                    </button>

                                    <?php if ($q['status'] !== 'approved'): ?>
                                        <form action="<?= url('admin/product-questions/update-status') ?>" method="POST" class="d-inline form-toggle-confirm" data-confirm-title="Approve Question?" data-confirm-msg="Are you sure you want to approve this question? It will be publicly published to the storefront.">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($q['status'] !== 'rejected'): ?>
                                        <form action="<?= url('admin/product-questions/update-status') ?>" method="POST" class="d-inline form-toggle-confirm" data-confirm-title="Reject Question?" data-confirm-msg="Are you sure you want to reject this question? It will not be visible on the storefront.">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Reject"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    <?php endif; ?>

                                    <form action="<?= url('admin/product-questions/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Question?" data-confirm-msg="Are you sure you want to permanently delete this customer question?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $q['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>

                                <!-- Answer Modal for this question -->
                                <div class="modal fade text-start" id="answerModal<?= $q['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title fw-bold">Answer Customer Question #<?= $q['id'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= url('admin/product-questions/answer') ?>" method="POST">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $q['id'] ?>">
                                                <div class="modal-body">
                                                    <div class="p-3 bg-light rounded-3 mb-3">
                                                        <small class="text-muted fw-bold d-block">Customer Question:</small>
                                                        <div class="text-dark fw-semibold mt-1"><?= e($q['question']) ?></div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-dark">Official AFOREHEALTH Care Response <span class="text-danger">*</span></label>
                                                        <textarea name="answer" class="form-control" rows="4" placeholder="Provide accurate sizing, fabric or absorbency guidance..." required><?= e($q['answer'] ?? '') ?></textarea>
                                                        <small class="text-muted">Submitting an answer will automatically mark this question as Approved and publish it to the product page.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-sm btn-afh-primary fw-bold px-3">Save &amp; Publish Answer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
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
        <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light rounded-bottom-4">
            <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> questions)</small>
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
