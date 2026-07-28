<?php

namespace App\Controllers;

use App\Models\TransaksiCheckDetailModel;
use App\Models\TransaksiCheckModel;
use App\Models\MesinModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class RiwayatController extends BaseController
{
    private array $categoryMap = [
        'penerangan'     => 'Penerangan',
        'kabel-dan-pipa' => 'Kabel dan Pipa',
        'angin-bocor'    => 'Angin Bocor',
        'bearing-cam'    => 'Bearing Cam',
        'gearbox'        => 'Gearbox',
        'belt-cam'       => 'Belt Cam',
        'mesin-cnc-bar-feeder' => 'Mesin CNC & Bar Feeder',
    ];

    private function resolveLokasi(string $slug): ?string
    {
        return match (strtolower($slug)) {
            'mfg2'  => 'MFG 2',
            'mfg1'  => 'MFG 1',
            'semua' => null,
            default => 'MFG 1',
        };
    }

    /**
     * GET /riwayat
     * Halaman riwayat pengecekan (default semua lokasi)
     */
    public function index()
    {
        return redirect()->to('/riwayat/lokasi/semua');
    }

    /**
     * GET /riwayat/lokasi/(:segment)
     * Daftar riwayat pengecekan untuk lokasi terpilih beserta filter pencarian.
     */
    public function lokasi(string $lokasiSlug)
    {
        $lokasiName = $this->resolveLokasi($lokasiSlug);
        
        // Validasi lokasi khusus untuk Leader Produksi
        if (session()->get('role') === 'leader') {
            $userLokasi = session()->get('lokasi');
            // Jika leader mencoba akses 'semua' atau lokasi yang bukan miliknya
            if ($userLokasi && $userLokasi !== $lokasiName) {
                if ($lokasiName === null) {
                    // Paksa ke lokasinya
                    $lokasiName = $userLokasi;
                    $lokasiSlug = strtolower(str_replace(' ', '', $userLokasi));
                } else {
                    return redirect()->to('/dashboard')->with('error', 'Akses ditolak. Anda hanya dapat mengakses riwayat lokasi ' . $userLokasi);
                }
            }
        }

        $mesinModel = new MesinModel();
        $transaksiModel = new TransaksiCheckModel();

        // Dropdown filter mesin dinamis (semua mesin jika lokasi null)
        $daftarMesin = $mesinModel->getByLokasi($lokasiName);

        // Ambil input filter pencarian
        $userLine = (session()->get('role') === 'leader') ? session()->get('line') : null;
        
        $filters = [
            'lokasi'      => $lokasiName,
            'id_mesin'    => $this->request->getGet('id_mesin') === 'all' ? null : ($this->request->getGet('id_mesin') ?: null),
            'line'        => $userLine ?: ($this->request->getGet('line') === 'all' ? null : ($this->request->getGet('line') ?: null)),
            'jenis_check' => $this->request->getGet('jenis_check') === 'all' ? null : ($this->request->getGet('jenis_check') ?: null),
            'kategori'    => $this->request->getGet('kategori') === 'all' ? null : ($this->request->getGet('kategori') ?: null),
            'bulan'       => $this->request->getGet('bulan') === 'all' ? null : ($this->request->getGet('bulan') ?: null),
            'status'      => $this->request->getGet('status') === 'all' ? null : ($this->request->getGet('status') ?: null),
            'pic'         => $this->request->getGet('pic') === 'all' ? null : ($this->request->getGet('pic') ?: null),
            'sort_by'     => $this->request->getGet('sort_by') ?: 'id_transaksi',
            'order'       => $this->request->getGet('order') ?: 'desc',
        ];

        // Semua role bisa lihat semua riwayat
        $riwayat = $transaksiModel->getRiwayatFiltered($filters);

        // Pisahkan kategori dropdown berdasarkan Lokasi & Jenis Check secara dinamis
        $parameterModel = new \App\Models\ParameterCheckModel();
        
        // Jenis check pada transaksi_check bisa "Checklist Report", "Preventive", "Overhaul", "Inspection Report"
        // Tapi di master_parameter_check cuma ada "Preventive" dan "Overhaul".
        $jenisDb = (in_array(strtolower($filters['jenis_check']), ['preventive', 'checklist report'])) ? 'Preventive' : 'Overhaul';
        
        $catQuery = $parameterModel->select('kategori');
        if ($lokasiName !== null) {
            $catQuery->where('lokasi', $lokasiName);
        }
        $dbCategories = $catQuery->where('jenis_check', $jenisDb)
            ->distinct()
            ->findAll();

        $categoriesList = [];
        foreach ($dbCategories as $cat) {
            $slug = strtolower(str_replace(' ', '-', $cat['kategori']));
            $categoriesList[$slug] = $cat['kategori'];
        }

        $jenisLabel = $filters['jenis_check'] === 'Preventive' ? 'Checklist Report' : ($filters['jenis_check'] === 'Overhaul' ? 'Inspection Report' : 'Pengecekan');
        $title = "Riwayat {$jenisLabel} — {$lokasiName}";

        $availableLines = [];
        if ($lokasiName === 'MFG 1') {
            $availableLines = ['Line 1', 'Line 2', 'Line 3'];
        } elseif ($lokasiName === 'MFG 2') {
            $availableLines = ['CG', 'Second'];
        } else {
            $availableLines = ['Line 1', 'Line 2', 'Line 3', 'CG', 'Second'];
        }

        // Ambil daftar PIC dinamis berdasarkan transaksi yang ada
        $db = \Config\Database::connect();
        $picQuery = $db->table('transaksi_check')
                       ->select('transaksi_check.nama_pic, users.nama as nama_staff')
                       ->join('users', 'users.id = transaksi_check.id_user');
        if ($lokasiName !== null) {
            $picQuery->where('transaksi_check.lokasi_check', $lokasiName);
        }
        if (!empty($filters['jenis_check'])) {
            $picQuery->where('transaksi_check.jenis_check', $filters['jenis_check']);
        }
        $rawPics = $picQuery->distinct()->get()->getResultArray();
        $availablePics = [];
        foreach ($rawPics as $row) {
            $raw = $row['nama_pic'] ?: $row['nama_staff'];
            $parts = explode(' - ', $raw);
            $name = end($parts);
            if ($name) {
                $availablePics[] = trim($name);
            }
        }
        $availablePics = array_unique($availablePics);
        sort($availablePics);

        // List bulan dinamis dari database (berdasarkan waktu_mulai)
        $bulanQuery = $db->table('transaksi_check')
            ->select("DATE_FORMAT(waktu_mulai, '%Y-%m') as bulan", false);
        if ($lokasiName !== null) {
            $bulanQuery->where('lokasi_check', $lokasiName);
        }
        if (!empty($filters['jenis_check'])) {
            $bulanQuery->where('jenis_check', $filters['jenis_check']);
        }
        $rawBulan = $bulanQuery->distinct()->orderBy('bulan', 'DESC')->get()->getResultArray();
        $bulanList = [];
        foreach ($rawBulan as $row) {
            $val = $row['bulan'];
            if ($val) {
                $time = \CodeIgniter\I18n\Time::parse($val . '-01');
                $label = $time->toLocalizedString('MMMM yyyy');
                $bulanList[$val] = $label;
            }
        }
        // Tetap tambahkan bulan ini jika belum ada (opsional)
        $currentMonthVal = \CodeIgniter\I18n\Time::now()->format('Y-m');
        if (!isset($bulanList[$currentMonthVal])) {
            $bulanList[$currentMonthVal] = \CodeIgniter\I18n\Time::now()->toLocalizedString('MMMM yyyy');
            krsort($bulanList); // sort keys descending
        }

        return view('riwayat/index', [
            'title'           => $title,
            'jenisLabel'      => $jenisLabel,
            'lokasiSlug'      => $lokasiSlug,
            'lokasiName'      => $lokasiName ?? 'Semua Lokasi',
            'availableLines'  => $availableLines,
            'availablePics'   => $availablePics,
            'bulanList'       => $bulanList,
            'userLine'        => $userLine,
            'daftarMesin'     => $daftarMesin,
            'categories'      => $categoriesList,
            'riwayat'         => $riwayat,
            'selectedFilters' => $filters,
        ]);
    }

    /**
     * GET /riwayat/download-pdf-all/(:segment)
     * Download tabel riwayat secara keseluruhan berdasarkan filter yang aktif
     */
    public function downloadPdfAll(string $lokasiSlug)
    {
        $lokasiName = $this->resolveLokasi($lokasiSlug);
        
        // Validasi lokasi khusus untuk Leader Produksi
        if (session()->get('role') === 'leader') {
            $userLokasi = session()->get('lokasi');
            if ($userLokasi && $userLokasi !== $lokasiName) {
                if ($lokasiName === null) {
                    $lokasiName = $userLokasi;
                    $lokasiSlug = strtolower(str_replace(' ', '', $userLokasi));
                } else {
                    return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
                }
            }
        }

        $transaksiModel = new TransaksiCheckModel();
        $userLine = (session()->get('role') === 'leader') ? session()->get('line') : null;
        
        $filters = [
            'lokasi'      => $lokasiName,
            'id_mesin'    => $this->request->getGet('id_mesin') === 'all' ? null : ($this->request->getGet('id_mesin') ?: null),
            'line'        => $userLine ?: ($this->request->getGet('line') === 'all' ? null : ($this->request->getGet('line') ?: null)),
            'jenis_check' => $this->request->getGet('jenis_check') === 'all' ? null : ($this->request->getGet('jenis_check') ?: null),
            'kategori'    => $this->request->getGet('kategori') === 'all' ? null : ($this->request->getGet('kategori') ?: null),
            'bulan'       => $this->request->getGet('bulan') === 'all' ? null : ($this->request->getGet('bulan') ?: null),
            'status'      => $this->request->getGet('status') === 'all' ? null : ($this->request->getGet('status') ?: null),
            'pic'         => $this->request->getGet('pic') === 'all' ? null : ($this->request->getGet('pic') ?: null),
            'sort_by'     => $this->request->getGet('sort_by') ?: 'id_transaksi',
            'order'       => $this->request->getGet('order') ?: 'desc',
        ];

        $riwayat = $transaksiModel->getRiwayatFiltered($filters);
        
        // Fetch full details for each transaction
        $detailModel = new \App\Models\TransaksiCheckDetailModel();
        $allReports = [];
        foreach ($riwayat as $row) {
            $header = $transaksiModel->getDetailTransaksi($row['id_transaksi']);
            if ($header) {
                $details = $detailModel->getDetailByTransaksi($row['id_transaksi']);
                $details = $detailModel->calculateRowspans($details, $header['jenis_check']);
                $allReports[] = [
                    'header' => $header,
                    'details' => $details
                ];
            }
        }
        
        $jenisLabel = $filters['jenis_check'] === 'Preventive' ? 'Checklist Report' : ($filters['jenis_check'] === 'Overhaul' ? 'Inspection Report' : 'Pengecekan');

        $data = [
            'title'      => "Riwayat {$jenisLabel} - {$lokasiName}",
            'allReports' => $allReports,
            'filters'    => $filters,
            'lokasiName' => $lokasiName,
            'jenisLabel' => $jenisLabel
        ];

        $html = view('riwayat/pdf_all_details', $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = "Summary_Riwayat_" . str_replace(' ', '_', $jenisLabel) . "_" . date('Ymd_His') . ".pdf";
        $dompdf->stream($filename, ["Attachment" => true]);
    }

    /**
     * GET /riwayat/kategori/(:segment)
     * Fallback redirect untuk kompatibilitas tautan lama agar mengarah ke riwayat lokasi MFG 1.
     */
    public function kategori(string $categorySlug)
    {
        if (! isset($this->categoryMap[$categorySlug])) {
            return redirect()->to('/riwayat')->with('error', 'Kategori tidak valid.');
        }

        $categoryName = $this->categoryMap[$categorySlug];
        return redirect()->to('/riwayat/lokasi/mfg1?kategori=' . urlencode($categoryName));
    }

    /**
     * GET /riwayat/(:num)
     * Detail pengerjaan checklist riwayat pengecekan.
     */
    public function detail(int $id)
    {
        $transaksiModel = new TransaksiCheckModel();
        $header         = $transaksiModel->getDetailTransaksi($id);

        if (! $header) {
            return redirect()->to('/riwayat')->with('error', 'Transaksi tidak ditemukan.');
        }

        // Semua role bisa lihat semua riwayat (tidak ada pembatasan per user)
        // KECUALI untuk Approver: jangan biarkan mereka melihat sebelum giliran mereka
        $roleSession = session()->get('role');
        $approvalStatus = $header['status'] ?? 'Pending';
        
        if ($roleSession === 'sheadprd' && $approvalStatus === 'Pending') {
            return redirect()->to('/dashboard')->with('error', 'Dokumen ini belum siap (Masih menunggu Leader).');
        }
        if ($roleSession === 'sheadmtc' && in_array($approvalStatus, ['Pending', 'Approved L1'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Dokumen ini belum siap (Masih menunggu SHead Produksi).');
        }

        $detailModel = new TransaksiCheckDetailModel();
        $details     = $detailModel->getDetailByTransaksi($id);
        $details     = $detailModel->calculateRowspans($details, $header['jenis_check']);

        $durasiDetik = null;
        if (! empty($header['waktu_mulai']) && ! empty($header['waktu_selesai'])) {
            $durasiDetik = strtotime($header['waktu_selesai']) - strtotime($header['waktu_mulai']);
        }

        return view('riwayat/detail', [
            'title'       => 'Detail Pengecekan',
            'header'      => $header,
            'details'     => $details,
            'durasiDetik' => $durasiDetik,
            'leaderPicList' => (function() use ($header) {
                $lineSlug = strtolower(str_replace(' ', '', $header['line'] ?? ''));
                if ($lineSlug === 'second') $lineSlug = 'sc';
                $roleName = 'leader' . str_replace('line', '', $lineSlug);
                return (new \App\Models\PicModel())->where('role_pic', $roleName)->findAll();
            })(),
            'staffPic'    => (new \App\Models\PicModel())->where('role_pic', 'Staff')->findAll(),
            'from'        => $this->request->getGet('from'),
            'cb_lokasi'   => $this->request->getGet('lokasi'),
            'cb_line'     => $this->request->getGet('line'),
            'cb_kategori' => $this->request->getGet('kategori'),
            'cb_bulan'    => $this->request->getGet('bulan'),
        ]);
    }

    public function redirectDetail()
    {
        $idMesin  = $this->request->getGet('id_mesin');
        $kategori = $this->request->getGet('kategori');
        $bulan    = $this->request->getGet('bulan');
        $line     = $this->request->getGet('line');
        $lokasi   = $this->request->getGet('lokasi');

        $db = \Config\Database::connect();
        $tx = $db->table('transaksi_check')
                 ->select('id_transaksi')
                 ->where('id_mesin', $idMesin)
                 ->where('kategori', $kategori)
                 ->like('waktu_mulai', $bulan, 'after')
                 ->orderBy('id_transaksi', 'DESC')
                 ->get()
                 ->getRowArray();

        if ($tx) {
            $qsArray = [
                'from'     => 'kontrol',
                'lokasi'   => $lokasi,
                'line'     => $line,
                'kategori' => $kategori,
                'bulan'    => $bulan,
            ];
            $qsSummary = $this->request->getGet('qs_summary');
            if ($qsSummary) {
                $qsArray['qs_summary'] = $qsSummary;
            }
            $qs = http_build_query($qsArray);
            return redirect()->to('/riwayat/' . $tx['id_transaksi'] . '?' . $qs);
        } else {
            // Fallback ke daftar riwayat jika tidak ada transaksi
            return redirect()->to('/riwayat/lokasi/' . rawurlencode($lokasi) . '?line=' . rawurlencode($line) . '&kategori=' . rawurlencode($kategori) . '&bulan=' . rawurlencode($bulan) . '&id_mesin=' . rawurlencode($idMesin))->with('error', 'Belum ada laporan untuk mesin ini di bulan yang dipilih.');
        }
    }

    /**
     * Download PDF untuk Riwayat
     */
    public function downloadPdf(int $id)
    {
        $transaksiModel = new TransaksiCheckModel();
        $header         = $transaksiModel->getDetailTransaksi($id);

        if (! $header) {
            return redirect()->to('/riwayat')->with('error', 'Transaksi tidak ditemukan.');
        }

        $detailModel = new TransaksiCheckDetailModel();
        $details     = $detailModel->getDetailByTransaksi($id);
        $details     = $detailModel->calculateRowspans($details, $header['jenis_check']);

        $durasiDetik = null;
        if (! empty($header['waktu_mulai']) && ! empty($header['waktu_selesai'])) {
            $durasiDetik = strtotime($header['waktu_selesai']) - strtotime($header['waktu_mulai']);
        }

        // Render HTML
        $html = view('riwayat/detail_pdf', [
            'title'       => 'Detail Pengecekan',
            'header'      => $header,
            'details'     => $details,
            'durasiDetik' => $durasiDetik,
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Laporan_' . ($header['jenis_check'] === 'Preventive' ? 'Checklist' : 'Inspection') . '_' . $header['no_mesin'] . '_' . date('Ymd', strtotime($header['waktu_mulai'])) . '.pdf';

        $dompdf->stream($filename, ["Attachment" => true]);
        exit();
    }

    /**
     * GET /riwayat/edit/(:num)
     * Menampilkan form edit riwayat pengecekan (Khusus Admin).
     */
    public function edit(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $transaksiModel = new TransaksiCheckModel();
        $header = $transaksiModel->getDetailTransaksi($id);
        if (!$header) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }

        $detailModel = new TransaksiCheckDetailModel();
        $details = $detailModel->where('id_transaksi', $id)->findAll();

        $detailsMap = [];
        foreach ($details as $d) {
            $detailsMap[$d['id_parameter']] = $d;
        }

        $mesinModel = new MesinModel();
        $parameterModel = new \App\Models\ParameterCheckModel();
        
        // Convert string ke format routing (slug)
        $lokasiSlug = strtolower(str_replace(' ', '', $header['lokasi_check']));
        $jenisSlug = strtolower(str_replace(' ', '-', $header['jenis_check']));
        $categorySlug = array_search($header['kategori'], $this->categoryMap, true) ?: 'penerangan';
        $waktuMulai = new \CodeIgniter\I18n\Time($header['waktu_mulai']);

        $data = [
            'title'             => "Edit Pengecekan {$header['jenis_check']} - {$header['kategori']}",
            'lokasiSlug'        => $lokasiSlug,
            'lokasiName'        => $header['lokasi_check'],
            'jenisSlug'         => $jenisSlug,
            'jenisName'         => $header['jenis_check'],
            'categorySlug'      => $categorySlug,
            'categoryName'      => $header['kategori'],
            'daftarMesin'       => $mesinModel->getByLokasi($header['lokasi_check']),
            'rows'              => $parameterModel->getFormRows($header['lokasi_check'], $header['jenis_check'], $header['kategori']),
            'masterPic'         => (new \App\Models\PicModel())->findAll(),
            'staffPic'          => (new \App\Models\PicModel())->where('role_pic', 'Staff')->findAll(),
            'namaPic'           => $header['nama_pic'],
            'namaStaff'         => $header['nama_staff'],
            'waktuMulai'        => $header['waktu_mulai'],
            'waktuMulaiDisplay' => $waktuMulai->toLocalizedString('dd MMMM yyyy, HH:mm:ss'),
            'idMesin'           => $header['id_mesin'],
            'isEdit'            => true,
            'idTransaksi'       => $id,
            'detailsMap'        => $detailsMap,
        ];

        // Jika overhaul, ambil bar_feeder_type dan support_pic
        if (strtolower($header['jenis_check']) === 'overhaul') {
            $db = \Config\Database::connect();
            $overhaul = $db->table('transaksi_overhaul')->where('id_transaksi', $id)->get()->getRowArray();
            if ($overhaul) {
                $data['barFeederType'] = $overhaul['bar_feeder_type'];
                $data['supportPic'] = $overhaul['support_pic'];
                $data['noteRecommendation'] = $overhaul['note_recommendation'];
            }
        }

        return view('checklist/form', $data);
    }

    /**
     * POST /riwayat/update/(:num)
     * Menyimpan perubahan dari form edit riwayat pengecekan (Khusus Admin).
     */
    public function update(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $transaksiModel = new TransaksiCheckModel();
        $header = $transaksiModel->find($id);
        if (!$header) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }

        $rules = [
            'id_mesin'    => 'required|numeric',
            'waktu_mulai' => 'required',
            'kategori'    => 'required',
        ];

        if (strtolower($header['jenis_check']) === 'overhaul') {
            $rules['bar_feeder_type'] = 'permit_empty|string';
            $rules['support_pic.*']   = 'permit_empty|string';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', \Config\Services::validation()->getErrors());
        }

        $idMesin      = (int) $this->request->getPost('id_mesin');
        $namaPic      = $this->request->getPost('nama_pic') ?: $header['nama_pic'];
        $waktuMulai   = $this->request->getPost('waktu_mulai');
        $kategoriName = $this->request->getPost('kategori');
        $waktuSelesai = $header['waktu_selesai']; // Tetap pakai waktu selesai asli

        $hasilCheck = $this->request->getPost('hasil_check') ?? [];
        $ulasan     = $this->request->getPost('ulasan') ?? [];

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Update Header
        $transaksiModel->update($id, [
            'id_mesin'      => $idMesin,
            'nama_pic'      => $namaPic,
            'kategori'      => $kategoriName,
            'waktu_mulai'   => $waktuMulai,
            'status'        => 'Pending', // Reset approval on edit?
            'approved_by'   => null,
            'approved_at'   => null,
        ]);

        // 2. Delete existing detail & laporan_abnormal
        $detailModel = new TransaksiCheckDetailModel();
        
        // Simpan foto lama sebelum dihapus agar tidak hilang jika tidak ada upload baru
        $oldDetails = $detailModel->where('id_transaksi', $id)->findAll();
        $oldPhotos = [];
        foreach ($oldDetails as $od) {
            $oldPhotos[$od['id_parameter']] = [
                'f1' => $od['foto_abnormal'],
                'f2' => $od['foto_abnormal_2']
            ];
        }

        // Delete cascading will handle laporan_abnormal
        $detailModel->where('id_transaksi', $id)->delete();

        // 3. Re-insert Details
        $parameterModel = new \App\Models\ParameterCheckModel();
        $uploadPath = FCPATH . 'uploads/abnormal/';
        foreach ($hasilCheck as $idParameter => $hasil) {
            $fotoAbnormal = $oldPhotos[$idParameter]['f1'] ?? null;
            $fotoAbnormal2 = $oldPhotos[$idParameter]['f2'] ?? null;

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

            $idDetail = $detailModel->insert([
                'id_transaksi'   => $id,
                'id_parameter'   => (int) $idParameter,
                'hasil_check'    => $hasil !== '' ? $hasil : null,
                'ulasan'         => $ulasan[$idParameter] ?? null,
                'foto_abnormal'  => $fotoAbnormal,
                'foto_abnormal_2'=> $fotoAbnormal2,
            ]);

            // Save to laporan_abnormal ONLY if status is Δ (segitiga)
            if ($hasil === 'Δ') {
                $paramInfo = $parameterModel->find((int)$idParameter);
                $pointCheckName = $paramInfo ? $paramInfo['point_check'] : 'Parameter #' . $idParameter;
                
                $abnormalDesc = $ulasan[$idParameter] ?? '';
                if (empty($abnormalDesc)) {
                    $abnormalDesc = 'Ditemukan kondisi abnormal (' . $hasil . ')';
                }

                $db->table('laporan_abnormal')->insert([
                    'id_transaksi'       => $id,
                    'id_detail'          => $idDetail,
                    'id_mesin'           => $idMesin,
                    'point_check'        => $pointCheckName,
                    'abnormal_condition' => $abnormalDesc,
                    'pengecekan_tanggal' => date('Y-m-d', strtotime($waktuSelesai)),
                    'pengecekan_pic'     => session()->get('nama') ?: 'Admin', // who edited
                    'foto_abnormal'      => $fotoAbnormal,
                    'foto_abnormal_2'    => $fotoAbnormal2,
                    'created_at'         => $waktuSelesai,
                    'updated_at'         => $waktuSelesai,
                ]);
            }
        }

        // 4. Update Overhaul Table
        if (strtolower($header['jenis_check']) === 'overhaul') {
            $barFeederType = $this->request->getPost('bar_feeder_type');
            $rawSupport    = $this->request->getPost('support_pic');
            
            $supportStr = null;
            if (is_array($rawSupport)) {
                $filtered = array_filter(array_map('trim', $rawSupport));
                if (!empty($filtered)) {
                    $supportStr = implode(', ', $filtered);
                }
            }
            
            $existing = $db->table('transaksi_overhaul')->where('id_transaksi', $id)->get()->getRowArray();
            if ($existing) {
                $db->table('transaksi_overhaul')->where('id_transaksi', $id)->update([
                    'bar_feeder_type'     => $barFeederType ?: null,
                    'support_pic'         => $supportStr,
                    'note_recommendation' => $this->request->getPost('note_recommendation') ?: null,
                ]);
            } else {
                $db->table('transaksi_overhaul')->insert([
                    'id_transaksi'        => $id,
                    'bar_feeder_type'     => $barFeederType ?: null,
                    'support_pic'         => $supportStr,
                    'note_recommendation' => $this->request->getPost('note_recommendation') ?: null,
                ]);
            }
        }

        // 5. Update Checklist Control (jika preventive)
        if (strtolower($header['jenis_check']) === 'preventive' || strtolower($header['jenis_check']) === 'checklist report') {
            // Re-calculate
            $combinedUlasan = [];
            foreach ($ulasan as $idParam => $text) {
                $text = trim($text);
                if ($text !== '') {
                    $combinedUlasan[] = $text;
                }
            }
            $ulasanKontrol = !empty($combinedUlasan) ? implode(', ', $combinedUlasan) : null;

            $overallStatus = 'V';
            $hasTriangle   = false;
            $allAreX       = true;
            foreach ($hasilCheck as $hasil) {
                if ($hasil === 'Δ') {
                    $hasTriangle = true;
                    $allAreX = false;
                } elseif ($hasil === 'V') {
                    $allAreX = false;
                } elseif ($hasil !== 'X') {
                    $allAreX = false;
                }
            }
            if ($allAreX && count($hasilCheck) > 0) {
                $overallStatus = 'X';
            } elseif ($hasTriangle) {
                $overallStatus = 'Δ';
            } else {
                $overallStatus = 'V';
            }

            $tanggalCheckDate = date('Y-m-d', strtotime($waktuSelesai));
            
            // Try updating where id_mesin + kategori + tanggal_check matches
            $db->table('ceklis_kontrol')
               ->where('id_mesin', $header['id_mesin'])
               ->where('kategori', $header['kategori'])
               ->where('tanggal_check', $tanggalCheckDate)
               ->update([
                   'id_mesin'     => $idMesin, // in case it changed
                   'status_check' => $overallStatus,
                   'ulasan'       => $ulasanKontrol,
               ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat mengupdate riwayat.');
        }

        return redirect()->to('/riwayat/' . $id)->with('success', 'Riwayat berhasil diupdate.');
    }

    /**
     * POST /riwayat/delete/(:num)
     * Menghapus riwayat pengecekan (Khusus Admin).
     */
    public function delete(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $transaksiModel = new TransaksiCheckModel();
        $header = $transaksiModel->find($id);
        if (!$header) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Jika tipe checklist ini update ceklis_kontrol, coba hapus
        if (in_array(strtolower($header['jenis_check']), ['preventive', 'checklist report'], true)) {
            $tanggalCheckDate = date('Y-m-d', strtotime($header['waktu_selesai'] ?? date('Y-m-d')));
            $db->table('ceklis_kontrol')
               ->where('id_mesin', $header['id_mesin'])
               ->where('kategori', $header['kategori'])
               ->where('tanggal_check', $tanggalCheckDate)
               ->delete();
        }

        // Hapus laporan abnormal secara eksplisit karena mungkin tidak ada CASCADE
        $db->table('laporan_abnormal')->where('id_transaksi', $id)->delete();
        
        // Hapus detail check secara eksplisit
        $db->table('transaksi_check_detail')->where('id_transaksi', $id)->delete();

        // Delete transaksi utama
        $transaksiModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus riwayat (Gagal menghapus detail/abnormal).');
        }

        return redirect()->back()->with('success', 'Riwayat pengecekan berhasil dihapus.');
    }

    /**
     * POST /riwayat/approve/(:num)
     * Menyetujui laporan pengecekan dan memproses laporan_abnormal & ceklis_kontrol.
     */
    public function approve($idTransaksi)
    {
        $role = session()->get('role');
        if (!in_array($role, ['member', 'sheadprd', 'sheadmtc', 'admin', 'leader'], true)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk menyetujui laporan.');
        }

        $transaksiModel = new TransaksiCheckModel();
        $transaksi = $transaksiModel->find($idTransaksi);

        if (!$transaksi) {
            return redirect()->back()->with('error', 'Laporan tidak ditemukan.');
        }

        if ($role === 'leader') {
            $mesinModel = new \App\Models\MesinModel();
            $mesinInfo = $mesinModel->find($transaksi['id_mesin']);
            
            if ($mesinInfo) {
                if (session()->get('lokasi') && $mesinInfo['lokasi'] !== session()->get('lokasi')) {
                    return redirect()->back()->with('error', 'Anda hanya dapat menyetujui laporan dari mesin di lokasi ' . session()->get('lokasi'));
                }
                
                if (session()->get('line') && strtolower($mesinInfo['line']) !== strtolower(session()->get('line'))) {
                    return redirect()->back()->with('error', 'Akses ditolak! Mesin ini tidak berada di ' . session()->get('line') . ' yang menjadi tanggung jawab Anda.');
                }
            }
        }

        if ($transaksi['status'] === 'Approved') {
            return redirect()->back()->with('error', 'Laporan ini sudah disetujui sepenuhnya.');
        }

        $jenisSlug = strtolower(str_replace(' ', '-', $transaksi['jenis_check']));
        $now = date('Y-m-d H:i:s');
        $userId = session()->get('user_id');
        $waktuSelesai = $transaksi['waktu_selesai'] ?? $now;

        $updateData = [];
        $newStatus = '';

        if ($jenisSlug === 'overhaul') {
            // MULTI-LEVEL APPROVAL UNTUK OVERHAUL
            if ($role === 'admin') {
                $newStatus = 'Approved';
                $updateData = [
                    'status' => 'Approved',
                    'approved_by' => $userId,
                    'approved_at' => $now,
                ];
            } elseif ($role === 'leader') {
                if ($transaksi['status'] !== 'Pending') {
                    return redirect()->back()->with('error', 'Laporan sudah diperiksa (bukan status Pending).');
                }
                
                $leaderNama = $this->request->getPost('leader_nama');
                if (empty(trim($leaderNama))) {
                    return redirect()->back()->with('error', 'Nama Leader wajib diisi.');
                }
                
                $newStatus = 'Approved L1';
                $updateData = [
                    'status' => 'Approved L1',
                    'approval_l1_by' => $userId,
                    'leader_nama' => trim($leaderNama),
                    'approval_l1_at' => $now,
                ];
            } elseif ($role === 'sheadprd') {
                if ($transaksi['status'] !== 'Approved L1') {
                    return redirect()->back()->with('error', 'Laporan belum diperiksa oleh Leader.');
                }
                $newStatus = 'Approved L2';
                $updateData = [
                    'status' => 'Approved L2',
                    'approval_l2_by' => $userId,
                    'approval_l2_at' => $now,
                ];
            } elseif ($role === 'sheadmtc') {
                if ($transaksi['status'] !== 'Approved L2') {
                    return redirect()->back()->with('error', 'Laporan belum disetujui oleh S. Head Produksi.');
                }
                $newStatus = 'Approved';
                $updateData = [
                    'status' => 'Approved',
                    'approved_by' => $userId,
                    'approved_at' => $now,
                ];
            } else {
                return redirect()->back()->with('error', 'Role Anda tidak memiliki akses persetujuan untuk laporan Overhaul.');
            }
        } else {
            // PREVENTIVE (SINGLE-LEVEL)
            if (!in_array($role, ['admin', 'member'], true)) {
                return redirect()->back()->with('error', 'Hanya Admin atau Member MTC yang dapat menyetujui laporan Preventive.');
            }
            
            $picLineNama = $this->request->getPost('pic_line_nama');
            if (empty(trim($picLineNama))) {
                return redirect()->back()->with('error', 'Nama PIC Line wajib diisi.');
            }

            $newStatus = 'Approved';
            $updateData = [
                'status' => 'Approved',
                'approved_by' => $userId,
                'pic_line_nama' => trim($picLineNama),
                'approved_at' => $now,
            ];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Update status
        $transaksiModel->update($idTransaksi, $updateData);

        // Jika sudah Final (Approved), barulah proses Laporan Abnormal & Checklist Control
        if ($newStatus === 'Approved') {

        // 2. Fetch details for laporan_abnormal
        $detailModel = new TransaksiCheckDetailModel();
        $details = $detailModel->where('id_transaksi', $idTransaksi)->findAll();
        
        $parameterModel = new \App\Models\ParameterCheckModel();
        
        // Setup data for ceklis_kontrol
        $hasilCheck = [];
        $ulasan = [];
        
        foreach ($details as $d) {
            $idParameter = $d['id_parameter'];
            $hasil       = $d['hasil_check'];
            $ulasanParam = $d['ulasan'];
            
            $hasilCheck[$idParameter] = $hasil;
            $ulasan[$idParameter]     = $ulasanParam;

            // Save to laporan_abnormal ONLY if status is Δ (segitiga)
            if ($hasil === 'Δ') {
                $paramInfo = $parameterModel->find((int)$idParameter);
                $pointCheckName = $paramInfo ? $paramInfo['point_check'] : 'Parameter #' . $idParameter;
                
                $abnormalDesc = $ulasanParam ?? '';
                if (empty($abnormalDesc)) {
                    $abnormalDesc = 'Ditemukan kondisi abnormal (' . $hasil . ')';
                }

                $db->table('laporan_abnormal')->insert([
                    'id_transaksi'       => $idTransaksi,
                    'id_detail'          => $d['id_detail'],
                    'id_mesin'           => $transaksi['id_mesin'],
                    'point_check'        => $pointCheckName,
                    'abnormal_condition' => $abnormalDesc,
                    'pengecekan_tanggal' => date('Y-m-d', strtotime($waktuSelesai)),
                    'pengecekan_pic'     => $transaksi['nama_pic'],
                    'foto_abnormal'      => $d['foto_abnormal'] ?? null,
                    'foto_abnormal_2'    => $d['foto_abnormal_2'] ?? null,
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                ]);
            }
        }

        // 3. Simpan atau update Checklist Control jika jenisnya adalah Preventive
        $jenisSlug = strtolower(str_replace(' ', '-', $transaksi['jenis_check']));
        if ($jenisSlug === 'preventive' || $jenisSlug === 'checklist-report') {
            $kategoriName = $transaksi['kategori'];
            $lokasiName   = $transaksi['lokasi_check'];
            $idMesin      = $transaksi['id_mesin'];
            $picNama      = $transaksi['nama_pic'];

            // Gabungkan ulasan parameter yang tidak kosong
            $combinedUlasan = [];
            foreach ($ulasan as $text) {
                $text = trim($text ?? '');
                if ($text !== '') {
                    $combinedUlasan[] = $text;
                }
            }
            $ulasanKontrol = !empty($combinedUlasan) ? implode(', ', $combinedUlasan) : null;

            // Hitung status keseluruhan (Δ > V) (X diabaikan kecuali semuanya X)
            $overallStatus = 'V';
            $hasTriangle   = false;
            $allAreX       = true;
            foreach ($hasilCheck as $hasil) {
                if ($hasil === 'Δ') {
                    $hasTriangle = true;
                    $allAreX = false;
                } elseif ($hasil === 'V') {
                    $allAreX = false;
                } elseif ($hasil !== 'X') {
                    $allAreX = false;
                }
            }
            if ($allAreX && count($hasilCheck) > 0) {
                $overallStatus = 'X';
            } elseif ($hasTriangle) {
                $overallStatus = 'Δ';
            } else {
                $overallStatus = 'V';
            }

            $bulanTahun = date('Y-m', strtotime($waktuSelesai));
            $tanggalCheckDate = date('Y-m-d', strtotime($waktuSelesai));

            // Ambil jadwal rencana untuk bulan ini
            $schedule = $db->table('jadwal_preventive')
                           ->where('lokasi', $lokasiName)
                           ->where('kategori', $kategoriName)
                           ->where('bulan_tahun', $bulanTahun)
                           ->get()
                           ->getRowArray();

            $outOfPlanDate = null;
            $periodeKe     = null;

            if ($schedule) {
                $tglRencana = strtotime($schedule['tanggal_rencana']);
                $dayOfWeek  = (int) date('N', $tglRencana); 
                $mondayTs   = strtotime('-' . ($dayOfWeek - 1) . ' days', $tglRencana);

                $weekDates = []; 
                for ($d = 0; $d < 5; $d++) {
                    $weekDates[$d + 1] = date('Y-m-d', strtotime("+{$d} days", $mondayTs));
                }

                $matchedCol = array_search($tanggalCheckDate, $weekDates);
                if ($matchedCol !== false) {
                    $periodeKe = (int) $matchedCol;
                } else {
                    $outOfPlanDate = $tanggalCheckDate;
                    $day = (int) date('d', strtotime($waktuSelesai));
                    $periodeKe = intval(($day - 1) / 7) + 1;
                    if ($periodeKe > 5) $periodeKe = 5;
                }
            } else {
                $outOfPlanDate = $tanggalCheckDate;
                $day = (int) date('d', strtotime($waktuSelesai));
                $periodeKe = intval(($day - 1) / 7) + 1;
                if ($periodeKe > 5) $periodeKe = 5;
            }

            $kontrolData = [
                'id_mesin'      => $idMesin,
                'kategori'      => $kategoriName,
                'bulan_tahun'   => $bulanTahun,
                'periode_ke'    => $periodeKe,
                'status_check'  => $overallStatus,
                'pic_nama'      => $picNama,
                'out_of_plan'   => $outOfPlanDate,
                'ulasan'        => $ulasanKontrol,
                'tanggal_check' => $tanggalCheckDate,
                'updated_at'    => date('Y-m-d H:i:s'),
            ];

            $exist = $db->table('ceklis_kontrol')
                        ->where('id_mesin', $idMesin)
                        ->where('kategori', $kategoriName)
                        ->where('bulan_tahun', $bulanTahun)
                        ->where('periode_ke', $periodeKe)
                        ->get()
                        ->getRowArray();

            if ($exist) {
                $db->table('ceklis_kontrol')
                   ->where('id_kontrol', $exist['id_kontrol'])
                   ->update($kontrolData);
            } else {
                $kontrolData['created_at'] = date('Y-m-d H:i:s');
                $db->table('ceklis_kontrol')->insert($kontrolData);
            }
        }

        } // <-- End of if ($newStatus === 'Approved')

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memproses persetujuan laporan.');
        }

        if ($newStatus === 'Approved') {
            return redirect()->back()->with('success', 'Laporan berhasil disetujui sepenuhnya. Data kini masuk ke Checklist Control dan Laporan Abnormal jika ada.');
        } else {
            return redirect()->back()->with('success', 'Laporan berhasil disetujui (Tahap: ' . $newStatus . '). Menunggu persetujuan selanjutnya.');
        }
    }

}

