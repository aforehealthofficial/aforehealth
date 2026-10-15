<?php
namespace Controllers;

use Models\User;
use Services\SmsService;

class AuthController {
    public function showLogin(): void {
        view('auth.login', [
            'pageTitle' => 'Login with Mobile - AFOREHEALTH'
        ]);
    }

    public function login(): void {
        $mobile = preg_replace('/[^0-9]/', '', $_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';

        if (strlen($mobile) !== 10) {
            set_flash('danger', 'Please enter a valid 10-digit Indian mobile number.');
            redirect('/login');
        }

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

        // Establish session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'mobile' => $user['mobile'],
            'email' => $user['email']
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
        $name = trim($_POST['name'] ?? '');
        $mobile = preg_replace('/[^0-9]/', '', $_POST['mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $dob = $_POST['dob'] ?? null;
        $gender = $_POST['gender'] ?? 'female';
        $refCode = trim($_POST['referral_code'] ?? $_SESSION['pending_referral_code'] ?? '');

        if (empty($name)) {
            set_flash('danger', 'Please enter your full name.');
            redirect('/register');
        }

        if (strlen($mobile) !== 10) {
            set_flash('danger', 'Please enter a valid 10-digit mobile number.');
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

        $userId = $userModel->register([
            'name' => $name,
            'mobile' => $mobile,
            'password' => $password,
            'email' => $_POST['email'] ?? null,
            'dob' => $dob,
            'gender' => $gender
        ]);

        // Generate user's unique referral code
        $referralModel = new \Models\Referral();
        $myRefCode = $referralModel->getReferralCode($userId);

        // Record incoming referral if friend referred them
        if (!empty($refCode)) {
            $referrer = $referralModel->findByCode($refCode);
            if ($referrer && $referrer['id'] != $userId) {
                $referralModel->recordReferral((int)$referrer['id'], $userId, $refCode);
                unset($_SESSION['pending_referral_code']);
            }
        }

        $_SESSION['user'] = [
            'id' => $userId,
            'name' => $name,
            'mobile' => $mobile,
            'email' => $_POST['email'] ?? null,
            'referral_code' => $myRefCode
        ];

        set_flash('success', 'Welcome to AFOREHEALTH! Your account has been created.');
        redirect('/account');
    }

    public function showOtpVerify(): void {
        $mobile = $_GET['mobile'] ?? $_SESSION['otp_mobile'] ?? '';
        $userModel = new User();
        $latestOtp = !empty($mobile) ? $userModel->getLatestActiveOtp($mobile, 'login') : ($_SESSION['latest_demo_otp'] ?? null);

        view('auth.otp-verify', [
            'pageTitle' => 'Verify OTP - AFOREHEALTH',
            'mobile' => $mobile,
            'latestOtp' => $latestOtp
        ]);
    }

    public function sendOtp(): void {
        $mobile = preg_replace('/[^0-9]/', '', $_POST['mobile'] ?? '');
        if (strlen($mobile) !== 10) {
            json_response(['success' => false, 'message' => 'Please provide a valid 10-digit Indian mobile number.'], 400);
        }

        try {
            $userModel = new User();
            $otp = $userModel->createOtp($mobile, 'login');

            // Dispatch SMS (falls back safely if gateway not connected)
            try {
                $smsService = new SmsService();
                $smsService->sendOtp($mobile, $otp);
            } catch (\Throwable $t) {
                // Ignore external SMS failures in testing
            }

            $_SESSION['otp_mobile'] = $mobile;
            $_SESSION['latest_demo_otp'] = $otp;

            $respData = [
                'success' => true,
                'message' => 'OTP generated successfully for +91 ' . $mobile . '.',
                'demo_otp' => $otp
            ];

            json_response($respData);
        } catch (\Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 429);
        }
    }

    public function verifyOtp(): void {
        $mobile = preg_replace('/[^0-9]/', '', $_POST['mobile'] ?? $_SESSION['otp_mobile'] ?? '');
        $otp = trim($_POST['otp'] ?? '');

        if (strlen($mobile) !== 10 || strlen($otp) < 4) {
            json_response(['success' => false, 'message' => 'Invalid OTP.'], 400);
        }

        try {
            $userModel = new User();
            $valid = $userModel->verifyOtp($mobile, $otp, 'login');

            if (!$valid) {
                json_response(['success' => false, 'message' => 'Invalid or expired OTP code.'], 400);
            }
        } catch (\Exception $e) {
            json_response(['success' => false, 'message' => $e->getMessage()], 429);
        }

        // Find or create customer
        $user = $userModel->findByMobile($mobile);
        if (!$user) {
            $userId = $userModel->register([
                'name' => 'AFOREHEALTH Member',
                'mobile' => $mobile,
                'password' => bin2hex(random_bytes(8))
            ]);
            $user = $userModel->find($userId);
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'mobile' => $user['mobile'],
            'email' => $user['email']
        ];

        unset($_SESSION['otp_mobile']);

        json_response([
            'success' => true,
            'message' => 'Logged in successfully!',
            'redirect_url' => url('account')
        ]);
    }

    public function logout(): void {
        unset($_SESSION['user']);
        set_flash('info', 'You have been logged out.');
        redirect('/');
    }
}
