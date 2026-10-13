<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item active">My Account</li>
        </ol>
    </nav>

    <div class="row g-4">
        
        <!-- Account Sidebar -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="text-center mb-4 pb-3 border-bottom">
                    <div class="mx-auto mb-2 text-white fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 58px; height: 58px; background: #8B5E83; font-size: 1.4rem;">
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    </div>
                    <h5 class="fw-bold m-0 text-dark"><?= e($user['name']) ?></h5>
                    <small class="text-muted">+91 <?= e($user['mobile']) ?></small>
                </div>

                <div class="list-group list-group-flush small">
                    <a href="<?= url('account') ?>" class="list-group-item list-group-item-action fw-bold active"><i class="bi bi-grid me-2"></i> Dashboard</a>
                    <a href="<?= url('account/orders') ?>" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i> My Orders</a>
                    <a href="<?= url('account/buy-again') ?>" class="list-group-item list-group-item-action"><i class="bi bi-arrow-repeat me-2"></i> Buy Again</a>
                    <a href="<?= url('account/points') ?>" class="list-group-item list-group-item-action"><i class="bi bi-coin me-2 text-warning"></i> AFORE Points</a>
                    <a href="<?= url('account/referral') ?>" class="list-group-item list-group-item-action"><i class="bi bi-gift me-2 text-primary"></i> Refer &amp; Earn</a>
                    <a href="<?= url('account/questions') ?>" class="list-group-item list-group-item-action"><i class="bi bi-chat-dots me-2"></i> My Questions</a>
                    <a href="<?= url('account/wishlist') ?>" class="list-group-item list-group-item-action"><i class="bi bi-heart me-2"></i> My Wishlist</a>
                    <a href="<?= url('account/addresses') ?>" class="list-group-item list-group-item-action"><i class="bi bi-geo-alt me-2"></i> Saved Addresses</a>
                    <a href="<?= url('account/reviews') ?>" class="list-group-item list-group-item-action"><i class="bi bi-star me-2"></i> My Reviews</a>
                    <a href="<?= url('account/profile') ?>" class="list-group-item list-group-item-action"><i class="bi bi-person me-2"></i> Profile &amp; Password</a>
                    <a href="<?= url('account/change-mobile') ?>" class="list-group-item list-group-item-action"><i class="bi bi-phone me-2"></i> Change Mobile</a>
                    <a href="<?= url('logout') ?>" class="list-group-item list-group-item-action text-danger mt-2 border-top pt-3"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Main Account Content -->
        <div class="col-lg-9">
            
            <!-- Welcome Banner -->
            <div class="p-4 rounded-4 mb-4" style="background: linear-gradient(135deg, #FFF9FA 0%, #F8EBF2 100%); border: 1px solid var(--afh-border);">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h2 class="h4 fw-bold text-dark mb-1">Hello, <?= e($user['name']) ?>!</h2>
                        <p class="text-muted small m-0">Manage your orders, tracking, AFORE Points ledger, and intimate wellness favorites.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= url('account/points') ?>" class="btn btn-outline-dark btn-sm fw-bold"><i class="bi bi-coin text-warning me-1"></i> <?= number_format($stats['points_balance'] ?? 0) ?> Pts</a>
                        <a href="<?= url('shop') ?>" class="btn btn-afh-primary btn-sm fw-bold">Shop Collections →</a>
                    </div>
                </div>
            </div>

            <!-- Quick Metrics (Feature 19: Total Orders, Total Spent, AFORE Points, Wishlist Items, Pending Orders) -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Total Orders</div>
                        <div class="h4 fw-extrabold text-primary m-0"><?= (int)($stats['total_orders'] ?? count($recentOrders)) ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Total Spent</div>
                        <div class="h4 fw-extrabold text-dark m-0">₹<?= number_format($stats['total_spent'] ?? 0, 0) ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">AFORE Points</div>
                        <div class="h4 fw-extrabold text-warning m-0"><?= number_format($stats['points_balance'] ?? 0) ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Wishlist Items</div>
                        <div class="h4 fw-extrabold text-dark m-0"><?= (int)($stats['wishlist_count'] ?? get_wishlist_count()) ?></div>
                    </div>
                </div>
                <div class="col-12 col-md-4 col-lg">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Pending Orders</div>
                        <div class="h4 fw-extrabold text-success m-0"><?= (int)($stats['active_orders_count'] ?? 0) ?></div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold m-0">Recent Orders</h5>
                    <a href="<?= url('account/orders') ?>" class="small text-primary fw-semibold">View All Orders →</a>
                </div>

                <?php if (empty($recentOrders)): ?>
                    <div class="text-center py-4 text-muted small">
                        You have not placed any orders yet. Discover our leakproof comfort range!
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $ord): ?>
                                    <tr>
                                        <td><strong><?= e($ord['order_number']) ?></strong></td>
                                        <td><?= date('d M Y', strtotime($ord['created_at'])) ?></td>
                                        <td><span class="status-pill status-<?= strtolower($ord['order_status']) ?>"><?= e($ord['order_status']) ?></span></td>
                                        <td class="fw-bold">₹<?= number_format($ord['grand_total'], 0) ?></td>
                                        <td>
                                            <a href="<?= url('account/orders/' . $ord['order_number']) ?>" class="btn btn-sm btn-outline-dark">Details</a>
                                            <a href="<?= url('order/invoice/' . $ord['order_number']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Invoice"><i class="bi bi-printer"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Buy Again Recommendations (Feature 16) -->
            <?php if (!empty($buyAgainProducts)): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h5 class="fw-bold m-0"><i class="bi bi-arrow-repeat text-primary me-2"></i>Buy Again</h5>
                            <small class="text-muted">Repurchase your favourite period and intimate care essentials at current pricing.</small>
                        </div>
                        <a href="<?= url('account/buy-again') ?>" class="small text-primary fw-semibold">View All →</a>
                    </div>

                    <div class="row g-3">
                        <?php foreach ($buyAgainProducts as $bProd): ?>
                            <div class="col-sm-6 col-md-3">
                                <div class="card h-100 border rounded-3 p-2 text-center">
                                    <a href="<?= url('product/' . $bProd['slug']) ?>" class="d-block mb-2">
                                        <img src="<?= asset($bProd['primary_image'] ?: 'assets/images/placeholder.jpg') ?>" 
                                             alt="<?= e($bProd['name']) ?>" 
                                             class="img-fluid rounded-2" 
                                             style="height: 120px; object-fit: cover;"
                                             onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                                    </a>
                                    <h6 class="small fw-bold text-dark text-truncate mb-1">
                                        <a href="<?= url('product/' . $bProd['slug']) ?>" class="text-dark text-decoration-none"><?= e($bProd['name']) ?></a>
                                    </h6>
                                    <div class="d-flex justify-content-center align-items-center gap-1 mb-2">
                                        <span class="fw-bold text-primary small">₹<?= number_format($bProd['sale_price'] ?? $bProd['price'], 0) ?></span>
                                        <?php if (!empty($bProd['mrp']) && $bProd['mrp'] > ($bProd['sale_price'] ?? $bProd['price'])): ?>
                                            <span class="text-muted text-decoration-line-through" style="font-size: 0.75rem;">₹<?= number_format($bProd['mrp'], 0) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?= url('product/' . $bProd['slug']) ?>" class="btn btn-sm btn-outline-dark w-100 fw-semibold" style="font-size: 0.78rem;">
                                        Buy Again
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
