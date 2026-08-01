<?php

$file = 'app/Services/AbnormalService.php';
$content = file_get_contents($file);

// Replace 1: pdf()
$content = preg_replace(
<<<'REGEX'
/\$db = \\Config\\Database::connect\(\);\s*\$builder = \$db->table\('laporan_abnormal'\)\s*->select\('laporan_abnormal\.\*, master_mesin\.no_mesin, master_mesin\.type_mesin, master_mesin\.lokasi, transaksi_check\.kategori'\)\s*->join\('master_mesin', 'master_mesin\.id_mesin = laporan_abnormal\.id_mesin'\)\s*->join\('transaksi_check', 'transaksi_check\.id_transaksi = laporan_abnormal\.id_transaksi', 'left'\);\s*if \(!empty\(\$lokasiFilter\)\) \{\s*\$builder->where\('master_mesin\.lokasi', \$lokasiFilter\);\s*\}\s*if \(!empty\(\$kategoriFilter\)\) \{\s*\$builder->where\('transaksi_check\.kategori', \$kategoriFilter\);\s*\}\s*if \(!empty\(\$bulanFilter\)\) \{\s*\$builder->like\('laporan_abnormal\.pengecekan_tanggal', \$bulanFilter \. '-', 'after'\);\s*\}\s*if \(!empty\(\$searchFilter\)\) \{\s*\$builder->groupStart\(\)\s*->like\('laporan_abnormal\.point_check', \$searchFilter\)\s*->orLike\('laporan_abnormal\.abnormal_condition', \$searchFilter\)\s*->orLike\('master_mesin\.no_mesin', \$searchFilter\)\s*->orLike\('master_mesin\.type_mesin', \$searchFilter\)\s*->groupEnd\(\);\s*\}\s*\$reports = \$builder->orderBy\('laporan_abnormal\.pengecekan_tanggal', 'DESC'\)\s*->orderBy\('laporan_abnormal\.id_abnormal', 'DESC'\)\s*->get\(\)\s*->getResultArray\(\);/s
REGEX,
<<<'PHP'
$abnormalModel = new \App\Models\LaporanAbnormalModel();
        $reports = $abnormalModel->getPdfLaporan($lokasiFilter, $kategoriFilter, $bulanFilter, $searchFilter);
PHP,
$content, 1);

// Replace 2: pdfAllCategories()
$content = preg_replace(
<<<'REGEX'
/\$builder = \$db->table\('laporan_abnormal'\)\s*->select\('laporan_abnormal\.\*, master_mesin\.no_mesin, master_mesin\.type_mesin, master_mesin\.lokasi, transaksi_check\.kategori'\)\s*->join\('master_mesin', 'master_mesin\.id_mesin = laporan_abnormal\.id_mesin'\)\s*->join\('transaksi_check', 'transaksi_check\.id_transaksi = laporan_abnormal\.id_transaksi', 'left'\)\s*->where\('master_mesin\.lokasi', \$lokasiFilter\)\s*->where\('transaksi_check\.kategori', \$cat\);\s*if \(!empty\(\$bulanFilter\)\) \{\s*\$builder->like\('laporan_abnormal\.pengecekan_tanggal', \$bulanFilter \. '-', 'after'\);\s*\}\s*if \(!empty\(\$searchFilter\)\) \{\s*\$builder->groupStart\(\)\s*->like\('laporan_abnormal\.point_check', \$searchFilter\)\s*->orLike\('laporan_abnormal\.abnormal_condition', \$searchFilter\)\s*->orLike\('master_mesin\.no_mesin', \$searchFilter\)\s*->orLike\('master_mesin\.type_mesin', \$searchFilter\)\s*->groupEnd\(\);\s*\}\s*\$reports = \$builder->orderBy\('laporan_abnormal\.pengecekan_tanggal', 'DESC'\)\s*->orderBy\('laporan_abnormal\.id_abnormal', 'DESC'\)\s*->get\(\)\s*->getResultArray\(\);/s
REGEX,
<<<'PHP'
$abnormalModel = new \App\Models\LaporanAbnormalModel();
            $reports = $abnormalModel->getPdfAllCategoriesLaporan($lokasiFilter, $cat, $bulanFilter, $searchFilter);
PHP,
$content, 1);
$content = str_replace('$db = \Config\Database::connect();', '', $content); // I will handle carefully if needed, or leave it unused.

