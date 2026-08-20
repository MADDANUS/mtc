<?php

namespace App\Controllers;

use App\Enums\Role;
use App\Enums\Lokasi;
use App\Enums\JenisCheck;

use App\Models\TransaksiCheckDetailModel;
use App\Models\TransaksiCheckModel;
use App\Models\MesinModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
            'mfg2'  => Lokasi::MFG2->value,
            'mfg1'  => Lokasi::MFG1->value,
            'plan2' => Lokasi::PLAN2->value,
            'semua' => null,
            default => Lokasi::MFG1->value,
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
        
        $riwayatService = new \App\Services\RiwayatService();
        try {
            $lokasiName = $riwayatService->validateLeaderAccess($lokasiName);
        } catch (\Exception $e) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak. Anda hanya dapat mengakses riwayat lokasi ' . session()->get('lokasi'));
        }
        if ($lokasiName && $lokasiName !== $this->resolveLokasi($lokasiSlug)) {
             $lokasiSlug = strtolower(str_replace(' ', '', $lokasiName));
        }

        $mesinModel = new MesinModel();
        $transaksiModel = new TransaksiCheckModel();

        // Dropdown filter mesin dinamis (semua mesin jika lokasi null)
        $daftarMesin = $mesinModel->getByLokasi($lokasiName);

        $userLine = (session()->get('role') === Role::Leader->value) ? session()->get('line') : null;
        $filters = $this->buildSearchFilters($lokasiName, $userLine);

        $perPage = (int) ($this->request->getGet('per_page') ?: 15);
        $currentPage = (int) ($this->request->getGet('page_riwayat') ?: 1);

        // Semua role bisa lihat riwayat yang sudah Approved
        // Magang hanya lihat milik mereka sendiri (semua status), role lain hanya lihat Approved
        $magang_userId = (session()->get('role') === Role::Magang->value) ? (int) session()->get('user_id') : null;
        $riwayat = $transaksiModel->getRiwayatFiltered($filters, $magang_userId, null, $perPage);
        $pager = $transaksiModel->pager;
        $totalItems = $pager ? $pager->getTotal('riwayat') : 0;
        $totalPages = $pager ? $pager->getPageCount('riwayat') : 1;
        $startNo = ($currentPage - 1) * $perPage + 1;

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'html'        => view('riwayat/_rows', [
                                    'riwayat'    => $riwayat,
                                    'startNo'    => $startNo,
                                    'lokasiSlug' => $lokasiSlug
                                 ]),
                'currentPage' => $currentPage,
                'totalPages'  => $totalPages,
                'totalItems'  => $totalItems,
                'perPage'     => $perPage,
                'startNo'     => $startNo
            ]);
        }

        $categoriesList = $this->buildCategoriesList($lokasiName, $filters['jenis_check'] ?? null);
        $jenisLabel = $filters['jenis_check'] === JenisCheck::Preventive->value ? 'Checklist Report' : ($filters['jenis_check'] === JenisCheck::Overhaul->value ? 'Inspection Report' : 'Pengecekan');
        $title = "Riwayat {$jenisLabel} — " . ($lokasiName ?? 'Semua Lokasi');
        
        $availableLines = $this->buildAvailableLines($lokasiName);
        $availablePics = $this->buildAvailablePics($transaksiModel, $lokasiName, $filters['jenis_check'] ?? null);
        $bulanList = $this->buildAvailableBulan($transaksiModel, $lokasiName, $filters['jenis_check'] ?? null);

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
            'startNo'         => $startNo,
            'totalItems'      => $totalItems,
            'perPage'         => $perPage,
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

    /**
     * GET /riwayat/export-excel/(:segment)
     * Download rekap riwayat dalam format Excel
     */
    public function exportExcel(string $lokasiSlug)
    {
        $lokasiName = $this->resolveLokasi($lokasiSlug);
        $riwayatService = new \App\Services\RiwayatService();
        try {
            $lokasiName = $riwayatService->validateLeaderAccess($lokasiName);
        } catch (\Exception $e) {
            return redirect()->to('/dashboard')->with('error', $e->getMessage());
        }

        $userLine = (session()->get('role') === Role::Leader->value) ? session()->get('line') : null;
        $filters = $this->buildSearchFilters($lokasiName, $userLine);
        
        $transaksiModel = new TransaksiCheckModel();
        // Fetch all matching records (no pagination)
        $riwayat = $transaksiModel->getRiwayatFiltered($filters, null, null, null);

        $jenisLabel = $filters['jenis_check'] === JenisCheck::Preventive->value
            ? 'Preventive'
            : ($filters['jenis_check'] === JenisCheck::Overhaul->value ? 'Overhaul' : 'Pengecekan');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap ' . $jenisLabel);

        // Header Styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F81BD']
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        helper('tanggal');
        
        // Headers
        $headers = ['No', 'Tipe', 'Lokasi', 'Line', 'Nama Mesin', 'Kategori', 'Kondisi', 'PIC (Mekanik)', 'Waktu Mulai', 'Waktu Selesai', 'Status'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        $sheet->getStyle('A1:K1')->applyFromArray($headerStyle);

        // Data Rows
        $row = 2;
        $no = 1;
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
        ];

        foreach ($riwayat as $data) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $data['jenis_check'] ?? '-');
            $sheet->setCellValue('C' . $row, $data['lokasi_check'] ?? '-');
            $sheet->setCellValue('D' . $row, $data['line'] ?? '-');
            $sheet->setCellValue('E' . $row, $data['no_mesin'] ?? '-');
            $sheet->setCellValue('F' . $row, $data['kategori'] ?? '-');
            
            $kondisiExcel = 'Normal';
            if ($data['kondisi_mesin'] === 'Δ') {
                $kondisiExcel = 'Perlu Tindakan';
            } elseif ($data['kondisi_mesin'] === 'X') {
                $kondisiExcel = 'Tidak Ada';
            }
            $sheet->setCellValue('G' . $row, $kondisiExcel);
            
            $waktuMulai = !empty($data['waktu_mulai']) ? format_tanggal_indo($data['waktu_mulai'], true, true) : '-';
            $waktuSelesai = !empty($data['waktu_selesai']) ? format_tanggal_indo($data['waktu_selesai'], true, true) : '-';
            
            $sheet->setCellValue('H' . $row, $data['nama_pic'] ?? '-');
            $sheet->setCellValue('I' . $row, $waktuMulai);
            $sheet->setCellValue('J' . $row, $waktuSelesai);
            $sheet->setCellValue('K' . $row, $data['status'] ?? '-');
            $row++;
        }

        if ($row > 2) {
            $sheet->getStyle('A2:K' . ($row - 1))->applyFromArray($dataStyle);
            $sheet->getStyle('A2:A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B2:K' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // Auto size columns
        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = "Rekap_Riwayat_" . $jenisLabel . "_" . date('Ymd_His') . ".xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
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
        $filename = 'Laporan_' . ($data['header']['jenis_check'] === JenisCheck::Preventive->value ? 'Checklist' : 'Inspection') . '_' . $data['header']['no_mesin'] . '_' . date('Ymd', strtotime($data['header']['waktu_mulai'])) . '.pdf';
        $dompdf->stream($filename, ["Attachment" => 0]);
        exit();
    }

    public function edit(int $id)
    {
        if (session()->get('role') !== Role::Admin->value) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        $riwayatService = new \App\Services\RiwayatService();
        $data = $riwayatService->getEditData($id);
        if (!$data) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan.');
        }
        return view('checklist/form', $data);
    }

    public function update(int $id)
    {
        if (session()->get('role') !== Role::Admin->value) {
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
        if (session()->get('role') !== Role::Admin->value) {
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

    public function deleteApproval(int $id)
    {
        if (session()->get('role') !== \App\Enums\Role::Admin->value) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        $riwayatService = new \App\Services\RiwayatService();
        $result = $riwayatService->deleteApproval($id);
        if (!$result['status']) {
            return redirect()->back()->with('error', $result['message']);
        }
        return redirect()->back()->with('success', $result['message']);
    }

    private function buildSearchFilters(?string $lokasiName, ?string $userLine): array
    {
        $rawStatus = $this->request->getGet('status');
        if ($rawStatus === 'all') {
            $statusFilter = null;
        } elseif ($rawStatus && $rawStatus !== 'all') {
            $statusFilter = $rawStatus;
        } else {
            $role = session()->get('role');
            if ($role === Role::Leader->value) {
                $statusFilter = ['Approved L1', 'Approved L2', 'Approved', 'Approved Final'];
            } elseif ($role === Role::Sheadprd->value) {
                $statusFilter = ['Approved L2', 'Approved', 'Approved Final'];
            } elseif ($role === Role::Sheadmtc->value) { 
                $statusFilter = ['Approved', 'Approved Final'];
            } elseif ($role === Role::Magang->value) { 
                $statusFilter = null; 
            } else { 
                $statusFilter = ['Approved', 'Approved Final']; 
            }
        }
        
        return [
            'lokasi'      => $lokasiName,
            'id_mesin'    => $this->request->getGet('id_mesin') === 'all' ? null : ($this->request->getGet('id_mesin') ?: null),
            'line'        => $userLine ?: ($this->request->getGet('line') === 'all' ? null : ($this->request->getGet('line') ?: null)),
            'jenis_check' => $this->request->getGet('jenis_check') === 'all' ? null : ($this->request->getGet('jenis_check') ?: null),
            'kategori'    => $this->request->getGet('kategori') === 'all' ? null : ($this->request->getGet('kategori') ?: null),
            'bulan'       => $this->request->getGet('bulan') === 'all' ? null : ($this->request->getGet('bulan') ?: date('Y-m')),
            'status'      => $statusFilter,
            'pic'         => $this->request->getGet('pic') === 'all' ? null : ($this->request->getGet('pic') ?: null),
            'sort_by'     => $this->request->getGet('sort_by') ?: 'id_transaksi',
            'order'       => $this->request->getGet('order') ?: 'desc',
        ];
    }

    private function buildCategoriesList(?string $lokasiName, ?string $jenisCheckFilter): array
    {
        $parameterModel = new \App\Models\ParameterCheckModel();
        $jenisDb = (in_array(strtolower($jenisCheckFilter ?? ''), ['preventive', 'checklist report'])) ? JenisCheck::Preventive->value : JenisCheck::Overhaul->value;
        
        $catQuery = $parameterModel->select('kategori');
        if ($lokasiName !== null) {
            $catQuery->where('lokasi', $lokasiName);
        }
        $dbCategories = $catQuery->where('jenis_check', $jenisDb)->distinct()->findAll();

        $categoriesList = [];
        foreach ($dbCategories as $cat) {
            $slug = strtolower(str_replace(' ', '-', $cat['kategori']));
            $categoriesList[$slug] = $cat['kategori'];
        }
        return $categoriesList;
    }

    private function buildAvailableLines(?string $lokasiName): array
    {
        if ($lokasiName === Lokasi::MFG1->value) {
            return ['Line 1', 'Line 2', 'Line 3'];
        } elseif ($lokasiName === Lokasi::MFG2->value) {
            return ['CG', 'Second'];
        }
        return ['Line 1', 'Line 2', 'Line 3', 'CG', 'Second'];
    }

    private function buildAvailablePics(\App\Models\TransaksiCheckModel $transaksiModel, ?string $lokasiName, ?string $jenisCheckFilter): array
    {
        $rawPics = $transaksiModel->getAvailablePics($lokasiName, $jenisCheckFilter);
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
        return $availablePics;
    }

    private function buildAvailableBulan(\App\Models\TransaksiCheckModel $transaksiModel, ?string $lokasiName, ?string $jenisCheckFilter): array
    {
        $rawBulan = $transaksiModel->getAvailableBulan($lokasiName, $jenisCheckFilter);
        $bulanList = [];
        foreach ($rawBulan as $row) {
            $val = $row['bulan'];
            if ($val) {
                $time = \CodeIgniter\I18n\Time::parse($val . '-01');
                $label = format_bulan_indo($time->format("Y-m"));
                $bulanList[$val] = $label;
            }
        }
        $currentMonthVal = \CodeIgniter\I18n\Time::now()->format('Y-m');
        if (!isset($bulanList[$currentMonthVal])) {
            $bulanList[$currentMonthVal] = format_bulan_indo(date('Y-m'));
            krsort($bulanList);
        }
        return $bulanList;
    }
}
