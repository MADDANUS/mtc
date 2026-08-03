<?php

namespace App\Controllers;

use App\Enums\Role;
use App\Enums\Lokasi;
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
        'gearbox'        => 'Gearbox',
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

    private function resolveLokasi(string $slug): string
    {
        return match (strtolower($slug)) {
            'mfg2'  => Lokasi::MFG2->value,
            default => Lokasi::MFG1->value,
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
        return $jenisName === JenisCheck::Preventive->value ? 'Checklist Report' : $jenisName;
    }

    /**
     * GET /checklist
     * Halaman pilih lokasi (MFG 1 / MFG 2).
     */
    public function pilihLokasi()
    {
        return view('checklist/pilih_lokasi', [
            'title' => 'Pilih Jenis Pengecekan',
        ]);
    }

    /**
     * GET /checklist/(:segment)
     * Halaman pilih jenis pengecekan (Preventive / Overhaul).
     */
    public function pilihJenis(string $lokasiSlug)
    {
        return view('checklist/pilih_jenis', [
            'title'      => 'Pilih Jenis Pengecekan',
            'lokasiSlug' => $lokasiSlug,
            'lokasiName' => $this->resolveLokasi($lokasiSlug),
        ]);
    }

    /**
     * GET /checklist/(:segment)/(:segment)
     * Halaman pilih kategori berdasarkan jenis (Preventive / Overhaul).
     */
    public function indexKategori(string $lokasiSlug, string $jenisSlug)
    {
        $lokasiName       = $this->resolveLokasi($lokasiSlug);
        $jenisDbName      = $this->resolveJenis($jenisSlug);
        $jenisDisplayName = $this->resolveJenisDisplay($jenisDbName);
        $idMesin          = $this->request->getGet('id_mesin') ?: null;

        // Auto-routing jika id_mesin ada
        if ($idMesin && strtolower($jenisSlug) === 'overhaul') {
            $mesin = $this->mesinModel->find($idMesin);
            if ($mesin) {
                if ($lokasiName === Lokasi::MFG1->value) {
                    // MFG 1 Overhaul selalu memakai form mesin-cnc-bar-feeder
                    return redirect()->to("/checklist/{$lokasiSlug}/{$jenisSlug}/create/mesin-cnc-bar-feeder?id_mesin={$idMesin}");
                } else if (!empty($mesin['jenis'])) {
                    // MFG 2 Overhaul mengikuti jenis mesinnya (milling, thread, dll)
                    $kategoriSlug = url_title(strtolower($mesin['jenis']), '-', true);
                    return redirect()->to("/checklist/{$lokasiSlug}/{$jenisSlug}/create/{$kategoriSlug}?id_mesin={$idMesin}");
                }
            }
        }

        // Jika Overhaul MFG 1 tanpa id_mesin, langsung arahkan ke form Mesin CNC & Bar Feeder
        if (strtolower($jenisSlug) === 'overhaul' && $lokasiName === Lokasi::MFG1->value) {
            $redirectUrl = "/checklist/{$lokasiSlug}/{$jenisSlug}/create/mesin-cnc-bar-feeder";
            if ($idMesin) {
                $redirectUrl .= "?id_mesin=" . $idMesin;
            }
            return redirect()->to($redirectUrl);
        } else {
            $categories = $this->resolveCategoriesList($jenisSlug, $lokasiName);
        }

        return view('checklist/index', [
            'title'      => "Pilih Kategori - {$jenisDisplayName} {$lokasiName}",
            'lokasiSlug' => $lokasiSlug,
            'lokasiName' => $lokasiName,
            'jenisSlug'  => $jenisSlug,
            'jenisName'  => $jenisDisplayName,
            'categories' => $categories,
            'idMesin'    => $idMesin,
        ]);
    }

    /**
     * GET /checklist/(:segment)/(:segment)/create/(:segment)
     * Menampilkan form pengecekan.
     */
    public function create(string $lokasiSlug, string $jenisSlug, string $categorySlug)
    {
        if (! isset($this->categoryMap[$categorySlug])) {
            return redirect()->to("/checklist/{$lokasiSlug}/{$jenisSlug}")->with('error', 'Kategori tidak valid.');
        }

        $lokasiName       = $this->resolveLokasi($lokasiSlug);
        $jenisDbName      = $this->resolveJenis($jenisSlug);
        $jenisDisplayName = $this->resolveJenisDisplay($jenisDbName);
        $categoryName     = $this->categoryMap[$categorySlug];
        $waktuMulai       = Time::now();
        $idMesin          = $this->request->getGet('id_mesin') ?: null;

        // NEW LOGIC: Block if Jadwal Preventive is not created for this month, location, and category
        if ($jenisDbName === JenisCheck::Preventive->value) {
            $jadwalModel = new \App\Models\JadwalPreventiveModel();
            $bulanIni = date('Y-m'); // e.g., '2026-07'
            
            $cekJadwal = $jadwalModel->where('lokasi', $lokasiName)
                                     ->where('kategori', $categoryName)
                                     ->where('bulan_tahun', $bulanIni)
                                     ->first();

            if (!$cekJadwal) {
                // If it came from QR scan with id_mesin, preserve the id_mesin in the redirect url if desired,
                // but redirecting back to category list is fine.
                $redirectUrl = "/checklist/{$lokasiSlug}/{$jenisSlug}";
                if ($idMesin) {
                    $redirectUrl .= "?id_mesin=" . $idMesin;
                }
                
                return redirect()->to($redirectUrl)
                                 ->with('error', "Gagal! Jadwal Preventive untuk kategori {$categoryName} di bulan ini belum dibuat oleh Admin/Leader.");
            }
        }

        if (strtolower($jenisSlug) === 'overhaul') {
            if ($lokasiName === Lokasi::MFG2->value) {
                $daftarMesin = $this->mesinModel->where('lokasi', $lokasiName)
                                                ->where('jenis', $categoryName)
                                                ->orderBy('no_mesin', 'ASC')
                                                ->findAll();
            } else {
                // MFG 1 - Overhaul (Mesin CNC & Bar Feeder)
                $daftarMesin = $this->mesinModel->where('lokasi', $lokasiName)
                                                ->where('jenis', 'CNC')
                                                ->orderBy('no_mesin', 'ASC')
                                                ->findAll();
            }
        } else {
            $daftarMesin = $this->mesinModel->getByLokasi($lokasiName);
        }

        $data = [
            'title'             => "Form Pengecekan {$jenisDisplayName} - {$categoryName}",
            'lokasiSlug'        => $lokasiSlug,
            'lokasiName'        => $lokasiName,
            'jenisSlug'         => $jenisSlug,
            'jenisName'         => $jenisDisplayName,
            'categorySlug'      => $categorySlug,
            'categoryName'      => $categoryName,
            'daftarMesin'       => $daftarMesin,
            'rows'              => $this->parameterModel->getFormRows($lokasiName, $jenisDbName, $categoryName),
            'masterPic'         => array_filter((new \App\Models\PicModel())->findAll(), function($p) {
                return strpos(strtolower(str_replace(' ', '', $p['role_pic'] ?? '')), Role::Leader->value) === false;
            }),
            'namaStaff'         => session()->get('nama'),
            'waktuMulai'        => $waktuMulai->toDateTimeString(),
            'waktuMulaiDisplay' => $waktuMulai->toLocalizedString('dd MMMM yyyy, HH:mm:ss'),
            'idMesin'           => $idMesin,
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
            // Overhaul
            if (isset($this->categoryMap[$kategoriSlug])) {
                $kategori = $this->categoryMap[$kategoriSlug];
            } else {
                $kategori = strtoupper(str_replace('-', ' ', $kategoriSlug));
            }
        } else {
            // Preventive
            if (isset($this->categoryMap[$kategoriSlug])) {
                $kategori = $this->categoryMap[$kategoriSlug];
            }
        }

        $bulan = date('Y-m'); // "satu mesin hanya satu bulan"

        $transaksiModel = new \App\Models\TransaksiCheckModel();
        $rows = $transaksiModel->checkDuplicate((int)$idMesin, $jenisCheck, $bulan, $kategori);

        $duplicate = count($rows) > 0;
        $tanggal   = '';
        $pic       = '';
        if ($duplicate) {
            $row     = $rows[0];
            $waktu   = $row['waktu_mulai'] ?: $row['created_at'];
            // Format: Kamis, 24 Juli 2026, 08:38
            $ts      = strtotime($waktu);
            $bulanId = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
            $hariId  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
            $tanggal = $hariId[date('w', $ts)] . ', ' . date('d', $ts) . ' ' . $bulanId[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts) . ', ' . date('H:i', $ts);
            $pic     = $row['nama_pic'] ?? '';
        }

        return $this->response->setJSON([
            'duplicate' => $duplicate,
            'kategori'  => $kategori,
            'tanggal'   => $tanggal,
            'pic'       => $pic,
        ]);
    }

    /**
     * POST /checklist/(:segment)/(:segment)/store
     */
    public function store(string $lokasiSlug, string $jenisSlug)
    {
        if (! $this->validateStoreRules($jenisSlug)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $lokasiName   = $this->resolveLokasi($lokasiSlug);
        $jenisName    = $this->resolveJenis($jenisSlug);
        $idMesin      = (int) $this->request->getPost('id_mesin');
        $waktuMulai   = $this->request->getPost('waktu_mulai');
        $kategoriName = $this->request->getPost('kategori');
        $waktuSelesai = Time::now()->toDateTimeString();

        $hasilCheck = $this->request->getPost('hasil_check') ?? [];
        $ulasan     = $this->request->getPost('ulasan') ?? [];
        $inputPic   = $this->request->getPost('nama_pic') ?: (session()->get('nama') ?: 'Staff');

        $categorySlug = array_search($kategoriName, $this->categoryMap, true) ?: 'penerangan';

        $db = \Config\Database::connect();
        $db->transStart();

        $idTransaksi = $this->transaksiModel->insert([
            'id_user'       => session()->get('user_id'),
            'nama_pic'      => $inputPic,
            'id_mesin'      => $idMesin,
            'lokasi_check'  => $lokasiName,
            'jenis_check'   => $jenisName,
            'kategori'      => $kategoriName,
            'waktu_mulai'   => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'status'        => 'Pending',
        ]);
        if (!$idTransaksi) {
            log_message('error', 'Failed to insert transaksi_check: ' . json_encode($this->transaksiModel->errors()));
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal membuat header transaksi pengecekan.');
        }

        $this->processChecklistDetails($idTransaksi, $hasilCheck, $ulasan);

        // (Logika ceklis_kontrol dipindah ke proses Approval)

        if (strtolower($jenisSlug) === 'overhaul') {
            $this->processOverhaulMetadata($idTransaksi);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data pengecekan.');
        }

        $roleSession = session()->get('role');
        if ($roleSession === Role::Magang->value) {
            $redirectUrl = "/riwayat/lokasi/{$lokasiSlug}?jenis_check=" . urlencode($jenisName) . "&kategori=" . urlencode($kategoriName);
        } else {
            $redirectUrl = "/approval";
        }

        return redirect()->to($redirectUrl)
                          ->with('success', 'Pengecekan berhasil disimpan. Durasi pengerjaan: '
                              . $this->formatDurasi($waktuMulai, $waktuSelesai));
    }

    /**
     * GET /checklist/(:segment)/overhaul
     * Halaman placeholder Overhaul.
     */
    public function overhaulPlaceholder(string $lokasiSlug)
    {
        return redirect()->to("/checklist/{$lokasiSlug}/overhaul");
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

            $this->detailModel->insert([
                'id_transaksi'    => $idTransaksi,
                'id_parameter'    => (int) $idParameter,
                'hasil_check'     => $hasil !== '' ? $hasil : null,
                'ulasan'          => $ulasan[$idParameter] ?? null,
                'foto_abnormal'   => $fotoAbnormal,
                'foto_abnormal_2' => $fotoAbnormal2,
            ]);
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

    private function resolveCategoriesList(string $jenisSlug, string $lokasiName): array
    {
        if (strtolower($jenisSlug) === 'overhaul' && $lokasiName === Lokasi::MFG2->value) {
            return [
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
        }

        if ($lokasiName === Lokasi::MFG2->value) {
            return [
                'penerangan'     => 'Penerangan',
                'kabel-dan-pipa' => 'Kabel dan Pipa',
                'angin-bocor'    => 'Angin Bocor',
            ];
        }

        return [
            'penerangan'     => 'Penerangan',
            'kabel-dan-pipa' => 'Kabel dan Pipa',
            'angin-bocor'    => 'Angin Bocor',
            'bearing-cam'    => 'Bearing Cam',
            'gearbox'        => 'Gearbox',
            'belt-cam'       => 'Belt Cam',
        ];
    }
}
