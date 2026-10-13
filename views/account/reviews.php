<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">My Account</a></li>
            <li class="breadcrumb-item active">My Reviews</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">My Submitted Reviews</h1>
            <p class="text-muted small m-0">Track the status of your reviews and community contributions</p>
        </div>
    </div>

    <?php if (empty($reviews)): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm p-4 text-muted">
            <i class="bi bi-star display-4 mb-2 d-block"></i>
            You haven't submitted any reviews yet. Review your purchased products to help other women!
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($reviews as $rev): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <strong class="text-dark"><?= e($rev['product_name']) ?></strong>
                            <div class="text-warning small"><?= str_repeat('★', (int)$rev['rating']) ?></div>
                        </div>
                        <span class="status-pill status-<?= $rev['status'] === 'approved' ? 'approved' : ($rev['status'] === 'rejected' ? 'rejected' : 'pending') ?>">
                            <?= strtoupper($rev['status']) ?>
                        </span>
                    </div>
                    <h6 class="fw-bold mb-1"><?= e($rev['title']) ?></h6>
                    <p class="text-muted small mb-0"><?= nl2br(e($rev['comment'])) ?></p>
                    <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">Submitted on <?= date('d M Y', strtotime($rev['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
