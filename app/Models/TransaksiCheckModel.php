<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiCheckModel extends Model
{
    protected $table         = 'transaksi_check';
    protected $primaryKey    = 'id_transaksi';
    protected $allowedFields = [
        'id_user', 'nama_pic', 'id_mesin', 'plant', 'departemen_check', 'line_check', 'jenis_check', 'kategori',
        'waktu_mulai', 'waktu_selesai', 'status', 'approved_by', 'approved_at',
        'approval_l1_by', 'approval_l1_at', 'approval_l2_by', 'approval_l2_at',
        'target_periode', 'ss_type_mesin', 'ss_serial_nomor', 'ss_bar_feeder',
        'ss_no_mesin', 'ss_approval_l1_name', 'ss_approval_l2_name', 'ss_approved_name'
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
        $builder = $this->select('transaksi_check.*, COALESCE(users.nama, transaksi_check.nama_pic) as nama_staff, COALESCE(approver.nama, transaksi_check.ss_approved_name) as approver_nama, COALESCE(master_mesin.no_mesin, transaksi_check.ss_no_mesin) as no_mesin, master_mesin.type_mesin, COALESCE(master_mesin.plant, transaksi_check.plant) as plant, COALESCE(master_mesin.line, riwayat_mesin.line, transaksi_check.line_check) as line')
                         ->join('users', 'users.id = transaksi_check.id_user', 'left')
                         ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                         ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin', 'left')
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
        $builder = $this->select('transaksi_check.*, COALESCE(users.nama, transaksi_check.nama_pic) as nama_staff, COALESCE(approver.nama, transaksi_check.ss_approved_name) as approver_nama, COALESCE(master_mesin.no_mesin, transaksi_check.ss_no_mesin) as no_mesin, COALESCE(master_mesin.plant, transaksi_check.plant) as plant, COALESCE(master_mesin.type_mesin, transaksi_check.ss_type_mesin) as type_mesin, COALESCE(master_mesin.line, riwayat_mesin.line, transaksi_check.line_check) as line, (SELECT CASE WHEN SUM(CASE WHEN hasil_check = \'Δ\' THEN 1 ELSE 0 END) > 0 THEN \'Δ\' WHEN COUNT(id_detail) > 0 AND SUM(CASE WHEN hasil_check = \'X\' THEN 1 ELSE 0 END) = COUNT(id_detail) THEN \'X\' ELSE \'V\' END FROM transaksi_check_detail WHERE id_transaksi = transaksi_check.id_transaksi) as kondisi_mesin')
                         ->join('users', 'users.id = transaksi_check.id_user', 'left')
                         ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                         ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin', 'left')
                         ->join('riwayat_mesin', 'riwayat_mesin.id_mesin = transaksi_check.id_mesin AND riwayat_mesin.tanggal_mulai <= LAST_DAY(STR_TO_DATE(CONCAT(transaksi_check.target_periode, "-01"), "%Y-%m-%d")) AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= LAST_DAY(STR_TO_DATE(CONCAT(transaksi_check.target_periode, "-01"), "%Y-%m-%d")))', 'left');

        if ($userId !== null) {
            $builder->where('transaksi_check.id_user', $userId);
        }

        if (!empty($filters['plant']) && $filters['plant'] !== 'all') {
            $plantsArray = array_map('trim', explode(',', $filters['plant']));
            $escapedPlants = array_map(function($p) { return $this->db->escape($p); }, $plantsArray);
            $inClause = implode(',', $escapedPlants);
            $builder->where('IF(transaksi_check.jenis_check = "Overhaul", COALESCE(master_mesin.plant, transaksi_check.plant), master_mesin.plant) IN (' . $inClause . ')', null, false);
        }

        if (!empty($filters['departemen'])) {
            $deptsArray = array_map('trim', explode(',', $filters['departemen']));
            if (count($deptsArray) === 1) {
                $builder->where('transaksi_check.departemen_check', $deptsArray[0]);
            } else {
                $escapedDepts = array_map(function($d) { return $this->db->escape($d); }, $deptsArray);
                $inClause = implode(',', $escapedDepts);
                $builder->where('transaksi_check.departemen_check IN (' . $inClause . ')', null, false);
            }
        }

        if (!empty($filters['jenis_check'])) {
            $builder->where('transaksi_check.jenis_check', $filters['jenis_check']);
        }

        if (!empty($filters['id_mesin'])) {
            $builder->where('transaksi_check.id_mesin', (int)$filters['id_mesin']);
        }

        if (!empty($filters['line']) && $filters['line'] !== 'all') {
            $linesArray = array_map('trim', explode(',', $filters['line']));
            $escapedLines = array_map(function($l) { return $this->db->escape($l); }, $linesArray);
            $inClause = implode(',', $escapedLines);
            $builder->where('IF(transaksi_check.jenis_check = "Overhaul", COALESCE(master_mesin.line, transaksi_check.line_check), COALESCE(master_mesin.line, riwayat_mesin.line)) IN (' . $inClause . ')', null, false);
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
        return $this->select('transaksi_check.*, COALESCE(users.nama, transaksi_check.nama_pic) as nama_staff, COALESCE(approver.nama, transaksi_check.ss_approved_name) as approver_nama, COALESCE(approver_l1.nama, transaksi_check.ss_approval_l1_name) as approver_l1_nama, COALESCE(approver_l2.nama, transaksi_check.ss_approval_l2_name) as approver_l2_nama, COALESCE(master_mesin.no_mesin, transaksi_check.ss_no_mesin) as no_mesin, COALESCE(master_mesin.plant, transaksi_check.plant) as plant, COALESCE(master_mesin.line, transaksi_check.line_check) as line, COALESCE(master_mesin.departemen, transaksi_check.departemen_check) as departemen, COALESCE(master_mesin.type_mesin, transaksi_check.ss_type_mesin) as type_mesin, COALESCE(master_mesin.serial_nomor, transaksi_check.ss_serial_nomor) as serial_nomor, COALESCE(transaksi_overhaul.bar_feeder_type, transaksi_check.ss_bar_feeder) as bar_feeder_type, transaksi_overhaul.support_pic, transaksi_overhaul.note_recommendation')
                    ->join('users', 'users.id = transaksi_check.id_user', 'left')
                    ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                    ->join('users as approver_l1', 'approver_l1.id = transaksi_check.approval_l1_by', 'left')
                    ->join('users as approver_l2', 'approver_l2.id = transaksi_check.approval_l2_by', 'left')
                    ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin', 'left')
                    ->join('transaksi_overhaul', 'transaksi_overhaul.id_transaksi = transaksi_check.id_transaksi', 'left')
                    ->where('transaksi_check.id_transaksi', $idTransaksi)
                    ->first();
    }

    /**
     * Laporan durasi pengecekan (analisis efisiensi) untuk Leader/Admin.
     */
    public function getLaporanDurasi(array $filters = [], ?int $perPage = null): array
    {
        $builder = $this->select("transaksi_check.*, COALESCE(users.nama, transaksi_check.nama_pic) as nama_staff, COALESCE(approver.nama, transaksi_check.ss_approved_name) as approver_nama, COALESCE(master_mesin.no_mesin, transaksi_check.ss_no_mesin) as no_mesin, COALESCE(master_mesin.plant, transaksi_check.plant) as plant, COALESCE(master_mesin.type_mesin, transaksi_check.ss_type_mesin) as type_mesin, COALESCE(master_mesin.line, transaksi_check.line_check) as line, COALESCE(master_mesin.departemen, transaksi_check.departemen_check) as lokasi_mesin, TIMESTAMPDIFF(SECOND, transaksi_check.waktu_mulai, transaksi_check.waktu_selesai) as durasi_detik, COALESCE(transaksi_overhaul.bar_feeder_type, transaksi_check.ss_bar_feeder) as bar_feeder_type, transaksi_overhaul.support_pic, transaksi_overhaul.note_recommendation")
                    ->join('users', 'users.id = transaksi_check.id_user', 'left')
                    ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                    ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin', 'left')
                    ->join('transaksi_overhaul', 'transaksi_overhaul.id_transaksi = transaksi_check.id_transaksi', 'left');
                    
        if (!empty($filters['departemen']) && $filters['departemen'] !== '-') {
            $deptsArray = array_map('trim', explode(',', $filters['departemen']));
            $builder->whereIn('master_mesin.departemen', $deptsArray);
        }
        if (!empty($filters['line']) && $filters['line'] !== '-') {
            $linesArray = array_map('trim', explode(',', $filters['line']));
            $builder->whereIn('master_mesin.line', $linesArray);
        }
        if (!empty($filters['plant']) && $filters['plant'] !== '-') {
            $planArray = array_map('trim', explode(',', $filters['plant']));
            $builder->whereIn('master_mesin.plant', $planArray);
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
            'lokasi_mesin' => 'master_mesin.departemen',
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
                    
        if (!empty($filters['departemen'])) {
            $deptsArray = array_map('trim', explode(',', $filters['departemen']));
            $builder->whereIn('master_mesin.departemen', $deptsArray);
        }
        if (!empty($filters['line']) && $filters['line'] !== '-') {
            $linesArray = array_map('trim', explode(',', $filters['line']));
            $builder->whereIn('master_mesin.line', $linesArray);
        }
        if (!empty($filters['plant']) && $filters['plant'] !== '-') {
            $planArray = array_map('trim', explode(',', $filters['plant']));
            $builder->whereIn('master_mesin.plant', $planArray);
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

    public function getTerbaruKhususLine(?string $departemenLine = null): array
    {
        $builder = $this->select('transaksi_check.*, users.nama as nama_staff, master_mesin.no_mesin, COALESCE(master_mesin.plant, transaksi_check.plant) as plant, master_mesin.type_mesin, COALESCE(master_mesin.line, transaksi_check.line_check) as line, TIMESTAMPDIFF(SECOND, transaksi_check.waktu_mulai, transaksi_check.waktu_selesai) as durasi_detik')
                        ->join('users', 'users.id = transaksi_check.id_user')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);

        if ($departemenLine) {
            $linesArray = array_map('trim', explode(',', $departemenLine));
            $builder->whereIn('master_mesin.line', $linesArray);
        }

        return $builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }

    public function getPendingOverhaulLeader(?string $departemenLine = null): array
    {
        $builder = $this->select('transaksi_check.*, master_mesin.no_mesin as nama_mesin')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                        ->where('transaksi_check.status', 'Pending');
        
        if ($departemenLine) {
            $linesArray = array_map('trim', explode(',', $departemenLine));
            $builder->whereIn('master_mesin.line', $linesArray);
        }
        return $builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }

    public function getPendingOverhaulByRole(?string $sessionLine = null, ?string $sessionDepts = null, ?string $sessionPlant = null): array
    {
        $builder = $this->select('transaksi_check.*, master_mesin.no_mesin as nama_mesin, COALESCE(master_mesin.plant, transaksi_check.plant) as plant, COALESCE(master_mesin.departemen, transaksi_check.departemen_check) as departemen_mesin, COALESCE(master_mesin.line, transaksi_check.line_check) as line_mesin')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);
        
        $builder->groupStart();
        $conditionsAdded = false;

        if (has_role(\App\Enums\Role::Leader->value)) {
            $builder->orGroupStart()
                        ->whereIn('transaksi_check.status', ['Pending', 'Revised']);
            if ($sessionDepts && $sessionDepts !== '-') {
                $deptsArray = array_map('trim', explode(',', $sessionDepts));
                $builder->whereIn('transaksi_check.departemen_check', $deptsArray);
            }
            if ($sessionPlant && $sessionPlant !== '-') {
                $planArray = array_map('trim', explode(',', $sessionPlant));
                $builder->whereIn('master_mesin.plant', $planArray);
            }
            if ($sessionLine && $sessionLine !== '-') {
                $linesArray = array_map('trim', explode(',', $sessionLine));
                $builder->whereIn('master_mesin.line', $linesArray);
            }
            $builder->groupEnd();
            $conditionsAdded = true;
        } 
        
        if (has_role(\App\Enums\Role::Sheadprd->value)) {
            $builder->orGroupStart()
                        ->whereIn('transaksi_check.status', ['Approved L1', 'Revised']);
            if ($sessionDepts && $sessionDepts !== '-') {
                $deptsArray = array_map('trim', explode(',', $sessionDepts));
                $builder->whereIn('transaksi_check.departemen_check', $deptsArray);
            }
            if ($sessionPlant && $sessionPlant !== '-') {
                $planArray = array_map('trim', explode(',', $sessionPlant));
                $builder->whereIn('master_mesin.plant', $planArray);
            }
            if ($sessionLine && $sessionLine !== '-') {
                $linesArray = array_map('trim', explode(',', $sessionLine));
                $builder->whereIn('master_mesin.line', $linesArray);
            }
            $builder->groupEnd();
            $conditionsAdded = true;
        } 
        
        if (has_role(\App\Enums\Role::Sheadmtc->value)) {
            $builder->orGroupStart()
                        ->where('transaksi_check.status', 'Approved L2');
            $builder->groupEnd();
            $conditionsAdded = true;
        }

        if (!$conditionsAdded) {
            return [];
        }

        $builder->groupEnd();
        
        return $builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }

    public function checkDuplicate(int $idMesin, string $jenisCheck, string $bulan, ?string $kategori = null, bool $isSemester = false): array
    {
        $builder = $this->select('id_transaksi, waktu_mulai, created_at, nama_pic, target_periode')
                        ->where('id_mesin', $idMesin)
                        ->where('jenis_check', $jenisCheck);

        if ($isSemester) {
            $year = substr($bulan, 0, 4);
            $month = (int)substr($bulan, 5, 2);
            if ($month <= 6) {
                $builder->where("target_periode >=", "$year-01")
                        ->where("target_periode <=", "$year-06");
            } else {
                $builder->where("target_periode >=", "$year-07")
                        ->where("target_periode <=", "$year-12");
            }
        } else {
            $builder->where('target_periode', $bulan);
        }
                        
        if (!empty($kategori)) {
            $builder->where('kategori', $kategori);
        }
        
        return $builder->orderBy('id_transaksi', 'DESC')->findAll();
    }

    public function getAvailablePics(?string $departemenName = null, ?string $jenisCheck = null): array
    {
        $builder = $this->select('transaksi_check.nama_pic, users.nama as nama_staff')
                        ->join('users', 'users.id = transaksi_check.id_user');
        
        if ($departemenName !== null) {
            $builder->where('transaksi_check.departemen_check', $departemenName);
        }
        if (!empty($jenisCheck)) {
            $builder->where('transaksi_check.jenis_check', $jenisCheck);
        }
        return $builder->distinct()->findAll();
    }

    public function getAvailableBulan(?string $departemenName = null, ?string $jenisCheck = null): array
    {
        $builder = $this->select("COALESCE(NULLIF(target_periode, ''), DATE_FORMAT(waktu_mulai, '%Y-%m')) as bulan", false);
        
        if ($departemenName !== null) {
            $builder->where('departemen_check', $departemenName);
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

    public function getInboxApprovalTransaksi(?string $line = null): array
    {
        $joinDate = 'COALESCE(NULLIF(transaksi_check.target_periode, ""), DATE_FORMAT(transaksi_check.waktu_mulai, "%Y-%m"))';
        $joinCondition = 'riwayat_mesin.id_mesin = transaksi_check.id_mesin AND riwayat_mesin.tanggal_mulai <= LAST_DAY(STR_TO_DATE(CONCAT(' . $joinDate . ', "-01"), "%Y-%m-%d")) AND (riwayat_mesin.tanggal_selesai IS NULL OR riwayat_mesin.tanggal_selesai >= LAST_DAY(STR_TO_DATE(CONCAT(' . $joinDate . ', "-01"), "%Y-%m-%d")))';

        $builder = $this->select('transaksi_check.id_transaksi AS doc_id, transaksi_check.jenis_check, transaksi_check.kategori, transaksi_check.departemen_check, COALESCE(master_mesin.line, riwayat_mesin.line, transaksi_check.line_check) AS line, transaksi_check.nama_pic, users.nama AS nama_staff, transaksi_check.waktu_mulai AS doc_date, transaksi_check.status, master_mesin.no_mesin, COALESCE(master_mesin.plant, transaksi_check.plant) as plant, master_mesin.type_mesin, "transaksi" AS doc_source, NULL AS departemen, NULL AS persen', false)
                        ->join('users', 'users.id = transaksi_check.id_user', 'left')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin', 'left')
                        ->join('riwayat_mesin', $joinCondition, 'left');

        $builder->groupStart();

        $conditionsAdded = false;

        if (has_role(\App\Enums\Role::Leader->value)) {
            $builder->orGroupStart()
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                        ->whereIn('transaksi_check.status', ['Pending', 'Revised']);
            if (($userDepts = session()->get('departemen')) && $userDepts !== '-') {
                $deptsArray = array_map('trim', explode(',', $userDepts));
                $builder->whereIn('transaksi_check.departemen_check', $deptsArray);
            }
            if (($userPlan = session()->get('plant')) && $userPlan !== '-') {
                $planArray = array_map('trim', explode(',', $userPlan));
                $builder->whereIn('master_mesin.plant', $planArray);
            }
            if ($line && $line !== '-') {
                $linesArray = array_map('trim', explode(',', $line));
                $escapedLines = array_map(function($l) { return $this->db->escape($l); }, $linesArray);
                $inClause = implode(',', $escapedLines);
                $builder->where('IF(transaksi_check.jenis_check = "Overhaul", COALESCE(master_mesin.line, transaksi_check.line_check), COALESCE(master_mesin.line, riwayat_mesin.line)) IN (' . $inClause . ')', null, false);
            }
            $builder->groupEnd();
            $conditionsAdded = true;
        } 
        
        if (has_role(\App\Enums\Role::Sheadprd->value)) {
            $builder->orGroupStart()
                        ->whereIn('transaksi_check.jenis_check', [\App\Enums\JenisCheck::Overhaul->value, \App\Enums\JenisCheck::Preventive->value])
                        ->whereIn('transaksi_check.status', ['Approved L1', 'Revised']);
            if (($userDepts = session()->get('departemen')) && $userDepts !== '-') {
                $deptsArray = array_map('trim', explode(',', $userDepts));
                $builder->whereIn('transaksi_check.departemen_check', $deptsArray);
            }
            if (($userPlan = session()->get('plant')) && $userPlan !== '-') {
                $planArray = array_map('trim', explode(',', $userPlan));
                $builder->whereIn('master_mesin.plant', $planArray);
            }
            if (($userLine = session()->get('line')) && $userLine !== '-') {
                $linesArray = array_map('trim', explode(',', $userLine));
                $escapedLines = array_map(function($l) { return $this->db->escape($l); }, $linesArray);
                $inClause = implode(',', $escapedLines);
                $builder->where('IF(transaksi_check.jenis_check = "Overhaul", COALESCE(master_mesin.line, transaksi_check.line_check), COALESCE(master_mesin.line, riwayat_mesin.line)) IN (' . $inClause . ')', null, false);
            }
            $builder->groupEnd();
            $conditionsAdded = true;
        } 
        
        if (has_role(\App\Enums\Role::Sheadmtc->value)) {
            $builder->orGroupStart()
                        ->whereIn('transaksi_check.jenis_check', [\App\Enums\JenisCheck::Overhaul->value, \App\Enums\JenisCheck::Preventive->value])
                        ->where('transaksi_check.status', 'Approved L2');
            $builder->groupEnd();
            $conditionsAdded = true;
        } 
        
        if (has_any_role([\App\Enums\Role::Admin->value, \App\Enums\Role::Member->value, \App\Enums\Role::LeaderMember->value])) {
            $builder->orWhereNotIn('transaksi_check.status', ['Approved', 'Final', 'Approved Final']);
            $conditionsAdded = true;
        }

        if (!$conditionsAdded) {
            return [];
        }

        $builder->groupEnd();

        return $builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }

    public function getPercentageSummary(?array $filters = null): array
    {
        $db = \Config\Database::connect();
        
        // 1. Tentukan Basis Total Mesin (Difilter jika ada)
        $mesinBuilder = $db->table('master_mesin m');
        
        if (!empty($filters['line']) && $filters['line'] !== '-') {
            $linesArray = array_map('trim', explode(',', $filters['line']));
            $mesinBuilder->whereIn('m.line', $linesArray);
        }
        if (!empty($filters['departemen']) && $filters['departemen'] !== '-') {
            $deptsArray = array_map('trim', explode(',', $filters['departemen']));
            $mesinBuilder->whereIn('m.departemen', $deptsArray);
        }
        if (!empty($filters['plant']) && $filters['plant'] !== '-') {
            $planArray = array_map('trim', explode(',', $filters['plant']));
            $mesinBuilder->whereIn('m.plant', $planArray);
        }
        if (!empty($filters['id_mesin'])) {
            $mesinBuilder->where('m.id_mesin', $filters['id_mesin']);
        }
        
        $totalMesinPreventive = (int) $mesinBuilder->countAllResults(false);

        // Hitung total mesin overhaul per plant
        $mesinOvP1 = clone $mesinBuilder;
        $mesinOvP2 = clone $mesinBuilder;
        $totalMesinOvP1 = (int) $mesinOvP1->whereNotIn('m.jenis', ['-', 'CAM'])->where('m.plant', 'Plant 1')->countAllResults(false);
        $totalMesinOvP2 = (int) $mesinOvP2->whereNotIn('m.jenis', ['-', 'CAM'])->where('m.plant', 'Plant 2')->countAllResults(false);
        $totalMesinOverhaul = $totalMesinOvP1 + $totalMesinOvP2;
        
        // Tentukan Periode Waktu Preventive
        $bulanSekarang = !empty($filters['bulan']) && $filters['bulan'] !== 'all' ? $filters['bulan'] : date('Y-m');
        
        // Ambil siklus aktif per plant dari tabel periode_overhaul
        $periodeModel = new \App\Models\PeriodeOverhaulModel();
        $siklusP1 = $periodeModel->getAktif('Plant 1');
        $siklusP2 = $periodeModel->getAktif('Plant 2');

        $mulaiP1 = $siklusP1 ? $siklusP1['tanggal_mulai'] : date('Y-m-d', strtotime('first day of this year'));
        $mulaiP2 = $siklusP2 ? $siklusP2['tanggal_mulai'] : date('Y-m-d', strtotime('first day of this year'));
        $today = date('Y-m-d');
        
        if ($totalMesinPreventive === 0 && $totalMesinOverhaul === 0) {
            return [
                'total_mesin'          => 0,
                'total_mesin_overhaul' => 0,
                'bulan'                => $bulanSekarang,
                'periode_plant1'       => 'Belum ada siklus',
                'periode_plant2'       => 'Belum ada siklus',
                'tanggal_mulai_plant1' => null,
                'tanggal_mulai_plant2' => null,
                'preventive'           => ['checked' => 0, 'coverage' => 0, 'normal' => 0, 'abnormal' => 0, 'normal_count' => 0, 'abnormal_count' => 0],
                'overhaul_plant1'      => ['checked' => 0, 'coverage' => 0, 'normal' => 0, 'abnormal' => 0, 'normal_count' => 0, 'abnormal_count' => 0],
                'overhaul_plant2'      => ['checked' => 0, 'coverage' => 0, 'normal' => 0, 'abnormal' => 0, 'normal_count' => 0, 'abnormal_count' => 0],
            ];
        }

        // Fungsi Helper untuk mengambil status
        $getStats = function(string $jenis, string $periodeStart, string $periodeEnd, int $totalTarget, ?string $plant = null) use ($db, $filters) {
            $builder = $db->table('transaksi_check t')
                          ->select('t.id_mesin, t.kategori, m.jenis,
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
            
            if ($plant !== null) {
                $builder->where('m.plant', $plant);
                // Overhaul hanya untuk mesin yang punya jenis (bukan -)
                $builder->whereNotIn('m.jenis', ['-', 'CAM']);
            }

            // Terapkan Filter Tambahan (Line, Departemen, Mesin)
            if (!empty($filters['line']) && $filters['line'] !== '-') {
                $linesArray = array_map('trim', explode(',', $filters['line']));
                $builder->whereIn('m.line', $linesArray);
            }
            if (!empty($filters['departemen']) && $filters['departemen'] !== '-') {
                $deptsArray = array_map('trim', explode(',', $filters['departemen']));
                $builder->whereIn('m.departemen', $deptsArray);
            }
            if ($plant === null && !empty($filters['plant']) && $filters['plant'] !== '-') {
                $planArray = array_map('trim', explode(',', $filters['plant']));
                $builder->whereIn('m.plant', $planArray);
            }
            if (!empty($filters['id_mesin'])) {
                $builder->where('t.id_mesin', $filters['id_mesin']);
            }
            
            // Logika Periode — untuk overhaul gunakan DATE range aktual (waktu_mulai)
            if ($jenis === 'Overhaul') {
                // Range dari tanggal_mulai siklus aktif hingga hari ini
                $builder->where("DATE(t.waktu_mulai) >= '$periodeStart'")
                        ->where("DATE(t.waktu_mulai) <= '$periodeEnd'");
            } else {
                // Preventive: periode Y-m
                if ($periodeStart === $periodeEnd) {
                    $builder->groupStart()
                                ->where('t.target_periode', $periodeStart)
                                ->orGroupStart()
                                    ->where('(t.target_periode IS NULL OR t.target_periode = "")', null, false)
                                    ->like('t.waktu_mulai', $periodeStart, 'after')
                                ->groupEnd()
                            ->groupEnd();
                } else {
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
            }

            $builder->orderBy('t.id_transaksi', 'ASC');
            $results = $builder->get()->getResultArray();

            $mesinUnik = [];
            foreach ($results as $row) {
                if (!isset($mesinUnik[$row['id_mesin']])) {
                    $mesinUnik[$row['id_mesin']] = [
                        'jenis' => $row['jenis'] ?? '',
                        'kategori' => [],
                        'kondisi' => 'V'
                    ];
                }
                
                // Tambahkan kategori unik
                if (!in_array($row['kategori'], $mesinUnik[$row['id_mesin']]['kategori'])) {
                    $mesinUnik[$row['id_mesin']]['kategori'][] = $row['kategori'];
                }
                
                // Hitung kondisi gabungan
                if ($row['kondisi'] === 'Δ') {
                    $mesinUnik[$row['id_mesin']]['kondisi'] = 'Δ';
                } else if ($row['kondisi'] === 'X' && $mesinUnik[$row['id_mesin']]['kondisi'] !== 'Δ') {
                    $mesinUnik[$row['id_mesin']]['kondisi'] = 'X';
                }
            }

            $checked  = 0;
            $normal   = 0;
            $abnormal = 0;
            $tidakAda = 0;
            
            foreach ($mesinUnik as $idMesin => $data) {
                $isCam = (strcasecmp($data['jenis'], 'CAM') === 0);
                
                if ($jenis === 'Overhaul' || count($data['kategori']) > 0) {
                    $checked++;
                    if ($data['kondisi'] === 'V') {
                        $normal++;
                    } elseif ($data['kondisi'] === 'Δ') {
                        $abnormal++;
                    } else if ($data['kondisi'] === 'X') {
                        $tidakAda++;
                    }
                }
            }

            return [
                'checked'         => $checked,
                'coverage'        => $totalTarget > 0 ? round(($checked / $totalTarget) * 100, 1) : 0,
                'normal'          => $checked > 0 ? round(($normal / $checked) * 100, 1) : 0,
                'abnormal'        => $checked > 0 ? round(($abnormal / $checked) * 100, 1) : 0,
                'normal_count'    => $normal,
                'abnormal_count'  => $abnormal,
                'tidak_ada_count' => $tidakAda
            ];
        };

        // Label periode untuk ditampilkan di UI
        $labelP1 = 'Mulai ' . (new \CodeIgniter\I18n\Time($mulaiP1))->toLocalizedString('d MMM yyyy');
        $labelP2 = 'Mulai ' . (new \CodeIgniter\I18n\Time($mulaiP2))->toLocalizedString('d MMM yyyy');

        return [
            'total_mesin'          => $totalMesinPreventive,
            'total_mesin_overhaul' => $totalMesinOverhaul,
            'total_mesin_ov_p1'    => $totalMesinOvP1,
            'total_mesin_ov_p2'    => $totalMesinOvP2,
            'preventive'           => $getStats('Preventive', $bulanSekarang, $bulanSekarang, $totalMesinPreventive),
            'overhaul_plant1'      => $getStats('Overhaul', $mulaiP1, $today, $totalMesinOvP1, 'Plant 1'),
            'overhaul_plant2'      => $getStats('Overhaul', $mulaiP2, $today, $totalMesinOvP2, 'Plant 2'),
            'bulan'                => $bulanSekarang,
            'periode_plant1'       => $labelP1,
            'tanggal_mulai_plant1' => $mulaiP1,
            'periode_plant2'       => $labelP2,
            'tanggal_mulai_plant2' => $mulaiP2,
        ];
    }
}
