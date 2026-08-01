<?php

// 1. TransaksiCheckModel
$file1 = 'app/Models/TransaksiCheckModel.php';
$content1 = file_get_contents($file1);
$methods1 = <<<PHP

    public function getLatestIdByMesinAndKategori(int \$idMesin, string \$kategori, ?string \$bulan): ?array
    {
        \$builder = \$this->select('id_transaksi')
                        ->where('id_mesin', \$idMesin)
                        ->where('kategori', \$kategori);
        if (\$bulan) {
            \$builder->like('waktu_mulai', \$bulan, 'after');
        }
        return \$builder->orderBy('id_transaksi', 'DESC')->first();
    }
PHP;
$content1 = preg_replace('/\}\s*$/', $methods1 . "\n}\n", $content1);
file_put_contents($file1, $content1);

// 2. CeklisKontrolModel
$file2 = 'app/Models/CeklisKontrolModel.php';
$content2 = file_get_contents($file2);
$methods2 = <<<PHP

    public function updateChecklistKontrol(int \$idMesin, string \$kategori, string \$tanggalCheck, array \$data): bool
    {
        return \$this->where('id_mesin', \$idMesin)
                    ->where('kategori', \$kategori)
                    ->where('tanggal_check', \$tanggalCheck)
                    ->set(\$data)
                    ->update();
    }

    public function findChecklistKontrol(int \$idMesin, string \$kategori, string \$bulanTahun, ?int \$periodeKe): ?array
    {
        return \$this->where('id_mesin', \$idMesin)
                    ->where('kategori', \$kategori)
                    ->where('bulan_tahun', \$bulanTahun)
                    ->where('periode_ke', \$periodeKe)
                    ->first();
    }
PHP;
$content2 = preg_replace('/\}\s*$/', $methods2 . "\n}\n", $content2);
file_put_contents($file2, $content2);

// 3. JadwalPreventiveModel
$file3 = 'app/Models/JadwalPreventiveModel.php';
$content3 = file_get_contents($file3);
$methods3 = <<<PHP

    public function getJadwalForChecklist(string \$lokasiName, string \$kategoriName, string \$bulanTahun): ?array
    {
        return \$this->where('lokasi', \$lokasiName)
                    ->where('kategori', \$kategoriName)
                    ->where('bulan_tahun', \$bulanTahun)
                    ->first();
    }
PHP;
$content3 = preg_replace('/\}\s*$/', $methods3 . "\n}\n", $content3);
file_put_contents($file3, $content3);

echo "Model methods injected.";
