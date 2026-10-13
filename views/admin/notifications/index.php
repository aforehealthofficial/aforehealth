<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark">Notification Center</h4>
        <small class="text-muted">Real-time alerts for customer reviews, product questions, order events, and return requests.</small>
    </div>
    <?php if ($unreadCount > 0): ?>
        <form action="<?= url('admin/notifications/mark-read') ?>" method="POST" data-confirm-title="Mark All as Read?" data-confirm-msg="Are you sure you want to mark all <?= $unreadCount ?> unread notifications as read?">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-dark btn-sm fw-bold rounded-pill shadow-sm">
                <i class="bi bi-check2-all me-1 text-success"></i> Mark All as Read
            </button>
        </form>
    <?php endif; ?>
</div>

<div class="admin-table-card">
    <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="small fw-bold text-dark">Recent Notifications (<?= $total ?? count($notifications) ?>)</span>
            <?php if ($unreadCount > 0): ?>
                <span class="badge bg-danger"><?= $unreadCount ?> Unread</span>
            <?php else: ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle">All Caught Up</span>
            <?php endif; ?>
        </div>
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

    <?php if (empty($notifications)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bell-slash display-5 mb-2 d-block text-secondary"></i>
            No notifications logged yet.
        </div>
    <?php else: ?>
        <div class="list-group list-group-flush">
            <?php foreach ($notifications as $n): ?>
                <div class="list-group-item p-3 <?= !$n['is_read'] ? 'bg-light border-start border-4 border-primary' : '' ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="d-flex gap-3">
                            <div class="fs-4 text-primary">
                                <?php if ($n['type'] === 'review'): ?>
                                    <i class="bi bi-star text-warning"></i>
                                <?php elseif ($n['type'] === 'question'): ?>
                                    <i class="bi bi-chat-dots text-primary"></i>
                                <?php elseif ($n['type'] === 'order'): ?>
                                    <i class="bi bi-bag-check text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-info-circle text-info"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-dark"><?= e($n['title']) ?></h6>
                                <p class="small text-muted mb-1"><?= e($n['message']) ?></p>
                                <small class="text-muted" style="font-size: 0.75rem;"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></small>
                            </div>
                        </div>
                        <div>
                            <?php if (!empty($n['link'])): ?>
                                <a href="<?= url(ltrim($n['link'], '/')) ?>" class="btn btn-sm btn-outline-primary fw-bold">
                                    View Details →
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (($totalPages ?? 1) > 1): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light rounded-bottom-4">
            <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> notifications)</small>
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
