<?php require __DIR__ . '/../layout-header.php'; ?>

<!-- Page Title & Header Actions -->
<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            <i class="bi bi-bell text-primary"></i> Notification Center
        </h4>
        <p class="text-muted small m-0">Real-time alerts for customer reviews, product questions, order events, and inquiries.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <?php if ($unreadCount > 0): ?>
            <form action="<?= url('admin/notifications/mark-read') ?>" method="POST" data-confirm-title="Mark All as Read?" data-confirm-msg="Are you sure you want to mark all <?= $unreadCount ?> unread notifications as read?">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-dark fw-bold rounded-pill shadow-xs px-3 py-2">
                    <i class="bi bi-check2-all me-1 text-success"></i> Mark All as Read
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Main Notifications Container -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
    
    <!-- Top Filter & Control Bar -->
    <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3 bg-light bg-opacity-50">
        <!-- Filter Tabs -->
        <div class="d-flex flex-wrap align-items-center gap-1.5" id="notifFilterTabs">
            <button type="button" class="btn btn-sm rounded-pill fw-bold px-3 py-1.5 active-filter btn-primary" onclick="filterNotifs('all', this)">
                All (<?= $total ?? count($notifications) ?>)
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-bold px-3 py-1.5 btn-outline-secondary" onclick="filterNotifs('unread', this)">
                Unread <span class="badge bg-danger rounded-pill ms-1"><?= $unreadCount ?></span>
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-bold px-3 py-1.5 btn-outline-secondary" onclick="filterNotifs('question', this)">
                <i class="bi bi-chat-dots me-1"></i> Questions
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-bold px-3 py-1.5 btn-outline-secondary" onclick="filterNotifs('order', this)">
                <i class="bi bi-bag-check me-1"></i> Orders
            </button>
            <button type="button" class="btn btn-sm rounded-pill fw-bold px-3 py-1.5 btn-outline-secondary" onclick="filterNotifs('review', this)">
                <i class="bi bi-star me-1"></i> Reviews
            </button>
        </div>

        <!-- Per-Page Selector -->
        <form method="GET" class="d-flex align-items-center gap-2 m-0 ms-auto">
            <label class="small text-muted text-nowrap m-0">Show:</label>
            <select name="per_page" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="10" <?= ($perPage ?? 20) == 10 ? 'selected' : '' ?>>10</option>
                <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20</option>
                <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30</option>
                <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50</option>
            </select>
        </form>
    </div>

    <!-- Notifications List -->
    <?php if (empty($notifications)): ?>
        <div class="text-center py-5 text-muted">
            <div class="rounded-circle bg-light d-inline-flex p-3 mb-3">
                <i class="bi bi-bell-slash fs-1 text-muted"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">No notifications yet</h6>
            <p class="small text-muted mb-0">You're all caught up! New alerts will appear here as they arrive.</p>
        </div>
    <?php else: ?>
        <div class="p-3 d-flex flex-column gap-2.5" id="notifListWrap">
            <?php foreach ($notifications as $n): 
                $isUnread = !(bool)$n['is_read'];
                $type = $n['type'] ?? 'info';
                
                // Color palette & icon by type
                if ($type === 'order') {
                    $icon = 'bi-bag-check-fill';
                    $iconBg = '#DCFCE7';
                    $iconColor = '#15803D';
                    $typeLabel = 'Customer Order';
                } elseif ($type === 'question') {
                    $icon = 'bi-chat-quote-fill';
                    $iconBg = '#F3E8FF';
                    $iconColor = '#7E22CE';
                    $typeLabel = 'Product Q&A';
                } elseif ($type === 'review') {
                    $icon = 'bi-star-fill';
                    $iconBg = '#FEF3C7';
                    $iconColor = '#B45309';
                    $typeLabel = 'Product Review';
                } else {
                    $icon = 'bi-info-circle-fill';
                    $iconBg = '#E0E7FF';
                    $iconColor = '#4338CA';
                    $typeLabel = 'System Alert';
                }
            ?>
                <div class="notif-card p-3 rounded-3 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 <?= $isUnread ? 'notif-unread' : 'notif-read' ?>" 
                     data-type="<?= e($type) ?>" 
                     data-unread="<?= $isUnread ? '1' : '0' ?>">
                    
                    <div class="d-flex align-items-start gap-3 flex-grow-1">
                        <!-- Icon Circle -->
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" 
                             style="width: 44px; height: 44px; background: <?= $iconBg ?>; color: <?= $iconColor ?>;">
                            <i class="bi <?= $icon ?> fs-5"></i>
                        </div>

                        <!-- Content Text -->
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <span class="badge border bg-white text-dark small fw-semibold px-2 py-0.5" style="font-size: 0.7rem;">
                                    <?= $typeLabel ?>
                                </span>
                                <?php if ($isUnread): ?>
                                    <span class="badge rounded-pill bg-danger px-2 py-0.5 fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">
                                        NEW
                                    </span>
                                <?php endif; ?>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i><?= time_ago($n['created_at']) ?> • <?= date('d M Y, h:i A', strtotime($n['created_at'])) ?>
                                </small>
                            </div>

                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 0.95rem;">
                                <?= e($n['title']) ?>
                            </h6>
                            <p class="small text-muted mb-0" style="line-height: 1.5;">
                                <?= e($n['message']) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <?php if (!empty($n['link'])): ?>
                        <div class="flex-shrink-0 ms-auto ms-sm-0">
                            <a href="<?= url(ltrim($n['link'], '/')) ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-xs notif-btn">
                                <span>View Details</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if (($totalPages ?? 1) > 1): ?>
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center p-3 border-top bg-light bg-opacity-50 gap-2">
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

<style>
/* Notification Cards Clean SaaS Styling */
.notif-card {
    border: 1px solid #ECE7EB;
    background: #FFFFFF;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.notif-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(31, 25, 32, 0.05);
    border-color: #D8CFD5;
}
.notif-card.notif-unread {
    background: #FFFBFC;
    border-left: 4px solid var(--admin-primary) !important;
    border-color: #F0E4EA;
}
.notif-btn {
    transition: all 0.2s ease;
    border-color: #D8CFD5;
}
.notif-btn:hover {
    background-color: var(--admin-primary);
    border-color: var(--admin-primary);
    color: #FFFFFF !important;
}
.notif-btn:hover i {
    transform: translateX(2px);
}
</style>

<script>
// Instant Notification Filtering
function filterNotifs(type, btn) {
    // Update active tab buttons
    document.querySelectorAll('#notifFilterTabs button').forEach(b => {
        b.classList.remove('btn-primary', 'active-filter');
        b.classList.add('btn-outline-secondary');
    });
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-primary', 'active-filter');

    // Filter cards
    const cards = document.querySelectorAll('#notifListWrap .notif-card');
    cards.forEach(card => {
        if (type === 'all') {
            card.style.display = 'flex';
        } else if (type === 'unread') {
            card.style.display = (card.getAttribute('data-unread') === '1') ? 'flex' : 'none';
        } else {
            card.style.display = (card.getAttribute('data-type') === type) ? 'flex' : 'none';
        }
    });
}
</script>

<?php require __DIR__ . '/../layout-footer.php'; ?>
