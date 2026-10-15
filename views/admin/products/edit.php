<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('admin/products') ?>" class="small text-muted text-decoration-none">← Back to Products</a>
        <h4 class="fw-bold m-0 text-dark">Edit Product #<?= $product['id'] ?></h4>
    </div>
    <a href="<?= url('product/' . $product['slug']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
        <i class="bi bi-box-arrow-up-right me-1"></i> Preview on Storefront
    </a>
</div>

<form action="<?= url('admin/products/update/' . $product['id']) ?>" method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- 1. Basic Info -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-info-circle me-1 text-primary"></i> Basic Product Information</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Product Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SKU Code</label>
                        <input type="text" class="form-control bg-light font-monospace" value="<?= e($product['sku']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Category *</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Short Description *</label>
                    <textarea name="short_description" class="form-control" rows="2" required><?= e($product['short_description']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Detailed Description</label>
                    <textarea name="description" class="form-control" rows="5"><?= e($product['description']) ?></textarea>
                </div>
            </div>

            <!-- 2. Product Media (Primary & Gallery Management) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-images me-1 text-primary"></i> Product Media &amp; Gallery</h6>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Primary Catalog Image</label>
                        <div class="border rounded-3 p-3 text-center bg-light">
                            <div class="mb-2">
                                <img id="editPrimaryPreview" src="<?= upload_url('products/' . ($product['primary_image'] ?: 'cat_period_panties.svg')) ?>" class="rounded-3 border shadow-sm bg-white" style="width: 110px; height: 110px; object-fit: contain;" alt="Primary Image">
                            </div>
                            <input type="file" name="primary_image" class="form-control form-control-sm" accept="image/*" onchange="previewImg(this, 'editPrimaryPreview')">
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Upload new photo to replace current primary image</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Add More Gallery Images</label>
                        <div class="border rounded-3 p-3 text-center bg-light">
                            <div id="newGalleryPreviews" class="d-flex flex-wrap gap-2 justify-content-center mb-2" style="min-height: 110px; align-items: center;">
                                <div class="text-muted small"><i class="bi bi-cloud-plus fs-2 d-block text-secondary"></i> Select photos to append</div>
                            </div>
                            <input type="file" name="gallery_images[]" class="form-control form-control-sm" accept="image/*" multiple onchange="previewGalleryImgs(this)">
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Supports multiple image selection</small>
                        </div>
                    </div>
                </div>

                <!-- Existing Gallery Photos Grid -->
                <?php if (!empty($images)): ?>
                    <div class="border-top pt-3">
                        <label class="form-label small fw-bold text-dark d-block mb-2">Existing Gallery Images (<?= count($images) ?>):</label>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach ($images as $img): ?>
                                <div class="position-relative border rounded-3 p-1 bg-white shadow-sm text-center" style="width: 100px;">
                                    <img src="<?= upload_url('products/' . $img['image_path']) ?>" alt="<?= e($product['name']) ?> Gallery" style="width: 88px; height: 88px; object-fit: cover;" class="rounded">
                                    <div class="form-check text-start mt-1 ms-1">
                                        <input class="form-check-input" type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>" id="delImg<?= $img['id'] ?>">
                                        <label class="form-check-label text-danger" style="font-size: 0.7rem;" for="delImg<?= $img['id'] ?>">Delete</label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- 3. Existing Variants Manager (Editable) -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                    <h6 class="fw-bold m-0 text-dark"><i class="bi bi-palette me-1 text-primary"></i> Product Variants &amp; Inventory (<?= count($variants) ?>)</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="addNewVariantRow()">
                        <i class="bi bi-plus-circle me-1"></i> Add Variant
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm align-middle small mb-0" id="existingVariantsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Variant SKU</th>
                                <th>Color Name</th>
                                <th style="width: 60px;">Code</th>
                                <th style="width: 85px;">Size</th>
                                <th style="width: 95px;">Price (₹)</th>
                                <th style="width: 95px;">MRP (₹)</th>
                                <th style="width: 80px;">Stock</th>
                                <th style="width: 90px;">Status</th>
                                <th style="width: 60px;" class="text-center">Delete</th>
                            </tr>
                        </thead>
                        <tbody id="existingVariantsBody">
                            <?php foreach ($variants as $v): ?>
                                <tr>
                                    <td>
                                        <input type="text" name="existing_variants[<?= $v['id'] ?>][sku]" class="form-control form-control-sm font-monospace" value="<?= e($v['sku']) ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="existing_variants[<?= $v['id'] ?>][color]" class="form-control form-control-sm" value="<?= e($v['color']) ?>">
                                    </td>
                                    <td>
                                        <input type="color" name="existing_variants[<?= $v['id'] ?>][color_code]" class="form-control form-control-color form-control-sm p-0 border-0" value="<?= $v['color_code'] ?: '#1F1E24' ?>">
                                    </td>
                                    <td>
                                        <input type="text" name="existing_variants[<?= $v['id'] ?>][size]" class="form-control form-control-sm text-center fw-bold" value="<?= e($v['size']) ?>" style="text-transform: uppercase;">
                                    </td>
                                    <td>
                                        <input type="number" name="existing_variants[<?= $v['id'] ?>][price]" class="form-control form-control-sm" value="<?= (int)$v['price'] ?>">
                                    </td>
                                    <td>
                                        <input type="number" name="existing_variants[<?= $v['id'] ?>][mrp]" class="form-control form-control-sm" value="<?= (int)$v['mrp'] ?>">
                                    </td>
                                    <td>
                                        <input type="number" name="existing_variants[<?= $v['id'] ?>][stock]" class="form-control form-control-sm text-center" value="<?= (int)$v['stock'] ?>">
                                    </td>
                                    <td>
                                        <select name="existing_variants[<?= $v['id'] ?>][status]" class="form-select form-select-sm">
                                            <option value="active" <?= $v['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= $v['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="delete_variants[]" value="<?= $v['id'] ?>" class="form-check-input" title="Check to delete this variant">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">Check "Delete" box on any row to delete that variant upon saving.</small>
            </div>

            <!-- 4. Specifications -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-shield-check me-1 text-primary"></i> Specifications &amp; Care Details</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Material Composition</label>
                    <input type="text" name="material" class="form-control" value="<?= e($product['material']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Washing &amp; Care Instructions</label>
                    <input type="text" name="care_instructions" class="form-control" value="<?= e($product['care_instructions']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Hygiene Notice</label>
                    <input type="text" name="hygiene_notice" class="form-control" value="<?= e($product['hygiene_notice']) ?>">
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-currency-rupee me-1 text-primary"></i> Master Pricing &amp; Stock</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Selling Price (INR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="1" name="price" id="editMasterPrice" class="form-control" value="<?= (int)$product['price'] ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">MRP (INR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="1" name="mrp" id="editMasterMrp" class="form-control" value="<?= (int)$product['mrp'] ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Master Stock Count *</label>
                    <input type="number" name="stock" class="form-control" value="<?= (int)$product['stock'] ?>" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Auto-synced with active variant stocks</small>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Publishing Status</label>
                    <select name="status" class="form-select">
                        <option value="published" <?= $product['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="archived" <?= $product['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="bi bi-tag me-1 text-primary"></i> Badges &amp; Placement</h6>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_bestseller" value="1" id="eBest" <?= !empty($product['is_bestseller']) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="eBest">Mark as Bestseller</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="eFeat" <?= !empty($product['is_featured']) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="eFeat">Feature on Homepage</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="is_new_arrival" value="1" id="eNew" <?= !empty($product['is_new_arrival']) ? 'checked' : '' ?>>
                    <label class="form-check-label small" for="eNew">Mark as New Arrival</label>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-afh-primary py-2 fw-bold shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Update Product &amp; Variants
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
    const container = document.getElementById('newGalleryPreviews');
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
        container.innerHTML = '<div class="text-muted small"><i class="bi bi-cloud-plus fs-2 d-block text-secondary"></i> Select photos to append</div>';
    }
}

let newVarCounter = 0;
function addNewVariantRow() {
    const tbody = document.getElementById('existingVariantsBody');
    const tr = document.createElement('tr');
    tr.className = 'table-warning-subtle';
    const defPrice = document.getElementById('editMasterPrice').value || 849;
    const defMrp = document.getElementById('editMasterMrp').value || 1199;

    tr.innerHTML = `
        <td>
            <input type="text" name="new_variants[${newVarCounter}][sku]" class="form-control form-control-sm font-monospace" placeholder="AUTO-SKU" style="text-transform: uppercase;">
        </td>
        <td>
            <input type="text" name="new_variants[${newVarCounter}][color]" class="form-control form-control-sm" placeholder="e.g. Classic Black" required>
        </td>
        <td>
            <input type="color" name="new_variants[${newVarCounter}][color_code]" class="form-control form-control-color form-control-sm p-0 border-0" value="#1F1E24">
        </td>
        <td>
            <input type="text" name="new_variants[${newVarCounter}][size]" class="form-control form-control-sm text-center fw-bold" placeholder="M" style="text-transform: uppercase;" required>
        </td>
        <td>
            <input type="number" name="new_variants[${newVarCounter}][price]" class="form-control form-control-sm" value="${defPrice}">
        </td>
        <td>
            <input type="number" name="new_variants[${newVarCounter}][mrp]" class="form-control form-control-sm" value="${defMrp}">
        </td>
        <td>
            <input type="number" name="new_variants[${newVarCounter}][stock]" class="form-control form-control-sm text-center" value="20">
        </td>
        <td>
            <span class="badge bg-success">New</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="this.closest('tr').remove()"><i class="bi bi-x-circle"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    newVarCounter++;
}
</script>

<?php require __DIR__ . '/../layout-footer.php'; ?>
