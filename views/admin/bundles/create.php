<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="mb-4">
    <a href="<?= url('admin/bundles') ?>" class="text-decoration-none small text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Bundles</a>
    <h4 class="fw-bold m-0 text-dark mt-2">Create Custom Kit / Bundle</h4>
</div>

<div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white" style="max-width: 800px;">
    <form action="<?= url('admin/bundles/store') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label fw-bold small text-dark">Bundle Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Build Your Custom Period Kit" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold small text-dark">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Explain the kit combination and savings..."></textarea>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-bold small text-dark">Discount Type</label>
                <select name="discount_type" class="form-select">
                    <option value="percentage">Percentage (e.g. 20%)</option>
                    <option value="fixed">Fixed Amount (e.g. ₹300)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-dark">Discount Value <span class="text-danger">*</span></label>
                <input type="number" name="discount_value" class="form-control" value="20" min="1" step="1" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold small text-dark">Min Items to Qualify</label>
                <input type="number" name="min_items" class="form-control" value="3" min="2" max="10" required>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-bold small text-dark">Step-by-step Bundle Categories <span class="text-danger">*</span></label>
            <p class="small text-muted mb-2">Select the categories customers will choose items from (e.g. Period Panties, Pads, Liners, Intimate Wash):</p>
            <div class="row g-2 p-3 bg-light rounded-3">
                <?php foreach ($categories as $cat): ?>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="category_ids[]" value="<?= $cat['id'] ?>" id="cat_<?= $cat['id'] ?>">
                            <label class="form-check-label small fw-semibold text-dark" for="cat_<?= $cat['id'] ?>">
                                <?= e($cat['name']) ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-afh-primary fw-bold px-4">Create Bundle</button>
            <a href="<?= url('admin/bundles') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
