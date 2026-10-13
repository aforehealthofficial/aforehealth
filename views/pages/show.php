<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5" style="max-width: 880px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item active"><?= e($page['title']) ?></li>
        </ol>
    </nav>

    <div class="bg-white rounded-4 shadow-sm p-4 p-md-5 border">
        <h1 class="h2 fw-extrabold text-dark mb-4 pb-3 border-bottom"><?= e($page['title']) ?></h1>
        <div class="cms-page-content" style="line-height: 1.8; font-size: 1rem; color: #333;">
            <?= $page['content'] ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
