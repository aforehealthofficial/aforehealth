<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- Breadcrumbs Header -->
<div class="bg-light py-3 border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small m-0">
                <li class="breadcrumb-item"><a href="<?= url() ?>" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('blog') ?>" class="text-decoration-none text-muted">Wellness Blog</a></li>
                <?php if (!empty($post['category_name'])): ?>
                    <li class="breadcrumb-item"><a href="<?= url('blog?category=' . urlencode($post['category_slug'] ?? '')) ?>" class="text-decoration-none text-muted"><?= e($post['category_name']) ?></a></li>
                <?php endif; ?>
                <li class="breadcrumb-item active text-truncate" style="max-width: 320px;" aria-current="page"><?= e($post['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<!-- Schema.org BlogPosting Structured Data for SEO -->
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

<!-- Main Blog Details Container -->
<div class="container py-2 py-lg-4 mb-5">
    <div class="row g-4 g-xl-5">

        <!-- ========================================== -->
        <!-- 1. MAIN ARTICLE COLUMN                     -->
        <!-- ========================================== -->
        <main class="col-lg-8">
            <article class="bg-white rounded-4 shadow-sm p-4 p-md-5 border mb-5">
                
                <!-- Category Pill & Metadata -->
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge px-3 py-2 rounded-pill fw-bold text-uppercase" style="background: #F7E5EC; color: #8B5E83; font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="bi bi-tag-fill me-1"></i><?= e($post['category_name'] ?? 'Wellness Guide') ?>
                    </span>
                    <span class="text-muted small">
                        <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($post['published_at'])) ?>
                    </span>
                    <span class="text-muted small">•</span>
                    <?php 
                        $wordCount = str_word_count(strip_tags($post['content'] ?? ''));
                        $readingTime = max(2, (int)ceil($wordCount / 180));
                    ?>
                    <span class="text-muted small">
                        <i class="bi bi-clock me-1"></i><?= $readingTime ?> min read
                    </span>
                </div>

                <!-- Main Article Title -->
                <h1 class="display-6 fw-extrabold text-dark mb-3" style="line-height: 1.25;">
                    <?= e($post['title']) ?>
                </h1>

                <!-- Doctor & Specialist Review Row -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 py-3 border-top border-bottom mb-4 bg-light bg-opacity-50 px-3 rounded-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 46px; height: 46px; border-radius: 50%; background: #F3E1EC; color: #8B5E83; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; border: 2px solid #E6D0DE;">
                            AFH
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <strong class="text-dark"><?= e($post['author'] ?? 'AFOREHEALTH Medical Board') ?></strong>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0" style="font-size: 0.65rem;">
                                    <i class="bi bi-patch-check-fill me-1"></i>Verified
                                </span>
                            </div>
                            <small class="text-muted">Doctor &amp; Women's Textile Engineer Reviewed</small>
                        </div>
                    </div>
                    
                    <!-- Quick Share Shortcuts -->
                    <div class="d-flex align-items-center gap-2">
                        <?php 
                            $shareUrl = url('blog/' . $post['slug']);
                            $shareText = urlencode($post['title'] . ' - ' . $shareUrl);
                        ?>
                        <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" title="Share on WhatsApp">
                            <i class="bi bi-whatsapp"></i> <span class="d-none d-sm-inline small">Share</span>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" onclick="navigator.clipboard.writeText('<?= $shareUrl ?>').then(() => showToast('Link copied to clipboard!', 'success'))" title="Copy Link">
                            <i class="bi bi-link-45deg"></i>
                        </button>
                    </div>
                </div>

                <!-- Featured Banner Artwork -->
                <?php if (!empty($post['featured_image'])): ?>
                    <div class="mb-4 rounded-4 overflow-hidden border shadow-sm position-relative">
                        <img src="<?= upload_url('blog/' . $post['featured_image']) ?>" 
                             alt="<?= e($post['title']) ?>" 
                             class="w-100 object-fit-cover" 
                             style="max-height: 480px; width: 100%;">
                    </div>
                <?php endif; ?>

                <!-- Educational Video Guide -->
                <?php if (!empty($post['video_url'])): 
                    $blogVideo = parse_video_source($post['video_url']);
                ?>
                    <?php if (!empty($blogVideo)): ?>
                        <div class="mb-4 p-3 rounded-4 bg-light border">
                            <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-bold">
                                <i class="bi bi-play-circle-fill text-danger fs-5"></i>
                                <span>Doctor Video Explanation</span>
                            </div>
                            <div class="rounded-3 overflow-hidden shadow-sm bg-black ratio ratio-16x9">
                                <?php if ($blogVideo['is_embed']): ?>
                                    <iframe src="<?= e($blogVideo['src']) ?>" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                <?php else: ?>
                                    <video controls class="w-100 h-100 object-fit-contain">
                                        <source src="<?= e($blogVideo['src']) ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Lead Excerpt -->
                <?php if (!empty($post['excerpt'])): ?>
                    <div class="lead text-muted mb-4 py-2 px-3 rounded-3" style="border-left: 4px solid #8B5E83; background: #FCF9FA; font-size: 1.15rem; font-style: italic; line-height: 1.7;">
                        <?= e($post['excerpt']) ?>
                    </div>
                <?php endif; ?>

                <!-- Main Rich Content Body -->
                <div class="blog-content-body text-dark" style="font-size: 1.08rem; line-height: 1.85;">
                    <?= $post['content'] ?>
                </div>

                <!-- Share Footer Bar -->
                <div class="border-top pt-4 mt-5 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-dark small"><i class="bi bi-share me-1"></i>Share this article:</span>
                        <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>" target="_blank" class="btn btn-sm btn-success rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Share on WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>" target="_blank" class="btn btn-sm btn-dark rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;" title="Share on X / Twitter">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>" target="_blank" class="btn btn-sm btn-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background: #1877F2; border-color: #1877F2;" title="Share on Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" onclick="navigator.clipboard.writeText('<?= $shareUrl ?>').then(() => showToast('Article link copied!', 'success'))">
                            <i class="bi bi-link-45deg me-1"></i>Copy Link
                        </button>
                    </div>

                    <a href="<?= url('blog') ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-bold">
                        <i class="bi bi-arrow-left me-1"></i>All Articles
                    </a>
                </div>

                <!-- WhatsApp Sisterhood Community Box -->
                <div class="sisterhood-card p-4 mt-5 text-center shadow-sm rounded-4" style="background: linear-gradient(135deg, #FCF5F8 0%, #F5E8F0 100%); border: 1px solid #ECD8E4;">
                    <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold text-uppercase mb-2" style="font-size: 0.7rem;">
                        <i class="bi bi-shield-lock-fill me-1"></i> 100% Private &amp; Discreet
                    </span>
                    <h5 class="fw-bold text-dark mb-2">Have confidential questions about your menstrual health?</h5>
                    <p class="text-muted small mb-3 mx-auto" style="max-width: 520px;">
                        Join our exclusive AFOREHEALTH WhatsApp sisterhood community to connect with other women, ask doctors confidential questions, and get VIP intimate wellness tips.
                    </p>
                    <a href="<?= e(get_setting('whatsapp_group_url') ?: 'https://chat.whatsapp.com/sample') ?>" target="_blank" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp fs-5"></i> Join AFOREHEALTH Sisterhood
                    </a>
                </div>
            </article>
        </main>

        <!-- ========================================== -->
        <!-- 2. RIGHT SIDEBAR: Clinical Seal & Products -->
        <!-- ========================================== -->
        <aside class="col-lg-4">
            <div class="position-sticky" style="top: 90px;">
                
                <!-- Medical Review Seal Widget -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                    <div class="card-body p-4 text-center">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: #F3E1EC; color: #8B5E83; font-size: 1.75rem;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Doctor &amp; Engineer Verified</h6>
                        <p class="text-muted small mb-3" style="font-size: 0.82rem; line-height: 1.5;">
                            Every AFOREHEALTH wellness guide is clinically reviewed by gynecologists and textile engineers for menstrual safety, microbiome health, and leakproof science.
                        </p>
                        <div class="p-2 rounded-3 bg-light d-flex align-items-center justify-content-around text-center small text-muted">
                            <div>
                                <i class="bi bi-droplet-half text-danger d-block fs-6 mb-1"></i>
                                <span>Leakproof Lab Tested</span>
                            </div>
                            <div class="border-start ps-3">
                                <i class="bi bi-heart-pulse text-success d-block fs-6 mb-1"></i>
                                <span>pH 3.8 Safe</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Doctor-Recommended Essentials Widget -->
                <?php if (!empty($recommendedProducts)): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h6 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                <i class="bi bi-bag-heart text-danger"></i> Recommended Essentials
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($recommendedProducts as $prod): ?>
                                    <div class="d-flex align-items-center gap-3 p-2 rounded-3 hover-lift border bg-light bg-opacity-25">
                                        <a href="<?= url('product/' . $prod['slug']) ?>" class="flex-shrink-0" style="width: 64px; height: 64px; border-radius: 10px; overflow: hidden; background: #FFF;">
                                            <img src="<?= upload_url('products/' . ($prod['primary_image'] ?: 'cat_period_panties.svg')) ?>" 
                                                 alt="<?= e($prod['name']) ?>" 
                                                 class="w-100 h-100 object-fit-cover">
                                        </a>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <a href="<?= url('product/' . $prod['slug']) ?>" class="text-dark fw-bold text-decoration-none small d-block text-truncate mb-1" title="<?= e($prod['name']) ?>">
                                                <?= e($prod['name']) ?>
                                            </a>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-dark small">₹<?= number_format((float)$prod['price'], 0) ?></span>
                                                <?php if ($prod['mrp'] > $prod['price']): ?>
                                                    <span class="text-muted text-decoration-line-through small" style="font-size: 0.75rem;">₹<?= number_format((float)$prod['mrp'], 0) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <a href="<?= url('product/' . $prod['slug']) ?>" class="btn btn-sm btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;" title="View Product">
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-3 text-center">
                                <a href="<?= url('catalog') ?>" class="small fw-bold text-decoration-none" style="color: #8B5E83;">
                                    View All Period Essentials <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Browse Topics Widget -->
                <?php if (!empty($categories)): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                            <h6 class="fw-bold text-dark m-0 d-flex align-items-center gap-2">
                                <i class="bi bi-folder2-open text-primary"></i> Explore Topics
                            </h6>
                        </div>
                        <div class="card-body px-4 pt-1 pb-4">
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($categories as $cat): ?>
                                    <a href="<?= url('blog?category=' . urlencode($cat['slug'])) ?>" 
                                       class="badge text-decoration-none px-3 py-2 rounded-pill fw-semibold border"
                                       style="background: #FDF9FA; color: #555;">
                                        <?= e($cat['name']) ?>
                                        <span class="text-muted ms-1" style="font-size: 0.7rem;">(<?= (int)$cat['post_count'] ?>)</span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </aside>

    </div>

    <!-- ========================================== -->
    <!-- 3. RELATED GUIDES & ARTICLES (FULL WIDTH)  -->
    <!-- ========================================== -->
    <?php if (!empty($recentPosts)): ?>
        <section class="mt-5 pt-4 border-top">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold m-0 text-dark">Related Guides &amp; Articles</h4>
                    <p class="text-muted small m-0">Recommended reading from the AFOREHEALTH medical editorial board</p>
                </div>
                <a href="<?= url('blog') ?>" class="btn btn-outline-secondary btn-sm fw-bold rounded-pill px-3 py-1">
                    View All Articles <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="row g-4">
                <?php foreach ($recentPosts as $rp): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white transition-all hover-lift">
                            <a href="<?= url('blog/' . $rp['slug']) ?>" class="d-block overflow-hidden position-relative" style="height: 180px;">
                                <img src="<?= upload_url('blog/' . ($rp['featured_image'] ?? 'blog_science_period_panties.svg')) ?>" 
                                     alt="<?= e($rp['title']) ?>" 
                                     class="w-100 h-100 object-fit-cover">
                                <?php if (!empty($rp['category_name'])): ?>
                                    <span class="position-absolute top-0 start-0 m-2 badge px-2 py-1 rounded-pill fw-bold text-uppercase" style="background: rgba(255,255,255,0.92); color: #8B5E83; font-size: 0.65rem;">
                                        <?= e($rp['category_name']) ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            <div class="p-4 d-flex flex-column flex-grow-1">
                                <span class="text-muted small mb-2" style="font-size: 0.75rem;">
                                    <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($rp['published_at'])) ?>
                                </span>
                                <h6 class="fw-bold mb-2" style="line-height: 1.4;">
                                    <a href="<?= url('blog/' . $rp['slug']) ?>" class="text-dark text-decoration-none">
                                        <?= e($rp['title']) ?>
                                    </a>
                                </h6>
                                <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.84rem; line-height: 1.6;">
                                    <?= e(mb_strimwidth($rp['excerpt'] ?? '', 0, 95, '...')) ?>
                                </p>
                                <a href="<?= url('blog/' . $rp['slug']) ?>" class="small fw-bold mt-auto d-inline-flex align-items-center gap-1 text-decoration-none" style="color: #8B5E83;">
                                    Read Guide <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
