<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AuditLogLaporan extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_log' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kategori_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'aksi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'no_mesin' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'waktu_eksekusi' => [
                'type' => 'DATETIME',
            ],
            'dieksekusi_oleh' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'alasan' => [
                'type' => 'TEXT',
            ],
            'detail_perubahan' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id_log', true);
        $this->forge->createTable('log_audit_laporan', true);
    }

    public function down()
    {
        $this->forge->dropTable('log_audit_laporan', true);
    }
}
