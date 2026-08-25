<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\Departemen;

use App\Models\CeklisKontrolModel;
use App\Models\MesinModel;
use CodeIgniter\I18n\Time;

class KontrolService
{
    protected CeklisKontrolModel $kontrolModel;
    protected MesinModel $mesinModel;

    /**
     * POST /kontrol/update-cell
     * Menyimpan atau memperbarui data sel Checklist Control (dari Modal Quick Edit).
     */
    public function updateCell($request)
    {
        $idKontrol   = $request->getPost('id_kontrol');
        $idMesin     = (int) $request->getPost('id_mesin');
        $kategori    = $request->getPost('kategori');
        $bulanTahun  = $request->getPost('bulan_tahun');
        $periodeKe   = (int) $request->getPost('periode_ke');
        $statusCheck = $request->getPost('status_check');
        $picNama     = $request->getPost('pic_nama');
        $outOfPlan   = $request->getPost('out_of_plan') ?: null;
        $ulasan      = $request->getPost('ulasan');

        $mesin = $this->mesinModel->find($idMesin);
        $departemenRedirect = $mesin ? $mesin['departemen'] : Departemen::MFG1->value;

        $data = [
            'id_mesin'      => $idMesin,
            'kategori'      => $kategori,
            'bulan_tahun'   => $bulanTahun,
            'periode_ke'    => $periodeKe,
            'status_check'  => $statusCheck,
            'pic_nama'      => $picNama,
            'out_of_plan'   => $outOfPlan,
            'ulasan'        => $ulasan,
            'tanggal_check' => date('Y-m-d'),
        ];

        if ($idKontrol && (int)$idKontrol > 0) {
            (new \App\Models\CeklisKontrolModel())->update((int)$idKontrol, $data);
        } else {
            $exist = (new \App\Models\CeklisKontrolModel())->where('id_mesin', $idMesin)
                                        ->where('kategori', $kategori)
                                        ->where('bulan_tahun', $bulanTahun)
                                        ->where('periode_ke', $periodeKe)
                                        ->first();
            if ($exist) {
                (new \App\Models\CeklisKontrolModel())->update($exist['id_kontrol'], $data);
            } else {
                (new \App\Models\CeklisKontrolModel())->insert($data);
            }
        }

        return redirect()->to("/kontrol?departemen=" . urlencode($departemenRedirect) . "&kategori=" . urlencode($kategori) . "&bulan=" . urlencode($bulanTahun))
                         ->with('success', 'Sel Checklist Control berhasil diperbarui.');
    }

    /**
     * GET /kontrol
     * Dashboard Checklist Control bulanan.
     */
     
        public function pdf($request)
    {
        $departemen   = $request->getGet('departemen') ?: Departemen::MFG1->value;
        $kategori = $request->getGet('kategori') ?: 'Penerangan';
        $bulan    = $request->getGet('bulan') ?: date('Y-m');
        $line     = $request->getGet('line') ?: null;

        $categoriesList = $this->resolveCategories($departemen, $line);
        if (!in_array($kategori, $categoriesList)) {
            $kategori = 'Penerangan';
        }
        $categories = array_combine($categoriesList, $categoriesList);

        $availableLines = $this->resolveAvailableLines($departemen);
        if (empty($line) && !empty($availableLines)) {
            $line = $availableLines[0];
        }

        $grid = (new \App\Models\CeklisKontrolModel())->getGridData($departemen, $kategori, $bulan, $line);

        $db = \Config\Database::connect();
        $jadwalModel = new \App\Models\JadwalPreventiveModel();
        $schedule = $jadwalModel->getJadwalForChecklist($departemen, $kategori, $bulan);

        $hasSchedule = !empty($schedule);
        $columnDates = $this->calculateColumnDates($schedule);

        $approvalModel = new \App\Models\ApprovalBulananModel();
        $approval = $approvalModel->getApprovalWithUsers($departemen, $kategori, $bulan, $line ?: 'NONE');
        
        $approvalStatus = $approval ? $approval['status'] : 'Pending';

        $data = [
            'title'          => 'Checklist Control Bulanan',
            'departemen'         => $departemen,
            'line'           => $line,
            'kategori'       => $kategori,
            'bulan'          => $bulan,
            'categories'     => $categories,
            'availableLines' => $availableLines,
            'grid'           => $grid,
            'hasSchedule'    => $hasSchedule,
            'columnDates'    => $columnDates,
            'approvalStatus' => $approvalStatus,
            'approvalData'   => $approval,
        ];

        return $data;
    }

