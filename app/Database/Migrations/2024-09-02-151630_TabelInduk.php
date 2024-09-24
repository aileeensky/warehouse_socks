<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TabelInduk extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_induk' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'no_order' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'no_model' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'kode_buyer' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'smv' => [
                'type' => 'DOUBLE',
            ],
            'qty_po_model' => [
                'type' => 'DOUBLE',
            ],
            'ratarata_produksi' => [
                'type' => 'DOUBLE',
            ],
            'delivery' => [
                'type' => 'DATE',
            ],
            'keterangan' => [
                'type' => 'TEXT',
            ],
            'admin' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
        ]);
        $this->forge->addKey('id_induk', true);
        $this->forge->createTable('tabel_induk');
    }

    public function down()
    {
        $this->forge->dropTable('tabel_induk');
    }
}
