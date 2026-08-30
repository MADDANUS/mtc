<?php

namespace App\Models;

use CodeIgniter\Model;

class RiwayatMesinModel extends Model
{
    protected $table         = 'riwayat_mesin';
    protected $primaryKey    = 'id_riwayat';
    protected $allowedFields = [
        'ss_no_mesin',
        'id_mesin',
        'departemen',
        'plant',
        'line',
        'tanggal_mulai',
        'tanggal_selesai'
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    public function getTotalMesinPerLineHistorical(string $bulanTahun): array
    {
        // $bulanTahun format is 'YYYY-MM'
        $dateStr = $bulanTahun . '-01';
        
        $sql = "
            SELECT COALESCE(riwayat_mesin.plant, master_mesin.plant, 'Plant 1') as plant, 
                   COALESCE(riwayat_mesin.departemen, master_mesin.departemen) as departemen, 
                   COALESCE(riwayat_mesin.line, master_mesin.line) as line, 
                   COUNT(DISTINCT master_mesin.id_mesin) as total,
                   COUNT(DISTINCT CASE WHEN master_mesin.jenis = 'CAM' THEN master_mesin.id_mesin END) as total_cam
            FROM master_mesin
            LEFT JOIN riwayat_mesin ON master_mesin.id_mesin = riwayat_mesin.id_mesin
              AND riwayat_mesin.tanggal_mulai <= LAST_DAY(STR_TO_DATE(?, '%Y-%m-%d'))
              AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= LAST_DAY(STR_TO_DATE(?, '%Y-%m-%d')))
            WHERE COALESCE(riwayat_mesin.line, master_mesin.line) != '' AND COALESCE(riwayat_mesin.line, master_mesin.line) IS NOT NULL
            GROUP BY COALESCE(riwayat_mesin.plant, master_mesin.plant, 'Plant 1'), COALESCE(riwayat_mesin.departemen, master_mesin.departemen), COALESCE(riwayat_mesin.line, master_mesin.line)
        ";

        return $this->db->query($sql, [$dateStr, $dateStr])->getResultArray();
    }
}
