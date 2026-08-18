<?php
namespace App\Controllers;
use App\Controllers\BaseController;

class TestDeleteApproval extends BaseController {
    public function index() {
        $id_transaksi = 88; // from db list (Preventive, id_mesin 197)

        $transaksiModel = new \App\Models\TransaksiCheckModel();
        $header = $transaksiModel->find($id_transaksi);
        echo "Header ID: " . $header['id_transaksi'] . "\n";

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

        echo "kategoriName = $kategoriName\n";
        echo "bulanTahun = $bulanTahun\n";
        echo "periodeKe = $periodeKe\n";
        echo "outOfPlanDate = $outOfPlanDate\n";

        if (!$kategoriName && $jadwal) {
            $kategoriName = $jadwal['kategori'];
        }

        $ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
        $exist = $ceklisKontrolModel->findChecklistKontrol($header['id_mesin'], $kategoriName, $bulanTahun, $periodeKe);

        echo "exist = " . print_r($exist, true) . "\n";
    }
}
