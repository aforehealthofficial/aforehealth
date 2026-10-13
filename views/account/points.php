<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">My Account</a></li>
            <li class="breadcrumb-item active">AFORE Points</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="list-group list-group-flush small">
                    <a href="<?= url('account') ?>" class="list-group-item list-group-item-action"><i class="bi bi-grid me-2"></i> Dashboard</a>
                    <a href="<?= url('account/orders') ?>" class="list-group-item list-group-item-action"><i class="bi bi-box-seam me-2"></i> My Orders</a>
                    <a href="<?= url('account/buy-again') ?>" class="list-group-item list-group-item-action"><i class="bi bi-arrow-repeat me-2"></i> Buy Again</a>
                    <a href="<?= url('account/points') ?>" class="list-group-item list-group-item-action fw-bold active"><i class="bi bi-coin me-2 text-warning"></i> AFORE Points</a>
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

        <!-- AFORE Points Content -->
        <div class="col-lg-9">
            <!-- Points Balance Card -->
            <div class="p-4 rounded-4 mb-4 text-white shadow-sm" style="background: linear-gradient(135deg, #8B5E83 0%, #6E4567 100%);">
                <div class="row align-items-center">
                    <div class="col-md-7 mb-3 mb-md-0">
                        <span class="badge bg-white text-dark small mb-2"><i class="bi bi-stars text-warning me-1"></i> AFORE Rewards Club</span>
                        <h2 class="h1 fw-extrabold m-0"><?= number_format($balance) ?> <span class="fs-4 fw-normal text-white-50">Points</span></h2>
                        <p class="small text-white-50 m-0 mt-2">
                            Worth ₹<?= number_format($balance * (float)get_setting('loyalty_point_value', 1), 0) ?> towards future orders. Earn 5 points for every ₹100 spent!
                        </p>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <a href="<?= url('shop') ?>" class="btn btn-light text-primary fw-bold btn-sm px-3 shadow-sm">
                            <i class="bi bi-bag-heart me-1"></i> Shop &amp; Earn More
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ledger Summary Stats -->
            <?php
            $earnedTotal = 0;
            $redeemedTotal = 0;
            $expiredTotal = 0;
            foreach ($history as $tx) {
                if ($tx['transaction_type'] === 'earned') {
                    $earnedTotal += (int)$tx['points'];
                } elseif ($tx['transaction_type'] === 'redeemed') {
                    $redeemedTotal += abs((int)$tx['points']);
                } elseif ($tx['transaction_type'] === 'expired') {
                    $expiredTotal += abs((int)$tx['points']);
                }
            }
            ?>
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Points Earned</div>
                        <div class="h4 fw-extrabold text-success m-0">+<?= number_format($earnedTotal) ?></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Points Redeemed</div>
                        <div class="h4 fw-extrabold text-danger m-0">-<?= number_format($redeemedTotal) ?></div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Points Expired</div>
                        <div class="h4 fw-extrabold text-muted m-0"><?= number_format($expiredTotal) ?></div>
                    </div>
                </div>
            </div>

            <!-- Transaction Ledger History -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold m-0"><i class="bi bi-clock-history me-2 text-primary"></i>Points Ledger &amp; History</h5>
                        <small class="text-muted">Complete audit trail of earned and redeemed loyalty credits.</small>
                    </div>
                </div>

                <?php if (empty($history)): ?>
                    <div class="text-center py-5 text-muted small">
                        <i class="bi bi-coin display-5 mb-2 d-block text-secondary"></i>
                        No points activity yet. Complete an order to earn your first AFORE Points!
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th class="text-end">Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $item): ?>
                                    <tr>
                                        <td><?= date('d M Y, h:i A', strtotime($item['created_at'])) ?></td>
                                        <td>
                                            <?php if ($item['transaction_type'] === 'earned'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Earned</span>
                                            <?php elseif ($item['transaction_type'] === 'redeemed'): ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Redeemed</span>
                                            <?php elseif ($item['transaction_type'] === 'reversed'): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Reversed</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle"><?= ucfirst($item['transaction_type']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-dark">
                                            <?= e($item['description'] ?: 'Loyalty reward') ?>
                                            <?php if (!empty($item['reference_type']) && !empty($item['reference_id'])): ?>
                                                <span class="text-muted small">(<?= e($item['reference_type']) ?> #<?= (int)$item['reference_id'] ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold fs-6 <?= (int)$item['points'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= (int)$item['points'] > 0 ? '+' . number_format($item['points']) : number_format($item['points']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
