<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiCheckModel extends Model
{
    protected $table         = 'transaksi_check';
    protected $primaryKey    = 'id_transaksi';
    protected $allowedFields = [
        'id_user', 'nama_pic', 'id_mesin', 'lokasi_check', 'jenis_check', 'kategori',
        'waktu_mulai', 'waktu_selesai', 'status', 'approved_by', 'pic_line_nama', 'approved_at',
        'approval_l1_by', 'leader_nama', 'approval_l1_at', 'approval_l2_by', 'approval_l2_at',
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
        $builder = $this->select('transaksi_check.*, users.nama as nama_staff, approver.nama as approver_nama, master_mesin.no_mesin, master_mesin.type_mesin')
                         ->join('users', 'users.id = transaksi_check.id_user')
                         ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                         ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
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
    public function getRiwayatFiltered(array $filters = [], ?int $userId = null, ?int $limit = null): array
    {
        $builder = $this->select('transaksi_check.*, users.nama as nama_staff, approver.nama as approver_nama, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.line as line')
                         ->join('users', 'users.id = transaksi_check.id_user')
                         ->join('users as approver', 'approver.id = transaksi_check.approved_by', 'left')
                         ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin');

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

        if (!empty($filters['line'])) {
            $builder->where('master_mesin.line', $filters['line']);
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
            $builder->like('transaksi_check.waktu_mulai', $filters['bulan'], 'after');
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

        return $builder->findAll();
    }

    /**
     * Header transaksi + info staff & mesin, untuk halaman detail riwayat.
     */
    public function getDetailTransaksi(int $idTransaksi): ?array
    {
        return $this->select('transaksi_check.*, users.nama as nama_staff, approver.nama as approver_nama, approver_l1.nama as approver_l1_nama, approver_l2.nama as approver_l2_nama, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.serial_nomor, transaksi_overhaul.bar_feeder_type, transaksi_overhaul.support_pic, transaksi_overhaul.note_recommendation')
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
    public function getLaporanDurasi(array $filters = []): array
    {
        $builder = $this->select("transaksi_check.*, users.nama as nama_staff, approver.nama as approver_nama, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.line, master_mesin.lokasi as lokasi_mesin, TIMESTAMPDIFF(SECOND, transaksi_check.waktu_mulai, transaksi_check.waktu_selesai) as durasi_detik, transaksi_overhaul.bar_feeder_type, transaksi_overhaul.support_pic, transaksi_overhaul.note_recommendation")
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
            $builder->like('transaksi_check.waktu_mulai', $filters['bulan'], 'after');
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

        return $builder->orderBy($sortField, $order)
                       ->findAll();
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
        $builder = $this->select('id_transaksi, waktu_mulai, created_at, nama_pic')
                        ->where('id_mesin', $idMesin)
                        ->where('jenis_check', $jenisCheck)
                        ->where("DATE_FORMAT(created_at, '%Y-%m')", $bulan);
                        
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
        $builder = $this->select("DATE_FORMAT(waktu_mulai, '%Y-%m') as bulan", false);
        
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
            $builder->like('waktu_mulai', $bulan, 'after');
        }
        return $builder->orderBy('id_transaksi', 'DESC')->first();
    }
}
