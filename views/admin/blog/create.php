<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="row g-4 justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Write &amp; Publish New Guide</h5>
                    <p class="text-muted small mb-0">Help customers choose the right period-care &amp; intimate hygiene routine.</p>
                </div>
                <a href="<?= url('admin/blog') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                    <i class="bi bi-arrow-left"></i> Back to All Articles
                </a>
            </div>

            <form action="<?= url('admin/blog/store') ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Article Title *</label>
                    <input type="text" name="title" class="form-control form-control-lg fs-6" placeholder="e.g. How to Care and Wash Your Period Panties for Maximum Longevity" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Educational Category *</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Short Excerpt / Teaser *</label>
                    <textarea name="excerpt" class="form-control" rows="2" placeholder="A 1-2 sentence overview shown on blog listings and social cards..." required></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Full Article Content (Markdown or HTML) *</label>
                    <textarea name="content" class="form-control font-monospace" rows="12" placeholder="Write the complete article content here..." required></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= url('admin/blog') ?>" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-afh-primary px-5 fw-bold">Publish Guide</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layout-footer.php'; ?>
