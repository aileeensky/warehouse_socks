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

    public function getData($admin, $nomodel = null, $tgl_jalan = null)
    {
        $builder = $this->select('tabel_induk.no_model, tabel_induk.delivery, tabel_anak.id_anak, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna, permintaan.id_minta, DATE(permintaan.created_at) as tgl_minta, permintaan.tgl_jalan, permintaan.wh, permintaan.eff, permintaan.direct, permintaan.kapasitas, permintaan.qty_minta, permintaan.ket_packing, permintaan.gd_setting')
            ->join('tabel_anak', 'permintaan.id_anak = tabel_anak.id_anak', 'left') // left join juga untuk tabel anak
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left') // left join untuk tabel induk
            ->where('permintaan.area_packing',  $admin)
            ->where('permintaan.status', '');
        if (!empty($nomodel)) {
            $builder->where('tabel_induk.no_model', $nomodel);
        }

        if (!empty($tgl_jalan)) {
            $builder->where('permintaan.tgl_jalan', $tgl_jalan);
        }
        return $builder->groupBy('permintaan.id_minta')
            ->orderBy('permintaan.tgl_jalan, tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }

    public function getDataPermintaan($admin, $nomodel = null, $tgl_jalan = null)
    {
        $builder = $this->select('tabel_induk.no_model, tabel_induk.delivery, tabel_induk.kode_buyer, tabel_anak.id_anak, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna, permintaan.id_minta, DATE(permintaan.created_at) as tgl_minta, permintaan.tgl_jalan, permintaan.wh, permintaan.eff, permintaan.direct, permintaan.kapasitas, permintaan.qty_minta, permintaan.ket_packing, permintaan.gd_setting, permintaan.status, IFNULL(pengeluaran.qty_keluar, 0) AS qty_keluar, SUM(permintaan.qty_minta - IFNULL(pengeluaran.qty_keluar, 0)) AS tagihan')
            ->join('tabel_anak', 'permintaan.id_anak = tabel_anak.id_anak', 'left') // left join juga untuk tabel anak
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left') // left join untuk tabel induk
            ->join('pengeluaran', 'permintaan.id_minta = pengeluaran.id_minta', 'left') // left join untuk tabel induk
            ->where('permintaan.area_packing',  $admin)
            ->where('permintaan.status <>', '');
        if (!empty($nomodel)) {
            $builder->where('tabel_induk.no_model', $nomodel);
        }

        if (!empty($tgl_jalan)) {
            $builder->where('permintaan.tgl_jalan', $tgl_jalan);
        }
        return $builder->groupBy('permintaan.id_minta')
            ->orderBy('permintaan.tgl_jalan, tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }

    public function getDataMinta($nomodel = null, $tgl_jalan = null)
    {
        $builder = $this->select('tabel_induk.no_model, tabel_induk.delivery, tabel_induk.kode_buyer, tabel_anak.id_anak, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna, tabel_anak.qty_po_inisial, permintaan.id_minta, DATE(permintaan.created_at) as tgl_minta, permintaan.area_packing, permintaan.tgl_jalan, permintaan.wh, permintaan.eff, permintaan.direct, permintaan.kapasitas, permintaan.qty_minta, permintaan.ket_packing, permintaan.gd_setting, permintaan.status, IFNULL(SUM(pengeluaran.qty_keluar), 0) AS qty_keluar, (permintaan.qty_minta - IFNULL(SUM(pengeluaran.qty_keluar), 0)) AS tagihan')
            ->join('tabel_anak', 'permintaan.id_anak = tabel_anak.id_anak', 'left')
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left')
            ->join('pengeluaran', 'permintaan.id_minta = pengeluaran.id_minta', 'left')
            ->where('permintaan.status', 'ON PROCESS');

        if (!empty($nomodel)) {
            $builder->where('tabel_induk.no_model', $nomodel);
        }

        if (!empty($tgl_jalan)) {
            $builder->where('permintaan.tgl_jalan', $tgl_jalan);
        }

        return $builder->groupBy('permintaan.id_minta')
            ->orderBy('permintaan.tgl_jalan, tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }

    public function getDataTerkirim($nomodel = null, $tgl_jalan = null)
    {
        $builder = $this->select('tabel_induk.no_model, tabel_induk.delivery, tabel_induk.kode_buyer, tabel_anak.id_anak, tabel_anak.area, tabel_anak.inisial, tabel_anak.style, tabel_anak.warna, tabel_anak.qty_po_inisial, permintaan.id_minta, DATE(permintaan.created_at) as tgl_minta, permintaan.area_packing, permintaan.tgl_jalan, permintaan.wh, permintaan.eff, permintaan.direct, permintaan.kapasitas, permintaan.qty_minta, permintaan.ket_packing, permintaan.gd_setting, permintaan.status, IFNULL(SUM(pengeluaran.qty_keluar), 0) AS qty_keluar, (permintaan.qty_minta - SUM(COALESCE(pengeluaran.qty_keluar, 0))) AS tagihan, DATE(pengeluaran.created_at) AS tgl_keluar')
            ->join('tabel_anak', 'permintaan.id_anak = tabel_anak.id_anak', 'left') // left join juga untuk tabel anak
            ->join('tabel_induk', 'tabel_induk.id_induk = tabel_anak.id_induk', 'left') // left join untuk tabel induk
            ->join('pengeluaran', 'permintaan.id_minta = pengeluaran.id_minta', 'left') // left join untuk tabel induk
            ->where('permintaan.status', 'DONE');

        if (!empty($nomodel)) {
            $builder->where('tabel_induk.no_model', $nomodel);
        }

        if (!empty($tgl_jalan)) {
            $builder->where('permintaan.tgl_jalan', $tgl_jalan);
        }

        return $builder->groupBy('permintaan.id_minta')
            ->orderBy('permintaan.tgl_jalan, tabel_induk.no_model, tabel_anak.inisial', 'ASC')
            ->findAll();
    }

    public function getAreaPacking()
    {
        return $this->select('area_packing')
            ->where('area_packing !=', '')
            ->groupBy('area_packing')
            ->findAll();
    }
}
