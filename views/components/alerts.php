<?php
$flashes = get_flashes();
if (!empty($flashes)): ?>
<div class="container mt-3">
    <?php foreach ($flashes as $flash): 
        $alertClass = match($flash['type']) {
            'success' => 'alert-success',
            'danger', 'error' => 'alert-danger',
            'warning' => 'alert-warning',
            default => 'alert-info'
        };
    ?>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center gap-2">
                <?php if ($flash['type'] === 'success'): ?>
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <?php elseif ($flash['type'] === 'danger'): ?>
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                <?php else: ?>
                    <i class="bi bi-info-circle-fill text-primary fs-5"></i>
                <?php endif; ?>
                <span><?= e($flash['message']) ?></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
