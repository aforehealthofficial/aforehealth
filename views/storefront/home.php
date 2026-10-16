<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- 1. Hero Banner Section -->
<section class="hero-banner py-4 py-lg-5" style="background: linear-gradient(135deg, #FFF9FA 0%, #F8ECF3 100%); border-bottom: 1px solid #F3E1EC;">
    <div class="container py-lg-3">
        <?php if (!empty($heroBanners) && count($heroBanners) > 1): ?>
            <!-- Multiple Banners Carousel -->
            <div id="homeHeroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4500">
                <div class="carousel-indicators mb-2">
                    <?php foreach ($heroBanners as $bIdx => $b): ?>
                        <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="<?= $bIdx ?>" class="<?= $bIdx === 0 ? 'active' : '' ?>" aria-label="Slide <?= $bIdx + 1 ?>"></button>
                    <?php endforeach; ?>
                </div>
                <div class="carousel-inner rounded-4 shadow-sm">
                    <?php foreach ($heroBanners as $bIdx => $b): ?>
                        <div class="carousel-item <?= $bIdx === 0 ? 'active' : '' ?>">
                            <div class="row align-items-center g-4 p-3 p-lg-4 bg-white rounded-4 border">
                                <div class="col-lg-6 order-2 order-lg-1">
                                    <div class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm mb-2 border fw-bold">
                                        🌸 Everyday Comfort. Period Confidence.
                                    </div>
                                    <h1 class="hero-title text-dark mb-2 lh-sm">
                                        <?= e($b['title']) ?>
                                    </h1>
                                    <?php if (!empty($b['subtitle'])): ?>
                                        <p class="hero-lead text-muted mb-3">
                                            <?= e($b['subtitle']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="hero-buttons d-flex flex-wrap gap-2 mb-3">
                                        <a href="<?= e(url(ltrim($b['link'], '/'))) ?>" class="btn btn-afh-primary px-4 py-2.5 shadow-sm fw-bold">
                                            <?= e($b['cta_text'] ?: 'SHOP NOW') ?> →
                                        </a>
                                        <a href="<?= url('category/period-panties') ?>" class="btn btn-afh-secondary px-4 py-2.5 fw-bold">
                                            EXPLORE PERIOD PANTIES
                                        </a>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 pt-1">
                                        <div class="text-warning small">★★★★★</div>
                                        <div class="small fw-semibold text-dark">
                                            Rated <strong>4.9 / 5</strong> by 50,000+ Indian Women
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 order-1 order-lg-2 text-center">
                                    <?php if (!empty($b['video_url'])): 
                                        $carVideo = parse_video_source($b['video_url']);
                                    ?>
                                        <?php if (!empty($carVideo)): ?>
                                            <div class="rounded-4 overflow-hidden shadow-sm ratio ratio-16x9 bg-black border">
                                                <?php if ($carVideo['is_embed']): ?>
                                                    <iframe src="<?= e($carVideo['src']) ?>" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                                <?php else: ?>
                                                    <video controls autoplay muted loop playsinline class="w-100 h-100 object-fit-cover">
                                                        <source src="<?= e($carVideo['src']) ?>" type="video/mp4">
                                                    </video>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="<?= e(url(ltrim($b['link'], '/'))) ?>" class="d-block text-decoration-none">
                                            <picture>
                                                <source media="(max-width: 767px)" srcset="<?= upload_url('banners/' . ($b['image_mobile'] ?: $b['image_desktop'])) ?>">
                                                <img src="<?= upload_url('banners/' . $b['image_desktop']) ?>" alt="<?= e($b['title']) ?>" class="img-fluid rounded-4 shadow-sm w-100" style="max-height: 420px; object-fit: cover;">
                                            </picture>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <?php 
                $activeBanner = !empty($heroBanners[0]) ? $heroBanners[0] : null;
                $heroTitle = $activeBanner['title'] ?? 'Comfort That Moves With You.';
                $heroSubtitle = $activeBanner['subtitle'] ?? 'Thoughtfully designed leakproof underwear, buttery-soft everyday panties, and pH-balanced essentials engineered specifically for Indian bodies and tropical climates.';
                $heroLink = !empty($activeBanner['link']) ? url(ltrim($activeBanner['link'], '/')) : url('shop');
                $heroCta = !empty($activeBanner['cta_text']) ? $activeBanner['cta_text'] : 'SHOP ALL COLLECTION →';
                $heroDeskImg = !empty($activeBanner['image_desktop']) ? upload_url('banners/' . $activeBanner['image_desktop']) : asset('images/hero_banner_1.svg');
                $heroMobImg = !empty($activeBanner['image_mobile']) ? upload_url('banners/' . $activeBanner['image_mobile']) : $heroDeskImg;
            ?>
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm mb-2 border fw-bold">
                        🌸 Everyday Comfort. Period Confidence.
                    </div>
                    <h1 class="hero-title text-dark mb-2 lh-sm">
                        <?= e($heroTitle) ?>
                    </h1>
                    <p class="hero-lead text-muted mb-3">
                        <?= e($heroSubtitle) ?>
                    </p>
                    <div class="hero-buttons d-flex flex-wrap gap-2 mb-3">
                        <a href="<?= e($heroLink) ?>" class="btn btn-afh-primary px-4 py-2.5 shadow-sm fw-bold">
                            <?= e($heroCta) ?>
                        </a>
                        <a href="<?= url('category/period-panties') ?>" class="btn btn-afh-secondary px-4 py-2.5 fw-bold">
                            EXPLORE PERIOD PANTIES
                        </a>
                    </div>
                    <div class="d-flex align-items-center gap-2 pt-1">
                        <div class="text-warning small">★★★★★</div>
                        <div class="small fw-semibold text-dark">
                            Rated <strong>4.9 / 5</strong> by 50,000+ Indian Women
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2 text-center">
                    <?php if (!empty($activeBanner['video_url'])): 
                        $bVideo = parse_video_source($activeBanner['video_url']);
                    ?>
                        <?php if (!empty($bVideo)): ?>
                            <div class="rounded-4 overflow-hidden shadow-sm ratio ratio-16x9 bg-black border">
                                <?php if ($bVideo['is_embed']): ?>
                                    <iframe src="<?= e($bVideo['src']) ?>" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                                <?php else: ?>
                                    <video controls autoplay muted loop playsinline class="w-100 h-100 object-fit-cover">
                                        <source src="<?= e($bVideo['src']) ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?= e($heroLink) ?>" class="d-block text-decoration-none">
                            <picture>
                                <source media="(max-width: 767px)" srcset="<?= e($heroMobImg) ?>">
                                <img src="<?= e($heroDeskImg) ?>" alt="<?= e($heroTitle) ?>" class="img-fluid rounded-4 shadow-sm w-100" style="max-height: 420px; object-fit: cover;">
                            </picture>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- 2. Trust Badges Row -->
<section class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="row g-3">
            <div class="col-lg-2 col-md-4 col-6">
                <div class="trust-badge-card h-100 p-2 text-center d-flex flex-column align-items-center">
                    <div class="trust-icon-wrap mb-1"><i class="bi bi-shield-lock"></i></div>
                    <h6 class="fw-bold mb-0 small">Secure Payments</h6>
                    <span class="text-muted" style="font-size: 0.7rem;">UPI, Cards &amp; COD</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="trust-badge-card h-100 p-2 text-center d-flex flex-column align-items-center">
                    <div class="trust-icon-wrap mb-1"><i class="bi bi-box-seam"></i></div>
                    <h6 class="fw-bold mb-0 small">Discreet Packaging</h6>
                    <span class="text-muted" style="font-size: 0.7rem;">100% Unmarked Boxes</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="trust-badge-card h-100 p-2 text-center d-flex flex-column align-items-center">
                    <div class="trust-icon-wrap mb-1"><i class="bi bi-arrow-repeat"></i></div>
                    <h6 class="fw-bold mb-0 small">Easy Exchanges</h6>
                    <span class="text-muted" style="font-size: 0.7rem;">7-Day Sizing Support</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="trust-badge-card h-100 p-2 text-center d-flex flex-column align-items-center">
                    <div class="trust-icon-wrap mb-1"><i class="bi bi-lightning-charge"></i></div>
                    <h6 class="fw-bold mb-0 small">Fast Delivery</h6>
                    <span class="text-muted" style="font-size: 0.7rem;">2-4 Days All Across India</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="trust-badge-card h-100 p-2 text-center d-flex flex-column align-items-center">
                    <div class="trust-icon-wrap mb-1"><i class="bi bi-droplet"></i></div>
                    <h6 class="fw-bold mb-0 small">4-Layer Leakproof</h6>
                    <span class="text-muted" style="font-size: 0.7rem;">Absorbs up to 40ml</span>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-6">
                <div class="trust-badge-card h-100 p-2 text-center d-flex flex-column align-items-center">
                    <div class="trust-icon-wrap mb-1"><i class="bi bi-heart"></i></div>
                    <h6 class="fw-bold mb-0 small">Made for Indian Bodies</h6>
                    <span class="text-muted" style="font-size: 0.7rem;">Curated Hip-to-Waist Fit</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Shop By Category -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <span class="badge bg-light text-primary px-3 py-1 rounded-pill fw-bold text-uppercase">Curated Collections</span>
            <h2 class="fw-bold mt-2">Shop by Category</h2>
            <p class="text-muted">Choose comfort tailored to each phase of your cycle and day</p>
        </div>

        <div class="row g-3 g-md-4">
            <?php foreach ($categories as $cat): ?>
                <div class="col-lg-2 col-md-4 col-6 d-flex align-items-stretch">
                    <a href="<?= url('category/' . $cat['slug']) ?>" class="cat-card w-100">
                        <div class="cat-img-wrap">
                            <img src="<?= upload_url('products/' . $cat['image']) ?>" alt="<?= e($cat['name']) ?>" loading="lazy">
                        </div>
                        <h4 class="cat-title"><?= e($cat['name']) ?></h4>
                        <span class="cat-badge"><?= (int)$cat['product_count'] ?> Products</span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 4. Best Sellers Section -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="badge bg-light text-primary px-3 py-1 rounded-pill fw-bold text-uppercase">Most Loved</span>
                <h2 class="fw-bold mt-2 mb-1">Our Bestsellers</h2>
                <p class="text-muted mb-0">Highest rated leakproof and everyday essentials</p>
            </div>
            <a href="<?= url('shop?sort=popularity') ?>" class="btn btn-outline-dark fw-bold mt-3 mt-md-0">
                View All Bestsellers →
            </a>
        </div>

        <div class="row g-3 g-md-4">
            <?php foreach ($bestsellers as $product): ?>
                <div class="col-lg-3 col-md-4 col-6 product-grid-col">
                    <?php require __DIR__ . '/../components/product-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 5. Period Care Spotlight / Science of 4-Layer Gusset -->
<section class="py-5" style="background: #252126; color: #FFFFFF;">
    <div class="container py-lg-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge bg-secondary text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">The Innovation</span>
                <h2 class="display-6 fw-bold text-white mb-3">
                    The 4-Layer Leakproof Revolution.
                </h2>
                <p class="text-white-50 lead mb-4" style="font-size: 1.05rem;">
                    Say goodbye to scratchy plastic pads, plastic rustle, and ruined mattresses. Our proprietary gusset pairs certified organic cotton with micro-porous breathable barriers.
                </p>
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                            <div class="fw-bold text-white mb-1">1. Moisture-Wicking</div>
                            <small class="text-white-50">Dries in under 3s, zero wet feel against skin.</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                            <div class="fw-bold text-white mb-1">2. Absorbent Core</div>
                            <small class="text-white-50">Holds up to 40ml fluid without reverse leak.</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                            <div class="fw-bold text-white mb-1">3. Waterproof Shield</div>
                            <small class="text-white-50">Breathable TPU stops stains on clothes.</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);">
                            <div class="fw-bold text-white mb-1">4. Soft Outer Shell</div>
                            <small class="text-white-50">Silky bamboo modal for zero panty lines.</small>
                        </div>
                    </div>
                </div>

                <a href="<?= url('category/period-panties') ?>" class="btn btn-afh-primary btn-lg">
                    Shop Period Panties Now
                </a>
            </div>
            <div class="col-lg-6 text-center">
                <img src="<?= upload_url('products/cat_period_panties.svg') ?>" alt="Period Panty Gusset Anatomy" class="img-fluid rounded-4 shadow-lg" style="max-height: 420px; background: #FFF9FA; padding: 20px;">
            </div>
        </div>
    </div>
