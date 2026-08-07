<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BackfillLineCheckSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        $sql = "
            UPDATE transaksi_check tc
            LEFT JOIN riwayat_mesin rm 
              ON rm.id_mesin = tc.id_mesin 
              AND rm.tanggal_mulai <= LAST_DAY(COALESCE(NULLIF(tc.target_periode, ''), DATE_FORMAT(tc.waktu_mulai, '%Y-%m-01'))) 
              AND (rm.tanggal_selesai IS NULL OR rm.tanggal_selesai >= LAST_DAY(COALESCE(NULLIF(tc.target_periode, ''), DATE_FORMAT(tc.waktu_mulai, '%Y-%m-01'))))
            LEFT JOIN master_mesin mm
              ON mm.id_mesin = tc.id_mesin
            SET tc.line_check = COALESCE(rm.line, mm.line)
            WHERE tc.line_check IS NULL;
        ";

        $db->query($sql);
        
        echo "Backfill line_check completed successfully.\n";
    }
}
