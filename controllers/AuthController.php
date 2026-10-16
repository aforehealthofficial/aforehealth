<?php
namespace Controllers;

use Models\User;
use Models\Referral;
use Services\TwoFactorService;
use Exception;
use InvalidArgumentException;

class AuthController {
    public function showLogin(): void {
        view('auth.login', [
            'pageTitle' => 'Login with Mobile - AFOREHEALTH'
        ]);
    }

    public function login(): void {
        // CSRF verification
        $token = $_POST['_csrf_token'] ?? null;
        if (!verify_csrf_token($token)) {
            set_flash('danger', 'Security token expired. Please try again.');
            redirect('/login');
        }

        $twoFactor = new TwoFactorService();
        try {
            $phoneData = $twoFactor->normalizePhone($_POST['mobile'] ?? '');
            $mobile = $phoneData['local'];
        } catch (InvalidArgumentException $ie) {
            set_flash('danger', $ie->getMessage());
            redirect('/login');
        }

        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByMobile($mobile);

        if (!$user || !password_verify($password, $user['password'])) {
            set_flash('danger', 'Invalid mobile number or password.');
            redirect('/login');
        }

        if ($user['status'] !== 'active') {
            set_flash('danger', 'Your account has been deactivated. Please contact support.');
            redirect('/login');
        }

        // Establish secure session with session ID regeneration
        session_regenerate_id(true);
        $referralModel = new Referral();
        $myRefCode = $referralModel->getReferralCode((int)$user['id']);

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'mobile' => $user['mobile'],
            'email' => $user['email'],
            'referral_code' => $myRefCode
        ];

