<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddApproverSnapshotsToTransaksiCheck extends Migration
{
    public function up()
    {
        $fields = [
            'ss_approval_l1_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'ss_approval_l2_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'ss_approved_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
        ];

        $this->forge->addColumn('transaksi_check', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transaksi_check', 'ss_approval_l1_name');
        $this->forge->dropColumn('transaksi_check', 'ss_approval_l2_name');
        $this->forge->dropColumn('transaksi_check', 'ss_approved_name');
    }
}
