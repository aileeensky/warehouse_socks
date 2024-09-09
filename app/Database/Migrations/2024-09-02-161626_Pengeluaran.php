<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Pengeluaran extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_keluar' => [
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
            'id_minta' => [
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
            'qty_keluar' => [
                'type' => 'DOUBLE',
            ],
            'box_keluar' => [
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
            'ket_keluar' => [
                'type' => 'TEXT',
            ],
            'hapus_jalur' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
            ],
            'admin' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
            ],
        ]);
        $this->forge->addKey('id_keluar', true);
        $this->forge->addForeignKey('id_anak', 'tabel_anak', 'id_anak', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('jalur', 'layout', 'jalur', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('pengeluaran');

        // Trigger AFTER INSERT
        $this->db->query("
            CREATE TRIGGER INPUT
            AFTER INSERT ON pengeluaran
            FOR EACH ROW
            BEGIN
                UPDATE stock 
                SET qty_stock = qty_stock - NEW.qty_keluar, 
                    box_stock = box_stock - NEW.box_keluar 
                WHERE id_anak = NEW.id_anak 
                AND jalur = NEW.jalur;
            END
        ");

        // Trigger AFTER UPDATE
        $this->db->query("
            CREATE TRIGGER EDIT
            AFTER UPDATE ON pengeluaran
            FOR EACH ROW
            BEGIN
                UPDATE stock 
                SET qty_stock = (qty_stock + OLD.qty_keluar) - NEW.qty_keluar, 
                    box_stock = (box_stock + OLD.box_keluar) - NEW.box_keluar 
                WHERE id_anak = OLD.id_anak 
                AND jalur = OLD.jalur;
            END
        ");

        // Trigger AFTER DELETE
        $this->db->query("
            CREATE TRIGGER HAPUS_PENGELUARAN
            AFTER DELETE ON pengeluaran
            FOR EACH ROW
            BEGIN
                UPDATE stock 
                SET qty_stock = qty_stock + OLD.qty_keluar, 
                    box_stock = box_stock + OLD.box_keluar 
                WHERE id_anak = OLD.id_anak 
                AND jalur = OLD.jalur;
            END
        ");
    }

    public function down()
    {
        // Menghapus trigger jika ada
        $this->db->query("DROP TRIGGER IF EXISTS INPUT");
        $this->db->query("DROP TRIGGER IF EXISTS EDIT");
        $this->db->query("DROP TRIGGER IF EXISTS DELETE");

        $this->forge->dropTable('pengeluaran');
    }
}
