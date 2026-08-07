<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLineCheckToTransaksiCheck extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transaksi_check', [
            'line_check' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'lokasi_check', // taruh setelah lokasi_check
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transaksi_check', 'line_check');
    }
}
