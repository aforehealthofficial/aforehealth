<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="row g-4">
    <!-- Categories List -->
    <div class="col-lg-8">
        <div class="admin-table-card">
            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                    Store Categories <span class="badge bg-secondary-subtle text-dark fs-6 rounded-pill">Total: <?= count($categories) ?></span>
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">#</th>
                            <th style="width: 70px;">Image</th>
                            <th>Category Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $idx => $cat): ?>
                            <tr>
                                <td class="text-center text-muted fw-bold small"><?= $idx + 1 ?></td>
                                <td>
                                    <img src="<?= upload_url('products/' . ($cat['image'] ?: 'cat_period_panties.svg')) ?>" 
                                         alt="<?= e($cat['name']) ?>" 
                                         class="rounded-3 border p-1 shadow-sm object-fit-contain bg-white" 
                                         style="width: 50px; height: 50px;">
                                </td>
                                <td>
                                    <strong><?= e($cat['name']) ?></strong>
                                    <?php if (!empty($cat['description'])): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;"><?= e($cat['description']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><code><?= e($cat['slug']) ?></code></td>
                                <td><span class="badge bg-light text-dark fw-bold"><?= (int)$cat['product_count'] ?></span></td>
                                <td><span class="badge bg-secondary-subtle text-dark"><?= (int)($cat['sort_order'] ?? 0) ?></span></td>
                                <td>
                                    <span class="badge <?= ($cat['status'] ?? 'active') === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($cat['status'] ?? 'active') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary me-1" 
                                            onclick='openEditCategoryModal(<?= json_encode($cat) ?>)'
                                            title="Edit Category">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="<?= url('admin/categories/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Category?" data-confirm-msg="Are you sure you want to delete category '<?= e($cat['name']) ?>'?">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Category Box -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h5 class="fw-bold mb-3 border-bottom pb-2">Add New Category</h5>
            <form action="<?= url('admin/categories/store') ?>" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Category Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Maternity &amp; Nursing" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Category purpose and customer benefits"></textarea>
                </div>

                <!-- Category Image Upload -->
                <div class="mb-3">
                    <label class="form-label small fw-bold">Category Image</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <img id="catImgPreview" src="<?= upload_url('products/cat_period_panties.svg') ?>" class="rounded-3 border p-1 shadow-sm bg-white" style="width: 60px; height: 60px; object-fit: contain;" alt="Preview">
                        <div class="flex-grow-1">
                            <input type="file" name="image" id="catImgInput" class="form-control form-control-sm" accept="image/*" onchange="previewCategoryImg(this, 'catImgPreview')">
                            <small class="text-muted" style="font-size: 0.75rem;">JPG, PNG, WebP or SVG (Recommended 200x200)</small>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-afh-primary w-100 fw-bold py-2">Create Category</button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="editCatModalTitle">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('admin/categories/update') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="editCatId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category Name *</label>
                        <input type="text" name="name" id="editCatName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" id="editCatDesc" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <!-- Image update -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category Image</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img id="editCatImgPreview" src="" class="rounded-3 border p-1 shadow-sm bg-white" style="width: 64px; height: 64px; object-fit: contain;" alt="Current Image">
                            <div class="flex-grow-1">
                                <input type="file" name="image" class="form-control form-control-sm" accept="image/*" onchange="previewCategoryImg(this, 'editCatImgPreview')">
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Leave empty to keep existing image</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Sort Order</label>
                            <input type="number" name="sort_order" id="editCatSort" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Status</label>
                            <select name="status" id="editCatStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-afh-primary rounded-pill px-4 fw-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewCategoryImg(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openEditCategoryModal(cat) {
    document.getElementById('editCatId').value = cat.id;
    document.getElementById('editCatName').value = cat.name;
    document.getElementById('editCatDesc').value = cat.description || '';
    document.getElementById('editCatSort').value = cat.sort_order || 0;
    document.getElementById('editCatStatus').value = cat.status || 'active';
    
    const imgName = cat.image || 'cat_period_panties.svg';
    document.getElementById('editCatImgPreview').src = '<?= url("uploads/products/") ?>/' + imgName;
    document.getElementById('editCatModalTitle').innerText = 'Edit Category: ' + cat.name;

    const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}
</script>

<?php require __DIR__ . '/../layout-footer.php'; ?>
