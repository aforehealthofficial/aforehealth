<?php require __DIR__ . '/../layouts/header.php'; ?>

<!-- Blog Hero Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #FFF9FA 0%, #F5E8F0 100%); border-bottom: 1px solid #EEDCE6;">
    <div class="container text-center" style="max-width: 820px;">
        <span class="badge bg-secondary text-dark px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">Education &amp; Science</span>
        <h1 class="display-5 fw-bold text-dark mb-2">The Period Care &amp; Wellness Guide</h1>
        <p class="lead text-muted mb-0">
            Evidence-based menstrual health guides, sizing tutorials, fabric science, and doctor-approved intimate hygiene advice.
        </p>
    </div>
</section>

<!-- Blog Main Layout -->
<div class="container py-5">
    <div class="row g-4 g-xl-5">

        <!-- ========================================== -->
        <!-- 1. LEFT SIDEBAR: Filters & Search          -->
        <!-- ========================================== -->
        <aside class="col-lg-3">
            <div class="position-sticky" style="top: 90px;">
                
                <!-- Search Box Widget -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                    <div class="card-body p-3 p-xl-4">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                            <i class="bi bi-search text-primary"></i> Search Guides
                        </h6>
                        <form action="<?= url('blog') ?>" method="GET" class="position-relative">
                            <?php if (!empty($selectedCategory)): ?>
                                <input type="hidden" name="category" value="<?= e($selectedCategory) ?>">
                            <?php endif; ?>
                            <div class="input-group">
                                <input type="text" name="q" value="<?= e($searchQuery) ?>" class="form-control rounded-start-pill ps-3" placeholder="Search keywords..." aria-label="Search articles">
                                <button class="btn btn-primary rounded-end-pill px-3" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                            <?php if (!empty($searchQuery)): ?>
                                <div class="mt-2 text-end">
                                    <a href="<?= url('blog' . (!empty($selectedCategory) ? '?category=' . urlencode($selectedCategory) : '')) ?>" class="small text-danger text-decoration-none">
                                        <i class="bi bi-x-circle me-1"></i>Clear search
                                    </a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <!-- Categories Filter Widget -->
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-funnel text-primary"></i> Filter by Topic
                        </h6>
                    </div>
                    <div class="card-body px-3 py-2">
                        <div class="list-group list-group-flush">
                            <!-- All Articles Item -->
                            <a href="<?= url('blog' . (!empty($searchQuery) ? '?q=' . urlencode($searchQuery) : '')) ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded-3 border-0 px-3 py-2.5 mb-1 <?= empty($selectedCategory) ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-dark' ?>" 
                               style="transition: all 0.2s ease;">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="bi bi-grid-fill small"></i> All Topics
                                </span>
                                <span class="badge rounded-pill <?= empty($selectedCategory) ? 'bg-white text-primary' : 'bg-light text-muted' ?>">
                                    <?= $totalPublishedPosts ?>
                                </span>
                            </a>

                            <!-- Category Items -->
                            <?php foreach ($categories as $cat): ?>
                                <?php $isActive = ($selectedCategory === $cat['slug']); ?>
                                <a href="<?= url('blog?category=' . urlencode($cat['slug']) . (!empty($searchQuery) ? '&q=' . urlencode($searchQuery) : '')) ?>" 
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded-3 border-0 px-3 py-2.5 mb-1 <?= $isActive ? 'active bg-primary text-white fw-bold shadow-sm' : 'text-dark' ?>"
                                   style="transition: all 0.2s ease;">
                                    <span class="d-flex align-items-center gap-2 text-truncate me-2" title="<?= e($cat['name']) ?>">
                                        <i class="bi bi-journal-text small"></i> <?= e($cat['name']) ?>
                                    </span>
                                    <span class="badge rounded-pill <?= $isActive ? 'bg-white text-primary' : 'bg-light text-muted' ?>">
                                        <?= (int)$cat['post_count'] ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($selectedCategory) || !empty($searchQuery)): ?>
                            <div class="pt-3 pb-2 text-center border-top mt-2">
                                <a href="<?= url('blog') ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset All Filters
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Top Guides Widget -->
                <?php if (!empty($recentPosts)): ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden d-none d-lg-block">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-stars text-warning"></i> Latest Guides
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($recentPosts as $rp): ?>
                                    <a href="<?= url('blog/' . $rp['slug']) ?>" class="d-flex gap-3 text-decoration-none group-hover-title align-items-center">
                                        <img src="<?= upload_url('blog/' . $rp['featured_image']) ?>" 
                                             alt="<?= e($rp['title']) ?>" 
                                             class="rounded-3 object-fit-cover shadow-sm flex-shrink-0" 
                                             style="width: 64px; height: 64px;">
                                        <div class="overflow-hidden">
                                            <span class="badge bg-light text-primary small px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                                <?= e($rp['category_name']) ?>
                                            </span>
                                            <div class="text-dark small fw-bold text-truncate-2 mt-1 lh-sm" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                <?= e($rp['title']) ?>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.72rem;"><?= date('d M Y', strtotime($rp['published_at'])) ?></small>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- WhatsApp Doctor / Community Consultation Banner -->
                <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #1E7E34 0%, #25D366 100%);">
                    <div class="card-body p-4 text-center">
                        <i class="bi bi-whatsapp fs-1 mb-2 d-inline-block"></i>
                        <h6 class="fw-bold mb-1">Period &amp; Sizing Doubts?</h6>
                        <p class="small opacity-90 mb-3" style="font-size: 0.8125rem;">
                            Chat privately with our certified intimate care specialists on WhatsApp.
                        </p>
                        <a href="https://wa.me/919654504052?text=<?= urlencode('Hello AFOREHEALTH! I need assistance with period panty sizing & hygiene guidance.') ?>" 
                           target="_blank" 
                           class="btn btn-light text-success fw-bold btn-sm rounded-pill px-4 shadow-sm">
                            <i class="bi bi-chat-dots-fill me-1"></i> WhatsApp Us
                        </a>
                    </div>
                </div>

            </div>
        </aside>

        <!-- ========================================== -->
        <!-- 2. RIGHT MAIN CONTENT: Toolbar & Articles  -->
        <!-- ========================================== -->
        <main class="col-lg-9">
            
            <!-- Toolbar Header: Results Count + Active Filter Tags + Layout Switcher -->
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    
                    <!-- Left: Results count & chips -->
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <span class="fw-bold text-dark">
                            Showing <span class="text-primary"><?= count($posts) ?></span> <?= count($posts) === 1 ? 'Article' : 'Articles' ?>
                        </span>
                        
                        <?php if (!empty($selectedCategory)): ?>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1.5 small">
                                Category: <?= e($categories[array_search($selectedCategory, array_column($categories, 'slug'))]['name'] ?? $selectedCategory) ?>
                                <a href="<?= url('blog' . (!empty($searchQuery) ? '?q=' . urlencode($searchQuery) : '')) ?>" class="text-primary text-decoration-none ms-1" title="Remove filter">&times;</a>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($searchQuery)): ?>
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1.5 small">
                                Search: "<?= e($searchQuery) ?>"
                                <a href="<?= url('blog' . (!empty($selectedCategory) ? '?category=' . urlencode($selectedCategory) : '')) ?>" class="text-dark text-decoration-none ms-1" title="Remove search">&times;</a>
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Right: Layout Switcher (3-grid, 4-grid, 2-grid, List View) -->
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted fw-bold d-none d-sm-inline">Layout:</span>
                        <div class="btn-group btn-group-sm rounded-pill p-1 bg-light border" role="group" aria-label="Blog Layout Switcher">
                            <!-- 3 in row (Default) -->
                            <button type="button" 
                                    class="btn btn-sm rounded-pill px-2.5 py-1 text-dark fw-bold layout-toggle-btn active shadow-sm bg-white" 
                                    id="btnLayout3" 
                                    onclick="setBlogLayout('grid-3')" 
                                    title="3 Columns Grid (Default)">
                                <i class="bi bi-grid-3x3-gap-fill text-primary"></i> 
                                <span class="d-none d-md-inline ms-1">3 in Row</span>
                            </button>

                            <!-- 4 in row -->
                            <button type="button" 
                                    class="btn btn-sm rounded-pill px-2.5 py-1 text-muted layout-toggle-btn" 
                                    id="btnLayout4" 
                                    onclick="setBlogLayout('grid-4')" 
                                    title="4 Columns Grid">
                                <i class="bi bi-grid-fill"></i> 
                                <span class="d-none d-md-inline ms-1">4 in Row</span>
                            </button>

                            <!-- 2 in row -->
                            <button type="button" 
                                    class="btn btn-sm rounded-pill px-2.5 py-1 text-muted layout-toggle-btn" 
                                    id="btnLayout2" 
                                    onclick="setBlogLayout('grid-2')" 
                                    title="2 Columns Grid">
                                <i class="bi bi-layout-split"></i> 
                                <span class="d-none d-md-inline ms-1">2 in Row</span>
                            </button>

                            <!-- List View -->
                            <button type="button" 
                                    class="btn btn-sm rounded-pill px-2.5 py-1 text-muted layout-toggle-btn" 
                                    id="btnLayoutList" 
                                    onclick="setBlogLayout('list')" 
                                    title="List View">
                                <i class="bi bi-view-list"></i> 
                                <span class="d-none d-md-inline ms-1">List View</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Articles Feed Container -->
            <?php if (empty($posts)): ?>
                <!-- Empty State -->
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
                    <div class="mb-3">
                        <i class="bi bi-journal-x text-muted" style="font-size: 3.5rem;"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-2">No Guides or Articles Found</h4>
                    <p class="text-muted mb-4" style="max-width: 480px; margin: 0 auto;">
                        We couldn't find any articles matching your current filter or search query. Try searching with different keywords or view all topics.
                    </p>
                    <div>
                        <a href="<?= url('blog') ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                            <i class="bi bi-arrow-left me-1"></i> View All Articles
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Dynamic Grid / List Container (Default: 3 in row) -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="blogPostsContainer">
                    <?php foreach ($posts as $post): ?>
                        <div class="col blog-post-col">
                            <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white blog-article-card transition-all">
                                
                                <!-- Card Image & Video Badge -->
                                <div class="blog-card-media-wrap position-relative overflow-hidden" style="background: #FFF9FA;">
                                    <a href="<?= url('blog/' . $post['slug']) ?>" class="d-block w-100 h-100">
                                        <img src="<?= upload_url('blog/' . $post['featured_image']) ?>" 
                                             alt="<?= e($post['title']) ?>" 
                                             class="w-100 h-100 object-fit-cover blog-card-img" 
                                             loading="lazy"
                                             style="aspect-ratio: 16/9; transition: transform 0.4s ease;">
                                    </a>
                                    <?php if (!empty($post['video_url'])): ?>
                                        <span class="badge bg-danger position-absolute top-0 end-0 m-2.5 px-2 py-1 rounded-pill fw-bold shadow-sm d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-play-circle-fill"></i> VIDEO
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Card Content -->
                                <div class="card-body p-3.5 p-xl-4 d-flex flex-column flex-grow-1 blog-card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <a href="<?= url('blog?category=' . urlencode($post['category_slug'])) ?>" class="badge bg-light text-primary fw-bold text-uppercase text-decoration-none px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                                            <?= e($post['category_name']) ?>
                                        </a>
                                        <small class="text-muted" style="font-size: 0.78rem;">
                                            <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($post['published_at'])) ?>
                                        </small>
                                    </div>

                                    <h5 class="fw-bold mb-2 blog-card-title lh-sm">
                                        <a href="<?= url('blog/' . $post['slug']) ?>" class="text-dark text-decoration-none hover-primary">
                                            <?= e($post['title']) ?>
                                        </a>
                                    </h5>

                                    <p class="text-muted small mb-4 flex-grow-1 blog-card-excerpt" style="line-height: 1.6;">
                                        <?= e($post['excerpt']) ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto blog-card-footer gap-2">
                                        <small class="text-muted text-truncate d-flex align-items-center gap-1.5" style="font-size: 0.78rem; min-width: 0;" title="<?= e($post['author']) ?>">
                                            <i class="bi bi-person-circle text-primary flex-shrink-0"></i> 
                                            <span class="text-truncate"><?= e($post['author']) ?></span>
                                        </small>
                                        <a href="<?= url('blog/' . $post['slug']) ?>" class="small text-primary fw-bold text-decoration-none d-inline-flex align-items-center gap-1 text-nowrap flex-shrink-0">
                                            Read Guide <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>

                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination (Displays when more than 20 articles exist across pages) -->
                <?php if (!empty($totalPages) && $totalPages > 1): ?>
                    <nav class="mt-5 pt-3" aria-label="Blog pagination">
                        <ul class="pagination justify-content-center gap-1 flex-wrap">
                            <?php 
                                $queryParams = $_GET;
                                unset($queryParams['page']);
                                $buildPageUrl = function($p) use ($queryParams) {
                                    $qp = array_merge($queryParams, ['page' => $p]);
                                    return url('blog') . '?' . http_build_query($qp);
                                };
                            ?>
                            <!-- Previous -->
                            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link rounded-pill px-3 shadow-none border" href="<?= $currentPage > 1 ? $buildPageUrl($currentPage - 1) : '#' ?>">
                                    <i class="bi bi-chevron-left me-1"></i> Prev
                                </a>
                            </li>

                            <!-- Pages -->
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                    <a class="page-link rounded-circle d-flex align-items-center justify-content-center shadow-none border <?= $i === $currentPage ? 'bg-primary text-white border-primary fw-bold' : '' ?>" style="width: 38px; height: 38px;" href="<?= $buildPageUrl($i) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link rounded-pill px-3 shadow-none border" href="<?= $currentPage < $totalPages ? $buildPageUrl($currentPage + 1) : '#' ?>">
                                    Next <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>

        </main>

    </div>
