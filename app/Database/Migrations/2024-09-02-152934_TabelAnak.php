<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TabelAnak extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_anak' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_induk' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'area' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'inisial' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
            ],
            'style' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'warna' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'qty_po_inisial' => [
                'type' => 'DOUBLE',
            ],
            'keterangan' => [
                'type' => 'TEXT',
            ],
            'admin' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
        ]);
        $this->forge->addKey('id_anak', true);
        $this->forge->addForeignKey('id_induk', 'tabel_induk', 'id_induk', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('tabel_anak');
    }

    public function down()
    {
        $this->forge->dropTable('tabel_anak');
    }
}
