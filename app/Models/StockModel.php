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

    public function getDataStock($jalur)
    {
        return $this->select('layout.jalur, SUM(COALESCE(stock.qty_stock, 0)) AS qty_stock, SUM(COALESCE(stock.box_stock, 0)) AS box_stock, tabel_induk.no_model, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna')
            ->join('layout', 'layout.jalur = stock.jalur', 'left') // left join agar tetap menampilkan jalur walau stock kosong
            ->join('tabel_anak', 'stock.id_anak = tabel_anak.id_anak', 'left') // left join juga untuk tabel anak
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left') // left join untuk tabel induk
            ->where('layout.jalur', $jalur)
            ->groupBy('tabel_anak.inisial')
            ->orderBy('tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }
}
