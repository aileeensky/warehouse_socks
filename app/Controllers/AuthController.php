<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use App\Models\UserModel;

class AuthController extends BaseController
{
    public function index()
    {
        return view('Auth/index');
    }
    public function login()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $UserModel = new Usermodel;
        $userData = $UserModel->login($username, $password);
        if (!$userData) {
            return redirect()->to(base_url('/login'))->withInput()->with('error', 'Invalid username or password');
        }
        session()->set('id_user', $userData['id']);
        session()->set('username', $userData['username']);
        session()->set('role', $userData['role']);
        switch ($userData['role']) {
            case 'gudang':
                return redirect()->to(base_url('/gudang'));
                break;
            case 'packing':
                return redirect()->to(base_url('/packing'));
                break;
            case 'monitoring':
                return redirect()->to(base_url('/monitoring'));
                break;
            case 'user':
                return redirect()->to(base_url('/user'));
                break;

            default:
                return redirect()->to(base_url('/login'))->withInput()->with('error', 'Invalid username or password');
                break;
        }
    }
    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/login'));
    }
}
