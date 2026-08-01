<?php

$file = 'app/Services/ApprovalService.php';
$content = file_get_contents($file);

// Replace 1: TransaksiCheckModel logic
$content = preg_replace(
<<<'REGEX'
/\$txBuilder = \$db->table\('transaksi_check tc'\).*?\$transaksiRows = \$txBuilder->orderBy\('tc\.waktu_mulai', 'DESC'\)->get\(\)->getResultArray\(\);/s
REGEX,
<<<'PHP'
$transaksiModel = new \App\Models\TransaksiCheckModel();
        if (!in_array($role, [\App\Enums\Role::Leader->value, \App\Enums\Role::Sheadprd->value, \App\Enums\Role::Sheadmtc->value, \App\Enums\Role::Member->value, \App\Enums\Role::Admin->value])) {
            return redirect()->to('/dashboard')->with('error', 'Akses tidak diizinkan.');
        }
        $transaksiRows = $transaksiModel->getInboxApprovalTransaksi($role, $line);
PHP,
$content, 1);

// Replace 2: ApprovalBulananModel (kontrolBuilder logic)
$content = preg_replace(
<<<'REGEX'
/\$kontrolBuilder = \$db->table\('approval_bulanan ab'\).*?\$approvalRows = \$kontrolBuilder->orderBy\('ab\.bulan_tahun', 'DESC'\)->get\(\)->getResultArray\(\);/s
REGEX,
<<<'PHP'
$approvalModel = new \App\Models\ApprovalBulananModel();
            $approvalRows = $approvalModel->getInboxApprovalKontrol($role);
PHP,
$content, 1);

// Replace 3: master_mesin (totalMesinData logic)
$content = preg_replace(
<<<'REGEX'
/\$totalMesinData = \$db->table\('master_mesin'\)\s*->select\('lokasi, line, COUNT\(id_mesin\) AS total'\)\s*->groupBy\('lokasi, line'\)\s*->get\(\)->getResultArray\(\);/s
REGEX,
<<<'PHP'
$mesinModel = new \App\Models\MesinModel();
                $totalMesinData = $mesinModel->getTotalMesinPerLine();
PHP,
$content, 1);

// Replace 4: ceklis_kontrol (checkedData logic)
$content = preg_replace(
<<<'REGEX'
/\$checkedData = \$db->table\('ceklis_kontrol ck'\)\s*->select\('mm\.lokasi, mm\.line, ck\.kategori, COUNT\(DISTINCT ck\.id_mesin\) AS checked'\)\s*->join\('master_mesin mm', 'mm\.id_mesin = ck\.id_mesin'\)\s*->where\('ck\.bulan_tahun', \$bulanIni\)\s*->where\("ck\.pic_nama != 'PIC'"\)\s*->where\("ck\.pic_nama IS NOT NULL"\)\s*->groupBy\('mm\.lokasi, mm\.line, ck\.kategori'\)\s*->get\(\)->getResultArray\(\);/s
REGEX,
<<<'PHP'
$ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
                $checkedData = $ceklisKontrolModel->getCheckedMachinesCount($bulanIni);
PHP,
$content, 1);

// Replace 5: approval_bulanan (existingApprovals logic)
$content = preg_replace(
<<<'REGEX'
/\$existingApprovals = \$db->table\('approval_bulanan'\)\s*->select\('lokasi, line, kategori'\)\s*->where\('type', 'kontrol'\)\s*->where\('bulan_tahun', \$bulanIni\)\s*->get\(\)->getResultArray\(\);/s
REGEX,
<<<'PHP'
$existingApprovals = $approvalModel->getExistingApprovals($bulanIni);
PHP,
$content, 1);


file_put_contents($file, $content);
echo "ApprovalService refactored.";
