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
        $role = session()->get('role');

        $data = [
            'role' => $role,
        ];
        return view($role . '/stock', $data);
    }

    public function dataPermintaan()
    {
        $role = session()->get('role');

        $data = [
            'role' => $role,
        ];
        return view($role . '/datapermintaan', $data);
    }

    public function dataTerkirim()
    {
        $role = session()->get('role');

        $data = [
            'role' => $role,
        ];
        return view($role . '/dataterkirim', $data);
    }

    public function reportPemasukan()
    {
        $role = session()->get('role');

        $data = [
            'role' => $role,
        ];
        return view($role . '/reportpemasukan', $data);
    }

    public function reportPengeluaran()
    {
        $role = session()->get('role');

        $data = [
            'role' => $role,
        ];
        return view($role . '/reportpengeluaran', $data);
    }
}