    public function pdfAllCategories($request)
    {
        $plant         = $request->getGet('plant') ?: 'Plant 1';
        $departemen   = $request->getGet('departemen') ?: Departemen::MFG1->value;
        $bulan    = $request->getGet('bulan') ?: date('Y-m');
        $line     = $request->getGet('line') ?: null;

        $categories = $this->resolveCategories($departemen, $line);
        $availableLines = $this->resolveAvailableLines($departemen);
        if (empty($line) && !empty($availableLines)) {
            $line = $availableLines[0];
        }

        $allGrids = [];
        $db = \Config\Database::connect();
        
        foreach ($categories as $cat) {
            $grid = (new \App\Models\CeklisKontrolModel())->getGridData($departemen, $cat, $bulan, $line);
            $jadwalModel = new \App\Models\JadwalPreventiveModel();
            $schedule = $jadwalModel->getJadwalForChecklist($departemen, $cat, $bulan);
                           
            $hasSchedule = false;
            $tglRencanaStr = '-';
            if ($schedule) {
                $hasSchedule = true;
                $tglRencanaStr = date('d-m-Y', strtotime($schedule['tanggal_rencana']));
            }
            $columnDates = $this->calculateColumnDates($schedule);

            $approvalModel = new \App\Models\ApprovalBulananModel();
            $approval = $approvalModel->getApprovalWithUsers($departemen, $cat, $bulan, $line ?: 'NONE', $plant) ?: [];

            $allGrids[] = [
                'kategori'    => $cat,
                'grid'        => $grid,
                'hasSchedule' => $hasSchedule,
                'tglRencana'  => $tglRencanaStr,
                'columnDates' => $columnDates,
                'approvalData'=> $approval
            ];
        }
        
        $bulanList = $this->buildBulanList();

        $data = [
            'title'      => "Checklist Control - {$plant} - {$departemen} - Semua Kategori",
            'plant'       => $plant,
            'departemen'     => $departemen,
            'bulan'      => $bulan,
            'line'       => $line,
            'allGrids'   => $allGrids,
            'bulanList'  => $bulanList
        ];

        return $data;
    }

