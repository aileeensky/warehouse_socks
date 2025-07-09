<?php

namespace App\Models;

use CodeIgniter\Model;

class PemasukanModel extends Model
{
    protected $table            = 'pemasukan';
    protected $primaryKey       = 'id_masuk';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_masuk', 'id_anak', 'created_at', 'updated_at', 'qty_masuk', 'box_masuk', 'jalur', 'gd_setting', 'note', 'hapus_jalur', 'ket_masuk', 'admin'];

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

    public function getData($nomodel = null, $tgl_masuk = null)
    {
        $builder = $this->select('pemasukan.*, tabel_induk.no_model, tabel_induk.kode_buyer, tabel_anak.area, tabel_anak.inisial, tabel_anak.style')
            ->join('tabel_anak', 'tabel_anak.id_anak = pemasukan.id_anak', 'left')
            ->join('tabel_induk', 'tabel_anak.id_induk = tabel_induk.id_induk', 'left');

        if (!empty($nomodel)) {
            $builder->where('tabel_induk.no_model', $nomodel);
        }

        if (!empty($tgl_masuk)) {
            $builder->where('DATE(pemasukan.created_at)', $tgl_masuk);
        }

        return $builder->groupBy('pemasukan.id_masuk')
            ->orderBy('pemasukan.created_at', 'DESC')
            ->orderBy('tabel_induk.no_model', 'ASC')
            ->orderBy('tabel_anak.inisial', 'ASC')
            ->orderBy('tabel_anak.style', 'ASC')
            ->findAll();
    }
}
