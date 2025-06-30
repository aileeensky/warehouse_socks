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

    public function inputNoModel()
    {
        $role = session()->get('role');
        $dataAnak = $this->anakModel->getSelect();
        $data = [
            'role' => $role,
            'db' => $dataAnak,
        ];
        return view($role . '/inputnomodel', $data);
    }

    public function importDatabase()
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

    // public function importStock() 
    // {
    //     $admin = session()->get('username');
    //     $file = $this->request->getFile('file');

    //     $stylesNotInserted = []; // Variabel untuk menyimpan style yang tidak berhasil diinsert
    //     $totalInserted = 0;

    //     if ($file && $file->isValid() && !$file->hasMoved()) {
    //         log_message('info', 'File uploaded: ' . $file->getName());

    //         $filePath = WRITEPATH . 'uploads/' . $file->getName();
    //         $file->move(WRITEPATH . 'uploads');

    //         try {
    //             $spreadsheet = IOFactory::load($filePath);
    //             $sheet = $spreadsheet->getActiveSheet();
    //             $dataRows = $sheet->toArray();
    //             log_message('info', 'Number of data rows: ' . count($dataRows));
    //         } catch (\Exception $e) {
    //             log_message('error', 'Error reading Excel file: ' . $e->getMessage());
    //             return redirect()->back()->with('error', 'Error reading Excel file.');
    //         }

    //         $dataKey = [];
    //         foreach ($dataRows as $index => $row) {
    //             // Lewati header atau baris pertama
    //             if ($index === 0) {
    //                 continue;
    //             }

    //             // Validasi untuk kolom yang penting
    //             if (empty($row[4]) || empty($row[12]) || empty($row[21]) || empty($row[26])) {
    //                 log_message('info', 'Skipping row ' . ($index + 1) . ' due to empty required fields.');
    //                 continue; // Lewati jika kolom penting kosong
    //             }

    //             $dataKey[] = [$row[4] . ';' . $row[21] . ';' . $row[26] => 0];

    //             $dataInduk = $this->indukModel->select('id_induk, delivery')->where('no_model', $row[21])->first();
    //             if (!$dataInduk) {
    //                 log_message('info', 'Skipping row ' . ($index + 1) . ' due to no_model not found.');
    //                 continue; // Lewati jika no_model tidak ditemukan
    //             }

    //             $dataAnak = $this->anakModel->select('id_anak')->where('id_induk', $dataInduk['id_induk'])->where('area', $row[26])->where('style', $row[4])->first();
    //             if (!$dataAnak) {
    //                 log_message('info', 'Skipping row ' . ($index + 1) . ' due to id_anak not found.');
    //                 continue; // Lewati jika id_anak tidak ditemukan
    //             }

    //             $dataStock = $this->stockModel->select('id_stock')->where('id_anak', $dataAnak['id_anak'])->first();
    //             if (!$dataStock) {
    //                 log_message('info', 'Skipping row ' . ($index + 1) . ' due to id_stock not found.');
    //                 continue; // Lewati jika id_anak tidak ditemukan
    //             }

    //             $thisMonth = new DateTime('now');
    //             $add2Month = $thisMonth->modify('+2 month')->format('Y-m-d');
    //             $add2Month = $thisMonth->modify('+3 month')->format('Y-m-d');

    //             // $thisMonth->modify('+2 month');
    //             // $thisMonth = $thisMonth->format('Y-m-d');

    //             if ($dataInduk['delivery'] > $thisMonth && $dataInduk['delivery'] < $add2Month) {
    //             }

    //         }

    //         // Hapus file setelah proses selesai
    //         unlink($filePath);

    //     }
    // }

    public function importStock()
    {
        $admin  = session()->get('username');
        $file   = $this->request->getFile('file');
        $today  = new \DateTime('now');

        // array untuk baris tanpa stock (nanti di‐cluster)
        $points = [];

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $filePath = WRITEPATH . 'uploads/' . $file->getName();
            $file->move(WRITEPATH . 'uploads');

            $sheet = IOFactory::load($filePath)
                ->getActiveSheet()
                ->toArray(null, true, true, true);

            foreach (array_slice($sheet, 17, null, true) as $idx => $row) {
                // Trim & ambil key
                $area  = trim($row['AA']);
                $style = trim($row['E']);
                $qty   = trim($row['M']);
                $model = trim($row['V']);

                // Skip kalau data minimal kosong
                if ($area === '' || $style === '' || $qty === '' || $model === '') {
                    continue;
                }

                // Ambil data induk (pakai alias buyer)
                $dataInduk = $this->indukModel
                    ->select('id_induk, delivery, kode_buyer AS buyer')
                    ->where('no_model', $model)
                    ->first();
                if (! $dataInduk) continue;

                // Ambil data anak
                $dataAnak = $this->anakModel
                    ->select('id_anak')
                    ->where('id_induk', $dataInduk['id_induk'])
                    ->where('area', $area)
                    ->where('style', $style)
                    ->first();
                if (! $dataAnak) continue;

                // Coba ambil data stock
                $stock = $this->stockModel
                    ->select('id_stock, qty_stock, box_stock')
                    ->where('id_anak', $dataAnak['id_anak'])
                    ->first();

                if ($stock) {
                    // 1) Update stock yang sudah ada
                    $tambahQty = floatval($qty) / 24;
                    $newQty    = floatval($stock['qty_stock']) + $tambahQty;
                    $newBox    = floatval($stock['box_stock']) + 1;

                    $this->stockModel->update($stock['id_stock'], [
                        'qty_stock' => $newQty,
                        'box_stock' => $newBox,
                    ]);
                } else {
                    // 2) Belum ada stock → masuk ke clustering
                    $delivDate = new \DateTime($dataInduk['delivery']);
                    $daysToExp = $today->diff($delivDate)->days;

                    $points[] = [
                        'id_anak' => $dataAnak['id_anak'],
                        'buyer'    => $dataInduk['buyer'],
                        'days'     => $daysToExp,
                    ];
                }
            }

            // Hapus file upload
            unlink($filePath);

            // Kalau tidak ada poin sama sekali, berarti semua sudah di‐update
            if (empty($points)) {
                return redirect()->back()->with('success', 'Semua baris ditemukan stock-nya, sudah di‐update.');
            }

            //
            // ——————— PROSES CLUSTERING ———————
            //

            // load semua layout (520 jalur A→Z→AA→…)
            $layouts = $this->layoutModel
                ->orderBy('jalur', 'ASC')
                ->findAll();
            $k = count($layouts);

            // 1) Label‐encode buyer
            $buyers = array_unique(array_column($points, 'buyer'));
            $encode = array_flip($buyers);
            foreach ($points as &$p) {
                $p['bcode'] = $encode[$p['buyer']];
            }
            unset($p);

            // 2) Normalisasi Min‑Max
            $bcArr = array_column($points, 'bcode');
            $dyArr = array_column($points, 'days');
            $minB  = min($bcArr);
            $maxB = max($bcArr);
            $minD  = min($dyArr);
            $maxD = max($dyArr);
            foreach ($points as &$p) {
                $p['nb'] = ($p['bcode'] - $minB) / max(1, $maxB - $minB);
                $p['nd'] = ($p['days']  - $minD) / max(1, $maxD - $minD);
            }
            unset($p);

            // 3) Inisialisasi centroid (acak merata di rentang days)
            $centroids = [];
            for ($i = 0; $i < $k; $i++) {
                $centroids[] = [
                    'x' => 0.0,
                    'y' => $minD + ($maxD - $minD) * ($i / ($k - 1)),
                ];
            }

            // 4) Iterasi K‐Means
            $eps     = 0.001;
            $maxIter = 100;
            for ($iter = 0; $iter < $maxIter; $iter++) {
                // assign
                foreach ($points as &$pt) {
                    $dists = array_map(
                        fn($c) =>
                        sqrt(($pt['nb'] - $c['x']) ** 2 + ($pt['nd'] - $c['y']) ** 2),
                        $centroids
                    );
                    $pt['cluster'] = array_search(min($dists), $dists);
                }
                unset($pt);

                // recompute
                $sums = array_fill(0, $k, ['sx' => 0, 'sy' => 0, 'cnt' => 0]);
                foreach ($points as $pt) {
                    $c = $pt['cluster'];
                    $sums[$c]['sx']  += $pt['nb'];
                    $sums[$c]['sy']  += $pt['nd'];
                    $sums[$c]['cnt']++;
                }
                $moved = false;
                foreach ($sums as $iC => $v) {
                    if ($v['cnt'] === 0) continue;
                    $nx = $v['sx'] / $v['cnt'];
                    $ny = $v['sy'] / $v['cnt'];
                    if (
                        abs($nx - $centroids[$iC]['x']) > $eps
                        || abs($ny - $centroids[$iC]['y']) > $eps
                    ) {
                        $moved = true;
                    }
                    $centroids[$iC] = ['x' => $nx, 'y' => $ny];
                }
                if (! $moved) break;
            }

            //
            // ——————— MAPPING KE LAYOUT (80 per jalur) ———————
            //

            // hitung rata‐rata days per klaster
            $clusterDays = [];
            foreach ($points as $p) {
                $clusterDays[$p['cluster']][] = $p['days'];
            }
            $centroidDays = [];
            for ($i = 0; $i < $k; $i++) {
                $list = $clusterDays[$i] ?? [];
                $centroidDays[$i] = array_sum($list) / max(1, count($list));
            }
            // urutkan ascending: days kecil → layout depan
            asort($centroidDays, SORT_NUMERIC);

            // split tiap klaster ke jalur berkapasitas 80
            $layoutCursor = 0;
            foreach (array_keys($centroidDays) as $clusterIdx) {
                $members = array_filter($points, fn($p) => $p['cluster'] == $clusterIdx);
                $batches = array_chunk($members, 80);
                foreach ($batches as $batch) {
                    if (! isset($layouts[$layoutCursor])) break;
                    $jalurKey  = $layouts[$layoutCursor++]['jalur'];
                    foreach ($batch as $pt) {
                        // update stock baru dengan layout_id
                        $this->stockModel->update($pt['id_anak'], [
                            'jalur' => $jalurKey
                        ]);
                    }
                }
            }

            return redirect()->back()
                ->with('success', 'Import & clustering + penempatan layout selesai!');
        }
    }
}