</div>

<!-- Custom Blog CSS for Layout Styles and Transitions -->
<style>
/* Hover animation on article cards */
.blog-article-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.blog-article-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08) !important;
}
.blog-article-card:hover .blog-card-img {
    transform: scale(1.04);
}
.hover-primary:hover {
    color: var(--afh-primary, #8B5E83) !important;
}

/* 4-in-a-row specific typography adjustments */
.blog-layout-grid-4 .blog-card-title {
    font-size: 0.98rem !important;
}
.blog-layout-grid-4 .blog-card-excerpt {
    font-size: 0.8rem !important;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 0.75rem !important;
}
.blog-layout-grid-4 .card-body {
    padding: 1rem !important;
}

/* 2-in-a-row adjustments */
.blog-layout-grid-2 .blog-card-title {
    font-size: 1.25rem !important;
}

/* List-view mode styling */
.blog-layout-list .blog-post-col {
    width: 100% !important;
}
.blog-layout-list .blog-article-card {
    flex-direction: row !important;
    align-items: stretch;
}
.blog-layout-list .blog-card-media-wrap {
    width: 360px !important;
    min-width: 360px;
    height: 100% !important;
}
.blog-layout-list .blog-card-media-wrap img {
    height: 100% !important;
    aspect-ratio: auto !important;
}
.blog-layout-list .blog-card-body {
    padding: 1.75rem !important;
}
.blog-layout-list .blog-card-title {
    font-size: 1.35rem !important;
}
.blog-layout-list .blog-card-excerpt {
    font-size: 0.95rem !important;
    -webkit-line-clamp: 4;
}

@media (max-width: 991.98px) {
    .blog-layout-list .blog-article-card {
        flex-direction: column !important;
    }
    .blog-layout-list .blog-card-media-wrap {
        width: 100% !important;
        min-width: 100%;
        height: auto !important;
    }
    .blog-layout-list .blog-card-media-wrap img {
        aspect-ratio: 16/9 !important;
    }
}
</style>

<!-- Layout Switcher Client Script with localStorage persistence -->
<script>
function setBlogLayout(layout) {
    const container = document.getElementById('blogPostsContainer');
    if (!container) return;

    // Reset buttons active states
    document.querySelectorAll('.layout-toggle-btn').forEach(btn => {
        btn.classList.remove('active', 'shadow-sm', 'bg-white', 'text-dark', 'fw-bold');
        btn.classList.add('text-muted');
        const icon = btn.querySelector('i');
        if (icon) icon.classList.remove('text-primary');
    });

    // Reset container classes
    container.className = 'row g-4';

    if (layout === 'grid-4') {
        container.classList.add('row-cols-1', 'row-cols-md-2', 'row-cols-lg-3', 'row-cols-xl-4', 'blog-layout-grid-4');
        const btn = document.getElementById('btnLayout4');
        if (btn) activateBtn(btn);
    } else if (layout === 'grid-2') {
        container.classList.add('row-cols-1', 'row-cols-md-2', 'blog-layout-grid-2');
        const btn = document.getElementById('btnLayout2');
        if (btn) activateBtn(btn);
    } else if (layout === 'list') {
        container.classList.add('row-cols-1', 'blog-layout-list');
        const btn = document.getElementById('btnLayoutList');
        if (btn) activateBtn(btn);
    } else {
        // Default: 3 in a row
        layout = 'grid-3';
        container.classList.add('row-cols-1', 'row-cols-md-2', 'row-cols-lg-3', 'blog-layout-grid-3');
        const btn = document.getElementById('btnLayout3');
        if (btn) activateBtn(btn);
    }

    try {
        localStorage.setItem('afh_blog_layout', layout);
    } catch (e) {}
}

function activateBtn(btn) {
    btn.classList.add('active', 'shadow-sm', 'bg-white', 'text-dark', 'fw-bold');
    btn.classList.remove('text-muted');
    const icon = btn.querySelector('i');
    if (icon) icon.classList.add('text-primary');
}

// Restore saved layout on initial page load
document.addEventListener('DOMContentLoaded', function() {
    let savedLayout = 'grid-3';
    try {
        savedLayout = localStorage.getItem('afh_blog_layout') || 'grid-3';
    } catch (e) {}
    setBlogLayout(savedLayout);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
