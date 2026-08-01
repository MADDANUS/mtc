<?php

// 1. TransaksiCheckModel
$file1 = 'app/Models/TransaksiCheckModel.php';
$content1 = file_get_contents($file1);
$methods1 = <<<PHP

    public function getInboxApprovalTransaksi(string \$role, ?string \$line): array
    {
        \$builder = \$this->select('transaksi_check.id_transaksi AS doc_id, transaksi_check.jenis_check, transaksi_check.kategori, transaksi_check.lokasi_check, master_mesin.line, transaksi_check.nama_pic, users.nama AS nama_staff, transaksi_check.waktu_mulai AS doc_date, transaksi_check.status, master_mesin.no_mesin, master_mesin.type_mesin, "transaksi" AS doc_source, NULL AS lokasi, NULL AS persen', false)
                        ->join('users', 'users.id = transaksi_check.id_user', 'left')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin', 'left');

        if (\$role === \App\Enums\Role::Leader->value) {
            \$builder->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                    ->where('transaksi_check.status', 'Pending');
            if (\$line) {
                \$builder->where('master_mesin.line', \$line);
            }
        } elseif (\$role === \App\Enums\Role::Sheadprd->value) {
            \$builder->whereIn('transaksi_check.jenis_check', [\App\Enums\JenisCheck::Overhaul->value, \App\Enums\JenisCheck::Preventive->value])
                    ->where('transaksi_check.status', 'Approved L1');
        } elseif (\$role === \App\Enums\Role::Sheadmtc->value) {
            \$builder->whereIn('transaksi_check.jenis_check', [\App\Enums\JenisCheck::Overhaul->value, \App\Enums\JenisCheck::Preventive->value])
                    ->where('transaksi_check.status', 'Approved L2');
        } elseif (\$role === \App\Enums\Role::Member->value) {
            \$builder->groupStart()
                        ->groupStart()
                            ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Preventive->value)
                            ->where('transaksi_check.status', 'Pending')
                        ->groupEnd()
                        ->orGroupStart()
                            ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                            ->whereIn('transaksi_check.status', ['Pending', 'Approved L1', 'Approved L2'])
                        ->groupEnd()
                    ->groupEnd();
        } elseif (\$role === \App\Enums\Role::Admin->value) {
            \$builder->whereNotIn('transaksi_check.status', ['Approved']);
        }
        
        return \$builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }
PHP;
$content1 = preg_replace('/\}\s*$/', $methods1 . "\n}\n", $content1);
file_put_contents($file1, $content1);


// 2. ApprovalBulananModel
$file2 = 'app/Models/ApprovalBulananModel.php';
$content2 = file_get_contents($file2);
$methods2 = <<<PHP

    public function getInboxApprovalKontrol(string \$role): array
    {
        \$builder = \$this->select('approval_bulanan.id_approval AS doc_id, approval_bulanan.type AS jenis_check, approval_bulanan.kategori, approval_bulanan.lokasi, approval_bulanan.line, approval_bulanan.bulan_tahun AS doc_date, approval_bulanan.status, "kontrol" AS doc_source, NULL AS lokasi_check, NULL AS nama_pic, NULL AS nama_staff, NULL AS no_mesin, NULL AS type_mesin, NULL AS persen', false);

        if (\$role === \App\Enums\Role::Sheadprd->value) {
            \$builder->where('approval_bulanan.status', 'Approved L1');
        } elseif (\$role === \App\Enums\Role::Sheadmtc->value) {
            \$builder->where('approval_bulanan.status', 'Approved L2');
        } elseif (in_array(\$role, [\App\Enums\Role::Member->value, \App\Enums\Role::Admin->value])) {
            \$builder->whereNotIn('approval_bulanan.status', ['Final', 'Approved Final']);
        }

        return \$builder->orderBy('approval_bulanan.bulan_tahun', 'DESC')->findAll();
    }

    public function getExistingApprovals(string \$bulan): array
    {
        return \$this->select('lokasi, line, kategori')
                    ->where('type', 'kontrol')
                    ->where('bulan_tahun', \$bulan)
                    ->findAll();
    }
PHP;
$content2 = preg_replace('/\}\s*$/', $methods2 . "\n}\n", $content2);
file_put_contents($file2, $content2);

echo "ApprovalService model methods injected.";
