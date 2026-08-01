<?php

namespace App\Models;

use CodeIgniter\Model;

class CeklisKontrolModel extends Model
{
    protected $table         = 'ceklis_kontrol';
    protected $primaryKey    = 'id_kontrol';
    protected $allowedFields = [
        'id_mesin',
        'kategori',
        'bulan_tahun',
        'periode_ke',
        'status_check',
        'pic_nama',
        'out_of_plan',
        'ulasan',
        'tanggal_check'
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    /**
     * Mengambil data Checklist Control untuk lokasi, kategori, dan bulan tertentu.
     * Mengembalikan data yang distrukturkan per mesin lengkap dengan data periode 1-5.
     */
    public function getGridData(string $lokasi, string $kategori, string $bulanTahun, ?string $line = null): array
    {
        // 1. Ambil semua mesin untuk lokasi ini
        $mesinModel = new MesinModel();
        $builder = $mesinModel->where('lokasi', $lokasi);
        if ($line) {
            $builder->where('line', $line);
        }
        $daftarMesin = $builder->orderBy('no_mesin', 'ASC')->findAll();

        // 2. Ambil semua catatan Checklist Control untuk kategori & bulan ini
        $records = $this->where('kategori', $kategori)
                        ->where('bulan_tahun', $bulanTahun)
                        ->findAll();

        // Map records by [id_mesin][periode_ke] untuk akses instan
        $mapped = [];
        foreach ($records as $r) {
            $mapped[$r['id_mesin']][$r['periode_ke']] = $r;
        }

        // 3. Gabungkan data mesin dengan data Checklist Control periode 1 s.d 5
        $grid = [];
        foreach ($daftarMesin as $m) {
            $idMesin = (int) $m['id_mesin'];
            $row = [
                'mesin' => $m,
                'periodes' => []
            ];

            // Inisialisasi default ulasan dan out_of_plan global bulanan (diambil dari periode terbaru jika ada)
            $rowUlasan = '';
            $rowOutOfPlan = null;
            $rowPic = '';

            for ($p = 1; $p <= 5; $p++) {
                if (isset($mapped[$idMesin][$p])) {
                    $rec = $mapped[$idMesin][$p];
                    $row['periodes'][$p] = [
                        'id_kontrol'   => (int) $rec['id_kontrol'],
                        'status_check' => $rec['status_check'],
                        'pic_nama'     => $rec['pic_nama'],
                        'out_of_plan'  => $rec['out_of_plan'],
                        'ulasan'       => $rec['ulasan'],
                        'tanggal_check'=> $rec['tanggal_check'],
                    ];
                    
                    // Ambil PIC, out_of_plan, ulasan terisi untuk data ringkasan baris
                    if (!empty($rec['pic_nama'])) {
                        $rowPic = $rec['pic_nama'];
                    }
                    if (!empty($rec['ulasan'])) {
                        $rowUlasan = $rec['ulasan'];
                    }
                    if (!empty($rec['out_of_plan'])) {
                        $rowOutOfPlan = $rec['out_of_plan']; // Tanggal Realita
                    }
                } else {
                    $row['periodes'][$p] = null;
                }
            }

            $row['pic_nama']    = $rowPic ?: 'PIC';
            $row['out_of_plan'] = $rowOutOfPlan; // date or null
            $row['ulasan']      = $rowUlasan ?: '';

            // Fetch abnormal photos for this machine in this month
            $db = \Config\Database::connect();
            $photos = $db->table('laporan_abnormal')
                         ->select('laporan_abnormal.foto_abnormal, laporan_abnormal.foto_abnormal_2')
                         ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi')
                         ->where('laporan_abnormal.id_mesin', $idMesin)
                         ->where('transaksi_check.kategori', $kategori)
                         ->like('laporan_abnormal.pengecekan_tanggal', $bulanTahun, 'after') // YYYY-MM
                         ->get()->getResultArray();
            $row['photos'] = [];
            foreach ($photos as $ph) {
                if (!empty($ph['foto_abnormal']) && !in_array($ph['foto_abnormal'], $row['photos'])) $row['photos'][] = $ph['foto_abnormal'];
                if (!empty($ph['foto_abnormal_2']) && !in_array($ph['foto_abnormal_2'], $row['photos'])) $row['photos'][] = $ph['foto_abnormal_2'];
            }

            $grid[] = $row;
        }

        return $grid;
    }

    /**
     * Get Checklist Control ready for Leader approval (100% checked, but not approved yet)
     */
    public function getPendingApprovalsForLeader(string $lokasi, string $line, string $bulanTahun): array
    {
        $db = \Config\Database::connect();
        
        // Define categories based on lokasi (same logic as in KontrolController)
        $categories = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'];
        if ($lokasi !== 'MFG 2') {
            $categories = array_merge($categories, ['Bearing Cam', 'Gearbox', 'Belt Cam']);
        }

        $pendingList = [];

        foreach ($categories as $kategori) {
            $grid = $this->getGridData($lokasi, $kategori, $bulanTahun, $line);
            
            // Skip if there are no machines
            if (empty($grid)) {
                continue;
            }

            // Check if 100% completed
            $allChecked = true;
            foreach ($grid as $row) {
                if ($row['pic_nama'] === 'PIC') {
                    $allChecked = false;
                    break;
                }
            }

            if ($allChecked) {
                // Check if already approved by leader
                $approval = $db->table('approval_bulanan')
                               ->where('type', 'kontrol')
                               ->where('lokasi', $lokasi)
                               ->where('line', $line)
                               ->where('kategori', $kategori)
                               ->where('bulan_tahun', $bulanTahun)
                               ->get()
                               ->getRowArray();

                if (!$approval || $approval['status'] === 'Pending') {
                    $pendingList[] = [
                        'lokasi'      => $lokasi,
                        'line'        => $line,
                        'kategori'    => $kategori,
                        'bulan_tahun' => $bulanTahun,
                        'status'      => 'Siap Approve'
                    ];
                }
            }
        }

        return $pendingList;
    }

    /**
     * Get Checklist Control ready for Section Head approval
     */
    public function getPendingApprovalsForSHead(string $role, string $bulanTahun = null): array
    {
        $db = \Config\Database::connect();
        $builder = $db->table('approval_bulanan')->where('type', 'kontrol');
        
        if ($role === 'section_head_produksi') {
            $builder->where('status', 'Approved L1');
        } elseif ($role === 'section_head_mtc') {
            $builder->where('status', 'Approved L2');
        } else {
            return []; // Other roles don't have SH pending approvals
        }

        // Optional: Filter by month if needed. Usually dashboard shows all pending.
        if ($bulanTahun) {
            $builder->where('bulan_tahun', $bulanTahun);
        }

        return $builder->get()->getResultArray();
    }

    public function updateChecklistKontrol(int $idMesin, string $kategori, string $tanggalCheck, array $data): bool
    {
        return $this->where('id_mesin', $idMesin)
                    ->where('kategori', $kategori)
                    ->where('tanggal_check', $tanggalCheck)
                    ->set($data)
                    ->update();
    }

    public function findChecklistKontrol(int $idMesin, string $kategori, string $bulanTahun, ?int $periodeKe): ?array
    {
        return $this->where('id_mesin', $idMesin)
                    ->where('kategori', $kategori)
                    ->where('bulan_tahun', $bulanTahun)
                    ->where('periode_ke', $periodeKe)
                    ->first();
    }
}