    public function pdfAllSummary($request)
    {
        $bulan = $request->getGet('bulan') ?: date('Y-m');
        $filterPlant = $request->getGet('filter_plant') === 'all' ? '' : ($request->getGet('filter_plant') ?: '');
        $filterLokasi = $request->getGet('filter_lokasi') === 'all' ? '' : ($request->getGet('filter_lokasi') ?: '');
        $filterLine = $request->getGet('filter_line') === 'all' ? '' : ($request->getGet('filter_line') ?: '');
        $filterKategori = $request->getGet('filter_kategori') === 'all' ? '' : ($request->getGet('filter_kategori') ?: '');
        
        $departemenList = [
            Departemen::MFG1->value => ['Line 1', 'Line 2', 'Line 3'],
            Departemen::MFG2->value => ['CG', 'Second']
        ];
        
        $allGrids = [];
        $db = \Config\Database::connect();
        
        $plansToIterate = ['Plant 1', 'Plant 2'];

        foreach ($plansToIterate as $pln) {
            if (!empty($filterPlant) && $pln !== $filterPlant) continue;

            foreach ($departemenList as $departemen => $lines) {
                if (!empty($filterLokasi) && $departemen !== $filterLokasi) continue;
                
                foreach ($lines as $line) {
                    if (!empty($filterLine) && $line !== $filterLine) continue;
                    
                    $hasCam = (new \App\Models\MesinModel())->hasCamMachine($departemen, $line);
                    $categories = $hasCam 
                        ? ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox Cam', 'Belt Cam']
                        : ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'];
                    
                    foreach ($categories as $cat) {
                        if (!empty($filterKategori) && $cat !== $filterKategori) continue;
                        
                        $grid = (new \App\Models\CeklisKontrolModel())->getGridData($departemen, $cat, $bulan, $line);
                        
                        if (empty($grid)) continue;

                        // Skip if completely unfilled (no PIC assigned)
                        $hasData = false;
                        foreach ($grid as $row) {
                            if (isset($row['mesin']['plant']) && $row['mesin']['plant'] !== $pln) continue; // Ensure the machine belongs to this plant
                            if (isset($row['pic_nama']) && $row['pic_nama'] !== 'PIC' && !empty($row['pic_nama'])) {
                                $hasData = true;
                                break;
                            }
                        }
                        if (!$hasData) continue;

                        // Filter grid to only include machines for this plant
                        $filteredGrid = array_filter($grid, function($row) use ($pln) {
                            return (isset($row['mesin']['plant']) ? $row['mesin']['plant'] : '') === $pln;
                        });
                        
                        if (empty($filteredGrid)) continue;

                        $jadwalModel = new \App\Models\JadwalPreventiveModel();
                        $schedule = $jadwalModel->getJadwalForChecklist($departemen, $cat, $bulan);
                                       
                        $columnDates = [];
                        $hasSchedule = false;
                        $tglRencanaStr = '-';
                        if ($schedule) {
                            $hasSchedule = true;
                            $tglRencanaStr = date('d-m-Y', strtotime($schedule['tanggal_rencana']));
                            
                            $tglRencanaTs = strtotime($schedule['tanggal_rencana']);
                            $dayOfWeek      = (int) date('N', $tglRencanaTs);
                            $mondayTs       = strtotime('-' . ($dayOfWeek - 1) . ' days', $tglRencanaTs);

                            for ($d = 0; $d < 5; $d++) {
                                $columnDates[$d + 1] = date('Y-m-d', strtotime("+$d days", $mondayTs));
                            }
                        }

                        $approvalModel = new \App\Models\ApprovalBulananModel();
                        $approval = $approvalModel->getApprovalWithUsers($departemen, $cat, $bulan, $line, $pln);
                        $approvalQuery = true;

                        $allGrids[] = [
                            'plant'            => $pln,
                            'departemen'      => $departemen,
                            'line'        => $line,
                            'kategori'    => $cat,
                            'grid'        => array_values($filteredGrid),
                            'hasSchedule' => $hasSchedule,
                        'tglRencana'  => $tglRencanaStr,
                        'columnDates' => $columnDates,
                        'approvalData'=> $approval
                    ];
                }
            }
        }
        }
        
        $bulanList = [];
        for ($i = 0; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $bulanList[$time->format('Y-m')] = format_bulan_indo($time->format("Y-m"));
        }

        $data = [
            'title'      => "Checklist Control - Ringkasan Semua Area",
            'departemen'     => 'SEMUA AREA',
            'bulan'      => $bulan,
            'line'       => null,
            'allGrids'   => $allGrids,
            'bulanList'  => $bulanList
        ];

        return $data;
    }

