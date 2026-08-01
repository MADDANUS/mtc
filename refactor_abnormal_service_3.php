<?php

$file = 'app/Services/AbnormalService.php';
$content = file_get_contents($file);

// Replace 9: overhaulPdfAllCategories()
$content = preg_replace(
<<<'REGEX'
/\$db = \\Config\\Database::connect\(\);\s*\$builder = \$db->table\('laporan_abnormal'\)\s*->select\('laporan_abnormal\.\*, master_mesin\.no_mesin, master_mesin\.type_mesin, master_mesin\.lokasi, transaksi_check\.kategori'\)\s*->join\('master_mesin', 'master_mesin\.id_mesin = laporan_abnormal\.id_mesin'\)\s*->join\('transaksi_check', 'transaksi_check\.id_transaksi = laporan_abnormal\.id_transaksi', 'left'\)\s*->where\('transaksi_check\.jenis_check', JenisCheck::Overhaul->value\);\s*if \(!empty\(\$lokasiFilter\) && \$lokasiFilter !== 'all'\) \{\s*\$builder->where\('master_mesin\.lokasi', \$lokasiFilter\);\s*\}\s*if \(!empty\(\$bulanFilter\) && \$bulanFilter !== 'all'\) \{\s*\$builder->like\('laporan_abnormal\.pengecekan_tanggal', \$bulanFilter \. '-', 'after'\);\s*\}\s*if \(!empty\(\$searchFilter\)\) \{\s*\$builder->groupStart\(\)\s*->like\('laporan_abnormal\.point_check', \$searchFilter\)\s*->orLike\('laporan_abnormal\.abnormal_condition', \$searchFilter\)\s*->orLike\('master_mesin\.no_mesin', \$searchFilter\)\s*->orLike\('master_mesin\.type_mesin', \$searchFilter\)\s*->groupEnd\(\);\s*\}\s*\$reports = \$builder->orderBy\('laporan_abnormal\.pengecekan_tanggal', 'DESC'\)\s*->orderBy\('laporan_abnormal\.id_abnormal', 'DESC'\)\s*->get\(\)\s*->getResultArray\(\);/s
REGEX,
<<<'PHP'
$abnormalModel = new \App\Models\LaporanAbnormalModel();
        $reports = $abnormalModel->getOverhaulLaporan($lokasiFilter, $bulanFilter, $searchFilter);
PHP,
$content, 1);

// Replace 10: dashboardSummaryOverhaul() inside foreach ([Lokasi...])
$content = preg_replace(
<<<'REGEX'
/\$builder = \$db->table\('laporan_abnormal'\)\s*->select\('laporan_abnormal\.\*, master_mesin\.no_mesin, master_mesin\.type_mesin, master_mesin\.lokasi'\)\s*->join\('master_mesin', 'master_mesin\.id_mesin = laporan_abnormal\.id_mesin'\)\s*->join\('transaksi_check', 'transaksi_check\.id_transaksi = laporan_abnormal\.id_transaksi', 'left'\)\s*->where\('transaksi_check\.jenis_check', JenisCheck::Overhaul->value\)\s*->where\('master_mesin\.lokasi', \$lokasi\)\s*->like\('laporan_abnormal\.pengecekan_tanggal', \$bulanFilter \. '-', 'after'\);\s*\$reports = \$builder->get\(\)->getResultArray\(\);/s
REGEX,
<<<'PHP'
$abnormalModel = new \App\Models\LaporanAbnormalModel();
            $reports = $abnormalModel->getOverhaulDashboardSummaryLaporan($lokasi, $bulanFilter);
PHP,
$content, 1);

// Replace 11: dashboardSummaryOverhaul() outside foreach
$content = preg_replace(
<<<'REGEX'
/\$allAbnormal = \$db->table\('laporan_abnormal'\)\s*->select\('SUM\(CASE WHEN laporan_abnormal\.action IS NULL OR laporan_abnormal\.action = \\'\\' THEN 1 ELSE 0 END\) as totalOpen,\s*COUNT\(laporan_abnormal\.id_abnormal\) as totalAll'\)\s*->join\('transaksi_check', 'transaksi_check\.id_transaksi = laporan_abnormal\.id_transaksi', 'left'\)\s*->where\('transaksi_check\.jenis_check', JenisCheck::Overhaul->value\)\s*->like\('laporan_abnormal\.pengecekan_tanggal', \$bulanFilter \. '-', 'after'\)\s*->get\(\)->getRowArray\(\);/s
REGEX,
<<<'PHP'
$abnormalModel = new \App\Models\LaporanAbnormalModel();
        $allAbnormal = $abnormalModel->getOverhaulDashboardSummaryTotals($bulanFilter);
PHP,
$content, 1);


file_put_contents($file, $content);
echo "AbnormalService part 3 refactored.";
