<?php

namespace App\Controllers;

use App\Models\TransaksiCheckModel;
use App\Models\MesinModel;

class ApprovalController extends BaseController
{
    /**
     * GET /approval
     * Halaman Inbox Approval — menampilkan:
     *  - Ceklis Kontrol "Belum Selesai" (sedang diisi, belum 100%)
     *  - Ceklis Kontrol "Pending/Approved L1/L2" (menunggu approval)
     *  - Checklist Report & Inspection Report yang butuh approval
     * History hanya menampilkan yang sudah Approved Final.
     */
    public function index()
    {
        $role   = session()->get('role');
        $userId = session()->get('user_id');
        $line   = session()->get('line');

        $db = \Config\Database::connect();

        // ─── 1. CHECKLIST REPORT & INSPECTION REPORT (transaksi_check) ──────────
        $txBuilder = $db->table('transaksi_check tc')
            ->select('
                tc.id_transaksi  AS doc_id,
                tc.jenis_check,
                tc.kategori,
                tc.lokasi_check,
                mm.line,
                tc.nama_pic,
                u.nama           AS nama_staff,
                tc.waktu_mulai   AS doc_date,
                tc.status,
                mm.no_mesin,
                mm.type_mesin,
                "transaksi"      AS doc_source,
                NULL             AS lokasi,
                NULL             AS persen
            ', false)
            ->join('users u', 'u.id = tc.id_user', 'left')
            ->join('master_mesin mm', 'mm.id_mesin = tc.id_mesin', 'left');

        // Filter berdasarkan role
        if ($role === 'leader') {
            $txBuilder->where('tc.jenis_check', 'Overhaul')
                      ->where('tc.status', 'Pending');
            if ($line) {
                $txBuilder->where('mm.line', $line);
            }
        } elseif ($role === 'sheadprd') {
            $txBuilder->whereIn('tc.jenis_check', ['Overhaul', 'Preventive'])
                      ->where('tc.status', 'Approved L1');
        } elseif ($role === 'sheadmtc') {
            $txBuilder->whereIn('tc.jenis_check', ['Overhaul', 'Preventive'])
                      ->where('tc.status', 'Approved L2');
        } elseif ($role === 'member') {
            $txBuilder->groupStart()
                        ->groupStart()
                            ->where('tc.jenis_check', 'Preventive')
                            ->where('tc.status', 'Pending')
                        ->groupEnd()
                        ->orGroupStart()
                            ->where('tc.jenis_check', 'Overhaul')
                            ->whereIn('tc.status', ['Pending', 'Approved L1', 'Approved L2'])
                        ->groupEnd()
                      ->groupEnd();
        } elseif ($role === 'admin') {
            $txBuilder->whereNotIn('tc.status', ['Approved']);
        } else {
            return redirect()->to('/dashboard')->with('error', 'Akses tidak diizinkan.');
        }

        $transaksiRows = $txBuilder->orderBy('tc.waktu_mulai', 'DESC')->get()->getResultArray();

        // ─── 2. CHECKLIST CONTROL BULANAN ────────────────────────────────────────
        // Bagian A: yang sudah ada di approval_bulanan (Pending, Approved L1/L2)
        $kontrolRows = [];

        if (in_array($role, ['member', 'admin', 'sheadprd', 'sheadmtc'], true)) {

            // -- A. Kontrol yang sudah di-submit ke approval_bulanan (ada status Pending/L1/L2) --
            $kontrolBuilder = $db->table('approval_bulanan ab')
                ->select('
                    ab.id_approval   AS doc_id,
                    ab.type          AS jenis_check,
                    ab.kategori,
                    ab.lokasi,
                    ab.line,
                    ab.bulan_tahun   AS doc_date,
                    ab.status,
                    "kontrol"        AS doc_source,
                    NULL             AS lokasi_check,
                    NULL             AS nama_pic,
                    NULL             AS nama_staff,
                    NULL             AS no_mesin,
                    NULL             AS type_mesin,
                    NULL             AS persen
                ', false);

            if ($role === 'sheadprd') {
                $kontrolBuilder->where('ab.status', 'Approved L1');
            } elseif ($role === 'sheadmtc') {
                $kontrolBuilder->where('ab.status', 'Approved L2');
            } elseif (in_array($role, ['member', 'admin'])) {
                // Tampilkan semua kecuali yang sudah Final (Final → masuk History)
                $kontrolBuilder->whereNotIn('ab.status', ['Final', 'Approved Final']);
            }

            $approvalRows = $kontrolBuilder->orderBy('ab.bulan_tahun', 'DESC')->get()->getResultArray();

            // -- B. Kontrol yang BELUM SELESAI diisi (belum ada di approval_bulanan) --
            // Hanya untuk role member & admin — agar bisa membuka dan melanjutkan pengisian
            $belumSelesaiRows = [];
            if (in_array($role, ['member', 'admin'], true)) {

                // Ambil semua kombinasi lokasi/line/kategori/bulan yang sudah ada di ceklis_kontrol bulan ini
                $bulanIni = date('Y-m');

                // Total mesin per lokasi/line
                $totalMesinData = $db->table('master_mesin')
                    ->select('lokasi, line, COUNT(id_mesin) AS total')
                    ->groupBy('lokasi, line')
                    ->get()->getResultArray();
                $totalMesinMap = [];
                foreach ($totalMesinData as $tm) {
                    $totalMesinMap[$tm['lokasi']][$tm['line']] = (int) $tm['total'];
                }

                // Kategori per lokasi
                $kategoriByLokasi = [
                    'MFG 1' => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'],
                    'MFG 2' => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'],
                ];

                // Mesin yang sudah dicek bulan ini per lokasi/line/kategori
                $checkedData = $db->table('ceklis_kontrol ck')
                    ->select('mm.lokasi, mm.line, ck.kategori, COUNT(DISTINCT ck.id_mesin) AS checked')
                    ->join('master_mesin mm', 'mm.id_mesin = ck.id_mesin')
                    ->where('ck.bulan_tahun', $bulanIni)
                    ->where("ck.pic_nama != 'PIC'")
                    ->where("ck.pic_nama IS NOT NULL")
                    ->groupBy('mm.lokasi, mm.line, ck.kategori')
                    ->get()->getResultArray();
                $checkedMap = [];
                foreach ($checkedData as $cd) {
                    $checkedMap[$cd['lokasi']][$cd['line']][$cd['kategori']] = (int) $cd['checked'];
                }

                // Ambil daftar id_approval yang sudah ada untuk bulan ini (untuk hindari duplikat)
                $existingApprovals = $db->table('approval_bulanan')
                    ->select('lokasi, line, kategori')
                    ->where('type', 'kontrol')
                    ->where('bulan_tahun', $bulanIni)
                    ->get()->getResultArray();
                $approvedSet = [];
                foreach ($existingApprovals as $ea) {
                    $approvedSet[$ea['lokasi']][$ea['line']][$ea['kategori']] = true;
                }

                // Bangun list "Belum Selesai"
                foreach ($kategoriByLokasi as $lok => $kats) {
                    if (!isset($totalMesinMap[$lok])) continue;
                    foreach ($totalMesinMap[$lok] as $ln => $totalMesin) {
                        foreach ($kats as $kat) {
                            // Sudah ada di approval_bulanan? Lewati, sudah ditangani di bagian A
                            if (isset($approvedSet[$lok][$ln][$kat])) continue;

                            $checked = $checkedMap[$lok][$ln][$kat] ?? 0;
                            $persen = $totalMesin > 0 ? round(($checked / $totalMesin) * 100) : 0;

                            // Hanya tampilkan jika ada data yang diisi (ada setidaknya 1 mesin tercek)
                            // atau kalau persen < 100 (belum selesai)
                            // Kalau 0% dan tidak ada isian sama sekali, tidak perlu ditampilkan
                            if ($checked === 0) continue;

                            $belumSelesaiRows[] = [
                                'doc_id'      => null,
                                'jenis_check' => 'kontrol',
                                'kategori'    => $kat,
                                'lokasi'      => $lok,
                                'line'        => $ln,
                                'doc_date'    => $bulanIni,
                                'status'      => 'Belum Selesai',
                                'doc_source'  => 'kontrol',
                                'lokasi_check'=> null,
                                'nama_pic'    => null,
                                'nama_staff'  => null,
                                'no_mesin'    => null,
                                'type_mesin'  => null,
                                'persen'      => $persen,
                            ];
                        }
                    }
                }
            }

            $kontrolRows = array_merge($approvalRows, $belumSelesaiRows);
        }

        // ─── 3. Gabungkan & apply filter GET ──────────────────────────────────────
        $allDocs = array_merge($transaksiRows, $kontrolRows);

        // Ekstrak data unik untuk dropdown
        $uniqueLokasi = [];
        $uniqueKategori = [];
        $uniqueMesin = [];
        foreach ($allDocs as $doc) {
            $loc = $doc['lokasi_check'] ?? $doc['lokasi'] ?? '';
            $line = $doc['line'] ?? '';
            if (!empty($loc)) {
                $uniqueLokasi[$loc] = true;
            }
            if (!empty($line)) {
                $uniqueLokasi[$line] = true;
            }
            
            $kat = $doc['kategori'] ?? '';
            if (!empty($kat)) $uniqueKategori[$kat] = true;
            
            $mesinNo = $doc['no_mesin'] ?? '';
            $mesinType = $doc['type_mesin'] ?? '';
            if (!empty($mesinNo)) {
                $mesinLabel = $mesinNo . (!empty($mesinType) ? ' - ' . $mesinType : '');
                $uniqueMesin[$mesinNo] = $mesinLabel;
            }
        }
        $uniqueLokasi = array_keys($uniqueLokasi);
        $uniqueKategori = array_keys($uniqueKategori);
        asort($uniqueLokasi);
        asort($uniqueKategori);
        asort($uniqueMesin);

        $filterJenis    = $this->request->getGet('jenis') ?: null;
        $filterBulan    = $this->request->getGet('bulan') ?: null;
        $filterStatus   = $this->request->getGet('status') ?: null;
        $filterLokasi   = $this->request->getGet('lokasi') ?: null;
        $filterKategori = $this->request->getGet('kategori') ?: null;
        $filterMesin    = $this->request->getGet('mesin') ?: null;

        $filtered = array_filter($allDocs, function($row) use ($filterJenis, $filterBulan, $filterStatus, $filterLokasi, $filterKategori, $filterMesin) {
            if ($filterJenis && $filterJenis !== 'all') {
                $jenis = $row['jenis_check'] ?? '';
                if ($filterJenis === 'Preventive' && strtolower($jenis) !== 'preventive') return false;
                if ($filterJenis === 'Overhaul'   && strtolower($jenis) !== 'overhaul')   return false;
                if ($filterJenis === 'kontrol'    && ($row['doc_source'] ?? '') !== 'kontrol') return false;
            }
            if ($filterBulan && $filterBulan !== 'all') {
                $docDate = $row['doc_date'] ?? '';
                if (strpos($docDate, $filterBulan) === false) return false;
            }
            if ($filterStatus && $filterStatus !== 'all') {
                $status = $row['status'] ?? '';
                $jenis  = $row['jenis_check'] ?? '';
                
                if ($filterStatus === 'Pending_Overhaul') {
                    if ($status !== 'Pending' || $jenis !== 'Overhaul') return false;
                } elseif ($filterStatus === 'Pending_Preventive') {
                    if ($status !== 'Pending' || $jenis !== 'Preventive') return false;
                } else {
                    if ($status !== $filterStatus) return false;
                }
            }
            if ($filterLokasi && $filterLokasi !== 'all') {
                $loc = $row['lokasi_check'] ?? $row['lokasi'] ?? '';
                $line = $row['line'] ?? '';
                if (strtolower($loc) !== strtolower($filterLokasi) && strtolower($line) !== strtolower($filterLokasi)) {
                    return false;
                }
            }
            if ($filterKategori && $filterKategori !== 'all') {
                $kat = $row['kategori'] ?? '';
                if (strtolower($kat) !== strtolower($filterKategori)) return false;
            }
            if ($filterMesin && $filterMesin !== 'all') {
                $mesinNo = $row['no_mesin'] ?? '';
                if (strtolower($mesinNo) !== strtolower($filterMesin)) return false;
            }
            return true;
        });
        $filtered = array_values($filtered);

        // ─── 4. Pagination ─────────────────────────────────────────────────────────
        $perPage     = 15;
        $totalItems  = count($filtered);
        $totalPages  = max(1, (int) ceil($totalItems / $perPage));
        $currentPage = max(1, (int) ($this->request->getGet('page') ?: 1));
        if ($currentPage > $totalPages) $currentPage = $totalPages;
        $offset   = ($currentPage - 1) * $perPage;
        $paginated = array_slice($filtered, $offset, $perPage);

        // ─── 5. Daftar bulan untuk dropdown ──────────────────────────────────────
        $bulanIndo = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                      '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        $bulanList = [];
        for ($i = 0; $i < 12; $i++) {
            $t = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $bulanList[$t->format('Y-m')] = $bulanIndo[$t->format('m')] . ' ' . $t->format('Y');
        }

        return view('approval/index', [
            'title'       => 'Approval',
            'docs'        => $paginated,
            'totalItems'  => $totalItems,
            'totalPages'  => $totalPages,
            'currentPage' => $currentPage,
            'perPage'     => $perPage,
            'bulanList'   => $bulanList,
            'filterJenis'    => $filterJenis,
            'filterBulan'    => $filterBulan,
            'filterStatus'   => $filterStatus,
            'filterLokasi'   => $filterLokasi,
            'filterKategori' => $filterKategori,
            'filterMesin'    => $filterMesin,
            'uniqueLokasi'   => $uniqueLokasi,
            'uniqueKategori' => $uniqueKategori,
            'uniqueMesin'    => $uniqueMesin,
        ]);
    }
}
