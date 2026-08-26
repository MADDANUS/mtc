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
            SELECT riwayat_mesin.plant, riwayat_mesin.departemen, riwayat_mesin.line, 
                   COUNT(DISTINCT riwayat_mesin.id_mesin) as total,
                   COUNT(DISTINCT CASE WHEN master_mesin.jenis = 'CAM' THEN riwayat_mesin.id_mesin END) as total_cam
            FROM riwayat_mesin
            JOIN master_mesin ON master_mesin.id_mesin = riwayat_mesin.id_mesin
            WHERE riwayat_mesin.tanggal_mulai <= LAST_DAY(STR_TO_DATE(?, '%Y-%m-%d'))
              AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= LAST_DAY(STR_TO_DATE(?, '%Y-%m-%d')))
              AND riwayat_mesin.line != '' AND riwayat_mesin.line IS NOT NULL
            GROUP BY riwayat_mesin.plant, riwayat_mesin.departemen, riwayat_mesin.line
        ";

        return $this->db->query($sql, [$dateStr, $dateStr])->getResultArray();
    }
}
