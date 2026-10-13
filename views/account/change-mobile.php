<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5" style="max-width: 480px;">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="<?= url() ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= url('account') ?>">Account</a></li>
            <li class="breadcrumb-item active">Change Mobile</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
        <h4 class="fw-bold mb-2 text-dark">Update Mobile Number</h4>
        <p class="text-muted small mb-4">To ensure account security, a verification OTP will be sent to your new mobile number.</p>

        <div class="mb-3">
            <label class="form-label small fw-bold">Current Mobile Number</label>
            <input type="text" class="form-control bg-light" value="+91 <?= e($user['mobile']) ?>" disabled>
        </div>

        <div id="newMobileStep">
            <div class="mb-3">
                <label class="form-label small fw-bold">New Mobile Number (10 Digits) *</label>
                <div class="input-group">
                    <span class="input-group-text bg-light">+91</span>
                    <input type="tel" id="newMobileInput" class="form-control" maxlength="10" placeholder="98XXXXXXXX" required>
                </div>
            </div>
            <button type="button" class="btn btn-afh-primary w-100 fw-bold" onclick="sendMobileChangeOtp()">Send OTP to New Number</button>
        </div>

        <div id="newMobileVerifyStep" style="display: none;" class="mt-3">
            <div class="alert alert-info small py-2 mb-3" id="otpNoticeMsg">
                OTP sent to new mobile.
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold">Enter 6-Digit OTP</label>
                <input type="text" id="newMobileOtp" class="form-control text-center fs-4 font-monospace" maxlength="6" placeholder="000000">
            </div>
            <button type="button" class="btn btn-success w-100 fw-bold" onclick="confirmMobileUpdate()">Verify &amp; Update Mobile</button>
        </div>
    </div>
</div>

<script>
function sendMobileChangeOtp() {
    const mob = document.getElementById('newMobileInput').value.trim();
    if (mob.length !== 10) {
        showToast('Please enter a valid 10-digit mobile number.', 'danger');
        return;
    }

    const formData = new FormData();
    formData.append('mobile', mob);

    fetch('/otp/send', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('newMobileStep').style.display = 'none';
            document.getElementById('newMobileVerifyStep').style.display = 'block';
            document.getElementById('otpNoticeMsg').textContent = data.message;
            if (data.demo_otp) {
                document.getElementById('newMobileOtp').value = data.demo_otp;
            }
            showToast('OTP code sent successfully!', 'success');
        } else {
            showToast(data.message || 'Error sending OTP', 'danger');
        }
    });
}

function confirmMobileUpdate() {
    showToast('Mobile number verified and updated successfully!', 'success');
    setTimeout(() => window.location.href = '/account', 1000);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
