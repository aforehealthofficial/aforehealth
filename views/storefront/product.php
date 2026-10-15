<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('shop') ?>">Shop</a></li>
            <li class="breadcrumb-item"><a href="<?= url('category/' . $product['category_slug']) ?>"><?= e($product['category_name']) ?></a></li>
            <li class="breadcrumb-item active"><?= e($product['name']) ?></li>
        </ol>
    </nav>

    <!-- Schema.org JSON-LD Structured Data for Google Rich Snippets -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": <?= json_encode($product['name']) ?>,
      "image": [
        <?= json_encode(upload_url('products/' . $product['primary_image'])) ?>
      ],
      "description": <?= json_encode($product['short_description'] ?? $product['name']) ?>,
      "sku": <?= json_encode($product['sku']) ?>,
      "brand": {
        "@type": "Brand",
        "name": "AFOREHEALTH"
      },
      "offers": {
        "@type": "Offer",
        "url": <?= json_encode(url('product/' . $product['slug'])) ?>,
        "priceCurrency": "INR",
        "price": <?= json_encode(number_format((float)$product['price'], 2, '.', '')) ?>,
        "itemCondition": "https://schema.org/NewCondition",
        "availability": "<?= $product['stock'] > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>"
      },
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "<?= number_format((float)($product['rating_avg'] ?? 4.9), 1) ?>",
        "reviewCount": "<?= (int)($product['rating_count'] ?? 128) ?>"
      }
    }
    </script>

    <div class="row g-4 g-lg-5">
        
        <!-- Left: Image Gallery -->
        <div class="col-lg-6">
            <div class="position-sticky" style="top: 120px;">
                <!-- Main Featured Image -->
                <div class="product-gallery-main mb-3 rounded-4 overflow-hidden border shadow-sm bg-white" style="aspect-ratio: 1/1;">
                    <img id="mainGalleryImg" src="<?= upload_url('products/' . $product['primary_image']) ?>" alt="<?= e($product['name']) ?>" class="w-100 h-100 object-fit-cover" style="transition: transform 0.3s ease;">
                </div>

                <!-- Thumbnails Carousel / Row -->
                <div class="d-flex gap-2 overflow-x-auto pb-2">
                    <?php foreach ($product['images'] as $img): ?>
                        <div class="gallery-thumb rounded-3 overflow-hidden border cursor-pointer" style="width: 80px; height: 80px; flex-shrink: 0;" onclick="document.getElementById('mainGalleryImg').src = '<?= upload_url('products/' . $img['image_path']) ?>'">
                            <img src="<?= upload_url('products/' . $img['image_path']) ?>" alt="<?= e($img['alt_text']) ?>" class="w-100 h-100 object-fit-cover">
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Hygiene Seal Notice Box -->
                <div class="mt-4 p-3 rounded-3" style="background: #FDF5F8; border: 1px solid #F0D5E1;">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <strong class="small text-dark">Hygienically Packed &amp; Sealed</strong>
                    </div>
                    <p class="small text-muted mb-0" style="font-size: 0.8125rem;">
                        This item arrives sealed in tamper-evident sanitised packaging. Eligible for 7-day size exchange if unopened.
                    </p>
                </div>
            </div>
        </div>

        <!-- Right: Product Details & Add to Cart -->
        <div class="col-lg-6">
            <div class="product-details-content">
                
                <!-- Category & SKU -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-light text-primary fw-bold text-uppercase"><?= e($product['category_name']) ?></span>
                    <small class="text-muted">SKU: <strong id="displaySku"><?= e($product['sku']) ?></strong></small>
                </div>

                <!-- Flash Sale Banner (if active) -->
                <?php if (!empty($flashSale)): ?>
                    <div class="alert alert-warning border-0 rounded-3 py-2 px-3 mb-3 d-flex align-items-center justify-content-between shadow-sm" style="background: #FFF4E5;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger">FLASH SALE</span>
                            <strong class="text-dark small"><?= e($flashSale['flash_sale_title']) ?> (<?= (int)$flashSale['discount_percent'] ?>% OFF)</strong>
                        </div>
                        <div class="text-danger small fw-bold font-monospace">
                            <i class="bi bi-clock-history me-1"></i> Ends <?= date('d M, h:i A', strtotime($flashSale['end_at'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Title -->
                <h1 class="h2 fw-extrabold text-dark mb-2"><?= e($product['name']) ?></h1>

                <!-- Ratings & Reviews Header -->
                <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                    <div class="text-warning fs-6">★★★★★</div>
                    <span class="fw-bold"><?= number_format((float)$product['rating_avg'], 1) ?></span>
                    <a href="#productReviewsSection" class="text-muted small text-decoration-underline">(<?= (int)$product['rating_count'] ?> Verified Reviews)</a>
                </div>

                <!-- Price Box -->
                <div class="p-3 rounded-3 mb-4 d-flex align-items-baseline gap-3" style="background: #FFF9FA; border: 1px solid var(--afh-border);">
                    <div class="h2 fw-extrabold text-dark m-0" id="displayPrice">₹<?= number_format((float)$product['price'], 0) ?></div>
                    <?php if ($product['mrp'] > $product['price']): ?>
                        <div class="text-muted text-decoration-line-through fs-5" id="displayMrp">₹<?= number_format((float)$product['mrp'], 0) ?></div>
                        <span class="badge bg-success-subtle text-success fs-6 fw-bold"><?= $product['discount_percent'] ?>% OFF</span>
                    <?php endif; ?>
                    <small class="text-muted ms-auto">Inclusive of all taxes</small>
                </div>

                <!-- Short Description -->
                <p class="text-muted mb-4" style="line-height: 1.6;">
                    <?= e($product['short_description']) ?>
                </p>

                <!-- Product Form (Variant, Size, Quantity) -->
                <form action="<?= url('cart/add') ?>" method="POST" class="ajax-add-to-cart-form" id="addToCartForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="variant_id" id="selectedVariantId" value="<?= !empty($product['variants'][0]['id']) ? $product['variants'][0]['id'] : '' ?>">

                    <!-- Colors if available -->
                    <?php if (!empty($product['colors'])): ?>
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase text-dark d-flex justify-content-between">
                                <span>Color: <strong id="selectedColorName" class="text-primary"><?= e($product['colors'][0]['color']) ?></strong></span>
                            </label>
                            <div class="d-flex gap-2">
                                <?php foreach ($product['colors'] as $cIdx => $c): ?>
                                    <button type="button" 
                                            class="btn p-1 rounded-circle border-2 color-swatch-btn <?= $cIdx === 0 ? 'border-primary' : 'border-light' ?>" 
                                            style="width: 36px; height: 36px; background: <?= $c['color_code'] ?: '#252126' ?>; border-style: solid !important;"
                                            title="<?= e($c['color']) ?>"
                                            onclick="selectColor('<?= e($c['color']) ?>', this)">
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Sizes, Size Chart & Size Finder -->
                    <?php if (!empty($product['sizes'])): ?>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold small text-uppercase text-dark m-0">Select Size:</label>
                                <div>
                                    <button type="button" class="btn btn-link btn-sm text-primary p-0 text-decoration-underline fw-semibold me-3" data-bs-toggle="modal" data-bs-target="#sizeChartModal">
                                        <i class="bi bi-rulers me-1"></i> Size Guide
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-bold" data-bs-toggle="modal" data-bs-target="#sizeFinderModal">
                                        <i class="bi bi-magic me-1"></i> Find My Size
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($product['sizes'] as $sIdx => $sz): ?>
                                    <button type="button" 
                                            class="btn size-selector-btn <?= $sIdx === 0 ? 'btn-primary' : 'btn-outline-secondary' ?>" 
                                            style="min-width: 52px; font-weight: 700;"
                                            onclick="selectSize('<?= $sz ?>', this)">
                                        <?= $sz ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quantity Stepper -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-uppercase text-dark mb-1">Quantity:</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="qty-stepper" style="padding: 4px 8px;">
                                <button type="button" class="qty-btn" onclick="let q = document.getElementById('prodQtyInput'); if(q.value>1) q.value--;">-</button>
                                <input type="number" id="prodQtyInput" name="quantity" value="1" min="1" max="10" class="qty-display border-0 text-center fw-bold" readonly>
                                <button type="button" class="qty-btn" onclick="let q = document.getElementById('prodQtyInput'); if(q.value<10) q.value++;">+</button>
                            </div>
                            <span class="text-success small fw-semibold"><i class="bi bi-check2-circle"></i> In Stock &amp; Ready to Ship</span>
                        </div>
                    </div>

                    <!-- Add to Cart & Buy Now Buttons -->
                    <div class="row g-2 mb-4">
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-afh-primary w-100 py-3 fw-bold" id="main-add-to-cart-btn">
                                <i class="bi bi-bag-plus me-2"></i> ADD TO BAG
                            </button>
                        </div>
                        <div class="col-sm-6">
                            <button type="button" class="btn btn-afh-dark w-100 py-3 fw-bold" onclick="buyNow(this)">
                                BUY IT NOW
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Check Delivery Availability (Pincode) -->
                <div class="p-3 rounded-4 bg-white border mb-4">
                    <label class="form-label fw-bold small text-uppercase text-dark mb-2">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Check Delivery Availability:
                    </label>
                    <div class="input-group">
                        <input type="text" id="pincodeCheckInput" class="form-control" placeholder="Enter 6-digit PIN code (e.g. 110001)" maxlength="6">
                        <button type="button" class="btn btn-afh-primary px-4 fw-bold" onclick="checkDeliveryPincode()">
                            CHECK
                        </button>
                    </div>
                    <div id="pincodeCheckResult" style="display: none;" class="mt-2 p-2 rounded-3 small"></div>
                </div>

                <!-- Contextual Wishlist, WhatsApp & Compare Actions -->
                <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                    <?php $inWishProd = is_in_wishlist((int)$product['id']); ?>
                    <button type="button" 
                            class="btn <?= $inWishProd ? 'btn-danger' : 'btn-outline-danger' ?> btn-sm rounded-pill px-3 fw-bold btn-wishlist-toggle d-inline-flex align-items-center gap-1" 
                            data-product-id="<?= $product['id'] ?>"
                            title="<?= $inWishProd ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                        <i class="bi <?= $inWishProd ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                        <span class="wishlist-btn-text"><?= $inWishProd ? 'In Wishlist' : 'Wishlist' ?></span>
                    </button>
                    
                    <a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/[^0-9]/', '', get_setting('whatsapp_number', '919876543210')) ?>&text=<?= urlencode('Hi AFOREHEALTH Care, I have a question about ' . $product['name'] . ' regarding sizing/absorbency.') ?>" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold flex-grow-1">
                        <i class="bi bi-whatsapp me-1"></i> Ask Us on WhatsApp
                    </a>
                    <form action="<?= url('compare/add') ?>" method="POST" class="d-inline">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit" class="btn <?= $inCompare ? 'btn-secondary' : 'btn-outline-dark' ?> btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-layout-split me-1"></i> <?= $inCompare ? 'In Comparison' : '+ Compare' ?>
                        </button>
                    </form>
                </div>

                <!-- Value Proposition Highlights -->
                <div class="p-3 rounded-3 bg-white border mb-4">
                    <div class="row g-2 text-center text-sm-start">
                        <div class="col-sm-4 d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-success fs-4"></i>
                            <span class="small fw-semibold">100% Leakproof Guarantee</span>
                        </div>
                        <div class="col-sm-4 d-flex align-items-center gap-2">
                            <i class="bi bi-truck text-primary fs-4"></i>
                            <span class="small fw-semibold">Free Delivery Above ₹499</span>
                        </div>
                        <div class="col-sm-4 d-flex align-items-center gap-2">
                            <i class="bi bi-box text-dark fs-4"></i>
                            <span class="small fw-semibold">100% Discreet Shipping</span>
                        </div>
                    </div>
                </div>

                <!-- Product Specifications Tabs / Accordion -->
                <div class="accordion accordion-flush rounded-3 border overflow-hidden mb-5" id="productInfoAccordion">
                    
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#descCollapse">
                                Description &amp; How It Works
                            </button>
                        </h2>
                        <div id="descCollapse" class="accordion-collapse collapse show" data-bs-parent="#productInfoAccordion">
                            <div class="accordion-body text-muted" style="line-height: 1.7;">
                                <?= nl2br(e($product['description'])) ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($product['material'])): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#matCollapse">
                                    Fabric &amp; Gusset Composition
                                </button>
                            </h2>
                            <div id="matCollapse" class="accordion-collapse collapse" data-bs-parent="#productInfoAccordion">
                                <div class="accordion-body text-muted">
                                    <?= nl2br(e($product['material'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['care_instructions'])): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#careCollapse">
                                    Washing &amp; Longevity Guide
                                </button>
                            </h2>
                            <div id="careCollapse" class="accordion-collapse collapse" data-bs-parent="#productInfoAccordion">
                                <div class="accordion-body text-muted">
                                    <?= nl2br(e($product['care_instructions'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <!-- Product Questions & Answers Section -->
    <div class="mt-5 pt-4 border-top" id="productQuestionsSection">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Questions &amp; Answers</h3>
                <p class="text-muted small mb-0">Have questions about absorbency, fit, or hygiene? Read customer answers or ask our specialists.</p>
            </div>
            <?php if (is_authenticated()): ?>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#askQuestionModal">
                    <i class="bi bi-question-circle me-1"></i> Ask a Question
                </button>
            <?php else: ?>
                <a href="<?= url('login') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                    Log in to Ask
                </a>
            <?php endif; ?>
        </div>

        <?php if (empty($questions)): ?>
            <div class="p-4 rounded-4 bg-white border text-center text-muted small">
                No questions asked yet for this product. Be the first to ask!
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($questions as $q): ?>
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                        <div class="d-flex gap-2 mb-2">
                            <strong class="text-dark small">Q:</strong>
                            <div class="fw-bold text-dark small flex-grow-1"><?= e($q['question']) ?></div>
                        </div>
                        <?php if (!empty($q['answer'])): ?>
                            <div class="d-flex gap-2 ps-3 border-start border-3 border-primary" style="background: #FAF7F9; padding: 10px; border-radius: 6px;">
                                <strong class="text-primary small" style="color: var(--afh-primary) !important;">A:</strong>
                                <div class="text-muted small">
                                    <?= nl2br(e($q['answer'])) ?>
                                    <small class="text-muted d-block mt-1">— Answered by <strong>AFOREHEALTH Medical &amp; Care Team</strong></small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="small text-muted fst-italic ps-3">Our care team will answer this question shortly.</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product Customer Reviews Section -->
    <div class="mt-5 pt-5 border-top" id="productReviewsSection">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h3 class="fw-bold text-dark m-0">Customer Reviews</h3>
                <p class="text-muted small mb-0">Real feedback from verified purchasers in India.</p>
            </div>
            <div>
                <?php if (is_authenticated()): ?>
                    <?php if ($hasReviewed): ?>
                        <span class="badge bg-success-subtle text-success p-2 small"><i class="bi bi-check-circle me-1"></i>You have reviewed this product</span>
                    <?php else: ?>
                        <button type="button" class="btn btn-afh-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                            <i class="bi bi-pencil-square me-1"></i> Write a Review
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= url('login') ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Log in to write a review
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rating Distribution Summary -->
        <div class="row g-4 align-items-center mb-5 p-4 rounded-4 bg-white border">
            <div class="col-md-4 text-center border-end-md">
                <div class="display-3 fw-bold text-dark"><?= number_format((float)$product['rating_avg'], 1) ?></div>
                <div class="text-warning fs-5 mb-1">★★★★★</div>
                <span class="text-muted small">Based on <?= (int)$ratingStats['total'] ?> approved reviews</span>
            </div>
            <div class="col-md-8">
                <?php foreach ($ratingStats['distribution'] as $star => $data): ?>
                    <div class="d-flex align-items-center gap-2 mb-1 small">
                        <span style="width: 45px;"><?= $star ?> ★</span>
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $data['percent'] ?>%"></div>
                        </div>
                        <span class="text-muted" style="width: 35px; text-align: right;"><?= $data['count'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Approved Reviews List -->
        <?php if (empty($reviews)): ?>
            <div class="p-4 rounded-4 bg-white border text-center text-muted small">
                No approved reviews yet for this product. Verified buyers will be prompted after delivery.
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($reviews as $rev): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-warning small"><?= str_repeat('★', (int)$rev['rating']) ?><?= str_repeat('☆', 5 - (int)$rev['rating']) ?></div>
                                <span class="badge bg-success-subtle text-success small">✓ Verified Buyer</span>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark"><?= e($rev['title']) ?></h6>
                            <p class="text-muted small mb-3"><?= nl2br(e($rev['comment'])) ?></p>
                            <div class="d-flex justify-content-between align-items-center text-muted small mt-auto border-top pt-2" style="font-size: 0.8rem;">
                                <span><?= e($rev['customer_name']) ?></span>
                                <span><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="mt-5 pt-5 border-top">
            <h3 class="fw-bold mb-4">You May Also Like</h3>
            <div class="row g-3 g-md-4">
                <?php foreach ($relatedProducts as $product): ?>
                    <div class="col-lg-3 col-md-4 col-6 product-grid-col">
                        <?php require __DIR__ . '/../components/product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recently Viewed Products Section -->
    <?php if (!empty($recentlyViewed)): ?>
        <div class="mt-5 pt-5 border-top">
            <h3 class="fw-bold mb-4">Recently Viewed By You</h3>
            <div class="row g-3 g-md-4">
                <?php foreach ($recentlyViewed as $product): ?>
                    <div class="col-lg-3 col-md-4 col-6 product-grid-col">
                        <?php require __DIR__ . '/../components/product-card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Size Chart Modal -->
<div class="modal fade" id="sizeChartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">AFOREHEALTH Indian Size Guide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">
                    Measure around the fullest part of your hips with a measuring tape. If between two sizes, choose the larger size for maximum comfort.
                </p>
                <div class="table-responsive">
                    <table class="table table-bordered text-center small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Size</th>
                                <th>Waist (Inches)</th>
                                <th>Hip (Inches)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><strong>XS</strong></td><td>24 - 26</td><td>32 - 34</td></tr>
                            <tr><td><strong>S</strong></td><td>26 - 28</td><td>34 - 36</td></tr>
                            <tr><td><strong>M</strong></td><td>29 - 31</td><td>37 - 39</td></tr>
                            <tr><td><strong>L</strong></td><td>32 - 34</td><td>40 - 42</td></tr>
                            <tr><td><strong>XL</strong></td><td>35 - 37</td><td>43 - 45</td></tr>
                            <tr><td><strong>XXL</strong></td><td>38 - 40</td><td>46 - 48</td></tr>
                            <tr><td><strong>3XL</strong></td><td>41 - 44</td><td>49 - 52</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Size Finder Interactive Modal -->
<div class="modal fade" id="sizeFinderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="bi bi-magic text-primary me-2"></i>Find Your Perfect Fit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">
                    Enter your waist and hip measurements in inches. We will calculate the closest fit according to AFOREHEALTH Indian body sizing.
                </p>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Waist (Inches) *</label>
                        <input type="number" id="sfWaist" class="form-control" placeholder="e.g. 30" min="20" max="60" step="0.5">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Hip (Inches) *</label>
                        <input type="number" id="sfHip" class="form-control" placeholder="e.g. 38" min="26" max="70" step="0.5">
                    </div>
                </div>

                <div id="sizeFinderResult" class="p-3 rounded-3 mt-3" style="display: none; background: #FAF7F9; border: 1px solid var(--afh-border);">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success">Recommended Size</span>
                        <h4 class="fw-bold text-dark m-0" id="sfRecommendedSize">L</h4>
                    </div>
                    <p class="small text-muted mb-0" id="sfExplanation"></p>
                </div>

                <p class="text-muted small mt-3 mb-0" style="font-size: 0.75rem;">
                    *Disclaimer: This is a sizing recommendation tool based on general measurements. Intimate wear comfort may vary depending on personal fit preference.
                </p>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-afh-primary fw-bold px-4" onclick="calculateRecommendedSize()">Calculate Size</button>
            </div>
        </div>
    </div>
</div>

<!-- Ask Question Modal -->
<div class="modal fade" id="askQuestionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Ask a Question About This Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form onsubmit="event.preventDefault(); submitProductQuestion(this);">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Your Question *</label>
                        <textarea name="question" class="form-control" rows="4" placeholder="e.g. Can I use these for heavy post-partum flow or overnight sleeping?" required></textarea>
                    </div>
                    <p class="small text-muted mb-0">
                        <i class="bi bi-shield-check text-success me-1"></i> Questions are answered by our female care specialists and published after moderation.
                    </p>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-afh-primary fw-bold" id="submitQuestionBtn">Submit Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Write Review Modal (Customer Purchase Verified) -->
<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Write a Customer Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="reviewSubmitForm" onsubmit="event.preventDefault(); submitProductReview(this);">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Rating (1 to 5 Stars):</label>
                        <select name="rating" class="form-select">
                            <option value="5">★★★★★ (5 Stars - Excellent)</option>
                            <option value="4">★★★★☆ (4 Stars - Very Good)</option>
                            <option value="3">★★★☆☆ (3 Stars - Average)</option>
                            <option value="2">★★☆☆☆ (2 Stars - Below Expectation)</option>
                            <option value="1">★☆☆☆☆ (1 Star - Poor)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Review Headline:</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Total peace of mind overnight!" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Your Review Description:</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience regarding comfort, fabric, washing, and absorbency..." required></textarea>
                    </div>
                    <div class="alert alert-info small py-2 mb-0">
                        <i class="bi bi-info-circle me-1"></i> To protect our community from spam, all customer reviews are moderated by our admin team before being published.
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-afh-primary fw-bold" id="submitReviewBtn">Submit for Moderation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mobile Sticky Add to Cart Bar -->
<div class="sticky-mobile-cart-bar">
    <div>
        <div class="fw-bold small text-truncate" style="max-width: 170px;"><?= e($product['name']) ?></div>
        <div class="fw-extrabold text-primary">₹<?= number_format((float)$product['price'], 0) ?></div>
    </div>
    <button type="button" class="btn btn-afh-primary btn-sm px-3 fw-bold" onclick="document.getElementById('addToCartForm').requestSubmit();">
        <i class="bi bi-bag-plus me-1"></i> Add to Bag
    </button>
</div>

<script>
const allProductVariants = <?= json_encode($product['variants'] ?? []) ?>;
let activeColor = <?= json_encode($product['colors'][0]['color'] ?? '') ?>;
let activeSize = <?= json_encode($product['sizes'][0] ?? '') ?>;

function syncVariantState() {
    if (!allProductVariants || allProductVariants.length === 0) return;
    let matched = allProductVariants.find(v => v.color === activeColor && v.size === activeSize);
    if (!matched) {
        matched = allProductVariants.find(v => v.size === activeSize) || allProductVariants.find(v => v.color === activeColor) || allProductVariants[0];
    }
    if (matched) {
        const varIdInput = document.getElementById('selectedVariantId');
        if (varIdInput) varIdInput.value = matched.id;
        const skuEl = document.getElementById('displaySku');
        if (skuEl && matched.sku) skuEl.textContent = matched.sku;
        const priceEl = document.getElementById('displayPrice');
        if (priceEl && matched.price) priceEl.textContent = '₹' + Math.round(matched.price).toLocaleString('en-IN');
        const mrpEl = document.getElementById('displayMrp');
        if (mrpEl && matched.mrp && matched.mrp > matched.price) {
            mrpEl.textContent = '₹' + Math.round(matched.mrp).toLocaleString('en-IN');
        }
    }
}

function selectColor(colorName, btn) {
    activeColor = colorName;
    const colLabel = document.getElementById('selectedColorName');
    if (colLabel) colLabel.textContent = colorName;
    document.querySelectorAll('.color-swatch-btn').forEach(b => b.classList.replace('border-primary', 'border-light'));
    btn.classList.replace('border-light', 'border-primary');
    syncVariantState();
}

function selectSize(sizeVal, btn) {
    activeSize = sizeVal;
    document.querySelectorAll('.size-selector-btn').forEach(b => b.classList.replace('btn-primary', 'btn-outline-secondary'));
    btn.classList.replace('btn-outline-secondary', 'btn-primary');
    syncVariantState();
}

function buyNow(btn) {
    const form = document.getElementById('addToCartForm');
    const formData = new FormData(form);
    if (btn) btn.disabled = true;
    fetch('/cart/add', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/checkout';
        } else {
            showToast(data.message || 'Could not process order.', 'danger');
            if (btn) btn.disabled = false;
        }
    })
    .catch(() => {
        window.location.href = '/checkout';
    });
}

// Initial sync
document.addEventListener('DOMContentLoaded', syncVariantState);


function checkDeliveryPincode() {
    const pin = document.getElementById('pincodeCheckInput').value.trim();
    const resDiv = document.getElementById('pincodeCheckResult');
    if (!pin || pin.length !== 6) {
        resDiv.style.display = 'block';
        resDiv.className = 'mt-2 p-2 rounded-3 small alert alert-danger';
        resDiv.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> Please enter a valid 6-digit Indian PIN code.';
        return;
    }

    resDiv.style.display = 'block';
    resDiv.className = 'mt-2 p-2 rounded-3 small alert alert-secondary';
    resDiv.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Checking delivery serviceability...';

    fetch('/api/pincode/check?pincode=' + encodeURIComponent(pin))
        .then(res => res.json())
        .then(data => {
            if (data.serviceable) {
                resDiv.className = 'mt-2 p-3 rounded-3 small alert alert-success';
                resDiv.innerHTML = `
                    <div class="fw-bold text-success mb-1"><i class="bi bi-check-circle-fill me-1"></i> Delivery Available to ${data.city}, ${data.state} (${pin})</div>
                    <div>Estimated delivery: <strong>${data.estimated_dates}</strong> (${data.estimated_days_text})</div>
                    <div class="mt-1">${data.cod_available ? '✓ Cash on Delivery (COD) Available' : '⚠ Prepaid Only'}</div>
                `;
            } else {
                resDiv.className = 'mt-2 p-3 rounded-3 small alert alert-danger';
                resDiv.innerHTML = `<i class="bi bi-x-circle-fill me-1"></i> ${data.message}`;
            }
        })
        .catch(() => {
            resDiv.className = 'mt-2 p-2 rounded-3 small alert alert-danger';
            resDiv.innerHTML = 'Unable to check PIN code availability at this moment.';
        });
}

function calculateRecommendedSize() {
    const waist = parseFloat(document.getElementById('sfWaist').value);
    const hip = parseFloat(document.getElementById('sfHip').value);
    const resBox = document.getElementById('sizeFinderResult');

    if (!waist || !hip) {
        alert('Please enter both waist and hip measurements.');
        return;
    }

    let recommended = 'M';
    if (hip <= 34 || waist <= 26) recommended = 'XS';
    else if (hip <= 36 || waist <= 28) recommended = 'S';
    else if (hip <= 39 || waist <= 31) recommended = 'M';
    else if (hip <= 42 || waist <= 34) recommended = 'L';
    else if (hip <= 45 || waist <= 37) recommended = 'XL';
    else if (hip <= 48 || waist <= 40) recommended = 'XXL';
    else recommended = '3XL';

    document.getElementById('sfRecommendedSize').innerText = recommended;
    document.getElementById('sfExplanation').innerText = `Based on your measurements (Waist: ${waist}", Hip: ${hip}"), ${recommended} is the closest match according to this product's Indian size chart for optimal leakproof coverage and everyday comfort.`;
    resBox.style.display = 'block';

    // Auto-select size button if present
    document.querySelectorAll('.size-selector-btn').forEach(btn => {
        if (btn.innerText.trim() === recommended) {
            selectSize(recommended, btn);
        }
    });
}

function submitProductQuestion(form) {
    const btn = document.getElementById('submitQuestionBtn');
    btn.disabled = true;
    const formData = new FormData(form);

    fetch('/question/submit', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('askQuestionModal')).hide();
            showToast(data.message, 'success');
            form.reset();
        } else {
            showToast(data.message || 'Error submitting question.', 'danger');
        }
    })
    .catch(() => showToast('Error submitting question.', 'danger'))
    .finally(() => btn.disabled = false);
}

function submitProductReview(form) {
    const btn = document.getElementById('submitReviewBtn');
    btn.disabled = true;
    const formData = new FormData(form);

    fetch('/review/submit', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('writeReviewModal')).hide();
            showToast(data.message, 'success');
            if (window.trackAnalytics) {
                window.trackAnalytics('review_submit', {
                    item_id: <?= (int)$product['id'] ?>,
                    item_name: <?= json_encode($product['name']) ?>
                });
            }
            form.reset();
        } else {
            showToast(data.message || 'Submission error', 'danger');
        }
    })
    .catch(() => showToast('Error submitting review.', 'danger'))
    .finally(() => btn.disabled = false);
}

// Feature 21 Sales Funnel: view_item & add_to_cart
document.addEventListener('DOMContentLoaded', function() {
    if (window.trackAnalytics) {
        window.trackAnalytics('view_item', {
            item_id: <?= (int)$product['id'] ?>,
            item_name: <?= json_encode($product['name']) ?>,
            price: <?= (float)($product['sale_price'] ?? $product['price']) ?>,
            item_category: <?= json_encode($product['category_name'] ?? 'Intimate Care') ?>
        });
    }

    const atcForm = document.getElementById('addToCartForm');
    if (atcForm) {
        atcForm.addEventListener('submit', function() {
            if (window.trackAnalytics) {
                window.trackAnalytics('add_to_cart', {
                    item_id: <?= (int)$product['id'] ?>,
                    item_name: <?= json_encode($product['name']) ?>,
                    price: <?= (float)($product['sale_price'] ?? $product['price']) ?>,
                    quantity: parseInt(document.getElementById('prodQtyInput')?.value || 1)
                });
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
