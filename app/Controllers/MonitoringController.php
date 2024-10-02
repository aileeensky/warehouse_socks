<?php

namespace App\Controllers;

use DateTime;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\LayoutModel;
use App\Models\PemasukanModel;
use App\Models\PengeluaranModel;
use App\Models\PermintaanModel;
use App\Models\PesanModel;
use App\Models\StockModel;
use App\Models\TabelAnakModel;
use App\Models\TabelIndukModel;
use App\Models\UserModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MonitoringController extends BaseController
{
    protected $filters;
    protected $layoutModel;
    protected $pemasukanModel;
    protected $pengeluaranModel;
    protected $permintaanModel;
    protected $pesanModel;
    protected $stockModel;
    protected $anakModel;
    protected $indukModel;
    protected $userModel;
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->layoutModel = new LayoutModel();
        $this->pemasukanModel = new PemasukanModel();
        $this->pengeluaranModel = new PengeluaranModel();
        $this->permintaanModel = new PermintaanModel();
        $this->pesanModel = new PesanModel();
        $this->stockModel = new StockModel();
        $this->anakModel = new TabelAnakModel();
        $this->indukModel = new TabelIndukModel();
        $this->userModel = new UserModel();


        if ($this->filters = ['role' => ['monitoring', session()->get('role')]] !== session()->get('role')) {
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

    public function account()
    {
        $role = session()->get('role');
        $dataUser = $this->userModel->getData();

        $data = [
            'role' => $role,
            'user' => $dataUser,
        ];
        return view($role . '/create_account', $data);
    }

    public function inputUser()
    {
        $user = $this->userModel;
        $nama = $this->request->getPost('nama');
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $bagian = $this->request->getPost('role');

        $data = [
            'nama' => $nama,
            'username' => $username,
            'password' => $password,
            'role' => $bagian,
        ];

        // Cek apakah username sudah ada
        $existingUsername = $user->where('username', $username)->first();

        if ($existingUsername) {
            // Jika username sudah ada, kembalikan dengan pesan error
            return redirect()->to(base_url(session()->get('role') . '/account/'))
                ->withInput()
                ->with('error', 'Username sudah ada, gagal membuat akun.');
        }

        // Jika username belum ada, lanjutkan insert data
        $insert = $user->insert($data);

        if ($insert) {
            return redirect()->to(base_url(session()->get('role') . '/account/'))->withInput()->with('success', 'Berhasil Create Account');
        } else {
            return redirect()->to(base_url(session()->get('role') . '/account/'))->withInput()->with('error', 'Gagal Create Account');
        }
    }

    public function stock()
    {
        $role = session()->get('role');
        $dataJalur = $this->layoutModel->getDataJalur();

        $data = [
            'role' => $role,
            'jalur' => $dataJalur,
        ];
        return view($role . '/stock', $data);
    }

    public function inputJalur()
    {
        $layout = $this->layoutModel;
        $jalur = $this->request->getPost('jalur');
        $kapasitas = $this->request->getPost('kapasitas');
        $gd_setting = $this->request->getPost('gd_setting');
        $ket = $this->request->getPost('ket');

        $data = [
            'jalur' => $jalur,
            'jumlah_box' => $kapasitas,
            'gd_setting' => $gd_setting,
            'keterangan' => $ket,
        ];

        // Cek apakah jalur sudah ada
        $existingJalur = $layout->where('jalur', $jalur)->first();

        if ($existingJalur) {
            // Jika jalur sudah ada, kembalikan dengan pesan error
            return redirect()->to(base_url(session()->get('role') . '/stock/'))
                ->withInput()
                ->with('error', 'Jalur sudah ada, gagal membuat jalur.');
        }

        // Jika jalur belum ada, lanjutkan insert data
        $insert = $layout->insert($data);

        // Pastikan pengecekan insert menggunakan perbandingan dengan false
        if ($insert !== false) {
            return redirect()->to(base_url(session()->get('role') . '/stock/'))
                ->withInput()
                ->with('success', 'Berhasil Input Jalur Baru');
        } else {
            return redirect()->to(base_url(session()->get('role') . '/stock/'))
                ->withInput()
                ->with('error', 'Gagal Input Jalur Baru');
        }
    }
}
