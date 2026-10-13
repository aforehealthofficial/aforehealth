<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">My Account</a></li>
            <li class="breadcrumb-item active">My Questions</li>
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
                    <a href="<?= url('account/points') ?>" class="list-group-item list-group-item-action"><i class="bi bi-coin me-2 text-warning"></i> AFORE Points</a>
                    <a href="<?= url('account/referral') ?>" class="list-group-item list-group-item-action"><i class="bi bi-gift me-2 text-primary"></i> Refer &amp; Earn</a>
                    <a href="<?= url('account/questions') ?>" class="list-group-item list-group-item-action fw-bold active"><i class="bi bi-chat-dots me-2"></i> My Questions</a>
                    <a href="<?= url('account/wishlist') ?>" class="list-group-item list-group-item-action"><i class="bi bi-heart me-2"></i> My Wishlist</a>
                    <a href="<?= url('account/addresses') ?>" class="list-group-item list-group-item-action"><i class="bi bi-geo-alt me-2"></i> Saved Addresses</a>
                    <a href="<?= url('account/reviews') ?>" class="list-group-item list-group-item-action"><i class="bi bi-star me-2"></i> My Reviews</a>
                    <a href="<?= url('account/profile') ?>" class="list-group-item list-group-item-action"><i class="bi bi-person me-2"></i> Profile &amp; Password</a>
                    <a href="<?= url('logout') ?>" class="list-group-item list-group-item-action text-danger mt-2 border-top pt-3"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Questions List -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 fw-bold text-dark m-0"><i class="bi bi-chat-left-dots text-primary me-2"></i>My Product Questions</h2>
                    <p class="text-muted small m-0">Inquiries and product questions submitted to AFOREHEALTH care specialists.</p>
                </div>
            </div>

            <?php if (empty($questions)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm p-4 text-muted">
                    <i class="bi bi-chat-dots display-4 mb-2 d-block text-secondary"></i>
                    <h5 class="fw-bold text-dark">No questions submitted yet</h5>
                    <p class="small text-muted mb-3">Have questions about sizing, absorbency, or fabric care? Ask directly on any product page.</p>
                    <a href="<?= url('shop') ?>" class="btn btn-afh-primary btn-sm fw-bold">Explore Our Products</a>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($questions as $q): ?>
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 pb-3 mb-3 border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= asset($q['primary_image'] ?: 'assets/images/placeholder.jpg') ?>" 
                                         alt="<?= e($q['product_name']) ?>" 
                                         class="rounded-3 border" 
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                                    <div>
                                        <h6 class="fw-bold m-0">
                                            <a href="<?= url('product/' . $q['product_slug']) ?>" class="text-dark text-decoration-none">
                                                <?= e($q['product_name']) ?>
                                            </a>
                                        </h6>
                                        <small class="text-muted">Asked on <?= date('d M Y', strtotime($q['created_at'])) ?></small>
                                    </div>
                                </div>
                                <div>
                                    <?php if ($q['status'] === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                    <?php elseif ($q['status'] === 'pending'): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-clock me-1"></i> In Moderation</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle me-1"></i> Rejected</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-2">
                                <span class="badge bg-light text-muted me-1">Q:</span>
                                <span class="fw-semibold text-dark"><?= e($q['question']) ?></span>
                            </div>

                            <?php if (!empty($q['answer'])): ?>
                                <div class="p-3 rounded-3 bg-light border mt-2">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge bg-primary text-white" style="font-size: 0.7rem;">AFOREHEALTH Care Team</span>
                                        <?php if (!empty($q['answered_at'])): ?>
                                            <small class="text-muted" style="font-size: 0.72rem;"><?= date('d M Y', strtotime($q['answered_at'])) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <p class="small text-dark m-0" style="line-height: 1.5;"><?= nl2br(e($q['answer'])) ?></p>
                                </div>
                            <?php else: ?>
                                <p class="small text-muted fst-italic m-0 mt-2">Our intimate wellness specialist will answer this question shortly.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
