<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username' => 'gudang',
                'nama' => 'GUDANG',
                'password' => 'gudang',
                'role' => 'gudang'
            ],
            [
                'username' => 'packing',
                'nama' => 'PACKING',
                'password' => 'packing',
                'role' => 'packing'
            ],
            [
                'username' => 'mon',
                'nama' => 'MONITORING',
                'password' => 'mon',
                'role' => 'monitoring'
            ],
            [
                'username' => 'manajemengd',
                'nama' => 'MANAJEMEN GD',
                'password' => 'manajemengd',
                'role' => 'user'
            ],
        ];

        // Masukkan data ke dalam tabel layout
        $this->db->table('user')->insertBatch($data);
    }
}
