<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold m-0 text-dark">Home Page Banners &amp; Hero Sliders</h4>
        <small class="text-muted">Manage promotional banners, hero slide images, and call-to-actions displayed on the storefront</small>
    </div>
    <button type="button" class="btn btn-afh-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addBannerModal">
        <i class="bi bi-plus-lg me-1"></i> Add New Banner
    </button>
</div>

<div class="admin-table-card">
    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
            Active Storefront Banners <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill">Total: <?= count($banners) ?></span>
        </h5>
    </div>
    <div class="table-responsive">
        <table class="table admin-table align-middle">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">#</th>
                    <th style="width: 140px;">Desktop Banner</th>
                    <th style="width: 90px;">Mobile View</th>
                    <th>Banner Content</th>
                    <th>Target Link &amp; CTA</th>
                    <th>Position</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($banners)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-images fs-1 d-block mb-2 text-secondary"></i>
                            <p class="mb-2 fw-semibold">No custom banners uploaded yet.</p>
                            <small class="d-block mb-3">The homepage is currently displaying the default AFOREHEALTH hero section.</small>
                            <button type="button" class="btn btn-sm btn-afh-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                                Add Your First Banner Image
                            </button>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($banners as $idx => $b): ?>
                        <tr>
                            <td class="text-center text-muted fw-bold small"><?= $idx + 1 ?></td>
                            <td>
                                <div class="rounded-3 border overflow-hidden shadow-sm bg-light" style="width: 130px; height: 60px;">
                                    <img src="<?= upload_url('banners/' . $b['image_desktop']) ?>" alt="Desktop Banner" class="w-100 h-100 object-fit-cover">
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($b['image_mobile'])): ?>
                                    <div class="rounded-3 border overflow-hidden shadow-sm bg-light" style="width: 50px; height: 60px;">
                                        <img src="<?= upload_url('banners/' . $b['image_mobile']) ?>" alt="Mobile Banner" class="w-100 h-100 object-fit-cover">
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">Auto-scales</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="d-block text-dark">
                                    <?= e($b['title']) ?>
                                    <?php if (!empty($b['video_url'])): ?>
                                        <span class="badge bg-danger ms-1" style="font-size: 0.65rem;"><i class="bi bi-play-fill"></i> Video</span>
                                    <?php endif; ?>
                                </strong>
                                <?php if (!empty($b['subtitle'])): ?>
                                    <small class="text-muted d-block text-truncate" style="max-width: 250px;"><?= e($b['subtitle']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><a href="<?= e($b['link']) ?>" target="_blank" class="small fw-bold text-decoration-none text-primary"><i class="bi bi-box-arrow-up-right me-1"></i><?= e($b['link']) ?></a></div>
                                <span class="badge bg-light text-dark border"><?= e($b['cta_text'] ?: 'Shop Now') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-dark"><?= e($b['position']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark"><?= (int)$b['sort_order'] ?></span>
                            </td>
                            <td>
                                <form action="<?= url('admin/banners/toggle') ?>" method="POST" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn btn-sm <?= $b['status'] === 'active' ? 'btn-success' : 'btn-secondary' ?> py-0 px-2 rounded-pill fw-bold" style="font-size: 0.75rem;" title="Click to toggle status">
                                        <?= ucfirst($b['status']) ?>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick='openEditBannerModal(<?= json_encode($b) ?>)' title="Edit Banner">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="<?= url('admin/banners/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Banner?" data-confirm-msg="Are you sure you want to delete banner '<?= e($b['title']) ?>'?">
                                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Banner Modal -->
<div class="modal fade" id="addBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-images me-2 text-primary"></i> Add Home Page Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('admin/banners/store') ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Banner Headline / Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="e.g. Move With Period Confidence">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sub-headline / Tagline</label>
                            <input type="text" name="subtitle" class="form-control" placeholder="e.g. 4-Layer Antibacterial Protection for Indian Bodies">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Destination URL / Link *</label>
                            <input type="text" name="link" class="form-control" value="/shop" required placeholder="e.g. /shop or /category/period-panties">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">CTA Button Label</label>
                            <input type="text" name="cta_text" class="form-control" value="Shop All Collection" placeholder="e.g. Shop Now">
                        </div>

                        <!-- Desktop Banner Image -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Desktop Banner Image *</label>
                            <div class="border rounded-3 p-3 text-center bg-light">
                                <div class="mb-2">
                                    <img id="addDeskPreview" src="<?= asset('images/hero_banner_1.svg') ?>" class="rounded-3 border shadow-sm bg-white" style="width: 100%; max-height: 120px; object-fit: cover;" alt="Desktop Preview">
                                </div>
                                <input type="file" name="image_desktop" class="form-control form-control-sm" accept="image/*" required onchange="previewImg(this, 'addDeskPreview')">
                                <small class="text-muted d-block mt-1" style="font-size: 0.725rem;">Recommended: 1920x650 or 1200x500 (JPG, PNG, WebP, SVG)</small>
                            </div>
                        </div>

                        <!-- Mobile Banner Image -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile Banner Image (Optional)</label>
                            <div class="border rounded-3 p-3 text-center bg-light">
                                <div class="mb-2">
                                    <img id="addMobPreview" src="<?= asset('images/hero_banner_1.svg') ?>" class="rounded-3 border shadow-sm bg-white" style="width: 100px; height: 120px; object-fit: cover;" alt="Mobile Preview">
                                </div>
                                <input type="file" name="image_mobile" class="form-control form-control-sm" accept="image/*" onchange="previewImg(this, 'addMobPreview')">
                                <small class="text-muted d-block mt-1" style="font-size: 0.725rem;">Recommended: 750x600 or 600x600 (Optimized for phone screens)</small>
                            </div>
                        </div>

                        <!-- Video Link (Optional) -->
                        <div class="col-12">
                            <label class="form-label small fw-bold"><i class="bi bi-camera-video text-danger me-1"></i> Promotional Video URL / Reel (Optional)</label>
                            <input type="url" name="video_url" class="form-control form-control-sm" placeholder="https://www.youtube.com/watch?v=... or shorts / direct mp4">
                            <small class="text-muted d-block mt-1" style="font-size: 0.725rem;">Optional video embed / reel link</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Position</label>
                            <select name="position" class="form-select">
                                <option value="home_hero">Homepage Top Hero</option>
                                <option value="home_middle">Homepage Middle Promo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active (Published)</option>
                                <option value="inactive">Inactive (Draft)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-afh-primary rounded-pill px-4 fw-bold">Upload &amp; Publish Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Banner Modal -->
<div class="modal fade" id="editBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold text-dark" id="editBannerModalTitle"><i class="bi bi-pencil me-2 text-primary"></i> Edit Banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBannerForm" action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Banner Headline / Title *</label>
                            <input type="text" name="title" id="editBTitle" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sub-headline / Tagline</label>
                            <input type="text" name="subtitle" id="editBSubtitle" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Destination URL / Link *</label>
                            <input type="text" name="link" id="editBLink" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">CTA Button Label</label>
                            <input type="text" name="cta_text" id="editBCta" class="form-control">
                        </div>

                        <!-- Desktop Banner Image -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Desktop Banner Image</label>
                            <div class="border rounded-3 p-3 text-center bg-light">
                                <div class="mb-2">
                                    <img id="editBDeskPreview" src="" class="rounded-3 border shadow-sm bg-white" style="width: 100%; max-height: 120px; object-fit: cover;" alt="Desktop Preview">
                                </div>
                                <input type="file" name="image_desktop" class="form-control form-control-sm" accept="image/*" onchange="previewImg(this, 'editBDeskPreview')">
                                <small class="text-muted d-block mt-1" style="font-size: 0.725rem;">Leave empty to keep existing desktop image</small>
                            </div>
                        </div>

                        <!-- Mobile Banner Image -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile Banner Image</label>
                            <div class="border rounded-3 p-3 text-center bg-light">
                                <div class="mb-2">
                                    <img id="editBMobPreview" src="" class="rounded-3 border shadow-sm bg-white" style="width: 100px; height: 120px; object-fit: cover;" alt="Mobile Preview">
                                </div>
                                <input type="file" name="image_mobile" class="form-control form-control-sm" accept="image/*" onchange="previewImg(this, 'editBMobPreview')">
                                <small class="text-muted d-block mt-1" style="font-size: 0.725rem;">Leave empty to keep existing mobile image</small>
                            </div>
                        </div>

                        <!-- Video Link (Optional) -->
                        <div class="col-12">
                            <label class="form-label small fw-bold"><i class="bi bi-camera-video text-danger me-1"></i> Promotional Video URL / Reel (Optional)</label>
                            <input type="url" name="video_url" id="editBVideoUrl" class="form-control form-control-sm" placeholder="https://www.youtube.com/watch?v=... or shorts / direct mp4">
                            <small class="text-muted d-block mt-1" style="font-size: 0.725rem;">Optional video embed / reel link</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Position</label>
                            <select name="position" id="editBPosition" class="form-select">
                                <option value="home_hero">Homepage Top Hero</option>
                                <option value="home_middle">Homepage Middle Promo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" id="editBSort" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" id="editBStatus" class="form-select">
                                <option value="active">Active (Published)</option>
                                <option value="inactive">Inactive (Draft)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-afh-primary rounded-pill px-4 fw-bold">Save Banner Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

function openEditBannerModal(banner) {
    document.getElementById('editBannerForm').action = '<?= url("admin/banners/update") ?>/' + banner.id;
    document.getElementById('editBannerModalTitle').innerHTML = '<i class="bi bi-pencil me-2 text-primary"></i> Edit Banner #' + banner.id;
    document.getElementById('editBTitle').value = banner.title;
    document.getElementById('editBSubtitle').value = banner.subtitle || '';
    document.getElementById('editBLink').value = banner.link;
    document.getElementById('editBCta').value = banner.cta_text || 'Shop Now';
    document.getElementById('editBVideoUrl').value = banner.video_url || '';
    document.getElementById('editBPosition').value = banner.position || 'home_hero';
    document.getElementById('editBSort').value = banner.sort_order || 0;
    document.getElementById('editBStatus').value = banner.status || 'active';

    const deskUrl = '<?= url("uploads/banners/") ?>/' + banner.image_desktop;
    document.getElementById('editBDeskPreview').src = deskUrl;

    const mobUrl = banner.image_mobile ? ('<?= url("uploads/banners/") ?>/' + banner.image_mobile) : deskUrl;
    document.getElementById('editBMobPreview').src = mobUrl;

    const modal = new bootstrap.Modal(document.getElementById('editBannerModal'));
    modal.show();
}
</script>

<?php require __DIR__ . '/../layout-footer.php'; ?>