// Replace 3: pdfAllSummary()
$content = preg_replace(
<<<'REGEX'
/\$builder = \$db->table\('laporan_abnormal'\)\s*->select\('laporan_abnormal\.\*, master_mesin\.no_mesin, master_mesin\.type_mesin, master_mesin\.lokasi, transaksi_check\.kategori'\)\s*->join\('master_mesin', 'master_mesin\.id_mesin = laporan_abnormal\.id_mesin'\)\s*->join\('transaksi_check', 'transaksi_check\.id_transaksi = laporan_abnormal\.id_transaksi', 'left'\)\s*->where\('master_mesin\.lokasi', \$lokasi\)\s*->where\('transaksi_check\.kategori', \$cat\);\s*if \(!empty\(\$bulanFilter\)\) \{\s*\$builder->like\('laporan_abnormal\.pengecekan_tanggal', \$bulanFilter \. '-', 'after'\);\s*\}\s*if \(!empty\(\$filterLine\)\) \{\s*\$builder->where\('master_mesin\.line', \$filterLine\);\s*\}\s*\$reports = \$builder->orderBy\('laporan_abnormal\.pengecekan_tanggal', 'DESC'\)\s*->orderBy\('laporan_abnormal\.id_abnormal', 'DESC'\)\s*->get\(\)\s*->getResultArray\(\);/s
REGEX,
<<<'PHP'
$abnormalModel = new \App\Models\LaporanAbnormalModel();
                $reports = $abnormalModel->getPdfAllSummaryLaporan($lokasi, $cat, $bulanFilter, $filterLine);
PHP,
$content, 1);

// Replace 4: index()
$content = preg_replace(
<<<'REGEX'
/\$db = \\Config\\Database::connect\(\);\s*\$builder = \$db->table\('laporan_abnormal'\)\s*->select\('laporan_abnormal\.\*, master_mesin\.no_mesin, master_mesin\.type_mesin, master_mesin\.lokasi, transaksi_check\.kategori'\)\s*->join\('master_mesin', 'master_mesin\.id_mesin = laporan_abnormal\.id_mesin'\)\s*->join\('transaksi_check', 'transaksi_check\.id_transaksi = laporan_abnormal\.id_transaksi', 'left'\);\s*if \(!empty\(\$lokasiFilter\)\) \{\s*\$builder->where\('master_mesin\.lokasi', \$lokasiFilter\);\s*\}\s*if \(!empty\(\$kategoriFilter\)\) \{\s*\$builder->where\('transaksi_check\.kategori', \$kategoriFilter\);\s*\}\s*if \(!empty\(\$bulanFilter\)\) \{\s*\$builder->like\('laporan_abnormal\.pengecekan_tanggal', \$bulanFilter \. '-', 'after'\);\s*\}\s*if \(!empty\(\$searchFilter\)\) \{\s*\$builder->groupStart\(\)\s*->like\('laporan_abnormal\.point_check', \$searchFilter\)\s*->orLike\('laporan_abnormal\.abnormal_condition', \$searchFilter\)\s*->orLike\('master_mesin\.no_mesin', \$searchFilter\)\s*->orLike\('master_mesin\.type_mesin', \$searchFilter\)\s*->groupEnd\(\);\s*\}\s*\$reports = \$builder->orderBy\('laporan_abnormal\.pengecekan_tanggal', 'DESC'\)\s*->orderBy\('laporan_abnormal\.id_abnormal', 'DESC'\)\s*->get\(\)\s*->getResultArray\(\);/s
REGEX,
<<<'PHP'
$abnormalModel = new \App\Models\LaporanAbnormalModel();
        $reports = $abnormalModel->getIndexLaporan($lokasiFilter, $kategoriFilter, $bulanFilter, $searchFilter);
PHP,
$content, 1);


file_put_contents($file, $content);
echo "AbnormalService part 1 refactored.";
