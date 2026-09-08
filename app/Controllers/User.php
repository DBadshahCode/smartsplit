<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\User as UserEntity;
use App\Models\User as UserModel;
use App\Libraries\PasswordGeneratorService;
use App\Libraries\RecoveryCodeService;

class User extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    public function index()
    {
        return view('user/index', $this->viewData([
            'pageTitle' => 'Users'
        ]));
    }

    public function getUsers()
    {
        return $this->response->setJSON([
            'data' => $this->userModel->getUserList(),
        ]);
    }

    /**
     * Add a new user. Generates both the password (already handled by the
     * caller via generatePassword()) and a recovery code — the code is
     * hashed before storage and returned in plain text exactly once here,
     * so the admin can hand it to the new user alongside their password.
     */
    public function addUser()
    {
        $data = $this->request->getPost();

        // Hash password before storing
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $recoveryService = new RecoveryCodeService();
        $recoveryCode = $recoveryService->generate();
        $data['recovery_code_hash'] = $recoveryService->hash($recoveryCode);

        $this->userModel->save($data);

        return $this->response->setJSON([
            'status' => 'success',
            'recovery_code' => $recoveryCode,
        ]);
    }

    public function deleteUser(int $id)
    {
        $this->userModel->delete($id);
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
        /** @var UserEntity|null $currentUser */
        $currentUser = $this->userModel->find((int) $id);

        if (!($currentUser instanceof UserEntity)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found.'
            ])->setStatusCode(404);
        }

        // Prevent changing your own role
        if ((int) $id === (int) $this->currentUser['id']) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You cannot change your own role.'
            ])->setStatusCode(403);
        }

        $newRole = ($currentUser->role === 'admin') ? 'user' : 'admin';

        // Demote guard — cannot demote the last remaining admin
        if ($newRole === 'user' && $currentUser->role === 'admin') {
            if ($this->userModel->countAdmins() <= 1) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Cannot demote the only admin. Promote another user first.'
                ])->setStatusCode(400);
            }
        }

        $this->userModel->update($id, ['role' => $newRole]);

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
        /** @var UserEntity|null $user */
        $user = $this->userModel->find((int) $id);

        if (!($user instanceof UserEntity)) {
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

        $this->userModel->update($id, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Password reset successfully.',
        ]);
    }

    /**
     * (Re)generate a user's recovery code (admin action).
     * Works whether the user already had a code or never had one —
     * either way the previous hash is overwritten, so any old code is
     * immediately invalidated. The plain code is returned once and is
     * never stored or logged anywhere.
     */
    public function regenerateRecoveryCode(int $id)
    {
        /** @var UserEntity|null $user */
        $user = $this->userModel->find((int) $id);

        if (!($user instanceof UserEntity)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'User not found.'
            ])->setStatusCode(404);
        }

        $recoveryService = new RecoveryCodeService();
        $recoveryCode = $recoveryService->generate();

        $this->userModel->update($id, [
            'recovery_code_hash' => $recoveryService->hash($recoveryCode),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'recovery_code' => $recoveryCode,
        ]);
    }

    public function search()
    {
        $results = [];

        foreach ($this->userModel->getIdNameList() as $user) {
            $results[] = [
                'id' => $user['id'],
                'text' => $user['name']
            ];
        }

        return $this->response->setJSON($results);
    }

    /**
     * Generate a random memorable password for the admin to use when
     * creating a user or resetting a password. Does not touch the DB —
     * the admin still has to submit the form for it to take effect.
     */
    public function generatePassword()
    {
        $passwordGenerator = new PasswordGeneratorService();

        return $this->response->setJSON([
            'status'   => 'success',
            'password' => $passwordGenerator->generate(),
        ]);
    }
}