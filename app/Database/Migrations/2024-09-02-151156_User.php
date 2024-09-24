<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class User extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_user' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'nama' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
            'password' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'role' => [
                'type' => 'ENUM',
                'constraint' => ['user', 'monitoring', 'gudang', 'packing'],
                'default' => 'user',
            ],
        ]);
        $this->forge->addKey('id_user', true);
        $this->forge->createTable('user');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
