<?php

namespace App\Models;

use CodeIgniter\Model;

class RiwayatMesinModel extends Model
{
    protected $table         = 'riwayat_mesin';
    protected $primaryKey    = 'id_riwayat';
    protected $allowedFields = [
        'id_mesin',
        'lokasi',
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
            SELECT lokasi, line, COUNT(id_mesin) as total
            FROM riwayat_mesin
            WHERE tanggal_mulai <= LAST_DAY(STR_TO_DATE(?, '%Y-%m-%d'))
              AND (tanggal_selesai IS NULL OR tanggal_selesai >= LAST_DAY(STR_TO_DATE(?, '%Y-%m-%d')))
              AND line != '' AND line IS NOT NULL
            GROUP BY lokasi, line
        ";

        return $this->db->query($sql, [$dateStr, $dateStr])->getResultArray();
    }
}
