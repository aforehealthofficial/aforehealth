<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5" style="max-width: 480px;">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
        
        <div class="text-center mb-4">
            <h1 class="h3 fw-extrabold text-dark mb-1">Welcome Back</h1>
            <p class="text-muted small">Login to access your orders, saved addresses and exclusive discounts.</p>
        </div>

        <!-- Auth Tabs (Password vs OTP) -->
        <ul class="nav nav-pills nav-justified mb-4 p-1 rounded-pill bg-light" id="loginTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-bold small" id="password-tab" data-bs-toggle="pill" data-bs-target="#password-login" type="button" role="tab">With Password</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold small" id="otp-tab" data-bs-toggle="pill" data-bs-target="#otp-login" type="button" role="tab">With Mobile OTP</button>
            </li>
        </ul>

        <div class="tab-content" id="loginTabsContent">
            
            <!-- 1. Password Login -->
            <div class="tab-pane fade show active" id="password-login" role="tabpanel">
                <form action="<?= url('login') ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Mobile Number (10 Digits)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">+91</span>
                            <input type="tel" name="mobile" class="form-control" placeholder="9876543210" maxlength="10" pattern="[0-9]{10}" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-afh-primary py-2 fw-bold">Login to Account</button>
                    </div>
                </form>
            </div>

            <!-- 2. OTP Login -->
            <div class="tab-pane fade" id="otp-login" role="tabpanel">
                <div id="otpStep1">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">+91</span>
                            <input type="tel" id="otpMobileInput" class="form-control" placeholder="9876543210" maxlength="10">
                        </div>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="button" class="btn btn-afh-primary py-2 fw-bold" onclick="requestOtp()">Send Verification OTP</button>
                    </div>
                </div>

                <div id="otpStep2" style="display: none;">
                    <div class="alert alert-warning border-0 rounded-4 p-3 mb-3 shadow-sm text-start" style="background: #FFF9E6; border: 1px solid #FFE082 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="small fw-bold text-dark"><i class="bi bi-shield-lock-fill text-warning me-1"></i> Testing Mode OTP</span>
                            <span class="badge bg-dark rounded-pill px-2">Ready</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <div>
                                <span class="small text-muted d-block" style="font-size: 0.725rem;">Your on-screen verification OTP:</span>
                                <span class="fs-4 font-monospace fw-extrabold text-primary tracking-widest" id="testOtpDisplay">------</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-afh-primary rounded-pill px-3 fw-bold shadow-sm" onclick="document.getElementById('otpCodeInput').value=document.getElementById('testOtpDisplay').textContent">
                                Auto-Fill
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-info small py-2 mb-3" id="otpSentMsg">
                        OTP code generated for mobile.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Enter 6-Digit OTP</label>
                        <input type="text" id="otpCodeInput" class="form-control text-center fs-4 tracking-widest font-monospace" maxlength="6" placeholder="000000">
                    </div>
                    <div class="d-grid mb-3">
                        <button type="button" class="btn btn-afh-primary py-2 fw-bold" onclick="verifyOtpCode()">Verify &amp; Login</button>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn btn-link btn-sm text-muted p-0" onclick="requestOtp()">Resend OTP Code</button>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-center pt-3 border-top mt-3">
            <span class="text-muted small">New to AFOREHEALTH?</span>
            <a href="<?= url('register') ?>" class="text-primary small fw-bold ms-1">Create Account</a>
        </div>

    </div>
</div>

<script>
function requestOtp() {
    const mobile = document.getElementById('otpMobileInput').value.trim();
    if (mobile.length !== 10) {
        showToast('Please enter a valid 10-digit mobile number.', 'danger');
        return;
    }

    const formData = new FormData();
    formData.append('mobile', mobile);

    fetch('/otp/send', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('otpStep1').style.display = 'none';
            document.getElementById('otpStep2').style.display = 'block';
            document.getElementById('otpSentMsg').textContent = data.message;
            if (data.demo_otp) {
                document.getElementById('testOtpDisplay').textContent = data.demo_otp;
                document.getElementById('otpCodeInput').value = data.demo_otp;
                showToast('Test OTP is: ' + data.demo_otp + ' (Auto-filled on screen)', 'success');
            } else {
                showToast('OTP sent successfully!', 'success');
            }
        } else {
            showToast(data.message || 'Error sending OTP', 'danger');
        }
    })
    .catch(() => showToast('Could not send OTP.', 'danger'));
}

function verifyOtpCode() {
    const mobile = document.getElementById('otpMobileInput').value.trim();
    const otp = document.getElementById('otpCodeInput').value.trim();

    if (otp.length < 4) {
        showToast('Please enter the OTP code.', 'danger');
        return;
    }

    const formData = new FormData();
    formData.append('mobile', mobile);
    formData.append('otp', otp);

    fetch('/otp/verify', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            window.location.href = data.redirect_url || '/account';
        } else {
            showToast(data.message || 'Invalid OTP code', 'danger');
        }
    })
    .catch(() => showToast('Verification error.', 'danger'));
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
