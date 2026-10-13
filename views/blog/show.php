<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4" style="max-width: 860px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('blog') ?>">Wellness Blog</a></li>
            <li class="breadcrumb-item active"><?= e($post['title']) ?></li>
        </ol>
    </nav>

    <article class="bg-white rounded-4 shadow-sm p-4 p-md-5 border mb-5">
        <div class="mb-3">
            <span class="badge bg-light text-primary fw-bold text-uppercase px-3 py-2"><?= e($post['category_name']) ?></span>
            <span class="text-muted small ms-2">• Published on <?= date('d M Y', strtotime($post['published_at'])) ?></span>
        </div>

        <h1 class="display-6 fw-bold text-dark mb-3"><?= e($post['title']) ?></h1>

        <div class="d-flex align-items-center gap-3 py-3 border-top border-bottom mb-4">
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #F3E1EC; color: #8B5E83; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                AFH
            </div>
            <div>
                <strong class="d-block text-dark"><?= e($post['author']) ?></strong>
                <small class="text-muted">Doctor &amp; Women's Textile Engineer Reviewed</small>
            </div>
        </div>

        <!-- Featured Banner Art -->
        <div class="mb-4 rounded-4 overflow-hidden border">
            <img src="<?= upload_url('blog/' . $post['featured_image']) ?>" alt="<?= e($post['title']) ?>" class="w-100" style="max-height: 400px; object-fit: cover;">
        </div>

        <!-- Lead Excerpt -->
        <p class="lead text-muted mb-4 fst-italic" style="border-left: 4px solid var(--afh-primary); padding-left: 16px;">
            <?= e($post['excerpt']) ?>
        </p>

        <!-- Main Rich Body -->
        <div class="blog-content-body text-dark" style="font-size: 1.05rem; line-height: 1.8;">
            <?= $post['content'] ?>
        </div>

        <!-- WhatsApp Community Sisterhood Box -->
        <div class="p-4 rounded-4 my-5 text-center" style="background: linear-gradient(135deg, #E8F5EE 0%, #D8EDE1 100%); border: 1px solid #C4E6D0;">
            <h5 class="fw-bold text-dark mb-2">Have questions about your menstrual health?</h5>
            <p class="text-muted small mb-3">Join our WhatsApp sisterhood community to connect with other women and healthcare experts.</p>
            <a href="<?= e(get_setting('whatsapp_group_url')) ?>" target="_blank" class="btn btn-success fw-bold px-4 py-2 rounded-pill">
                <i class="bi bi-whatsapp me-1"></i> Join AFOREHEALTH Community
            </a>
        </div>
    </article>

    <!-- Recent Guides -->
    <?php if (!empty($recentPosts)): ?>
        <div class="mb-5">
            <h4 class="fw-bold mb-4">Related Guides &amp; Articles</h4>
            <div class="row g-3">
                <?php foreach ($recentPosts as $rp): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-white">
                            <h6 class="fw-bold mb-2">
                                <a href="<?= url('blog/' . $rp['slug']) ?>" class="text-dark"><?= e($rp['title']) ?></a>
                            </h6>
                            <a href="<?= url('blog/' . $rp['slug']) ?>" class="small text-primary fw-bold mt-auto">Read More →</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
