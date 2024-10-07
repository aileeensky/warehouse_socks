<?php

namespace App\Models;

use CodeIgniter\Model;

class TabelIndukModel extends Model
{
    protected $table            = 'tabel_induk';
    protected $primaryKey       = 'id_induk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_induk', 'no_order', 'no_model', 'kode_buyer', 'smv', 'qty_po_model', 'ratarata_produksi', 'delivery', 'keterangan', 'admin'];

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

    public function getIdInduk($nomodel)
    {
        return $this->select('id_induk')
            ->where('no_model', $nomodel)
            ->first();
    }

    public function selectNomodel()
    {
        return $this->select('id_induk, no_model')
            ->findAll();
    }
}