</section>

<!-- 6. Why AFOREHEALTH? -->
<section class="py-5">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <span class="badge bg-light text-primary px-3 py-1 rounded-pill fw-bold text-uppercase">The AFOREHEALTH Difference</span>
            <h2 class="fw-bold mt-2">Why Women Choose AFOREHEALTH</h2>
            <p class="text-muted">Designed specifically for Indian weather, clothes, and everyday movement</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 p-4 rounded-4 shadow-sm text-center" style="background: #FFFFFF;">
                    <div class="mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: #FFF0F5; color: #8B5E83; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                        <i class="bi bi-feather"></i>
                    </div>
                    <h5 class="fw-bold">Zero Wedgies &amp; No Rolling</h5>
                    <p class="text-muted small">
                        Contoured elastic-free waistbands engineered to stay in place without pinching love handles or rolling down during office hours.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 p-4 rounded-4 shadow-sm text-center" style="background: #FFFFFF;">
                    <div class="mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: #EBF7EE; color: #4E8B68; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                        <i class="bi bi-patch-check"></i>
                    </div>
                    <h5 class="fw-bold">Rash-Free Breathable Fabrics</h5>
                    <p class="text-muted small">
                        Certified organic bamboo and Lenzing modal stay 2-3 degrees cooler than polyester, preventing moisture-triggered sweat rashes.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 border-0 p-4 rounded-4 shadow-sm text-center" style="background: #FFFFFF;">
                    <div class="mx-auto mb-3" style="width: 60px; height: 60px; border-radius: 50%; background: #F0F4FC; color: #1E66A8; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">
                        <i class="bi bi-box2-heart"></i>
                    </div>
                    <h5 class="fw-bold">100% Discreet Delivery</h5>
                    <p class="text-muted small">
                        All items are securely boxed with no product labels on outer courier bags. Your comfort and privacy remain sacred.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 7. Combos & Value Packs Spotlight -->
