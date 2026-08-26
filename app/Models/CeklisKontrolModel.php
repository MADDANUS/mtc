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
        'tanggal_check',
        'plant',
        'departemen',
        'line',
        'ss_no_mesin',
        'ss_type_mesin'
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    /**
     * Mengambil data Checklist Control untuk departemen, kategori, dan bulan tertentu.
     * Mengembalikan data yang distrukturkan per mesin lengkap dengan data periode 1-5.
     */
    public function getGridData(string $departemen, string $kategori, string $bulanTahun, ?string $line = null): array
    {
        // 1. Ambil semua mesin untuk departemen dan bulan ini dari tabel riwayat_mesin
        $mesinModel = new MesinModel();
        $builder = $mesinModel->select('master_mesin.*, riwayat_mesin.departemen as r_lokasi, riwayat_mesin.line as r_line, riwayat_mesin.plant as r_plant')
                              ->join('riwayat_mesin', 'master_mesin.id_mesin = riwayat_mesin.id_mesin')
                              ->where('riwayat_mesin.departemen', $departemen);
        
        if ($line) {
            $linesArray = array_map('trim', explode(',', $line));
            $builder->whereIn('riwayat_mesin.line', $linesArray);
        }

        $firstDayOfMonth = $bulanTahun . '-01';
        $lastDayOfMonth = date('Y-m-t', strtotime($firstDayOfMonth));

        $builder->groupStart()
                ->where('riwayat_mesin.tanggal_mulai <=', $lastDayOfMonth)
                ->groupStart()
                    ->where('riwayat_mesin.tanggal_selesai IS NULL')
                    ->orWhere('riwayat_mesin.tanggal_selesai >=', $firstDayOfMonth)
                ->groupEnd()
                ->groupEnd();

        $daftarMesin = $builder->orderBy('master_mesin.no_mesin', 'ASC')->findAll();

        // Timpa field departemen, plan, dan line dengan historisnya agar display grid akurat
        foreach ($daftarMesin as &$m) {
            $m['departemen'] = $m['r_lokasi'];
            $m['line'] = $m['r_line'];
            $m['plant'] = $m['r_plant'] ?? $m['plant'];
        }
        unset($m); // Mencegah bug pass-by-reference pada foreach berikutnya

        // 2. Ambil semua catatan Checklist Control untuk kategori & bulan ini
        $records = $this->where('kategori', $kategori)
                        ->where('bulan_tahun', $bulanTahun)
                        ->findAll();

        // 2.5 Kumpulkan data mesin yatim piatu (orphaned) ke dalam daftarMesin
        $orphanedMachines = [];
        foreach ($records as $r) {
            if (empty($r['id_mesin']) && !empty($r['ss_no_mesin']) && $r['departemen'] === $departemen) {
                // Filter by line if provided
                if ($line) {
                    $linesArray = array_map('trim', explode(',', $line));
                    if (!in_array($r['line'], $linesArray)) {
                        continue;
                    }
                }
                
                $noMesin = $r['ss_no_mesin'];
                if (!isset($orphanedMachines[$noMesin])) {
                    $orphanedMachines[$noMesin] = [
                        'id_mesin' => 'orphaned_' . $noMesin,
                        'no_mesin' => $noMesin,
                        'type_mesin' => $r['ss_type_mesin'],
                        'departemen' => $r['departemen'],
                        'line' => $r['line'],
                        'plant' => $r['plant'],
                    ];
                }
            }
        }
        foreach ($orphanedMachines as $om) {
            $daftarMesin[] = $om;
        }

        // Map records by [id_mesin][periode_ke] untuk akses instan
        $mapped = [];
        foreach ($records as $r) {
            $id = !empty($r['id_mesin']) ? $r['id_mesin'] : ('orphaned_' . $r['ss_no_mesin']);
            $mapped[$id][$r['periode_ke']] = $r;
        }

        // 3. Gabungkan data mesin dengan data Checklist Control periode 1 s.d 5
        $grid = [];
        foreach ($daftarMesin as $m) {
            $idMesin = $m['id_mesin'];
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
            // We must only fetch photos from the LATEST transaction for this month,
            // because that is what is displayed when clicking "Detail Laporan".
            $transaksiModel = new \App\Models\TransaksiCheckModel();
            $latestTx = $transaksiModel->getLatestIdByMesinAndKategori($idMesin, $kategori, $bulanTahun);
            
            $row['photos'] = [];
            
            if ($latestTx) {
                $db = \Config\Database::connect();
                $photos = $db->table('laporan_abnormal')
                             ->select('foto_abnormal, foto_abnormal_2')
                             ->where('id_transaksi', $latestTx['id_transaksi'])
                             ->get()->getResultArray();
                             
                foreach ($photos as $ph) {
                    if (!empty($ph['foto_abnormal']) && !in_array($ph['foto_abnormal'], $row['photos'])) $row['photos'][] = $ph['foto_abnormal'];
                    if (!empty($ph['foto_abnormal_2']) && !in_array($ph['foto_abnormal_2'], $row['photos'])) $row['photos'][] = $ph['foto_abnormal_2'];
                }
            }
            // Fallback for orphaned machines (if no latestTx found because id_mesin is null, we can try by ss_no_mesin later, but usually orphaned means no new transactions anyway)

            $grid[] = $row;
        }

        return $grid;
    }

    /**
     * Get Checklist Control ready for Leader approval (100% checked, but not approved yet)
     */
    public function getPendingApprovalsForLeader(string $departemen, string $linesStr, string $bulanTahun): array
    {
        $db = \Config\Database::connect();
        
        // Define categories based on departemen
        $categories = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'];
        if ($departemen !== 'MFG 2') {
            $categories = array_merge($categories, ['Bearing Cam', 'Gearbox Cam', 'Belt Cam']);
        }

        $pendingList = [];
        $lines = array_map('trim', explode(',', $linesStr));

        foreach ($lines as $singleLine) {
            foreach ($categories as $kategori) {
                $grid = $this->getGridData($departemen, $kategori, $bulanTahun, $singleLine);
                
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
                                   ->where('departemen', $departemen)
                                   ->where('line', $singleLine)
                                   ->where('kategori', $kategori)
                                   ->where('bulan_tahun', $bulanTahun)
                                   ->get()
                                   ->getRowArray();

                    if (!$approval || $approval['status'] === 'Pending') {
                        $pendingList[] = [
                            'departemen'  => $departemen,
                            'line'        => $singleLine,
                            'kategori'    => $kategori,
                            'bulan_tahun' => $bulanTahun,
                            'status'      => 'Siap Approve'
                        ];
                    }
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

    public function updateChecklistKontrol($idMesin, string $kategori, string $tanggalCheck, array $data): bool
    {
        return $this->where('id_mesin', $idMesin)
                    ->where('kategori', $kategori)
                    ->where('tanggal_check', $tanggalCheck)
                    ->set($data)
                    ->update();
    }

    public function findChecklistKontrol($idMesin, string $kategori, string $bulanTahun, ?int $periodeKe): ?array
    {
        return $this->where('id_mesin', $idMesin)
                    ->where('kategori', $kategori)
                    ->where('bulan_tahun', $bulanTahun)
                    ->where('periode_ke', $periodeKe)
                    ->first();
    }

    public function getCheckedMachinesCount(?string $bulanTahun = null): array
    {
        $sql = "
            SELECT 
                r.plant,
                r.departemen, 
                r.line, 
                c.kategori, 
                c.bulan_tahun, 
                COUNT(DISTINCT c.id_mesin) as checked_count
            FROM ceklis_kontrol c
            JOIN riwayat_mesin r ON r.id_mesin = c.id_mesin JOIN master_mesin m ON m.id_mesin = r.id_mesin
                AND r.tanggal_mulai <= LAST_DAY(STR_TO_DATE(CONCAT(c.bulan_tahun, '-01'), '%Y-%m-%d'))
                AND (r.tanggal_selesai IS NULL OR r.tanggal_selesai >= LAST_DAY(STR_TO_DATE(CONCAT(c.bulan_tahun, '-01'), '%Y-%m-%d')))
            WHERE c.pic_nama != 'PIC' AND c.pic_nama IS NOT NULL
        ";
        
        $params = [];
        if ($bulanTahun) {
            $sql .= " AND c.bulan_tahun = ?";
            $params[] = $bulanTahun;
        }
        
        $sql .= " GROUP BY c.bulan_tahun, r.plant, r.departemen, r.line, c.kategori";
        
        return $this->db->query($sql, $params)->getResultArray();
    }
}
