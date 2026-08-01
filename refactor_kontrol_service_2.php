<?php

$file = 'app/Services/KontrolService.php';
$content = file_get_contents($file);

// Replace 10 (jadwal_preventive inside exportSemuaLine loop)
$content = preg_replace(
    '/\$schedule = \$db->table\(\'jadwal_preventive\'\)\s*->where\(\'lokasi\', \$lokasi\)\s*->where\(\'kategori\', \$cat\)\s*->where\(\'bulan_tahun\', \$bulan\)\s*->get\(\)\s*->getRowArray\(\);/',
    '$jadwalModel = new \App\Models\JadwalPreventiveModel();
            $schedule = $jadwalModel->getJadwalForChecklist($lokasi, $cat, $bulan);',
    $content
);

// Replace 11 (approval_bulanan inside exportSemuaLine loop)
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
            } else {
                $approvalQuery->where('a.line', 'NONE');
            }
            
            $approval = $approvalQuery->get()->getRowArray() ?: [];
PHP,
<<<'PHP'
            $approvalModel = new \App\Models\ApprovalBulananModel();
            $approval = $approvalModel->getApprovalWithUsers($lokasi, $cat, $bulan, $line ?: 'NONE') ?: [];
PHP,
$content);

// Replace 12 (deleteApprovalBulanan)
$content = str_replace(
<<<'PHP'
        $db = \Config\Database::connect();
        $db->table('approval_bulanan')
           ->where('type', 'kontrol')
           ->where('lokasi', $lokasi)
           ->where('line', $line)
           ->where('kategori', $kategori)
           ->where('bulan_tahun', $bulan)
           ->delete();
PHP,
<<<'PHP'
        $approvalModel = new \App\Models\ApprovalBulananModel();
        $approvalModel->deleteApprovalKontrol($lokasi, $line, $kategori, $bulan);
PHP,
$content);


file_put_contents($file, $content);
echo "KontrolService extra refactored.";
