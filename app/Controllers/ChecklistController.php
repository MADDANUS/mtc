<?php

namespace App\Controllers;

use App\Enums\Role;
use App\Enums\Departemen;
use App\Enums\JenisCheck;

use App\Models\MesinModel;
use App\Models\ParameterCheckModel;
use App\Models\TransaksiCheckModel;
use App\Models\TransaksiCheckDetailModel;
use CodeIgniter\I18n\Time;

class ChecklistController extends BaseController
{
    protected MesinModel $mesinModel;
    protected ParameterCheckModel $parameterModel;
    protected TransaksiCheckModel $transaksiModel;
    protected TransaksiCheckDetailModel $detailModel;

    public function __construct()
    {
        $this->mesinModel     = new MesinModel();
        $this->parameterModel = new ParameterCheckModel();
        $this->transaksiModel = new TransaksiCheckModel();
        $this->detailModel    = new TransaksiCheckDetailModel();
    }

    private array $categoryMap = [
        // Preventive
        'penerangan'     => 'Penerangan',
        'kabel-dan-pipa' => 'Kabel dan Pipa',
        'angin-bocor'    => 'Angin Bocor',
        'bearing-cam'    => 'Bearing Cam',
        'gearbox'        => 'Gearbox Cam',
        'belt-cam'       => 'Belt Cam',
        // Overhaul
        'mesin-cnc-bar-feeder' => 'Mesin CNC & Bar Feeder',
        'thread'               => 'THREAD',
        'double-milling'       => 'DOUBLE MILLING',
        'milling'              => 'MILLING',
        'double-center-drill'  => 'DOUBLE CENTER DRILL',
        'osl'                  => 'OSL',
        'knurling'             => 'KNURLING',
        'brother'              => 'BROTHER',
        'burnishing'           => 'BURNISHING',
        'buffing'              => 'BUFFING',
        'centering-grinding'   => 'CENTERING GRINDING',
    ];

    private function resolvePlan(string $slug): string
    {
        return match (strtolower($slug)) {
            'plant-2' => \App\Enums\Plant::PLANT2->value,
            default  => \App\Enums\Plant::PLANT1->value,
        };
    }

    private function resolveDepartemen(string $slug): string
    {
        return match (strtolower($slug)) {
            'mfg-2', 'mfg2' => Departemen::MFG2->value,
            default         => Departemen::MFG1->value,
        };
    }

    private function resolveJenis(string $slug): string
    {
        return match (strtolower($slug)) {
            'overhaul'         => JenisCheck::Overhaul->value,
            'checklist-report' => JenisCheck::Preventive->value,
            default            => JenisCheck::Preventive->value,
        };
    }

    private function resolveJenisDisplay(string $jenisName): string
    {
        if ($jenisName === JenisCheck::Preventive->value) {
            return 'Preventive Maintenance';
        }
        if ($jenisName === JenisCheck::Overhaul->value) {
            return 'Overhaul Maintenance';
        }
        return $jenisName;
    }

    /**
     * GET /checklist
     * Halaman pilih plant (Plant 1 / Plant 2).
     */
    public function pilihPlan()
    {
        return view('checklist/pilih_plan', [
            'title' => 'Pilih plant Pengecekan',
        ]);
    }

    /**
     * GET /checklist/plant/(:segment)
     * Halaman pilih departemen (MFG 1 / MFG 2).
     */
    public function pilihDepartemen(string $plantSlug)
    {
        return view('checklist/pilih_departemen', [
            'title' => 'Pilih Departemen',
            'plantSlug' => $plantSlug,
            'plantName' => $this->resolvePlan($plantSlug),
        ]);
    }

    /**
     * GET /checklist/plant/(:segment)/(:segment)
     * Halaman pilih jenis pengecekan (Preventive / Overhaul).
     */
    public function pilihJenis(string $plantSlug, string $departemenSlug)
    {
        return view('checklist/pilih_jenis', [
            'title'          => 'Pilih Jenis Pengecekan',
            'plantSlug'       => $plantSlug,
            'departemenSlug' => $departemenSlug,
            'departemenName' => $this->resolveDepartemen($departemenSlug),
            'role'           => session()->get('role'),
        ]);
    }

