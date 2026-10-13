<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">My Account</a></li>
            <li class="breadcrumb-item active">Buy Again</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="list-group list-group-flush small">
                    <a href="<?= url('account') ?>" class="list-group-item list-group-item-action"><i class="bi bi-grid me-2"></i> Dashboard</a>
                    <a href="<?= url('account/orders') ?>" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i> My Orders</a>
                    <a href="<?= url('account/buy-again') ?>" class="list-group-item list-group-item-action fw-bold active"><i class="bi bi-arrow-repeat me-2"></i> Buy Again</a>
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

        <!-- Buy Again Content -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 fw-bold text-dark m-0"><i class="bi bi-arrow-repeat text-primary me-2"></i>Buy Again</h2>
                    <p class="text-muted small m-0">Quickly reorder your previously purchased intimate care and period essentials at current catalog prices.</p>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm p-4 text-muted">
                    <i class="bi bi-arrow-repeat display-4 mb-2 d-block text-secondary"></i>
                    <h5 class="fw-bold text-dark">No repeat purchases found yet</h5>
                    <p class="small text-muted mb-3">Products from your delivered or confirmed orders will appear here for fast 1-click repurchase.</p>
                    <a href="<?= url('shop') ?>" class="btn btn-afh-primary btn-sm fw-bold">Explore Our Collections</a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($products as $prod): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-white text-center d-flex flex-column">
                                <a href="<?= url('product/' . $prod['slug']) ?>" class="d-block mb-3 overflow-hidden rounded-3">
                                    <img src="<?= asset($prod['primary_image'] ?: 'assets/images/placeholder.jpg') ?>" 
                                         alt="<?= e($prod['name']) ?>" 
                                         class="img-fluid" 
                                         style="height: 160px; width: 100%; object-fit: cover;"
                                         onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                                </a>
                                <h6 class="small fw-bold text-dark mb-1 flex-grow-0" style="min-height: 2.4em; line-height: 1.2;">
                                    <a href="<?= url('product/' . $prod['slug']) ?>" class="text-dark text-decoration-none">
                                        <?= e($prod['name']) ?>
                                    </a>
                                </h6>
                                <div class="d-flex justify-content-center align-items-center gap-2 mb-3 mt-auto">
                                    <span class="fw-extrabold text-primary fs-6">₹<?= number_format($prod['sale_price'] ?? $prod['price'], 0) ?></span>
                                    <?php if (!empty($prod['mrp']) && $prod['mrp'] > ($prod['sale_price'] ?? $prod['price'])): ?>
                                        <span class="text-muted text-decoration-line-through small">₹<?= number_format($prod['mrp'], 0) ?></span>
                                    <?php endif; ?>
                                </div>
                                <a href="<?= url('product/' . $prod['slug']) ?>" class="btn btn-sm btn-afh-primary w-100 fw-bold">
                                    <i class="bi bi-bag-plus me-1"></i> Buy Again
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