    public function index($request)
    {
        // Jika parameter view=summary atau tidak ada parameter spesifik, tampilkan halaman ringkasan
        if ($request->getGet('view') === 'summary' || (!$request->getGet('departemen') && !$request->getGet('line') && !$request->getGet('kategori'))) {
            return $this->summary($request);
        }

        $departemen   = $request->getGet('departemen') ?: Departemen::MFG1->value;
        $kategori = $request->getGet('kategori') ?: 'Penerangan';
        $bulan    = $request->getGet('bulan') ?: date('Y-m');
        $line     = $request->getGet('line') ?: null;

        // Daftar kategori khusus Preventive
        $categoriesList = $this->resolveCategories($departemen, $line);
        if (!in_array($kategori, $categoriesList)) {
            $kategori = 'Penerangan';
        }
        $categories = array_combine($categoriesList, $categoriesList);

        $bulanList = $this->buildBulanList();

        $availableLines = $this->resolveAvailableLines($departemen);
        if (empty($line) && !empty($availableLines)) {
            $line = $availableLines[0];
        }

        $grid = (new \App\Models\CeklisKontrolModel())->getGridData($departemen, $kategori, $bulan, $line);

        // Ambil jadwal rencana untuk departemen, kategori, dan bulan berjalan (maks 1 per bulan)
        $db = \Config\Database::connect();
        $jadwalModel = new \App\Models\JadwalPreventiveModel();
        $schedule = $jadwalModel->getJadwalForChecklist($departemen, $kategori, $bulan);

        // Hitung 5 tanggal hari kerja (Senin-Jumat) dari pekan terjadwal
        $hasSchedule = !empty($schedule);
        $columnDates = $this->calculateColumnDates($schedule);

        $allChecked = true;
        foreach ($grid as $row) {
            if ($row['pic_nama'] === 'PIC') {
                $allChecked = false;
                break;
            }
        }

        // Ambil status approval beserta nama approver
        $approvalModel = new \App\Models\ApprovalBulananModel();
        $approval = $approvalModel->getApprovalWithUsers($departemen, $kategori, $bulan, $line ?: 'NONE');

        $approvalStatus = $approval ? $approval['status'] : 'Pending';

        if (has_role(Role::Sheadprd->value) && !has_any_role([Role::Admin->value, Role::Leader->value, Role::Member->value]) && $approvalStatus === 'Pending') {
            return redirect()->to('/kontrol')->with('error', 'Dokumen belum siap untuk Anda (Masih menunggu persetujuan Leader).');
        }
        if (has_role(Role::Sheadmtc->value) && !has_any_role([Role::Admin->value, Role::Leader->value, Role::Member->value, Role::Sheadprd->value]) && in_array($approvalStatus, ['Pending', 'Approved L1'], true)) {
            return redirect()->to('/kontrol')->with('error', 'Dokumen belum siap untuk Anda (Masih menunggu persetujuan SHead Produksi).');
        }

        return [
            'title'          => 'Checklist Control Bulanan',
            'departemen'         => $departemen,
            'line'           => $line,
            'kategori'       => $kategori,
            'bulan'          => $bulan,
            'categories'     => $categories,
            'availableLines' => $availableLines,
            'bulanList'      => $bulanList,
            'grid'           => $grid,
            'hasSchedule'    => $hasSchedule,
            'columnDates'    => $columnDates,
            'allChecked'     => $allChecked,
            'approvalStatus' => $approvalStatus,
            'approvalData'   => $approval,
        ];
    }