    /**
     * GET /checklist/plant/(:segment)/(:segment)/(:segment)
     * Halaman pilih kategori berdasarkan jenis (Preventive / Overhaul).
     */
    public function indexKategori(string $plantSlug, string $departemenSlug, string $jenisSlug)
    {
        if (strtolower($jenisSlug) === 'overhaul' && has_role('magang') && !has_any_role(['admin', 'member', 'leader mtc', 'leader', 'sheadprd', 'sheadmtc'])) {
            return $this->redirectError('/dashboard', 'Akses Ditolak: Magang tidak memiliki akses ke Overhaul Mesin.');
        }

        $plantName             = $this->resolvePlan($plantSlug);
        $departemenName       = $this->resolveDepartemen($departemenSlug);
        $jenisDbName      = $this->resolveJenis($jenisSlug);
        $jenisDisplayName = $this->resolveJenisDisplay($jenisDbName);
        $idMesin          = $this->request->getGet('id_mesin') ?: null;
        $line             = $this->request->getGet('line') ?: null;

        // Auto-assign line if idMesin is provided (e.g. from QR scan)
        if (empty($line) && $idMesin) {
            $mesinForLine = $this->mesinModel->find($idMesin);
            if ($mesinForLine && !empty($mesinForLine['line'])) {
                $line = $mesinForLine['line'];
            }
        }

        // --- INTERCEPT: Jika Line belum dipilih, tampilkan halaman Pilih Line ---
        if (empty($line)) {
            $lineModel = new \App\Models\LineModel();
            // Filter by Departemen AND plant
            $linesGrouped = $lineModel->where('plant', $plantName)->getLinesGroupedByDepartemen();
            $lines = $linesGrouped[$plantName][$departemenName] ?? [];

            return view('checklist/pilih_line', [
                'title'          => "Pilih Line - {$jenisDisplayName} {$departemenName}",
                'plantSlug'       => $plantSlug,
                'departemenSlug' => $departemenSlug,
                'departemenName' => $departemenName,
                'jenisSlug'      => $jenisSlug,
                'jenisName'      => $jenisDisplayName,
                'lines'          => $lines,
            ]);
        }

        // Auto-routing jika id_mesin ada
        if ($idMesin && strtolower($jenisSlug) === 'overhaul') {
            $mesin = $this->mesinModel->find($idMesin);
            if ($mesin) {
                if ($departemenName === Departemen::MFG1->value) {
                    // MFG 1 Overhaul selalu memakai form mesin-cnc-bar-feeder
                    return redirect()->to("/checklist/plant/{$plantSlug}/{$departemenSlug}/{$jenisSlug}/create/mesin-cnc-bar-feeder?id_mesin={$idMesin}" . ($line ? "&line=" . urlencode($line) : ""));
                } else if (!empty($mesin['jenis'])) {
                    // MFG 2 Overhaul mengikuti jenis mesinnya (milling, thread, dll)
                    $kategoriSlug = url_title(strtolower($mesin['jenis']), '-', true);
                    return redirect()->to("/checklist/plant/{$plantSlug}/{$departemenSlug}/{$jenisSlug}/create/{$kategoriSlug}?id_mesin={$idMesin}" . ($line ? "&line=" . urlencode($line) : ""));
                }
            }
        }

        // Jika Overhaul MFG 1, langsung arahkan ke form Mesin CNC & Bar Feeder
        if (strtolower($jenisSlug) === 'overhaul' && $departemenName === Departemen::MFG1->value) {
            $redirectUrl = "/checklist/plant/{$plantSlug}/{$departemenSlug}/{$jenisSlug}/create/mesin-cnc-bar-feeder";
            $queryParams = [];
            if ($idMesin) {
                $queryParams[] = "id_mesin=" . $idMesin;
            }
            if ($line) {
                $queryParams[] = "line=" . urlencode($line);
            }
            if (!empty($queryParams)) {
                $redirectUrl .= "?" . implode("&", $queryParams);
            }
            return redirect()->to($redirectUrl);
        }

        $categories = $this->resolveCategoriesList($jenisSlug, $departemenName, $plantName, $line);

        return view('checklist/index', [
            'title'      => "Pilih Kategori - {$jenisDisplayName} {$departemenName}",
            'plantSlug'       => $plantSlug,
            'departemenSlug' => $departemenSlug,
            'departemenName' => $departemenName,
            'jenisSlug'  => $jenisSlug,
            'jenisName'  => $jenisDisplayName,
            'categories' => $categories,
            'idMesin'    => $idMesin,
            'line'       => $line,
        ]);
    }

