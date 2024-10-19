<?php

namespace App\Models;

use CodeIgniter\Model;

class PermintaanModel extends Model
{
    protected $table            = 'permintaan';
    protected $primaryKey       = 'id_minta';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['id_minta', 'id_anak', 'area_packing', 'gd_setting', 'created_at', 'updated_at', 'tgl_jalan', 'wh', 'eff', 'direct', 'kapasitas', 'qty_minta', 'ket_packing', 'ket_gd', 'status', 'deffect', 'admin'];

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

    public function getData($admin)
    {
        return $this->select('tabel_induk.no_model, tabel_induk.delivery, tabel_anak.id_anak, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna, permintaan.id_minta, DATE(permintaan.created_at) as tgl_minta, permintaan.tgl_jalan, permintaan.wh, permintaan.eff, permintaan.direct, permintaan.kapasitas, permintaan.qty_minta, permintaan.ket_packing, permintaan.gd_setting')
            ->join('tabel_anak', 'permintaan.id_anak = tabel_anak.id_anak', 'left') // left join juga untuk tabel anak
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left') // left join untuk tabel induk
            ->where('permintaan.area_packing',  $admin)
            ->where('permintaan.status', '')
            ->groupBy('permintaan.id_minta')
            ->orderBy('permintaan.tgl_jalan, tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }

    public function getDataPermintaan($admin)
    {
        return $this->select('tabel_induk.no_model, tabel_induk.delivery, tabel_induk.kode_buyer, tabel_anak.id_anak, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna, permintaan.id_minta, DATE(permintaan.created_at) as tgl_minta, permintaan.tgl_jalan, permintaan.wh, permintaan.eff, permintaan.direct, permintaan.kapasitas, permintaan.qty_minta, permintaan.ket_packing, permintaan.gd_setting, permintaan.status, pengeluaran.qty_keluar')
            ->join('tabel_anak', 'permintaan.id_anak = tabel_anak.id_anak', 'left') // left join juga untuk tabel anak
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left') // left join untuk tabel induk
            ->join('pengeluaran', 'permintaan.id_minta = pengeluaran.id_minta', 'left') // left join untuk tabel induk
            ->where('permintaan.area_packing',  $admin)
            ->where('permintaan.status <>', '')
            ->groupBy('permintaan.id_minta')
            ->orderBy('permintaan.tgl_jalan, tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }

    public function getDataMinta()
    {
        return $this->select('tabel_induk.no_model, tabel_induk.delivery, tabel_induk.kode_buyer, tabel_anak.id_anak, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna, tabel_anak.qty_po_inisial, permintaan.id_minta, DATE(permintaan.created_at) as tgl_minta, permintaan.area_packing, permintaan.tgl_jalan, permintaan.wh, permintaan.eff, permintaan.direct, permintaan.kapasitas, permintaan.qty_minta, permintaan.ket_packing, permintaan.gd_setting, permintaan.status, pengeluaran.qty_keluar')
            ->join('tabel_anak', 'permintaan.id_anak = tabel_anak.id_anak', 'left') // left join juga untuk tabel anak
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left') // left join untuk tabel induk
            ->join('pengeluaran', 'permintaan.id_minta = pengeluaran.id_minta', 'left') // left join untuk tabel induk
            ->where('permintaan.status', 'ON PROCCESS')
            ->groupBy('permintaan.id_minta')
            ->orderBy('permintaan.tgl_jalan, tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }
}
