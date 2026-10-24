<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\User as UserModel;
use App\Libraries\RecoveryCodeService;

class Auth extends BaseController
{
    public function index()
    {
        return redirect()->to('/auth/login');
    }

    /**
     * GET /auth/login
     * If already logged in, bounce to the dashboard.
     */
    public function login()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/');
        }

        return view('auth/login');
    }

    /**
     * POST /auth/authenticate
     * ThrottleFilter runs before this method (registered in Routes.php).
     * On success: clears the throttle counter so the window resets for
     * the next legitimate login.
     */
    public function authenticate()
    {
        $credentials = $this->request->getPost([
            'email',
            'password',
        ]);

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();

        $user = $userModel->findByEmail(trim((string) $credentials['email']));

        // Generic message — never reveal whether the email exists.
        if (
            $user === null ||
            ! password_verify((string) $credentials['password'], (string) $user->password)
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Invalid email or password.');
        }

        // ── Success ────────────────────────────────────────────────────────

        // Clear the throttle counter for this IP so the window resets.
        $cache = \Config\Services::cache();
        $cache->delete('login_attempts_' . md5($this->request->getIPAddress()));

        // Regenerate session ID to prevent session fixation.
        session()->regenerate(true);

        // Seed the session with everything the layout and filters need.
        // group_name: SmartSplit has one household — use a config value or
        // hardcode here. If you add a groups table later, query it here.
        session()->set([
            'user_id'         => $user->id,
            'role'            => $user->role,
            'name'            => $user->name,
            'group_name'      => 'SmartSplit House', // update if you add a groups table
            'is_logged_in'    => true,
            'last_activity'   => time(),
            'role_checked_at' => time(),
        ]);

        return redirect()->to('/');
    }

    /**
     * GET /auth/logout
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login');
    }

    /**
     * GET /auth/forgotPassword
     */
    public function forgotPassword()
    {
        // Bounce logged-in users away from the forgot-password form.
        if (session()->get('is_logged_in')) {
            return redirect()->to('/');
        }

        return view('auth/forgot_password');
    }

    /**
     * POST /auth/resetWithRecoveryCode
     */
    public function resetWithRecoveryCode()
    {
        $rules = [
            'email'            => 'required|valid_email',
            'recovery_code'    => 'required',
            'password'         => 'required|min_length[8]',
            'password_confirm' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email           = trim((string) $this->request->getPost('email'));
        $recoveryCode    = trim((string) $this->request->getPost('recovery_code'));
        $newPassword     = (string) $this->request->getPost('password');
        $confirmPassword = (string) $this->request->getPost('password_confirm');

        if ($newPassword !== $confirmPassword) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'New passwords do not match.');
        }

        $userModel = new UserModel();
        $user      = $userModel->findByEmail($email);

        $recoveryService = new RecoveryCodeService();

        // Generic message — no distinction between "no account" and "wrong code".
        if ($user === null || ! $recoveryService->verify($recoveryCode, (string) $user->recovery_code_hash)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Invalid email or recovery code.');
        }

        $userModel->update($user->id, [
            'password'           => password_hash($newPassword, PASSWORD_DEFAULT),
            'recovery_code_hash' => null, // single-use — invalidate immediately
        ]);

        return redirect()
            ->to('/auth/login')
            ->with('success', 'Password reset successfully. You can now sign in.');
    }
}