<section class="py-5 bg-white border-top border-bottom">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="badge bg-light text-primary px-3 py-1 rounded-pill fw-bold text-uppercase">Bundle &amp; Save</span>
                <h2 class="fw-bold mt-2 mb-1">Combos &amp; Value Packs</h2>
                <p class="text-muted mb-0">Build your complete cycle routine with up to 35% savings</p>
            </div>
            <a href="<?= url('combos') ?>" class="btn btn-outline-primary fw-bold mt-3 mt-md-0">
                Explore All Bundles →
            </a>
        </div>

        <div class="row g-3 g-md-4">
            <?php foreach (array_slice($newArrivals, 0, 4) as $product): ?>
                <div class="col-lg-3 col-md-4 col-6 product-grid-col">
                    <?php require __DIR__ . '/../components/product-card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 8. Approved Customer Reviews Section -->
<section class="py-5" style="background: #FFF9FA;">
    <div class="container py-lg-4">
        <div class="text-center mb-5">
            <span class="badge bg-light text-primary px-3 py-1 rounded-pill fw-bold text-uppercase">Real Verified Stories</span>
            <h2 class="fw-bold mt-2">Loved by 50,000+ Indian Women</h2>
            <div class="d-flex align-items-center justify-content-center gap-2 mt-2">
                <span class="text-warning fs-5">★★★★★</span>
                <span class="fw-bold text-dark fs-5">4.9</span>
                <span class="text-muted">(Based on 2,400+ Verified Purchases)</span>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 p-4 rounded-4 shadow-sm" style="background: #FFFFFF;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="text-warning">
                                    <?= str_repeat('★', (int)$rev['rating']) ?>
                                </div>
                                <span class="badge bg-success-subtle text-success small"><i class="bi bi-patch-check-fill me-1"></i> Verified Buyer</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-2"><?= e($rev['title']) ?></h6>
                            <p class="text-muted small mb-3 flex-grow-1" style="line-height: 1.6;">
                                "<?= e($rev['comment']) ?>"
                            </p>
                            <div class="d-flex align-items-center gap-2 pt-3 border-top">
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: #F3E1EC; color: #8B5E83; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem;">
                                    <?= strtoupper(substr($rev['customer_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="fw-bold small text-dark"><?= e($rev['customer_name']) ?></div>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?= e($rev['product_name'] ?? 'AFOREHEALTH Wear') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 9. Frequently Asked Questions Accordion -->
<section class="py-4 py-lg-5 bg-white border-top" id="faq-section">
    <div class="container" style="max-width: 820px;">
        <div class="text-center mb-3 mb-md-4">
            <span class="badge px-3 py-1 rounded-pill fw-bold text-uppercase" style="background: var(--afh-secondary-light); color: var(--afh-primary); font-size: 0.72rem; letter-spacing: 0.06em;">Got Questions?</span>
            <h2 class="fw-bold mt-2 mb-1" style="font-size: clamp(1.4rem, 2.5vw, 2rem);">Frequently Asked Questions</h2>
            <p class="text-muted small m-0">Everything you need to know about sizing, washing, and leakproof wear</p>
        </div>

        <div class="accordion accordion-flush d-flex flex-column gap-2" id="homeFaqAccordion">
            <?php foreach ($faqs as $idx => $fq): ?>
                <div class="accordion-item border rounded-3 overflow-hidden shadow-xs">
                    <h2 class="accordion-header" id="heading<?= $idx ?>">
                        <button class="accordion-button fw-bold text-dark py-3 px-3 px-md-4 <?= $idx === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $idx ?>" aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>">
                            <?= e($fq['question']) ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $idx ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" data-bs-parent="#homeFaqAccordion">
                        <div class="accordion-body text-muted pt-0 pb-3 px-3 px-md-4" style="line-height: 1.6; font-size: 0.925rem;">
                            <?= e($fq['answer']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-3 pt-1">
            <a href="<?= url('faq') ?>" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-4 py-2">View All 15+ FAQs →</a>
        </div>
    </div>
</section>

<!-- 10. WhatsApp Community Call-To-Action Banner -->
<section class="py-4 py-lg-4 bg-white border-top">
    <div class="container" style="max-width: 880px;">
        <div class="sisterhood-card p-3 p-sm-4 p-md-4 text-center shadow-sm">
            <div class="d-inline-flex align-items-center gap-2 mb-2">
                <span class="badge bg-success text-white px-3 py-1 rounded-pill fw-bold text-uppercase" style="letter-spacing: 0.05em; font-size: 0.7rem;">
                    <i class="bi bi-shield-lock-fill me-1"></i> Private &amp; Discreet
                </span>
                <div class="avatar-stack d-none d-sm-inline-flex">
                    <span class="avatar-stack-item">🌸</span>
                    <span class="avatar-stack-item">✨</span>
                    <span class="avatar-stack-item">💖</span>
                    <span class="avatar-stack-item" style="background:#198754; font-size:0.6rem;">+25k</span>
                </div>
            </div>

            <h2 class="fw-bold text-dark mb-1" style="font-size: clamp(1.35rem, 2.4vw, 1.85rem);">
                Join the AFOREHEALTH <span class="text-success">WhatsApp Sisterhood</span>
            </h2>
            <p class="text-muted mx-auto mb-3" style="max-width: 600px; font-size: 0.925rem; line-height: 1.55;">
                Over 25,000 women share real experiences, get discreet gynecologist advice, and enjoy VIP secret discounts. 100% confidential.
            </p>

            <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                <span class="badge bg-white text-dark border px-2.5 py-1.5 rounded-pill small fw-semibold" style="font-size: 0.78rem;">
                    <i class="bi bi-check-circle-fill text-success me-1"></i> Free Gynaecologist Q&amp;A
                </span>
                <span class="badge bg-white text-dark border px-2.5 py-1.5 rounded-pill small fw-semibold" style="font-size: 0.78rem;">
                    <i class="bi bi-check-circle-fill text-success me-1"></i> Secret VIP Flash Drops
                </span>
                <span class="badge bg-white text-dark border px-2.5 py-1.5 rounded-pill small fw-semibold" style="font-size: 0.78rem;">
                    <i class="bi bi-check-circle-fill text-success me-1"></i> Zero Spam Guarantee
                </span>
            </div>

            <a href="<?= e(get_setting('whatsapp_group_url') ?: 'https://chat.whatsapp.com/sample') ?>" target="_blank" class="btn btn-success px-4 py-2 px-md-5 py-md-2.5 fw-bold rounded-pill shadow-sm d-inline-flex align-items-center gap-2" style="font-size: 0.925rem;">
                <i class="bi bi-whatsapp fs-5"></i> JOIN OUR WHATSAPP COMMUNITY <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
