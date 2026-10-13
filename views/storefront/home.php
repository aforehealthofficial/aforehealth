<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- 1. Hero Banner Section -->
<section class="hero-banner py-5" style="background: linear-gradient(135deg, #FFF9FA 0%, #F8ECF3 100%); border-bottom: 1px solid #F3E1EC;">
    <div class="container py-lg-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="badge bg-white text-primary px-3 py-2 rounded-pill shadow-sm mb-3 border fw-bold">
                    🌸 Everyday Comfort. Period Confidence.
                </div>
                <h1 class="display-4 fw-extrabold text-dark mb-3 lh-sm" style="font-weight: 800; letter-spacing: -0.02em;">
                    Comfort That Moves <br><span style="color: #8B5E83;">With You.</span>
                </h1>
                <p class="lead text-muted mb-4" style="font-size: 1.15rem; line-height: 1.6;">
                    Thoughtfully designed leakproof underwear, buttery-soft everyday panties, and pH-balanced essentials engineered specifically for Indian bodies and tropical climates.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="<?= url('shop') ?>" class="btn btn-afh-primary btn-lg px-4 py-3 shadow-sm">
                        SHOP ALL COLLECTION →
                    </a>
                    <a href="<?= url('category/period-panties') ?>" class="btn btn-afh-secondary btn-lg px-4 py-3">
                        EXPLORE PERIOD PANTIES
                    </a>
                </div>
                <div class="d-flex align-items-center gap-3 pt-2">
                    <div class="text-warning fs-5">★★★★★</div>
                    <div class="small fw-semibold text-dark">
                        Rated <strong>4.9 / 5</strong> by 50,000+ Indian Women
                    </div>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <div class="position-relative">
                    <img src="<?= asset('images/hero_banner_1.svg') ?>" alt="AFOREHEALTH Hero" class="img-fluid rounded-4 shadow-sm" style="max-height: 480px; width: 100%; object-fit: cover;">
                </div>
            </div>
        </div>
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
                <div class="col-lg-2 col-md-4 col-6">
                    <a href="<?= url('category/' . $cat['slug']) ?>" class="cat-card">
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
<section class="py-5 bg-white border-top">
    <div class="container py-lg-4" style="max-width: 860px;">
        <div class="text-center mb-5">
            <span class="badge bg-light text-primary px-3 py-1 rounded-pill fw-bold text-uppercase">Got Questions?</span>
            <h2 class="fw-bold mt-2">Frequently Asked Questions</h2>
            <p class="text-muted">Everything you need to know about sizing, washing, and leakproof wear</p>
        </div>

        <div class="accordion accordion-flush" id="homeFaqAccordion">
            <?php foreach ($faqs as $idx => $fq): ?>
                <div class="accordion-item border rounded-3 mb-3 overflow-hidden shadow-sm">
                    <h2 class="accordion-header" id="heading<?= $idx ?>">
                        <button class="accordion-button fw-bold text-dark <?= $idx === 0 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $idx ?>" aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>">
                            <?= e($fq['question']) ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $idx ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" data-bs-parent="#homeFaqAccordion">
                        <div class="accordion-body text-muted" style="line-height: 1.7;">
                            <?= e($fq['answer']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="<?= url('faq') ?>" class="btn btn-outline-primary fw-bold">View All 15+ FAQs →</a>
        </div>
    </div>
</section>

<!-- 10. WhatsApp Community Call-To-Action Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #E8F5EE 0%, #D8EDE1 100%); border-top: 1px solid #C5E5D1;">
    <div class="container text-center py-lg-3" style="max-width: 760px;">
        <div class="display-6 fw-bold text-dark mb-3">
            Join the AFOREHEALTH <span class="text-success">WhatsApp Sisterhood</span>
        </div>
        <p class="lead text-dark mb-4" style="font-size: 1.1rem;">
            Over 25,000 women share real experiences, get discreet gynecologist advice, and enjoy VIP secret discounts. (We never spam or auto-add you!)
        </p>
        <a href="<?= e(get_setting('whatsapp_group_url')) ?>" target="_blank" class="btn btn-success btn-lg px-4 py-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
            <i class="bi bi-whatsapp fs-4"></i> JOIN OUR WHATSAPP COMMUNITY
        </a>
    </div>
</section>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
