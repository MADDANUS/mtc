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

        // History hanya menampilkan dokumen yang sudah Approved Final
        // Admin bisa override via status=all
                $rawStatus = $this->request->getGet('status');
        if ($rawStatus === 'all') {
            $statusFilter = null;
        } elseif ($rawStatus && $rawStatus !== 'all') {
            $statusFilter = $rawStatus;
        } else {
            // Default history visibility based on role
            $role = session()->get('role');
            if ($role === 'leader') {
                $statusFilter = ['Approved L1', 'Approved L2', 'Approved', 'Approved Final'];
            } elseif ($role === 'sheadprd') {
                $statusFilter = ['Approved L2', 'Approved', 'Approved Final'];
            } elseif ($role === 'sheadmtc') { $statusFilter = ['Approved', 'Approved Final'];
            } elseif ($role === 'magang') { $statusFilter = null; } else { $statusFilter = ['Approved', 'Approved Final']; }
        }
        
        $filters = [
            'lokasi'      => $lokasiName,
            'id_mesin'    => $this->request->getGet('id_mesin') === 'all' ? null : ($this->request->getGet('id_mesin') ?: null),
            'line'        => $userLine ?: ($this->request->getGet('line') === 'all' ? null : ($this->request->getGet('line') ?: null)),
            'jenis_check' => $this->request->getGet('jenis_check') === 'all' ? null : ($this->request->getGet('jenis_check') ?: null),
            'kategori'    => $this->request->getGet('kategori') === 'all' ? null : ($this->request->getGet('kategori') ?: null),
            'bulan'       => $this->request->getGet('bulan') === 'all' ? null : ($this->request->getGet('bulan') ?: null),
            'status'      => $statusFilter,
            'pic'         => $this->request->getGet('pic') === 'all' ? null : ($this->request->getGet('pic') ?: null),
            'sort_by'     => $this->request->getGet('sort_by') ?: 'id_transaksi',
            'order'       => $this->request->getGet('order') ?: 'desc',
        ];

        // Semua role bisa lihat riwayat yang sudah Approved
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
        $riwayatService = new \App\Services\RiwayatService();
        try {
            $lokasiName = $riwayatService->validateLeaderAccess($lokasiName);
        } catch (\Exception $e) {
            return redirect()->to('/dashboard')->with('error', $e->getMessage());
        }
        $data = $riwayatService->getPdfAllData($lokasiName, $this->request->getGet());
        $html = view('riwayat/pdf_all_details', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $filename = "Summary_Riwayat_" . str_replace(' ', '_', $data['jenisLabel']) . "_" . date('Ymd_His') . ".pdf";
        $dompdf->stream($filename, ["Attachment" => true]);
    }

    public function detail(int $id)
    {
        $riwayatService = new \App\Services\RiwayatService();
        try {
            $data = $riwayatService->getDetailData($id, session()->get('role'));
        } catch (\Exception $e) {
            return redirect()->to('/dashboard')->with('error', $e->getMessage());
        }
        if (! $data) {
            return redirect()->to('/riwayat')->with('error', 'Transaksi tidak ditemukan.');
        }
        $data['title'] = 'Detail Pengecekan';
        $data['from'] = $this->request->getGet('from');
        $data['cb_lokasi'] = $this->request->getGet('lokasi');
        $data['cb_line'] = $this->request->getGet('line');
        $data['cb_kategori'] = $this->request->getGet('kategori');
        $data['cb_bulan'] = $this->request->getGet('bulan');
        $data['staffPic'] = $data['staffPicList'];
        return view('riwayat/detail', $data);
    }

    public function redirectDetail()
    {
        $riwayatService = new \App\Services\RiwayatService();
        $url = $riwayatService->getRedirectDetailUrl($this->request->getGet());
        if ($url) {
            return redirect()->to($url);
        } else {
            return redirect()->to('/riwayat/lokasi/' . rawurlencode($this->request->getGet('lokasi')) . '?line=' . rawurlencode($this->request->getGet('line')) . '&kategori=' . rawurlencode($this->request->getGet('kategori')) . '&bulan=' . rawurlencode($this->request->getGet('bulan')) . '&id_mesin=' . rawurlencode($this->request->getGet('id_mesin')))->with('error', 'Belum ada laporan untuk mesin ini di bulan yang dipilih.');
        }
    }

    public function downloadPdf(int $id)
    {
        $riwayatService = new \App\Services\RiwayatService();
        $data = $riwayatService->getPdfData($id);
        if (! $data) {
            return redirect()->to('/riwayat')->with('error', 'Transaksi tidak ditemukan.');
        }
        $data['title'] = 'Detail Pengecekan';
        $html = view('riwayat/detail_pdf', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $filename = 'Laporan_' . ($data['header']['jenis_check'] === 'Preventive' ? 'Checklist' : 'Inspection') . '_' . $data['header']['no_mesin'] . '_' . date('Ymd', strtotime($data['header']['waktu_mulai'])) . '.pdf';
        $dompdf->stream($filename, ["Attachment" => true]);
        exit();
    }

    public function edit(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        $riwayatService = new \App\Services\RiwayatService();
        $data = $riwayatService->getEditData($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }
        return view('riwayat/edit', $data);
    }

    public function update(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        $riwayatService = new \App\Services\RiwayatService();
        $result = $riwayatService->updateTransaksi($id, $this->request, \Config\Services::validation());
        if (isset($result['errors'])) {
            return redirect()->back()->withInput()->with('errors', $result['errors']);
        }
        if (!$result['status']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->back()->with('success', $result['message'] ?? 'Riwayat berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        $riwayatService = new \App\Services\RiwayatService();
        if ($riwayatService->deleteTransaksi($id)) {
            return redirect()->back()->with('success', 'Transaksi berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
    }

    public function approve($idTransaksi)
    {
        $riwayatService = new \App\Services\RiwayatService();
        $result = $riwayatService->approveTransaksi($idTransaksi, $this->request);
        if (!$result['status']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->back()->with('success', $result['message']);
    }
}
