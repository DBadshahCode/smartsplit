<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (!$this->currentUser['isLoggedIn']) {
            return redirect()->to('auth/login');
        }

        return view('home/index', $this->viewData([
            'pageTitle' => 'Dashboard'
        ]));
    }
}
