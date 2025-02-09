<?php

namespace App\Models;

use CodeIgniter\Model;

class PengeluaranModel extends Model
{
    protected $table            = 'pengeluaran';
    protected $primaryKey       = 'id_keluar';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_keluar', 'id_anak', 'id_minta', 'created_at', 'updated_at', 'qty_keluar', 'box_keluar', 'jalur', 'gd_setting', 'ket_keluar', 'hapus_jalur', 'admin'];

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

    public function getData()
    {
        return $this->select('tabel_induk.no_model, tabel_induk.kode_buyer, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, DATE(pengeluaran.created_at) as tgl_keluar, pengeluaran.qty_keluar, pengeluaran.box_keluar, pengeluaran.ket_keluar')
            ->join('tabel_anak', 'pengeluaran.id_anak = tabel_anak.id_anak', 'left') // left join juga untuk tabel anak
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left') // left join untuk tabel induk
            ->groupBy('pengeluaran.id_keluar')
            ->orderBy('pengeluaran.created_at, tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }
}
