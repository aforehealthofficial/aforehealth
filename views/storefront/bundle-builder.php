<?php require __DIR__ . '/../layouts/header.php'; ?>

<main class="py-5" style="background-color: var(--afh-bg);">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Build Your Period Kit</li>
            </ol>
        </nav>

        <?php if (!$activeBundle): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                <h4 class="fw-bold">No active bundle packages at this time.</h4>
                <p class="text-muted">Please check back soon or explore our pre-made kits.</p>
                <a href="<?= url('combos') ?>" class="btn btn-afh-primary rounded-pill px-4 mx-auto">Explore Pre-Made Kits</a>
            </div>
        <?php else: ?>
            <!-- Header Banner -->
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4 position-relative overflow-hidden">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <span class="badge rounded-pill bg-primary px-3 py-2 text-uppercase mb-2" style="background-color: var(--afh-primary) !important;">
                            ✨ Save <?= (int)$activeBundle['discount_value'] ?>% Instant Bundle Discount
                        </span>
                        <h1 class="h2 fw-bold text-dark mb-2"><?= e($activeBundle['name']) ?></h1>
                        <p class="text-muted mb-0"><?= e($activeBundle['description']) ?></p>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div class="d-inline-flex align-items-center gap-2 p-3 bg-light rounded-4 border">
                            <i class="bi bi-gift fs-2 text-primary" style="color: var(--afh-primary) !important;"></i>
                            <div class="text-start">
                                <small class="text-muted d-block">Includes Minimum</small>
                                <strong class="text-dark"><?= (int)$activeBundle['min_items'] ?> Custom Items</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Builder Form -->
            <form id="bundleBuilderForm" action="<?= url('bundle/add-to-cart') ?>" method="POST">
                <input type="hidden" name="bundle_id" value="<?= $activeBundle['id'] ?>">

                <div class="row g-4">
                    <!-- Left: Category Item Selectors -->
                    <div class="col-lg-8">
                        <?php foreach ($activeBundle['categories'] as $stepIdx => $cat): ?>
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle bg-dark text-white p-2" style="width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;">
                                            <?= $stepIdx + 1 ?>
                                        </span>
                                        <h5 class="fw-bold text-dark m-0">Step <?= $stepIdx + 1 ?>: Choose Your <?= e($cat['category_name']) ?></h5>
                                    </div>
                                    <span class="badge bg-light text-muted border">Select 1 Item</span>
                                </div>

                                <div class="row g-3">
                                    <?php foreach ($cat['products'] as $p): ?>
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3 h-100 bundle-card position-relative transition-all" id="card-prod-<?= $p['id'] ?>">
                                                <div class="d-flex gap-3">
                                                    <img src="<?= upload_url('products/' . ($p['primary_image'] ?? 'placeholder.svg')) ?>" 
                                                         class="rounded-3 border" style="width: 75px; height: 75px; object-fit: contain;" alt="<?= e($p['name']) ?>">
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold small mb-1 text-dark"><?= e($p['name']) ?></h6>
                                                        <div class="d-flex align-items-center gap-2 mb-2">
                                                            <strong class="text-primary small" style="color: var(--afh-primary) !important;">₹<?= number_format($p['price'], 0) ?></strong>
                                                            <?php if ($p['mrp'] > $p['price']): ?>
                                                                <small class="text-muted text-decoration-line-through">₹<?= number_format($p['mrp'], 0) ?></small>
                                                            <?php endif; ?>
                                                        </div>

                                                        <!-- Variant Selector -->
                                                        <?php if (!empty($p['variants'])): ?>
                                                            <div class="mb-2">
                                                                <select class="form-select form-select-sm bundle-variant-select" data-category="<?= $cat['category_id'] ?>" onchange="updateBundleSelection(this, <?= $cat['category_id'] ?>)">
                                                                    <option value="">-- Choose Size / Color --</option>
                                                                    <?php foreach ($p['variants'] as $v): ?>
                                                                        <option value="<?= $v['id'] ?>" data-price="<?= $v['price'] ?: $p['price'] ?>" data-mrp="<?= $v['mrp'] ?: $p['mrp'] ?>" data-title="<?= e($p['name'] . ' (' . ($v['color'] ? $v['color'] . ' ' : '') . $v['size'] . ')') ?>">
                                                                            <?= e(($v['color'] ? $v['color'] . ' - ' : '') . 'Size ' . $v['size']) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Right: Live Summary & Sticky Checkout Box -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 100px;">
                            <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">Your Custom Kit Summary</h5>

                            <div id="bundleItemsList" class="mb-3">
                                <p class="text-muted small mb-0 fst-italic">Select items from the steps on the left to build your kit...</p>
                            </div>

                            <div class="border-top pt-3 mb-3">
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Individual Retail Total:</span>
                                    <span id="summaryIndividualTotal">₹0</span>
                                </div>
                                <div class="d-flex justify-content-between text-success small mb-2 fw-bold">
                                    <span>Bundle Discount (<?= (int)$activeBundle['discount_value'] ?>%):</span>
                                    <span id="summaryDiscountAmount">-₹0</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2 text-dark">
                                    <span>Bundle Final Price:</span>
                                    <span id="summaryFinalPrice" style="color: var(--afh-primary);">₹0</span>
                                </div>
                                <div class="badge bg-success-subtle text-success w-100 py-2 mt-2 fw-bold" id="summarySavingsBadge" style="display: none;">
                                    Total Savings: <span id="summarySavings">₹0</span>
                                </div>
                            </div>

                            <button type="submit" id="bundleSubmitBtn" class="btn btn-afh-primary w-100 fw-bold py-3 rounded-pill shadow-sm" disabled>
                                Select Required Items to Add
                            </button>

                            <p class="text-muted small text-center mt-3 mb-0">
                                <i class="bi bi-shield-check text-success me-1"></i> 100% Discreet Packaging Guaranteed
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<script>
let selectedVariants = {};

