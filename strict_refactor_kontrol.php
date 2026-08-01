<?php

$file = 'app/Services/KontrolService.php';
$content = file_get_contents($file);

// Replace 1: jadwal_preventive in exportSemuaLine and export
$content = preg_replace(
    '/\$schedule = \$db->table\(\'jadwal_preventive\'\)\s*->where\(\'lokasi\', \$lokasi\)\s*->where\(\'kategori\', (\$cat|\$kategori)\)\s*->where\(\'bulan_tahun\', \$bulan\)\s*->get\(\)\s*->getRowArray\(\);/',
    '$jadwalModel = new \App\Models\JadwalPreventiveModel();
        $schedule = $jadwalModel->getJadwalForChecklist($lokasi, $1, $bulan);',
    $content
);

// Replace 2: approvalQuery in exportSemuaLine and export
$content = preg_replace(
    '/\$approvalQuery = \$db->table\(\'approval_bulanan a\'\)\s*->select\(\'a\.\*, u1\.nama as l1_name, u2\.nama as l2_name, u3\.nama as final_name\'\)\s*->join\(\'users u1\', \'u1\.id = a\.approved_l1_by\', \'left\'\)\s*->join\(\'users u2\', \'u2\.id = a\.approved_l2_by\', \'left\'\)\s*->join\(\'users u3\', \'u3\.id = a\.approved_final_by\', \'left\'\)\s*->where\(\'a\.type\', \'kontrol\'\)\s*->where\(\'a\.lokasi\', \$lokasi\)\s*->where\(\'a\.kategori\', (\$cat|\$kategori)\)\s*->where\(\'a\.bulan_tahun\', \$bulan\);/',
    '$approvalModel = new \App\Models\ApprovalBulananModel();
        $approval = $approvalModel->getApprovalWithUsers($lokasi, $1, $bulan, $line);
        // DUMMY ASSIGNMENT TO KEEP REST WORKING
        $approvalQuery = true;',
    $content
);

// Replace 2b: fix the if($line) $approvalQuery->where(...) logic for exportSemuaLine and export
$content = preg_replace(
    '/if \(\$line\) \{\s*\$approvalQuery->where\(\'a\.line\', \$line\);\s*\}\s*(else \{\s*\$approvalQuery->where\(\'a\.line\', \'NONE\'\);\s*\})?\s*\$approval = \$approvalQuery->get\(\)->getRowArray\(\)(\s*\?\: \[\])?;/',
    '',
    $content
);

// Replace 3: totalMesinQuery in dashboardSummary
$content = preg_replace(
    '/\$totalMesinQuery = \$db->table\(\'master_mesin\'\)\s*->select\(\'lokasi, line, COUNT\(id_mesin\) as total\'\)\s*->groupBy\(\'lokasi, line\'\)\s*->get\(\)->getResultArray\(\);/',
    '$mesinModel = new \App\Models\MesinModel();
        $totalMesinQuery = $mesinModel->getTotalMesinPerLine();',
    $content
);

// Replace 4: checkedQuery in dashboardSummary
$content = preg_replace(
    '/\$checkedQuery = \$db->table\(\'ceklis_kontrol\'\)\s*->select\(\'master_mesin\.lokasi, master_mesin\.line, ceklis_kontrol\.kategori, COUNT\(DISTINCT ceklis_kontrol\.id_mesin\) as checked_count\'\)\s*->join\(\'master_mesin\', \'master_mesin\.id_mesin = ceklis_kontrol\.id_mesin\'\)\s*->where\(\'ceklis_kontrol\.bulan_tahun\', \$bulan\)\s*->where\("ceklis_kontrol\.pic_nama != \'PIC\'"\)\s*->where\("ceklis_kontrol\.pic_nama IS NOT NULL"\)\s*->groupBy\(\'master_mesin\.lokasi, master_mesin\.line, ceklis_kontrol\.kategori\'\)\s*->get\(\)->getResultArray\(\);/',
    '$ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
        $checkedQuery = $ceklisKontrolModel->getCheckedMachinesCount($bulan);',
    $content
);

// Replace 5: approvals in dashboardSummary
$content = preg_replace(
    '/\$approvals = \$db->table\(\'approval_bulanan\'\)\s*->where\(\'type\', \'kontrol\'\)\s*->where\(\'bulan_tahun\', \$bulan\)\s*->get\(\)->getResultArray\(\);/',
    '$approvalModel = new \App\Models\ApprovalBulananModel();
        $approvals = $approvalModel->getAllApprovals($bulan);',
    $content
);

// Replace 6: approval in approveBulanan
$content = preg_replace(
    '/\$approval = \$db->table\(\'approval_bulanan\'\)\s*->where\(\'type\', \'kontrol\'\)\s*->where\(\'lokasi\', \$lokasi\)\s*->where\(\'line\', \$line\)\s*->where\(\'kategori\', \$kategori\)\s*->where\(\'bulan_tahun\', \$bulan\)\s*->get\(\)\s*->getRowArray\(\);/',
    '$approvalModel = new \App\Models\ApprovalBulananModel();
        $approval = $approvalModel->getApprovalKontrol($lokasi, $line, $kategori, $bulan);',
    $content
);

// Replace 7: update/insert in approveBulanan
$content = preg_replace(
    '/if \(\$approval\) \{\s*\$db->table\(\'approval_bulanan\'\)->where\(\'id_approval\', \$approval\[\'id_approval\'\]\)->update\(\$data\);\s*\} else \{\s*\$data\[\'created_at\'\] = \$now;\s*\$db->table\(\'approval_bulanan\'\)->insert\(\$data\);\s*\}/',
    'if ($approval) {
            $approvalModel->update($approval[\'id_approval\'], $data);
        } else {
            $data[\'created_at\'] = $now;
            $approvalModel->insert($data);
        }',
    $content
);

// Replace 8: delete in deleteApprovalBulanan
$content = preg_replace(
    '/\$db->table\(\'approval_bulanan\'\)\s*->where\(\'type\', \'kontrol\'\)\s*->where\(\'lokasi\', \$lokasi\)\s*->where\(\'line\', \$line\)\s*->where\(\'kategori\', \$kategori\)\s*->where\(\'bulan_tahun\', \$bulan\)\s*->delete\(\);/',
    '$approvalModel = new \App\Models\ApprovalBulananModel();
        $approvalModel->deleteApprovalKontrol($lokasi, $line, $kategori, $bulan);',
    $content
);

file_put_contents($file, $content);
echo "KontrolService strictly refactored.";
