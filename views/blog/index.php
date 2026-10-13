<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- Blog Hero Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #FFF9FA 0%, #F5E8F0 100%); border-bottom: 1px solid #EEDCE6;">
    <div class="container text-center" style="max-width: 820px;">
        <span class="badge bg-secondary text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">Education &amp; Science</span>
        <h1 class="display-5 fw-bold text-dark mb-2">The Period Care &amp; Wellness Guide</h1>
        <p class="lead text-muted mb-0">
            Evidence-based menstrual health guides, sizing tutorials, fabric science, and doctor-approved intimate hygiene advice.
        </p>
    </div>
</section>

<div class="container py-5">
    <div class="row g-4">
        <?php foreach ($posts as $post): ?>
            <div class="col-md-6 col-lg-6">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <a href="<?= url('blog/' . $post['slug']) ?>" class="d-block" style="aspect-ratio: 16/9; background: #FFF9FA;">
                        <img src="<?= upload_url('blog/' . $post['featured_image']) ?>" alt="<?= e($post['title']) ?>" class="w-100 h-100 object-fit-cover">
                    </a>
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-light text-primary fw-bold text-uppercase"><?= e($post['category_name']) ?></span>
                            <small class="text-muted"><?= date('d M Y', strtotime($post['published_at'])) ?></small>
                        </div>
                        <h4 class="fw-bold mb-2">
                            <a href="<?= url('blog/' . $post['slug']) ?>" class="text-dark text-decoration-none"><?= e($post['title']) ?></a>
                        </h4>
                        <p class="text-muted small mb-4 flex-grow-1" style="line-height: 1.6;">
                            <?= e($post['excerpt']) ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                            <small class="text-muted"><i class="bi bi-person me-1"></i> <?= e($post['author']) ?></small>
                            <a href="<?= url('blog/' . $post['slug']) ?>" class="small text-primary fw-bold">Read Guide →</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
