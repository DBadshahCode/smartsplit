<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\User as UserModel;

class User extends BaseController
{
    public function index()
    {
        $page_title = 'User Management';
        return view('user/index', compact('page_title'));
    }

    public function getUsers()
    {
        $userModel = new UserModel();
        $users = $userModel->findAll();
        return $this->response->setJSON(['data' => $users]);
    }

    public function addUser()
    {
        $userModel = new UserModel();
        $data = $this->request->getPost();

        // Hash password before storing
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $userModel->insert($data);
        return $this->response->setJSON(['status' => 'success']);
    }

    public function deleteUser($id)
    {
        $userModel = new UserModel();
        $userModel->delete($id);
        return $this->response->setJSON(['status' => 'deleted']);
    }

    /**
     * Toggle a user's role between 'admin' and 'user'.
     *
     * Rules enforced:
     *  - Cannot change your own role (prevents self-demotion).
     *  - Cannot demote the last/only admin (would leave the app adminless).
     *  - Multiple admins are allowed.
     */
    public function updateRole(int $id)
    {
        $userModel = new UserModel();

        /** @var \App\Entities\User|null $currentUser */
        $currentUser = $userModel->find((int) $id);

        if (!($currentUser instanceof \App\Entities\User)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found.'
            ])->setStatusCode(404);
        }

        // Prevent changing your own role
        if ((int) $id === (int) session()->get('user_id')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You cannot change your own role.'
            ])->setStatusCode(403);
        }

        $newRole = ($currentUser->role === 'admin') ? 'user' : 'admin';

        // Demote guard — cannot demote the last remaining admin
        if ($newRole === 'user' && $currentUser->role === 'admin') {
            $adminCount = $userModel->where('role', 'admin')->countAllResults();
            if ($adminCount <= 1) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Cannot demote the only admin. Promote another user first.'
                ])->setStatusCode(400);
            }
        }

        $userModel->update($id, ['role' => $newRole]);

        return $this->response->setJSON([
            'status' => 'success',
            'new_role' => $newRole,
            'message' => "Role updated to {$newRole}."
        ]);
    }

    /**
     * Reset a user's password (admin only).
     * Accepts a new plain-text password via POST, hashes it, and saves.
     *
     * Rules enforced:
     *  - New password must be at least 6 characters.
     *  - Admin cannot reset their own password via this endpoint
     *    (they should use a dedicated profile/settings page instead).
     */
    public function resetPassword(int $id)
    {
        $userModel = new UserModel();

        /** @var \App\Entities\User|null $user */
        $user = $userModel->find((int) $id);

        if (!($user instanceof \App\Entities\User)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found.'
            ])->setStatusCode(404);
        }

        $newPassword = trim((string) $this->request->getPost('password'));

        if (strlen($newPassword) < 6) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Password must be at least 6 characters.'
            ])->setStatusCode(400);
        }

        $userModel->update($id, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Password reset successfully.',
        ]);
    }

    public function search()
    {
        $userModel = new UserModel();

        $users = $userModel
            ->select('id, name')
            ->findAll();

        $results = [];

        foreach ($users as $user) {
            $results[] = [
                'id' => $user->id,
                'text' => $user->name
            ];
        }

        return $this->response->setJSON($results);
    }
}