<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4" style="max-width: 860px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">My Account</a></li>
            <li class="breadcrumb-item active">Profile &amp; Password</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Profile Info Form -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h5 class="fw-bold mb-3 pb-2 border-bottom">Personal Details</h5>
                <form action="<?= url('account/profile/update') ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name *</label>
                        <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= e($user['email'] ?? '') ?>" placeholder="name@example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Mobile Number</label>
                        <input type="text" class="form-control bg-light" value="+91 <?= e($user['mobile']) ?>" disabled>
                        <a href="<?= url('account/change-mobile') ?>" class="small text-primary fw-bold mt-1 d-inline-block">Change Mobile Number →</a>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" value="<?= e($user['dob'] ?? '') ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="female" <?= ($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="prefer_not_to_say" <?= ($user['gender'] ?? '') === 'prefer_not_to_say' ? 'selected' : '' ?>>Prefer not to say</option>
                            <option value="other" <?= ($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-afh-primary w-100 fw-bold">Save Profile</button>
                </form>
            </div>
        </div>

        <!-- Change Password Form -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <h5 class="fw-bold mb-3 pb-2 border-bottom">Change Password</h5>
                <form action="<?= url('account/change-password') ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Current Password *</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">New Password (Min. 6 chars) *</label>
                        <input type="password" name="new_password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Confirm New Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <button type="submit" class="btn btn-outline-dark w-100 fw-bold">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
