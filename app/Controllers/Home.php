<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (!$this->session->get('isLoggedIn')) {
            return redirect()->to('auth/login');
        }
        $page_title = 'Dashboard';
        return view('home/index', compact('page_title'));
    }
}
