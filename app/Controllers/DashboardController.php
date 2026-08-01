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
        
        // Karena kita butuh filter line dengan tepat, lebih baik kita gunakan database builder langsung.
        $db = \Config\Database::connect();
        
        // Ambil riwayat terbaru khusus line ini
        $terbaruQuery = $db->table('transaksi_check')
                           ->select('transaksi_check.*, users.nama as nama_staff, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.line, TIMESTAMPDIFF(SECOND, transaksi_check.waktu_mulai, transaksi_check.waktu_selesai) as durasi_detik')
                           ->join('users', 'users.id = transaksi_check.id_user')
                           ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                           ->where('transaksi_check.jenis_check', JenisCheck::Overhaul->value);
                           
        if ($lokasiLine) {
            $terbaruQuery->where('master_mesin.line', $lokasiLine);
        }
        $terbaru = $terbaruQuery->orderBy('transaksi_check.waktu_mulai', 'DESC')->get()->getResultArray();

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
        $pendingOverhaulQuery = $db->table('transaksi_check')
                                   ->select('transaksi_check.*, master_mesin.no_mesin as nama_mesin')
                                   ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                                   ->where('transaksi_check.jenis_check', JenisCheck::Overhaul->value)
                                   ->where('transaksi_check.status', 'Pending');
        
        if ($lokasiLine) {
            $pendingOverhaulQuery->where('master_mesin.line', $lokasiLine);
        }
        $pendingOverhaul = $pendingOverhaulQuery->orderBy('transaksi_check.waktu_mulai', 'DESC')->get()->getResultArray();

        // Fetch pending kontrol for leader
        $pendingKontrol = [];
        if ($lokasiLine) {
            $lokasiMesin = $db->table('master_mesin')->select('lokasi')->where('line', $lokasiLine)->limit(1)->get()->getRowArray();
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

        $db = \Config\Database::connect();
        $pendingKontrolQuery = $db->table('approval_bulanan')->where('type', 'kontrol');
        
        if ($role === Role::Sheadprd->value) {
            $pendingKontrolQuery->where('status', 'Approved L1');
        } elseif ($role === Role::Sheadmtc->value) {
            $pendingKontrolQuery->where('status', 'Approved L2');
        } elseif ($role === Role::Member->value) {
            $pendingKontrolQuery->where('status', 'Pending');
        } else {
            $pendingKontrolQuery->where('1=0');
        }
        $pendingKontrol = $pendingKontrolQuery->orderBy('updated_at', 'DESC')->get()->getResultArray();

        // Fetch pending overhaul
        $pendingOverhaulQuery = $db->table('transaksi_check')
                                   ->select('transaksi_check.*, master_mesin.no_mesin as nama_mesin')
                                   ->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')
                                   ->where('transaksi_check.jenis_check', JenisCheck::Overhaul->value);
        
        if ($role === Role::Leader->value) {
            $pendingOverhaulQuery->where('transaksi_check.status', 'Pending');
            // For leaders, get 'line' from session
            $sessionLine = session()->get('line') ?: session()->get('lokasi');
            if ($sessionLine) {
                $pendingOverhaulQuery->where('master_mesin.line', $sessionLine);
            }
        } elseif ($role === Role::Sheadprd->value) {
            $pendingOverhaulQuery->where('transaksi_check.status', 'Approved L1');
        } elseif ($role === Role::Sheadmtc->value) {
            $pendingOverhaulQuery->where('transaksi_check.status', 'Approved L2');
        } else {
            $pendingOverhaulQuery->where('1=0');
        }
        $pendingOverhaul = $pendingOverhaulQuery->orderBy('transaksi_check.waktu_mulai', 'DESC')->get()->getResultArray();

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
