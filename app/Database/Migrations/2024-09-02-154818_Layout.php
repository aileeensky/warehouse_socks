<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Layout extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'jalur' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'jumlah_box' => [
                'type' => 'DOUBLE',
            ],
            'keterangan' => [
                'type' => 'TEXT',
            ],
            'gd_setting' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
        ]);
        $this->forge->addKey('jalur', true);
        $this->forge->createTable('layout');
    }

    public function down()
    {
        $this->forge->dropTable('layout');
    }
}
