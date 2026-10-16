<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4 py-lg-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">

            <!-- Quiz Card Container -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                
                <!-- Progress Header -->
                <div class="p-4 border-bottom text-center position-relative" style="background: linear-gradient(135deg, #FFF9FA 0%, #F8ECF3 100%);">
                    <span class="badge bg-white text-primary border px-3 py-1.5 rounded-pill fw-bold mb-2 shadow-sm">
                        ✨ 60-Second Fit &amp; Flow Recommender
                    </span>
                    <h3 class="fw-bold text-dark m-0">Find Your Perfect Period Panty</h3>
                    <p class="text-muted small mb-3">Engineered for Indian body proportions and tropical absorbency needs.</p>
                    
                    <!-- Progress Bar -->
                    <div class="progress" style="height: 6px; max-width: 380px; margin: 0 auto;">
                        <div id="quizProgressBar" class="progress-bar bg-primary" role="progressbar" style="width: 33%; transition: width 0.3s ease;"></div>
                    </div>
                </div>

                <!-- Form Steps Container -->
                <div class="p-4 p-md-5">

                    <!-- STEP 1: Flow Profile -->
                    <div class="quiz-step" id="quizStep1">
                        <div class="text-center mb-4">
                            <span class="text-muted small fw-bold text-uppercase">Question 1 of 3</span>
                            <h4 class="fw-bold text-dark mt-1">What is your typical flow intensity?</h4>
                            <p class="text-muted small">Choose the days you need protection for.</p>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <label class="quiz-option-card border rounded-4 p-3 d-flex align-items-center gap-3 cursor-pointer shadow-sm">
                                <input type="radio" name="flow" value="light" class="form-check-input flex-shrink-0 fs-5 m-0" onchange="nextStep(2)">
                                <div class="fs-2 text-danger p-2 bg-light rounded-3">🌸</div>
                                <div>
                                    <strong class="d-block text-dark">Light Flow &amp; Daily Spotting</strong>
                                    <small class="text-muted">For beginning/end of cycle, ovulation discharge, or light workout security (10-15ml).</small>
                                </div>
                            </label>

                            <label class="quiz-option-card border rounded-4 p-3 d-flex align-items-center gap-3 cursor-pointer shadow-sm">
                                <input type="radio" name="flow" value="medium" class="form-check-input flex-shrink-0 fs-5 m-0" onchange="nextStep(2)">
                                <div class="fs-2 text-danger p-2 bg-light rounded-3">🩸</div>
                                <div>
                                    <strong class="d-block text-dark">Regular / Moderate Flow</strong>
                                    <small class="text-muted">Standard daily flow. Absorbs up to 2-3 pads worth without wetness feeling (20-25ml).</small>
                                </div>
                            </label>

                            <label class="quiz-option-card border rounded-4 p-3 d-flex align-items-center gap-3 cursor-pointer shadow-sm border-primary" style="background: #FFFDFC;">
                                <input type="radio" name="flow" value="heavy" class="form-check-input flex-shrink-0 fs-5 m-0" checked onchange="nextStep(2)">
                                <div class="fs-2 text-danger p-2 bg-light rounded-3">🩸🩸</div>
                                <div>
                                    <strong class="d-block text-dark">Heavy Flow &amp; Overnight Security (Most Popular)</strong>
                                    <small class="text-muted">Heavy bleeding, postpartum, or peaceful 8-hour sleep with 4-layer lock core (35-40ml).</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- STEP 2: Fit & Coverage -->
                    <div class="quiz-step d-none" id="quizStep2">
                        <div class="text-center mb-4">
                            <span class="text-muted small fw-bold text-uppercase">Question 2 of 3</span>
                            <h4 class="fw-bold text-dark mt-1">Which waist rise &amp; fit do you prefer?</h4>
                            <p class="text-muted small">All designs have breathable bamboo cotton against sensitive skin.</p>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <label class="quiz-option-card border rounded-4 p-3 d-flex align-items-center gap-3 cursor-pointer shadow-sm">
                                <input type="radio" name="style" value="high_waist" class="form-check-input flex-shrink-0 fs-5 m-0" checked onchange="nextStep(3)">
                                <div class="fs-2 text-primary p-2 bg-light rounded-3"><i class="bi bi-shield-check"></i></div>
                                <div>
                                    <strong class="d-block text-dark">High-Waist Tummy Hug</strong>
                                    <small class="text-muted">Gentle core support, zero muffin-top, and extra gusset rise in back for leak protection.</small>
                                </div>
                            </label>

                            <label class="quiz-option-card border rounded-4 p-3 d-flex align-items-center gap-3 cursor-pointer shadow-sm">
                                <input type="radio" name="style" value="mid_rise" class="form-check-input flex-shrink-0 fs-5 m-0" onchange="nextStep(3)">
                                <div class="fs-2 text-primary p-2 bg-light rounded-3"><i class="bi bi-heart"></i></div>
                                <div>
                                    <strong class="d-block text-dark">Classic Mid-Rise Bikini</strong>
                                    <small class="text-muted">Everyday feel under low/mid jeans, suits, and daily casual wear.</small>
                                </div>
                            </label>

                            <label class="quiz-option-card border rounded-4 p-3 d-flex align-items-center gap-3 cursor-pointer shadow-sm">
                                <input type="radio" name="style" value="seamless" class="form-check-input flex-shrink-0 fs-5 m-0" onchange="nextStep(3)">
                                <div class="fs-2 text-primary p-2 bg-light rounded-3"><i class="bi bi-feather"></i></div>
                                <div>
                                    <strong class="d-block text-dark">Seamless Boy-short / No-Show</strong>
                                    <small class="text-muted">No visible panty lines under leggings, gym tights, and festive bodycon outfits.</small>
                                </div>
                            </label>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="nextStep(1)">← Back</button>
                        </div>
                    </div>

                    <!-- STEP 3: Size Selection -->
                    <div class="quiz-step d-none" id="quizStep3">
                        <div class="text-center mb-4">
                            <span class="text-muted small fw-bold text-uppercase">Question 3 of 3</span>
                            <h4 class="fw-bold text-dark mt-1">Select your standard underwear size</h4>
                            <p class="text-muted small">Tailored for Indian body proportions. Order regular size.</p>
                        </div>

                        <div class="row g-2 mb-4" id="quizSizeOptions">
                            <?php 
                            $sizes = [
                                ['s' => 'XS', 'hip' => '32 - 34 in'],
                                ['s' => 'S', 'hip' => '34 - 36 in'],
                                ['s' => 'M', 'hip' => '36 - 38 in'],
                                ['s' => 'L', 'hip' => '38 - 40 in'],
                                ['s' => 'XL', 'hip' => '40 - 42 in'],
                                ['s' => 'XXL', 'hip' => '42 - 44 in'],
                                ['s' => '3XL', 'hip' => '44 - 47 in'],
                            ];
                            foreach ($sizes as $idx => $sz): 
                            ?>
                                <div class="col-4 col-sm-3">
                                    <label class="btn btn-outline-secondary w-100 rounded-3 p-2 text-center size-select-btn <?= $sz['s'] === 'M' ? 'active border-primary text-primary' : '' ?>">
                                        <input type="radio" name="size" value="<?= $sz['s'] ?>" class="d-none" <?= $sz['s'] === 'M' ? 'checked' : '' ?>>
                                        <div class="fw-bold fs-5"><?= $sz['s'] ?></div>
                                        <div class="text-muted" style="font-size: 0.7rem;"><?= $sz['hip'] ?></div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" onclick="nextStep(2)">← Back</button>
                            <button type="button" class="btn btn-afh-primary rounded-pill px-4 fw-bold shadow-sm" onclick="calculateRecommendation()">
                                See My Recommended Match →
                            </button>
                        </div>
                    </div>

                    <!-- STEP 4: RESULT / RECOMMENDATION -->
                    <div class="quiz-step d-none text-center" id="quizStepResult">
                        <div class="badge bg-success-subtle text-success border px-3 py-1.5 rounded-pill fw-bold mb-2">
                            ✓ 99% Fit &amp; Absorbency Match Found!
                        </div>
                        <h4 class="fw-bold text-dark mb-1" id="recProdName">AFOREHEALTH 4-Layer Leakproof Panty</h4>
                        <p class="text-muted small mb-3" id="recAbsorbency">4-Layer Core (Absorbs up to 40ml / ~4 pads)</p>

                        <div class="card border rounded-4 p-3 bg-light mb-4 text-start">
                            <div class="row align-items-center g-3">
                                <div class="col-sm-4 text-center">
                                    <img id="recProdImg" src="" alt="Recommended Product" class="rounded-3 border shadow-sm bg-white" style="width: 120px; height: 120px; object-fit: contain;">
                                </div>
                                <div class="col-sm-8">
                                    <div class="d-flex gap-2 mb-2">
                                        <span class="badge bg-dark" id="recSizeBadge">Size: M</span>
                                        <span class="badge bg-primary" id="recFlowBadge">Heavy Flow</span>
                                    </div>
                                    <div class="d-flex align-items-baseline gap-2 mb-2">
                                        <span class="h4 fw-bold text-dark m-0" id="recPrice">₹799</span>
                                        <span class="text-muted text-decoration-line-through small" id="recMrp">₹1,199</span>
                                        <span class="badge bg-success text-white small">SAVE 33%</span>
                                    </div>
                                    <ul class="list-unstyled small text-muted mb-0">
                                        <li><i class="bi bi-check2 text-success me-1"></i> 100% Breathable Organic Cotton Core</li>
                                        <li><i class="bi bi-check2 text-success me-1"></i> Anti-Odor &amp; Zero Synthetic Fragrance</li>
                                        <li><i class="bi bi-check2 text-success me-1"></i> Free Discreet Shipping across India</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-afh-primary py-2.5 rounded-pill fw-bold shadow-sm fs-6" id="quizAddToCartBtn" onclick="addQuizItemToCart()">
                                <i class="bi bi-bag-plus me-1"></i> Add Matched Panty to Bag
                            </button>
                            <a id="recProdLink" href="#" class="btn btn-outline-secondary py-2 rounded-pill small">
                                View Full Product Details
                            </a>
                            <button type="button" class="btn btn-link text-muted btn-sm" onclick="nextStep(1)">
                                ↺ Retake Quiz
                            </button>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<style>
