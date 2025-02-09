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

        $data = [
            'role' => $role,
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

                $data = [
                    'no_order' => $row[3],
                    'no_model' => $row[4],
                    'kode_buyer' => $row[2],
                    'smv' => $row[8],
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

                if (!$existingAnak) {
                    $data2 = [
                        'id_induk' => $id_induk,
                        'waktu_input' => $waktu_input,
                        'area' => $row[0],
                        'inisial' => $row[5],
                        'style' => $style,
                        'warna' => $row[7],
                        'qty_po_inisial' => $row[9],
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

        $data = [
            'role' => $role,
            'dataKeluar' => '$dataKeluar',
        ];

        return view($role . '/reportpengeluaran', $data);
    }
}
