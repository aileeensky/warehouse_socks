<?php

namespace App\Models;

use CodeIgniter\Model;

class TabelAnakModel extends Model
{
    protected $table            = 'tabel_anak';
    protected $primaryKey       = 'id_anak';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_anak', 'id_induk', 'waktu_input', 'area', 'inisial', 'style', 'warna', 'qty_po_inisial', 'keterangan', 'admin'];

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

    public function getSelect()
    {
        return $this->select('tabel_induk.no_order, tabel_anak.waktu_input, tabel_anak.area, tabel_induk.no_model, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna, tabel_induk.delivery, tabel_anak.qty_po_inisial')
            ->join('tabel_induk', 'tabel_anak.id_induk = tabel_induk.id_induk', 'left')
            ->where('tabel_induk.no_model IS NOT NULL')
            ->orderBy('tabel_anak.waktu_input', 'DESC')
            ->findAll();
    }

    public function getData($id_induk)
    {
        return $this->select('area, inisial, style')
            ->join('tabel_induk', 'tabel_anak.id_induk = tabel_induk.id_induk', 'left')
            ->where('tabel_induk.id_induk', $id_induk)
            ->orderBy('tabel_anak.inisial', 'ASC')
            ->findAll();
    }
}
