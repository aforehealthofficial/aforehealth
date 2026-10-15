<?php require __DIR__ . '/../layout-header.php'; ?>

<div class="row g-4 justify-content-center">
    <div class="col-lg-10">
        <form action="<?= url('admin/blog/update') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $post['id'] ?>">
            
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Edit Article: <?= e($post['title']) ?></h5>
                        <p class="text-muted small mb-0">Update educational content, image, or SEO ranking tags.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= url('blog/' . $post['slug']) ?>" target="_blank" class="btn btn-outline-primary btn-sm fw-bold">
                            <i class="bi bi-box-arrow-up-right me-1"></i> View Live
                        </a>
                        <a href="<?= url('admin/blog') ?>" class="btn btn-outline-secondary btn-sm fw-bold">
                            <i class="bi bi-arrow-left"></i> All Articles
                        </a>
                    </div>
                </div>

                <!-- Basic Article Information -->
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Article Title *</label>
                        <input type="text" name="title" id="blogTitleInput" class="form-control form-control-lg fs-6" 
                               value="<?= e($post['title']) ?>" required oninput="updateGooglePreview()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Category *</label>
                        <select name="category_id" class="form-select form-select-lg fs-6" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $post['category_id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label small fw-bold">SEO URL Slug *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted small">/blog/</span>
                            <input type="text" name="slug" id="blogSlugInput" class="form-control font-monospace small" 
                                   value="<?= e($post['slug']) ?>" required oninput="document.getElementById('googlePreviewSlug').textContent=this.value">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Author Name</label>
                        <input type="text" name="author" class="form-control" value="<?= e($post['author'] ?? 'AFOREHEALTH Wellness Team') ?>">
                    </div>
                </div>

                <!-- Featured Cover Image -->
                <div class="mb-4 p-3 rounded-4 bg-light border">
                    <label class="form-label small fw-bold text-dark mb-1">
                        <i class="bi bi-image text-primary me-1"></i> Featured Cover Image
                    </label>
                    <p class="text-muted small mb-3">Upload a new image to replace current cover, or leave blank to keep existing.</p>
                    <div class="d-flex align-items-center gap-3">
                        <input type="file" name="featured_image" id="featuredImgInput" class="form-control" accept="image/*" onchange="previewBlogImg(this)">
                        <div id="imgPreviewWrap" class="border rounded-3 overflow-hidden bg-white shadow-sm flex-shrink-0" style="width: 100px; height: 65px;">
                            <img id="previewImgEl" src="<?= upload_url('blog/' . ($post['featured_image'] ?? 'blog_science_period_panties.svg')) ?>" class="w-100 h-100 object-fit-cover" alt="Current Cover">
                        </div>
                    </div>
                </div>

                <!-- Teaser Excerpt -->
                <div class="mb-4">
                    <label class="form-label small fw-bold">Short Excerpt / Teaser Summary *</label>
                    <textarea name="excerpt" id="blogExcerptInput" class="form-control" rows="2" required oninput="updateGooglePreview()"><?= e($post['excerpt']) ?></textarea>
                </div>

                <!-- Full Content -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label small fw-bold m-0">Full Article Content (HTML / Markdown) *</label>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" onclick="insertFormat('**', '**')"><strong>B</strong></button>
                            <button type="button" class="btn btn-outline-secondary" onclick="insertFormat('### ', '\n')">H3</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="insertFormat('- ', '\n')">• List</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="insertFormat('> ', '\n')">Quote</button>
                        </div>
                    </div>
                    <textarea name="content" id="blogContentInput" class="form-control font-monospace" rows="16" required><?= e($post['content']) ?></textarea>
                </div>

                <!-- SEO & Meta Tags Card -->
                <div class="card border rounded-4 p-3 p-md-4 mb-4" style="background: #FFFBFD; border-color: #E8DEE5 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h6 class="fw-bold m-0 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-google text-primary"></i> Search Engine Optimization (SEO) Settings
                        </h6>
                        <span class="badge bg-primary-subtle text-primary fw-bold">Google Ranking Tools</span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold d-flex justify-content-between">
                                <span>SEO Meta Title</span>
                                <span class="text-muted" style="font-size: 0.725rem;"><span id="titleCount"><?= strlen($post['seo_title'] ?? '') ?></span>/60 chars</span>
                            </label>
                            <input type="text" name="seo_title" id="seoTitleInput" class="form-control" maxlength="70" 
                                   value="<?= e($post['seo_title'] ?? '') ?>" placeholder="Title displayed in Google search results" oninput="updateGooglePreview()">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Publishing Status</label>
                            <select name="status" class="form-select">
                                <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published (Visible to public &amp; search engines)</option>
                                <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft (Saved privately)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold d-flex justify-content-between">
                                <span>SEO Meta Description</span>
                                <span class="text-muted" style="font-size: 0.725rem;"><span id="descCount"><?= strlen($post['seo_description'] ?? '') ?></span>/160 chars</span>
                            </label>
                            <textarea name="seo_description" id="seoDescInput" class="form-control" rows="2" maxlength="170" 
                                      placeholder="Summary displayed below the title in Google search results" oninput="updateGooglePreview()"><?= e($post['seo_description'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Google Snippet Live Preview -->
                    <div class="p-3 bg-white rounded-3 border">
                        <div class="text-muted small fw-bold text-uppercase mb-2" style="font-size: 0.7rem;">Live Google Search Result Preview:</div>
                        <div class="font-sans" style="max-width: 600px;">
                            <div class="text-muted small" style="font-size: 0.75rem;">https://aforehealth.test › blog › <span id="googlePreviewSlug"><?= e($post['slug']) ?></span></div>
                            <div class="text-primary fw-bold fs-6 text-truncate" id="googlePreviewTitle"><?= e($post['seo_title'] ?: $post['title']) ?> | AFOREHEALTH</div>
                            <div class="text-muted small" style="font-size: 0.8rem; line-height: 1.4;" id="googlePreviewDesc"><?= e($post['seo_description'] ?: $post['excerpt']) ?></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2">
                    <a href="<?= url('admin/blog') ?>" class="btn btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-afh-primary px-5 fw-bold shadow-sm">Save Changes</button>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
function previewBlogImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImgEl').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateGooglePreview() {
    const rawTitle = document.getElementById('seoTitleInput')?.value || document.getElementById('blogTitleInput')?.value || 'Article Title';
    const rawDesc = document.getElementById('seoDescInput')?.value || document.getElementById('blogExcerptInput')?.value || 'Summary description...';
    
    document.getElementById('googlePreviewTitle').textContent = rawTitle + ' | AFOREHEALTH';
    document.getElementById('googlePreviewDesc').textContent = rawDesc;
    
    document.getElementById('titleCount').textContent = document.getElementById('seoTitleInput')?.value.length || 0;
    document.getElementById('descCount').textContent = document.getElementById('seoDescInput')?.value.length || 0;
}

function insertFormat(startTag, endTag) {
    const textarea = document.getElementById('blogContentInput');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selected = text.substring(start, end);
    textarea.value = text.substring(0, start) + startTag + selected + endTag + text.substring(end);
    textarea.focus();
}
</script>

<?php require __DIR__ . '/../layout-footer.php'; ?>
