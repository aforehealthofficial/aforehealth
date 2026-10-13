<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">My Account</a></li>
            <li class="breadcrumb-item active">Refer &amp; Earn</li>
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
                    <a href="<?= url('account/referral') ?>" class="list-group-item list-group-item-action fw-bold active"><i class="bi bi-gift me-2 text-primary"></i> Refer &amp; Earn</a>
                    <a href="<?= url('account/questions') ?>" class="list-group-item list-group-item-action"><i class="bi bi-chat-dots me-2"></i> My Questions</a>
                    <a href="<?= url('account/wishlist') ?>" class="list-group-item list-group-item-action"><i class="bi bi-heart me-2"></i> My Wishlist</a>
                    <a href="<?= url('account/addresses') ?>" class="list-group-item list-group-item-action"><i class="bi bi-geo-alt me-2"></i> Saved Addresses</a>
                    <a href="<?= url('account/reviews') ?>" class="list-group-item list-group-item-action"><i class="bi bi-star me-2"></i> My Reviews</a>
                    <a href="<?= url('account/profile') ?>" class="list-group-item list-group-item-action"><i class="bi bi-person me-2"></i> Profile &amp; Password</a>
                    <a href="<?= url('logout') ?>" class="list-group-item list-group-item-action text-danger mt-2 border-top pt-3"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Referral Program Content -->
        <div class="col-lg-9">
            <!-- Hero Card -->
            <div class="p-4 p-md-5 rounded-4 mb-4 text-white shadow-sm" style="background: linear-gradient(135deg, #8B5E83 0%, #A4759A 100%);">
                <span class="badge bg-white text-primary fw-bold small mb-2"><i class="bi bi-gift-fill me-1"></i> Give ₹100, Get 100 Points</span>
                <h2 class="h3 fw-extrabold m-0">Invite Friends to Period Confidence</h2>
                <p class="text-white-50 small mt-2 mb-4" style="max-width: 550px;">
                    Share your unique referral code. When your friend registers and places their first qualifying order, they get flat ₹100 off and you earn 100 AFORE Points!
                </p>

                <div class="row g-3 align-items-center">
                    <div class="col-md-7">
                        <label class="small text-white-50 fw-semibold mb-1">Your Unique Referral Link</label>
                        <div class="input-group">
                            <input type="text" id="refLinkInput" class="form-control form-control-sm bg-white font-monospace text-dark fw-bold" value="<?= e($referralLink) ?>" readonly>
                            <button class="btn btn-light btn-sm fw-bold px-3" type="button" onclick="copyReferralLink()">
                                <i class="bi bi-clipboard me-1"></i> Copy
                            </button>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <label class="small text-white-50 fw-semibold mb-1">Instant Share</label>
                        <div>
                            <a href="<?= e($whatsappUrl) ?>" target="_blank" class="btn btn-success btn-sm fw-bold w-100">
                                <i class="bi bi-whatsapp me-1"></i> Share on WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referral Stats -->
            <?php
            $totalReferred = count($referrals);
            $rewardedCount = count(array_filter($referrals, fn($r) => $r['status'] === 'rewarded'));
            $pendingCount = count(array_filter($referrals, fn($r) => $r['status'] === 'pending'));
            $totalPointsEarned = array_sum(array_column($referrals, 'points_rewarded'));
            ?>
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Friends Invited</div>
                        <div class="h4 fw-extrabold text-dark m-0"><?= $totalReferred ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Successful Orders</div>
                        <div class="h4 fw-extrabold text-success m-0"><?= $rewardedCount ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Pending First Order</div>
                        <div class="h4 fw-extrabold text-warning m-0"><?= $pendingCount ?></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                        <div class="text-muted small text-uppercase fw-bold mb-1" style="font-size: 0.72rem;">Points Earned</div>
                        <div class="h4 fw-extrabold text-primary m-0">+<?= number_format($totalPointsEarned) ?></div>
                    </div>
                </div>
            </div>

            <!-- Referred Friends List -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold m-0"><i class="bi bi-people me-2 text-primary"></i>Referred Friends</h5>
                        <small class="text-muted">Track the status of your friend invitations and qualification rewards.</small>
                    </div>
                </div>

                <?php if (empty($referrals)): ?>
                    <div class="text-center py-5 text-muted small">
                        <i class="bi bi-share display-5 mb-2 d-block text-secondary"></i>
                        No referrals yet. Share your link with friends, family, or colleagues!
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Friend</th>
                                    <th>Joined On</th>
                                    <th>Status</th>
                                    <th>Reward</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($referrals as $ref): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?= e($ref['referee_name'] ?: 'Friend') ?></td>
                                        <td><?= date('d M Y', strtotime($ref['created_at'])) ?></td>
                                        <td>
                                            <?php if ($ref['status'] === 'rewarded'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i> Qualified &amp; Rewarded</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle"><i class="bi bi-clock me-1"></i> Awaiting First Order</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-success">
                                            <?= $ref['points_rewarded'] > 0 ? '+' . (int)$ref['points_rewarded'] . ' Pts' : '—' ?>
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

<script>
function copyReferralLink() {
    const input = document.getElementById('refLinkInput');
    input.select();
    navigator.clipboard.writeText(input.value).then(() => {
        showToast('Referral link copied to clipboard!', 'success');
    }).catch(() => {
        document.execCommand('copy');
        showToast('Referral link copied!', 'success');
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
