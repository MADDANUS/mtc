<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTanggalAktifToMasterMesin extends Migration
{
    public function up()
    {
        $this->forge->addColumn('master_mesin', [
            'tanggal_aktif' => [
                'type' => 'DATE',
                'null' => true,
                'default' => null,
                'after' => 'jenis'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('master_mesin', 'tanggal_aktif');
    }
}
