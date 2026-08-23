<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RenamePlanToPlant extends Migration
{
    protected $tables = [
        'approval_bulanan',
        'ceklis_kontrol',
        'jadwal_preventive',
        'laporan_abnormal',
        'master_line',
        'master_mesin',
        'master_parameter_check',
        'riwayat_mesin',
        'transaksi_check',
        'users'
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            $this->db->query("ALTER TABLE `{$table}` CHANGE `plan` `plant` VARCHAR(50) DEFAULT NULL");
        }
    }

    public function down()
    {
        foreach ($this->tables as $table) {
            $this->db->query("ALTER TABLE `{$table}` CHANGE `plant` `plan` VARCHAR(50) DEFAULT NULL");
        }
    }
}

