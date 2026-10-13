<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('admin/products') ?>" class="small text-muted text-decoration-none">← Back to Products</a>
        <h4 class="fw-bold m-0 text-dark">Add New Product</h4>
    </div>
</div>

<form action="<?= url('admin/products/store') ?>" method="POST">
    <div class="row g-4">
        
        <!-- Left Column: Details & Content -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Basic Product Information</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Product Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. AFOREHEALTH UltraDry Heavy Flow Period Panty">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">SKU Code *</label>
                        <input type="text" name="sku" class="form-control" required placeholder="AFH-PP-099" style="text-transform: uppercase;">
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
                    <textarea name="description" class="form-control" rows="5" placeholder="Detailed product description, fabric benefits, absorbency science..."></textarea>
                </div>
            </div>

            <!-- Material & Care Accordion / Box -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Fabric, Hygiene &amp; Care Details</h6>
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
                <h6 class="fw-bold mb-3 border-bottom pb-2">Pricing &amp; Inventory</h6>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Selling Price (INR) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="1" name="price" class="form-control" required placeholder="849">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">MRP (Original Price) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" step="1" name="mrp" class="form-control" required placeholder="1199">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Total Stock Quantity *</label>
                    <input type="number" name="stock" class="form-control" value="100" required>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold mb-3 border-bottom pb-2">Marketing Badges</h6>
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
                    <button type="submit" class="btn btn-afh-primary py-2 fw-bold">Publish Product</button>
                </div>
            </div>

        </div>

    </div>
</form>

<?php require __DIR__ . '/../layout-footer.php'; ?>
