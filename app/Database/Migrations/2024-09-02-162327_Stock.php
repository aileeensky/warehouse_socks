<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Stock extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_stock' => [
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
            'created_at' => [
                'type' => 'DATETIME',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
            ],
            'stock_awal' => [
                'type' => 'DOUBLE',
            ],
            'box_awal' => [
                'type' => 'DOUBLE',
            ],
            'qty_stock' => [
                'type' => 'DOUBLE',
            ],
            'box_stock' => [
                'type' => 'DOUBLE',
            ],
            'jalur' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'gd_setting' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'ket_stock' => [
                'type' => 'TEXT',
            ],
            'admin' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
        ]);
        $this->forge->addKey('id_stock', true);
        $this->forge->addForeignKey('id_anak', 'tabel_anak', 'id_anak', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('jalur', 'layout', 'jalur', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('stock');
    }

    public function down()
    {
        $this->forge->dropTable('stock');
    }
}
