<?php

namespace App\Controllers;

use App\Enums\Role;
use App\Enums\JenisCheck;

use App\Models\MesinModel;
use App\Models\ParameterCheckModel;
use App\Models\TransaksiCheckModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        return match (session()->get('role')) {
            Role::Admin->value    => $this->admin(),
            Role::Sheadmtc->value => $this->sheadmtc(),
            Role::Sheadprd->value => $this->sheadprd(),
            Role::Member->value   => $this->member(),
            Role::Leader->value   => $this->leader(),
            default    => $this->magang(),   // magang & fallback
        };
    }

    /**
     * Dashboard Magang — lihat ringkasan pengecekan sendiri.
     */
    private function magang()
    {
        $transaksiModel = new TransaksiCheckModel();
        $userId         = session()->get('user_id');
        $riwayat        = $transaksiModel->getRiwayat($userId);

        $hariIni = 0;
        $minggu  = 0;
        $today   = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));

        foreach ($riwayat as $r) {
            $tgl = substr($r['waktu_mulai'], 0, 10);
            if ($tgl === $today) {
                $hariIni++;
            }
            if ($tgl >= $weekAgo) {
                $minggu++;
            }
        }

        return view('dashboard/staff', [
            'title'         => 'Dashboard Magang',
            'hariIni'       => $hariIni,
            'minggu'        => $minggu,
            'riwayatTerbaru' => array_slice($riwayat, 0, 5),
        ]);
    }

    /**
     * Dashboard Member (PIC MTC) — lihat semua data, scope MTC.
     */
    private function member()
    {
        return $this->leaderStyleDashboard('Dashboard Member — PIC MTC');
    }

    /**
     * Dashboard SHead Produksi — lihat data scope produksi.
     */
    private function sheadprd()
    {
        return $this->leaderStyleDashboard('Dashboard Section Head Produksi');
    }

    /**
     * Dashboard SHead MTC — lihat data scope global MTC.
     */
    /**
     * Dashboard SHead MTC — lihat data scope global MTC.
     */
    private function sheadmtc()
    {
        return $this->leaderStyleDashboard('Dashboard Section Head MTC');
    }

    /**
     * Dashboard Khusus Leader Line — Filter berdasarkan Line masing-masing.
     */
    private function leader()
    {
        $role = session()->get('role');
        $lokasiLine = session()->get('line') ?: session()->get('lokasi'); // Menyimpan data Line (contoh: 'Line 1')

        $transaksiModel = new TransaksiCheckModel();
        $laporan        = $transaksiModel->getLaporanDurasi();
        
        // Filter Laporan (hanya Overhaul dan sesuai Line)
        $laporan = array_filter($laporan, function($l) use ($lokasiLine) {
            $isOverhaul = (strtolower($l['jenis_check']) === 'overhaul');
            // getLaporanDurasi tidak mengambil 'line' secara default di beberapa versi,
            // kita harus memastikan mesin tersebut berada di Line yang sesuai.
            // Namun untuk amannya, kita load detail mesin jika tidak ada 'line'.
            // Lebih baik kita asumsikan 'line' ada, atau join jika perlu.
            // Karena kita belum yakin getLaporanDurasi ada 'line', kita cek:
            $isSameLine = true;
            if ($lokasiLine) {
                // Asumsi: kita periksa jika ada field line atau kita dapat mengambilnya
                $isSameLine = (isset($l['line']) && $l['line'] === $lokasiLine);
                // Jika $l['line'] tidak ada, kita lewati filter ini sementara di array_filter
                // dan akan kita ubah querynya nanti jika diperlukan, tapi mari kita filter sebisa mungkin.
            }
            return $isOverhaul;
        });
        
        // Ambil riwayat terbaru khusus line ini
        $terbaru = $transaksiModel->getTerbaruKhususLine($lokasiLine);

        $totalTransaksi = count($terbaru);
        $totalDurasi    = 0;
        
        $findings = 0;
        // Hitung total durasi dan temuan
        if ($totalTransaksi > 0) {
            $transaksiIds = array_column($terbaru, 'id_transaksi');
            
            $detailModel = new \App\Models\TransaksiCheckDetailModel();
            $findings = $detailModel->whereIn('id_transaksi', $transaksiIds)
                                    ->whereIn('hasil_check', ['Δ', 'X'])
                                    ->countAllResults();
                                    
            foreach ($terbaru as $t) {
                if ($t['durasi_detik'] !== null) {
                    $totalDurasi += (int) $t['durasi_detik'];
                }
            }
        }
        $rataDetik = $totalTransaksi > 0 ? intdiv($totalDurasi, $totalTransaksi) : 0;

        // Fetch pending overhaul
        $pendingOverhaul = $transaksiModel->getPendingOverhaulLeader($lokasiLine);

        // Fetch pending kontrol for leader
        $pendingKontrol = [];
        if ($lokasiLine) {
            $mesinModel = new \App\Models\MesinModel();
            $lokasiMesin = $mesinModel->getLokasiByLine($lokasiLine);
            if ($lokasiMesin) {
                $ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
                $pendingKontrol = $ceklisKontrolModel->getPendingApprovalsForLeader($lokasiMesin['lokasi'], $lokasiLine, date('Y-m'));
            }
        }

        return view('dashboard/leader', [
            'title'          => 'Dashboard Leader Line ' . ($lokasiLine ?: ''),
            'totalTransaksi' => $totalTransaksi,
            'rataDetik'      => $rataDetik,
            'perluTindakan'  => $findings,
            'terbaru'        => array_slice($terbaru, 0, 8),
            'pendingOverhaul'=> $pendingOverhaul,
            'pendingKontrol' => $pendingKontrol,
        ]);
    }

    /**
     * Shared leader-style dashboard (untuk member, sheadprd, sheadmtc).
     */
    private function leaderStyleDashboard(string $title)
    {
        $transaksiModel = new TransaksiCheckModel();
        $laporan        = $transaksiModel->getLaporanDurasi();
        $role           = session()->get('role');

        if (in_array($role, [Role::Sheadprd->value, Role::Sheadmtc->value, Role::Leader->value], true)) {
            $laporan = array_filter($laporan, fn($l) => strtolower($l['jenis_check']) === 'overhaul');
            // If leader, maybe filter by location if needed, but for now we just show all overhaul
            $laporan = array_values($laporan);
        }

        $totalTransaksi = count($laporan);
        $totalDurasi    = 0;

        $detailModel = new \App\Models\TransaksiCheckDetailModel();
        $findings    = $detailModel->whereIn('hasil_check', ['Δ', 'X'])->countAllResults();

        foreach ($laporan as $l) {
            if ($l['durasi_detik'] !== null) {
                $totalDurasi += (int) $l['durasi_detik'];
            }
        }
        $rataDetik = $totalTransaksi > 0 ? intdiv($totalDurasi, $totalTransaksi) : 0;

        $approvalModel = new \App\Models\ApprovalBulananModel();
        $pendingKontrol = $approvalModel->getPendingKontrolByRole($role);

        // Fetch pending overhaul
        $sessionLine = session()->get('line') ?: session()->get('lokasi');
        $pendingOverhaul = $transaksiModel->getPendingOverhaulByRole($role, $sessionLine);

        return view('dashboard/leader', [
            'title'          => $title,
            'totalTransaksi' => $totalTransaksi,
            'rataDetik'      => $rataDetik,
            'perluTindakan'  => $findings,
            'terbaru'        => array_slice($laporan, 0, 8),
            'pendingKontrol' => $pendingKontrol,
            'pendingOverhaul'=> $pendingOverhaul,
        ]);
    }

    /**
     * Dashboard Admin — full overview.
     */
    private function admin()
    {
        return view('dashboard/admin', [
            'title'        => 'Dashboard Admin',
            'totalUser'    => (new UserModel())->countAllResults(),
            'totalMesin'   => (new MesinModel())->countAllResults(),
            'totalParam'   => (new ParameterCheckModel())->countAllResults(),
            'totalTrans'   => (new TransaksiCheckModel())->countAllResults(),
        ]);
    }
}
