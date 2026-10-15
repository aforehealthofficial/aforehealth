<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5" style="max-width: 460px;">
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white text-center">
        
        <div class="mx-auto mb-3" style="width: 64px; height: 64px; border-radius: 50%; background: #FDF2F7; color: #8B5E83; display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
            <i class="bi bi-shield-check"></i>
        </div>

        <h1 class="h3 fw-extrabold text-dark mb-1">OTP Verification</h1>
        <p class="text-muted small mb-3">Enter the 6-digit code sent to your mobile number <strong>+91 <?= e($mobile) ?></strong></p>

        <?php if (!empty($latestOtp)): ?>
            <div class="alert alert-warning border-0 rounded-4 p-3 mb-4 shadow-sm text-start" style="background: #FFF9E6; border: 1px solid #FFE082 !important;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="small fw-bold text-dark"><i class="bi bi-shield-lock-fill text-warning me-1"></i> Testing Mode OTP</span>
                    <span class="badge bg-dark rounded-pill px-2">Ready</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <div>
                        <span class="small text-muted d-block" style="font-size: 0.725rem;">Your on-screen verification OTP:</span>
                        <span class="fs-4 font-monospace fw-extrabold text-primary tracking-widest" id="standaloneOtpDisplay"><?= e($latestOtp) ?></span>
                    </div>
                    <button type="button" class="btn btn-sm btn-afh-primary rounded-pill px-3 fw-bold shadow-sm" onclick="document.getElementById('vOtp').value='<?= e($latestOtp) ?>'">
                        Auto-Fill
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div id="dynamicOtpBox" class="alert alert-warning border-0 rounded-4 p-3 mb-4 shadow-sm text-start" style="display: none; background: #FFF9E6; border: 1px solid #FFE082 !important;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="small fw-bold text-dark"><i class="bi bi-shield-lock-fill text-warning me-1"></i> Testing Mode OTP</span>
                    <span class="badge bg-dark rounded-pill px-2">Ready</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <div>
                        <span class="small text-muted d-block" style="font-size: 0.725rem;">Your on-screen verification OTP:</span>
                        <span class="fs-4 font-monospace fw-extrabold text-primary tracking-widest" id="standaloneOtpDisplay">------</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-afh-primary rounded-pill px-3 fw-bold shadow-sm" onclick="document.getElementById('vOtp').value=document.getElementById('standaloneOtpDisplay').textContent">
                        Auto-Fill
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <form id="standaloneOtpForm" onsubmit="event.preventDefault(); verifyCode();">
            <input type="hidden" id="vMobile" value="<?= e($mobile) ?>">
            
            <div class="mb-4">
                <input type="text" id="vOtp" class="form-control text-center fs-3 tracking-widest font-monospace py-2" maxlength="6" placeholder="000000" value="<?= !empty($latestOtp) ? e($latestOtp) : '' ?>" autofocus required>
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
                const disp = document.getElementById('standaloneOtpDisplay');
                if (disp) disp.textContent = data.demo_otp;
                const box = document.getElementById('dynamicOtpBox');
                if (box) box.style.display = 'block';
                showToast('Test OTP code: ' + data.demo_otp + ' (Auto-filled on screen)', 'success');
            }
        } else {
            showToast(data.message || 'Error sending OTP', 'danger');
        }
    })
    .catch(() => showToast('Failed to resend code.', 'danger'));
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