    /**
     * Halaman Ringkasan (Summary) Checklist Control
     */
    public function summary($request)
    {
        $bulan = $request->getGet('bulan') ?: date('Y-m');
        $filterLokasi = $request->getGet('filter_lokasi') === 'all' ? '' : ($request->getGet('filter_lokasi') ?: '');
        $filterPlant = $request->getGet('filter_plant') === 'all' ? '' : ($request->getGet('filter_plant') ?: '');
        $filterLine = $request->getGet('filter_line') === 'all' ? '' : ($request->getGet('filter_line') ?: '');
        $filterKategori = $request->getGet('filter_kategori') === 'all' ? '' : ($request->getGet('filter_kategori') ?: '');
        $filterStatus = $request->getGet('filter_status') === 'all' ? '' : ($request->getGet('filter_status') ?: '');
        $sortBy = $request->getGet('sort_by') ?: 'departemen';
        $order = strtolower($request->getGet('order') ?: 'asc');

        $db = \Config\Database::connect();

        // 1. Total mesin per Line
        $riwayatMesinModel = new \App\Models\RiwayatMesinModel();
        $totalMesinQuery = $riwayatMesinModel->getTotalMesinPerLineHistorical($bulan);
        
        $totalMesin = [];
        $linesByLokasi = [];
        foreach($totalMesinQuery as $tm) {
            $pln = $tm['plant'] ?? 'Plant 1';
            $totalMesin[$pln][$tm['departemen']][$tm['line']] = (int) $tm['total'];
            $linesByLokasi[$tm['departemen']][] = $tm['line'];
        }

        // 2. Checked machines per Line & Category
        $ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
        $checkedQuery = $ceklisKontrolModel->getCheckedMachinesCount($bulan);
                           
        $checkedData = [];
        foreach($checkedQuery as $cq) {
            $pln = $cq['plant'] ?? 'Plant 1';
            $checkedData[$pln][$cq['departemen']][$cq['line']][$cq['kategori']] = (int) $cq['checked_count'];
        }

        // 3. Approval status
        $approvalModel = new \App\Models\ApprovalBulananModel();
        $approvals = $approvalModel->getAllApprovals($bulan);
                        
        $approvalData = [];
        foreach($approvals as $ap) {
            $pln = $ap['plant'] ?? 'Plant 1';
            $approvalData[$pln][$ap['departemen']][$ap['line']][$ap['kategori']] = $ap['status'];
        }

        // Categories mapping
        $mesinModel = new \App\Models\MesinModel();
        $kategoriByLokasi = [
            Departemen::MFG1->value => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox Cam', 'Belt Cam'],
            Departemen::MFG2->value => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'],
        ];
        
        // Buat list 12 bulan terakhir untuk dropdown filter
        $bulanList = $this->buildBulanList();

        // Build flat array for summary rows
        $summaryRows = [];
        $notCheckedRows = [];
        
        $plansToIterate = ['Plant 1', 'Plant 2'];

        foreach ($plansToIterate as $pln) {
            if (!empty($filterPlant) && $pln !== $filterPlant) continue;
            
            foreach ($kategoriByLokasi as $departemen => $categories) {
                if (!empty($filterLokasi) && $departemen !== $filterLokasi) continue;
                
                $lines = isset($linesByLokasi[$departemen]) ? array_unique($linesByLokasi[$departemen]) : [];
                sort($lines);

                foreach ($lines as $line) {
                    if (!empty($filterLine) && $line !== $filterLine) continue;

                    foreach ($categories as $kategori) {
                        if (!empty($filterKategori) && $kategori !== $filterKategori) continue;
                        
                        $total = $totalMesin[$pln][$departemen][$line] ?? 0;
                        if ($total == 0) continue;
                        
                        $checked = $checkedData[$pln][$departemen][$line][$kategori] ?? 0;
                        
                        if ($checked == 0) {
                            $notCheckedRows[] = [
                                'plant'         => $pln,
                                'departemen'   => $departemen,
                                'line'     => $line,
                                'kategori' => $kategori
                            ];
                            continue;
                        }
                        
                        $percent = $total > 0 ? round(($checked / $total) * 100) : 0;
                        $status = $approvalData[$pln][$departemen][$line][$kategori] ?? '';
                        
                        // Hitung status teks dan warna badge yang akurat sesuai progress
                    $badgeClass = 'bg-secondary';
                    $statusText = 'Belum Selesai (' . $percent . '%)';
                    
                    if ($percent == 100) {
                        if (empty($status) || $status === 'Pending') {
                            $badgeClass = 'bg-warning text-dark';
                            $statusText = 'Menunggu Member (100%)';
                        } elseif ($status === 'Approved L1') {
                            $badgeClass = 'bg-info text-dark';
                            $statusText = 'Menunggu SHead PRD';
                        } elseif ($status === 'Approved L2') {
                            $badgeClass = 'bg-primary';
                            $statusText = 'Menunggu SHead MTC';
                        } elseif ($status === 'Final' || $status === 'Approved Final') {
                            $badgeClass = 'bg-success';
                            $statusText = 'Selesai (Final)';
                        }
                    }

                    // Eksekusi: History Checklist Control HANYA boleh menampilkan dokumen yang SUDAH 100% SELESAI DAN APPROVED FINAL.
                    // Jika belum 100% atau belum di-approve final, maka sembunyikan dari History (otomatis masuk ke menu Approval).
                    // Pengecualian: Role 'magang' dapat melihat semua data terlepas dari status progress/approval.
                    if (!has_role('magang')) {
                        if ($percent < 100 || !in_array($status, ['Final', 'Approved Final'], true)) {
                            continue;
                        }
                    }
                    
                    if (!empty($filterStatus) && $statusText !== $filterStatus) continue;

                    $summaryRows[] = [
                        'plant'            => $pln,
                        'departemen'      => $departemen,
                        'line'        => $line,
                        'kategori'    => $kategori,
                        'total'       => $total,
                        'checked'     => $checked,
                        'percent'     => $percent,
                        'statusText'  => $statusText,
                        'badgeClass'  => $badgeClass
                    ];
                }
            }
        }
    }

        // Sort the flat array
        usort($summaryRows, function($a, $b) use ($sortBy, $order) {
            $valA = $a[$sortBy] ?? '';
            $valB = $b[$sortBy] ?? '';
            
            if ($valA == $valB) return 0;
            
            $cmp = ($valA < $valB) ? -1 : 1;
            return ($order === 'asc') ? $cmp : -$cmp;
        });

        // Determine available lines and categories for the dropdowns
        $availableLines = [];
        $availableCategories = [];
        if (!empty($filterLokasi)) {
            $availableLines = isset($linesByLokasi[$filterLokasi]) ? array_unique($linesByLokasi[$filterLokasi]) : [];
            $availableCategories = isset($kategoriByLokasi[$filterLokasi]) ? array_unique($kategoriByLokasi[$filterLokasi]) : [];
        } else {
            foreach ($linesByLokasi as $lines) {
                $availableLines = array_merge($availableLines, $lines);
            }
            $availableLines = array_unique($availableLines);
            foreach ($kategoriByLokasi as $cats) {
                $availableCategories = array_merge($availableCategories, $cats);
            }
            $availableCategories = array_unique($availableCategories);
        }
        sort($availableLines);
        sort($availableCategories);

        return array_merge(["is_summary" => true], [
            'title'            => 'Ringkasan Checklist Control',
            'bulan'            => $bulan,
            'bulanList'        => $bulanList,
            'summaryRows'      => $summaryRows,
            'notCheckedRows'   => $notCheckedRows,
            'filterLokasi'     => $filterLokasi,
            'filterLine'       => $filterLine,
            'filterKategori'   => $filterKategori,
            'filterStatus'     => $filterStatus,
            'sortBy'           => $sortBy,
            'order'            => $order,
            'availableLines'   => $availableLines,
            'availableCategories'=> $availableCategories,
        ]);
    }

