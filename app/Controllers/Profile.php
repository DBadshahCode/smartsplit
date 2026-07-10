<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\User as UserModel;

class Profile extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();

        /** @var \App\Entities\User|null $user */
        $user = $userModel->find((int) session()->get('user_id'));

        if (!($user instanceof \App\Entities\User)) {
            return redirect()->to('/')->with('error', 'User not found.');
        }

        $page_title = 'My Profile';
        return view('profile/index', compact('page_title', 'user'));
    }

    /**
     * Update name and/or email.
     * POST /profile/updateInfo
     */
    public function updateInfo()
    {
        $userModel = new UserModel();
        $userId = (int) session()->get('user_id');

        /** @var \App\Entities\User|null $user */
        $user = $userModel->find($userId);

        if (!($user instanceof \App\Entities\User)) {
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
        $existing = $userModel->where('email', $email)
            ->where('id !=', $userId)
            ->first();

        if ($existing !== null) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'That email is already in use by another account.',
            ])->setStatusCode(409);
        }

        $userModel->update($userId, [
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
        $userModel = new UserModel();
        $userId = (int) session()->get('user_id');

        /** @var \App\Entities\User|null $user */
        $user = $userModel->find($userId);

        if (!($user instanceof \App\Entities\User)) {
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

        $userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * GET /profile/getLatestDistributionMonth
     * Returns the most recent month that has a distribution record for this user.
     */
    public function getLatestDistributionMonth()
    {
        $db = \Config\Database::connect();
        $userId = (int) session()->get('user_id');

        $row = $db->table('final_distributions')
            ->where('user_id', $userId)
            ->orderBy('month', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return $this->response->setJSON([
            'month' => $row ? $row['month'] : null,
        ]);
    }

    /**
     * GET /profile/getDistributionByMonth/:month
     * Returns the distribution row for this user for the given month.
     */
    public function getDistributionByMonth($month = '')
    {
        $db = \Config\Database::connect();
        $userId = (int) session()->get('user_id');

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return $this->response->setJSON(['data' => null])->setStatusCode(400);
        }

        $row = $db->table('final_distributions')
            ->where('user_id', $userId)
            ->where('month', $month)
            ->get()
            ->getRowArray();

        return $this->response->setJSON(['data' => $row ?: null]);
    }
}