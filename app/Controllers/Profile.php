<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\User as UserEntity;
use App\Models\FinalDistribution as FinalDistributionModel;
use App\Models\User as UserModel;
use App\Libraries\RecoveryCodeService;

class Profile extends BaseController
{
    protected UserModel $userModel;
    protected FinalDistributionModel $finalDistributionModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->finalDistributionModel = new FinalDistributionModel();
    }

    public function index()
    {
        /** @var UserEntity|null $user */
        $user = $this->userModel->find((int) $this->currentUser['id']);

        if (!($user instanceof UserEntity)) {
            return redirect()->to('/')->with('error', 'User not found.');
        }

        return view('profile/index', $this->viewData([
            'pageTitle' => 'My Profile',
            'user' => $user,
        ]));
    }

    /**
     * Update name and/or email.
     * POST /profile/updateInfo
     */
    public function updateInfo()
    {
        $userId = (int) $this->currentUser['id'];

        /** @var UserEntity|null $user */
        $user = $this->userModel->find($userId);

        if (!($user instanceof UserEntity)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found.',
            ])->setStatusCode(404);
        }

        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));

        if ($name === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Name cannot be empty.',
            ])->setStatusCode(400);
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'A valid email address is required.',
            ])->setStatusCode(400);
        }

        // Check email uniqueness (exclude current user)
        if ($this->userModel->isEmailTakenByOther($email, $userId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'That email is already in use by another account.',
            ])->setStatusCode(409);
        }

        $this->userModel->update($userId, [
            'name' => $name,
            'email' => $email,
        ]);

        // Refresh session name so topbar reflects the change immediately
        session()->set('name', $name);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'name' => $name,
        ]);
    }

    /**
     * Update password.
     * POST /profile/updatePassword
     * Requires current password for verification.
     */
    public function updatePassword()
    {
        $userId = (int) $this->currentUser['id'];

        /** @var UserEntity|null $user */
        $user = $this->userModel->find($userId);

        if (!($user instanceof UserEntity)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found.',
            ])->setStatusCode(404);
        }

        $currentPassword = trim((string) $this->request->getPost('current_password'));
        $newPassword = trim((string) $this->request->getPost('new_password'));
        $confirmPassword = trim((string) $this->request->getPost('confirm_password'));

        if (!password_verify($currentPassword, $user->password)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Current password is incorrect.',
            ])->setStatusCode(400);
        }

        if (strlen($newPassword) < 6) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'New password must be at least 6 characters.',
            ])->setStatusCode(400);
        }

        if ($newPassword !== $confirmPassword) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'New passwords do not match.',
            ])->setStatusCode(400);
        }

        $this->userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * (Re)generate the logged-in user's own recovery code.
     * POST /profile/regenerateRecoveryCode
     * Same semantics as User::regenerateRecoveryCode — overwrites any
     * existing code, plain value returned once, never stored.
     */
    public function regenerateRecoveryCode()
    {
        $userId = (int) $this->currentUser['id'];

        /** @var UserEntity|null $user */
        $user = $this->userModel->find($userId);

        if (!($user instanceof UserEntity)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found.',
            ])->setStatusCode(404);
        }

        $recoveryService = new RecoveryCodeService();
        $recoveryCode = $recoveryService->generate();

        $this->userModel->update($userId, [
            'recovery_code_hash' => $recoveryService->hash($recoveryCode),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'recovery_code' => $recoveryCode,
        ]);
    }

    /**
     * GET /profile/getLatestDistributionMonth
     * Returns the most recent month that has a distribution record for this user.
     */
    public function getLatestDistributionMonth()
    {
        $month = $this->finalDistributionModel
            ->getLatestMonthForUser((int) $this->currentUser['id']);

        return $this->response->setJSON([
            'month' => $month,
        ]);
    }

    /**
     * GET /profile/getDistributionByMonth/:month
     * Returns the distribution row for this user for the given month.
     */
    public function getDistributionByMonth($month = '')
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return $this->response->setJSON(['data' => null])->setStatusCode(400);
        }

        $row = $this->finalDistributionModel->getRowForUserMonth(
            (int) $this->currentUser['id'],
            $month
        );

        return $this->response->setJSON(['data' => $row]);
    }
}