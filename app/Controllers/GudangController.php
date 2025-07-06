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
            'role' => $role,
            'stock' => $totalStock,
            'pemasukan' => $totalPemasukan,
            'permintaan' => $totalPermintaan,
            'pengeluaran' => $totalPengeluaran,
            'chartData' => $chartData,
        ];
        return view($role . '/index', $data);
    }

    public function layout()
    {
        $role = session()->get('role');

        $data = [
            'role' => $role,
        ];
        return view($role . '/index', $data);
    }

    public function dataOrder()
    {
        $role = session()->get('role');
        $dataAnak = $this->anakModel->getSelect();
        $data = [
            'role' => $role,
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

        $data = [
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

        $data = [
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
            }

            return redirect()->to(base_url(session()->get('role') . '/dataterkirim/'))
                ->withInput()
                ->with('success', 'Berhasil Input Pengeluaran');
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
            'role' => $role,
            'dataMasuk' => $dataMasuk,
            'title' => 'Report Pemasukan',
        ];
        return view($role . '/reportpemasukan', $data);
    }

    public function reportPengeluaran()
    {
        $role = session()->get('role');
        $dataKeluar = $this->pengeluaranModel->getData();
        dd($dataKeluar);
        $data = [
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
            $qtyBoxes = floatval($qty) / 24;

            // Skip jika stock sudah ada (update di sini jika perlu)
            $exists = $this->stockModel->where('id_anak', $dataAnak['id_anak'])->first();
            if ($exists) {
                // Contoh update
                $this->stockModel->update($exists['id_stock'], [
                    'qty_stock' => $exists['qty_stock'] + $qtyBoxes,
                    'box_stock' => $exists['box_stock'] + 1,
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

        // Siapkan samples untuk clustering: days + hash model
        $samples = [];
        foreach ($pointsRaw as $p) {
            $hashModel = crc32($p['model']);
            $samples[] = [(float)$p['days'], (float)$hashModel];
        }

        // Jalankan clustering
        $clusterCount = count($this->layoutModel->findAll());
        $kmeans = new KMeans($clusterCount);
        $clusters = $kmeans->cluster($samples);

        // Mapping hasil cluster ke pointsRaw
        foreach ($clusters as $clusterId => $clusterSamples) {
            foreach ($clusterSamples as $sample) {
                foreach ($pointsRaw as &$p) {
                    $hashModel = crc32($p['model']);
                    if ((float)$p['days'] === $sample[0] && (float)$hashModel === $sample[1] && !isset($p['cluster'])) {
                        $p['cluster'] = $clusterId;
                        break;
                    }
                }
            }
        }
        unset($p);

        // 5) Agregasi per id_anak
        $agg = [];
        foreach ($pointsRaw as $p) {
            $id = $p['id_anak'];
            if (!isset($agg[$id])) {
                $agg[$id] = [
                    'id_anak'   => $id,
                    'cluster'   => $p['cluster'],
                    'total_qty' => $p['qty'],
                    'box_count' => 1,
                ];
            } else {
                $agg[$id]['total_qty'] += $p['qty'];
                $agg[$id]['box_count']++;
            }
        }

        // 6) Simpan ke stock & pemasukan
        $layouts = $this->layoutModel->orderBy('jalur', 'ASC')->findAll();
        $now = date('Y-m-d H:i:s');

        foreach ($agg as $data) {
            $layout = $layouts[$data['cluster']] ?? null;
            if (!$layout) continue;

            $exist = $this->stockModel
                ->where('id_anak', $data['id_anak'])
                ->where('jalur', $layout['jalur'])
                ->first();

            if ($exist) {
                $this->stockModel->update($exist['id_stock'], [
                    'qty_stock' => $exist['qty_stock'] + $data['total_qty'],
                    'box_stock' => $exist['box_stock'] + $data['box_count'],
                ]);
            } else {
                $this->stockModel->insert([
                    'id_anak'   => $data['id_anak'],
                    'qty_stock' => 0,
                    'box_stock' => 0,
                    'jalur'     => $layout['jalur'],
                    'admin'     => $admin,
                    'created_at' => $now,
                ]);
            }

            $this->pemasukanModel->insert([
                'id_anak'   => $data['id_anak'],
                'qty_masuk' => $data['total_qty'],
                'box_masuk' => $data['box_count'],
                'jalur'     => $layout['jalur'],
                'admin'     => $admin,
                'created_at' => $now,
            ]);
        }

        return redirect()->back()->with('success', 'Import & clustering selesai!');
    }
}
