<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">My Account</a></li>
            <li class="breadcrumb-item active">My Orders</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="list-group list-group-flush small">
                    <a href="<?= url('account') ?>" class="list-group-item list-group-item-action"><i class="bi bi-grid me-2"></i> Dashboard</a>
                    <a href="<?= url('account/orders') ?>" class="list-group-item list-group-item-action fw-bold active"><i class="bi bi-box-seam me-2"></i> My Orders</a>
                    <a href="<?= url('account/buy-again') ?>" class="list-group-item list-group-item-action"><i class="bi bi-arrow-repeat me-2"></i> Buy Again</a>
                    <a href="<?= url('account/points') ?>" class="list-group-item list-group-item-action"><i class="bi bi-coin me-2 text-warning"></i> AFORE Points</a>
                    <a href="<?= url('account/referral') ?>" class="list-group-item list-group-item-action"><i class="bi bi-gift me-2 text-primary"></i> Refer &amp; Earn</a>
                    <a href="<?= url('account/questions') ?>" class="list-group-item list-group-item-action"><i class="bi bi-chat-dots me-2"></i> My Questions</a>
                    <a href="<?= url('account/wishlist') ?>" class="list-group-item list-group-item-action"><i class="bi bi-heart me-2"></i> My Wishlist</a>
                    <a href="<?= url('account/addresses') ?>" class="list-group-item list-group-item-action"><i class="bi bi-geo-alt me-2"></i> Saved Addresses</a>
                    <a href="<?= url('account/reviews') ?>" class="list-group-item list-group-item-action"><i class="bi bi-star me-2"></i> My Reviews</a>
                    <a href="<?= url('account/profile') ?>" class="list-group-item list-group-item-action"><i class="bi bi-person me-2"></i> Profile &amp; Password</a>
                    <a href="<?= url('logout') ?>" class="list-group-item list-group-item-action text-danger mt-2 border-top pt-3"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        <div class="col-lg-9">
            <h2 class="h4 fw-bold text-dark mb-3">Order History</h2>

            <?php if (empty($orders)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm p-4 text-muted">
                    <i class="bi bi-box-seam display-4 mb-2 d-block"></i>
                    No orders placed yet.
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($orders as $ord): ?>
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 pb-3 mb-3 border-bottom">
                                <div>
                                    <h6 class="fw-bold m-0 text-dark">Order #<?= e($ord['order_number']) ?></h6>
                                    <small class="text-muted">Placed on <?= date('d M Y', strtotime($ord['created_at'])) ?> • <?= (int)$ord['item_count'] ?> item(s)</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-pill status-<?= strtolower($ord['order_status']) ?>"><?= e($ord['order_status']) ?></span>
                                    <span class="fw-extrabold text-primary fs-6">₹<?= number_format($ord['grand_total'], 0) ?></span>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                <div class="small text-muted">
                                    <strong>Item:</strong> <?= e($ord['first_item_name']) ?> <?= $ord['item_count'] > 1 ? 'and ' . ($ord['item_count'] - 1) . ' more' : '' ?>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="<?= url('account/orders/' . $ord['order_number']) ?>" class="btn btn-sm btn-outline-dark fw-bold">View Order</a>
                                    <form method="POST" action="<?= url('account/reorder') ?>" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="order_number" value="<?= e($ord['order_number']) ?>">
                                        <button type="submit" class="btn btn-sm btn-afh-primary fw-bold"><i class="bi bi-arrow-repeat me-1"></i> Buy Again</button>
                                    </form>
                                    <a href="<?= url('track-order?order_number=' . $ord['order_number']) ?>" class="btn btn-sm btn-outline-primary fw-bold"><i class="bi bi-geo-alt"></i> Track</a>
                                    <a href="<?= url('order/invoice/' . $ord['order_number']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Invoice"><i class="bi bi-printer"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
