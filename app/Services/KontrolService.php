<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\Lokasi;

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
        $lokasiRedirect = $mesin ? $mesin['lokasi'] : Lokasi::MFG1->value;

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

        return redirect()->to("/kontrol?lokasi=" . urlencode($lokasiRedirect) . "&kategori=" . urlencode($kategori) . "&bulan=" . urlencode($bulanTahun))
                         ->with('success', 'Sel Checklist Control berhasil diperbarui.');
    }

    /**
     * GET /kontrol
     * Dashboard Checklist Control bulanan.
     */
     
        public function pdf($request)
    {
        $lokasi   = $request->getGet('lokasi') ?: Lokasi::MFG1->value;
        $kategori = $request->getGet('kategori') ?: 'Penerangan';
        $bulan    = $request->getGet('bulan') ?: date('Y-m');
        $line     = $request->getGet('line') ?: null;

        if ($lokasi === Lokasi::MFG2->value) {
            $categories = [
                'Penerangan'     => 'Penerangan',
                'Kabel dan Pipa' => 'Kabel dan Pipa',
                'Angin Bocor'    => 'Angin Bocor',
            ];
        } else {
            $categories = [
                'Penerangan'     => 'Penerangan',
                'Kabel dan Pipa' => 'Kabel dan Pipa',
                'Angin Bocor'    => 'Angin Bocor',
                'Bearing Cam'    => 'Bearing Cam',
                'Gearbox'        => 'Gearbox',
                'Belt Cam'       => 'Belt Cam',
            ];
        }

        if (!isset($categories[$kategori])) {
            $kategori = 'Penerangan';
        }

        $availableLines = [];
        if ($lokasi === Lokasi::MFG1->value) {
            $availableLines = ['Line 1', 'Line 2', 'Line 3'];
        } elseif ($lokasi === Lokasi::MFG2->value) {
            $availableLines = ['CG', 'Second'];
        }

        if (empty($line) && !empty($availableLines)) {
            $line = $availableLines[0];
        }

        $grid = (new \App\Models\CeklisKontrolModel())->getGridData($lokasi, $kategori, $bulan, $line);

        $db = \Config\Database::connect();
        $schedule = $db->table('jadwal_preventive')
                       ->where('lokasi', $lokasi)
                       ->where('kategori', $kategori)
                       ->where('bulan_tahun', $bulan)
                       ->get()
                       ->getRowArray();

        $columnDates = [];
        $hasSchedule = false;

        if ($schedule) {
            $hasSchedule    = true;
            $tglRencana     = strtotime($schedule['tanggal_rencana']);
            $dayOfWeek      = (int) date('N', $tglRencana);
            $mondayTs       = strtotime('-' . ($dayOfWeek - 1) . ' days', $tglRencana);

            for ($d = 0; $d < 5; $d++) {
                $columnDates[$d + 1] = date('Y-m-d', strtotime("+${d} days", $mondayTs));
            }
        }

        $approvalQuery = $db->table('approval_bulanan a')
                       ->select('a.*, u1.nama as l1_name, u2.nama as l2_name, u3.nama as final_name')
                       ->join('users u1', 'u1.id = a.approved_l1_by', 'left')
                       ->join('users u2', 'u2.id = a.approved_l2_by', 'left')
                       ->join('users u3', 'u3.id = a.approved_final_by', 'left')
                       ->where('a.type', 'kontrol')
                       ->where('a.lokasi', $lokasi)
                       ->where('a.kategori', $kategori)
                       ->where('a.bulan_tahun', $bulan);

        if ($line) {
            $approvalQuery->where('a.line', $line);
        } else {
            $approvalQuery->where('a.line', 'NONE');
        }
        
        $approval = $approvalQuery->get()->getRowArray();
        $approvalStatus = $approval ? $approval['status'] : 'Pending';

        $data = [
            'title'          => 'Checklist Control Bulanan',
            'lokasi'         => $lokasi,
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
        $lokasi   = $request->getGet('lokasi') ?: Lokasi::MFG1->value;
        $bulan    = $request->getGet('bulan') ?: date('Y-m');
        $line     = $request->getGet('line') ?: null;

        if ($lokasi === Lokasi::MFG2->value) {
            $categories = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'];
        } else {
            $categories = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'];
        }

        $availableLines = [];
        if ($lokasi === Lokasi::MFG1->value) {
            $availableLines = ['Line 1', 'Line 2', 'Line 3'];
        } elseif ($lokasi === Lokasi::MFG2->value) {
            $availableLines = ['CG', 'Second'];
        }

        if (empty($line) && !empty($availableLines)) {
            $line = $availableLines[0];
        }

        $allGrids = [];
        $db = \Config\Database::connect();
        
        foreach ($categories as $cat) {
            $grid = (new \App\Models\CeklisKontrolModel())->getGridData($lokasi, $cat, $bulan, $line);
            $jadwalModel = new \App\Models\JadwalPreventiveModel();
            $schedule = $jadwalModel->getJadwalForChecklist($lokasi, $cat, $bulan);
                           
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
            $approval = $approvalModel->getApprovalWithUsers($lokasi, $cat, $bulan, $line ?: 'NONE') ?: [];

            $allGrids[] = [
                'kategori'    => $cat,
                'grid'        => $grid,
                'hasSchedule' => $hasSchedule,
                'tglRencana'  => $tglRencanaStr,
                'columnDates' => $columnDates,
                'approvalData'=> $approval
            ];
        }
        
        $bulanList = [];
        for ($i = 0; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $bulanList[$time->format('Y-m')] = $time->toLocalizedString('MMMM yyyy');
        }

        $data = [
            'title'      => "Checklist Control - {$lokasi} - Semua Kategori",
            'lokasi'     => $lokasi,
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
        $filterLokasi = $request->getGet('filter_lokasi') === 'all' ? '' : ($request->getGet('filter_lokasi') ?: '');
        $filterLine = $request->getGet('filter_line') === 'all' ? '' : ($request->getGet('filter_line') ?: '');
        $filterKategori = $request->getGet('filter_kategori') === 'all' ? '' : ($request->getGet('filter_kategori') ?: '');
        
        $lokasiList = [
            Lokasi::MFG1->value => ['Line 1', 'Line 2', 'Line 3'],
            Lokasi::MFG2->value => ['CG', 'Second']
        ];
        
        $allGrids = [];
        $db = \Config\Database::connect();
        
        foreach ($lokasiList as $lokasi => $lines) {
            if (!empty($filterLokasi) && $lokasi !== $filterLokasi) continue;
            
            $categories = ($lokasi === Lokasi::MFG2->value)
                ? ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor']
                : ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'];
                
            foreach ($lines as $line) {
                if (!empty($filterLine) && $line !== $filterLine) continue;
                
                foreach ($categories as $cat) {
                    if (!empty($filterKategori) && $cat !== $filterKategori) continue;
                    
                    $grid = (new \App\Models\CeklisKontrolModel())->getGridData($lokasi, $cat, $bulan, $line);
                    
                    if (empty($grid)) continue;

                    // Skip if completely unfilled (no PIC assigned)
                    $hasData = false;
                    foreach ($grid as $row) {
                        if ($row['pic_nama'] !== 'PIC' && !empty($row['pic_nama'])) {
                            $hasData = true;
                            break;
                        }
                    }
                    if (!$hasData) continue;
                    $jadwalModel = new \App\Models\JadwalPreventiveModel();
            $schedule = $jadwalModel->getJadwalForChecklist($lokasi, $cat, $bulan);
                                   
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

                    $approvalQuery = $db->table('approval_bulanan a')
                                   ->select('a.*, u1.nama as l1_name, u2.nama as l2_name, u3.nama as final_name')
                                   ->join('users u1', 'u1.id = a.approved_l1_by', 'left')
                                   ->join('users u2', 'u2.id = a.approved_l2_by', 'left')
                                   ->join('users u3', 'u3.id = a.approved_final_by', 'left')
                                   ->where('a.type', 'kontrol')
                                   ->where('a.lokasi', $lokasi)
                                   ->where('a.kategori', $cat)
                                   ->where('a.bulan_tahun', $bulan);

                    if ($line) {
                        $approvalQuery->where('a.line', $line);
                    } else {
                        $approvalQuery->where('a.line', 'NONE');
                    }
                    
                    $approval = $approvalQuery->get()->getRowArray() ?: [];

                    $allGrids[] = [
                        'lokasi'      => $lokasi,
                        'line'        => $line,
                        'kategori'    => $cat,
                        'grid'        => $grid,
                        'hasSchedule' => $hasSchedule,
                        'tglRencana'  => $tglRencanaStr,
                        'columnDates' => $columnDates,
                        'approvalData'=> $approval
                    ];
                }
            }
        }
        
        $bulanList = [];
        for ($i = 0; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $bulanList[$time->format('Y-m')] = $time->toLocalizedString('MMMM yyyy');
        }

        $data = [
            'title'      => "Checklist Control - Ringkasan Semua Area",
            'lokasi'     => 'SEMUA AREA',
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
        if ($request->getGet('view') === 'summary' || (!$request->getGet('lokasi') && !$request->getGet('line') && !$request->getGet('kategori'))) {
            return $this->summary($request);
        }

        $lokasi   = $request->getGet('lokasi') ?: Lokasi::MFG1->value;
        $kategori = $request->getGet('kategori') ?: 'Penerangan';
        $bulan    = $request->getGet('bulan') ?: date('Y-m');
        $line     = $request->getGet('line') ?: null;

        // Daftar kategori khusus Preventive
        if ($lokasi === Lokasi::MFG2->value) {
            $categories = [
                'Penerangan'     => 'Penerangan',
                'Kabel dan Pipa' => 'Kabel dan Pipa',
                'Angin Bocor'    => 'Angin Bocor',
            ];
        } else {
            $categories = [
                'Penerangan'     => 'Penerangan',
                'Kabel dan Pipa' => 'Kabel dan Pipa',
                'Angin Bocor'    => 'Angin Bocor',
                'Bearing Cam'    => 'Bearing Cam',
                'Gearbox'        => 'Gearbox',
                'Belt Cam'       => 'Belt Cam',
            ];
        }

        // Fallback jika kategori tidak valid untuk lokasi saat ini
        if (!isset($categories[$kategori])) {
            $kategori = 'Penerangan';
        }

        // Buat list 12 bulan terakhir untuk dropdown filter
        $bulanList = [];
        for ($i = 0; $i < 12; $i++) {
            $time = Time::now()->subMonths($i);
            $val  = $time->format('Y-m');
            $label = $time->toLocalizedString('MMMM yyyy');
            $bulanList[$val] = $label;
        }

        $availableLines = [];
        if ($lokasi === Lokasi::MFG1->value) {
            $availableLines = ['Line 1', 'Line 2', 'Line 3'];
        } elseif ($lokasi === Lokasi::MFG2->value) {
            $availableLines = ['CG', 'Second'];
        }

        if (empty($line) && !empty($availableLines)) {
            $line = $availableLines[0];
        }

        $grid = (new \App\Models\CeklisKontrolModel())->getGridData($lokasi, $kategori, $bulan, $line);

        // Ambil jadwal rencana untuk lokasi, kategori, dan bulan berjalan (maks 1 per bulan)
        $db = \Config\Database::connect();
        $schedule = $db->table('jadwal_preventive')
                       ->where('lokasi', $lokasi)
                       ->where('kategori', $kategori)
                       ->where('bulan_tahun', $bulan)
                       ->get()
                       ->getRowArray();

        // Hitung 5 tanggal hari kerja (Senin-Jumat) dari pekan terjadwal
        $columnDates = []; // Array 5 elemen: tanggal Senin s.d Jumat
        $hasSchedule = false;

        if ($schedule) {
            $hasSchedule    = true;
            $tglRencana     = strtotime($schedule['tanggal_rencana']);
            $dayOfWeek      = (int) date('N', $tglRencana); // 1=Senin ... 7=Minggu
            $mondayTs       = strtotime('-' . ($dayOfWeek - 1) . ' days', $tglRencana);

            for ($d = 0; $d < 5; $d++) {
                $columnDates[$d + 1] = date('Y-m-d', strtotime("+{$d} days", $mondayTs));
            }
        }

        $allChecked = true;
        foreach ($grid as $row) {
            if ($row['pic_nama'] === 'PIC') {
                $allChecked = false;
                break;
            }
        }

        // Ambil status approval beserta nama approver
        $approvalQuery = $db->table('approval_bulanan a')
                       ->select('a.*, u1.nama as l1_name, u2.nama as l2_name, u3.nama as final_name')
                       ->join('users u1', 'u1.id = a.approved_l1_by', 'left')
                       ->join('users u2', 'u2.id = a.approved_l2_by', 'left')
                       ->join('users u3', 'u3.id = a.approved_final_by', 'left')
                       ->where('a.type', 'kontrol')
                       ->where('a.lokasi', $lokasi)
                       ->where('a.kategori', $kategori)
                       ->where('a.bulan_tahun', $bulan);

        if ($line) {
            $approvalQuery->where('a.line', $line);
        } else {
            // Jika tidak ada line yang dipilih, paksakan tidak menemukan approval (atau bisa juga buat kondisi 'IS NULL')
            $approvalQuery->where('a.line', 'NONE');
        }
        
        $approval = $approvalQuery->get()->getRowArray();

        $approvalStatus = $approval ? $approval['status'] : 'Pending';

        $roleSession = session()->get('role');
        if ($roleSession === Role::Sheadprd->value && $approvalStatus === 'Pending') {
            return redirect()->to('/kontrol')->with('error', 'Dokumen belum siap untuk Anda (Masih menunggu persetujuan Leader).');
        }
        if ($roleSession === Role::Sheadmtc->value && in_array($approvalStatus, ['Pending', 'Approved L1'], true)) {
            return redirect()->to('/kontrol')->with('error', 'Dokumen belum siap untuk Anda (Masih menunggu persetujuan SHead Produksi).');
        }

        return [
            'title'          => 'Checklist Control Bulanan',
            'lokasi'         => $lokasi,
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
            'staffPicList'   => (new \App\Models\PicModel())->where('role_pic', 'Staff')->findAll(),
        ];
    }

    /**
     * Halaman Ringkasan (Summary) Checklist Control
     */
    public function summary($request)
    {
        $bulan = $request->getGet('bulan') ?: date('Y-m');
        $filterLokasi = $request->getGet('filter_lokasi') === 'all' ? '' : ($request->getGet('filter_lokasi') ?: '');
        $filterLine = $request->getGet('filter_line') === 'all' ? '' : ($request->getGet('filter_line') ?: '');
        $filterKategori = $request->getGet('filter_kategori') === 'all' ? '' : ($request->getGet('filter_kategori') ?: '');
        $filterStatus = $request->getGet('filter_status') === 'all' ? '' : ($request->getGet('filter_status') ?: '');
        $sortBy = $request->getGet('sort_by') ?: 'lokasi';
        $order = strtolower($request->getGet('order') ?: 'asc');

        $db = \Config\Database::connect();

        // 1. Total mesin per Line
        $totalMesinQuery = $db->table('master_mesin')
                              ->select('lokasi, line, COUNT(id_mesin) as total')
                              ->groupBy('lokasi, line')
                              ->get()->getResultArray();
        
        $totalMesin = [];
        $linesByLokasi = [];
        foreach($totalMesinQuery as $tm) {
            $totalMesin[$tm['lokasi']][$tm['line']] = (int) $tm['total'];
            $linesByLokasi[$tm['lokasi']][] = $tm['line'];
        }

        // 2. Checked machines per Line & Category
        $checkedQuery = $db->table('ceklis_kontrol')
                           ->select('master_mesin.lokasi, master_mesin.line, ceklis_kontrol.kategori, COUNT(DISTINCT ceklis_kontrol.id_mesin) as checked_count')
                           ->join('master_mesin', 'master_mesin.id_mesin = ceklis_kontrol.id_mesin')
                           ->where('ceklis_kontrol.bulan_tahun', $bulan)
                           ->where("ceklis_kontrol.pic_nama != 'PIC'")
                           ->where("ceklis_kontrol.pic_nama IS NOT NULL")
                           ->groupBy('master_mesin.lokasi, master_mesin.line, ceklis_kontrol.kategori')
                           ->get()->getResultArray();
                           
        $checkedData = [];
        foreach($checkedQuery as $cq) {
            $checkedData[$cq['lokasi']][$cq['line']][$cq['kategori']] = (int) $cq['checked_count'];
        }

        // 3. Approval status
        $approvals = $db->table('approval_bulanan')
                        ->where('type', 'kontrol')
                        ->where('bulan_tahun', $bulan)
                        ->get()->getResultArray();
                        
        $approvalData = [];
        foreach($approvals as $ap) {
            $approvalData[$ap['lokasi']][$ap['line']][$ap['kategori']] = $ap['status'];
        }

        // Categories mapping
        $kategoriByLokasi = [
            Lokasi::MFG1->value => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'],
            Lokasi::MFG2->value => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor']
        ];

        // Buat list 12 bulan terakhir untuk dropdown filter
        $bulanList = [];
        for ($i = 0; $i < 12; $i++) {
            $time = Time::now()->subMonths($i);
            $val  = $time->format('Y-m');
            $label = $time->toLocalizedString('MMMM yyyy');
            $bulanList[$val] = $label;
        }

        // Build flat array for summary rows
        $summaryRows = [];
        $notCheckedRows = [];
        foreach ($kategoriByLokasi as $lokasi => $categories) {
            if (!empty($filterLokasi) && $lokasi !== $filterLokasi) continue;
            
            $lines = isset($linesByLokasi[$lokasi]) ? array_unique($linesByLokasi[$lokasi]) : [];
            sort($lines);

            foreach ($lines as $line) {
                if (!empty($filterLine) && $line !== $filterLine) continue;

                foreach ($categories as $kategori) {
                    if (!empty($filterKategori) && $kategori !== $filterKategori) continue;
                    
                    $total = $totalMesin[$lokasi][$line] ?? 0;
                    if ($total == 0) continue;
                    
                    $checked = $checkedData[$lokasi][$line][$kategori] ?? 0;
                    
                    if ($checked == 0) {
                        $notCheckedRows[] = [
                            'lokasi'   => $lokasi,
                            'line'     => $line,
                            'kategori' => $kategori
                        ];
                        continue;
                    }
                    
                    $percent = $total > 0 ? round(($checked / $total) * 100) : 0;
                    
                    $status = $approvalData[$lokasi][$line][$kategori] ?? '';
                    
                    // Filter based on role
                    $roleSession = session()->get('role');
                    if ($roleSession === Role::Sheadprd->value && (empty($status) || in_array($status, ['Pending', 'Approved L1'], true))) {
                        continue;
                    }
                    if ($roleSession === Role::Sheadmtc->value && (empty($status) || in_array($status, ['Pending', 'Approved L1', 'Approved L2'], true))) {
                        continue;
                    }
                    $badgeClass = 'bg-secondary';
                    $statusText = 'Belum Selesai';
                    
                    if ($percent == 100) {
                        if (empty($status) || $status === 'Pending') {
                            $badgeClass = 'bg-warning text-dark';
                            $statusText = 'Menunggu Approval (L1)';
                        } elseif ($status === 'Approved L1') {
                            $badgeClass = 'bg-info text-dark';
                            $statusText = 'Approved L1 (Menunggu L2)';
                        } elseif ($status === 'Approved L2') {
                            $badgeClass = 'bg-primary';
                            $statusText = 'Approved L2 (Menunggu Final)';
                        } elseif ($status === 'Final' || $status === 'Approved Final') {
                            $badgeClass = 'bg-success';
                            $statusText = 'Selesai (Final)';
                        }
                    }
                    
                    // History hanya menampilkan yang sudah Final untuk role member/admin/leader
                    // Role approver (sheadprd/sheadmtc) sudah difilter di atas
                    if (in_array($roleSession, [Role::Member->value, Role::Admin->value, Role::Leader->value], true)) {
                        if (!in_array($status, ['Final', 'Approved Final'], true)) {
                            continue; // Belum selesai → tetap di Approval Inbox
                        }
                    }
                    
                    if (!empty($filterStatus) && $statusText !== $filterStatus) continue;

                    $summaryRows[] = [
                        'lokasi'      => $lokasi,
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
        $role = session()->get('role');
        if (!in_array($role, [Role::Member->value, Role::Sheadprd->value, Role::Sheadmtc->value, Role::Admin->value])) {
            return ["status" => false, "message" => 'Akses ditolak.'];
        }

        $lokasi   = $request->getPost('lokasi');
        $line     = $request->getPost('line');
        $kategori = $request->getPost('kategori');
        $bulan    = $request->getPost('bulan_tahun');

        if (empty($line)) {
            return ["status" => false, "message" => 'Silakan pilih Line terlebih dahulu untuk melakukan persetujuan.'];
        }

        $db = \Config\Database::connect();
        $approval = $db->table('approval_bulanan')
                       ->where('type', 'kontrol')
                       ->where('lokasi', $lokasi)
                       ->where('line', $line)
                       ->where('kategori', $kategori)
                       ->where('bulan_tahun', $bulan)
                       ->get()
                       ->getRowArray();

        $currentStatus = $approval ? $approval['status'] : 'Pending';
        $userId        = session()->get('user_id');
        $now           = date('Y-m-d H:i:s');

        $data = [
            'type'        => 'kontrol',
            'lokasi'      => $lokasi,
            'line'        => $line,
            'kategori'    => $kategori,
            'bulan_tahun' => $bulan,
            'updated_at'  => $now,
        ];

        // Admin override
        if ($role === Role::Admin->value) {
            $data['status'] = 'Approved Final';
            $data['approved_final_by'] = $userId;
            $data['approved_final_at'] = $now;
        } elseif ($role === Role::Member->value) {
            if ($currentStatus !== 'Pending') return ["status" => false, "message" => 'Sudah diproses L1.'];
            
            $picLineNama = $request->getPost('pic_line_nama');
            if (empty(trim($picLineNama))) {
                return ["status" => false, "message" => 'Nama PIC Line wajib diisi.'];
            }
            
            $data['status'] = 'Approved L1';
            $data['approved_l1_by'] = $userId;
            $data['pic_line_nama']  = trim($picLineNama);
            $data['approved_l1_at'] = $now;
        } elseif ($role === Role::Sheadprd->value) {
            if ($currentStatus !== 'Approved L1') return ["status" => false, "message" => 'Belum disetujui L1.'];
            $data['status'] = 'Approved L2';
            $data['approved_l2_by'] = $userId;
            $data['approved_l2_at'] = $now;
        } elseif ($role === Role::Sheadmtc->value) {
            if ($currentStatus !== 'Approved L2') return ["status" => false, "message" => 'Belum disetujui L2.'];
            $data['status'] = 'Approved Final';
            $data['approved_final_by'] = $userId;
            $data['approved_final_at'] = $now;
        }

        if ($approval) {
            $db->table('approval_bulanan')->where('id_approval', $approval['id_approval'])->update($data);
        } else {
            $data['created_at'] = $now;
            $db->table('approval_bulanan')->insert($data);
        }

        return ["status" => true, "message" => 'Berhasil menyetujui Checklist Control bulanan.'];
    }

    /**
     * POST /kontrol/delete-approval
     * Menghapus record approval (Khusus Admin)
     */
    public function deleteApprovalBulanan($request)
    {
        if (session()->get('role') !== Role::Admin->value) {
            return ["status" => false, "message" => 'Akses ditolak.'];
        }

        $lokasi   = $request->getPost('lokasi');
        $line     = $request->getPost('line');
        $kategori = $request->getPost('kategori');
        $bulan    = $request->getPost('bulan_tahun');

        $approvalModel = new \App\Models\ApprovalBulananModel();
        $approvalModel->deleteApprovalKontrol($lokasi, $line, $kategori, $bulan);

        return ["status" => true, "message" => 'Data approval Checklist Control berhasil dihapus (Reset ke Belum Selesai).'];
    }
}