    /**
     * GET /checklist/plant/(:segment)/(:segment)/(:segment)/create/(:segment)
     * Menampilkan form pengecekan.
     */
    public function create(string $plantSlug, string $departemenSlug, string $jenisSlug, string $categorySlug)
    {
        if (strtolower($jenisSlug) === 'overhaul' && has_role('magang') && !has_any_role(['admin', 'member', 'leader mtc', 'leader', 'sheadprd', 'sheadmtc'])) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak: Magang tidak diizinkan melakukan Overhaul.');
        }

        $plantName             = $this->resolvePlan($plantSlug);
        $departemenName       = $this->resolveDepartemen($departemenSlug);
        $jenisDbName          = $this->resolveJenis($jenisSlug);
        $jenisDisplayName     = $this->resolveJenisDisplay($jenisDbName);
        
        $line             = $this->request->getGet('line') ?: null;
        
        $categories = $this->resolveCategoriesList($jenisSlug, $departemenName, $plantName, $line);
        if (!isset($categories[$categorySlug])) {
            if (isset($this->categoryMap[$categorySlug])) {
                $categoryName = $this->categoryMap[$categorySlug];
            } else {
                return redirect()->to("/checklist/plant/{$plantSlug}/{$departemenSlug}/{$jenisSlug}")->with('error', 'Kategori tidak valid.');
            }
        } else {
            $categoryName = $categories[$categorySlug];
        }

        $waktuMulai       = Time::now();
        $idMesin          = $this->request->getGet('id_mesin') ?: null;

        // NEW LOGIC: Block if Jadwal Preventive is not created for this month, location, and category
        if ($jenisDbName === JenisCheck::Preventive->value) {
            $jadwalModel = new \App\Models\JadwalPreventiveModel();
            $bulanIni = date('Y-m'); // e.g., '2026-07'
            
            $cekJadwal = $jadwalModel->where('plant', $plantName)
                                     ->where('departemen', $departemenName)
                                     ->where('kategori', $categoryName)
                                     ->where('bulan_tahun', $bulanIni)
                                     ->first();

            if (!$cekJadwal) {
                // If it came from QR scan with id_mesin, preserve the id_mesin in the redirect url if desired,
                // but redirecting back to category list is fine.
                $redirectUrl = "/checklist/plant/{$plantSlug}/{$departemenSlug}/{$jenisSlug}";
                if ($idMesin) {
                    $redirectUrl .= "?id_mesin=" . $idMesin;
                }
                
                return redirect()->to($redirectUrl)
                                 ->with('error', "Gagal! Jadwal Preventive untuk kategori {$categoryName} di bulan ini belum dibuat oleh Admin/Leader.");
            }
        }

