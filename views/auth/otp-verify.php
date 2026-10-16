<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5" style="max-width: 480px;">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center">
        
        <div class="mx-auto mb-3" style="width: 64px; height: 64px; border-radius: 50%; background: #FDF2F7; color: #8B5E83; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <h1 class="h3 fw-extrabold text-dark mb-1">Verify Mobile Number</h1>
        <p class="text-muted small mb-3">
            Enter the 6-digit verification code sent via SMS to
            <br>
            <strong class="text-dark font-monospace" id="vMobileDisplay">+91 <?= e($maskedPhone ?: $mobile) ?></strong>
            <a href="<?= $purpose === 'register' ? url('register') : url('login') ?>" class="small text-primary text-decoration-none fw-bold ms-1" title="Change number">
                <i class="bi bi-pencil-square"></i> Edit
            </a>
        </p>

        <div id="verifyAlert" class="alert d-none small py-2 mb-3 text-start" role="alert"></div>

        <form id="standaloneOtpForm" onsubmit="event.preventDefault(); verifyCode();">
            <input type="hidden" id="vMobile" value="<?= e($mobile) ?>">
            <input type="hidden" id="vPurpose" value="<?= e($purpose ?? 'login') ?>">
            
            <div class="mb-4">
                <label for="vOtp" class="form-label small fw-bold text-dark visually-hidden">6-digit Verification Code</label>
                <input type="text" id="vOtp" class="form-control text-center fs-3 tracking-widest font-monospace py-2" maxlength="6" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" placeholder="000000" autofocus required>
                <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Valid for 10 minutes. Do not share your OTP with anyone.</small>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-afh-primary py-2 fw-bold" id="verifyBtn">
                    Verify &amp; Continue
                </button>
            </div>
        </form>

        <div class="text-center pt-2">
            <div id="resendTimerContainer" class="small text-muted">
                Didn't receive the SMS? Resend code in <span id="resendTimer" class="fw-bold text-dark">60</span>s
            </div>
            <button type="button" id="resendBtn" class="btn btn-link btn-sm text-primary p-0 fw-bold" style="display: none;" onclick="resendCode()">
                Resend OTP via SMS
            </button>
        </div>

        <div class="text-center pt-3 border-top mt-4">
            <a href="<?= $purpose === 'register' ? url('register') : url('login') ?>" class="text-muted small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Return to <?= $purpose === 'register' ? 'Registration' : 'Login' ?>
            </a>
        </div>

    </div>
</div>

<script>
(function() {
    let countdownInterval = null;

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    function showAlert(message, type = 'danger') {
        const el = document.getElementById('verifyAlert');
        if (!el) return;
        el.className = `alert alert-${type} small py-2 mb-3 text-start`;
        el.textContent = message;
        el.classList.remove('d-none');
    }

    function hideAlert() {
        const el = document.getElementById('verifyAlert');
        if (el) el.classList.add('d-none');
    }

    function startCountdown(seconds = 60) {
        if (countdownInterval) clearInterval(countdownInterval);
        const timerContainer = document.getElementById('resendTimerContainer');
        const timerSpan = document.getElementById('resendTimer');
        const resendBtn = document.getElementById('resendBtn');

        let remaining = seconds;
        timerContainer.style.display = 'block';
        resendBtn.style.display = 'none';
        timerSpan.textContent = remaining;

        countdownInterval = setInterval(() => {
            remaining--;
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                timerContainer.style.display = 'none';
                resendBtn.style.display = 'inline-block';
            } else {
                timerSpan.textContent = remaining;
            }
        }, 1000);
    }

    window.verifyCode = function() {
        const mobile = document.getElementById('vMobile').value.trim();
        const purpose = document.getElementById('vPurpose').value.trim();
        const otpInput = document.getElementById('vOtp');
        const otp = otpInput ? otpInput.value.trim().replace(/[^0-9]/g, '') : '';
        const btn = document.getElementById('verifyBtn');

        hideAlert();

        if (otp.length < 4 || otp.length > 8) {
            showAlert('Please enter the 6-digit verification code.');
            if (otpInput) otpInput.focus();
            return;
        }

        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Verifying...';

        const formData = new FormData();
        formData.append('mobile', mobile);
        formData.append('otp', otp);
        formData.append('purpose', purpose);
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
                showAlert(data.message || 'Verification successful! Redirecting...', 'success');
                window.location.href = data.redirect_url || '/account';
            } else {
                btn.disabled = false;
                btn.innerHTML = originalText;
                showAlert(data.message || 'Invalid verification code. Please check and try again.', 'danger');
                if (data.locked) {
                    btn.disabled = true;
                    if (otpInput) otpInput.disabled = true;
                }
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            showAlert('Verification service error. Please try again.', 'danger');
        });
    };

    window.resendCode = function() {
        const mobile = document.getElementById('vMobile').value.trim();
        const purpose = document.getElementById('vPurpose').value.trim();
        const resendBtn = document.getElementById('resendBtn');

        hideAlert();

        const originalText = resendBtn.innerHTML;
        resendBtn.disabled = true;
        resendBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Sending...';

        const formData = new FormData();
        formData.append('mobile', mobile);
        formData.append('purpose', purpose);
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
            resendBtn.disabled = false;
            resendBtn.innerHTML = originalText;

            if (data.success) {
                showAlert(data.message || 'A new verification code has been sent.', 'success');
                startCountdown(data.resend_in_seconds || 60);
                const otpInput = document.getElementById('vOtp');
                if (otpInput) {
                    otpInput.value = '';
                    otpInput.disabled = false;
                    otpInput.focus();
                }
                const btn = document.getElementById('verifyBtn');
                if (btn) btn.disabled = false;
            } else {
                showAlert(data.message || 'Error sending verification code. Please try again later.', 'danger');
            }
        })
        .catch(() => {
            resendBtn.disabled = false;
            resendBtn.innerHTML = originalText;
            showAlert('Failed to connect to verification service.', 'danger');
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        startCountdown(60);

        const otpInput = document.getElementById('vOtp');
        if (otpInput) {
            otpInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                if (e.target.value.length === 6) {
                    window.verifyCode();
                }
            });
        }
    });
})();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
