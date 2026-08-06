<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLineToApprovalBulanan extends Migration
{
    public function up()
    {
        $this->forge->addColumn('approval_bulanan', [
            'line' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'lokasi',
            ]
        ]);
    }

    public function down()
    {
        if ($this->forge->getConnection()->fieldExists('line', 'approval_bulanan')) {
            $this->forge->dropColumn('approval_bulanan', 'line');
        }
    }
}