        if (strtolower($jenisSlug) === 'overhaul') {
            $mesinQuery = $this->mesinModel->where('plant', $plantName)
                                           ->where('departemen', $departemenName);
            if ($line) {
                $mesinQuery->where('line', $line);
            }

            if ($departemenName === Departemen::MFG2->value) {
                $mesinQuery->where('jenis', $categoryName);
            } else {
                // MFG 1 - Overhaul (Mesin CNC & Bar Feeder)
                $mesinQuery->where('jenis', 'CNC');
            }
            
            $daftarMesin = $mesinQuery->orderBy('no_mesin', 'ASC')->findAll();
        } else {
            // Preventive: filter mesin berdasarkan Line yang dipilih jika ada
            $mesinQuery = $this->mesinModel->where('plant', $plantName)->where('departemen', $departemenName);
            if ($line) {
                $mesinQuery->where('line', $line);
            }
            
            // Filter khusus mesin CAM untuk 3 kategori bawah di MFG 1
            if ($departemenName === Departemen::MFG1->value) {
                $camCategories = ['bearing-cam', 'gearbox', 'belt-cam'];
                if (in_array($categorySlug, $camCategories)) {
                    $mesinQuery->where('jenis', 'CAM');
                }
            }

            $daftarMesin = $mesinQuery->orderBy('no_mesin', 'ASC')->findAll();

            // Jika Line dipilih tapi tidak ada mesin, redirect balik ke Pilih Line dengan SweetAlert
            if ($line && empty($daftarMesin)) {
                return redirect()
                    ->to("/checklist/plant/{$plantSlug}/{$departemenSlug}/{$jenisSlug}")
                    ->with('warning', "Tidak ada mesin yang terdaftar di Line <b>\"{$line}\"</b>. Silakan pilih Line lain atau hubungi Admin untuk mendaftarkan mesin.");
            }
        }

        $data = [
            'title'             => "Form Pengecekan {$jenisDisplayName} - {$categoryName}",
            'plantSlug'          => $plantSlug,
            'departemenSlug'        => $departemenSlug,
            'departemenName'        => $departemenName,
            'jenisSlug'         => $jenisSlug,
            'jenisName'         => $jenisDisplayName,
            'categorySlug'      => $categorySlug,
            'categoryName'      => $categoryName,
            'daftarMesin'       => $daftarMesin,
            'rows'              => $this->parameterModel->getFormRows($departemenName, $jenisDbName, $categoryName),
            'masterPic'         => (new \App\Models\UserModel())->whereIn('role', ['member', 'magang'])->orderBy('nama', 'ASC')->findAll(),
            'namaStaff'         => session()->get('nama'),
            'waktuMulai'        => $waktuMulai->toDateTimeString(),
            'waktuMulaiDisplay' => $waktuMulai->format("d/m/Y H:i:s"),
            'idMesin'           => $idMesin,
            'line'              => $line,
        ];

