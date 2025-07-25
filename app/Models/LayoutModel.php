<?php

namespace App\Models;

use CodeIgniter\Model;

class LayoutModel extends Model
{
    protected $table            = 'layout';
    protected $primaryKey       = 'jalur';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['jalur', 'jumlah_box', 'keterangan', 'gd_setting', 'status'];

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

    public function getDataJalur()
    {
        return $this->select("
            layout.jalur,
            layout.jumlah_box,
            (layout.jumlah_box - COALESCE(SUM(stock.box_stock), 0)) AS space,
            SUM(COALESCE(stock.qty_stock, 0)) AS qty_stock,
            SUM(COALESCE(stock.box_stock, 0)) AS box_stock,
            GROUP_CONCAT(DISTINCT tabel_induk.no_model SEPARATOR ', ') AS models,
            layout.keterangan
        ")
            ->join('stock',        'layout.jalur = stock.jalur',             'left')
            ->join('tabel_anak',   'stock.id_anak = tabel_anak.id_anak',      'left')
            ->join('tabel_induk',  'tabel_induk.id_induk = tabel_anak.id_induk', 'left')
            ->groupBy('layout.jalur')
            ->orderBy('layout.jalur', 'ASC')
            ->findAll();
    }
}
