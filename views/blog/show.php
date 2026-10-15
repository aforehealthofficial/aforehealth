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

    <!-- Schema.org BlogPosting Structured Data for Google Search -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": <?= json_encode($post['title']) ?>,
      "image": [
        <?= json_encode(upload_url('blog/' . $post['featured_image'])) ?>
      ],
      "datePublished": "<?= date('c', strtotime($post['published_at'])) ?>",
      "dateModified": "<?= date('c', strtotime($post['updated_at'] ?? $post['published_at'])) ?>",
      "author": [{
          "@type": "Person",
          "name": <?= json_encode($post['author'] ?? 'AFOREHEALTH Wellness Team') ?>
      }],
      "publisher": {
          "@type": "Organization",
          "name": "AFOREHEALTH",
          "logo": {
            "@type": "ImageObject",
            "url": "<?= asset('images/logo.svg') ?>"
          }
      },
      "description": <?= json_encode($post['excerpt']) ?>
    }
    </script>

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
        <div class="sisterhood-card p-4 my-5 text-center shadow-sm">
            <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.7rem;">
                <i class="bi bi-shield-lock-fill me-1"></i> Private &amp; Discreet
            </span>
            <h5 class="fw-bold text-dark mb-2">Have questions about your menstrual health?</h5>
            <p class="text-muted small mb-3 mx-auto" style="max-width: 500px;">
                Join our WhatsApp sisterhood community to connect with other women, ask doctors confidential questions, and get VIP tips.
            </p>
            <a href="<?= e(get_setting('whatsapp_group_url') ?: 'https://chat.whatsapp.com/sample') ?>" target="_blank" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-whatsapp fs-5"></i> Join AFOREHEALTH Sisterhood
            </a>
        </div>
    </article>

    <!-- Recent Guides -->
    <?php if (!empty($recentPosts)): ?>
        <div class="mb-5 pt-4 border-top">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold m-0 text-dark">Related Guides &amp; Articles</h4>
                <a href="<?= url('blog') ?>" class="btn btn-outline-primary btn-sm fw-bold rounded-pill">View All Articles</a>
            </div>
            <div class="row g-3">
                <?php foreach ($recentPosts as $rp): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white transition-all hover-lift">
                            <a href="<?= url('blog/' . $rp['slug']) ?>" class="d-block overflow-hidden" style="height: 140px;">
                                <img src="<?= upload_url('blog/' . ($rp['featured_image'] ?? 'blog_science_period_panties.svg')) ?>" alt="<?= e($rp['title']) ?>" class="w-100 h-100 object-fit-cover">
                            </a>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <h6 class="fw-bold mb-2">
                                    <a href="<?= url('blog/' . $rp['slug']) ?>" class="text-dark text-decoration-none"><?= e($rp['title']) ?></a>
                                </h6>
                                <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.8rem; line-height: 1.5;">
                                    <?= e(substr($rp['excerpt'] ?? '', 0, 80)) ?>...
                                </p>
                                <a href="<?= url('blog/' . $rp['slug']) ?>" class="small text-primary fw-bold mt-auto d-inline-flex align-items-center gap-1">Read Guide <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
