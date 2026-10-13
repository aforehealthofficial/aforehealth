<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">My Account</a></li>
            <li class="breadcrumb-item active">Saved Addresses</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark m-0">Saved Addresses</h1>
            <p class="text-muted small m-0">Manage your shipping destinations across India</p>
        </div>
        <button type="button" class="btn btn-afh-primary btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#newAddressModal">
            + Add New Address
        </button>
    </div>

    <div class="row g-3">
        <?php if (empty($addresses)): ?>
            <div class="col-12">
                <div class="text-center py-5 bg-white rounded-4 shadow-sm p-4 text-muted">
                    No saved addresses yet.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($addresses as $addr): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold m-0"><?= e($addr['full_name']) ?></h6>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-secondary text-dark text-uppercase small"><?= e($addr['address_type']) ?></span>
                                <form action="<?= url('account/addresses/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Address?" data-confirm-msg="Are you sure you want to remove this saved address for <?= e($addr['full_name']) ?>?">
                                    <input type="hidden" name="address_id" value="<?= $addr['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0 border-0" title="Delete Address">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="text-muted small mb-3 flex-grow-1" style="line-height: 1.6;">
                            <?= e($addr['house_flat']) ?>, <?= e($addr['area']) ?><br>
                            <?= !empty($addr['landmark']) ? 'Landmark: ' . e($addr['landmark']) . '<br>' : '' ?>
                            <?= e($addr['city']) ?>, <?= e($addr['state']) ?> - <strong><?= e($addr['pincode']) ?></strong><br>
                            Phone: <?= e($addr['mobile']) ?>
                        </div>
                        <?php if ($addr['is_default']): ?>
                            <span class="badge bg-success-subtle text-success small w-auto align-self-start">Default Delivery Address</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Address Modal -->
<div class="modal fade" id="newAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Add Delivery Address</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= url('account/addresses/save') ?>" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" required placeholder="Priya Sharma">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Mobile Number *</label>
                        <input type="tel" name="mobile" class="form-control" maxlength="10" pattern="[0-9]{10}" required placeholder="9876543210">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">House / Flat / Building *</label>
                        <input type="text" name="house_flat" class="form-control" required placeholder="Flat 402, Sunshine Heights">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Street / Area *</label>
                        <input type="text" name="area" class="form-control" required placeholder="Linking Road, Bandra West">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Landmark</label>
                        <input type="text" name="landmark" class="form-control" placeholder="Near National College">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-bold">PIN Code *</label>
                            <input type="text" name="pincode" class="form-control" maxlength="6" pattern="[0-9]{6}" required placeholder="400050">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold">City *</label>
                            <input type="text" name="city" class="form-control" required placeholder="Mumbai">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-bold">State *</label>
                            <input type="text" name="state" class="form-control" required placeholder="Maharashtra">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Address Type</label>
                        <select name="address_type" class="form-select">
                            <option value="home">Home</option>
                            <option value="work">Work / Office</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="defCheck">
                        <label class="form-check-label small" for="defCheck">Set as default delivery address</label>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-afh-primary fw-bold">Save Address</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
