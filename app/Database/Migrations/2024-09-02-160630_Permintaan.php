<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Permintaan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_minta' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_anak' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'area_packing' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'gd_setting' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
            'tgl_jalan' => [
                'type' => 'DATE',
            ],
            'wh' => [
                'type' => 'DOUBLE',
            ],
            'eff' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'direct' => [
                'type' => 'DOUBLE',
            ],
            'kapasitas' => [
                'type' => 'DOUBLE',
            ],
            'qty_minta' => [
                'type' => 'DOUBLE',
            ],
            'ket_packing' => [
                'type' => 'TEXT',
            ],
            'ket_gd' => [
                'type' => 'TEXT',
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'deffect' => [
                'type' => 'DOUBLE',
            ],
            'admin' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
        ]);
        $this->forge->addKey('id_minta', true);
        $this->forge->addForeignKey('id_anak', 'tabel_anak', 'id_anak', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('permintaan');
    }

    public function down()
    {
        $this->forge->dropTable('permintaan');
    }
}
