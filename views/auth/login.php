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
                    <?= csrf_field() ?>
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
                
                <!-- Step 1: Mobile Entry -->
                <div id="otpStep1">
                    <div id="otpAlert1" class="alert d-none small py-2 mb-3" role="alert"></div>

                    <div class="mb-3">
                        <label for="otpMobileInput" class="form-label small fw-bold text-dark">Mobile Number (10 Digits)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">+91</span>
                            <input type="tel" id="otpMobileInput" class="form-control" placeholder="9876543210" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" autofocus>
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">We will send a 6-digit OTP via SMS to verify your number.</small>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="button" id="sendOtpBtn" class="btn btn-afh-primary py-2 fw-bold" onclick="requestOtp(false)">
                            Send Verification OTP
                        </button>
                    </div>
                </div>

                <!-- Step 2: OTP Verification -->
                <div id="otpStep2" style="display: none;">
                    <div id="otpAlert2" class="alert d-none small py-2 mb-3" role="alert"></div>

                    <div class="bg-light p-3 rounded-3 mb-3 text-center">
                        <span class="small text-muted d-block mb-1">Verification code sent to</span>
                        <div class="d-flex align-items-center justify-content-center">
                            <span class="fw-bold text-dark font-monospace me-2" id="otpMaskedPhoneDisplay">+91 ----------</span>
                            <button type="button" class="btn btn-link btn-sm text-primary p-0 text-decoration-none fw-bold" onclick="editMobileNumber()" title="Edit mobile number">
                                <i class="bi bi-pencil-square"></i> Change
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="otpCodeInput" class="form-label small fw-bold text-dark">Enter 6-Digit Verification Code</label>
                        <input type="text" id="otpCodeInput" class="form-control text-center fs-3 tracking-widest font-monospace py-2" maxlength="6" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" placeholder="000000">
                    </div>

                    <div class="d-grid mb-3">
                        <button type="button" id="verifyOtpBtn" class="btn btn-afh-primary py-2 fw-bold" onclick="verifyOtpCode()">
                            Verify &amp; Login
                        </button>
                    </div>

                    <div class="text-center">
                        <div id="resendTimerContainer" class="small text-muted">
                            Resend code in <span id="resendCountdown" class="fw-bold text-dark">60</span>s
                        </div>
                        <button type="button" id="resendOtpBtn" class="btn btn-link btn-sm text-primary p-0 fw-bold" style="display: none;" onclick="requestOtp(true)">
                            Resend OTP Code
                        </button>
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
(function() {
    let countdownInterval = null;

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function showAlert(alertElId, message, type = 'danger', isHtml = false) {
        const el = document.getElementById(alertElId);
        if (!el) return;
        el.className = `alert alert-${type} small py-2 mb-3`;
        if (isHtml) {
            el.innerHTML = message;
        } else {
            el.textContent = message;
        }
        el.classList.remove('d-none');
    }

    function hideAlert(alertElId) {
        const el = document.getElementById(alertElId);
        if (el) el.classList.add('d-none');
    }

    function startResendCountdown(seconds = 60) {
        if (countdownInterval) clearInterval(countdownInterval);
        const timerContainer = document.getElementById('resendTimerContainer');
        const countdownSpan = document.getElementById('resendCountdown');
        const resendBtn = document.getElementById('resendOtpBtn');

        let remaining = seconds;
        timerContainer.style.display = 'block';
        resendBtn.style.display = 'none';
        countdownSpan.textContent = remaining;

        countdownInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                timerContainer.style.display = 'none';
                resendBtn.style.display = 'inline-block';
            } else {
                countdownSpan.textContent = remaining;
            }
        }, 1000);
    }

    window.requestOtp = function(isResend = false) {
        const mobileInput = document.getElementById('otpMobileInput');
        const mobile = mobileInput ? mobileInput.value.trim().replace(/[^0-9]/g, '') : '';
        const sendBtn = isResend ? document.getElementById('resendOtpBtn') : document.getElementById('sendOtpBtn');
        const alertId = isResend ? 'otpAlert2' : 'otpAlert1';

        hideAlert(alertId);

        if (mobile.length !== 10 || !['6', '7', '8', '9'].includes(mobile[0])) {
            showAlert(alertId, 'Please enter a valid 10-digit Indian mobile number (e.g. 9876543210).', 'danger');
            return;
        }

        const originalBtnText = sendBtn.innerHTML;
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Sending...';

        const formData = new FormData();
        formData.append('mobile', mobile);
        formData.append('purpose', 'login');
        formData.append('_csrf_token', getCsrfToken());

        fetch('/otp/send', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(res => res.json().then(data => ({ status: res.status, data })))
        .then(({ status, data }) => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalBtnText;

            if (data.success) {
                document.getElementById('otpStep1').style.display = 'none';
                document.getElementById('otpStep2').style.display = 'block';
                document.getElementById('otpMaskedPhoneDisplay').textContent = data.masked_phone || ('+91 ' + mobile);
                
                showAlert('otpAlert2', data.message || 'Verification code sent to your mobile.', 'success');
                startResendCountdown(data.resend_in_seconds || 60);

                const codeInput = document.getElementById('otpCodeInput');
                if (codeInput) {
                    codeInput.value = '';
                    codeInput.focus();
                }
            } else {
                if (data.not_found) {
                    showAlert(alertId, 'No account found with this number. <a href="<?= url('register') ?>" class="alert-link fw-bold">Create an account here</a>.', 'warning', true);
                } else {
                    showAlert(alertId, data.message || 'Could not send verification code.', 'danger');
                }
            }
        })
        .catch(() => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalBtnText;
            showAlert(alertId, 'Network error. Please check your connection and try again.', 'danger');
        });
    };

    window.verifyOtpCode = function() {
        const mobile = document.getElementById('otpMobileInput').value.trim().replace(/[^0-9]/g, '');
        const otpInput = document.getElementById('otpCodeInput');
        const otp = otpInput ? otpInput.value.trim() : '';
        const verifyBtn = document.getElementById('verifyOtpBtn');

        hideAlert('otpAlert2');

        if (otp.length < 4 || otp.length > 8) {
            showAlert('otpAlert2', 'Please enter a valid 6-digit verification code.', 'danger');
            if (otpInput) otpInput.focus();
            return;
        }

        const originalText = verifyBtn.innerHTML;
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Verifying...';

        const formData = new FormData();
        formData.append('mobile', mobile);
        formData.append('otp', otp);
        formData.append('purpose', 'login');
        formData.append('_csrf_token', getCsrfToken());

        fetch('/otp/verify', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(res => res.json().then(data => ({ status: res.status, data })))
        .then(({ status, data }) => {
            if (data.success) {
                showAlert('otpAlert2', data.message || 'Verified successfully! Redirecting...', 'success');
                window.location.href = data.redirect_url || '/account';
            } else {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = originalText;
                showAlert('otpAlert2', data.message || 'Verification failed. Please try again.', 'danger');
                if (data.locked) {
                    verifyBtn.disabled = true;
                }
            }
        })
        .catch(() => {
            verifyBtn.disabled = false;
            verifyBtn.innerHTML = originalText;
            showAlert('otpAlert2', 'Verification service error. Please try again.', 'danger');
        });
    };

    window.editMobileNumber = function() {
        if (countdownInterval) clearInterval(countdownInterval);
        hideAlert('otpAlert2');
        hideAlert('otpAlert1');
        document.getElementById('otpStep2').style.display = 'none';
        document.getElementById('otpStep1').style.display = 'block';
        const mobileInput = document.getElementById('otpMobileInput');
        if (mobileInput) mobileInput.focus();
    };

    // Auto-submit when 6 digits are typed
    document.addEventListener('DOMContentLoaded', () => {
        const otpInput = document.getElementById('otpCodeInput');
        if (otpInput) {
            otpInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                if (e.target.value.length === 6) {
                    window.verifyOtpCode();
                }
            });
            otpInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.verifyOtpCode();
                }
            });
        }

        const mobileInput = document.getElementById('otpMobileInput');
        if (mobileInput) {
            mobileInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    window.requestOtp(false);
                }
            });
        }
    });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
