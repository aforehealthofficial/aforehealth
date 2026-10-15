<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('admin/products') ?>" class="small text-muted text-decoration-none">← Back to Products</a>
        <h4 class="fw-bold m-0 text-dark">Add New Product</h4>
    </div>
</div>

<form action="<?= url('admin/products/store') ?>" method="POST" enctype="multipart/form-data" id="createProductForm">
    <div class="row g-4">
        
        <!-- Left Column: Details & Content -->
        <div class="col-lg-8">
            <!-- 1. Basic Info -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-info-circle me-1 text-primary"></i> Basic Product Information</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Product Name *</label>
                    <input type="text" name="name" id="prodNameInput" class="form-control" required placeholder="e.g. AFOREHEALTH UltraDry Heavy Flow Period Panty" oninput="autoGenerateSku(this.value)">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SKU Code *</label>
                        <input type="text" name="sku" id="prodSkuInput" class="form-control" required placeholder="AFH-PP-099" style="text-transform: uppercase;">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Primary Category *</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Short Description *</label>
                    <textarea name="short_description" class="form-control" rows="2" placeholder="Brief summary of benefits for product card" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Detailed Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Detailed product description, fabric benefits, absorbency science..."></textarea>
                </div>
            </div>

            <!-- 2. Product Images (Primary & Gallery) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-images me-1 text-primary"></i> Product Media &amp; Images</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Primary Product Image *</label>
                        <div class="border rounded-3 p-3 text-center bg-light position-relative">
                            <div class="mb-2">
                                <img id="primaryImgPreview" src="<?= upload_url('products/cat_period_panties.svg') ?>" class="rounded-3 border shadow-sm bg-white" style="width: 120px; height: 120px; object-fit: contain;" alt="Primary Preview">
                            </div>
                            <input type="file" name="primary_image" id="primaryImgInput" class="form-control form-control-sm" accept="image/*" onchange="previewImg(this, 'primaryImgPreview')">
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Main catalog thumbnail (JPG, PNG, WebP or SVG)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Additional Gallery Images</label>
                        <div class="border rounded-3 p-3 text-center bg-light position-relative">
                            <div id="galleryPreviews" class="d-flex flex-wrap gap-2 justify-content-center mb-2" style="min-height: 120px; align-items: center;">
                                <div class="text-muted small"><i class="bi bi-cloud-arrow-up fs-2 d-block text-secondary"></i> Select multiple images</div>
                            </div>
                            <input type="file" name="gallery_images[]" id="galleryImgInput" class="form-control form-control-sm" accept="image/*" multiple onchange="previewGalleryImgs(this)">
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Select 1 or more angle/gusset photos</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Sizes & Colors (Variant Setup) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h6 class="fw-bold m-0 text-dark"><i class="bi bi-palette me-1 text-primary"></i> Available Colors &amp; Sizes</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="generateVariantMatrix()">
                        <i class="bi bi-arrow-repeat me-1"></i> Generate Combinations
                    </button>
                </div>

                <!-- Colors Selection -->
                <div class="mb-4">
                    <label class="form-label small fw-bold d-block">1. Select Product Colors</label>
                    <div class="d-flex flex-wrap gap-2 mb-3" id="colorPillsContainer">
                        <?php 
                        $presetColors = [
                            ['name' => 'Classic Black', 'code' => '#1F1E24', 'checked' => true],
                            ['name' => 'Nude Beige', 'code' => '#D8B7A4', 'checked' => true],
                            ['name' => 'Dusty Rose', 'code' => '#C98A9C', 'checked' => false],
                            ['name' => 'Lavender Mist', 'code' => '#9C8EB9', 'checked' => false],
                            ['name' => 'Deep Wine', 'code' => '#6B1724', 'checked' => false],
                            ['name' => 'Midnight Blue', 'code' => '#1E293B', 'checked' => false],
                            ['name' => 'Pure White', 'code' => '#FFFFFF', 'checked' => false],
                            ['name' => 'Sage Green', 'code' => '#7B9E87', 'checked' => false],
                        ];
                        foreach ($presetColors as $cIdx => $pc): 
                        ?>
                            <label class="btn btn-sm border rounded-pill px-3 py-1 d-flex align-items-center gap-2 cursor-pointer shadow-none color-toggle-label <?= $pc['checked'] ? 'btn-light border-dark' : 'btn-white' ?>">
                                <input type="checkbox" class="form-check-input m-0 color-checkbox" value="<?= e($pc['name']) ?>" data-code="<?= $pc['code'] ?>" <?= $pc['checked'] ? 'checked' : '' ?> onchange="this.parentElement.classList.toggle('border-dark', this.checked)">
                                <span class="rounded-circle border" style="width: 14px; height: 14px; background-color: <?= $pc['code'] ?>;"></span>
                                <span class="small fw-semibold"><?= e($pc['name']) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Add Custom Color Form -->
                    <div class="row g-2 align-items-center" style="max-width: 480px;">
                        <div class="col-6">
                            <input type="text" id="customColorName" class="form-control form-control-sm" placeholder="Custom color name (e.g. Mauve)">
                        </div>
                        <div class="col-3 d-flex align-items-center gap-1">
                            <input type="color" id="customColorPicker" class="form-control form-control-color form-control-sm w-100" value="#8B5E83" title="Choose color">
                        </div>
                        <div class="col-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 fw-bold" onclick="addCustomColor()">+ Add</button>
                        </div>
                    </div>
                </div>

                <!-- Sizes Selection -->
                <div class="mb-4">
                    <label class="form-label small fw-bold d-block">2. Select Available Sizes</label>
                    <div class="d-flex flex-wrap gap-2 mb-3" id="sizePillsContainer">
                        <?php 
                        $presetSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'Free Size'];
                        $defaultChecked = ['S', 'M', 'L', 'XL', 'XXL'];
                        foreach ($presetSizes as $sz):
                            $isChecked = in_array($sz, $defaultChecked);
                        ?>
                            <label class="btn btn-sm border rounded-pill px-3 py-1 d-flex align-items-center gap-2 cursor-pointer shadow-none <?= $isChecked ? 'btn-light border-dark' : 'btn-white' ?>">
                                <input type="checkbox" class="form-check-input m-0 size-checkbox" value="<?= $sz ?>" <?= $isChecked ? 'checked' : '' ?> onchange="this.parentElement.classList.toggle('border-dark', this.checked)">
                                <span class="small fw-bold"><?= $sz ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Add Custom Size -->
                    <div class="d-flex gap-2" style="max-width: 320px;">
                        <input type="text" id="customSizeInput" class="form-control form-control-sm" placeholder="Custom size (e.g. 4XL or Teen)">
                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold text-nowrap" onclick="addCustomSize()">+ Add</button>
                    </div>
                </div>

                <!-- 3. Generated Variant Table -->
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold m-0">3. Specific Variant Details (SKU, Stock &amp; Pricing)</label>
                        <small class="text-muted" id="variantCountNotice">Click 'Generate Combinations' to preview variants</small>
                    </div>
                    <div class="table-responsive border rounded-3 bg-light">
                        <table class="table table-sm align-middle mb-0 bg-white" id="variantsTable">
                            <thead class="table-light small">
                                <tr>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Variant SKU</th>
                                    <th style="width: 110px;">Stock</th>
                                    <th style="width: 120px;">Price (₹)</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="variantsTableBody">
                                <!-- Populated by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 4. Material & Care -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-shield-check me-1 text-primary"></i> Fabric, Hygiene &amp; Care Details</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Material Composition</label>
                    <input type="text" name="material" class="form-control" placeholder="e.g. Body: 95% Organic Bamboo Cotton, 5% Spandex. Gusset: 4-Layer Antibacterial">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Washing Instructions</label>
                    <input type="text" name="care_instructions" class="form-control" placeholder="e.g. Cold water rinse, machine wash gentle, shade dry. No bleach.">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Hygiene Notice / Restrictions</label>
                    <input type="text" name="hygiene_notice" class="form-control" placeholder="e.g. Sealed tamper-evident packaging. Eligible for 7-day size exchange if unopened.">
                </div>
            </div>
        </div>

        <!-- Right Column: Pricing & Badges -->
        <div class="col-lg-4">
            
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-currency-rupee me-1 text-primary"></i> Master Pricing &amp; Stock</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Selling Price (INR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="1" name="price" id="masterPriceInput" class="form-control" required placeholder="849" oninput="syncPricesToVariants()">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">MRP (Original Price) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="1" name="mrp" id="masterMrpInput" class="form-control" required placeholder="1199">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Total Stock Quantity *</label>
                    <input type="number" name="stock" id="masterStockInput" class="form-control" value="100" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Will automatically adjust if variant quantities are specified.</small>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-tag me-1 text-primary"></i> Badges &amp; Placement</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_bestseller" value="1" id="chkBest">
                    <label class="form-check-label small" for="chkBest">Mark as Bestseller</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="chkFeat" checked>
                    <label class="form-check-label small" for="chkFeat">Feature on Homepage</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_new_arrival" value="1" id="chkNew" checked>
                    <label class="form-check-label small" for="chkNew">Mark as New Arrival</label>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-afh-primary py-2 fw-bold shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Publish Product
                    </button>
                </div>
            </div>

        </div>

    </div>
