<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Pemasukan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_masuk' => [
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
            'qty_masuk' => [
                'type' => 'DOUBLE',
            ],
            'box_masuk' => [
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
            'note' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'hapus_jalur' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'ket_masuk' => [
                'type' => 'TEXT',
            ],
            'admin' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
        ]);
        $this->forge->addKey('id_masuk', true);
        $this->forge->addForeignKey('id_anak', 'tabel_anak', 'id_anak', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('pemasukan');

        // Trigger AFTER INSERT
        $this->db->query("
            CREATE TRIGGER TAMBAH
            AFTER INSERT ON pemasukan
            FOR EACH ROW
            BEGIN
                UPDATE stock 
                SET qty_stock = qty_stock + NEW.qty_masuk, 
                    box_stock = box_stock + NEW.box_masuk 
                WHERE id_anak = NEW.id_anak 
                AND jalur = NEW.jalur;
            END
        ");

        // Trigger AFTER UPDATE
        $this->db->query("
            CREATE TRIGGER UBAH
            AFTER UPDATE ON pemasukan
            FOR EACH ROW
            BEGIN
                UPDATE stock 
                SET qty_stock = (qty_stock - OLD.qty_masuk) + NEW.qty_masuk, 
                    box_stock = (box_stock - OLD.box_masuk) + NEW.box_masuk 
                WHERE id_anak = OLD.id_anak 
                AND jalur = OLD.jalur;
            END
        ");

        // Trigger AFTER DELETE
        $this->db->query("
            CREATE TRIGGER HAPUS
            AFTER DELETE ON pemasukan
            FOR EACH ROW
            BEGIN
                UPDATE stock 
                SET qty_stock = qty_stock - OLD.qty_masuk, 
                    box_stock = box_stock - OLD.box_masuk 
                WHERE id_anak = OLD.id_anak 
                AND jalur = OLD.jalur;
            END
        ");
    }

    public function down()
    {
        // Menghapus trigger jika ada
        $this->db->query("DROP TRIGGER IF EXISTS TAMBAH");
        $this->db->query("DROP TRIGGER IF EXISTS UBAH");
        $this->db->query("DROP TRIGGER IF EXISTS HAPUS");

        $this->forge->dropTable('pemasukan');
    }
}
