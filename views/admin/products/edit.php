<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('admin/products') ?>" class="small text-muted text-decoration-none">← Back to Products</a>
        <h4 class="fw-bold m-0 text-dark">Edit Product #<?= $product['id'] ?></h4>
    </div>
    <a href="<?= url('product/' . $product['slug']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-box-arrow-up-right me-1"></i> Preview on Storefront
    </a>
</div>

<form action="<?= url('admin/products/update/' . $product['id']) ?>" method="POST">
    <div class="row g-4">
        
        <!-- Left Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Basic Product Information</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Product Name *</label>
                    <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SKU Code</label>
                        <input type="text" class="form-control bg-light" value="<?= e($product['sku']) ?>" disabled>
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
                    <textarea name="description" class="form-control" rows="6"><?= e($product['description']) ?></textarea>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Specifications &amp; Care Details</h6>
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

            <!-- Existing Variants Table -->
            <?php if (!empty($variants)): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Product Variants (<?= count($variants) ?>)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle small mb-0">
                            <thead>
                                <tr>
                                    <th>Variant SKU</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Stock</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($variants as $v): ?>
                                    <tr>
                                        <td><code><?= e($v['sku']) ?></code></td>
                                        <td><?= e($v['color']) ?></td>
                                        <td><strong><?= e($v['size']) ?></strong></td>
                                        <td><?= (int)$v['stock'] ?></td>
                                        <td>₹<?= number_format($v['price'], 0) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Pricing &amp; Inventory</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Selling Price (INR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="1" name="price" class="form-control" value="<?= (int)$product['price'] ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">MRP (INR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="1" name="mrp" class="form-control" value="<?= (int)$product['mrp'] ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Master Stock Count *</label>
                    <input type="number" name="stock" class="form-control" value="<?= (int)$product['stock'] ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Publishing Status</label>
                    <select name="status" class="form-select">
                        <option value="published" <?= $product['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                    </select>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Badges &amp; Placement</h6>
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
                    <button type="submit" class="btn btn-afh-primary py-2 fw-bold">Update Product</button>
                </div>
            </div>
        </div>

    </div>
</form>

<?php require __DIR__ . '/../layout-footer.php'; ?>
