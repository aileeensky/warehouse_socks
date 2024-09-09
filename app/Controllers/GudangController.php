<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;


class GudangController extends BaseController
{
    protected $filters;

    public function __construct()
    {
        if ($this->filters = ['role' => ['gudang', session()->get('role')]] !== session()->get('role')) {
            return redirect()->to(base_url('/'));
        }
    }

    public function index()
    {
        $role = session()->get('role');

        $data = [
            'role' => $role,
        ];
        return view($role . '/index', $data);
    }

    public function inputNoModel()
    {
        $role = session()->get('role');

        $data = [
            'role' => $role,
        ];
        return view($role . '/inputnomodel', $data);
    }
}
