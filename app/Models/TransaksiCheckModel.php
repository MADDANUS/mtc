<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiCheckModel extends Model
{
    protected $table         = 'transaksi_check';
    protected $primaryKey    = 'id_transaksi';
    protected $allowedFields = [
        'id_user', 'nama_pic', 'id_mesin', 'lokasi_check', 'line_check', 'jenis_check', 'kategori',
        'waktu_mulai', 'waktu_selesai', 'status', 'approved_by', 'pic_line_nama', 'approved_at',
        'approval_l1_by', 'leader_nama', 'approval_l1_at', 'approval_l2_by', 'approval_l2_at',
        'target_periode', 'ss_type_mesin', 'ss_serial_nomor', 'ss_bar_feeder'
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    /**
     * Durasi pengerjaan dalam detik (waktu_selesai - waktu_mulai).
     * Berguna untuk analisis efisiensi (dipakai Leader).
     */
    public function getDurasiDetik(array $row): ?int
    {
        if (empty($row['waktu_mulai']) || empty($row['waktu_selesai'])) {
            return null;
        }

        return strtotime($row['waktu_selesai']) - strtotime($row['waktu_mulai']);
    }

    /**
     * Daftar riwayat transaksi (join users + master_mesin), terbaru dulu.
     * $userId null -> semua staff (dipakai Leader/Admin).
     * $userId diisi -> hanya milik staff tsb (dipakai Staff).
     */
    public function getRiwayat(?int $userId = null, ?int $limit = null, ?string $kategori = null): array
    {
        $builder = $this->select('transaksi_check.*, users.nama as nama_staff, approver.nama as approver_nama, master_mesin.no_mesin, master_mesin.type_mesin, IF(transaksi_check.jenis_check = "Overhaul", COALESCE(transaksi_check.line_check, master_mesin.line), COALESCE(riwayat_mesin.line, master_mesin.line)) as line')
                         ->join('users', 'users.id = transaksi_check.id_user')
                         ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                         ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                         ->join('riwayat_mesin', 'riwayat_mesin.id_mesin = transaksi_check.id_mesin AND riwayat_mesin.tanggal_mulai <= LAST_DAY(STR_TO_DATE(CONCAT(transaksi_check.target_periode, "-01"), "%Y-%m-%d")) AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= LAST_DAY(STR_TO_DATE(CONCAT(transaksi_check.target_periode, "-01"), "%Y-%m-%d")))', 'left')
                         ->orderBy('transaksi_check.id_transaksi', 'DESC');

        if ($userId !== null) {
            $builder->where('transaksi_check.id_user', $userId);
        }
        if ($kategori !== null) {
            $builder->where('transaksi_check.kategori', $kategori);
        }
        if ($limit !== null) {
            $builder->limit($limit);
        }

        return $builder->findAll();
    }

    /**
     * Daftar riwayat transaksi terfilter (join users + master_mesin), terbaru dulu.
     */
    public function getRiwayatFiltered(array $filters = [], ?int $userId = null, ?int $limit = null, ?int $perPage = null): array
    {
        $builder = $this->select('transaksi_check.*, users.nama as nama_staff, approver.nama as approver_nama, master_mesin.no_mesin, COALESCE(transaksi_check.ss_type_mesin, master_mesin.type_mesin) as type_mesin, IF(transaksi_check.jenis_check = "Overhaul", COALESCE(transaksi_check.line_check, master_mesin.line), COALESCE(riwayat_mesin.line, master_mesin.line)) as line, (SELECT CASE WHEN SUM(CASE WHEN hasil_check = \'Δ\' THEN 1 ELSE 0 END) > 0 THEN \'Δ\' WHEN COUNT(id_detail) > 0 AND SUM(CASE WHEN hasil_check = \'X\' THEN 1 ELSE 0 END) = COUNT(id_detail) THEN \'X\' ELSE \'V\' END FROM transaksi_check_detail WHERE id_transaksi = transaksi_check.id_transaksi) as kondisi_mesin')
                         ->join('users', 'users.id = transaksi_check.id_user')
                         ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                         ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                         ->join('riwayat_mesin', 'riwayat_mesin.id_mesin = transaksi_check.id_mesin AND riwayat_mesin.tanggal_mulai <= LAST_DAY(STR_TO_DATE(CONCAT(transaksi_check.target_periode, "-01"), "%Y-%m-%d")) AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= LAST_DAY(STR_TO_DATE(CONCAT(transaksi_check.target_periode, "-01"), "%Y-%m-%d")))', 'left');

        if ($userId !== null) {
            $builder->where('transaksi_check.id_user', $userId);
        }

        if (!empty($filters['lokasi'])) {
            $builder->where('transaksi_check.lokasi_check', $filters['lokasi']);
        }

        if (!empty($filters['jenis_check'])) {
            $builder->where('transaksi_check.jenis_check', $filters['jenis_check']);
        }

        if (!empty($filters['id_mesin'])) {
            $builder->where('transaksi_check.id_mesin', (int)$filters['id_mesin']);
        }

        if (!empty($filters['line']) && $filters['line'] !== 'all') {
            $escapedLine = $this->db->escape($filters['line']);
            $builder->where('IF(transaksi_check.jenis_check = "Overhaul", COALESCE(transaksi_check.line_check, master_mesin.line), COALESCE(riwayat_mesin.line, master_mesin.line)) = ' . $escapedLine, null, false);
        }

        if (!empty($filters['kategori'])) {
            $builder->where('transaksi_check.kategori', $filters['kategori']);
        }

                if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $builder->whereIn('transaksi_check.status', $filters['status']);
            } else {
                $builder->where('transaksi_check.status', $filters['status']);
            }
        }

        if (!empty($filters['bulan'])) {
            $builder->groupStart()
                        ->where('transaksi_check.target_periode', $filters['bulan'])
                        ->orGroupStart()
                            ->where('(transaksi_check.target_periode IS NULL OR transaksi_check.target_periode = "")', null, false)
                            ->like('transaksi_check.waktu_mulai', $filters['bulan'], 'after')
                        ->groupEnd()
                    ->groupEnd();
        }

        if (!empty($filters['pic'])) {
            $builder->groupStart()
                    ->where('users.nama', $filters['pic'])
                    ->orLike('transaksi_check.nama_pic', $filters['pic'], 'both')
                    ->groupEnd();
        }

        // Dynamic Sorting
        $sortBy = $filters['sort_by'] ?? 'id_transaksi';
        $order  = strtoupper($filters['order'] ?? 'DESC');
        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $allowedSortFields = [
            'id_transaksi' => 'transaksi_check.id_transaksi',
            'nama_staff'   => 'users.nama',
            'no_mesin'     => 'master_mesin.no_mesin',
            'kategori'     => 'transaksi_check.kategori',
            'waktu_mulai'  => 'transaksi_check.waktu_mulai',
            'status'       => 'transaksi_check.status',
            'durasi'       => 'TIMESTAMPDIFF(SECOND, transaksi_check.waktu_mulai, transaksi_check.waktu_selesai)',
        ];

        $sortColumn = $allowedSortFields[$sortBy] ?? 'transaksi_check.id_transaksi';
        $builder->orderBy($sortColumn, $order);

        if ($limit !== null) {
            $builder->limit($limit);
        }

        if ($perPage !== null) {
            return $builder->paginate($perPage, 'riwayat');
        }

        return $builder->findAll();
    }

    /**
     * Header transaksi + info staff & mesin, untuk halaman detail riwayat.
     */
    public function getDetailTransaksi(int $idTransaksi): ?array
    {
        return $this->select('transaksi_check.*, users.nama as nama_staff, approver.nama as approver_nama, approver_l1.nama as approver_l1_nama, approver_l2.nama as approver_l2_nama, master_mesin.no_mesin, COALESCE(transaksi_check.ss_type_mesin, master_mesin.type_mesin) as type_mesin, COALESCE(transaksi_check.ss_serial_nomor, master_mesin.serial_nomor) as serial_nomor, COALESCE(transaksi_check.ss_bar_feeder, transaksi_overhaul.bar_feeder_type) as bar_feeder_type, transaksi_overhaul.support_pic, transaksi_overhaul.note_recommendation')
                    ->join('users', 'users.id = transaksi_check.id_user')
                    ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                    ->join('users as approver_l1', 'approver_l1.id = transaksi_check.approval_l1_by', 'left')
                    ->join('users as approver_l2', 'approver_l2.id = transaksi_check.approval_l2_by', 'left')
                    ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                    ->join('transaksi_overhaul', 'transaksi_overhaul.id_transaksi = transaksi_check.id_transaksi', 'left')
                    ->where('transaksi_check.id_transaksi', $idTransaksi)
                    ->first();
    }

    /**
     * Laporan durasi pengecekan (analisis efisiensi) untuk Leader/Admin.
     */
    public function getLaporanDurasi(array $filters = [], ?int $perPage = null): array
    {
        $builder = $this->select("transaksi_check.*, users.nama as nama_staff, approver.nama as approver_nama, master_mesin.no_mesin, COALESCE(transaksi_check.ss_type_mesin, master_mesin.type_mesin) as type_mesin, master_mesin.line, master_mesin.lokasi as lokasi_mesin, TIMESTAMPDIFF(SECOND, transaksi_check.waktu_mulai, transaksi_check.waktu_selesai) as durasi_detik, COALESCE(transaksi_check.ss_bar_feeder, transaksi_overhaul.bar_feeder_type) as bar_feeder_type, transaksi_overhaul.support_pic, transaksi_overhaul.note_recommendation")
                    ->join('users', 'users.id = transaksi_check.id_user')
                    ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                    ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                    ->join('transaksi_overhaul', 'transaksi_overhaul.id_transaksi = transaksi_check.id_transaksi', 'left');
                    
        if (!empty($filters['lokasi'])) {
            $builder->where('master_mesin.lokasi', $filters['lokasi']);
        }
        if (!empty($filters['line'])) {
            $builder->where('master_mesin.line', $filters['line']);
        }
        if (!empty($filters['id_mesin'])) {
            $builder->where('transaksi_check.id_mesin', (int)$filters['id_mesin']);
        }
        if (!empty($filters['jenis_check'])) {
            $builder->where('transaksi_check.jenis_check', $filters['jenis_check']);
        }
        if (!empty($filters['bulan'])) {
            $builder->groupStart()
                        ->where('transaksi_check.target_periode', $filters['bulan'])
                        ->orGroupStart()
                            ->where('(transaksi_check.target_periode IS NULL OR transaksi_check.target_periode = "")', null, false)
                            ->like('transaksi_check.waktu_mulai', $filters['bulan'], 'after')
                        ->groupEnd()
                    ->groupEnd();
        }
        if (!empty($filters['pic'])) {
            $builder->groupStart()
                    ->where('users.nama', $filters['pic'])
                    ->orLike('transaksi_check.nama_pic', $filters['pic'], 'both')
                    ->groupEnd();
        }

        // Dynamic Sorting
        $sortBy = $filters['sort_by'] ?? 'id_transaksi';
        $order  = strtoupper($filters['order'] ?? 'DESC');
        if (!in_array($order, ['ASC', 'DESC'], true)) {
            $order = 'DESC';
        }

        $allowedSortFields = [
            'id_transaksi' => 'transaksi_check.id_transaksi',
            'nama_staff'   => 'users.nama',
            'no_mesin'     => 'master_mesin.no_mesin',
            'lokasi_mesin' => 'master_mesin.lokasi',
            'line'         => 'master_mesin.line',
            'jenis_check'  => 'transaksi_check.jenis_check',
            'waktu_mulai'  => 'transaksi_check.waktu_mulai',
            'waktu_selesai'=> 'transaksi_check.waktu_selesai',
            'durasi_detik' => 'durasi_detik',
        ];

        $sortField = $allowedSortFields[$sortBy] ?? 'transaksi_check.id_transaksi';

        $builder->orderBy($sortField, $order);

        if ($perPage !== null) {
            return $builder->paginate($perPage, 'durasi');
        }

        return $builder->findAll();
    }

    /**
     * Menghitung Rata-rata Durasi Pengecekan tanpa meload seluruh data.
     * Replikasi pasti dari logika intdiv(total, count) lama.
     */
    public function getRataRataDurasiFiltered(array $filters = []): int
    {
        $builder = $this->select("SUM(TIMESTAMPDIFF(SECOND, transaksi_check.waktu_mulai, transaksi_check.waktu_selesai)) as sum_detik, COUNT(TIMESTAMPDIFF(SECOND, transaksi_check.waktu_mulai, transaksi_check.waktu_selesai)) as count_detik")
                    ->join('users', 'users.id = transaksi_check.id_user')
                    ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                    ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                    ->join('transaksi_overhaul', 'transaksi_overhaul.id_transaksi = transaksi_check.id_transaksi', 'left');
                    
        if (!empty($filters['lokasi'])) {
            $builder->where('master_mesin.lokasi', $filters['lokasi']);
        }
        if (!empty($filters['line'])) {
            $builder->where('master_mesin.line', $filters['line']);
        }
        if (!empty($filters['id_mesin'])) {
            $builder->where('transaksi_check.id_mesin', (int)$filters['id_mesin']);
        }
        if (!empty($filters['jenis_check'])) {
            $builder->where('transaksi_check.jenis_check', $filters['jenis_check']);
        }
        if (!empty($filters['bulan'])) {
            $builder->groupStart()
                        ->where('transaksi_check.target_periode', $filters['bulan'])
                        ->orGroupStart()
                            ->where('(transaksi_check.target_periode IS NULL OR transaksi_check.target_periode = "")', null, false)
                            ->like('transaksi_check.waktu_mulai', $filters['bulan'], 'after')
                        ->groupEnd()
                    ->groupEnd();
        }
        if (!empty($filters['pic'])) {
            $builder->groupStart()
                    ->where('users.nama', $filters['pic'])
                    ->orLike('transaksi_check.nama_pic', $filters['pic'], 'both')
                    ->groupEnd();
        }

        $row = $builder->first();

        $sum = (int) ($row['sum_detik'] ?? 0);
        $count = (int) ($row['count_detik'] ?? 0);

        return $count > 0 ? intdiv($sum, $count) : 0;
    }

    public function getTerbaruKhususLine(?string $lokasiLine = null): array
    {
        $builder = $this->select('transaksi_check.*, users.nama as nama_staff, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.line, TIMESTAMPDIFF(SECOND, transaksi_check.waktu_mulai, transaksi_check.waktu_selesai) as durasi_detik')
                        ->join('users', 'users.id = transaksi_check.id_user')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);

        if ($lokasiLine) {
            $builder->where('master_mesin.line', $lokasiLine);
        }

        return $builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }

    public function getPendingOverhaulLeader(?string $lokasiLine = null): array
    {
        $builder = $this->select('transaksi_check.*, master_mesin.no_mesin as nama_mesin')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                        ->where('transaksi_check.status', 'Pending');
        
        if ($lokasiLine) {
            $builder->where('master_mesin.line', $lokasiLine);
        }
        return $builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }

    public function getPendingOverhaulByRole(string $role, ?string $sessionLine = null): array
    {
        $builder = $this->select('transaksi_check.*, master_mesin.no_mesin as nama_mesin')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);
        
        if ($role === \App\Enums\Role::Leader->value) {
            $builder->where('transaksi_check.status', 'Pending');
            if ($sessionLine) {
                $builder->where('master_mesin.line', $sessionLine);
            }
        } elseif ($role === \App\Enums\Role::Sheadprd->value) {
            $builder->where('transaksi_check.status', 'Approved L1');
        } elseif ($role === \App\Enums\Role::Sheadmtc->value) {
            $builder->where('transaksi_check.status', 'Approved L2');
        } else {
            $builder->where('1=0');
        }
        
        return $builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }

    public function checkDuplicate(int $idMesin, string $jenisCheck, string $bulan, ?string $kategori = null): array
    {
        $builder = $this->select('id_transaksi, waktu_mulai, created_at, nama_pic, target_periode')
                        ->where('id_mesin', $idMesin)
                        ->where('jenis_check', $jenisCheck)
                        ->where('target_periode', $bulan);
                        
        if (!empty($kategori)) {
            $builder->where('kategori', $kategori);
        }
        
        return $builder->orderBy('id_transaksi', 'DESC')->findAll();
    }

    public function getAvailablePics(?string $lokasiName = null, ?string $jenisCheck = null): array
    {
        $builder = $this->select('transaksi_check.nama_pic, users.nama as nama_staff')
                        ->join('users', 'users.id = transaksi_check.id_user');
        
        if ($lokasiName !== null) {
            $builder->where('transaksi_check.lokasi_check', $lokasiName);
        }
        if (!empty($jenisCheck)) {
            $builder->where('transaksi_check.jenis_check', $jenisCheck);
        }
        return $builder->distinct()->findAll();
    }

    public function getAvailableBulan(?string $lokasiName = null, ?string $jenisCheck = null): array
    {
        $builder = $this->select("COALESCE(NULLIF(target_periode, ''), DATE_FORMAT(waktu_mulai, '%Y-%m')) as bulan", false);
        
        if ($lokasiName !== null) {
            $builder->where('lokasi_check', $lokasiName);
        }
        if (!empty($jenisCheck)) {
            $builder->where('jenis_check', $jenisCheck);
        }
        
        return $builder->distinct()->orderBy('bulan', 'DESC')->findAll();
    }

    public function getLatestIdByMesinAndKategori(int $idMesin, string $kategori, ?string $bulan): ?array
    {
        $builder = $this->select('id_transaksi')
                        ->where('id_mesin', $idMesin)
                        ->where('kategori', $kategori);
        if ($bulan) {
            $builder->groupStart()
                        ->where('target_periode', $bulan)
                        ->orGroupStart()
                            ->groupStart()
                                ->where('target_periode IS NULL')
                                ->orWhere('target_periode', '')
                            ->groupEnd()
                            ->like('waktu_mulai', $bulan, 'after')
                        ->groupEnd()
                    ->groupEnd();
        }
        return $builder->orderBy('id_transaksi', 'DESC')->first();
    }

    public function getInboxApprovalTransaksi(string $role, ?string $line): array
    {
        $joinDate = 'COALESCE(NULLIF(transaksi_check.target_periode, ""), DATE_FORMAT(transaksi_check.waktu_mulai, "%Y-%m"))';
        $joinCondition = 'riwayat_mesin.id_mesin = transaksi_check.id_mesin AND riwayat_mesin.tanggal_mulai <= LAST_DAY(STR_TO_DATE(CONCAT(' . $joinDate . ', "-01"), "%Y-%m-%d")) AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= LAST_DAY(STR_TO_DATE(CONCAT(' . $joinDate . ', "-01"), "%Y-%m-%d")))';

        $builder = $this->select('transaksi_check.id_transaksi AS doc_id, transaksi_check.jenis_check, transaksi_check.kategori, transaksi_check.lokasi_check, IF(transaksi_check.jenis_check = "Overhaul", COALESCE(transaksi_check.line_check, master_mesin.line), COALESCE(riwayat_mesin.line, master_mesin.line)) AS line, transaksi_check.nama_pic, users.nama AS nama_staff, transaksi_check.waktu_mulai AS doc_date, transaksi_check.status, master_mesin.no_mesin, master_mesin.type_mesin, "transaksi" AS doc_source, NULL AS lokasi, NULL AS persen', false)
                        ->join('users', 'users.id = transaksi_check.id_user', 'left')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin', 'left')
                        ->join('riwayat_mesin', $joinCondition, 'left');

        if ($role === \App\Enums\Role::Leader->value) {
            $builder->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                    ->where('transaksi_check.status', 'Pending');
            if ($line) {
                $escapedLine = $this->db->escape($line);
                $builder->where('IF(transaksi_check.jenis_check = "Overhaul", COALESCE(transaksi_check.line_check, master_mesin.line), COALESCE(riwayat_mesin.line, master_mesin.line)) = ' . $escapedLine, null, false);
            }
        } elseif ($role === \App\Enums\Role::Sheadprd->value) {
            $builder->whereIn('transaksi_check.jenis_check', [\App\Enums\JenisCheck::Overhaul->value, \App\Enums\JenisCheck::Preventive->value])
                    ->where('transaksi_check.status', 'Approved L1');
        } elseif ($role === \App\Enums\Role::Sheadmtc->value) {
            $builder->whereIn('transaksi_check.jenis_check', [\App\Enums\JenisCheck::Overhaul->value, \App\Enums\JenisCheck::Preventive->value])
                    ->where('transaksi_check.status', 'Approved L2');
        } elseif ($role === \App\Enums\Role::Member->value) {
            $builder->groupStart()
                        ->groupStart()
                            ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Preventive->value)
                            ->where('transaksi_check.status', 'Pending')
                        ->groupEnd()
                        ->orGroupStart()
                            ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                            ->whereIn('transaksi_check.status', ['Pending', 'Approved L1', 'Approved L2'])
                        ->groupEnd()
                    ->groupEnd();
        } elseif ($role === \App\Enums\Role::Admin->value) {
            $builder->whereNotIn('transaksi_check.status', ['Approved']);
        }
        
        return $builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }

    public function getPercentageSummary(?array $filters = null): array
    {
        $db = \Config\Database::connect();
        
        // 1. Tentukan Basis Total Mesin (Difilter jika ada)
        $mesinBuilder = $db->table('master_mesin m');
        
        if (!empty($filters['line'])) {
            $mesinBuilder->where('m.line', $filters['line']);
        }
        if (!empty($filters['lokasi'])) {
            $mesinBuilder->where('m.lokasi', $filters['lokasi']);
        }
        if (!empty($filters['id_mesin'])) {
            $mesinBuilder->where('m.id_mesin', $filters['id_mesin']);
        }
        
        $totalMesin = (int) $mesinBuilder->countAllResults();
        
        if ($totalMesin === 0) {
            return [
                'total_mesin' => 0,
                'preventive' => ['checked' => 0, 'coverage' => 0, 'normal' => 0, 'abnormal' => 0, 'normal_count' => 0, 'abnormal_count' => 0],
                'overhaul'   => ['checked' => 0, 'coverage' => 0, 'normal' => 0, 'abnormal' => 0, 'normal_count' => 0, 'abnormal_count' => 0]
            ];
        }

        // Tentukan Periode Waktu
        // Jika ada filter bulan, gunakan itu sebagai bulan sekarang
        $bulanSekarang = !empty($filters['bulan']) && $filters['bulan'] !== 'all' ? $filters['bulan'] : date('Y-m');
        
        $tahun = (int) substr($bulanSekarang, 0, 4);
        $bulan = (int) substr($bulanSekarang, 5, 2);
        
        $semester = $bulan <= 6 ? 1 : 2;
        $semesterStart = $semester === 1 ? "$tahun-01" : "$tahun-07";
        $semesterEnd   = $semester === 1 ? "$tahun-06" : "$tahun-12";

        // Fungsi Helper untuk mengambil status
        $getStats = function(string $jenis, string $periodeStart, string $periodeEnd) use ($db, $totalMesin, $filters) {
            $builder = $db->table('transaksi_check t')
                          ->select('t.id_mesin, 
                                    (SELECT CASE 
                                        WHEN SUM(CASE WHEN d.hasil_check = \'Δ\' THEN 1 ELSE 0 END) > 0 THEN \'Δ\' 
                                        WHEN COUNT(d.id_detail) > 0 AND SUM(CASE WHEN d.hasil_check = \'X\' THEN 1 ELSE 0 END) = COUNT(d.id_detail) THEN \'X\' 
                                        ELSE \'V\' 
                                     END 
                                     FROM transaksi_check_detail d 
                                     WHERE d.id_transaksi = t.id_transaksi) as kondisi')
                          ->join('master_mesin m', 'm.id_mesin = t.id_mesin', 'left')
                          ->where('t.jenis_check', $jenis)
                          ->where('t.status', 'Approved');
            
            // Terapkan Filter Tambahan (Line, Lokasi, Mesin)
            if (!empty($filters['line'])) {
                $builder->where('m.line', $filters['line']);
            }
            if (!empty($filters['lokasi'])) {
                $builder->where('m.lokasi', $filters['lokasi']);
            }
            if (!empty($filters['id_mesin'])) {
                $builder->where('t.id_mesin', $filters['id_mesin']);
            }
            
            // Logika Periode (Y-m)
            if ($periodeStart === $periodeEnd) {
                // Bulan tertentu
                $builder->groupStart()
                            ->where('t.target_periode', $periodeStart)
                            ->orGroupStart()
                                ->where('(t.target_periode IS NULL OR t.target_periode = "")', null, false)
                                ->like('t.waktu_mulai', $periodeStart, 'after')
                            ->groupEnd()
                        ->groupEnd();
            } else {
                // Range Semester
                $builder->groupStart()
                            ->where("t.target_periode >= '$periodeStart'")
                            ->where("t.target_periode <= '$periodeEnd'")
                            ->orGroupStart()
                                ->where('(t.target_periode IS NULL OR t.target_periode = "")', null, false)
                                ->where("DATE_FORMAT(t.waktu_mulai, '%Y-%m') >= '$periodeStart'")
                                ->where("DATE_FORMAT(t.waktu_mulai, '%Y-%m') <= '$periodeEnd'")
                            ->groupEnd()
                        ->groupEnd();
            }

            $builder->orderBy('t.id_transaksi', 'ASC');
            $results = $builder->get()->getResultArray();

            $mesinUnik = [];
            foreach ($results as $row) {
                $mesinUnik[$row['id_mesin']] = $row['kondisi'];
            }

            $checked = count($mesinUnik);
            $normal = 0;
            $abnormal = 0;
            
            foreach ($mesinUnik as $kondisi) {
                if ($kondisi === 'V') {
                    $normal++;
                } else {
                    $abnormal++;
                }
            }

            return [
                'checked'  => $checked,
                'coverage' => $totalMesin > 0 ? round(($checked / $totalMesin) * 100, 1) : 0,
                'normal'   => $checked > 0 ? round(($normal / $checked) * 100, 1) : 0,
                'abnormal' => $checked > 0 ? round(($abnormal / $checked) * 100, 1) : 0,
                'normal_count' => $normal,
                'abnormal_count' => $abnormal
            ];
        };

        return [
            'total_mesin' => $totalMesin,
            'preventive'  => $getStats('Preventive', $bulanSekarang, $bulanSekarang),
            'overhaul'    => $getStats('Overhaul', $semesterStart, $semesterEnd),
            'bulan'       => $bulanSekarang,
            'semester'    => "Semester $semester $tahun"
        ];
    }
}
