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
use Phpml\Clustering\KMeans;

class GudangController extends BaseController
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
        if ($this->filters = ['role' => ['gudang', session()->get('role')]] !== session()->get('role')) {
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

        // Kategori Clusters
        $fastMoving = $this->stockModel
            ->selectCount('id_stock')
            ->like('jalur', 'A%')
            ->get()
            ->getRow()
            ->id_stock ?? 0;
        $mediumMoving = $this->stockModel
            ->selectCount('id_stock')
            ->like('jalur', 'B%')
            ->get()
            ->getRow()
            ->id_stock ?? 0;
        $slowMoving = $this->stockModel
            ->selectCount('id_stock')
            ->like('jalur', 'C%')
            ->get()
            ->getRow()
            ->id_stock ?? 0;
        $pieData = [
            'fastMoving' => $fastMoving,
            'mediumMoving' => $mediumMoving,
            'slowMoving' => $slowMoving
        ];

        $data = [
            'active' =>  $this->active,
            'title' => 'Dashboard',
            'role' => $role,
            'stock' => $totalStock,
            'pemasukan' => $totalPemasukan,
            'permintaan' => $totalPermintaan,
            'pengeluaran' => $totalPengeluaran,
            'chartData' => $chartData,
            'pieData' => $pieData,
        ];
        return view($role . '/index', $data);
    }

    public function layout()
    {
        $role = session()->get('role');

        $data = [
            'active' =>  $this->active,
            'role' => $role,
        ];
        return view($role . '/index', $data);
    }

    public function dataOrder()
    {
        $role = session()->get('role');
        $dataAnak = $this->anakModel->getSelect();
        $data = [
            'active' =>  $this->active,
            'role' => $role,
            'title' => 'Data Order',
            'db' => $dataAnak,
        ];
        return view($role . '/dataorder', $data);
    }

    public function importDataOrder()
    {
        $admin = session()->get('username');
        $induk = $this->indukModel;
        $anak = $this->anakModel;
        $file = $this->request->getFile('file');

        $stylesNotInserted = []; // Variabel untuk menyimpan style yang tidak berhasil diinsert
        $totalInserted = 0;

        if ($file && $file->isValid() && !$file->hasMoved()) {
            log_message('info', 'File uploaded: ' . $file->getName());

            $filePath = WRITEPATH . 'uploads/' . $file->getName();
            $file->move(WRITEPATH . 'uploads');

            try {
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $dataRows = $sheet->toArray();
                log_message('info', 'Number of data rows: ' . count($dataRows));
            } catch (\Exception $e) {
                log_message('error', 'Error reading Excel file: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Error reading Excel file.');
            }

            foreach ($dataRows as $index => $row) {
                // Lewati header atau baris pertama
                if ($index === 0) {
                    continue;
                }

                // Validasi untuk kolom yang penting
                if (empty($row[3]) || empty($row[4]) || empty($row[2])) {
                    log_message('info', 'Skipping row ' . ($index + 1) . ' due to empty required fields.');
                    continue; // Lewati jika kolom penting kosong
                }

                // Ambil dan format tanggal dari kolom delivery
                $deliveryDate = $row[1];
                // Periksa apakah tanggalnya valid (bukan kosong)
                if (empty($deliveryDate)) {
                    log_message('error', 'Empty delivery date at row ' . ($index + 1));
                    continue; // Lewati jika tanggal tidak valid
                }

                // Coba konversi jika bukan numeric (timestamp Excel)
                if (!is_numeric($deliveryDate)) {
                    // Coba ubah string menjadi objek DateTime
                    try {
                        $dateTime = new \DateTime($deliveryDate);
                        $formattedDeliveryDate = $dateTime->format('Y-m-d');
                    } catch (\Exception $e) {
                        log_message('error', 'Invalid delivery date format at row ' . ($index + 1) . ': ' . $deliveryDate);
                        continue; // Lewati jika format tidak valid
                    }
                } else {
                    $formattedDeliveryDate = Date::excelToDateTimeObject($deliveryDate)->format('Y-m-d');
                }
                $smvRaw = $row[8];
                $smvFormatted = str_replace(',', '.', trim($smvRaw));

                $data = [
                    'no_order' => $row[3],
                    'no_model' => $row[4],
                    'kode_buyer' => $row[2],
                    'smv' => is_numeric($smvFormatted) ? (float)$smvFormatted : 0,
                    'delivery' => $formattedDeliveryDate,
                    'admin' => $admin,
                ];

                $existingInduk = $induk->where('no_model', $data['no_model'])->first();
                if ($existingInduk) {
                    $id_induk = $existingInduk['id_induk'];
                } else {
                    if ($induk->insert($data) === false) {
                        log_message('error', 'Error inserting into induk table: ' . implode(', ', $induk->errors()));
                        return redirect()->back()->with('error', 'Error inserting data to induk database.');
                    }
                    $id_induk = $induk->insertID();
                }

                $style = $row[6];
                $existingAnak = $anak->where('id_induk', $id_induk)->where('style', $style)->first();
                $waktu_input = (new DateTime())->format('Y-m-d H:i:s');

                $qtyRaw = $row[9];
                $qtyFormatted = str_replace(',', '.', trim($qtyRaw));

                if (!$existingAnak) {
                    $data2 = [
                        'id_induk' => $id_induk,
                        'waktu_input' => $waktu_input,
                        'area' => $row[0],
                        'inisial' => $row[5],
                        'style' => $style,
                        'warna' => $row[7],
                        'qty_po_inisial' => is_numeric($qtyFormatted) ? (float)$qtyFormatted : 0,
                        'admin' => $admin,
                    ];

                    if ($anak->insert($data2)) {
                        $totalInserted++; // Hitung jika berhasil di-insert
                    } else {
                        log_message('error', 'Error inserting into anak table: ' . implode(', ', $anak->errors()));
                        $stylesNotInserted[] = $style; // Simpan style yang gagal diinsert
                    }
                }
            }

            // Hapus file setelah proses selesai
            unlink($filePath);

            // Cek jika ada data yang berhasil diinsert
            if ($totalInserted > 0) {
                return redirect()->to(base_url(session()->get('role') . '/inputdatabase'))->with('success', 'Data berhasil diimpor.');
            } else {
                return redirect()->to(base_url(session()->get('role') . '/inputdatabase'))->with('error', 'Tidak ada data yang berhasil diimpor.');
            }
        }
    }

    public function stock()
    {
        $admin = session()->get('username');
        $role = session()->get('role');
        $dataJalur = $this->layoutModel->getDataJalur();
        $dataNomodel = $this->indukModel->selectNomodel();
        // dd ($dataJalur, $dataNomodel);
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

    public function inputStockCluster()
    {
        $admin = session()->get('username');
        $role = session()->get('role');
        $dataJalur = $this->layoutModel->getDataJalur();
        $dataNomodel = $this->indukModel->selectNomodel();

        $data = [
            'title' => 'Stock Gudang',
            'role' => $role,
            'jalur' => $dataJalur,
            'pdk' => $dataNomodel,
            'admin' => $admin,
        ];
        return view($role . '/inputstock', $data);
    }

    public function getStockModal($id)
    {
        $dataAnak = $this->anakModel->getData($id);

        if ($dataAnak) {
            $area = [];
            $inisial = [];

            foreach ($dataAnak as $row) {
                $area[] = $row['area'];
                $inisial[] = ['id_anak' => $row['id_anak'], 'inisial' => $row['inisial']]; // Tambahkan id_anak dan inisial
            }

            $responseData = [
                'area' => array_unique($area),
                'inisial' => $inisial, // Kirim array dengan id_anak dan inisial
            ];

            return $this->response->setJSON($responseData);
        }

        return $this->response->setJSON(['area' => [], 'inisial' => []]);
    }

    public function inputStock()
    {
        $now = date('Y-m-d H:i:s');
        $pemasukan = $this->pemasukanModel;
        $stock = $this->stockModel;
        $jalur = $this->request->getPost('jalur');
        $space = $this->request->getPost('space');
        $id_anak = $this->request->getPost('id_anak');
        $qty_masuk = $this->request->getPost('qty_masuk');
        $box_masuk = $this->request->getPost('box_masuk');
        $gd_setting = $this->request->getPost('gd_setting');
        $ket_masuk = $this->request->getPost('keterangan');
        $admin = $this->request->getPost('admin');

        $data = [
            'created_at' => $now,
            'jalur' => $jalur,
            'id_anak' => $id_anak,
            'qty_stock' => 0,
            'box_stock' => 0,
            'gd_setting' => $gd_setting,
            'ket_stock' => $ket_masuk,
            'admin' => $admin,
        ];

        if ($space > 0 && $space >= $box_masuk) {
            // Cek apakah stock di jalur sudah ada
            $existingJalur = $stock->where([
                'id_anak' => $id_anak,
                'jalur' => $jalur
            ])->first();
        } else {
            return redirect()->to(base_url(session()->get('role') . '/stock/'))
                ->withInput()
                ->with('error', 'Qty Box Melebihi Kapasitas Jalur!');
        }

        if (!$existingJalur) {
            $insertStock = $stock->insert($data);
        }

        $data2 = [
            'created_at' => $now,
            'jalur' => $jalur,
            'id_anak' => $id_anak,
            'qty_masuk' => $qty_masuk,
            'box_masuk' => $box_masuk,
            'gd_setting' => $gd_setting,
            'ket_masuk' => $ket_masuk,
            'admin' => $admin,
        ];

        // Jika jalur belum ada, lanjutkan insert data
        $insertPemasukan = $pemasukan->insert($data2);

        // Pastikan pengecekan insert menggunakan perbandingan dengan false
        if ($insertPemasukan !== false) {
            return redirect()->to(base_url(session()->get('role') . '/stock/'))
                ->withInput()
                ->with('success', 'Berhasil Input Pemasukan');
        } else {
            return redirect()->to(base_url(session()->get('role') . '/stock/'))
                ->withInput()
                ->with('error', 'Gagal Input Pemasukan');
        }
    }

    public function detailStock($jalur)
    {
        $role = session()->get('role');
        $dataStock = $this->stockModel->getDataStock($jalur);
        // dd ($dataStock);
        $data = [
            'title' => 'Stock Gudang',
            'role' => $role,
            'stock' => $dataStock,
            'jalur' => $jalur,
        ];
        return view($role . '/detailstock', $data);
    }

    public function dataPermintaan()
    {
        $nomodel = $this->request->getPost('cari1');
        $tgl_jalan = $this->request->getPost('cari2');

        $admin = session()->get('username');
        $role = session()->get('role');

        $dataPermintaan = $this->permintaanModel->getDataMinta($nomodel, $tgl_jalan);

        $data = [
            'title' => 'Data Permintaan',
            'admin' => $admin,
            'role' => $role,
            'permintaan' => $dataPermintaan,
        ];
        return view($role . '/datapermintaan', $data);
    }

    public function getStockByIdAnak()
    {
        // Ambil id_anak dari request POST
        $idAnak = $this->request->getPost('id_anak');

        // Ambil data stock berdasarkan id_anak
        $dataStock = $this->stockModel->getStockNomodel($idAnak);

        // Kembalikan data dalam bentuk JSON
        return $this->response->setJSON($dataStock);
    }

    public function inputPengeluaran()
    {
        $now = date('Y-m-d H:i:s');
        $admin = session()->get('username');
        $pengeluaran = $this->pengeluaranModel;
        $permintaan = $this->permintaanModel;

        $idAnak = $this->request->getPost('id_anak');
        $idMinta = $this->request->getPost('id_minta');
        $qtyMinta = $this->request->getPost('qty_minta');
        $qtyKeluar = $this->request->getPost('qty_keluar');
        $boxKeluar = $this->request->getPost('box_keluar');
        $jalur = $this->request->getPost('jalur');
        $ketKeluar = $this->request->getPost('keterangan');
        $gdSetting = 'GD SETTING';

        // dd($qtyMinta);
        $data = [
            'created_at' => $now,
            'id_anak' => $idAnak,
            'id_minta' => $idMinta,
            'qty_keluar' => $qtyKeluar,
            'box_keluar' => $boxKeluar,
            'jalur' => $jalur,
            'gd_setting' => $gdSetting,
            'ket_keluar' => $ketKeluar,
            'admin' => $admin,
        ];

        $insertPengeluaran = $pengeluaran->insert($data);

        if ($insertPengeluaran) {
            // Ambil total qty_keluar berdasarkan id_minta
            $selectQtyKeluar = $pengeluaran->selectSum('qty_keluar')
                ->where('id_minta', $idMinta)
                ->get()
                ->getRowArray();

            $totalQtyKeluar = $selectQtyKeluar['qty_keluar'] ?? 0;

            $sisa = $qtyMinta - $totalQtyKeluar;

            // Jika sisa sudah nol atau kurang, update status permintaan ke DONE
            if ($sisa <= 0) {
                $permintaan->update($idMinta, ['status' => 'DONE']);
                return redirect()->to(base_url(session()->get('role') . '/dataterkirim/'))
                    ->withInput()
                    ->with('success', 'Berhasil Input Pengeluaran');
            } elseif ($sisa > 0) {
                // Jika masih ada sisa, update status permintaan ke ON PROCESS
                $permintaan->update($idMinta, ['status' => 'ON PROCESS']);
                return redirect()->to(base_url(session()->get('role') . '/datapermintaan/'))
                    ->withInput()
                    ->with('success', 'Berhasil Input Pengeluaran');
            }
        } else {
            return redirect()->to(base_url(session()->get('role') . '/datapermintaan/'))
                ->withInput()
                ->with('error', 'Gagal Input Pengeluaran');
        }
    }

    public function dataTerkirim()
    {
        $nomodel = $this->request->getPost('cari1');
        $tgl_jalan = $this->request->getPost('cari2');

        $role = session()->get('role');

        $terkirim = $this->permintaanModel->getDataTerkirim($nomodel, $tgl_jalan);

        $data = [
            'title' => 'Data Terkirim',
            'admin' => session()->get('username'),
            'active' =>  $this->active,
            'role' => $role,
            'terkirim' => $terkirim,
        ];
        return view($role . '/dataterkirim', $data);
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

    public function importStockBackup()
    {
        $admin = session()->get('username');
        $file  = $this->request->getFile('file');
        $today = new \DateTime('now');

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        // 1) Upload & load sheet
        $filePath = WRITEPATH . 'uploads/' . $file->getName();
        $file->move(WRITEPATH . 'uploads');
        $sheetArr = IOFactory::load($filePath)
            ->getActiveSheet()
            ->toArray(null, true, true, true);
        unlink($filePath);

        // 2) Kumpulkan semua baris yang belum punya stock
        $pointsRaw = [];
        foreach (array_slice($sheetArr, 17, null, true) as $idx => $row) {
            $area  = trim($row['AA'] ?? '');
            $style = trim($row['E']  ?? '');
            $qty   = trim($row['M']  ?? '');
            $model = trim($row['V']  ?? '');
            $boxId = trim($row['X']  ?? '');

            if ($area === '' || $style === '' || $qty === '' || $model === '' || $boxId === '') {
                continue;
            }

            // cek induk & anak
            $dataInduk = $this->indukModel
                ->select('id_induk, delivery, kode_buyer AS buyer')
                ->where('no_model', $model)
                ->first();
            if (!$dataInduk) continue;

            $dataAnak = $this->anakModel
                ->select('id_anak')
                ->where('id_induk', $dataInduk['id_induk'])
                ->where('area',     $area)
                ->where('style',    $style)
                ->first();
            if (!$dataAnak) continue;

            // jika sudah ada stock, update langsung dan skip clustering
            $stock = $this->stockModel
                ->select('id_stock, qty_stock, box_stock')
                ->where('id_anak', $dataAnak['id_anak'])
                ->first();
            if ($stock) {
                $addQty = floatval($qty) / 24;
                $this->stockModel->update($stock['id_stock'], [
                    'qty_stock' => $stock['qty_stock'] + $addQty,
                    'box_stock' => $stock['box_stock'] + 1,
                ]);
                continue;
            }

            // belum ada stock → simpan raw point
            $daysToExp = $today->diff(new \DateTime($dataInduk['delivery']))->days;
            $pointsRaw[] = [
                'id_anak' => $dataAnak['id_anak'],
                'model'   => $model,
                'days'    => $daysToExp,
                'qty'     => floatval($qty) / 24,
                'box_id'  => $boxId,
            ];
        }

        if (empty($pointsRaw)) {
            return redirect()->back()->with('success', 'Semua baris sudah ter‐update stock‑nya.');
        }

        // 3) Agregasi per id_anak: total qty & hitung unik box_count
        $agg = [];
        foreach ($pointsRaw as $p) {
            $id = $p['id_anak'];
            if (!isset($agg[$id])) {
                $agg[$id] = [
                    'id_anak' => $id,
                    'model'   => $p['model'],
                    'days'    => $p['days'],
                    'qty'     => $p['qty'],
                    'boxes'   => [],
                ];
            } else {
                $agg[$id]['qty'] += $p['qty'];
            }
            $agg[$id]['boxes'][$p['box_id']] = true;
        }
        $points = [];
        foreach ($agg as $v) {
            $points[] = [
                'id_anak'   => $v['id_anak'],
                'model'      => $v['model'],
                'days'      => $v['days'],
                'qty'       => $v['qty'],
                'box_count' => count($v['boxes']),
            ];
        }

        // 4) Clustering K-Means hanya berdasarkan 'days'
        $k = count($this->layoutModel->findAll());
        // normalisasi days
        $daysArr = array_column($points, 'days');
        $minD = min($daysArr);
        $maxD = max($daysArr);
        foreach ($points as &$p) {
            $p['nd'] = ($p['days'] - $minD) / max(1, $maxD - $minD);
        }
        unset($p);

        // inisialisasi centroid pada rentang [0..1]
        $centroids = [];
        for ($i = 0; $i < $k; $i++) {
            $centroids[] = ['y' => $i / ($k - 1)];
        }

        // iterasi K-Means
        $eps = 0.001;
        $maxIter = 100;
        for ($iter = 0; $iter < $maxIter; $iter++) {
            // assign
            foreach ($points as &$pt) {
                $distances = array_map(fn($c) => abs($pt['nd'] - $c['y']), $centroids);
                $pt['cluster'] = (int) array_keys($distances, min($distances))[0];
            }
            unset($pt);
            // recompute
            $sums = array_fill(0, $k, ['sy' => 0, 'cnt' => 0]);
            foreach ($points as $pt) {
                $c = $pt['cluster'];
                $sums[$c]['sy'] += $pt['nd'];
                $sums[$c]['cnt']++;
            }
            $moved = false;
            foreach ($sums as $iC => $v) {
                if ($v['cnt'] === 0) continue;
                $newY = $v['sy'] / $v['cnt'];
                if (abs($newY - $centroids[$iC]['y']) > $eps) $moved = true;
                $centroids[$iC]['y'] = $newY;
            }
            if (! $moved) break;
        }

        // 5) Penempatan + prioritas model
        $layouts      = $this->layoutModel->orderBy('jalur', 'ASC')->findAll();
        $layoutCursor = 0;
        $now          = date('Y-m-d H:i:s');
        foreach ($points as $pt) {
            // cek jalur existing dengan model sama dan kapasitas
            $stocks = $this->stockModel
                ->where('id_anak', $pt['id_anak'])
                ->findAll();
            $chosen = null;
            foreach ($stocks as $stk) {
                if ($stk['box_stock'] + $pt['box_count'] <= 80) {
                    $chosen = $stk['jalur'];
                    break;
                }
            }
            // jika tidak ada, ambil jalur baru
            if (!$chosen) {
                if (!isset($layouts[$layoutCursor])) break;
                $chosen = $layouts[$layoutCursor++]['jalur'];
            }
            // upsert stock
            $exist = $this->stockModel->where('id_anak', $pt['id_anak'])->where('jalur', $chosen)->first();
            if ($exist) {
                $this->stockModel->update($exist['id_stock'], [
                    'qty_stock' => $exist['qty_stock'] + $pt['qty'],
                    'box_stock' => $exist['box_stock'] + $pt['box_count'],
                ]);
            } else {
                $this->stockModel->insert([
                    'id_anak' => $pt['id_anak'],
                    'created_at' => $now,
                    'qty_stock' => $pt['qty'],
                    'box_stock' => $pt['box_count'],
                    'jalur' => $chosen,
                    'gd_setting' => 'GD SETTING',
                    'admin' => $admin,
                ]);
            }
            // insert pemasukan
            $this->pemasukanModel->insert([
                'id_anak' => $pt['id_anak'],
                'created_at' => $now,
                'qty_masuk' => $pt['qty'],
                'box_masuk' => $pt['box_count'],
                'jalur' => $chosen,
                'gd_setting' => 'GD SETTING',
                'admin' => $admin,
            ]);
        }

        return redirect()->back()->with('success', 'Import & clustering selesai!');
    }

    // buatan ayang
    // public function importStock()
    // {
    //     $admin = session()->get('username');
    //     $file  = $this->request->getFile('file');
    //     $today = new \DateTime('now');

    //     if (! $file || ! $file->isValid() || $file->hasMoved()) {
    //         return redirect()->back()->with('error', 'File tidak valid.');
    //     }

    //     // 1) Upload & load sheet
    //     $filePath = WRITEPATH . 'uploads/' . $file->getName();
    //     $file->move(WRITEPATH . 'uploads');
    //     $sheetArr = IOFactory::load($filePath)
    //         ->getActiveSheet()
    //         ->toArray(null, true, true, true);
    //     unlink($filePath);

    //     // 2) Kumpulkan semua baris yang belum punya stock
    //     $pointsRaw = [];
    //     foreach (array_slice($sheetArr, 17, null, true) as $idx => $row) {
    //         $area  = trim($row['AA'] ?? '');
    //         $style = trim($row['E']  ?? '');
    //         $qty   = trim($row['M']  ?? '');
    //         $model = trim($row['V']  ?? '');
    //         $boxId = trim($row['X']  ?? '');

    //         if ($area === '' || $style === '' || $qty === '' || $model === '' || $boxId === '') {
    //             continue;
    //         }

    //         // cek induk & anak
    //         $dataInduk = $this->indukModel
    //             ->select('id_induk, delivery, kode_buyer AS buyer')
    //             ->where('no_model', $model)
    //             ->first();
    //         if (!$dataInduk) continue;

    //         $dataAnak = $this->anakModel
    //             ->select('id_anak')
    //             ->where('id_induk', $dataInduk['id_induk'])
    //             ->where('area',     $area)
    //             ->where('style',    $style)
    //             ->first();
    //         if (!$dataAnak) continue;

    //         // jika sudah ada stock, update langsung dan skip clustering
    //         $stock = $this->stockModel
    //             ->select('id_stock, qty_stock, box_stock')
    //             ->where('id_anak', $dataAnak['id_anak'])
    //             ->first();
    //         if ($stock) {
    //             $addQty = floatval($qty) / 24;
    //             $this->stockModel->update($stock['id_stock'], [
    //                 'qty_stock' => $stock['qty_stock'] + $addQty,
    //                 'box_stock' => $stock['box_stock'] + 1,
    //             ]);
    //             continue;
    //         }

    //         // belum ada stock → simpan raw point
    //         $daysToExp = $today->diff(new \DateTime($dataInduk['delivery']))->days;
    //         $pointsRaw[] = [
    //             'id_anak' => $dataAnak['id_anak'],
    //             'model'   => $model,
    //             'days'    => $daysToExp,
    //             'qty'     => floatval($qty) / 24,
    //             'box_id'  => $boxId,
    //         ];
    //     }

    //     if (empty($pointsRaw)) {
    //         return redirect()->back()->with('success', 'Semua baris sudah ter‐update stock‑nya.');
    //     }

    //     // Implementasi K-Means clustering
    //     $points = [];
    //     foreach ($pointsRaw as $row) {
    //         $points[] = [(float)$row['days'], (float)$row['qty']];
    //     }
    //     $kmeans = new KMeans(200);
    //     $clusters = $kmeans->cluster($points);
    //     d($clusters);
    //     die;

    //     // 3) Agregasi per id_anak: total qty & hitung unik box_count
    //     $agg = [];
    //     foreach ($pointsRaw as $p) {
    //         $id = $p['id_anak'];
    //         if (!isset($agg[$id])) {
    //             $agg[$id] = [
    //                 'id_anak' => $id,
    //                 'model'   => $p['model'],
    //                 'days'    => $p['days'],
    //                 'qty'     => $p['qty'],
    //                 'boxes'   => [],
    //             ];
    //         } else {
    //             $agg[$id]['qty'] += $p['qty'];
    //         }
    //         $agg[$id]['boxes'][$p['box_id']] = true;
    //     }
    //     $points = [];
    //     foreach ($agg as $v) {
    //         $points[] = [
    //             'id_anak'   => $v['id_anak'],
    //             'model'      => $v['model'],
    //             'days'      => $v['days'],
    //             'qty'       => $v['qty'],
    //             'box_count' => count($v['boxes']),
    //         ];
    //     }

    //     // 4) Clustering K-Means hanya berdasarkan 'days'
    //     $k = count($this->layoutModel->findAll());
    //     // normalisasi days
    //     $daysArr = array_column($points, 'days');
    //     $minD = min($daysArr);
    //     $maxD = max($daysArr);
    //     foreach ($points as &$p) {
    //         $p['nd'] = ($p['days'] - $minD) / max(1, $maxD - $minD);
    //     }
    //     unset($p);

    //     // inisialisasi centroid pada rentang [0..1]
    //     $centroids = [];
    //     for ($i = 0; $i < $k; $i++) {
    //         $centroids[] = ['y' => $i / ($k - 1)];
    //     }

    //     // iterasi K-Means
    //     $eps = 0.001;
    //     $maxIter = 100;
    //     for ($iter = 0; $iter < $maxIter; $iter++) {
    //         // assign
    //         foreach ($points as &$pt) {
    //             $distances = array_map(fn($c) => abs($pt['nd'] - $c['y']), $centroids);
    //             $pt['cluster'] = (int) array_keys($distances, min($distances))[0];
    //         }
    //         unset($pt);
    //         // recompute
    //         $sums = array_fill(0, $k, ['sy' => 0, 'cnt' => 0]);
    //         foreach ($points as $pt) {
    //             $c = $pt['cluster'];
    //             $sums[$c]['sy'] += $pt['nd'];
    //             $sums[$c]['cnt']++;
    //         }
    //         $moved = false;
    //         foreach ($sums as $iC => $v) {
    //             if ($v['cnt'] === 0) continue;
    //             $newY = $v['sy'] / $v['cnt'];
    //             if (abs($newY - $centroids[$iC]['y']) > $eps) $moved = true;
    //             $centroids[$iC]['y'] = $newY;
    //         }
    //         if (! $moved) break;
    //     }

    //     // 5) Penempatan + prioritas model
    //     $layouts      = $this->layoutModel->orderBy('jalur', 'ASC')->findAll();
    //     $layoutCursor = 0;
    //     $now          = date('Y-m-d H:i:s');
    //     foreach ($points as $pt) {
    //         // cek jalur existing dengan model sama dan kapasitas
    //         $stocks = $this->stockModel
    //             ->where('id_anak', $pt['id_anak'])
    //             ->findAll();
    //         $chosen = null;
    //         foreach ($stocks as $stk) {
    //             if ($stk['box_stock'] + $pt['box_count'] <= 80) {
    //                 $chosen = $stk['jalur'];
    //                 break;
    //             }
    //         }
    //         // jika tidak ada, ambil jalur baru
    //         if (!$chosen) {
    //             if (!isset($layouts[$layoutCursor])) break;
    //             $chosen = $layouts[$layoutCursor++]['jalur'];
    //         }
    //         // upsert stock
    //         $exist = $this->stockModel->where('id_anak', $pt['id_anak'])->where('jalur', $chosen)->first();
    //         if ($exist) {
    //             // $this->stockModel->update($exist['id_stock'], [
    //             //     'qty_stock' => $exist['qty_stock'] + $pt['qty'],
    //             //     'box_stock' => $exist['box_stock'] + $pt['box_count'],
    //             // ]);
    //         } else {
    //             // $this->stockModel->insert([
    //             //     'id_anak' => $pt['id_anak'],
    //             //     'created_at' => $now,
    //             //     'qty_stock' => $pt['qty'],
    //             //     'box_stock' => $pt['box_count'],
    //             //     'jalur' => $chosen,
    //             //     'gd_setting' => 'GD SETTING',
    //             //     'admin' => $admin,
    //             // ]);
    //         }
    //         // insert pemasukan
    //         // $this->pemasukanModel->insert([
    //         //     'id_anak' => $pt['id_anak'],
    //         //     'created_at' => $now,
    //         //     'qty_masuk' => $pt['qty'],
    //         //     'box_masuk' => $pt['box_count'],
    //         //     'jalur' => $chosen,
    //         //     'gd_setting' => 'GD SETTING',
    //         //     'admin' => $admin,
    //         // ]);
    //     }

    //     return redirect()->back()->with('success', 'Import & clustering selesai!');
    // }

    public function importStock()
    {
        // 1) Perpanjang batas waktu agar clustering besar tidak timeout
        ini_set('max_execution_time', 300);

        $admin = session()->get('username');
        $file  = $this->request->getFile('file');
        $today = new \DateTime('now');

        // Validasi file
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        // 2) Upload & baca sheet Excel
        $filePath = WRITEPATH . 'uploads/' . $file->getName();
        $file->move(WRITEPATH . 'uploads');
        $sheetArr = IOFactory::load($filePath)
            ->getActiveSheet()
            ->toArray(null, true, true, true);
        unlink($filePath);

        // 3) Kumpulkan baris baru yang belum masuk stock
        $pointsRaw = [];
        foreach (array_slice($sheetArr, 17, null, true) as $idx => $row) {
            $area  = trim($row['AA'] ?? '');
            $style = trim($row['E']  ?? '');
            $qty   = trim($row['M']  ?? '');
            $model = trim($row['V']  ?? '');
            $boxId = trim($row['X']  ?? '');

            if (!$area || !$style || !$qty || !$model || !$boxId) continue;


            // cek induk & anak
            $dataInduk = $this->indukModel
                ->select('id_induk, delivery, kode_buyer AS buyer')
                ->where('no_model', $model)
                ->first();
            if (!$dataInduk) continue;

            $dataAnak = $this->anakModel
                ->select('id_anak')
                ->where('id_induk', $dataInduk['id_induk'])
                ->where('area',     $area)
                ->where('style',    $style)
                ->first();
            if (!$dataAnak) continue;

            // Hitung days dan qty
            $days = $today->diff(new \DateTime($dataInduk['delivery']))->days;
            $qtyBoxes = round(doubleval($qty) / 24, 2);

            // Skip jika stock sudah ada (update di sini jika perlu)
            $exists = $this->stockModel->where('id_anak', $dataAnak['id_anak'])->first();
            if ($exists) {
                // Update stock yang sudah ada
                // $this->stockModel->update($exists['id_stock'], [
                //     'qty_stock' => round($exists['qty_stock'] + $qtyBoxes, 2),
                //     'box_stock' => $exists['box_stock'] + 1,
                // ]);

                // Tambahkan ke tabel pemasukan
                $this->pemasukanModel->insert([
                    'id_anak'   => $dataAnak['id_anak'],
                    'qty_masuk' => $qtyBoxes,
                    'box_masuk' => 1,
                    'jalur'     => $exists['jalur'], // ambil dari data stock yang sudah ada
                    'admin'     => $admin,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
                continue;
            }

            $pointsRaw[] = [
                'id_anak' => $dataAnak['id_anak'],
                'model'   => $model,
                'days'    => $days,
                'qty'     => $qtyBoxes,
                'box_id'  => $boxId,
            ];
        }

        if (empty($pointsRaw)) {
            return redirect()->back()->with('success', 'Semua baris sudah ter‐update stock‑nya.');
        }

        // 4) Siapkan samples untuk clustering: days + hash model
        $samples = [];
        foreach ($pointsRaw as $point) {
            $hashModel = crc32($point['model']);
            $samples[] = [(float)$point['days'], (float)$hashModel];
        }

        // 5) Jalankan K-Means dengan 3 cluster
        $kmeans = new KMeans(3);
        $clusters = $kmeans->cluster($samples);

        // 6) Inisialisasi cluster & kategori
        foreach ($pointsRaw as &$point) {
            $point['cluster']  = null;
            $point['kategori'] = null;
        }
        unset($point);

        // 7) Mapping hasil cluster
        foreach ($clusters as $clusterId => $clusterSamples) {
            foreach ($clusterSamples as $sample) {
                foreach ($pointsRaw as &$point) {
                    if (
                        $point['cluster'] === null &&
                        abs($point['days'] - $sample[0]) < 0.01 &&
                        crc32($point['model']) === (int)$sample[1]
                    ) {
                        $point['cluster'] = $clusterId;
                        break;
                    }
                }
                unset($point);
            }
        }

        // Log contoh data pertama
        $firstPoint = reset($pointsRaw);
        log_message('debug', 'Sample match: model=' . $firstPoint['model'] . ', days=' . $firstPoint['days'] . ', cluster=' . $firstPoint['cluster']);

        // 8) Hitung rata-rata days per cluster
        $clusterDays = [];
        foreach ($clusters as $clusterId => $clusterSamples) {
            $sum = array_sum(array_column($clusterSamples, 0));
            $clusterDays[$clusterId] = $sum / count($clusterSamples);
        }
        asort($clusterDays);

        // 9) Tentukan kategori dari cluster
        $clusterMap = [];
        $i = 0;
        foreach (array_keys($clusterDays) as $cid) {
            $clusterMap[$cid] = ($i === 0 ? 'fast' : ($i === 1 ? 'medium' : 'slow'));
            $i++;
        }

        // 10) Assign kategori ke setiap poin
        foreach ($pointsRaw as &$p) {
            if ($p['cluster'] !== null) {
                $p['kategori'] = $clusterMap[$p['cluster']];
            }
        }
        unset($p);


        // 11) Agregasi per id_anak
        $agg = [];
        foreach ($pointsRaw as $p) {
            $id  = $p['id_anak'];
            if (! isset($agg[$id])) {
                $agg[$id] = [
                    'id_anak'   => $id,
                    'kategori'  => $p['kategori'],       // ← sertakan kategori
                    'total_qty' => $p['qty'],
                    'box_count' => 1,
                ];
            } else {
                $agg[$id]['total_qty'] += $p['qty'];
                $agg[$id]['box_count']++;
            }
        }
        // var_dump($agg);

        // 12) Siapkan layout per kategori
        $layouts = $this->layoutModel->orderBy('jalur', 'ASC')->findAll();
        // Kelompokkan jalur berdasarkan kategori
        $layoutMap = [
            'fast' => [],
            'medium' => [],
            'slow' => [],
        ];

        foreach ($layouts as $L) {
            $prefix = strtoupper(substr($L['jalur'], 0, 1));
            if ($prefix === 'A')     $layoutMap['fast'][]   = $L;
            elseif ($prefix === 'B') $layoutMap['medium'][] = $L;
            else                     $layoutMap['slow'][]   = $L;
        }

        // 13) Inisialisasi kapasitas sisa dari setiap jalur
        $jalurCapacity = [];
        foreach ($layouts as $L) {
            $used = $this->stockModel
                ->where('jalur', $L['jalur'])
                ->selectSum('box_stock', 'total')
                ->first()['total'] ?? 0;
            $jalurCapacity[$L['jalur']] = $L['jumlah_box'] - $used;
        }

        // 14) Simpan ke stock & pemasukan
        $now = date('Y-m-d H:i:s');
        foreach ($agg as $data) {
            $kategori         = $data['kategori'];
            $availableLayouts = $layoutMap[$kategori] ?? [];
            $chosenLayout     = null;

            // pilih jalur pertama yang muat
            foreach ($availableLayouts as $L) {
                $sisa = $jalurCapacity[$L['jalur']] ?? 0;
                if ($sisa >= $data['box_count']) {
                    $chosenLayout = $L;
                    $jalurCapacity[$L['jalur']] -= $data['box_count']; // Kurangi langsung di memori
                    break;
                }
            }

            if (! $chosenLayout) {
                continue;  // skip jika penuh semua
            }

            // Simpan ke stock dan pemasukan
            $this->stockModel->insert([
                'id_anak'   => $data['id_anak'],
                'qty_stock' => 0,
                'box_stock' => 0,
                'jalur'     => $chosenLayout['jalur'],
                'admin'     => $admin,
                'created_at' => $now,
            ]);

            $this->pemasukanModel->insert([
                'id_anak'   => $data['id_anak'],
                'qty_masuk' => round($data['total_qty'], 2),
                'box_masuk' => $data['box_count'],
                'jalur'     => $chosenLayout['jalur'],
                'admin'     => $admin,
                'created_at' => $now,
            ]);
        }

        return redirect()->back()->with('success', 'Import & clustering selesai!');
    }

    public function importStockTanpaLibrary()
    {
        ini_set('max_execution_time', 300);
        $admin         = session()->get('username');
        $file          = $this->request->getFile('file');
        $today         = (new \DateTime('now'))->modify('+2 day')->setTime(0, 0, 0);

        // Validasi file
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        // Upload & baca sheet
        $uploadPath    = WRITEPATH . 'uploads/';
        $file->move($uploadPath);
        $fullPath      = $uploadPath . $file->getName();
        $sheetArr      = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath)
            ->getActiveSheet()
            ->toArray(null, true, true, true);
        unlink($fullPath);

        $failedRows      = [];
        $errorDetails    = [];
        $pointsRaw       = [];
        $skippedExists   = [];

        // Siapkan layout & kapasitas awal per jalur
        $layouts         = $this->layoutModel->orderBy('jalur', 'ASC')->findAll();
        $jalurCapacity   = [];
        foreach ($layouts as $L) {
            $used = $this->stockModel
                ->where('jalur', $L['jalur'])
                ->selectSum('box_stock', 'total')
                ->first()['total'] ?? 0;
            $jalurCapacity[$L['jalur']] = $L['jumlah_box'] - $used;
        }

        // Proses tiap baris: validasi & ambil data Induk/Anak
        $modelDateLane = []; // Inisialisasi array untuk mapping model+tanggal ke jalur
        foreach (array_slice($sheetArr, 17, null, true) as $idx => $row) {
            $rowNum = $idx + 1;
            $area   = trim($row['AA'] ?? '');
            $style  = trim($row['E']  ?? '');
            $qty    = trim($row['M']  ?? '');
            $model  = trim($row['V']  ?? '');
            $boxId  = trim($row['X']  ?? '');

            // Jika model, area, atau style kosong, langsung continue
            if (! $model || ! $area || ! $style) {
                continue;
            }
            // Validasi kolom wajib
            if (! $qty || ! $boxId) {
                $failedRows[] = $rowNum;
                $errorDetails[$rowNum] = 'Data kolom tidak lengkap';
                continue;
            }

            // Cari data induk
            $dataInduk = $this->indukModel
                ->select('id_induk, delivery')
                ->where('no_model', $model)
                ->first();
                // dd ($dataInduk);
            if (! $dataInduk) {
                $failedRows[] = $rowNum;
                $errorDetails[$rowNum] = 'Model tidak ditemukan';
                continue;
            }

            // Cari data anak
            $dataAnak = $this->anakModel
                ->select('id_anak')
                ->where([
                    'id_induk' => $dataInduk['id_induk'],
                    'area'     => $area,
                    'style'    => $style
                ])
                ->first();
                // dd ($dataAnak);
            if (! $dataAnak) {
                $failedRows[] = $rowNum;
                $errorDetails[$rowNum] = 'Data anak (area/style) tidak ditemukan';
                continue;
            }

            $days     = $today->diff(new \DateTime($dataInduk['delivery']))->days;
            $qtyBoxes = round((float)$qty / 24, 2);

            // Cek stock eksisting dan penempatan berdasarkan model+tanggal
            $exists = $this->stockModel->where('id_anak', $dataAnak['id_anak'])
                ->where("DATE(created_at) = '{$today->format('Y-m-d')}'", null, false)
                ->first();
                // dd ($exists);
            if ($exists) {
                // Key unik berdasarkan model + tanggal delivery
                $deliveryDate   = (new \DateTime($dataInduk['delivery']))->format('Y-m-d');
                $modelDateKey   = $model . '|' . $today->format('Y-m-d');

                // Simpan jalur awal jika belum ada
                if (! isset($modelDateLane[$modelDateKey])) {
                    $modelDateLane[$modelDateKey] = $exists['jalur'];
                }
                $j = $modelDateLane[$modelDateKey];

                // Jika kapasitas jalur j sudah habis, cari jalur lain yang masih punya space
                if (($jalurCapacity[$j] ?? 0) < 1) {
                    foreach ($layouts as $L) {
                        if (($jalurCapacity[$L['jalur']] ?? 0) >= 1) {
                            $j = $L['jalur'];
                            $modelDateLane[$modelDateKey] = $j;  // update jalur untuk model+tanggal ini
                            // Catat error detail
                            $skippedExists[]       = $rowNum;
                            $errorDetails[$rowNum] = "Jalur {$exists['jalur']} penuh, dialihkan ke jalur {$j}";
                            continue;
                        }
                    }
                }

                // Kalau setelah cari tetap penuh, skip baris
                if (($jalurCapacity[$j] ?? 0) < 1) {
                    $skippedExists[]       = $rowNum;
                    $errorDetails[$rowNum] = "Semua jalur penuh untuk model {$model} pada {$deliveryDate}";
                    continue;
                }

                // Kurangi memori kapasitas untuk 1 box, lalu insert
                $jalurCapacity[$j]--;
                $this->pemasukanModel->insert([
                    'id_anak'   => $dataAnak['id_anak'],
                    'qty_masuk' => $qtyBoxes,
                    'box_masuk' => 1,
                    'jalur'     => $j,
                    'admin'     => $admin,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                continue;
            }


            // Kumpulkan data baru untuk clustering
            $pointsRaw[] = [
                'id_anak' => $dataAnak['id_anak'],
                'model'   => $model,
                'days'    => $days,
                'qty'     => $qtyBoxes,
                'box_id'  => $boxId,
            ];
        }

        // Jika tidak ada poin baru dan tidak ada exists yang skip
        if (empty($pointsRaw) && empty($skippedExists)) {
            // Komposisi pesan
            $msg = 'Semua baris telah terproses.';
            if ($failedRows) {
                $msg .= ' Baris gagal: ' . implode(', ', $failedRows) . '.';
                $msg .= '\nDetail: ' . json_encode($errorDetails);
                return redirect()->back()->with('error', nl2br($msg));
            }
            return redirect()->back()->with('success', $msg);
        }

        // Step 4: Siapkan samples untuk clustering
        $samples = [];
        foreach ($pointsRaw as $p) {
            $samples[] = [(float)$p['days'], (float)crc32($p['model'])];
        }

        $clusters = [];
        if (count($samples) > 0) {
            // Tentukan k tidak lebih besar dari jumlah samples
            $k = min(3, count($samples));
            $maxIter = 100;

            // Inisialisasi centroid acak
            $centroids = [];
            $indexes   = array_keys($samples);
            shuffle($indexes);
            $init = array_slice($indexes, 0, $k);
            foreach ($init as $i) {
                $centroids[] = $samples[$i];
            }

            // Iterasi K-Means
            for ($iter = 0; $iter < $maxIter; $iter++) {
                // Assign samples ke cluster
                $newClusters = array_fill(0, $k, []);
                foreach ($samples as $s) {
                    $best = 0;
                    $minD = PHP_FLOAT_MAX;
                    foreach ($centroids as $ci => $c) {
                        $d = sqrt(pow($s[0] - $c[0], 2) + pow($s[1] - $c[1], 2));
                        if ($d < $minD) {
                            $minD = $d;
                            $best = $ci;
                        }
                    }
                    $newClusters[$best][] = $s;
                }
                // Hitung centroid baru
                $converged = true;
                foreach ($newClusters as $ci => $group) {
                    if (empty($group)) continue;
                    $sumX = array_sum(array_column($group, 0));
                    $sumY = array_sum(array_column($group, 1));
                    $count = count($group);
                    $newC = [$sumX / $count, $sumY / $count];
                    if (abs($newC[0] - $centroids[$ci][0]) > 1e-4 || abs($newC[1] - $centroids[$ci][1]) > 1e-4) {
                        $converged = false;
                    }
                    $centroids[$ci] = $newC;
                }
                $clusters = $newClusters;
                if ($converged) break;
            }
        }

        // Step 6: Inisialisasi cluster & kategori
        foreach ($pointsRaw as &$point) {
            $point['cluster']  = null;
            $point['kategori'] = null;
        }
        unset($point);

        // Step 7: Mapping hasil cluster
        foreach ($clusters as $clusterId => $clusterSamples) {
            foreach ($clusterSamples as $sample) {
                foreach ($pointsRaw as &$point) {
                    if (
                        $point['cluster'] === null &&
                        abs($point['days'] - $sample[0]) < 0.01 &&
                        crc32($point['model']) === (int)$sample[1]
                    ) {
                        $point['cluster'] = $clusterId;
                        break;
                    }
                }
                unset($point);
            }
        }

        $firstPoint = reset($pointsRaw);

        // Step 8: Hitung rata-rata days per cluster
        $clusterDays = [];
        foreach ($clusters as $clusterId => $clusterSamples) {
            $sum = array_sum(array_column($clusterSamples, 0));
            $clusterDays[$clusterId] = $sum / count($clusterSamples);
        }
        asort($clusterDays);

        // Step 9: Tentukan kategori dari cluster
        $clusterMap = [];
        $i = 0;
        foreach (array_keys($clusterDays) as $cid) {
            $clusterMap[$cid] = ($i === 0 ? 'fast' : ($i === 1 ? 'medium' : 'slow'));
            $i++;
        }
        // dd ($clusterMap);
        // Step 10: Assign kategori ke setiap poin
        foreach ($pointsRaw as &$p) {
            if ($p['cluster'] !== null) {
                $p['kategori'] = $clusterMap[$p['cluster']];
            }
        }
        unset($p);

        // Setelah clustering -> agregasi per anak
        $agg = [];
        foreach ($pointsRaw as $p) {
            $id = $p['id_anak'];
            if (! isset($agg[$id])) {
                $agg[$id] = [
                    'id_anak'   => $id,
                    'kategori'  => $p['kategori'],
                    'total_qty' => $p['qty'],
                    'box_count' => 1,
                ];
            } else {
                $agg[$id]['total_qty'] += $p['qty'];
                $agg[$id]['box_count']++;
            }
        }

        // Mapping layout berdasarkan kategori
        $layoutMap = ['fast' => [], 'medium' => [], 'slow' => []];
        foreach ($layouts as $L) {
            $prefix = strtoupper(substr($L['jalur'], 0, 1));
            if ($prefix === 'A') $layoutMap['fast'][]   = $L;
            elseif ($prefix === 'B') $layoutMap['medium'][] = $L;
            else $layoutMap['slow'][] = $L;
        }

        // Simpan untuk poin baru
        $now         = date('Y-m-d H:i:s');
        $skippedNew  = [];

        foreach ($agg as $data) {
            $list   = $layoutMap[$data['kategori']] ?? [];
            $chosen = null;
            foreach ($list as $L) {
                if (($jalurCapacity[$L['jalur']] ?? 0) >= $data['box_count']) {
                    $chosen = $L;
                    break;
                }
            }
            if (! $chosen) {
                $skippedNew[] = $data['id_anak'];
                $errorDetails['new_' . $data['id_anak']] = 'Tidak ada kapasitas untuk kategori ' . $data['kategori'];
                continue;
            }

            $jalurCapacity[$chosen['jalur']] -= $data['box_count'];
            // Insert stock & pemasukan
            $this->stockModel->insert([
                'id_anak'   => $data['id_anak'],
                'qty_stock' => 0,
                'box_stock' => 0,
                'jalur'     => $chosen['jalur'],
                'admin'     => $admin,
                'created_at' => $now,
            ]);
            $this->pemasukanModel->insert([
                'id_anak'   => $data['id_anak'],
                'qty_masuk' => round($data['total_qty'], 2),
                'box_masuk' => $data['box_count'],
                'jalur'     => $chosen['jalur'],
                'admin'     => $admin,
                'created_at' => $now,
            ]);
        }

        // Bangun pesan akhir dengan detail error
        $msg    = 'Import selesai.';
        $status = 'success';
        if ($failedRows || $skippedExists || $skippedNew) {
            $status = 'error';
            $msg .= '<ul>';
            if ($failedRows) {
            $msg .= '<li>Baris gagal: ' . implode(', ', $failedRows) . '</li>';
            }
            if ($skippedExists) {
            $msg .= '<li>Baris penuh jalur (exists): ' . implode(', ', $skippedExists) . '</li>';
            }
            if ($skippedNew) {
            $msg .= '<li>Anak baru tanpa space: ' . implode(', ', $skippedNew) . '</li>';
            }
            if ($errorDetails) {
            $msg .= '<li>Detail:<ul>';
            foreach ($errorDetails as $row => $err) {
                $msg .= '<li>' . htmlspecialchars($row) . ': ' . htmlspecialchars($err) . '</li>';
            }
            $msg .= '</ul></li>';
            }
            $msg .= '</ul>';
            return redirect()->back()->with($status, $msg);
        }

        return redirect()->back()->with($status, $msg);
    }
}
