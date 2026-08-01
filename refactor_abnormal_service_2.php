<?php

$file = 'app/Services/AbnormalService.php';
$content = file_get_contents($file);

// Replace 5: summary() -> mesinQuery
$content = preg_replace(
<<<'REGEX'
/\$mesinQuery = \$db->table\('master_mesin'\)\s*->select\('lokasi, line'\)\s*->groupBy\('lokasi, line'\)\s*->get\(\)->getResultArray\(\);/s
REGEX,
<<<'PHP'
$mesinModel = new \App\Models\MesinModel();
        $mesinQuery = $mesinModel->getTotalMesinPerLine();
PHP,
$content, 1);

// Replace 6: summary() -> allAbnormal
$content = preg_replace(
<<<'REGEX'
/\$allAbnormal = \$db->table\('laporan_abnormal'\)\s*->select\('master_mesin\.lokasi, master_mesin\.line, transaksi_check\.kategori, \s*SUM\(CASE WHEN laporan_abnormal\.action IS NULL OR laporan_abnormal\.action = \\'\\' THEN 1 ELSE 0 END\) as totalOpen,\s*COUNT\(laporan_abnormal\.id_abnormal\) as totalAll'\)\s*->join\('master_mesin', 'master_mesin\.id_mesin = laporan_abnormal\.id_mesin'\)\s*->join\('transaksi_check', 'transaksi_check\.id_transaksi = laporan_abnormal\.id_transaksi', 'left'\)\s*->like\('laporan_abnormal\.pengecekan_tanggal', \$bulan \. '-', 'after'\)\s*->groupBy\('master_mesin\.lokasi, master_mesin\.line, transaksi_check\.kategori'\)\s*->get\(\)->getResultArray\(\);/s
REGEX,
<<<'PHP'
$abnormalModel = new \App\Models\LaporanAbnormalModel();
        $allAbnormal = $abnormalModel->getDashboardSummaryAbnormal($bulan);
PHP,
$content, 1);

// Replace 7: overhaul()
$content = preg_replace(
<<<'REGEX'
/\$db = \\Config\\Database::connect\(\);\s*\$builder = \$db->table\('laporan_abnormal'\)\s*->select\('laporan_abnormal\.\*, master_mesin\.no_mesin, master_mesin\.type_mesin, master_mesin\.lokasi, transaksi_check\.kategori'\)\s*->join\('master_mesin', 'master_mesin\.id_mesin = laporan_abnormal\.id_mesin'\)\s*->join\('transaksi_check', 'transaksi_check\.id_transaksi = laporan_abnormal\.id_transaksi', 'left'\);\s*\$builder->where\('transaksi_check\.jenis_check', JenisCheck::Overhaul->value\);\s*if \(!empty\(\$lokasiFilter\) && \$lokasiFilter !== 'all'\) \{\s*\$builder->where\('master_mesin\.lokasi', \$lokasiFilter\);\s*\}\s*\$bulan = date\('Y-m'\);\s*if \(!empty\(\$bulanFilter\)\) \{\s*\$bulan = \$bulanFilter;\s*\}\s*if \(!empty\(\$bulan\) && \$bulan !== 'all'\) \{\s*\$builder->like\('laporan_abnormal\.pengecekan_tanggal', \$bulan \. '-', 'after'\);\s*\}\s*if \(!empty\(\$searchFilter\)\) \{\s*\$builder->groupStart\(\)\s*->like\('laporan_abnormal\.point_check', \$searchFilter\)\s*->orLike\('laporan_abnormal\.abnormal_condition', \$searchFilter\)\s*->orLike\('master_mesin\.no_mesin', \$searchFilter\)\s*->orLike\('master_mesin\.type_mesin', \$searchFilter\)\s*->groupEnd\(\);\s*\}\s*\$reports = \$builder->orderBy\('laporan_abnormal\.pengecekan_tanggal', 'DESC'\)\s*->orderBy\('laporan_abnormal\.id_abnormal', 'DESC'\)\s*->get\(\)\s*->getResultArray\(\);/s
REGEX,
<<<'PHP'
$abnormalModel = new \App\Models\LaporanAbnormalModel();
        $bulan = !empty($bulanFilter) ? $bulanFilter : date('Y-m');
        $reports = $abnormalModel->getOverhaulLaporan($lokasiFilter, $bulan, $searchFilter);
PHP,
$content, 1);

// Replace 8: overhaulPdf()
$content = preg_replace(
<<<'REGEX'
/\$db = \\Config\\Database::connect\(\);\s*\$builder = \$db->table\('laporan_abnormal'\)\s*->select\('laporan_abnormal\.\*, master_mesin\.no_mesin, master_mesin\.type_mesin, master_mesin\.lokasi, transaksi_check\.kategori'\)\s*->join\('master_mesin', 'master_mesin\.id_mesin = laporan_abnormal\.id_mesin'\)\s*->join\('transaksi_check', 'transaksi_check\.id_transaksi = laporan_abnormal\.id_transaksi', 'left'\);\s*\$builder->where\('transaksi_check\.jenis_check', JenisCheck::Overhaul->value\);\s*if \(!empty\(\$bulan\)\) \{\s*\$builder->like\('laporan_abnormal\.pengecekan_tanggal', \$bulan \. '-', 'after'\);\s*\}\s*\$reports = \$builder->orderBy\('laporan_abnormal\.pengecekan_tanggal', 'DESC'\)\s*->orderBy\('laporan_abnormal\.id_abnormal', 'DESC'\)\s*->get\(\)\s*->getResultArray\(\);/s
REGEX,
<<<'PHP'
$abnormalModel = new \App\Models\LaporanAbnormalModel();
        $reports = $abnormalModel->getOverhaulPdfLaporan($bulan);
PHP,
$content, 1);

file_put_contents($file, $content);
echo "AbnormalService part 2 refactored.";
