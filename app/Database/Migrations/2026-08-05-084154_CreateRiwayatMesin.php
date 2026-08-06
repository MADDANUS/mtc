<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRiwayatMesin extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_riwayat' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'id_mesin' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'lokasi' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'line' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'tanggal_mulai' => [
                'type' => 'DATE',
            ],
            'tanggal_selesai' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id_riwayat', true);
        // We do not set a strict foreign key to allow flexibility, but we index it.
        $this->forge->addKey('id_mesin');
        $this->forge->createTable('riwayat_mesin');

        // Drop the `tanggal_aktif` from `master_mesin` since we don't need it anymore
        if ($this->forge->getConnection()->fieldExists('tanggal_aktif', 'master_mesin')) {
            $this->forge->dropColumn('master_mesin', 'tanggal_aktif');
        }
    }

    public function down()
    {
        $this->forge->dropTable('riwayat_mesin');

        // If rollback, we re-add the tanggal_aktif column just in case
        if (!$this->forge->getConnection()->fieldExists('tanggal_aktif', 'master_mesin')) {
            $this->forge->addColumn('master_mesin', [
                'tanggal_aktif' => [
                    'type' => 'DATE',
                    'null' => true,
                ]
            ]);
        }
    }
}
