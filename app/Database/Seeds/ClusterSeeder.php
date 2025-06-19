<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClusterSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'jalur'       => 'A.1.01',
                'jumlah_box' => 80,
                'keterangan' => '',
                'gd_setting' => 'GD SETTING',
                'status'     => '',
            ],
            [
                'jalur'       => 'A.1.02',
                'jumlah_box' => 80,
                'keterangan' => '',
                'gd_setting' => 'GD SETTING',
                'status'     => '',
            ],
            [
                'jalur'       => '',
                'jumlah_box' => 80,
                'keterangan' => '',
                'gd_setting' => 'GD SETTING',
                'status'     => '',
            ],
            [
                'jalur'       => '',
                'jumlah_box' => 80,
                'keterangan' => '',
                'gd_setting' => 'GD SETTING',
                'status'     => '',
            ],
            [
                'jalur'       => '',
                'jumlah_box' => 80,
                'keterangan' => '',
                'gd_setting' => 'GD SETTING',
                'status'     => '',
            ],
        ];
        // Masukkan data ke dalam tabel layout
        $this->db->table('layout')->insertBatch($data);
    }
}
