<?php

namespace App\Controllers;

use App\Controllers\BaseController;
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
}
