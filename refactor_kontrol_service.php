<?php

$file = 'app/Services/KontrolService.php';
$content = file_get_contents($file);

// Replace 1 (jadwal_preventive inside getKontrolExportData)
$content = str_replace(
<<<'PHP'
                    $schedule = $db->table('jadwal_preventive')
                                   ->where('lokasi', $lokasi)
                                   ->where('kategori', $cat)
                                   ->where('bulan_tahun', $bulan)
                                   ->get()
                                   ->getRowArray();
PHP,
<<<'PHP'
                    $jadwalModel = new \App\Models\JadwalPreventiveModel();
                    $schedule = $jadwalModel->getJadwalForChecklist($lokasi, $cat, $bulan);
PHP,
$content);

// Replace 2 (approval_bulanan inside getKontrolExportData)
$content = str_replace(
<<<'PHP'
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
                    }
                    $approval = $approvalQuery->get()->getRowArray();
PHP,
<<<'PHP'
                    $approvalModel = new \App\Models\ApprovalBulananModel();
                    $approval = $approvalModel->getApprovalWithUsers($lokasi, $cat, $bulan, $line);
PHP,
$content);

// Replace 3 (jadwal_preventive inside getKontrolViewData)
$content = str_replace(
<<<'PHP'
        // Ambil jadwal rencana untuk lokasi, kategori, dan bulan berjalan (maks 1 per bulan)
        $db = \Config\Database::connect();
        $schedule = $db->table('jadwal_preventive')
                       ->where('lokasi', $lokasi)
                       ->where('kategori', $kategori)
                       ->where('bulan_tahun', $bulan)
                       ->get()
                       ->getRowArray();
PHP,
<<<'PHP'
        // Ambil jadwal rencana untuk lokasi, kategori, dan bulan berjalan (maks 1 per bulan)
        $jadwalModel = new \App\Models\JadwalPreventiveModel();
        $schedule = $jadwalModel->getJadwalForChecklist($lokasi, $kategori, $bulan);
PHP,
$content);

// Replace 4 (approval_bulanan inside getKontrolViewData)
$content = str_replace(
<<<'PHP'
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
        }
        $approval = $approvalQuery->get()->getRowArray();
PHP,
<<<'PHP'
        // Ambil status approval beserta nama approver
        $approvalModel = new \App\Models\ApprovalBulananModel();
        $approval = $approvalModel->getApprovalWithUsers($lokasi, $kategori, $bulan, $line);
PHP,
$content);

// Replace 5 (master_mesin inside dashboardSummary)
$content = str_replace(
<<<'PHP'
        $db = \Config\Database::connect();

        // 1. Total mesin per Line
        $totalMesinQuery = $db->table('master_mesin')
                              ->select('lokasi, line, COUNT(id_mesin) as total')
                              ->groupBy('lokasi, line')
                              ->get()->getResultArray();
PHP,
<<<'PHP'
        // 1. Total mesin per Line
        $mesinModel = new \App\Models\MesinModel();
        $totalMesinQuery = $mesinModel->getTotalMesinPerLine();
PHP,
$content);

// Replace 6 (ceklis_kontrol inside dashboardSummary)
$content = str_replace(
<<<'PHP'
        // 2. Checked machines per Line & Category
        $checkedQuery = $db->table('ceklis_kontrol')
                           ->select('master_mesin.lokasi, master_mesin.line, ceklis_kontrol.kategori, COUNT(DISTINCT ceklis_kontrol.id_mesin) as checked_count')
                           ->join('master_mesin', 'master_mesin.id_mesin = ceklis_kontrol.id_mesin')
                           ->where('ceklis_kontrol.bulan_tahun', $bulan)
                           ->where("ceklis_kontrol.pic_nama != 'PIC'")
                           ->where("ceklis_kontrol.pic_nama IS NOT NULL")
                           ->groupBy('master_mesin.lokasi, master_mesin.line, ceklis_kontrol.kategori')
                           ->get()->getResultArray();
PHP,
<<<'PHP'
        // 2. Checked machines per Line & Category
        $ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
        $checkedQuery = $ceklisKontrolModel->getCheckedMachinesCount($bulan);
PHP,
$content);

// Replace 7 (approval_bulanan inside dashboardSummary)
$content = str_replace(
<<<'PHP'
        // 3. Approval status
        $approvals = $db->table('approval_bulanan')
                        ->where('type', 'kontrol')
                        ->where('bulan_tahun', $bulan)
                        ->get()->getResultArray();
PHP,
<<<'PHP'
        // 3. Approval status
        $approvalModel = new \App\Models\ApprovalBulananModel();
        $approvals = $approvalModel->getAllApprovals($bulan);
PHP,
$content);

// Replace 8 (approval_bulanan inside processApprovalBulanan)
$content = str_replace(
<<<'PHP'
        $db = \Config\Database::connect();
        $approval = $db->table('approval_bulanan')
                       ->where('type', 'kontrol')
                       ->where('lokasi', $lokasi)
                       ->where('line', $line)
                       ->where('kategori', $kategori)
                       ->where('bulan_tahun', $bulan)
                       ->get()
                       ->getRowArray();
PHP,
<<<'PHP'
        $approvalModel = new \App\Models\ApprovalBulananModel();
        $approval = $approvalModel->getApprovalKontrol($lokasi, $line, $kategori, $bulan);
PHP,
$content);

// Replace 9 (approval_bulanan update/insert inside processApprovalBulanan)
$content = str_replace(
<<<'PHP'
        if ($approval) {
            $db->table('approval_bulanan')->where('id_approval', $approval['id_approval'])->update($data);
        } else {
            $data['created_at'] = $now;
            $db->table('approval_bulanan')->insert($data);
        }
PHP,
<<<'PHP'
        if ($approval) {
            $approvalModel->update($approval['id_approval'], $data);
        } else {
            $data['type'] = 'kontrol';
            $data['lokasi'] = $lokasi;
            $data['line'] = $line;
            $data['kategori'] = $kategori;
            $data['bulan_tahun'] = $bulan;
            $data['created_at'] = $now;
            $approvalModel->insert($data);
        }
PHP,
$content);
// wait, the original insert logic in processApprovalBulanan didn't include type/lokasi/etc explicitly in $data array if it wasn't already there?
// Let me look at the code of `processApprovalBulanan` carefully.

file_put_contents($file, $content);
echo "KontrolService successfully refactored.";
