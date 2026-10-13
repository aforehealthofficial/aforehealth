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
                            <th>Category Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $idx => $cat): ?>
                            <tr>
                                <td class="text-center text-muted fw-bold small"><?= $idx + 1 ?></td>
                                <td><strong><?= e($cat['name']) ?></strong></td>
                                <td><code><?= e($cat['slug']) ?></code></td>
                                <td><small class="text-muted"><?= e(substr($cat['description'], 0, 50)) ?>...</small></td>
                                <td><span class="badge bg-light text-dark"><?= (int)$cat['product_count'] ?></span></td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td class="text-end">
                                    <form action="<?= url('admin/categories/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Category?" data-confirm-msg="Are you sure you want to delete category '<?= e($cat['name']) ?>'?">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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
            <form action="<?= url('admin/categories/store') ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Category Name *</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Maternity &amp; Nursing" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Category purpose and customer benefits"></textarea>
                </div>
                <button type="submit" class="btn btn-afh-primary w-100 fw-bold">Create Category</button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
