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

        [$uniqueLokasi, $uniqueKategori, $uniqueMesin, $uniquePic] = $this->extractUniqueFilters($allDocs);

        $filterJenis    = $request->getGet('jenis') ?: null;
        $filterBulan    = $request->getGet('bulan') ?: null;
        $filterStatus   = $request->getGet('status') ?: null;
        $filterLokasi   = $request->getGet('lokasi') ?: null;
        $filterKategori = $request->getGet('kategori') ?: null;
        $filterMesin    = $request->getGet('mesin') ?: null;
        $filterPic      = $request->getGet('pic') ?: null;

        $filtered = $this->applyGetFilters($allDocs, $filterJenis, $filterBulan, $filterStatus, $filterLokasi, $filterKategori, $filterMesin, $filterPic);
        $filtered = array_values($filtered);

        // ─── 4. Pagination ─────────────────────────────────────────────────────────
        $perPage     = (int) ($request->getGet('per_page') ?: 15);
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
            'filterPic'      => $filterPic,
            'uniqueLokasi'   => $uniqueLokasi,
            'uniqueKategori' => $uniqueKategori,
            'uniqueMesin'    => $uniqueMesin,
            'uniquePic'      => $uniquePic,
        ];
    }

    public function getBelumSelesaiRows(string $role, string $bulanIni, \App\Models\ApprovalBulananModel $approvalModel): array
    {
        $belumSelesaiRows = [];
        if (!in_array($role, [Role::Member->value, Role::Admin->value], true)) {
            return $belumSelesaiRows;
        }

        $riwayatMesinModel = new \App\Models\RiwayatMesinModel();

        $kategoriByLokasi = [
            Lokasi::MFG1->value => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'],
            Lokasi::MFG2->value => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'],
        ];

        $ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
        // Fetch ALL months (no bulan filter) to catch Tunggakan/Curi Start
        $checkedData = $ceklisKontrolModel->getCheckedMachinesCount(); 
        
        $checkedMap = [];
        $activeMonths = [];
        foreach ($checkedData as $cd) {
            $bt = $cd['bulan_tahun'];
            $activeMonths[$bt] = true;
            $checkedMap[$bt][$cd['lokasi']][$cd['line']][$cd['kategori']] = (int) $cd['checked_count'];
        }

        // Selalu sertakan bulan ini walau belum ada yg dicek (biar logic aslinya jalan jika perlu)
        // Walau praktiknya 'checked == 0' akan ter-skip.
        $activeMonths[$bulanIni] = true;

        $existingApprovals = $approvalModel->getExistingApprovals();
        $approvedSet = [];
        foreach ($existingApprovals as $ea) {
            $approvedSet[$ea['bulan_tahun']][$ea['lokasi']][$ea['line']][$ea['kategori']] = $ea['status'] ?? 'Pending';
        }

        foreach (array_keys($activeMonths) as $bt) {
            // Ambil total mesin per line secara historis untuk bulan tersebut
            $totalMesinData = $riwayatMesinModel->getTotalMesinPerLineHistorical($bt);
            $totalMesinMap = [];
            foreach ($totalMesinData as $tm) {
                $totalMesinMap[$tm['lokasi']][$tm['line']] = (int) $tm['total'];
            }

            foreach ($kategoriByLokasi as $lok => $kats) {
                if (!isset($totalMesinMap[$lok])) continue;
                foreach ($totalMesinMap[$lok] as $ln => $totalMesin) {
                    foreach ($kats as $kat) {
                        $checked = $checkedMap[$bt][$lok][$ln][$kat] ?? 0;
                        $persen = $totalMesin > 0 ? round(($checked / $totalMesin) * 100) : 0;

                        $approvalStatus = $approvedSet[$bt][$lok][$ln][$kat] ?? null;

                        if ($approvalStatus !== null) {
                            if ($persen == 100) {
                                continue; // Sudah 100% dan masuk tabel approval_bulanan -> diurus oleh Inbox normal atau History
                            }
                            if (!in_array($approvalStatus, ['Final', 'Approved Final'])) {
                                continue; // < 100% dan status belum final -> sudah ditarik oleh getInboxApprovalKontrol
                            }
                            // BLACK HOLE: < 100% TAPI status sudah Final/Approved Final di database.
                            // Kita biarkan lanjut agar muncul di Inbox (dengan status Belum Selesai) sehingga Admin bisa menghapus approvalnya.
                        }

                        if ($checked === 0) continue;

                        $belumSelesaiRows[] = [
                            'doc_id'      => null,
                            'jenis_check' => 'kontrol',
                            'kategori'    => $kat,
                            'lokasi'      => $lok,
                            'line'        => $ln,
                            'doc_date'    => $bt,
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
        return $belumSelesaiRows;
    }

    private function extractUniqueFilters(array $allDocs): array
    {
        $uniqueLokasi = [];
        $uniqueKategori = [];
        $uniqueMesin = [];
        $uniquePic = [];
        foreach ($allDocs as $doc) {
            $pic = $doc['nama_pic'] ?? '';
            if (!empty($pic)) $uniquePic[$pic] = true;
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
        $uniquePic = array_keys($uniquePic);
        asort($uniqueLokasi);
        asort($uniqueKategori);
        asort($uniqueMesin);
        asort($uniquePic);

        return [$uniqueLokasi, $uniqueKategori, $uniqueMesin, $uniquePic];
    }

    private function applyGetFilters(array $allDocs, ?string $filterJenis, ?string $filterBulan, ?string $filterStatus, ?string $filterLokasi, ?string $filterKategori, ?string $filterMesin, ?string $filterPic = null): array
    {
        return array_filter($allDocs, function($row) use ($filterJenis, $filterBulan, $filterStatus, $filterLokasi, $filterKategori, $filterMesin, $filterPic) {
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
                if ($mesinNo !== $filterMesin) return false;
            }
            if ($filterPic && $filterPic !== 'all') {
                $pic = $row['nama_pic'] ?? '';
                if ($pic !== $filterPic) return false;
            }

            return true;
        });
    }

    private function buildBulanList(): array
    {
        $bulanList = [];
        // Mulai dari -1 untuk menambahkan 1 bulan ke depan (Curi Start)
        for ($i = -1; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $bulanList[$time->format('Y-m')] = format_bulan_indo($time->format("Y-m"));
        }
        return $bulanList;
    }
}