        return view('checklist/form', $data);
    }

    /**
     * API untuk mengecek apakah sudah ada transaksi untuk mesin ini di bulan yang sama.
     */
    public function checkDuplicate()
    {
        $idMesin = $this->request->getPost('id_mesin');
        $jenisCheckSlug = $this->request->getPost('jenis_check');
        $kategoriSlug = $this->request->getPost('kategori');

        // Translate slug back to real DB name
        $jenisCheck = $this->resolveJenis($jenisCheckSlug);
        
        // Cek nama kategori
        $kategori = '';
        if ($jenisCheckSlug === 'overhaul') {
            if (isset($this->categoryMap[$kategoriSlug])) {
                $kategori = $this->categoryMap[$kategoriSlug];
            } else {
                $kategori = strtoupper(str_replace('-', ' ', $kategoriSlug));
            }
        } else {
            if (isset($this->categoryMap[$kategoriSlug])) {
                $kategori = $this->categoryMap[$kategoriSlug];
            }
        }

        // Get Departemen for Jadwal
        $mesinModel = new \App\Models\MesinModel();
        $mesin = $mesinModel->find($idMesin);
        $departemenName = $mesin['departemen'] ?? '';

        $transaksiModel = new \App\Models\TransaksiCheckModel();
        
        // --- BYPASS GATEKEEPER UNTUK OVERHAUL ---
        // Overhaul tidak memiliki jadwal di jadwal_preventive
        if ($jenisCheckSlug === 'overhaul') {
            $currentMonth = date('Y-m');
            $checked = $transaksiModel->checkDuplicate((int)$idMesin, $jenisCheck, $currentMonth, $kategori, true);
            
            if (count($checked) > 0) {
                return $this->response->setJSON([
                    'duplicate' => true,
                    'status' => 'blocked',
                    'tanggal' => $checked[0]['waktu_mulai'],
                    'pic' => $checked[0]['nama_pic']
                ]);
            }
            
            return $this->response->setJSON([
                'status' => 'normal',
                'target_periode' => $currentMonth,
                'message' => 'Lanjutkan pengisian form Overhaul.'
            ]);
        }
        
        $jadwalModel = new \App\Models\JadwalPreventiveModel();

        // 1. Cek Tunggakan (Bulan Lalu)
        $lastMonth = date('Y-m', strtotime('-1 month'));
        $scheduleLastMonth = $jadwalModel->getJadwalForChecklist($departemenName, $kategori, $lastMonth);
        
        if ($scheduleLastMonth) {
            $checkedLastMonth = $transaksiModel->checkDuplicate((int)$idMesin, $jenisCheck, $lastMonth, $kategori);
            if (count($checkedLastMonth) === 0) {
                // Ada jadwal bulan lalu tapi belum dikerjakan -> OVERDUE
                return $this->response->setJSON([
                    'status' => 'overdue',
                    'target_periode' => $lastMonth,
                    'message' => 'Mesin ini memiliki tunggakan pengecekan untuk periode ' . format_bulan_indo($lastMonth) . '. Anda harus menyelesaikannya terlebih dahulu.'
                ]);
            }
        }

        // 2. Cek Bulan Berjalan
        $currentMonth = date('Y-m');
        $scheduleCurrentMonth = $jadwalModel->getJadwalForChecklist($departemenName, $kategori, $currentMonth);

        if ($scheduleCurrentMonth) {
            $checkedCurrentMonth = $transaksiModel->checkDuplicate((int)$idMesin, $jenisCheck, $currentMonth, $kategori);
            if (count($checkedCurrentMonth) === 0) {
                // Jadwal bulan ini belum dikerjakan -> NORMAL
                return $this->response->setJSON([
                    'status' => 'normal',
                    'target_periode' => $currentMonth,
                    'message' => ''
                ]);
            }
        }

        // 3. Cek Curi Start (Bulan Depan)
        $nextMonth = date('Y-m', strtotime('+1 month'));
        $scheduleNextMonth = $jadwalModel->getJadwalForChecklist($departemenName, $kategori, $nextMonth);

        if ($scheduleNextMonth) {
            $checkedNextMonth = $transaksiModel->checkDuplicate((int)$idMesin, $jenisCheck, $nextMonth, $kategori);
            if (count($checkedNextMonth) === 0) {
                // Jadwal bulan depan sudah ada, dan belum dikerjakan -> ADVANCE (Curi Start)
                return $this->response->setJSON([
                    'status' => 'advance',
                    'target_periode' => $nextMonth,
                    'message' => 'Jatah bulan ini sudah tuntas. Apakah Anda ingin melakukan pengecekan Out of plant untuk periode ' . format_bulan_indo($nextMonth) . '?'
                ]);
            }
        }

        // Jika sampai sini, berarti semua jadwal sudah selesai atau tidak ada jadwal
        // Kita berikan fallback (duplicate pada bulan ini untuk memblokir)
        return $this->response->setJSON([
            'status' => 'blocked',
            'target_periode' => $currentMonth,
            'message' => 'Pengecekan kategori ini sudah diselesaikan sesuai jadwal yang ada.'
        ]);
    }

    /**
     * POST /checklist/plant/(:segment)/(:segment)/(:segment)/store
     */
    public function store(string $plantSlug, string $departemenSlug, string $jenisSlug)
    {
        if (! $this->validateStoreRules($jenisSlug)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $plantName        = $this->resolvePlan($plantSlug);
        $departemenName   = $this->resolveDepartemen($departemenSlug);
        $jenisName    = $this->resolveJenis($jenisSlug);
        $idMesin      = (int) $this->request->getPost('id_mesin');
        $waktuMulai   = $this->request->getPost('waktu_mulai');
        $kategoriName = $this->request->getPost('kategori');
        $targetPeriode= $this->request->getPost('target_periode') ?: date('Y-m');
        $waktuSelesai = Time::now()->toDateTimeString();

        $hasilCheck = $this->request->getPost('hasil_check') ?? [];
        $ulasan     = $this->request->getPost('ulasan') ?? [];
        $inputPic   = session()->get('nama') ?: 'Staff';

        $categorySlug = array_search($kategoriName, $this->categoryMap, true) ?: 'penerangan';

        $db = \Config\Database::connect();
        $db->transStart();

        $mesinInfo = $this->mesinModel->find($idMesin);
        // Prioritaskan Line dari request (dipilih user di halaman Pilih Line)
        $lineCheck = $this->request->getPost('line_check') ?: ($mesinInfo['line'] ?? null);

        $idTransaksi = $this->transaksiModel->insert([
            'id_user'       => session()->get('user_id'),
            'nama_pic'      => $inputPic,
            'plant'          => $plantName,
            'id_mesin'      => $idMesin,
            'departemen_check'  => $departemenName,
            'line_check'    => $lineCheck,
            'jenis_check'   => $jenisName,
            'kategori'      => $kategoriName,
            'target_periode'=> $targetPeriode,
            'waktu_mulai'   => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'status'        => 'Pending',
            'ss_type_mesin'   => $mesinInfo['type_mesin'] ?? null,
            'ss_serial_nomor' => $mesinInfo['serial_nomor'] ?? null,
            'ss_bar_feeder'   => $mesinInfo['bar_feeder_type'] ?? null,
            'ss_no_mesin'     => $mesinInfo['no_mesin'] ?? null,
        ]);
        if (!$idTransaksi) {
            log_message('error', 'Failed to insert transaksi_check: ' . json_encode($this->transaksiModel->errors()));
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal membuat header transaksi pengecekan.');
        }

        $this->processChecklistDetails($idTransaksi, $hasilCheck, $ulasan);

        // (Logika ceklis_kontrol diproses di Approval)

        if (strtolower($jenisSlug) === 'overhaul') {
            $this->processOverhaulMetadata($idTransaksi);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data pengecekan.');
        }

        $roleSession = session()->get('role');
        if ($roleSession === Role::Magang->value) {
            $redirectUrl = "/riwayat/departemen/{$departemenSlug}?jenis_check=" . urlencode($jenisName) . "&kategori=" . urlencode($kategoriName) . "&plant=" . urlencode($plantName);
        } else {
            $redirectUrl = "/approval";
        }

        return redirect()->to($redirectUrl)
                          ->with('success', 'Pengecekan berhasil disimpan. Durasi pengerjaan: '
                              . $this->formatDurasi($waktuMulai, $waktuSelesai));
    }

    /**
     * GET /checklist/plant/(:segment)/(:segment)/overhaul
     * Halaman placeholder Overhaul.
     */
    public function overhaulPlaceholder(string $plantSlug, string $departemenSlug)
    {
        return redirect()->to("/checklist/plant/{$plantSlug}/{$departemenSlug}/overhaul");
    }

    private function formatDurasi(string $mulai, string $selesai): string
    {
        $detik = strtotime($selesai) - strtotime($mulai);
        $menit = intdiv($detik, 60);
        $sisaDetik = $detik % 60;

        return "{$menit} menit {$sisaDetik} detik";
    }

    private function validateStoreRules(string $jenisSlug): bool
    {
        $rules = [
            'id_mesin'    => 'required|numeric',
            'waktu_mulai' => 'required',
            'kategori'    => 'required',
        ];

        if (strtolower($jenisSlug) === 'overhaul') {
            $rules['bar_feeder_type'] = 'permit_empty|string';
            $rules['support_pic.*']   = 'permit_empty|string';
        }

        return $this->validate($rules);
    }

    private function processChecklistDetails(int $idTransaksi, array $hasilCheck, array $ulasan): void
    {
        $uploadPath = FCPATH . 'uploads/abnormal/';
        $batchData = [];

        foreach ($hasilCheck as $idParameter => $hasil) {
            $fotoAbnormal = null;
            $fotoAbnormal2 = null;

            if ($hasil === 'Δ') {
                $file = $this->request->getFile("foto_abnormal.{$idParameter}");
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $newName = time() . '_1_' . uniqid() . '.' . $file->getClientExtension();
                    $file->move($uploadPath, $newName);
                    $fotoAbnormal = $newName;
                }
                
                $file2 = $this->request->getFile("foto_abnormal_2.{$idParameter}");
                if ($file2 && $file2->isValid() && !$file2->hasMoved()) {
                    $newName2 = time() . '_2_' . uniqid() . '.' . $file2->getClientExtension();
                    $file2->move($uploadPath, $newName2);
                    $fotoAbnormal2 = $newName2;
                }
            }

            $batchData[] = [
                'id_transaksi'    => $idTransaksi,
                'id_parameter'    => (int) $idParameter,
                'hasil_check'     => $hasil !== '' ? $hasil : null,
                'ulasan'          => $ulasan[$idParameter] ?? null,
                'foto_abnormal'   => $fotoAbnormal,
                'foto_abnormal_2' => $fotoAbnormal2,
            ];
        }

        if (!empty($batchData)) {
            $this->detailModel->insertBatch($batchData);
        }
    }

    private function processOverhaulMetadata(int $idTransaksi): void
    {
        $rawSupport = $this->request->getPost('support_pic');
        $supportStr = null;
        if (is_array($rawSupport)) {
            $filtered = array_filter(array_map('trim', $rawSupport));
            if (!empty($filtered)) {
                $supportStr = implode(', ', $filtered);
            }
        }
        
        $overhaulModel = new \App\Models\TransaksiOverhaulModel();
        $overhaulModel->insert([
            'id_transaksi'        => $idTransaksi,
            'bar_feeder_type'     => $this->request->getPost('bar_feeder_type') ?: null,
            'support_pic'         => $supportStr,
            'note_recommendation' => $this->request->getPost('note_recommendation') ?: null,
        ]);
    }

    private function resolveCategoriesList(string $jenisSlug, string $departemenName, string $plantName = 'Plant 1', ?string $line = null): array
    {
        if (strtolower($jenisSlug) === 'overhaul' && $departemenName === Departemen::MFG2->value) {
            $db = \Config\Database::connect();
            
            $sql = "SELECT DISTINCT jenis FROM master_mesin WHERE plant = ? AND departemen = ? AND jenis IS NOT NULL AND jenis NOT IN ('-', 'CAM')";
            $params = [$plantName, $departemenName];
            
            if (!empty($line)) {
                $sql .= " AND line = ?";
                $params[] = $line;
            }
            
            $query = $db->query($sql, $params);
            $results = $query->getResultArray();
            
            $categories = [];
            foreach ($results as $row) {
                $jenis = trim($row['jenis']);
                if (!empty($jenis)) {
                    $slug = url_title(strtolower($jenis), '-', true);
                    $categories[$slug] = strtoupper($jenis);
                }
            }
            return $categories;
        }

        if ($departemenName === Departemen::MFG2->value) {
            return [
                'penerangan'     => 'Penerangan',
                'kabel-dan-pipa' => 'Kabel dan Pipa',
                'angin-bocor'    => 'Angin Bocor',
            ];
        }

        $baseCategories = [
            'penerangan'     => 'Penerangan',
            'kabel-dan-pipa' => 'Kabel dan Pipa',
            'angin-bocor'    => 'Angin Bocor',
        ];

        if ($departemenName === Departemen::MFG1->value) {
            $db = \Config\Database::connect();
            $sql = "SELECT 1 FROM master_mesin WHERE plant = ? AND departemen = ? AND jenis = 'CAM'";
            $params = [$plantName, $departemenName];
            if (!empty($line)) {
                $sql .= " AND line = ?";
                $params[] = $line;
            }
            $sql .= " LIMIT 1";
            
            $hasCam = $db->query($sql, $params)->getRow();
            
            if ($hasCam) {
                $baseCategories['bearing-cam'] = 'Bearing Cam';
                $baseCategories['gearbox'] = 'Gearbox Cam';
                $baseCategories['belt-cam'] = 'Belt Cam';
            }
        }

        return $baseCategories;
    }
}
