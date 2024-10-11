<?php

namespace App\Controllers;

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

class PackingController extends BaseController
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


        if ($this->filters = ['role' => ['packing', session()->get('role')]] !== session()->get('role')) {
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

    public function stock()
    {
        $admin = session()->get('username');
        $role = session()->get('role');
        $dataStock = $this->stockModel->getAllStock();
        $dataNomodel = $this->indukModel->selectNomodel();

        $data = [
            'role' => $role,
            'admin' => $admin,
            'stock' => $dataStock,
            'pdk' => $dataNomodel,
        ];
        return view($role . '/stock', $data);
    }

    public function inputPermintaan()
    {
        $now = date('Y-m-d H:i:s');
        $permintaan = $this->permintaanModel;
        $idAnak = $this->request->getPost('id_anak');
        $areaPck = $this->request->getPost('admin');
        $gdSetting = $this->request->getPost('gd_setting');
        $tglJalan = $this->request->getPost('tgl_jalan');
        $wh = $this->request->getPost('wh');
        $eff = $this->request->getPost('eff');
        $direct = $this->request->getPost('direct');
        $kapasitas = $this->request->getPost('kapasitas');
        $qtyMinta = $this->request->getPost('qty_minta');
        $ketPck = $this->request->getPost('ket_pck');
        $admin = $this->request->getPost('admin');

        $data = [
            'created_at' => $now,
            'id_anak' => $idAnak,
            'area_packing' => $areaPck,
            'gd_setting' => $gdSetting,
            'tgl_jalan' => $tglJalan,
            'wh' => $wh,
            'eff' => $eff,
            'direct' => $direct,
            'kapasitas' => $kapasitas,
            'qty_minta' => $qtyMinta,
            'ket_packing' => $ketPck,
            'admin' => $admin,
        ];

        // Jika jalur belum ada, lanjutkan insert data
        $insertPermintaan = $permintaan->insert($data);

        // Pastikan pengecekan insert menggunakan perbandingan dengan false
        if ($insertPermintaan !== false) {
            return redirect()->to(base_url(session()->get('role') . '/stock/'))
                ->withInput()
                ->with('success', 'Berhasil Membuat Schedule Packing');
        } else {
            return redirect()->to(base_url(session()->get('role') . '/stock/'))
                ->withInput()
                ->with('error', 'Gagal Membuat Schedule Packing');
        }
    }

    public function schedulePacking()
    {
        $admin = session()->get('username');
        $role = session()->get('role');
        $dataPermintaan = $this->permintaanModel->getData($admin);

        $data = [
            'role' => $role,
            'admin' => $admin,
            'permintaan' => $dataPermintaan,
        ];
        return view($role . '/schedule', $data);
    }
}
