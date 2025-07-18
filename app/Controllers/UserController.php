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
use DateTime;
use DatePeriod;
use DateInterval;

class UserController extends BaseController
{
    protected $role;
    protected $active;
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

        $this->role = session()->get('role');
        $this->active = '/index.php/' . session()->get('role');
        if ($this->filters = ['role' => ['packing', session()->get('role')]] !== session()->get('role')) {
            return redirect()->to(base_url('/'));
        }
    }

    public function index()
    {
        $role = session()->get('role');
        $today = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-6 days'));

        // Data Hari Ini
        $totalStock = $this->stockModel->selectSum('qty_stock')->get()->getRow()->qty_stock;
        $totalPemasukan = $this->pemasukanModel
            ->selectSum('qty_masuk')
            ->where('DATE(created_at)', $today)
            ->get()
            ->getRow()
            ->qty_masuk;
        $totalPermintaan = $this->permintaanModel
            ->selectSum('qty_minta')
            ->where('DATE(created_at)', $today)
            ->where('status <>', '')
            ->get()
            ->getRow()
            ->qty_minta;
        $totalPengeluaran = $this->pengeluaranModel
            ->selectSum('qty_keluar')
            ->where('DATE(created_at)', $today)
            ->get()
            ->getRow()
            ->qty_keluar;

        // Data untuk Chart (7 Hari Terakhir)
        $dates = [];
        $stockData = [];
        $pemasukanData = [];
        $permintaanData = [];
        $pengeluaranData = [];

        $datePeriod = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            new DateTime($today . ' 23:59:59')
        );

        foreach ($datePeriod as $date) {
            $dateStr = $date->format('Y-m-d');
            $dates[] = $dateStr;

            $stock = $this->stockModel->selectSum('qty_stock')->get()->getRow()->qty_stock;
            $pemasukan = $this->pemasukanModel
                ->selectSum('qty_masuk')
                ->where('DATE(created_at)', $dateStr)
                ->get()
                ->getRow()
                ->qty_masuk ?? 0;
            $permintaan = $this->permintaanModel
                ->selectSum('qty_minta')
                ->where('DATE(created_at)', $dateStr)
                ->get()
                ->getRow()
                ->qty_minta ?? 0;
            $pengeluaran = $this->pengeluaranModel
                ->selectSum('qty_keluar')
                ->where('DATE(created_at)', $dateStr)
                ->get()
                ->getRow()
                ->qty_keluar ?? 0;

            $stockData[] = (int) $stock;
            $pemasukanData[] = (int) $pemasukan;
            $permintaanData[] = (int) $permintaan;
            $pengeluaranData[] = (int) $pengeluaran;
        }

        // Data untuk Chart (7 Hari)
        $chartData = json_encode([
            'dates' => $dates,
            'stock' => $stockData,
            'pemasukan' => $pemasukanData,
            'permintaan' => $permintaanData,
            'pengeluaran' => $pengeluaranData,
        ]);

        $data = [
            'title' => 'Dashboard',
            'role' => $role,
            'stock' => $totalStock,
            'pemasukan' => $totalPemasukan,
            'permintaan' => $totalPermintaan,
            'pengeluaran' => $totalPengeluaran,
            'chartData' => $chartData,
        ];
        return view($role . '/index', $data);
    }

    public function stock()
    {
        $admin = session()->get('username');
        $role = session()->get('role');
        $dataJalur = $this->layoutModel->getDataJalur();
        $dataNomodel = $this->indukModel->selectNomodel();

        $data = [
            'active' =>  $this->active,
            'title' => 'Stock Gudang',
            'role' => $role,
            'jalur' => $dataJalur,
            'pdk' => $dataNomodel,
            'admin' => $admin,
        ];
        return view($role . '/stock', $data);
    }

    public function detailStock($jalur)
    {
        $role = session()->get('role');
        $dataStock = $this->stockModel->getDataStock($jalur);

        $data = [
            'title' => 'Stock Gudang',
            'role' => $role,
            'stock' => $dataStock,
            'jalur' => $jalur,
        ];
        return view($role . '/detailstock', $data);
    }

    public function reportPemasukan()
    {
        $nomodel = $this->request->getPost('cari1');
        $tgl_masuk = $this->request->getPost('cari2');

        $role = session()->get('role');
        $dataMasuk = $this->pemasukanModel->getData($nomodel, $tgl_masuk);

        $data = [
            'title' => 'Report Pemasukan',
            'role' => $role,
            'dataMasuk' => $dataMasuk,
            'title' => 'Report Pemasukan',
        ];
        return view($role . '/reportpemasukan', $data);
    }

    public function reportPermintaan()
    {
        $nomodel = $this->request->getPost('cari1');
        $tgl_jalan = $this->request->getPost('cari2');

        $role = session()->get('role');
        $dataPermintaan = $this->permintaanModel->getDataPermintaan($nomodel, $tgl_jalan);

        $data = [
            'role' => $role,
            'title' => 'Report Pemesanan Packing',
            'permintaan' => $dataPermintaan,
        ];
        return view($role . '/reportpermintaan', $data);
    }

    public function reportPengeluaran()
    {
        $nomodel = $this->request->getPost('cari1');
        $tgl_keluar = $this->request->getPost('cari2');

        $role = session()->get('role');
        $dataKeluar = $this->pengeluaranModel->getData($nomodel, $tgl_keluar);

        $data = [
            'title' => 'Report Pengeluaran',
            'active' =>  $this->active,
            'admin' => session()->get('username'),
            'role' => $role,
            'dataKeluar' => $dataKeluar,
        ];

        return view($role . '/reportpengeluaran', $data);
    }
}