.quiz-option-card {
    transition: all 0.2s ease;
}
.quiz-option-card:hover {
    border-color: var(--afh-primary) !important;
    transform: translateY(-2px);
    background: #FFF9FA;
}
.size-select-btn.active {
    border-color: var(--afh-primary) !important;
    background-color: #FFF9FA !important;
    color: var(--afh-primary) !important;
}
</style>

<script>
let quizResultData = null;

function nextStep(stepNumber) {
    document.querySelectorAll('.quiz-step').forEach(el => el.classList.add('d-none'));
    const target = document.getElementById('quizStep' + stepNumber);
    if (target) {
        target.classList.remove('d-none');
    }
    const bar = document.getElementById('quizProgressBar');
    if (bar) {
        const pct = stepNumber === 1 ? '33%' : (stepNumber === 2 ? '66%' : (stepNumber === 3 ? '100%' : '100%'));
        bar.style.width = pct;
    }
}

document.querySelectorAll('#quizSizeOptions .size-select-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('#quizSizeOptions .size-select-btn').forEach(b => b.classList.remove('active', 'border-primary', 'text-primary'));
        this.classList.add('active', 'border-primary', 'text-primary');
        const inp = this.querySelector('input');
        if (inp) inp.checked = true;
    });
});

function calculateRecommendation() {
    const flow = document.querySelector('input[name="flow"]:checked')?.value || 'heavy';
    const style = document.querySelector('input[name="style"]:checked')?.value || 'high_waist';
    const size = document.querySelector('input[name="size"]:checked')?.value || 'M';

    const formData = new FormData();
    formData.append('flow', flow);
    formData.append('style', style);
    formData.append('size', size);

    fetch('/api/fit-quiz/recommend', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            quizResultData = data;
            document.getElementById('recProdName').innerText = data.product.name;
            document.getElementById('recAbsorbency').innerText = data.absorbency_text;
            document.getElementById('recProdImg').src = data.product.image_url;
            document.getElementById('recSizeBadge').innerText = 'Size: ' + data.size;
            document.getElementById('recFlowBadge').innerText = data.absorbency_badge;
            document.getElementById('recPrice').innerText = '₹' + data.product.price;
            document.getElementById('recMrp').innerText = '₹' + data.product.mrp;
            document.getElementById('recProdLink').href = '/product/' + data.product.slug;

            document.querySelectorAll('.quiz-step').forEach(el => el.classList.add('d-none'));
            document.getElementById('quizStepResult').classList.remove('d-none');
        }
    })
    .catch(() => {
        alert('Could not compute match. Please try again.');
    });
}

function addQuizItemToCart() {
    if (!quizResultData || !quizResultData.product) return;

    const btn = document.getElementById('quizAddToCartBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Adding...';

    const formData = new FormData();
    formData.append('product_id', quizResultData.product.id);
    if (quizResultData.product.variant_id) {
        formData.append('variant_id', quizResultData.product.variant_id);
    }
    formData.append('quantity', 1);

    fetch('/cart/add', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '✓ Added to Bag!';
        if (data.success) {
            // Update cart badge
            document.querySelectorAll('.cart-badge-count').forEach(b => {
                b.innerText = data.cart_count;
                b.style.display = data.cart_count > 0 ? '' : 'none';
            });
            // Open Cart Drawer
            if (typeof CartDrawer !== 'undefined' && CartDrawer.open) {
                CartDrawer.open();
            } else {
                window.location.href = '/checkout';
            }
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = 'Add to Bag';
        window.location.href = '/cart';
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