        set_flash('success', 'Welcome back, ' . $user['name'] . '!');
        $intended = $_SESSION['intended_url'] ?? '/account';
        unset($_SESSION['intended_url']);
        redirect($intended);
    }

    public function showRegister(): void {
        if (!empty($_GET['ref'])) {
            $_SESSION['pending_referral_code'] = trim($_GET['ref']);
        }
        view('auth.register', [
            'pageTitle' => 'Create AFOREHEALTH Account - AFOREHEALTH',
            'referralCode' => $_GET['ref'] ?? $_SESSION['pending_referral_code'] ?? null
        ]);
    }

    public function register(): void {
        // CSRF verification
        $token = $_POST['_csrf_token'] ?? null;
        if (!verify_csrf_token($token)) {
            set_flash('danger', 'Security token expired. Please try again.');
            redirect('/register');
        }

        $twoFactor = new TwoFactorService();
        try {
            $phoneData = $twoFactor->normalizePhone($_POST['mobile'] ?? '');
            $mobile = $phoneData['local'];
        } catch (InvalidArgumentException $ie) {
            set_flash('danger', $ie->getMessage());
            redirect('/register');
        }

        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $dob = $_POST['dob'] ?? null;
        $gender = $_POST['gender'] ?? 'female';
        $refCode = trim($_POST['referral_code'] ?? $_SESSION['pending_referral_code'] ?? '');

        if (empty($name)) {
            set_flash('danger', 'Please enter your full name.');
            redirect('/register');
        }

        if (strlen($password) < 6) {
            set_flash('danger', 'Password must be at least 6 characters long.');
            redirect('/register');
        }

        if ($password !== $confirmPassword) {
            set_flash('danger', 'Passwords do not match.');
            redirect('/register');
        }

        $userModel = new User();
        if ($userModel->findByMobile($mobile)) {
            set_flash('danger', 'An account already exists with this mobile number. Please login.');
            redirect('/login');
        }

        // Check rate limit before sending OTP
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        try {
            $userModel->checkSendRateLimit($mobile, $ip);
        } catch (Exception $e) {
            set_flash('danger', $e->getMessage());
            redirect('/register');
        }

        // Store registration inputs in server session awaiting OTP verification
        $_SESSION['pending_registration'] = [
            'name' => $name,
            'mobile' => $mobile,
            'password' => $password,
            'email' => !empty($_POST['email']) ? trim($_POST['email']) : null,
            'dob' => $dob,
            'gender' => $gender,
            'referral_code' => $refCode
        ];

        // Send OTP via 2Factor AUTOGEN API
        $sendRes = $twoFactor->sendAutogenOtp($mobile);
        if (!$sendRes['success']) {
            set_flash('danger', $sendRes['message']);
            redirect('/register');
        }

        // Record provider session ID in DB
        $userModel->recordTwoFactorSession($mobile, 'register', $sendRes['session_id'], $ip);
        $_SESSION['otp_mobile'] = $mobile;
        $_SESSION['otp_purpose'] = 'register';
        $_SESSION['otp_session_id'] = $sendRes['session_id'];

        set_flash('success', 'A 6-digit verification code has been sent to ' . $twoFactor->maskPhone($mobile) . '.');
        redirect('/otp-verify?purpose=register&mobile=' . urlencode($mobile));
    }

    public function showOtpVerify(): void {
        $purpose = $_GET['purpose'] ?? $_SESSION['otp_purpose'] ?? 'login';
        $mobile = preg_replace('/[^0-9]/', '', $_GET['mobile'] ?? $_SESSION['otp_mobile'] ?? '');
        $twoFactor = new TwoFactorService();
        $maskedPhone = !empty($mobile) ? $twoFactor->maskPhone($mobile) : '';

        view('auth.otp-verify', [
            'pageTitle' => 'Verify OTP - AFOREHEALTH',
            'mobile' => $mobile,
            'maskedPhone' => $maskedPhone,
            'purpose' => $purpose
        ]);
    }

    /**
     * Send or resend OTP via 2Factor.in AUTOGEN API
     */
    public function sendOtp(): void {
        // CSRF verification
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!verify_csrf_token($token)) {
            json_response(['success' => false, 'message' => 'Security token expired. Please refresh the page.'], 419);
        }

        $twoFactor = new TwoFactorService();
        try {
            $phoneData = $twoFactor->normalizePhone($_POST['mobile'] ?? '');
            $mobile = $phoneData['local'];
        } catch (InvalidArgumentException $ie) {
            json_response(['success' => false, 'message' => $ie->getMessage()], 400);
        }

        $purpose = trim($_POST['purpose'] ?? 'login');
        if (!in_array($purpose, ['login', 'register', 'change_mobile', 'checkout'], true)) {
            $purpose = 'login';
        }

        $userModel = new User();
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        // Context checks
        if ($purpose === 'login') {
            $user = $userModel->findByMobile($mobile);
            if (!$user) {
                json_response([
                    'success' => false,
                    'message' => 'No account found with this mobile number. Please create an account first.',
                    'not_found' => true
                ], 404);
            }
            if ($user['status'] !== 'active') {
                json_response([
                    'success' => false,
                    'message' => 'Your account has been deactivated. Please contact support.'
                ], 403);
            }
        } elseif ($purpose === 'register') {
            if ($userModel->findByMobile($mobile)) {
                json_response([
                    'success' => false,
                    'message' => 'An account already exists with this mobile number. Please login.',
                    'already_exists' => true
                ], 409);
            }
        }

        // Rate limit check: max 3 requests per 10 minutes per phone
        try {
            $userModel->checkSendRateLimit($mobile, $ip);
        } catch (Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 429);
        }

        // Send OTP using 2Factor AUTOGEN API
        $sendRes = $twoFactor->sendAutogenOtp($mobile);
        if (!$sendRes['success']) {
            json_response([
                'success' => false,
                'message' => $sendRes['message'],
                'code' => $sendRes['code'] ?? 'SEND_FAILED'
            ], 400);
        }

        // Record provider session ID in database
        $userModel->recordTwoFactorSession($mobile, $purpose, $sendRes['session_id'], $ip);
        $_SESSION['otp_mobile'] = $mobile;
        $_SESSION['otp_purpose'] = $purpose;
        $_SESSION['otp_session_id'] = $sendRes['session_id'];

        json_response([
            'success' => true,
            'message' => 'Verification code sent to ' . $twoFactor->maskPhone($mobile) . '.',
            'masked_phone' => $twoFactor->maskPhone($mobile),
            'resend_in_seconds' => 60
        ]);
    }

    /**
     * Verify OTP via 2Factor.in Verification API
     */
    public function verifyOtp(): void {
        // CSRF verification
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!verify_csrf_token($token)) {
            json_response(['success' => false, 'message' => 'Security token expired. Please refresh the page.'], 419);
        }

        $twoFactor = new TwoFactorService();
        try {
            $phoneData = $twoFactor->normalizePhone($_POST['mobile'] ?? $_SESSION['otp_mobile'] ?? '');
            $mobile = $phoneData['local'];
        } catch (InvalidArgumentException $ie) {
            json_response(['success' => false, 'message' => $ie->getMessage()], 400);
        }

        $otp = trim($_POST['otp'] ?? '');
        $purpose = trim($_POST['purpose'] ?? $_SESSION['otp_purpose'] ?? 'login');

        if (empty($otp) || strlen($otp) < 4 || strlen($otp) > 8) {
            json_response(['success' => false, 'message' => 'Please enter a valid OTP code.'], 400);
        }

        $userModel = new User();
        $activeSession = $userModel->getActiveTwoFactorSession($mobile, $purpose);

        if (!$activeSession || empty($activeSession['twofactor_session_id'])) {
            json_response(['success' => false, 'message' => 'OTP has expired or session is invalid. Please request a new code.'], 400);
        }

        if ((int)$activeSession['attempts'] >= 5) {
            json_response(['success' => false, 'message' => 'Too many failed attempts. This OTP session has been locked for security. Please request a new OTP.'], 429);
        }

        // Verify with 2Factor provider
        $verifyRes = $twoFactor->verifyOtp($activeSession['twofactor_session_id'], $otp, $mobile);

        if (!$verifyRes['success']) {
            $attempts = $userModel->recordFailedAttempt((int)$activeSession['id']);
            $remaining = max(0, 5 - $attempts);

            if ($remaining === 0) {
                json_response([
                    'success' => false,
                    'message' => 'Too many incorrect attempts. This OTP has been locked. Please request a new code.',
                    'locked' => true
                ], 429);
            }

            json_response([
                'success' => false,
                'message' => $verifyRes['message'] . " ({$remaining} attempt" . ($remaining === 1 ? '' : 's') . " remaining).",
                'remaining_attempts' => $remaining
            ], 400);
        }

        // Mark OTP session verified in database
        $userModel->markOtpSessionVerified((int)$activeSession['id']);

        // Authenticate or activate account
        if ($purpose === 'register') {
            $pending = $_SESSION['pending_registration'] ?? [];
            if (!empty($pending) && $pending['mobile'] === $mobile) {
                $userId = $userModel->register([
                    'name' => $pending['name'],
                    'mobile' => $pending['mobile'],
                    'password' => $pending['password'],
                    'email' => $pending['email'] ?? null,
                    'dob' => $pending['dob'] ?? null,
                    'gender' => $pending['gender'] ?? 'female'
                ]);

                if (!empty($pending['referral_code'])) {
                    $referralModel = new Referral();
                    $referrer = $referralModel->findByCode($pending['referral_code']);
                    if ($referrer && (int)$referrer['id'] !== $userId) {
                        $referralModel->recordReferral((int)$referrer['id'], $userId, $pending['referral_code']);
                    }
                }
                unset($_SESSION['pending_registration'], $_SESSION['pending_referral_code']);
                $user = $userModel->find($userId);
            } else {
                // In case pending session was lost, find existing or create member
                $user = $userModel->findByMobile($mobile);
                if (!$user) {
                    $userId = $userModel->register([
                        'name' => 'AFOREHEALTH Member',
                        'mobile' => $mobile,
                        'password' => bin2hex(random_bytes(8))
                    ]);
                    $user = $userModel->find($userId);
                }
            }
        } else {
            // Login flow
            $user = $userModel->findByMobile($mobile);
            if (!$user) {
                json_response(['success' => false, 'message' => 'Account not found. Please register first.'], 404);
            }
            if ($user['status'] !== 'active') {
                json_response(['success' => false, 'message' => 'Your account has been deactivated. Please contact support.'], 403);
            }
        }

        // Establish authenticated session with session ID regeneration (security: prevent session fixation)
        session_regenerate_id(true);

        $referralModel = new Referral();
        $myRefCode = $referralModel->getReferralCode((int)$user['id']);

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'mobile' => $user['mobile'],
            'email' => $user['email'] ?? null,
            'referral_code' => $myRefCode
        ];

        unset($_SESSION['otp_mobile'], $_SESSION['otp_purpose'], $_SESSION['otp_session_id']);

        $intended = $_SESSION['intended_url'] ?? url('account');
        unset($_SESSION['intended_url']);

        json_response([
            'success' => true,
            'message' => 'Authentication successful! Welcome, ' . $user['name'] . '.',
            'redirect_url' => $intended
        ]);
    }

    public function logout(): void {
        unset($_SESSION['user']);
        session_regenerate_id(true);
        set_flash('info', 'You have been logged out.');
        redirect('/');
    }
}
