<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5" style="max-width: 460px;">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center">
        
        <div class="mx-auto mb-3" style="width: 64px; height: 64px; border-radius: 50%; background: #FDF2F7; color: #8B5E83; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="bi bi-shield-check"></i>
        </div>

        <h1 class="h3 fw-extrabold text-dark mb-1">OTP Verification</h1>
        <p class="text-muted small mb-4">Enter the 6-digit code sent to your mobile number <strong>+91 <?= e($mobile) ?></strong></p>

        <form id="standaloneOtpForm" onsubmit="event.preventDefault(); verifyCode();">
            <input type="hidden" id="vMobile" value="<?= e($mobile) ?>">
            
            <div class="mb-4">
                <input type="text" id="vOtp" class="form-control text-center fs-3 tracking-widest font-monospace py-2" maxlength="6" placeholder="000000" autofocus required>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-afh-primary py-2 fw-bold" id="verifyBtn">Verify &amp; Continue</button>
            </div>
        </form>

        <div class="text-center pt-2">
            <span class="text-muted small">Didn't receive the code?</span>
            <button type="button" class="btn btn-link btn-sm text-primary p-0 ms-1 fw-bold" onclick="resendCode()">Resend OTP</button>
        </div>

    </div>
</div>

<script>
function verifyCode() {
    const mobile = document.getElementById('vMobile').value;
    const otp = document.getElementById('vOtp').value.trim();

    if (otp.length < 4) {
        showToast('Please enter a valid OTP code.', 'danger');
        return;
    }

    const btn = document.getElementById('verifyBtn');
    btn.disabled = true;

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
            showToast(data.message || 'Verification failed', 'danger');
            btn.disabled = false;
        }
    })
    .catch(() => {
        showToast('Verification failed.', 'danger');
        btn.disabled = false;
    });
}

function resendCode() {
    const mobile = document.getElementById('vMobile').value;
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
            showToast(data.message, 'success');
            if (data.demo_otp) {
                document.getElementById('vOtp').value = data.demo_otp;
            }
        }
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
