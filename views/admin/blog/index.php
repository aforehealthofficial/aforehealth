<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="row g-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Period Care &amp; Wellness SEO Blog Guides</h5>
                    <p class="text-muted small mb-0">Publish high-ranking educational guides, doctor-reviewed period advice, and organic search content.</p>
                </div>
                <div>
                    <a href="<?= url('admin/blog/create') ?>" class="btn btn-afh-primary fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square"></i> Write New Article
                    </a>
                </div>
            </div>
        </div>

        <div class="admin-table-card">
            <div class="p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h6 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                    Published &amp; Draft Articles <span class="badge bg-secondary-subtle text-dark rounded-pill">Total: <?= $total ?? count($posts) ?></span>
                </h6>

                <!-- Search & Per Page -->
                <form method="GET" action="<?= url('admin/blog') ?>" class="d-flex flex-wrap gap-2">
                    <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                        <option value="20" <?= ($perPage ?? 20) == 20 ? 'selected' : '' ?>>20 per page</option>
                        <option value="30" <?= ($perPage ?? 20) == 30 ? 'selected' : '' ?>>30 per page</option>
                        <option value="50" <?= ($perPage ?? 20) == 50 ? 'selected' : '' ?>>50 per page</option>
                    </select>

                    <div class="input-group input-group-sm" style="width: 240px;">
                        <input type="text" name="q" class="form-control" placeholder="Search blog titles..." value="<?= e($search ?? '') ?>">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 45px; text-align: center;">#</th>
                            <th>Cover</th>
                            <th>Article Title &amp; SEO Slug</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No blog articles found. Click "Write New Article" to start.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $idx => $post): ?>
                                <tr>
                                    <td class="text-center text-muted fw-bold small"><?= (($page ?? 1) - 1) * ($perPage ?? 20) + $idx + 1 ?></td>
                                    <td style="width: 80px;">
                                        <img src="<?= upload_url('blog/' . ($post['featured_image'] ?? 'blog_science_period_panties.svg')) ?>" 
                                             class="rounded-3 border" style="width: 60px; height: 45px; object-fit: cover;" alt="<?= e($post['title']) ?>">
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= e($post['title']) ?></div>
                                        <small class="text-muted font-monospace d-block">/blog/<?= e($post['slug']) ?></small>
                                        <?php if (!empty($post['seo_title'])): ?>
                                            <span class="badge bg-light text-primary border" style="font-size: 0.68rem;"><i class="bi bi-google me-1"></i>SEO Title Set</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= e($post['category_name'] ?? 'General') ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted fw-semibold"><?= e($post['author'] ?? 'Team') ?></small>
                                    </td>
                                    <td>
                                        <?php if ($post['status'] === 'published'): ?>
                                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-1"><i class="bi bi-check-circle me-1"></i>Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning fw-bold px-2 py-1"><i class="bi bi-hourglass me-1"></i>Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= date('d M Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></small>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="<?= url('blog/' . $post['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="View Public Post">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                            <a href="<?= url('admin/blog/edit/' . $post['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit Article">
                                                <i class="bi bi-pencil"></i>
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

            <?php if (($totalPages ?? 1) > 1): ?>
                <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light rounded-bottom-4">
                    <small class="text-muted">Showing page <?= $page ?> of <?= $totalPages ?> (Total <?= $total ?> articles)</small>
                    <nav>
                        <ul class="pagination pagination-sm m-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">«</a></li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">»</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
