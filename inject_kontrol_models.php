<?php

// 1. ApprovalBulananModel
$file1 = 'app/Models/ApprovalBulananModel.php';
$content1 = file_get_contents($file1);
$methods1 = <<<PHP

    public function getApprovalWithUsers(string \$lokasi, string \$kategori, string \$bulan, ?string \$line): ?array
    {
        \$builder = \$this->select('approval_bulanan.*, u1.nama as l1_name, u2.nama as l2_name, u3.nama as final_name')
                        ->join('users u1', 'u1.id = approval_bulanan.approved_l1_by', 'left')
                        ->join('users u2', 'u2.id = approval_bulanan.approved_l2_by', 'left')
                        ->join('users u3', 'u3.id = approval_bulanan.approved_final_by', 'left')
                        ->where('approval_bulanan.type', 'kontrol')
                        ->where('approval_bulanan.lokasi', \$lokasi)
                        ->where('approval_bulanan.kategori', \$kategori)
                        ->where('approval_bulanan.bulan_tahun', \$bulan);
        if (\$line) {
            \$builder->where('approval_bulanan.line', \$line);
        }
        return \$builder->first();
    }

    public function getAllApprovals(string \$bulanTahun): array
    {
        return \$this->where('type', 'kontrol')
                    ->where('bulan_tahun', \$bulanTahun)
                    ->findAll();
    }

    public function getApprovalKontrol(string \$lokasi, string \$line, string \$kategori, string \$bulanTahun): ?array
    {
        return \$this->where('type', 'kontrol')
                    ->where('lokasi', \$lokasi)
                    ->where('line', \$line)
                    ->where('kategori', \$kategori)
                    ->where('bulan_tahun', \$bulanTahun)
                    ->first();
    }

    public function deleteApprovalKontrol(string \$lokasi, string \$line, string \$kategori, string \$bulanTahun): bool
    {
        return \$this->where('type', 'kontrol')
                    ->where('lokasi', \$lokasi)
                    ->where('line', \$line)
                    ->where('kategori', \$kategori)
                    ->where('bulan_tahun', \$bulanTahun)
                    ->delete();
    }
PHP;
$content1 = preg_replace('/\}\s*$/', $methods1 . "\n}\n", $content1);
file_put_contents($file1, $content1);


// 2. MesinModel
$file2 = 'app/Models/MesinModel.php';
$content2 = file_get_contents($file2);
$methods2 = <<<PHP

    public function getTotalMesinPerLine(): array
    {
        return \$this->select('lokasi, line, COUNT(id_mesin) as total')
                    ->groupBy('lokasi, line')
                    ->findAll();
    }
PHP;
$content2 = preg_replace('/\}\s*$/', $methods2 . "\n}\n", $content2);
file_put_contents($file2, $content2);


// 3. CeklisKontrolModel
$file3 = 'app/Models/CeklisKontrolModel.php';
$content3 = file_get_contents($file3);
$methods3 = <<<PHP

    public function getCheckedMachinesCount(string \$bulanTahun): array
    {
        return \$this->select('master_mesin.lokasi, master_mesin.line, ceklis_kontrol.kategori, COUNT(DISTINCT ceklis_kontrol.id_mesin) as checked_count')
                    ->join('master_mesin', 'master_mesin.id_mesin = ceklis_kontrol.id_mesin')
                    ->where('ceklis_kontrol.bulan_tahun', \$bulanTahun)
                    ->where("ceklis_kontrol.pic_nama != 'PIC'")
                    ->where("ceklis_kontrol.pic_nama IS NOT NULL")
                    ->groupBy('master_mesin.lokasi, master_mesin.line, ceklis_kontrol.kategori')
                    ->findAll();
    }
PHP;
$content3 = preg_replace('/\}\s*$/', $methods3 . "\n}\n", $content3);
file_put_contents($file3, $content3);

echo "Model methods injected for KontrolService.";