</form>

<script>
function previewImg(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewGalleryImgs(input) {
    const container = document.getElementById('galleryPreviews');
    container.innerHTML = '';
    if (input.files && input.files.length > 0) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'rounded-2 border p-1 shadow-sm bg-white';
                img.style.width = '55px';
                img.style.height = '55px';
                img.style.objectFit = 'cover';
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    } else {
        container.innerHTML = '<div class="text-muted small"><i class="bi bi-cloud-arrow-up fs-2 d-block text-secondary"></i> Select multiple images</div>';
    }
}

function autoGenerateSku(name) {
    const skuInput = document.getElementById('prodSkuInput');
    if (!skuInput.dataset.manual && name.trim().length > 3) {
        const words = name.trim().split(/\s+/);
        let prefix = words.map(w => w[0]).join('').substring(0, 4).toUpperCase();
        if (prefix.length < 3) prefix = 'AFH';
        const rand = Math.floor(100 + Math.random() * 900);
        skuInput.value = 'AFH-' + prefix + '-' + rand;
    }
}
document.getElementById('prodSkuInput').addEventListener('input', function() {
    this.dataset.manual = '1';
});

function addCustomColor() {
    const nameInput = document.getElementById('customColorName');
    const colorPicker = document.getElementById('customColorPicker');
    const name = nameInput.value.trim();
    const code = colorPicker.value;

    if (!name) {
        alert('Please enter a color name.');
        return;
    }

    const container = document.getElementById('colorPillsContainer');
    const label = document.createElement('label');
    label.className = 'btn btn-sm border rounded-pill px-3 py-1 d-flex align-items-center gap-2 cursor-pointer shadow-none btn-light border-dark';
    label.innerHTML = `
        <input type="checkbox" class="form-check-input m-0 color-checkbox" value="${name}" data-code="${code}" checked onchange="this.parentElement.classList.toggle('border-dark', this.checked)">
        <span class="rounded-circle border" style="width: 14px; height: 14px; background-color: ${code};"></span>
        <span class="small fw-semibold">${name}</span>
    `;
    container.appendChild(label);
    nameInput.value = '';
    generateVariantMatrix();
}

