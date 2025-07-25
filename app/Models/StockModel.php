<?php

namespace App\Models;

use CodeIgniter\Model;

class StockModel extends Model
{
    protected $table            = 'stock';
    protected $primaryKey       = 'id_stock';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_stock', 'id_anak', 'created_at', 'update_at', 'qty_stock', 'box_stock', 'jalur', 'gd_setting', 'ket_stock', 'admin'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getDataStock(string $jalur)
    {
        return $this->select([
            'layout.jalur',
            'tabel_induk.no_model',
            'tabel_anak.area',
            'tabel_anak.inisial',
            'tabel_anak.style',
            'tabel_anak.warna',
            'SUM(COALESCE(stock.qty_stock, 0))   AS qty_stock',
            'SUM(COALESCE(stock.box_stock, 0))  AS box_stock',
        ])
            // ->from('stock')
            ->join('layout',       'layout.jalur        = stock.jalur',      'left')
            ->join('tabel_anak',   'tabel_anak.id_anak   = stock.id_anak',   'left')
            ->join('tabel_induk',  'tabel_induk.id_induk = tabel_anak.id_induk', 'left')
            ->where('layout.jalur', $jalur)
            ->groupBy([
                'layout.jalur',
                'tabel_induk.no_model',
                'tabel_anak.area',
                'tabel_anak.inisial',
                'tabel_anak.style',
                'tabel_anak.warna',
            ])
            ->orderBy('tabel_induk.no_model', 'ASC')
            ->orderBy('tabel_anak.inisial',  'ASC')
            ->findAll();
    }


    public function getAllStock($nomodel = null)
    {
        $now = date('Y-m-d');
        $builder = $this->select('layout.jalur, stock.gd_setting, SUM(COALESCE(stock.qty_stock, 0)) AS qty_stock,(tabel_anak.qty_po_inisial - COALESCE(pengeluaran.qty_keluar, 0)) AS sisa_jatah, SUM(COALESCE(stock.box_stock, 0)) AS box_stock, tabel_induk.no_model, tabel_induk.kode_buyer, tabel_induk.smv, tabel_induk.delivery, tabel_anak.id_anak, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna, tabel_anak.qty_po_inisial, pengeluaran.qty_keluar')
            ->join('layout', 'layout.jalur = stock.jalur', 'left') // left join agar tetap menampilkan jalur walau stock kosong
            ->join('tabel_anak', 'stock.id_anak = tabel_anak.id_anak', 'left') // left join juga untuk tabel anak
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left') // left join untuk tabel induk
            ->join('pengeluaran', 'tabel_anak.id_anak = pengeluaran.id_anak', 'left') // left join untuk tabel induk
            ->where('tabel_induk.delivery >', $now);
        if (!empty($nomodel)) {
            $builder->where('tabel_induk.no_model', $nomodel);
        }
        return $builder->groupBy('tabel_induk.no_model, tabel_anak.inisial')
            ->orderBy('tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }

    public function getStockNomodel($data)
    {
        return $this->select('layout.jalur, SUM(COALESCE(stock.qty_stock, 0)) AS qty_stock, SUM(COALESCE(stock.box_stock, 0)) AS box_stock, tabel_induk.no_model, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna')
            ->join('layout', 'layout.jalur = stock.jalur', 'left') // left join agar tetap menampilkan jalur walau stock kosong
            ->join('tabel_anak', 'stock.id_anak = tabel_anak.id_anak', 'left') // left join juga untuk tabel anak
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left') // left join untuk tabel induk
            ->where('tabel_anak.id_anak', $data)
            ->groupBy('layout.jalur, tabel_anak.id_anak')
            ->orderBy('tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }
}
