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
        if (has_role(Role::Admin->value)) {
            return $this->admin();
        }
        if (has_any_role([Role::Sheadmtc->value, Role::Sheadprd->value, Role::Leader->value, Role::Member->value, Role::LeaderMember->value])) {
            return $this->leaderStyleDashboard('Dashboard Master');
        }
        
        return $this->magang();
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

        $bulan = $this->request->getGet('bulan') ?: date('Y-m');
        $departemenUser = session()->get('departemen');

        return view('dashboard/staff', [
            'title'         => 'Dashboard Magang',
            'hariIni'       => $hariIni,
            'minggu'        => $minggu,
            'riwayatTerbaru' => array_slice($riwayat, 0, 5),
            'percentageSummary' => $transaksiModel->getPercentageSummary(['bulan' => $bulan, 'departemen' => $departemenUser]),
        ]);
    }

    /**
     * Shared leader-style dashboard (untuk member, sheadprd, sheadmtc).
     */
    private function leaderStyleDashboard(string $title)
    {
        $transaksiModel = new TransaksiCheckModel();
        
        $sessionLokasi = session()->get('departemen');
        $sessionPlant = session()->get('plant');
        $sessionLine = session()->get('line');

        $laporan = $transaksiModel->getLaporanDurasi([
            'departemen' => $sessionLokasi,
            'plant'      => $sessionPlant,
            'line'       => $sessionLine
        ]);
        
        $totalTransaksi = count($laporan);
        $totalDurasi    = 0;

        $abnormalModel = new \App\Models\LaporanAbnormalModel();
        $findingsQuery = $abnormalModel->select('laporan_abnormal.id_abnormal')
                                       ->groupStart()
                                           ->where('laporan_abnormal.action IS NULL', null, false)
                                           ->orWhere('laporan_abnormal.action', '')
                                       ->groupEnd();
                                       
        if (has_any_role([Role::Sheadprd->value, Role::Sheadmtc->value, Role::Leader->value])) {
            $findingsQuery->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin', 'left');
            if (($userDepts = session()->get('departemen')) && $userDepts !== '-') {
                $findingsQuery->whereIn('master_mesin.departemen', array_map('trim', explode(',', $userDepts)));
            }
            if (($userPlan = session()->get('plant')) && $userPlan !== '-') {
                $findingsQuery->whereIn('master_mesin.plant', array_map('trim', explode(',', $userPlan)));
            }
            if (($userLine = session()->get('line')) && $userLine !== '-') {
                $findingsQuery->whereIn('master_mesin.line', array_map('trim', explode(',', $userLine)));
            }
        }
        $findings = $findingsQuery->countAllResults();

        foreach ($laporan as $l) {
            if ($l['durasi_detik'] !== null) {
                $totalDurasi += (int) $l['durasi_detik'];
            }
        }
        $rataDetik = $totalTransaksi > 0 ? intdiv($totalDurasi, $totalTransaksi) : 0;

        $approvalModel = new \App\Models\ApprovalBulananModel();
        $pendingKontrol = $approvalModel->getPendingKontrolByRole();

        $sessionLokasi = session()->get('departemen');
        $sessionPlant = session()->get('plant');
        $sessionLine = session()->get('line');

        // Fetch pending overhaul
        $pendingOverhaul = $transaksiModel->getPendingOverhaulByRole($sessionLine, $sessionLokasi, $sessionPlant);

        $bulan = $this->request->getGet('bulan') ?: date('Y-m');

        return view('dashboard/leader', [
            'title'          => $title,
            'totalTransaksi' => $totalTransaksi,
            'rataDetik'      => $rataDetik,
            'perluTindakan'  => $findings,
            'terbaru'        => array_slice($laporan, 0, 8),
            'pendingKontrol' => $pendingKontrol,
            'pendingOverhaul'=> $pendingOverhaul,
            'percentageSummary' => $transaksiModel->getPercentageSummary([
                'bulan'      => $bulan, 
                'departemen' => $sessionLokasi,
                'plant'      => $sessionPlant,
                'line'       => $sessionLine
            ]),
        ]);
    }

    /**
     * Dashboard Admin — full overview.
     */
    private function admin()
    {
        $transaksiModel = new TransaksiCheckModel();
        $bulan = $this->request->getGet('bulan') ?: date('Y-m');
        return view('dashboard/admin', [
            'title'        => 'Dashboard Admin',
            'totalUser'    => (new UserModel())->countAllResults(),
            'totalMesin'   => (new MesinModel())->countAllResults(),
            'totalParam'   => (new ParameterCheckModel())->countAllResults(),
            'totalTrans'   => $transaksiModel->countAllResults(),
            'percentageSummary' => $transaksiModel->getPercentageSummary(['bulan' => $bulan]),
        ]);
    }

    /**
     * API to get details of checked and unchecked machines
     */
    public function detailPencapaian()
    {
        $jenis         = $this->request->getGet('jenis'); // 'preventive', 'overhaul_plant1', 'overhaul_plant2'
        $bulanSekarang = $this->request->getGet('bulan') ?: date('Y-m');
        $departemen    = $this->request->getGet('departemen');
        $plant         = $this->request->getGet('plant');
        $line          = $this->request->getGet('line');

        $db = \Config\Database::connect();

        $isOverhaul = str_starts_with($jenis, 'overhaul');
        $plantFilter = null;

        if ($jenis === 'overhaul_plant1') {
            $plantFilter = 'Plant 1';
        } elseif ($jenis === 'overhaul_plant2') {
            $plantFilter = 'Plant 2';
        }

        $hasActiveCycle = false;
        if ($isOverhaul && $plantFilter) {
            $periodeModel = new \App\Models\PeriodeOverhaulModel();
            $siklus = $periodeModel->getAktif($plantFilter);
            if ($siklus) {
                $hasActiveCycle = true;
                $periodeStart = $siklus['tanggal_mulai'];
                $periodeEnd   = date('Y-m-d');
                $labelPeriode = 'Mulai ' . date('d M Y', strtotime($periodeStart)) . ' s/d Sekarang';
            } else {
                $periodeStart = date('Y-m-d');
                $periodeEnd   = date('Y-m-d');
                $labelPeriode = 'Belum ada siklus berjalan';
            }
        } else {
            // Preventive — bulan tertentu
            $periodeStart = $bulanSekarang;
            $periodeEnd   = $bulanSekarang;
            $labelPeriode = date('m/Y', strtotime($bulanSekarang . '-01'));
        }

        $jenisStr = $isOverhaul ? 'Overhaul' : 'Preventive';

        // 1. Get All Target Machines
        $mesinBuilder = $db->table('master_mesin m')
                           ->select('m.id_mesin, m.no_mesin, m.type_mesin, m.departemen, m.line, m.plant');

        if (!empty($departemen) && $departemen !== '-') {
            $deptsArray = array_map('trim', explode(',', $departemen));
            $mesinBuilder->whereIn('m.departemen', $deptsArray);
        }
        if (!empty($line) && $line !== '-') {
            $linesArray = array_map('trim', explode(',', $line));
            $mesinBuilder->whereIn('m.line', $linesArray);
        }
        if (!$isOverhaul && !empty($plant) && $plant !== '-') {
            $planArray = array_map('trim', explode(',', $plant));
            $mesinBuilder->whereIn('m.plant', $planArray);
        }
        if ($isOverhaul) {
            $mesinBuilder->whereNotIn('m.jenis', ['-', 'CAM']);
            if ($plantFilter) {
                $mesinBuilder->where('m.plant', $plantFilter);
            }
        }
        $targetMesin = $mesinBuilder->orderBy('m.departemen', 'ASC')->orderBy('m.line', 'ASC')->orderBy('m.no_mesin', 'ASC')->get()->getResultArray();

        // 2. Get Checked Machines (hanya jika ada siklus aktif, jika tidak, kosongkan data pengecekan)
        $checkedMap = [];
        if (!$isOverhaul || ($isOverhaul && $hasActiveCycle)) {
            $builder = $db->table('transaksi_check t')
                          ->select('t.id_mesin, t.kategori, m.jenis,
                                    (SELECT CASE 
                                        WHEN SUM(CASE WHEN d.hasil_check = \'Δ\' THEN 1 ELSE 0 END) > 0 THEN \'Δ\' 
                                        WHEN COUNT(d.id_detail) > 0 AND SUM(CASE WHEN d.hasil_check = \'X\' THEN 1 ELSE 0 END) = COUNT(d.id_detail) THEN \'X\' 
                                        ELSE \'V\' 
                                     END 
                                     FROM transaksi_check_detail d 
                                     WHERE d.id_transaksi = t.id_transaksi) as kondisi')
                          ->join('master_mesin m', 'm.id_mesin = t.id_mesin', 'left')
                          ->where('t.jenis_check', $jenisStr)
                          ->where('t.status', 'Approved');

            if (!empty($departemen) && $departemen !== '-') {
                $deptsArray = array_map('trim', explode(',', $departemen));
                $builder->whereIn('m.departemen', $deptsArray);
            }
            if (!empty($line) && $line !== '-') {
                $linesArray = array_map('trim', explode(',', $line));
                $builder->whereIn('m.line', $linesArray);
            }
            if (!$isOverhaul && !empty($plant) && $plant !== '-') {
                $planArray = array_map('trim', explode(',', $plant));
                $builder->whereIn('m.plant', $planArray);
            }
            if ($isOverhaul && $plantFilter) {
                $builder->where('m.plant', $plantFilter);
            }

            if ($isOverhaul) {
                $builder->where("DATE(t.waktu_mulai) >= '$periodeStart'")
                        ->where("DATE(t.waktu_mulai) <= '$periodeEnd'");
            } else {
                $builder->groupStart()
                            ->where('t.target_periode', $periodeStart)
                            ->orGroupStart()
                                ->where('(t.target_periode IS NULL OR t.target_periode = "")', null, false)
                                ->like('t.waktu_mulai', $periodeStart, 'after')
                            ->groupEnd()
                        ->groupEnd();
            }

            $checkedRecords = $builder->orderBy('t.id_transaksi', 'ASC')->get()->getResultArray();
            
            foreach ($checkedRecords as $row) {
                if (!isset($checkedMap[$row['id_mesin']])) {
                    $checkedMap[$row['id_mesin']] = ['categories' => [], 'kondisi' => 'V'];
                }
                $checkedMap[$row['id_mesin']]['categories'][] = $row['kategori'];
                
                if ($row['kondisi'] === 'Δ') {
                    $checkedMap[$row['id_mesin']]['kondisi'] = 'Δ';
                } elseif ($row['kondisi'] === 'X' && $checkedMap[$row['id_mesin']]['kondisi'] !== 'Δ') {
                    $checkedMap[$row['id_mesin']]['kondisi'] = 'X';
                }
            }
        }

        $sudahDicek = [];
        $belumDicek = [];

        foreach ($targetMesin as $m) {
            if (array_key_exists($m['id_mesin'], $checkedMap)) {
                $uniqueCategories = array_unique($checkedMap[$m['id_mesin']]['categories']);
                $checkedCount = count($uniqueCategories);

                if ($isOverhaul || $checkedCount > 0) {
                    $m['kondisi'] = $checkedMap[$m['id_mesin']]['kondisi'];
                    $sudahDicek[] = $m;
                } else {
                    $belumDicek[] = $m;
                }
            } else {
                $belumDicek[] = $m;
            }
        }

        $canManage = has_any_role(['admin', 'leader mtc', 'member']);

        return $this->response->setJSON([
            'sudah_dicek'      => $sudahDicek,
            'belum_dicek'      => $belumDicek,
            'jenis'            => $jenisStr . ($plantFilter ? " ($plantFilter)" : ''),
            'periode'          => $labelPeriode,
            'is_overhaul'      => $isOverhaul,
            'plant'            => $plantFilter,
            'has_active_cycle' => $hasActiveCycle,
            'can_manage'       => $canManage,
        ]);
    }

    /**
     * POST: Akhiri periode overhaul aktif untuk plant tertentu
     */
    public function akhiriPeriodeOverhaul()
    {
        if (!has_any_role(['admin', 'leader mtc', 'member'])) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Tidak punya akses.']);
        }

        $plant  = $this->request->getPost('plant');
        $userId = session()->get('user_id');

        if (!in_array($plant, ['Plant 1', 'Plant 2'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Plant tidak valid.']);
        }

        $periodeModel = new \App\Models\PeriodeOverhaulModel();
        $result = $periodeModel->akhiriPeriode($plant, (int) $userId);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => "Siklus Overhaul $plant telah diakhiri dan ditutup. Status saat ini menjadi Kosong/Selesai."]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada siklus aktif yang ditemukan.']);
    }

    /**
     * POST: Awali periode overhaul baru untuk plant tertentu
     */
    public function awaliPeriodeOverhaul()
    {
        if (!has_any_role(['admin', 'leader mtc', 'member'])) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Tidak punya akses.']);
        }

        $plant  = $this->request->getPost('plant');

        if (!in_array($plant, ['Plant 1', 'Plant 2'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Plant tidak valid.']);
        }

        $periodeModel = new \App\Models\PeriodeOverhaulModel();
        $result = $periodeModel->awaliPeriode($plant);

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => "Siklus Overhaul $plant yang BARU telah dimulai hari ini."]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Gagal memulai. Mungkin masih ada siklus yang aktif.']);
    }
    public function detailKategoriMesin()
    {
        $idMesin = $this->request->getGet('id_mesin');
        $bulan   = $this->request->getGet('bulan');

        $db = \Config\Database::connect();
        
        $mesin = $db->table('master_mesin')->select('no_mesin, jenis')->where('id_mesin', $idMesin)->get()->getRowArray();

        // Target kategori wajib: CAM = 6 kategori, Lainnya = 3 kategori
        if ($mesin && strcasecmp($mesin['jenis'], 'CAM') === 0) {
            $kategoriList = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox Cam', 'Belt Cam'];
        } else {
            $kategoriList = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'];
        }

        $checkedKategori = $db->table('transaksi_check t')
                              ->select('t.kategori, t.status as approval_status, 
                                        (SELECT CASE 
                                            WHEN SUM(CASE WHEN d.hasil_check = \'Δ\' THEN 1 ELSE 0 END) > 0 THEN \'Δ\' 
                                            WHEN COUNT(d.id_detail) > 0 AND SUM(CASE WHEN d.hasil_check = \'X\' THEN 1 ELSE 0 END) = COUNT(d.id_detail) THEN \'X\' 
                                            ELSE \'V\' 
                                         END 
                                         FROM transaksi_check_detail d 
                                         WHERE d.id_transaksi = t.id_transaksi) as kondisi')
                              ->where('t.id_mesin', $idMesin)
                              ->where('t.jenis_check', \App\Enums\JenisCheck::Preventive->value)
                              ->groupStart()
                                  ->where('t.target_periode', $bulan)
                                  ->orGroupStart()
                                      ->where('(t.target_periode IS NULL OR t.target_periode = "")', null, false)
                                      ->like('t.waktu_mulai', $bulan, 'after')
                                  ->groupEnd()
                              ->groupEnd()
                              ->get()->getResultArray();

        $checkedMap = [];
        foreach ($checkedKategori as $c) {
            $checkedMap[$c['kategori']] = [
                'kondisi' => $c['kondisi'],
                'approval_status' => $c['approval_status']
            ];
        }

        $result = [];
        foreach ($kategoriList as $kat) {
            if (isset($checkedMap[$kat])) {
                $statusStr = $checkedMap[$kat]['approval_status'] === 'Approved' ? 'Sudah Selesai' : 'Proses ('.$checkedMap[$kat]['approval_status'].')';
                $result[] = [
                    'kategori' => $kat,
                    'status' => $statusStr,
                    'is_done' => $checkedMap[$kat]['approval_status'] === 'Approved',
                    'kondisi' => $checkedMap[$kat]['kondisi']
                ];
            } else {
                $result[] = [
                    'kategori' => $kat,
                    'status' => 'Belum Dicek',
                    'is_done' => false,
                    'kondisi' => '-'
                ];
            }
        }

        $mesin = $db->table('master_mesin')->select('no_mesin')->where('id_mesin', $idMesin)->get()->getRowArray();

        return $this->response->setJSON([
            'status' => 'success',
            'mesin'  => $mesin ? $mesin['no_mesin'] : 'Unknown',
            'data'   => $result
        ]);
    }
}