    /**
     * POST /kontrol/approve
     */
    public function approveBulanan($request)
    {
        if (!has_any_role([\App\Enums\Role::Admin->value, \App\Enums\Role::Member->value, \App\Enums\Role::LeaderMember->value, \App\Enums\Role::Leader->value, \App\Enums\Role::Sheadprd->value, \App\Enums\Role::Sheadmtc->value])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $plant     = $request->getPost('plant') ?: 'Plant 1';
        $departemen   = $request->getPost('departemen');
        $line     = $request->getPost('line');
        $kategori = $request->getPost('kategori');
        $bulan    = $request->getPost('bulan_tahun');

        if (empty($line)) {
            return ["status" => false, "message" => 'Silakan pilih Line terlebih dahulu untuk melakukan persetujuan.'];
        }

        $db = \Config\Database::connect();
        $approvalModel = new \App\Models\ApprovalBulananModel();
        $approval = $approvalModel->getApprovalKontrol($departemen, $line, $kategori, $bulan, $plant);

        $currentStatus = $approval ? $approval['status'] : 'Pending';
        $userId        = session()->get('user_id');
        $now           = date('Y-m-d H:i:s');

        $data = [
            'type'        => 'kontrol',
            'plant'        => $plant,
            'departemen'      => $departemen,
            'line'        => $line,
            'kategori'    => $kategori,
            'bulan_tahun' => $bulan,
            'updated_at'  => $now,
        ];


        // Logic Approval Level
        if (has_role(Role::Admin->value)) {
            $data['status'] = 'Approved Final';
            $data['approved_final_by'] = $userId;
            $data['approved_final_at'] = $now;
        } elseif ($currentStatus === 'Pending') {
            if (!has_any_role([\App\Enums\Role::Member->value, \App\Enums\Role::LeaderMember->value])) return ["status" => false, "message" => 'Laporan butuh persetujuan Member/Leader MTC.'];
            
            $data['status'] = 'Approved L1';
            $data['approved_l1_by'] = $userId;
            $data['approved_l1_at'] = $now;
        } elseif ($currentStatus === 'Approved L1') {
            if (!has_role(Role::Sheadprd->value)) return ["status" => false, "message" => 'Laporan butuh persetujuan Section Head Produksi.'];
            
            // Location Validation for Sheadprd
            if (session()->get('departemen')) {
                $userDepts = array_map('trim', explode(',', session()->get('departemen')));
                if (!in_array($departemen, $userDepts)) {
                    return ["status" => false, "message" => 'Akses ditolak! Anda hanya dapat menyetujui laporan dari departemen ' . session()->get('departemen')];
                }
            }
            if (session()->get('line')) {
                $userLines = array_map('trim', explode(',', session()->get('line')));
                if (!in_array($line, $userLines)) {
                    return ["status" => false, "message" => 'Akses ditolak! Line ini tidak berada di ' . session()->get('line') . ' yang menjadi tanggung jawab Anda.'];
                }
            }
            
            $data['status'] = 'Approved L2';
            $data['approved_l2_by'] = $userId;
            $data['approved_l2_at'] = $now;
        } elseif ($currentStatus === 'Approved L2') {
            if (!has_role(Role::Sheadmtc->value)) return ["status" => false, "message" => 'Laporan butuh persetujuan Section Head MTC.'];
            
            $data['status'] = 'Approved Final';
            $data['approved_final_by'] = $userId;
            $data['approved_final_at'] = $now;
        } else {
            return ["status" => false, "message" => 'Status laporan tidak valid untuk diproses.'];
        }

        if ($approval) {
            $approvalModel->update($approval['id_approval'], $data);
        } else {
            $data['created_at'] = $now;
            $approvalModel->insert($data);
        }

        return ["status" => true, "message" => 'Berhasil menyetujui Checklist Control bulanan.'];
    }

    /**
     * POST /kontrol/delete-approval
     * Menghapus record approval (Khusus Admin)
     */
    public function deleteApprovalBulanan($request)
    {
        if (!has_role(Role::Admin->value)) {
            return ['status' => false, 'message' => 'Akses ditolak. Hanya Admin.'];
        }

        $plant     = $request->getPost('plant') ?: 'Plant 1';
        $departemen   = $request->getPost('departemen');
        $line     = $request->getPost('line');
        $kategori = $request->getPost('kategori');
        $bulan    = $request->getPost('bulan_tahun');

        $approvalModel = new \App\Models\ApprovalBulananModel();
        $approvalModel->deleteApprovalKontrol($departemen, $line, $kategori, $bulan, $plant);

        return ["status" => true, "message" => 'Data approval Checklist Control berhasil dihapus (Reset ke Belum Selesai).'];
    }

    private function resolveCategories(string $departemen, ?string $line = null): array
    {
        $hasCam = (new \App\Models\MesinModel())->hasCamMachine($departemen, $line);
        if ($hasCam) {
            return ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox Cam', 'Belt Cam'];
        }
        return ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'];
    }

    private function resolveAvailableLines(string $departemen): array
    {
        if ($departemen === Departemen::MFG1->value) {
            return ['Line 1', 'Line 2', 'Line 3'];
        } elseif ($departemen === Departemen::MFG2->value) {
            return ['CG', 'Second'];
        }
        return [];
    }

    private function calculateColumnDates($schedule): array
    {
        $columnDates = [];
        if ($schedule && !empty($schedule['tanggal_rencana'])) {
            $tglRencana = strtotime($schedule['tanggal_rencana']);
            $dayOfWeek = (int) date('N', $tglRencana);
            $mondayTs = strtotime('-' . ($dayOfWeek - 1) . ' days', $tglRencana);

            for ($d = 0; $d < 5; $d++) {
                $columnDates[$d + 1] = date('Y-m-d', strtotime("+$d days", $mondayTs));
            }
        }
        return $columnDates;
    }

    private function buildBulanList(): array
    {
        $bulanList = [];
        for ($i = -1; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $bulanList[$time->format('Y-m')] = format_bulan_indo($time->format("Y-m"));
        }
        return $bulanList;
    }
}
