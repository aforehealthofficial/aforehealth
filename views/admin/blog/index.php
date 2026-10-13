<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Period Care &amp; Wellness Blog Guides</h5>
                    <p class="text-muted small mb-0">Publish helpful, medically sensitive, and informative educational articles for customers.</p>
                </div>
                <div>
                    <a href="<?= url('admin/blog/create') ?>" class="btn btn-afh-primary fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square"></i> Write New Article
                    </a>
                </div>
            </div>
        </div>

        <div class="admin-table-card">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                    Published Articles <span class="badge bg-secondary-subtle text-dark rounded-pill">Total: <?= count($posts) ?></span>
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">#</th>
                            <th>Cover</th>
                            <th>Article Title &amp; Slug</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Published Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No blog articles published yet. Click "Write New Article" to start.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $idx => $post): ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold small"><?= $idx + 1 ?></td>
                                    <td style="width: 80px;">
                                        <img src="<?= asset('uploads/products/' . ($post['featured_image'] ?? 'blog_science_period_panties.svg')) ?>" 
                                             class="rounded-3 border" style="width: 60px; height: 45px; object-fit: cover;" alt="Cover">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($post['title']) ?></div>
                                        <small class="text-muted font-monospace">/blog/<?= e($post['slug']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= e($post['category_name']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?= ucfirst($post['status']) ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('d M Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></small>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="<?= url('blog/' . $post['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View Public Post">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                            <form action="<?= url('admin/blog/delete') ?>" method="POST" class="d-inline form-delete-confirm" data-confirm-title="Delete Blog Post?" data-confirm-msg="Are you sure you want to delete post '<?= e($post['title']) ?>'?">
                                                <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Post">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
