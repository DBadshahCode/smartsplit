<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        if (!$this->currentUser['isLoggedIn']) {
            return redirect()->to('auth/login');
        }
        $page_title = 'Dashboard';
        return view('home/index', $this->viewData([
            'page_title' => $page_title
        ]));
    }
}
