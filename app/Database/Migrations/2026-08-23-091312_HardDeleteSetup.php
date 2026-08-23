<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HardDeleteSetup extends Migration
{
    public function up()
    {
        // 1. ADD ss_no_mesin columns
        $this->forge->addColumn('transaksi_check', [
            'ss_no_mesin' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ]
        ]);
        $this->forge->addColumn('laporan_abnormal', [
            'ss_no_mesin' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ]
        ]);
        $this->forge->addColumn('riwayat_mesin', [
            'ss_no_mesin' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ]
        ]);

        // 2. COPY DATA from master_mesin to ss_no_mesin
        $this->db->query("UPDATE transaksi_check tc JOIN master_mesin mm ON tc.id_mesin = mm.id_mesin SET tc.ss_no_mesin = mm.no_mesin");
        $this->db->query("UPDATE laporan_abnormal la JOIN master_mesin mm ON la.id_mesin = mm.id_mesin SET la.ss_no_mesin = mm.no_mesin");
        $this->db->query("UPDATE riwayat_mesin rm JOIN master_mesin mm ON rm.id_mesin = mm.id_mesin SET rm.ss_no_mesin = mm.no_mesin");

        // 3. DROP old foreign keys
        // Need to drop foreign keys manually using SQL because CodeIgniter forge->dropForeignKey is sometimes buggy with naming
        $this->db->query("ALTER TABLE transaksi_check DROP FOREIGN KEY transaksi_check_id_user_foreign");
        $this->db->query("ALTER TABLE transaksi_check DROP FOREIGN KEY transaksi_check_id_mesin_foreign");
        $this->db->query("ALTER TABLE laporan_abnormal DROP FOREIGN KEY laporan_abnormal_id_mesin_foreign");
        // Not dropping laporan_abnormal_id_transaksi_foreign or detail_foreign because we want Cascade on Transactions!
        
        // 4. ALTER COLUMNS TO BE NULLABLE
        $this->db->query("ALTER TABLE transaksi_check MODIFY id_user INT(11) UNSIGNED NULL");
        $this->db->query("ALTER TABLE transaksi_check MODIFY id_mesin INT(11) UNSIGNED NULL");
        $this->db->query("ALTER TABLE laporan_abnormal MODIFY id_mesin INT(11) UNSIGNED NULL");

        // 5. RECREATE FOREIGN KEYS WITH SET NULL
        $this->db->query("ALTER TABLE transaksi_check ADD CONSTRAINT transaksi_check_id_user_foreign FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE");
        $this->db->query("ALTER TABLE transaksi_check ADD CONSTRAINT transaksi_check_id_mesin_foreign FOREIGN KEY (id_mesin) REFERENCES master_mesin(id_mesin) ON DELETE SET NULL ON UPDATE CASCADE");
        $this->db->query("ALTER TABLE laporan_abnormal ADD CONSTRAINT laporan_abnormal_id_mesin_foreign FOREIGN KEY (id_mesin) REFERENCES master_mesin(id_mesin) ON DELETE SET NULL ON UPDATE CASCADE");

        // 6. CREATE log_hapus_mesin
        $this->db->query("
            CREATE TABLE `log_hapus_mesin` (
                `id_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `id_mesin` int(11) unsigned DEFAULT NULL,
                `no_mesin` varchar(50) NOT NULL,
                `type_mesin` varchar(100) NOT NULL,
                `jenis` varchar(100) DEFAULT NULL,
                `serial_nomor` varchar(100) NOT NULL,
                `plant` varchar(50) DEFAULT NULL,
                `bar_feeder_type` varchar(100) DEFAULT NULL,
                `departemen` varchar(50) NOT NULL,
                `line` varchar(50) DEFAULT NULL,
                `waktu_dihapus` datetime NOT NULL,
                `dihapus_oleh` varchar(150) NOT NULL,
                `alasan_dihapus` text NOT NULL,
                PRIMARY KEY (`id_log`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        // 7. CREATE log_hapus_user
        $this->db->query("
            CREATE TABLE `log_hapus_user` (
                `id_log` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `id_user` int(11) unsigned DEFAULT NULL,
                `nama` varchar(100) NOT NULL,
                `username` varchar(50) NOT NULL,
                `role` varchar(50) DEFAULT NULL,
                `plant` varchar(50) DEFAULT NULL,
                `departemen` varchar(50) DEFAULT NULL,
                `line` varchar(50) DEFAULT NULL,
                `waktu_dihapus` datetime NOT NULL,
                `dihapus_oleh` varchar(150) NOT NULL,
                `alasan_dihapus` text NOT NULL,
                PRIMARY KEY (`id_log`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
    }

    public function down()
    {
        // Down method omitted for brevity, usually not run in this context
    }
}