function addCustomSize() {
    const sizeInput = document.getElementById('customSizeInput');
    const size = sizeInput.value.trim().toUpperCase();

    if (!size) {
        alert('Please enter a size.');
        return;
    }

    const container = document.getElementById('sizePillsContainer');
    const label = document.createElement('label');
    label.className = 'btn btn-sm border rounded-pill px-3 py-1 d-flex align-items-center gap-2 cursor-pointer shadow-none btn-light border-dark';
    label.innerHTML = `
        <input type="checkbox" class="form-check-input m-0 size-checkbox" value="${size}" checked onchange="this.parentElement.classList.toggle('border-dark', this.checked)">
        <span class="small fw-bold">${size}</span>
    `;
    container.appendChild(label);
    sizeInput.value = '';
    generateVariantMatrix();
}

function generateVariantMatrix() {
    const tbody = document.getElementById('variantsTableBody');
    tbody.innerHTML = '';

    const selectedColors = [];
    document.querySelectorAll('.color-checkbox:checked').forEach(cb => {
        selectedColors.push({
            name: cb.value,
            code: cb.dataset.code || '#1F1E24'
        });
    });

    const selectedSizes = [];
    document.querySelectorAll('.size-checkbox:checked').forEach(sb => {
        selectedSizes.push(sb.value.trim());
    });

    const baseSku = (document.getElementById('prodSkuInput').value.trim() || 'AFH-PROD').toUpperCase();
    const basePrice = document.getElementById('masterPriceInput').value || '849';
    const baseMrp = document.getElementById('masterMrpInput').value || '1199';
    const totalMasterStock = parseInt(document.getElementById('masterStockInput').value) || 100;

    if (selectedColors.length === 0 || selectedSizes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Please select at least 1 color and 1 size.</td></tr>';
        document.getElementById('variantCountNotice').innerText = '0 variants selected';
        return;
    }

    const totalCombinations = selectedColors.length * selectedSizes.length;
    const perStock = Math.max(5, Math.floor(totalMasterStock / totalCombinations));
    let vIdx = 0;

    selectedColors.forEach(c => {
        const colAbbr = c.name.replace(/[^a-zA-Z]/g, '').substring(0, 3).toUpperCase();
        selectedSizes.forEach(s => {
            const vSku = `${baseSku}-${colAbbr}-${s}`;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle border" style="width: 14px; height: 14px; background-color: ${c.code};"></span>
                        <span class="small fw-semibold">${c.name}</span>
                        <input type="hidden" name="variants[${vIdx}][color]" value="${c.name}">
                        <input type="hidden" name="variants[${vIdx}][color_code]" value="${c.code}">
                    </div>
                </td>
                <td>
                    <span class="badge bg-light text-dark fw-bold border">${s}</span>
                    <input type="hidden" name="variants[${vIdx}][size]" value="${s}">
                </td>
                <td>
                    <input type="text" name="variants[${vIdx}][sku]" class="form-control form-control-sm font-monospace" value="${vSku}" style="text-transform: uppercase;">
                </td>
                <td>
                    <input type="number" name="variants[${vIdx}][stock]" class="form-control form-control-sm variant-stock-input" value="${perStock}" min="0">
                </td>
                <td>
                    <input type="number" name="variants[${vIdx}][price]" class="form-control form-control-sm variant-price-input" value="${basePrice}">
                    <input type="hidden" name="variants[${vIdx}][mrp]" value="${baseMrp}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('tr').remove(); updateVariantCountNotice();" title="Remove"><i class="bi bi-x-circle"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
            vIdx++;
        });
    });

    updateVariantCountNotice();
}

function updateVariantCountNotice() {
    const count = document.querySelectorAll('#variantsTableBody tr').length;
    document.getElementById('variantCountNotice').innerText = `${count} variants generated`;
}

function syncPricesToVariants() {
    const basePrice = document.getElementById('masterPriceInput').value;
    document.querySelectorAll('.variant-price-input').forEach(inp => {
        inp.value = basePrice;
    });
}

// Initial generation on load
window.addEventListener('DOMContentLoaded', () => {
    generateVariantMatrix();
});
</script>

<?php require __DIR__ . '/../layout-footer.php'; ?>
