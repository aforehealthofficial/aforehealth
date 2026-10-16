<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5" style="max-width: 520px;">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
        
        <div class="text-center mb-3">
            <h1 class="h3 fw-extrabold text-dark mb-1">Create an Account</h1>
            <p class="text-muted small">Join AFOREHEALTH for personalized sizing, order tracking &amp; rewards.</p>
        </div>

        <div class="alert alert-light border d-flex align-items-center justify-content-between p-3 rounded-3 mb-4 shadow-sm" style="background: #faf8f9;">
            <div class="pe-2">
                <div class="fw-bold text-dark small"><i class="bi bi-shield-check text-primary me-1"></i> Prefer Mobile OTP?</div>
                <div class="text-muted" style="font-size: 0.78rem;">Instant passwordless login &amp; automatic account creation with Mobile OTP.</div>
            </div>
            <a href="<?= url('login') ?>" class="btn btn-sm btn-outline-primary fw-bold text-nowrap">Use Mobile OTP →</a>
        </div>

        <form action="<?= url('register') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="referral_code" value="<?= e($referralCode ?? '') ?>">
            <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Full Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Ananya Patel" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Mobile Number (10 Digits) *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">+91</span>
                    <input type="tel" name="mobile" class="form-control" placeholder="9811122233" maxlength="10" pattern="[0-9]{10}" required>
                </div>
                <small class="text-muted" style="font-size: 0.75rem;">Your mobile number is your primary AFOREHEALTH login ID.</small>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-sm-6">
                    <label class="form-label small fw-bold text-dark">Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 chars" required minlength="6">
                </div>
                <div class="col-sm-6">
                    <label class="form-label small fw-bold text-dark">Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required minlength="6">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-bold text-dark">Email Address (Optional)</label>
                <input type="email" name="email" class="form-control" placeholder="For receiving order invoices &amp; dispatch updates">
            </div>

            <div class="row g-2 mb-4">
                <div class="col-sm-6">
                    <label class="form-label small fw-bold text-dark">Date of Birth (Optional)</label>
                    <input type="date" name="dob" class="form-control">
                </div>
                <div class="col-sm-6">
                    <label class="form-label small fw-bold text-dark">Gender (Optional)</label>
                    <select name="gender" class="form-select">
                        <option value="female" selected>Female</option>
                        <option value="prefer_not_to_say">Prefer not to say</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-afh-primary py-2 fw-bold">Register Account →</button>
            </div>
        </form>

        <div class="text-center pt-3 border-top mt-2">
            <span class="text-muted small">Already have an account?</span>
            <a href="<?= url('login') ?>" class="text-primary small fw-bold ms-1">Login here</a>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
