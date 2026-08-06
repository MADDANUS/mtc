<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\MesinModel;
use App\Models\RiwayatMesinModel;

class RiwayatMesinSeeder extends Seeder
{
    public function run()
    {
        $mesinModel = new MesinModel();
        $riwayatModel = new RiwayatMesinModel();

        // Ambil semua mesin yang ada saat ini
        $semuaMesin = $mesinModel->findAll();

        foreach ($semuaMesin as $mesin) {
            // Kita anggap tanggal mulai adalah tanggal aktif, atau 2020-01-01 jika null
            $tanggalMulai = $mesin['tanggal_aktif'] ?? '2020-01-01';

            // Insert ke tabel riwayat_mesin
            $riwayatModel->insert([
                'id_mesin'        => $mesin['id_mesin'],
                'lokasi'          => $mesin['lokasi'],
                'line'            => $mesin['line'],
                'tanggal_mulai'   => $tanggalMulai,
                'tanggal_selesai' => null, // Masih berlaku sampai sekarang
            ]);
        }

        echo "Berhasil menyuntikkan data " . count($semuaMesin) . " mesin ke tabel riwayat_mesin.\n";
    }
}
