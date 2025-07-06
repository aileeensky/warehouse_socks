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
            $sheet->setCellValue('G' . $row, $data['no_model']);
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
}
