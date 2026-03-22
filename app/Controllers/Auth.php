<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\User as UserModel;

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
    public function loginUser()
    {
        $userModel = new UserModel();
        $data = $this->request->getPost();
        $user = $userModel->where('email', $data['email'])->first();
        if ($user && password_verify($data['password'], $user->password)) {
            $session = session();
            $session->set(
                [
                    'user_id' => $user->id,
                    'role' => $user->role,
                    'name' => $user->name,
                    'isLoggedIn' => true
                ]
            );
            return redirect()->to('/');
        } else {
            return redirect()->back()->with('error', 'Invalid login');
        }
    }
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login');
    }
}
