<?php

$file = 'app/Models/TransaksiCheckModel.php';
$content = file_get_contents($file);

$methods = <<<PHP

    public function getTerbaruKhususLine(?string \$lokasiLine = null): array
    {
        \$builder = \$this->select('transaksi_check.*, users.nama as nama_staff, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.line, TIMESTAMPDIFF(SECOND, transaksi_check.waktu_mulai, transaksi_check.waktu_selesai) as durasi_detik')
                        ->join('users', 'users.id = transaksi_check.id_user')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);

        if (\$lokasiLine) {
            \$builder->where('master_mesin.line', \$lokasiLine);
        }

        return \$builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }

    public function getPendingOverhaulLeader(?string \$lokasiLine = null): array
    {
        \$builder = \$this->select('transaksi_check.*, master_mesin.no_mesin as nama_mesin')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value)
                        ->where('transaksi_check.status', 'Pending');
        
        if (\$lokasiLine) {
            \$builder->where('master_mesin.line', \$lokasiLine);
        }
        return \$builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }

    public function getPendingOverhaulByRole(string \$role, ?string \$sessionLine = null): array
    {
        \$builder = \$this->select('transaksi_check.*, master_mesin.no_mesin as nama_mesin')
                        ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                        ->where('transaksi_check.jenis_check', \App\Enums\JenisCheck::Overhaul->value);
        
        if (\$role === \App\Enums\Role::Leader->value) {
            \$builder->where('transaksi_check.status', 'Pending');
            if (\$sessionLine) {
                \$builder->where('master_mesin.line', \$sessionLine);
            }
        } elseif (\$role === \App\Enums\Role::Sheadprd->value) {
            \$builder->where('transaksi_check.status', 'Approved L1');
        } elseif (\$role === \App\Enums\Role::Sheadmtc->value) {
            \$builder->where('transaksi_check.status', 'Approved L2');
        } else {
            \$builder->where('1=0');
        }
        
        return \$builder->orderBy('transaksi_check.waktu_mulai', 'DESC')->findAll();
    }
PHP;

$content = preg_replace('/\}\s*$/', $methods . "\n}\n", $content);
file_put_contents($file, $content);

echo "Methods injected.";
