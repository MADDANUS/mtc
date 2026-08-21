<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlanAndRenameLokasiToDepartemen extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        
        // 1. Rename 'lokasi' to 'departemen' and add 'plan'
        
        // master_mesin
        if ($db->fieldExists('lokasi', 'master_mesin')) {
            $this->forge->modifyColumn('master_mesin', [
                'lokasi' => [
                    'name' => 'departemen',
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false
                ]
            ]);
        }
        if (!$db->fieldExists('plan', 'master_mesin')) {
            $this->forge->addColumn('master_mesin', [
                'plan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'Plan 1',
                    'null' => false,
                    'after' => 'serial_nomor'
                ]
            ]);
        }

        // master_parameter_check
        if ($db->fieldExists('lokasi', 'master_parameter_check')) {
            $this->forge->modifyColumn('master_parameter_check', [
                'lokasi' => [
                    'name' => 'departemen',
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false
                ]
            ]);
        }
        if (!$db->fieldExists('plan', 'master_parameter_check')) {
            $this->forge->addColumn('master_parameter_check', [
                'plan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'Plan 1',
                    'null' => false,
                    'after' => 'jenis_check'
                ]
            ]);
        }

        // master_line
        if ($db->fieldExists('lokasi', 'master_line')) {
            $this->forge->modifyColumn('master_line', [
                'lokasi' => [
                    'name' => 'departemen',
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false
                ]
            ]);
        }
        if (!$db->fieldExists('plan', 'master_line')) {
            $this->forge->addColumn('master_line', [
                'plan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'Plan 1',
                    'null' => false,
                    'after' => 'id_line'
                ]
            ]);
            
            // Set existing lines to Plan 1
            $db->query("UPDATE master_line SET plan = 'Plan 1'");
        }

        // users
        if ($db->fieldExists('lokasi', 'users')) {
            $this->forge->modifyColumn('users', [
                'lokasi' => [
                    'name' => 'departemen',
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true
                ]
            ]);
        }
        if (!$db->fieldExists('plan', 'users')) {
            $this->forge->addColumn('users', [
                'plan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 100, // Can be multi-select like "Plan 1,Plan 2"
                    'null' => true,
                    'after' => 'role'
                ]
            ]);
            // Update existing users to have Plan 1 if they have a departemen
            $db->query("UPDATE users SET plan = 'Plan 1' WHERE departemen IS NOT NULL AND departemen != ''");
        }

        // jadwal_preventive
        if ($db->fieldExists('lokasi', 'jadwal_preventive')) {
            $this->forge->modifyColumn('jadwal_preventive', [
                'lokasi' => [
                    'name' => 'departemen',
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false
                ]
            ]);
        }
        if (!$db->fieldExists('plan', 'jadwal_preventive')) {
            $this->forge->addColumn('jadwal_preventive', [
                'plan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'Plan 1',
                    'null' => false,
                    'after' => 'id_jadwal'
                ]
            ]);
        }

        // approval_bulanan
        if ($db->fieldExists('lokasi', 'approval_bulanan')) {
            $this->forge->modifyColumn('approval_bulanan', [
                'lokasi' => [
                    'name' => 'departemen',
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false
                ]
            ]);
        }
        if (!$db->fieldExists('plan', 'approval_bulanan')) {
            $this->forge->addColumn('approval_bulanan', [
                'plan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'Plan 1',
                    'null' => false,
                    'after' => 'id_approval'
                ]
            ]);
        }

        // riwayat_mesin
        if ($db->fieldExists('lokasi', 'riwayat_mesin')) {
            $this->forge->modifyColumn('riwayat_mesin', [
                'lokasi' => [
                    'name' => 'departemen',
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => false
                ]
            ]);
        }
        if (!$db->fieldExists('plan', 'riwayat_mesin')) {
            $this->forge->addColumn('riwayat_mesin', [
                'plan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'Plan 1',
                    'null' => false,
                    'after' => 'id_riwayat'
                ]
            ]);
        }

        // transaksi_check
        if ($db->fieldExists('lokasi_check', 'transaksi_check')) {
            $this->forge->modifyColumn('transaksi_check', [
                'lokasi_check' => [
                    'name' => 'departemen_check',
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true
                ]
            ]);
        }
        if (!$db->fieldExists('plan', 'transaksi_check')) {
            $this->forge->addColumn('transaksi_check', [
                'plan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'Plan 1',
                    'null' => false,
                    'after' => 'id_mesin'
                ]
            ]);
        }

        // laporan_abnormal (lokasi doesn't exist here usually, but let's just check plan)
        if (!$db->fieldExists('plan', 'laporan_abnormal')) {
            $this->forge->addColumn('laporan_abnormal', [
                'plan' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'Plan 1',
                    'null' => false,
                    'after' => 'id_mesin'
                ]
            ]);
        }

        // ceklis_kontrol (if exists)
        if ($db->tableExists('ceklis_kontrol')) {
            if (!$db->fieldExists('plan', 'ceklis_kontrol')) {
                $this->forge->addColumn('ceklis_kontrol', [
                    'plan' => [
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'default' => 'Plan 1',
                        'null' => false,
                        'after' => 'id_mesin'
                    ]
                ]);
            }
        }

        // 2. Insert dummy lines for Plan 2
        $db->table('master_line')->insertBatch([
            [
                'plan'       => 'Plan 2',
                'departemen' => 'MFG 1',
                'nama_line'  => 'Line 1'
            ],
            [
                'plan'       => 'Plan 2',
                'departemen' => 'MFG 2',
                'nama_line'  => 'Line 1'
            ]
        ]);
    }

    public function down()
    {
        // Reverting this massive change is risky, but for completeness:
        $db = \Config\Database::connect();

        $tables = [
            'master_mesin', 'master_parameter_check', 'master_line', 
            'users', 'jadwal_preventive', 'approval_bulanan', 
            'riwayat_mesin', 'laporan_abnormal', 'ceklis_kontrol'
        ];

        foreach ($tables as $table) {
            if ($db->tableExists($table) && $db->fieldExists('plan', $table)) {
                $this->forge->dropColumn($table, 'plan');
            }
            if ($db->tableExists($table) && $db->fieldExists('departemen', $table)) {
                $this->forge->modifyColumn($table, [
                    'departemen' => [
                        'name' => 'lokasi',
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'null' => true
                    ]
                ]);
            }
        }

        if ($db->tableExists('transaksi_check')) {
            if ($db->fieldExists('plan', 'transaksi_check')) {
                $this->forge->dropColumn('transaksi_check', 'plan');
            }
            if ($db->fieldExists('departemen_check', 'transaksi_check')) {
                $this->forge->modifyColumn('transaksi_check', [
                    'departemen_check' => [
                        'name' => 'lokasi_check',
                        'type' => 'VARCHAR',
                        'constraint' => 50,
                        'null' => true
                    ]
                ]);
            }
        }
    }
}
