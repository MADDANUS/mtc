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
        return $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi')
                    ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                    ->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                    ->orderBy('laporan_abnormal.id_abnormal', 'DESC')
                    ->findAll();
    }

    public function getPdfLaporan(string $lokasi, string $kategori, string $bulan, string $search, ?int $perPage = null): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori, master_parameter_check.bagian_check, master_parameter_check.sub_item_check')
                        ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                        ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                        ->join('transaksi_check_detail', 'transaksi_check_detail.id_detail = laporan_abnormal.id_detail', 'left')
                        ->join('master_parameter_check', 'master_parameter_check.id_parameter = transaksi_check_detail.id_parameter', 'left');
        if (!empty($lokasi)) {
            $builder->where('master_mesin.lokasi', $lokasi);
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

    public function getPdfAllCategoriesLaporan(string $lokasi, string $kategori, string $bulan, string $search): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                        ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                        ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                        ->where('master_mesin.lokasi', $lokasi)
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

    public function getPdfAllSummaryLaporan(string $lokasi, string $kategori, string $bulan, string $line): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                        ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                        ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                        ->where('master_mesin.lokasi', $lokasi)
                        ->where('transaksi_check.kategori', $kategori);
        if (!empty($bulan)) {
            $builder->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after');
        }
        if (!empty($line)) {
            $builder->where('master_mesin.line', $line);
        }
        return $builder->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                       ->orderBy('laporan_abnormal.id_abnormal', 'DESC')
                       ->findAll();
    }

    public function getIndexLaporan(string $lokasi, string $kategori, string $bulan, string $search, ?int $perPage = null): array
    {
        return $this->getPdfLaporan($lokasi, $kategori, $bulan, $search, $perPage);
    }

    public function getDashboardSummaryAbnormal(string $bulan): array
    {
        return $this->select('master_mesin.lokasi, master_mesin.line, transaksi_check.kategori, SUM(CASE WHEN laporan_abnormal.action IS NULL OR laporan_abnormal.action = \'\' THEN 1 ELSE 0 END) as totalOpen, COUNT(laporan_abnormal.id_abnormal) as totalAll')
                    ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                    ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                    ->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after')
                    ->groupBy('master_mesin.lokasi, master_mesin.line, transaksi_check.kategori')
                    ->findAll();
    }

    public function getOverhaulLaporan(string $lokasi, string $bulan, string $search, ?int $perPage = null): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori, master_parameter_check.bagian_check, master_parameter_check.sub_item_check')
                        ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                        ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                        ->join('transaksi_check_detail', 'transaksi_check_detail.id_detail = laporan_abnormal.id_detail', 'left')
                        ->join('master_parameter_check', 'master_parameter_check.id_parameter = transaksi_check_detail.id_parameter', 'left')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);
        if (!empty($lokasi) && $lokasi !== 'all') {
            $builder->where('master_mesin.lokasi', $lokasi);
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
        $builder->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                ->orderBy('laporan_abnormal.id_abnormal', 'DESC');
                
        if ($perPage !== null) {
            return $builder->paginate($perPage, 'abnormal_overhaul');
        }
        return $builder->findAll();
    }

    public function getOverhaulPdfLaporan(string $bulan): array
    {
        $builder = $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                        ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                        ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);
        if (!empty($bulan)) {
            $builder->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after');
        }
        return $builder->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                       ->orderBy('laporan_abnormal.id_abnormal', 'DESC')
                       ->findAll();
    }

    public function getOverhaulDashboardSummaryLaporan(string $lokasi, string $bulan): array
    {
        return $this->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi')
                    ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                    ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                    ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                    ->where('master_mesin.lokasi', $lokasi)
                    ->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after')
                    ->findAll();
    }

    public function getOverhaulDashboardSummaryTotals(string $bulan): array
    {
        return $this->select('SUM(CASE WHEN laporan_abnormal.action IS NULL OR laporan_abnormal.action = \'\' THEN 1 ELSE 0 END) as totalOpen, COUNT(laporan_abnormal.id_abnormal) as totalAll')
                    ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                    ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                    ->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after')
                    ->first() ?: [];
    }
}
