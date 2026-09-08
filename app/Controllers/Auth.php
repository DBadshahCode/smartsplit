<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\User as UserModel;
use App\Libraries\RecoveryCodeService;

class Auth extends BaseController
{
    public function index()
    {
        //
    }
    public function login()
    {
        return view('auth/login');
    }
    public function authenticate()
    {
        $credentials = $this->request->getPost([
            'email',
            'password',
        ]);

        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userModel = new UserModel();

        $user = $userModel
            ->where('email', $credentials['email'])
            ->first();

        if (
            $user === null ||
            ! password_verify($credentials['password'], $user->password)
        ) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Invalid email or password.');
        }

        session()->regenerate();

        session()->set([
            'user_id'      => $user->id,
            'role'         => $user->role,
            'name'         => $user->name,
            'is_logged_in' => true,
        ]);

        return redirect()->to('/');
    }
    public function logout()
    {
        session()->destroy();

        return redirect()->to('/auth/login');
    }

    /**
     * GET /auth/forgotPassword
     * Shows the recovery-code-based reset form (no email/SMS involved —
     * the code was handed to the user out-of-band when their account
     * was created or last regenerated).
     */
    public function forgotPassword()
    {
        return view('auth/forgot_password');
    }

    /**
     * POST /auth/resetWithRecoveryCode
     * Verifies the submitted recovery code against the account's hash,
     * sets the new password, and invalidates the code (single-use).
     * Deliberately uses one generic error message for "no such account"
     * and "wrong code" alike, so a failed attempt can't be used to probe
     * which emails have accounts.
     */
    public function resetWithRecoveryCode()
    {
        $rules = [
            'email' => 'required|valid_email',
            'recovery_code' => 'required',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email = trim((string) $this->request->getPost('email'));
        $recoveryCode = trim((string) $this->request->getPost('recovery_code'));
        $newPassword = (string) $this->request->getPost('password');
        $confirmPassword = (string) $this->request->getPost('password_confirm');

        if ($newPassword !== $confirmPassword) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'New passwords do not match.');
        }

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        $recoveryService = new RecoveryCodeService();

        if ($user === null || !$recoveryService->verify($recoveryCode, $user->recovery_code_hash)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Invalid email or recovery code.');
        }

        $userModel->update($user->id, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'recovery_code_hash' => null, // single-use — must regenerate afterwards
        ]);

        return redirect()
            ->to('/auth/login')
            ->with('success', 'Password reset successfully. You can now sign in.');
    }
}