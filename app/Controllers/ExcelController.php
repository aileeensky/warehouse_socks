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

class ExcelController extends BaseController
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

    public function excelStockGudang()
    {
        $admin = session()->get('username');
        $role = session()->get('role');

        // Ambil data dari model
        $dataJalur = $this->layoutModel->getDataJalur();

        // Load PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul laporan di baris pertama
        $sheet->setCellValue('A1', 'Stock Gudang Kaos Kaki');
        $sheet->mergeCells('A1:H1'); // Gabungkan sel A1 sampai H1
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set header tabel di baris ke-3
        $columns = [
            'A' => 'No',
            'B' => 'Jalur',
            'C' => 'Kapasitas',
            'D' => 'Space',
            'E' => 'Qty Jalur',
            'F' => 'Box',
            'G' => 'No Model',
            'H' => 'Keterangan'
        ];

        // Set header tabel
        foreach ($columns as $col => $value) {
            $sheet->setCellValue($col . '3', $value);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Isi data dari database dimulai dari baris ke-4
        $row = 4;
        $no = 1;
        foreach ($dataJalur as $data) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $data['jalur']);
            $sheet->setCellValue('C' . $row, $data['jumlah_box']);
            $sheet->setCellValue('D' . $row, $data['space']);
            $sheet->setCellValue('E' . $row, $data['qty_stock']);
            $sheet->setCellValue('F' . $row, $data['box_stock']);
            $sheet->setCellValue('G' . $row, $data['models']);
            $sheet->setCellValue('H' . $row, $data['keterangan']);

            // Align center untuk semua data
            foreach (range('A', 'H') as $col) {
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }

            $row++;
            $no++;
        }

        // Styling tabel (border)
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A3:H' . ($row - 1))->applyFromArray($styleArray);

        // Nama file
        $fileName = 'Report_Stock_Gudang.xlsx';

        // Set header untuk download file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function excelReportPemasukan()
    {
        $nomodel = $this->request->getPost('cari1');
        $tgl_masuk = $this->request->getPost('cari2');

        $role = session()->get('role');
        $dataMasuk = $this->pemasukanModel->getData($nomodel, $tgl_masuk);

        // Load PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul laporan
        $sheet->setCellValue('A1', 'Report Pemasukan');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Header tabel
        $columns = [
            'A' => 'No',
            'B' => 'Tgl Masuk',
            'C' => 'Area',
            'D' => 'Buyer',
            'E' => 'No Model',
            'F' => 'In',
            'G' => 'Style',
            'H' => 'Qty Masuk',
            'I' => 'Box Masuk',
            'J' => 'Keterangan'
        ];

        foreach ($columns as $col => $value) {
            $sheet->setCellValue($col . '3', $value);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Isi data dari database dimulai dari baris ke-4
        $row = 4;
        $no = 1;
        $totalQtyMasuk = 0;
        $totalBoxMasuk = 0;
        foreach ($dataMasuk as $dt) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $dt['created_at']);
            $sheet->setCellValue('C' . $row, $dt['area']);
            $sheet->setCellValue('D' . $row, $dt['kode_buyer']);
            $sheet->setCellValue('E' . $row, $dt['no_model']);
            $sheet->setCellValue('F' . $row, $dt['inisial']);
            $sheet->setCellValue('G' . $row, $dt['style']);
            $sheet->setCellValue('H' . $row, $dt['qty_masuk']);
            $sheet->setCellValue('I' . $row, $dt['box_masuk']);
            $sheet->setCellValue('J' . $row, $dt['ket_masuk']);

            // Align center untuk semua data
            foreach (range('A', 'J') as $col) {
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }

            $row++;
            $no++;
            $totalQtyMasuk += $dt['qty_masuk'];;
            $totalBoxMasuk += $dt['qty_masuk'];;
        }

        // Styling tabel (border)
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A3:J' . ($row - 1))->applyFromArray($styleArray);

        //TOTAL
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, $totalQtyMasuk);
        $sheet->setCellValue('I' . $row, $totalBoxMasuk);

        // Styling total row
        $sheet->getStyle("A$row:J$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:J$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$row:J$row")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);


        // Nama file
        $fileName = 'Report_Pemasukan.xlsx';

        // Set header untuk download file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function excelReportPengeluaran()
    {
        $nomodel = $this->request->getPost('cari1');
        $tgl_keluar = $this->request->getPost('cari2');

        $role = session()->get('role');
        $dataKeluar = $this->pengeluaranModel->getData($nomodel, $tgl_keluar);

        // Load PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul laporan
        $sheet->setCellValue('A1', 'Report Pengeluaran');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Header tabel
        $columns = [
            'A' => 'No',
            'B' => 'Tgl Keluar',
            'C' => 'Area',
            'D' => 'Buyer',
            'E' => 'No Model',
            'F' => 'In',
            'G' => 'Style',
            'H' => 'Qty Keluar',
            'I' => 'Box Keluar',
            'J' => 'Keterangan'
        ];

        foreach ($columns as $col => $value) {
            $sheet->setCellValue($col . '3', $value);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Isi data dari database dimulai dari baris ke-4
        $row = 4;
        $no = 1;
        $totalQtyKeluar = 0;
        $totalBoxKeluar = 0;

        foreach ($dataKeluar as $dt) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $dt['tgl_keluar']);
            $sheet->setCellValue('C' . $row, $dt['area']);
            $sheet->setCellValue('D' . $row, $dt['kode_buyer']);
            $sheet->setCellValue('E' . $row, $dt['no_model']);
            $sheet->setCellValue('F' . $row, $dt['inisial']);
            $sheet->setCellValue('G' . $row, $dt['style']);
            $sheet->setCellValue('H' . $row, $dt['qty_keluar']);
            $sheet->setCellValue('I' . $row, $dt['box_keluar']);
            $sheet->setCellValue('J' . $row, $dt['ket_keluar']);

            // Align center untuk semua data
            foreach (range('A', 'J') as $col) {
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }

            $row++;
            $no++;
            $totalQtyKeluar += $dt['qty_keluar'];
            $totalBoxKeluar += $dt['box_keluar'];
        }

        // Styling tabel (border)
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A3:J' . ($row - 1))->applyFromArray($styleArray);

        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->setCellValue('H' . $row, $totalQtyKeluar);
        $sheet->setCellValue('I' . $row, $totalBoxKeluar);

        // Styling total row
        $sheet->getStyle("A$row:J$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:J$row")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$row:J$row")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);


        // Tambahkan border untuk total
        $sheet->getStyle("G$row:I$row")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);


        // Nama file
        $fileName = 'Report_Pengeluaran.xlsx';

        // Set header untuk download file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function excelDataOrder()
    {
        $role = session()->get('role');
        $dataOrder = $this->anakModel->getSelect();

        // Load PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul laporan
        $sheet->setCellValue('A1', 'Data Order');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Header tabel
        $columns = [
            'A' => 'No',
            'B' => 'Waktu Input',
            'C' => 'No Order',
            'D' => 'Area',
            'E' => 'Buyer',
            'F' => 'No Model',
            'G' => 'In',
            'H' => 'Style',
            'I' => 'Color',
            'J' => 'Delivery',
            'K' => 'Qty PO',
            'L' => 'Admin'
        ];

        foreach ($columns as $col => $value) {
            $sheet->setCellValue($col . '3', $value);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Isi data dari database dimulai dari baris ke-4
        $row = 4;
        $no = 1;
        foreach ($dataOrder as $dt) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $dt['waktu_input']);
            $sheet->setCellValue('C' . $row, $dt['no_order']);
            $sheet->setCellValue('D' . $row, $dt['area']);
            $sheet->setCellValue('E' . $row, $dt['kode_buyer']);
            $sheet->setCellValue('F' . $row, $dt['no_model']);
            $sheet->setCellValue('G' . $row, $dt['inisial']);
            $sheet->setCellValue('H' . $row, $dt['style']);
            $sheet->setCellValue('I' . $row, $dt['warna']);
            $sheet->setCellValue('J' . $row, $dt['delivery']);
            $sheet->setCellValue('K' . $row, $dt['qty_po_inisial']);
            $sheet->setCellValue('L' . $row, $dt['admin']);

            // Align center untuk semua data
            foreach (range('A', 'L') as $col) {
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }

            $row++;
            $no++;
        }

        // Styling tabel (border)
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A3:L' . ($row - 1))->applyFromArray($styleArray);

        // Nama file
        $fileName = 'Report_Data_Order.xlsx';

        // Set header untuk download file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function excelReportStock()
    {
        $nomodel = $this->request->getPost('cari1');

        $role = session()->get('role');
        $dataStock = $this->stockModel->getAllStock($nomodel);

        // Load PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul laporan
        $sheet->setCellValue('A1', 'Data Stock Gudang');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Header tabel
        $columns = [
            'A' => 'No',
            'B' => 'Kode Buyer',
            'C' => 'No Model',
            'D' => 'Area',
            'E' => 'Inisial',
            'F' => 'Style',
            'G' => 'Warna',
            'H' => 'Delivery',
            'I' => 'Qty Stock',
            'J' => 'Box Stock'
        ];

        foreach ($columns as $col => $value) {
            $sheet->setCellValue($col . '3', $value);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Isi data dari database dimulai dari baris ke-4
        $row = 4;
        $no = 1;
        $totalQty = 0;
        $totalBox = 0;

        foreach ($dataStock as $dt) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $dt['kode_buyer']);
            $sheet->setCellValue('C' . $row, $dt['no_model']);
            $sheet->setCellValue('D' . $row, $dt['area']);
            $sheet->setCellValue('E' . $row, $dt['inisial']);
            $sheet->setCellValue('F' . $row, $dt['style']);
            $sheet->setCellValue('G' . $row, $dt['warna']);
            $sheet->setCellValue('H' . $row, $dt['delivery']);
            $sheet->setCellValue('I' . $row, number_format($dt['qty_stock'], 2));
            $sheet->setCellValue('J' . $row, $dt['box_stock']);

            // Align center untuk semua data
            foreach (range('A', 'J') as $col) {
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }

            $row++;
            $no++;
            $totalQty += $dt['qty_stock'];
            $totalBox += $dt['box_stock'];
        }

        // Styling tabel (border)
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A3:J' . ($row - 1))->applyFromArray($styleArray);

        // Merge kolom A sampai H untuk tulisan TOTAL
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Isi total qty dan box
        $sheet->setCellValue('I' . $row, number_format($totalQty, 2));
        $sheet->setCellValue('J' . $row, number_format($totalBox, 2));

        // Styling (bold & center) untuk kolom I dan J
        $sheet->getStyle('I' . $row . ':J' . $row)->getFont()->setBold(true);
        foreach (range('I', 'J') as $col) {
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Border untuk seluruh baris total
        $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($styleArray);

        // Nama file
        $fileName = 'Report_Stock_Gudang.xlsx';

        // Set header untuk download file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    public function excelStatusPermintaan()
    {
        $nomodel = $this->request->getPost('cari1');
        $tgl_jalan = $this->request->getPost('cari2');

        $admin = session()->get('username');
        $role = session()->get('role');
        $dataPermintaan = $this->permintaanModel->getDataPermintaan($admin, $nomodel, $tgl_jalan);

        // Load PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set judul laporan
        $sheet->setCellValue('A1', 'Satus Permintaan Packing');
        $sheet->mergeCells('A1:R1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Header tabel
        $columns = [
            'A' => 'No',
            'B' => 'Tanggal Permintaan',
            'C' => 'Tanggal Jalan',
            'D' => 'No Model',
            'E' => 'Area',
            'F' => 'Inisial',
            'G' => 'Style',
            'H' => 'Warna',
            'I' => 'Delivery',
            'J' => 'WH',
            'K' => 'Eff(%)',
            'L' => 'Direct',
            'M' => 'Kapasitas',
            'N' => 'Qty Permintaan',
            'O' => 'Ket Packing',
            'P' => 'Qty Kirim',
            'Q' => 'Tagihan Packing',
            'R' => 'Status',
        ];

        foreach ($columns as $col => $value) {
            $sheet->setCellValue($col . '3', $value);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Isi data dari database dimulai dari baris ke-4
        $row = 4;
        $no = 1;
        $totalQtyMinta = 0;
        $totalQtyKirim = 0;
        $totalTagihan = 0;

        foreach ($dataPermintaan as $dt) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $dt['tgl_minta']);
            $sheet->setCellValue('C' . $row, $dt['tgl_jalan']);
            $sheet->setCellValue('D' . $row, $dt['no_model']);
            $sheet->setCellValue('E' . $row, $dt['area']);
            $sheet->setCellValue('F' . $row, $dt['inisial']);
            $sheet->setCellValue('G' . $row, $dt['style']);
            $sheet->setCellValue('H' . $row, $dt['warna']);
            $sheet->setCellValue('I' . $row, $dt['delivery']);
            $sheet->setCellValue('J' . $row, $dt['wh']);
            $sheet->setCellValue('K' . $row, $dt['eff']);
            $sheet->setCellValue('L' . $row, $dt['direct']);
            $sheet->setCellValue('M' . $row, $dt['kapasitas']);
            $sheet->setCellValue('N' . $row, number_format($dt['qty_minta'], 2));
            $sheet->setCellValue('O' . $row, $dt['ket_packing']);
            $sheet->setCellValue('P' . $row, number_format($dt['qty_keluar'], 2));
            $sheet->setCellValue('Q' . $row, $dt['tagihan']);
            $sheet->setCellValue('R' . $row, $dt['status']);

            // Align center untuk semua data
            foreach (range('A', 'R') as $col) {
                $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }

            $row++;
            $no++;
            $totalQtyMinta += $dt['qty_minta'];
            $totalQtyKirim += $dt['qty_keluar'];
            $totalTagihan += $dt['tagihan'];
        }

        // Styling tabel (border)
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A3:R' . ($row - 1))->applyFromArray($styleArray);

        // Merge kolom A sampai M untuk tulisan TOTAL
        $sheet->mergeCells('A' . $row . ':M' . $row);
        $sheet->setCellValue('A' . $row, 'TOTAL');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Isi total Qty Minta, Qty Kirim, Tagihan
        $sheet->setCellValue('N' . $row, number_format($totalQtyMinta, 2));
        $sheet->setCellValue('P' . $row, number_format($totalQtyKirim, 2));
        $sheet->setCellValue('Q' . $row, number_format($totalTagihan, 2));

        // Styling bold dan center
        $sheet->getStyle('N' . $row)->getFont()->setBold(true);
        $sheet->getStyle('P' . $row)->getFont()->setBold(true);
        $sheet->getStyle('Q' . $row)->getFont()->setBold(true);

        foreach (['N', 'P', 'Q'] as $col) {
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Border baris total
        $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray($styleArray);


        // Nama file
        $fileName = 'Report_Stock_Gudang.xlsx';

        // Set header untuk download file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
