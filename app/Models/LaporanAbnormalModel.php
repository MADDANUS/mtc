<?php

namespace App\Models;

use CodeIgniter\Model;

class LaporanAbnormalModel extends Model
{
    protected $table         = 'laporan_abnormal';
    protected $primaryKey    = 'id_abnormal';
    protected $allowedFields = [
        'id_transaksi',
        'id_detail',
        'id_mesin',
        'point_check',
        'abnormal_condition',
        'type_sparepart',
        'pengecekan_tanggal',
        'pengecekan_pic',
        'progres_stock',
        'progres_tanggal',
        'action',
        'repair_pic',
        'keterangan',
        'foto_abnormal',
        'foto_perbaikan',
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    /**
     * Get abnormal reports with machine details.
     */
    public function getAbnormalReports()
    {
        return $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi')
                    ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                    ->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                    ->orderBy('laporan_abnormal.id_abnormal', 'DESC')
                    ->findAll();
    }
}
