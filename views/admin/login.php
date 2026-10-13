<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - AFOREHEALTH Control Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background: #1F1B21;">
    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white" style="max-width: 440px; width: 100%;">
        <div class="text-center mb-4">
            <img src="<?= asset('images/logo.svg') ?>" alt="AFOREHEALTH" height="42" class="mb-3">
            <h4 class="fw-bold text-dark m-0">Admin Control Panel</h4>
            <small class="text-muted">Enter administrative credentials to sign in</small>
        </div>

        <?php require __DIR__ . '/../components/alerts.php'; ?>

        <form action="<?= url('admin/login') ?>" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Admin Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="admin@aforehealth.com" value="admin@aforehealth.com" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-dark">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Admin@12345" value="Admin@12345" required>
                </div>
                <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">Default credentials pre-filled for evaluation.</small>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-dark py-2 fw-bold" style="background: #8B5E83; border-color: #8B5E83;">
                    Sign In to Dashboard →
                </button>
            </div>

            <div class="text-center pt-2 border-top">
                <a href="<?= url() ?>" class="small text-muted text-decoration-none">← Return to Storefront</a>
            </div>
        </form>
    </div>
</body>
</html>
