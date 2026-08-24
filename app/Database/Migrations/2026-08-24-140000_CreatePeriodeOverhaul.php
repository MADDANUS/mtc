<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePeriodeOverhaul extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'plant' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'tanggal_selesai' => [
                'type'    => 'DATE',
                'null'    => true,
                'default' => null,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Aktif', 'Selesai'],
                'default'    => 'Aktif',
            ],
            'diakhiri_oleh' => [
                'type'    => 'INT',
                'null'    => true,
                'default' => null,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['plant', 'status']);
        $this->forge->createTable('periode_overhaul', true);

        $today = date('Y-m-d');
        $now   = date('Y-m-d H:i:s');

        $this->db->table('periode_overhaul')->insertBatch([
            ['plant' => 'Plant 1', 'tanggal_mulai' => $today, 'tanggal_selesai' => null, 'status' => 'Aktif', 'diakhiri_oleh' => null, 'created_at' => $now],
            ['plant' => 'Plant 2', 'tanggal_mulai' => $today, 'tanggal_selesai' => null, 'status' => 'Aktif', 'diakhiri_oleh' => null, 'created_at' => $now],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('periode_overhaul', true);
    }
}
