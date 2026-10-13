<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5 text-center" style="max-width: 600px;">
    <div class="display-1 fw-extrabold text-danger mb-3">500</div>
    <h1 class="h3 fw-bold text-dark mb-2">Internal Server Error</h1>
    <p class="text-muted mb-4">
        Something unexpected occurred on our end. Our technical team has been notified. Please try refreshing or visit our homepage.
    </p>
    <a href="<?= url() ?>" class="btn btn-afh-primary fw-bold">Return to Home</a>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
