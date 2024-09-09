<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Pesan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pesan' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'tgl_kirim' => [
                'type' => 'DATETIME',
            ],
            'pengirim' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'penerima' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'subjek' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'isi' => [
                'type' => 'TEXT',
            ],
            'status_baca' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'hapus_pengirim' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'hapus_penerima' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
        ]);
        $this->forge->addKey('id_pesan', true);
        $this->forge->createTable('pesan');
    }

    public function down()
    {
        $this->forge->dropTable('pesan');
    }
}
