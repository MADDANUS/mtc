<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestDeleteApproval extends BaseCommand {
    protected $group       = 'Testing';
    protected $name        = 'test:delete_approval';
    protected $description = 'Test delete approval logic';

    public function run(array $params) {
        $id_transaksi = 81; // Allfi

        $transaksiModel = new \App\Models\TransaksiCheckModel();
        $header = $transaksiModel->find($id_transaksi);
        CLI::write("Header ID: " . $header['id_transaksi']);

        $waktu = $header['waktu_selesai'] ?? $header['created_at'] ?? date('Y-m-d H:i:s');
        $tanggalCheckDate = date('Y-m-d', strtotime($waktu));
        $bulanTahun = $header['target_periode'] ?: date('Y-m', strtotime($tanggalCheckDate));
        $kategoriName = $header['kategori'] ?? null;
        
        $jadwalModel = new \App\Models\JadwalPreventiveModel();
        $jadwal = $jadwalModel->getJadwalForChecklist($header['lokasi_check'], $kategoriName, $bulanTahun);

        $riwayatService = new \App\Services\RiwayatService();
        $resolveMethod = new \ReflectionMethod($riwayatService, 'resolvePeriodeKe');
        $resolveMethod->setAccessible(true);
        [$periodeKe, $outOfPlanDate] = $resolveMethod->invoke($riwayatService, $jadwal, $tanggalCheckDate, $waktu);

        CLI::write("kategoriName = $kategoriName");
        CLI::write("bulanTahun = $bulanTahun");
        CLI::write("periodeKe = $periodeKe");
        CLI::write("outOfPlanDate = $outOfPlanDate");

        if (!$kategoriName && $jadwal) {
            $kategoriName = $jadwal['kategori'];
        }

        $ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
        $exist = $ceklisKontrolModel->findChecklistKontrol($header['id_mesin'], $kategoriName, $bulanTahun, $periodeKe);

        CLI::write("exist = " . print_r($exist, true));
    }
}
