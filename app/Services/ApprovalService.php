<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\Lokasi;
use App\Enums\JenisCheck;

use App\Models\TransaksiCheckModel;
use App\Models\MesinModel;

class ApprovalService
{
    /**
     * GET /approval
     * Halaman Inbox Approval — menampilkan:
     *  - Ceklis Kontrol "Belum Selesai" (sedang diisi, belum 100%)
     *  - Ceklis Kontrol "Pending/Approved L1/L2" (menunggu approval)
     *  - Checklist Report & Inspection Report yang butuh approval
     * History hanya menampilkan yang sudah Approved Final.
     */
    public function index($request) {
        $role   = session()->get('role');
        $userId = session()->get('user_id');
        $line   = session()->get('line');

        $db = \Config\Database::connect();

        // ─── 1. CHECKLIST REPORT & INSPECTION REPORT (transaksi_check) ──────────
        $transaksiModel = new \App\Models\TransaksiCheckModel();
        if (!in_array($role, [\App\Enums\Role::Leader->value, \App\Enums\Role::Sheadprd->value, \App\Enums\Role::Sheadmtc->value, \App\Enums\Role::Member->value, \App\Enums\Role::Admin->value])) {
            return redirect()->to('/dashboard')->with('error', 'Akses tidak diizinkan.');
        }
        $transaksiRows = $transaksiModel->getInboxApprovalTransaksi($role, $line);

        // ─── 2. CHECKLIST CONTROL BULANAN ────────────────────────────────────────
        // Bagian A: yang sudah ada di approval_bulanan (Pending, Approved L1/L2)
        $kontrolRows = [];

        if (in_array($role, [Role::Member->value, Role::Admin->value, Role::Sheadprd->value, Role::Sheadmtc->value], true)) {

            // -- A. Kontrol yang sudah di-submit ke approval_bulanan (ada status Pending/L1/L2) --
            $approvalModel = new \App\Models\ApprovalBulananModel();
            $approvalRows = $approvalModel->getInboxApprovalKontrol($role);

            // -- B. Kontrol yang BELUM SELESAI diisi (belum ada di approval_bulanan) --
            // Hanya untuk role member & admin — agar bisa membuka dan melanjutkan pengisian
            $belumSelesaiRows = $this->getBelumSelesaiRows($role, date('Y-m'), $approvalModel);

            $kontrolRows = array_merge($approvalRows, $belumSelesaiRows);
        }

        // ─── 3. Gabungkan & apply filter GET ──────────────────────────────────────
        $allDocs = array_merge($transaksiRows, $kontrolRows);

        [$uniqueLokasi, $uniqueKategori, $uniqueMesin] = $this->extractUniqueFilters($allDocs);

        $filterJenis    = $request->getGet('jenis') ?: null;
        $filterBulan    = $request->getGet('bulan') ?: null;
        $filterStatus   = $request->getGet('status') ?: null;
        $filterLokasi   = $request->getGet('lokasi') ?: null;
        $filterKategori = $request->getGet('kategori') ?: null;
        $filterMesin    = $request->getGet('mesin') ?: null;

        $filtered = $this->applyGetFilters($allDocs, $filterJenis, $filterBulan, $filterStatus, $filterLokasi, $filterKategori, $filterMesin);
        $filtered = array_values($filtered);

        // ─── 4. Pagination ─────────────────────────────────────────────────────────
        $perPage     = 15;
        $totalItems  = count($filtered);
        $totalPages  = max(1, (int) ceil($totalItems / $perPage));
        $currentPage = max(1, (int) ($request->getGet('page') ?: 1));
        if ($currentPage > $totalPages) $currentPage = $totalPages;
        $offset   = ($currentPage - 1) * $perPage;
        $paginated = array_slice($filtered, $offset, $perPage);

        // ─── 5. Daftar bulan untuk dropdown ──────────────────────────────────────
        $bulanList = $this->buildBulanList();

        return [
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
        ];
    }

    private function getBelumSelesaiRows(string $role, string $bulanIni, \App\Models\ApprovalBulananModel $approvalModel): array
    {
        $belumSelesaiRows = [];
        if (!in_array($role, [Role::Member->value, Role::Admin->value], true)) {
            return $belumSelesaiRows;
        }

        $mesinModel = new \App\Models\MesinModel();
        $totalMesinData = $mesinModel->getTotalMesinPerLine();
        $totalMesinMap = [];
        foreach ($totalMesinData as $tm) {
            $totalMesinMap[$tm['lokasi']][$tm['line']] = (int) $tm['total'];
        }

        $kategoriByLokasi = [
            Lokasi::MFG1->value => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'],
            Lokasi::MFG2->value => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'],
        ];

        $ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
        $checkedData = $ceklisKontrolModel->getCheckedMachinesCount($bulanIni);
        $checkedMap = [];
        foreach ($checkedData as $cd) {
            $checkedMap[$cd['lokasi']][$cd['line']][$cd['kategori']] = (int) $cd['checked'];
        }

        $existingApprovals = $approvalModel->getExistingApprovals($bulanIni);
        $approvedSet = [];
        foreach ($existingApprovals as $ea) {
            $approvedSet[$ea['lokasi']][$ea['line']][$ea['kategori']] = true;
        }

        foreach ($kategoriByLokasi as $lok => $kats) {
            if (!isset($totalMesinMap[$lok])) continue;
            foreach ($totalMesinMap[$lok] as $ln => $totalMesin) {
                foreach ($kats as $kat) {
                    if (isset($approvedSet[$lok][$ln][$kat])) continue;

                    $checked = $checkedMap[$lok][$ln][$kat] ?? 0;
                    $persen = $totalMesin > 0 ? round(($checked / $totalMesin) * 100) : 0;

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
        return $belumSelesaiRows;
    }

    private function extractUniqueFilters(array $allDocs): array
    {
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

        return [$uniqueLokasi, $uniqueKategori, $uniqueMesin];
    }

    private function applyGetFilters(array $allDocs, ?string $filterJenis, ?string $filterBulan, ?string $filterStatus, ?string $filterLokasi, ?string $filterKategori, ?string $filterMesin): array
    {
        return array_filter($allDocs, function($row) use ($filterJenis, $filterBulan, $filterStatus, $filterLokasi, $filterKategori, $filterMesin) {
            if ($filterJenis && $filterJenis !== 'all') {
                $jenis = $row['jenis_check'] ?? '';
                if ($filterJenis === JenisCheck::Preventive->value && strtolower($jenis) !== 'preventive') return false;
                if ($filterJenis === JenisCheck::Overhaul->value   && strtolower($jenis) !== 'overhaul')   return false;
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
                    if ($status !== 'Pending' || $jenis !== JenisCheck::Overhaul->value) return false;
                } elseif ($filterStatus === 'Pending_Preventive') {
                    if ($status !== 'Pending' || $jenis !== JenisCheck::Preventive->value) return false;
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
    }

    private function buildBulanList(): array
    {
        $bulanIndo = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni',
                      '07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'];
        $bulanList = [];
        for ($i = 0; $i < 12; $i++) {
            $t = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $bulanList[$t->format('Y-m')] = $bulanIndo[$t->format('m')] . ' ' . $t->format('Y');
        }
        return $bulanList;
    }
}
