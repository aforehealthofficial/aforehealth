<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5 text-center" style="max-width: 600px;">
    <div class="display-1 fw-extrabold text-primary mb-3">404</div>
    <h1 class="h3 fw-bold text-dark mb-2">Page Not Found</h1>
    <p class="text-muted mb-4">
        The page you are looking for may have been moved, renamed, or is temporarily unavailable.
    </p>
    <div class="d-flex justify-content-center gap-3">
        <a href="<?= url() ?>" class="btn btn-afh-primary fw-bold">Return to Home</a>
        <a href="<?= url('shop') ?>" class="btn btn-outline-dark fw-bold">Browse Catalog</a>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