function updateBundleSelection(selectElem, categoryId) {
    const val = selectElem.value;
    
    // Clear other selects in same category step
    document.querySelectorAll(`.bundle-variant-select[data-category="${categoryId}"]`).forEach(el => {
        if (el !== selectElem) el.value = '';
    });

    if (val) {
        const opt = selectElem.options[selectElem.selectedIndex];
        selectedVariants[categoryId] = {
            variant_id: parseInt(val),
            price: parseFloat(opt.dataset.price),
            mrp: parseFloat(opt.dataset.mrp),
            title: opt.dataset.title
        };
    } else {
        delete selectedVariants[categoryId];
    }

    renderBundleSummary();
}

function renderBundleSummary() {
    const container = document.getElementById('bundleItemsList');
    const items = Object.values(selectedVariants);
    const minItems = <?= (int)($activeBundle['min_items'] ?? 3) ?>;
    const discountPercent = <?= (float)($activeBundle['discount_value'] ?? 20) ?>;

    if (items.length === 0) {
        container.innerHTML = '<p class="text-muted small mb-0 fst-italic">Select items from the steps on the left to build your kit...</p>';
        document.getElementById('summaryIndividualTotal').innerText = '₹0';
        document.getElementById('summaryDiscountAmount').innerText = '-₹0';
        document.getElementById('summaryFinalPrice').innerText = '₹0';
        document.getElementById('summarySavingsBadge').style.display = 'none';
        document.getElementById('bundleSubmitBtn').disabled = true;
        document.getElementById('bundleSubmitBtn').innerText = `Select at least ${minItems} items to complete kit`;
        return;
    }

    let html = '<ul class="list-unstyled mb-0">';
    let individualTotal = 0;
    let mrpTotal = 0;

    // Clear and build hidden inputs in form
    const form = document.getElementById('bundleBuilderForm');
    form.querySelectorAll('input[name="variant_ids[]"]').forEach(el => el.remove());

    items.forEach(it => {
        individualTotal += it.price;
        mrpTotal += it.mrp;
        html += `
            <li class="d-flex justify-content-between small text-dark mb-1">
                <span class="text-truncate me-2">✓ ${it.title}</span>
                <span class="fw-bold">₹${it.price.toFixed(0)}</span>
            </li>
        `;
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'variant_ids[]';
        hiddenInput.value = it.variant_id;
        form.appendChild(hiddenInput);
    });
    html += '</ul>';
    container.innerHTML = html;

    const discountAmount = Math.round(individualTotal * (discountPercent / 100));
    const finalPrice = Math.max(0, individualTotal - discountAmount);
    const totalSavings = Math.round(individualTotal - finalPrice);

    document.getElementById('summaryIndividualTotal').innerText = `₹${individualTotal.toFixed(0)}`;
    document.getElementById('summaryDiscountAmount').innerText = `-₹${discountAmount.toFixed(0)}`;
    document.getElementById('summaryFinalPrice').innerText = `₹${finalPrice.toFixed(0)}`;
    
    if (totalSavings > 0) {
        document.getElementById('summarySavingsBadge').style.display = 'block';
        document.getElementById('summarySavings').innerText = `₹${totalSavings.toFixed(0)}`;
    }

    const btn = document.getElementById('bundleSubmitBtn');
    if (items.length >= minItems) {
        btn.disabled = false;
        btn.innerText = `Add Complete Kit to Bag (₹${finalPrice.toFixed(0)})`;
    } else {
        btn.disabled = true;
        btn.innerText = `Select ${minItems - items.length} more item(s)`;
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
