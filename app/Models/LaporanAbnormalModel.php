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
        'foto_abnormal_2',
        'foto_perbaikan',
        'foto_perbaikan_2',
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    /**
     * Get abnormal reports with machine details.
     */
    public function getAbnormalReports()
    {
        return $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.departemen')
                    ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                    ->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                    ->orderBy('laporan_abnormal.id_abnormal', 'DESC')
                    ->findAll();
    }

    public function getPdfLaporan(string $departemen, string $kategori, string $bulan, string $search, ?int $perPage = null, ?string $line = null): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.departemen, transaksi_check.kategori, master_parameter_check.bagian_check, master_parameter_check.sub_item_check, IF(laporan_abnormal.action IS NULL OR laporan_abnormal.action = "", master_mesin.line, COALESCE(riwayat_mesin.line, master_mesin.line)) as line')
                        ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                        ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                        ->join('transaksi_check_detail', 'transaksi_check_detail.id_detail = laporan_abnormal.id_detail', 'left')
                        ->join('master_parameter_check', 'master_parameter_check.id_parameter = transaksi_check_detail.id_parameter', 'left')
                        ->join('riwayat_mesin', 'riwayat_mesin.id_mesin = laporan_abnormal.id_mesin AND riwayat_mesin.tanggal_mulai <= DATE(laporan_abnormal.pengecekan_tanggal) AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= DATE(laporan_abnormal.pengecekan_tanggal))', 'left');
        if (!empty($departemen)) {
            $builder->where('master_mesin.departemen', $departemen);
        }
        if (!empty($line)) {
            $builder->where("IF(laporan_abnormal.action IS NULL OR laporan_abnormal.action = '', master_mesin.line, COALESCE(riwayat_mesin.line, master_mesin.line)) = " . $this->db->escape($line));
        }
        if (!empty($kategori)) {
            $builder->where('transaksi_check.kategori', $kategori);
        }
        if (!empty($bulan)) {
            $builder->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after');
        }
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('laporan_abnormal.point_check', $search)
                    ->orLike('laporan_abnormal.abnormal_condition', $search)
                    ->orLike('master_mesin.no_mesin', $search)
                    ->orLike('master_mesin.type_mesin', $search)
                    ->groupEnd();
        }
        $builder->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                ->orderBy('laporan_abnormal.id_abnormal', 'DESC');
                
        if ($perPage !== null) {
            return $builder->paginate($perPage, 'abnormal');
        }
        return $builder->findAll();
    }

    public function getPdfAllCategoriesLaporan(string $departemen, string $kategori, string $bulan, string $search): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.departemen, transaksi_check.kategori')
                        ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                        ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                        ->where('master_mesin.departemen', $departemen)
                        ->where('transaksi_check.kategori', $kategori);
        if (!empty($bulan)) {
            $builder->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after');
        }
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('laporan_abnormal.point_check', $search)
                    ->orLike('laporan_abnormal.abnormal_condition', $search)
                    ->orLike('master_mesin.no_mesin', $search)
                    ->orLike('master_mesin.type_mesin', $search)
                    ->groupEnd();
        }
        return $builder->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                       ->orderBy('laporan_abnormal.id_abnormal', 'DESC')
                       ->findAll();
    }

    public function getPdfAllSummaryLaporan(string $departemen, string $kategori, string $bulan, string $line): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.departemen, transaksi_check.kategori, IF(laporan_abnormal.action IS NULL OR laporan_abnormal.action = "", master_mesin.line, COALESCE(riwayat_mesin.line, master_mesin.line)) as line')
                        ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                        ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                        ->join('riwayat_mesin', 'riwayat_mesin.id_mesin = laporan_abnormal.id_mesin AND riwayat_mesin.tanggal_mulai <= DATE(laporan_abnormal.pengecekan_tanggal) AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= DATE(laporan_abnormal.pengecekan_tanggal))', 'left')
                        ->where('master_mesin.departemen', $departemen)
                        ->where('transaksi_check.kategori', $kategori);
        if (!empty($bulan)) {
            $builder->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after');
        }
        if (!empty($line)) {
            $builder->where("IF(laporan_abnormal.action IS NULL OR laporan_abnormal.action = '', master_mesin.line, COALESCE(riwayat_mesin.line, master_mesin.line)) = " . $this->db->escape($line));
        }
        return $builder->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                       ->orderBy('laporan_abnormal.id_abnormal', 'DESC')
                       ->findAll();
    }

    public function getIndexLaporan(string $departemen, string $kategori, string $bulan, string $search, ?int $perPage = null, ?string $line = null): array
    {
        return $this->getPdfLaporan($departemen, $kategori, $bulan, $search, $perPage, $line);
    }

    public function getDashboardSummaryAbnormal(string $bulan): array
    {
        return $this->select('master_mesin.departemen, COALESCE(riwayat_mesin.plant, master_mesin.plant) as plant, IF(laporan_abnormal.action IS NULL OR laporan_abnormal.action = \'\', master_mesin.line, COALESCE(riwayat_mesin.line, master_mesin.line)) as line, transaksi_check.kategori, SUM(CASE WHEN laporan_abnormal.action IS NULL OR laporan_abnormal.action = \'\' THEN 1 ELSE 0 END) as totalOpen, COUNT(laporan_abnormal.id_abnormal) as totalAll')
                    ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                    ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                    ->join('riwayat_mesin', 'riwayat_mesin.id_mesin = laporan_abnormal.id_mesin AND riwayat_mesin.tanggal_mulai <= DATE(laporan_abnormal.pengecekan_tanggal) AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= DATE(laporan_abnormal.pengecekan_tanggal))', 'left')
                    ->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after')
                    ->groupBy('plant, master_mesin.departemen, line, transaksi_check.kategori')
                    ->findAll();
    }

    public function getOverhaulLaporan(string $departemen, string $bulan, string $search, ?int $perPage = null, string $line = ''): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.departemen, transaksi_check.kategori, master_parameter_check.bagian_check, master_parameter_check.sub_item_check')
                        ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                        ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                        ->join('transaksi_check_detail', 'transaksi_check_detail.id_detail = laporan_abnormal.id_detail', 'left')
                        ->join('master_parameter_check', 'master_parameter_check.id_parameter = transaksi_check_detail.id_parameter', 'left')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);
        if (!empty($departemen) && $departemen !== 'all') {
            $builder->where('master_mesin.departemen', $departemen);
        }
        if (!empty($bulan) && $bulan !== 'all') {
            $builder->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after');
        }
        if (!empty($search)) {
            $builder->groupStart()
                    ->like('laporan_abnormal.point_check', $search)
                    ->orLike('laporan_abnormal.abnormal_condition', $search)
                    ->orLike('master_mesin.no_mesin', $search)
                    ->orLike('master_mesin.type_mesin', $search)
                    ->groupEnd();
        }
        if (!empty($line)) {
            $builder->join('riwayat_mesin', 'riwayat_mesin.id_mesin = laporan_abnormal.id_mesin AND riwayat_mesin.tanggal_mulai <= DATE(laporan_abnormal.pengecekan_tanggal) AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= DATE(laporan_abnormal.pengecekan_tanggal))', 'left');
            $builder->where('IF(laporan_abnormal.action IS NULL OR laporan_abnormal.action = \'\', master_mesin.line, COALESCE(riwayat_mesin.line, master_mesin.line))', $line);
        }
        $builder->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                ->orderBy('laporan_abnormal.id_abnormal', 'DESC');
                
        if ($perPage !== null) {
            return $builder->paginate($perPage, 'abnormal_overhaul');
        }
        return $builder->findAll();
    }

    public function getOverhaulPdfLaporan(string $bulan): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.departemen, transaksi_check.kategori')
                        ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                        ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);
        
        $this->applySemesterFilter($builder, $bulan);

        return $builder->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                       ->orderBy('laporan_abnormal.id_abnormal', 'DESC')
                       ->findAll();
    }

    public function getOverhaulDashboardSummaryLaporan(string $departemen, string $bulan): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.departemen, COALESCE(riwayat_mesin.plant, master_mesin.plant) as plant, IF(laporan_abnormal.action IS NULL OR laporan_abnormal.action = \'\', master_mesin.line, COALESCE(riwayat_mesin.line, master_mesin.line)) as line')
                    ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                    ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                    ->join('riwayat_mesin', 'riwayat_mesin.id_mesin = laporan_abnormal.id_mesin AND riwayat_mesin.tanggal_mulai <= DATE(laporan_abnormal.pengecekan_tanggal) AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= DATE(laporan_abnormal.pengecekan_tanggal))', 'left')
                    ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                    ->where('master_mesin.departemen', $departemen);
        $this->applySemesterFilter($builder, $bulan);
        return $builder->findAll();
    }

    public function getOverhaulDashboardSummaryTotals(string $bulan): array
    {
        $builder = $this->select('SUM(CASE WHEN laporan_abnormal.action IS NULL OR laporan_abnormal.action = \'\' THEN 1 ELSE 0 END) as totalOpen, COUNT(laporan_abnormal.id_abnormal) as totalAll')
                    ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                    ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);
        $this->applySemesterFilter($builder, $bulan);
        return $builder->first() ?: [];
    }

    private function applySemesterFilter($builder, string $bulan)
    {
        if (!empty($bulan) && $bulan !== 'all') {
            $year = substr($bulan, 0, 4);
            $month = (int)substr($bulan, 5, 2);
            if ($month <= 6) {
                $startDate = $year . '-01-01';
                $endDate = $year . '-06-30';
            } else {
                $startDate = $year . '-07-01';
                $endDate = $year . '-12-31';
            }
            $builder->where('laporan_abnormal.pengecekan_tanggal >=', $startDate)
                    ->where('laporan_abnormal.pengecekan_tanggal <=', $endDate);
        }
    }
}
