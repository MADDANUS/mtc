<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\Lokasi;
use App\Enums\JenisCheck;

use App\Models\LaporanAbnormalModel;
use App\Models\MesinModel;

class AbnormalService
{
    protected LaporanAbnormalModel $abnormalModel;
    protected MesinModel $mesinModel;

    /**
     * GET /abnormal
     */
     
        public function pdf($request)
    {
        $lokasiFilter   = $request->getGet('lokasi') ?: Lokasi::MFG1->value;
        $searchFilter   = $request->getGet('search') ?: '';
        $kategoriFilter = $request->getGet('kategori') ?: 'Penerangan';
        $bulanFilter    = $request->getGet('bulan') ?: date('Y-m');

        $abnormalModel = new \App\Models\LaporanAbnormalModel();
        $reports = $abnormalModel->getPdfLaporan($lokasiFilter, $kategoriFilter, $bulanFilter, $searchFilter);

        $categories = $this->resolveKategoriList($lokasiFilter);

        if (!in_array($kategoriFilter, $categories)) {
            $kategoriFilter = 'Penerangan';
        }

        $data = [
            'title'          => 'Laporan Abnormal Condition',
            'reports'        => $reports,
            'lokasiFilter'   => $lokasiFilter,
            'searchFilter'   => $searchFilter,
            'kategoriFilter' => $kategoriFilter,
            'bulanFilter'    => $bulanFilter,
            'categories'     => $categories,
        ];

    }

    public function pdfAllCategories($request)
    {
        $lokasiFilter   = $request->getGet('lokasi') ?: Lokasi::MFG1->value;
        $searchFilter   = $request->getGet('search') ?: '';
        $bulanFilter    = $request->getGet('bulan') ?: date('Y-m');

        $categories = $this->resolveKategoriList($lokasiFilter);

        $allReportsData = [];
        
        
        foreach ($categories as $cat) {
            $abnormalModel = new \App\Models\LaporanAbnormalModel();
            $reports = $abnormalModel->getPdfAllCategoriesLaporan($lokasiFilter, $cat, $bulanFilter, $searchFilter);

            $allReportsData[] = [
                'kategori' => $cat,
                'reports'  => $reports
            ];
        }

        $data = [
            'title'          => "Laporan Abnormal - Semua Kategori - {$lokasiFilter}",
            'allReportsData' => $allReportsData,
            'lokasiFilter'   => $lokasiFilter,
            'searchFilter'   => $searchFilter,
            'bulanFilter'    => $bulanFilter
        ];

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

    }

    public function pdfAllSummary($request)
    {
        $bulanFilter = $request->getGet('bulan') ?: date('Y-m');
        $filterLokasi = $request->getGet('filter_lokasi') === 'all' ? '' : ($request->getGet('filter_lokasi') ?: '');
        $filterLine = $request->getGet('filter_line') === 'all' ? '' : ($request->getGet('filter_line') ?: '');
        $filterKategori = $request->getGet('filter_kategori') === 'all' ? '' : ($request->getGet('filter_kategori') ?: '');
        
        $lokasiList = [Lokasi::MFG1->value, Lokasi::MFG2->value];
        
        $allReportsData = [];
        
        
        foreach ($lokasiList as $lokasi) {
            if (!empty($filterLokasi) && $lokasi !== $filterLokasi) continue;
            
            $categories = $this->resolveKategoriList($lokasi);
                
            foreach ($categories as $cat) {
                if (!empty($filterKategori) && $cat !== $filterKategori) continue;
                
                $abnormalModel = new \App\Models\LaporanAbnormalModel();
                $reports = $abnormalModel->getPdfAllSummaryLaporan($lokasi, $cat, $bulanFilter, $filterLine);

                // Skip if no data
                if (empty($reports)) continue;

                $allReportsData[] = [
                    'lokasi'   => $lokasi,
                    'kategori' => $cat,
                    'reports'  => $reports
                ];
            }
        }

        $data = [
            'title'          => "Laporan Abnormal - Ringkasan Semua Area",
            'allReportsData' => $allReportsData,
            'lokasiFilter'   => 'SEMUA AREA',
            'searchFilter'   => '',
            'bulanFilter'    => $bulanFilter
        ];

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

    }

    public function index($request)
    {
        // Jika parameter view=summary atau tidak ada parameter spesifik, tampilkan halaman ringkasan
        if ($request->getGet('view') === 'summary' || (!$request->getGet('lokasi') && !$request->getGet('search') && !$request->getGet('kategori'))) {
            return $this->summary($request);
        }

        $lokasiFilter   = $request->getGet('lokasi') ?: Lokasi::MFG1->value;
        $searchFilter   = $request->getGet('search') ?: '';
        $kategoriFilter = $request->getGet('kategori') ?: 'Penerangan';
        $bulanFilter    = $request->getGet('bulan') ?: date('Y-m');

        // Build query
        
        $abnormalModel = new \App\Models\LaporanAbnormalModel();
        
        $perPage = (int) ($request->getGet('per_page') ?: 15);
        $currentPage = (int) ($request->getGet('page_abnormal') ?: 1);
        
        $reports = $abnormalModel->getIndexLaporan($lokasiFilter, $kategoriFilter, $bulanFilter, $searchFilter, $perPage);
        
        $pager = $abnormalModel->pager;
        $totalItems = $pager ? $pager->getTotal('abnormal') : 0;
        $totalPages = $pager ? $pager->getPageCount('abnormal') : 1;
        $startNo = ($currentPage - 1) * $perPage + 1;

        $categories = $this->resolveKategoriList($lokasiFilter);

        if (!in_array($kategoriFilter, $categories)) {
            $kategoriFilter = 'Penerangan';
        }

        // Buat list bulan untuk filter
        $bulanList = $this->buildBulanList();

        // Cek semua terisi
        $allPics = (new \App\Models\PicModel())->orderBy('nama_pic', 'ASC')->findAll();
        $masterPic = array_filter($allPics, function($p) {
            return strpos(strtolower(str_replace(' ', '', $p['role_pic'] ?? '')), Role::Leader->value) === false;
        });

        $responseData = [
            'title'          => 'Laporan Abnormal Condition',
            'reports'        => $reports,
            'lokasiFilter'   => $lokasiFilter,
            'searchFilter'   => $searchFilter,
            'kategoriFilter' => $kategoriFilter,
            'bulanFilter'    => $bulanFilter,
            'categories'     => $categories,
            'bulanList'      => $bulanList,
            'masterPic'      => $masterPic,
            'totalItems'     => $totalItems,
            'perPage'        => $perPage,
            'startNo'        => $startNo,
            'currentPage'    => $currentPage,
            'totalPages'     => $totalPages,
        ];
        
        if ($request->isAJAX()) {
            $responseData['html'] = view('abnormal/_rows', $responseData);
        }
        
        return $responseData;
    }

    /**
     * Halaman Ringkasan (Summary) Abnormal
     */
    public function summary($request)
    {
        $bulan = $request->getGet('bulan') ?: date('Y-m');
        $filterLokasi = $request->getGet('filter_lokasi') === 'all' ? '' : ($request->getGet('filter_lokasi') ?: '');
        $filterLine = $request->getGet('filter_line') === 'all' ? '' : ($request->getGet('filter_line') ?: '');
        $filterKategori = $request->getGet('filter_kategori') === 'all' ? '' : ($request->getGet('filter_kategori') ?: '');
        $filterStatus = $request->getGet('filter_status') === 'all' ? '' : ($request->getGet('filter_status') ?: '');
        $sortBy = $request->getGet('sort_by') ?: 'lokasi';
        $order = strtolower($request->getGet('order') ?: 'asc');

        

        // Ambil semua data master mesin
        $mesinModel = new \App\Models\MesinModel();
        $mesinQuery = $mesinModel->getTotalMesinPerLine();

        $linesByLokasi = [];
        foreach($mesinQuery as $m) {
            $linesByLokasi[$m['lokasi']][] = $m['line'];
        }

        // Hitung abnormal terbuka (belum ada action) dan total abnormal per lokasi, line, kategori
        $abnormalModel = new \App\Models\LaporanAbnormalModel();
        $allAbnormal = $abnormalModel->getDashboardSummaryAbnormal($bulan);
                           
        $abnormalData = [];
        foreach($allAbnormal as $oa) {
            $kategori = $oa['kategori'] ?: 'Penerangan'; // Default fallback
            $abnormalData[$oa['lokasi']][$oa['line']][$kategori] = [
                'totalOpen' => (int) $oa['totalOpen'],
                'totalAll'  => (int) $oa['totalAll']
            ];
        }

        $kategoriByLokasi = [
            Lokasi::MFG1->value => $this->resolveKategoriList(Lokasi::MFG1->value),
            Lokasi::MFG2->value => $this->resolveKategoriList(Lokasi::MFG2->value)
        ];

        // List bulan
        $bulanList = $this->buildBulanList();

        // Build flat array for summary rows
        $summaryRows = [];
        foreach ($kategoriByLokasi as $lokasi => $categories) {
            if (!empty($filterLokasi) && $lokasi !== $filterLokasi) continue;
            
            $lines = isset($linesByLokasi[$lokasi]) ? array_unique($linesByLokasi[$lokasi]) : [];
            sort($lines);

            foreach ($lines as $line) {
                if (!empty($filterLine) && $line !== $filterLine) continue;

                foreach ($categories as $kategori) {
                    if (!empty($filterKategori) && $kategori !== $filterKategori) continue;
                    
                    $abData = $abnormalData[$lokasi][$line][$kategori] ?? ['totalOpen' => 0, 'totalAll' => 0];
                    $totalOpen = $abData['totalOpen'];
                    $totalAll  = $abData['totalAll'];
                    
                    if ($totalAll == 0) continue;
                    
                    if ($totalOpen > 0) {
                        $badgeClass = 'bg-danger';
                        $statusText = 'Belum Perbaikan';
                    } else {
                        $badgeClass = 'bg-success';
                        $statusText = 'Sudah Perbaikan';
                    }
                    
                    if (!empty($filterStatus) && $statusText !== $filterStatus) continue;

                    $summaryRows[] = [
                        'lokasi'      => $lokasi,
                        'line'        => $line,
                        'kategori'    => $kategori,
                        'totalOpen'   => $totalOpen,
                        'statusText'  => $statusText,
                        'badgeClass'  => $badgeClass
                    ];
                }
            }
        }

        // Sort the flat array
        usort($summaryRows, function($a, $b) use ($sortBy, $order) {
            $valA = $a[$sortBy] ?? '';
            $valB = $b[$sortBy] ?? '';
            
            if ($valA == $valB) return 0;
            
            $cmp = ($valA < $valB) ? -1 : 1;
            return ($order === 'asc') ? $cmp : -$cmp;
        });

        // Determine available lines and categories for the dropdowns
        $availableLines = [];
        $availableCategories = [];
        if (!empty($filterLokasi)) {
            $availableLines = isset($linesByLokasi[$filterLokasi]) ? array_unique($linesByLokasi[$filterLokasi]) : [];
            $availableCategories = isset($kategoriByLokasi[$filterLokasi]) ? array_unique($kategoriByLokasi[$filterLokasi]) : [];
        } else {
            foreach ($linesByLokasi as $lines) {
                $availableLines = array_merge($availableLines, $lines);
            }
            $availableLines = array_unique($availableLines);
            foreach ($kategoriByLokasi as $cats) {
                $availableCategories = array_merge($availableCategories, $cats);
            }
            $availableCategories = array_unique($availableCategories);
        }
        sort($availableLines);
        sort($availableCategories);

        return [
            'is_summary'       => true,
            'title'            => 'Ringkasan Laporan Abnormal',
            'bulan'            => $bulan,
            'bulanList'        => $bulanList,
            'summaryRows'      => $summaryRows,
            'filterLokasi'     => $filterLokasi,
            'filterLine'       => $filterLine,
            'filterKategori'   => $filterKategori,
            'filterStatus'     => $filterStatus,
            'sortBy'           => $sortBy,
            'order'            => $order,
            'availableLines'   => $availableLines,
            'availableCategories'=> $availableCategories,
        ];
    }

    /**
     * POST /abnormal/update
     */
    public function update($request)
    {
        $idAbnormal = (int) $request->getPost('id_abnormal');
        if ($idAbnormal <= 0) {
            return ["status" => false, "message" => 'Laporan Abnormal tidak valid.'];
        }

        $data = [
            'type_sparepart'  => $request->getPost('type_sparepart') ?: null,
            'progres_stock'   => $request->getPost('progres_stock') ?: null,
            'progres_tanggal' => $request->getPost('progres_tanggal') ?: null,
            'action'          => $request->getPost('action') ?: null,
            'repair_pic'      => $request->getPost('repair_pic') ?: null,
            'keterangan'      => $request->getPost('keterangan') ?: null,
        ];
        
        $isHapusSemua = $request->getPost('hapus_semua') == '1';
        if ($isHapusSemua) {
            $existing = (new \App\Models\LaporanAbnormalModel())->find($idAbnormal);
            if ($existing) {
                if (!empty($existing['foto_perbaikan'])) {
                    @unlink(FCPATH . 'uploads/abnormal/' . $existing['foto_perbaikan']);
                    $data['foto_perbaikan'] = null;
                }
                if (!empty($existing['foto_perbaikan_2'])) {
                    @unlink(FCPATH . 'uploads/abnormal/' . $existing['foto_perbaikan_2']);
                    $data['foto_perbaikan_2'] = null;
                }
            }
        }

        // Handle foto_perbaikan
        $file1 = $request->getFile('foto_perbaikan');
        if ($file1 && $file1->isValid() && !$file1->hasMoved()) {
            $newName = 'repair_' . time() . '_1_' . uniqid() . '.' . $file1->getClientExtension();
            $file1->move(FCPATH . 'uploads/abnormal/', $newName);
            $data['foto_perbaikan'] = $newName;
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        // Handle foto_perbaikan_2
        $file2 = $request->getFile('foto_perbaikan_2');
        if ($file2 && $file2->isValid() && !$file2->hasMoved()) {
            $newName = 'repair_' . time() . '_2_' . uniqid() . '.' . $file2->getClientExtension();
            $file2->move(FCPATH . 'uploads/abnormal/', $newName);
            $data['foto_perbaikan_2'] = $newName;
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        if ((new \App\Models\LaporanAbnormalModel())->update($idAbnormal, $data)) {
            return ["status" => true, "message" => 'Rencana perbaikan Laporan Abnormal berhasil diperbarui.'];
        }

        return ["status" => false, "message" => 'Gagal memperbarui Laporan Abnormal.'];
    }

    /**
     * GET /abnormal/overhaul
     */
    public function overhaul($request)
    {
        $viewType     = $request->getGet('view') ?: 'list';
        if ($viewType === 'summary') {
            return $this->summaryOverhaul($request);
        }

        $lokasiFilter = $request->getGet('lokasi') ?: Lokasi::MFG1->value;
        $searchFilter = $request->getGet('search') ?: '';
        $bulanFilter  = $request->getGet('bulan') ?: date('Y-m');

        
        $abnormalModel = new \App\Models\LaporanAbnormalModel();
        
        $perPage = (int) ($request->getGet('per_page') ?: 15);
        $currentPage = (int) ($request->getGet('page_abnormal_overhaul') ?: 1);

        $reports = $abnormalModel->getOverhaulLaporan($lokasiFilter, $bulanFilter, $searchFilter, $perPage);

        $pager = $abnormalModel->pager;
        $totalItems = $pager ? $pager->getTotal('abnormal_overhaul') : 0;
        $totalPages = $pager ? $pager->getPageCount('abnormal_overhaul') : 1;
        $startNo = ($currentPage - 1) * $perPage + 1;

        $allPics2 = (new \App\Models\PicModel())->orderBy('nama_pic', 'ASC')->findAll();
        $masterPic = array_filter($allPics2, function($p) {
            return strpos(strtolower(str_replace(' ', '', $p['role_pic'] ?? '')), Role::Leader->value) === false;
        });

        $bulanList = $this->buildBulanList();

        $data = [
            'title'          => 'Laporan Abnormal Overhaul',
            'reports'        => $reports,
            'lokasiFilter'   => $lokasiFilter,
            'searchFilter'   => $searchFilter,
            'bulanFilter'    => $bulanFilter,
            'masterPic'      => $masterPic,
            'bulanList'      => $bulanList,
            'totalItems'     => $totalItems,
            'perPage'        => $perPage,
            'startNo'        => $startNo,
            'currentPage'    => $currentPage,
            'totalPages'     => $totalPages,
        ];
        
        if ($request->isAJAX()) {
            $data['html'] = view('abnormal/_rows_overhaul', $data);
        }

        return $data;
    }

    protected function summaryOverhaul()
    {
        $bulanFilter    = $request->getGet('bulan') ?: date('Y-m');
        $filterLokasi   = $request->getGet('filter_lokasi') ?: '';
        $filterLine     = $request->getGet('filter_line') ?: '';
        $filterStatus   = $request->getGet('filter_status') ?: '';
        $sortBy         = $request->getGet('sort_by') ?: 'lokasi';
        $order          = $request->getGet('order') ?: 'asc';

        
        
        $linesByLokasi = [
            Lokasi::MFG1->value => ['Brother', 'Milling', 'Kasahara', 'Knurling', 'Osl', 'Centering Grinding', 'Double Milling', 'Double Center Drill'],
            Lokasi::MFG2->value => ['Brother', 'Osl', 'Kasahara', 'Buffing', 'Thread', 'Burnishing']
        ];

        $bulan = date('Y-m');
        if (!empty($bulanFilter)) {
            $bulan = $bulanFilter;
        }

        $builder = $db->table('laporan_abnormal')
                      ->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                      ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                      ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left');
                      
        $builder->where('transaksi_check.jenis_check', JenisCheck::Overhaul->value);

        if (!empty($bulan)) {
            $builder->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after');
        }

        $reports = $builder->get()->getResultArray();

        $abnormalData = [];
        foreach ($reports as $r) {
            $lokasi = trim($r['lokasi']);
            $line = trim($r['type_mesin']);
            
            if (!isset($abnormalData[$lokasi])) $abnormalData[$lokasi] = [];
            if (!isset($abnormalData[$lokasi][$line])) $abnormalData[$lokasi][$line] = ['totalOpen' => 0, 'totalAll' => 0];

            $abnormalData[$lokasi][$line]['totalAll']++;
            if (empty($r['action'])) {
                $abnormalData[$lokasi][$line]['totalOpen']++;
            }
        }

        $bulanList = [];
        $bulanIndo = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
        for ($i = 0; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $val  = $time->format('Y-m');
            $label = $bulanIndo[$time->format('m')] . ' ' . $time->format('Y');
            $bulanList[$val] = $label;
        }

        $summaryRows = [];
        foreach ($linesByLokasi as $lokasi => $lines) {
            if (!empty($filterLokasi) && $lokasi !== $filterLokasi) continue;
            
            foreach ($lines as $line) {
                if (!empty($filterLine) && $line !== $filterLine) continue;

                $abData = $abnormalData[$lokasi][$line] ?? ['totalOpen' => 0, 'totalAll' => 0];
                $totalOpen = $abData['totalOpen'];
                $totalAll  = $abData['totalAll'];
                
                if ($totalAll == 0) continue;
                
                if ($totalOpen > 0) {
                    $badgeClass = 'bg-danger';
                    $statusText = 'Belum Perbaikan';
                } else {
                    $badgeClass = 'bg-success';
                    $statusText = 'Sudah Perbaikan';
                }
                
                if (!empty($filterStatus) && $statusText !== $filterStatus) continue;

                $summaryRows[] = [
                    'lokasi'      => $lokasi,
                    'line'        => $line,
                    'totalOpen'   => $totalOpen,
                    'statusText'  => $statusText,
                    'badgeClass'  => $badgeClass
                ];
            }
        }

        usort($summaryRows, function($a, $b) use ($sortBy, $order) {
            $valA = $a[$sortBy] ?? '';
            $valB = $b[$sortBy] ?? '';
            
            if ($valA == $valB) return 0;
            
            $cmp = ($valA < $valB) ? -1 : 1;
            return ($order === 'asc') ? $cmp : -$cmp;
        });

        $availableLines = [];
        if (!empty($filterLokasi)) {
            $availableLines = isset($linesByLokasi[$filterLokasi]) ? array_unique($linesByLokasi[$filterLokasi]) : [];
        } else {
            foreach ($linesByLokasi as $lines) {
                $availableLines = array_merge($availableLines, $lines);
            }
            $availableLines = array_unique($availableLines);
        }
        sort($availableLines);

        return view('abnormal/summary_overhaul', [
            'is_summary'       => true,
            'title'            => 'Ringkasan Laporan Abnormal Overhaul',
            'bulan'            => $bulan,
            'bulanList'        => $bulanList,
            'summaryRows'      => $summaryRows,
            'filterLokasi'     => $filterLokasi,
            'filterLine'       => $filterLine,
            'filterStatus'     => $filterStatus,
            'sortBy'           => $sortBy,
            'order'            => $order,
            'availableLines'   => $availableLines,
        ]);
    }

    public function pdfOverhaul($request)
    {
        $lokasiFilter   = $request->getGet('lokasi') ?: Lokasi::MFG1->value;
        $searchFilter   = $request->getGet('search') ?: '';
        $bulanFilter    = $request->getGet('bulan') ?: date('Y-m');

        
        $builder = $db->table('laporan_abnormal')
                      ->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                      ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                      ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                      ->where('transaksi_check.jenis_check', JenisCheck::Overhaul->value);

        if (!empty($lokasiFilter) && $lokasiFilter !== 'all') {
            $builder->where('master_mesin.lokasi', $lokasiFilter);
        }

        if (!empty($bulanFilter) && $bulanFilter !== 'all') {
            $builder->like('laporan_abnormal.pengecekan_tanggal', $bulanFilter . '-', 'after');
        }

        if (!empty($searchFilter)) {
            $builder->groupStart()
                    ->like('laporan_abnormal.point_check', $searchFilter)
                    ->orLike('laporan_abnormal.abnormal_condition', $searchFilter)
                    ->orLike('master_mesin.no_mesin', $searchFilter)
                    ->orLike('master_mesin.type_mesin', $searchFilter)
                    ->groupEnd();
        }

        $reports = $builder->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                           ->orderBy('laporan_abnormal.id_abnormal', 'DESC')
                           ->get()
                           ->getResultArray();

        $data = [
            'title'          => 'Laporan Abnormal Overhaul',
            'reports'        => $reports,
            'lokasiFilter'   => $lokasiFilter,
            'searchFilter'   => $searchFilter,
            'bulanFilter'    => $bulanFilter,
            'isOverhaul'     => true,
            'kategoriFilter' => JenisCheck::Overhaul->value // Dummy untuk file PDF jika diperlukan
        ];

    }

    public function pdfAllSummaryOverhaul($request)
    {
        $bulanFilter = $request->getGet('bulan') ?: date('Y-m');
        
        
        $linesByLokasi = [
            Lokasi::MFG1->value => ['Brother', 'Milling', 'Kasahara', 'Knurling', 'Osl', 'Centering Grinding', 'Double Milling', 'Double Center Drill'],
            Lokasi::MFG2->value => ['Brother', 'Osl', 'Kasahara', 'Buffing', 'Thread', 'Burnishing']
        ];
        
        $allData = [];
        
        foreach ([Lokasi::MFG1->value, Lokasi::MFG2->value] as $lokasi) {
            $abnormalModel = new \App\Models\LaporanAbnormalModel();
            $reports = $abnormalModel->getOverhaulDashboardSummaryLaporan($lokasi, $bulanFilter);
            
            $abnormalData = [];
            foreach ($reports as $r) {
                $line = trim($r['type_mesin']);
                if (!isset($abnormalData[$line])) $abnormalData[$line] = ['totalOpen' => 0, 'totalAll' => 0];
                $abnormalData[$line]['totalAll']++;
                if (empty($r['action'])) {
                    $abnormalData[$line]['totalOpen']++;
                }
            }
            
            $summaryRows = [];
            $lines = $linesByLokasi[$lokasi] ?? [];
            foreach ($lines as $line) {
                $abData = $abnormalData[$line] ?? ['totalOpen' => 0, 'totalAll' => 0];
                $totalOpen = $abData['totalOpen'];
                $totalAll  = $abData['totalAll'];
                
                if ($totalAll == 0) continue;
                
                $summaryRows[] = [
                    'line'        => $line,
                    'totalOpen'   => $totalOpen,
                    'totalAll'    => $totalAll
                ];
            }
            $allData[$lokasi] = $summaryRows;
        }

        $abnormalModel = new \App\Models\LaporanAbnormalModel();
        $allAbnormal = $abnormalModel->getOverhaulDashboardSummaryTotals($bulanFilter);
                             
        $totalAllAbnormal = $allAbnormal['totalAll'] ?? 0;
        $totalOpenAbnormal = $allAbnormal['totalOpen'] ?? 0;
        $totalCloseAbnormal = $totalAllAbnormal - $totalOpenAbnormal;
        $achievement = $totalAllAbnormal > 0 ? round(($totalCloseAbnormal / $totalAllAbnormal) * 100) : 100;

        $data = [
            'bulanFilter' => $bulanFilter,
            'allData' => $allData,
            'totalAllAbnormal' => $totalAllAbnormal,
            'totalOpenAbnormal' => $totalOpenAbnormal,
            'totalCloseAbnormal' => $totalCloseAbnormal,
            'achievement' => $achievement,
            'isOverhaul' => true
        ];

    }

    public function updateOverhaul($request)
    {
        $idAbnormal = (int) $request->getPost('id_abnormal');
        if ($idAbnormal <= 0) {
            return ["status" => false, "message" => 'Laporan Abnormal tidak valid.'];
        }

        $data = [
            'type_sparepart'  => $request->getPost('type_sparepart') ?: null,
            'progres_stock'   => $request->getPost('progres_stock') ?: null,
            'progres_tanggal' => $request->getPost('progres_tanggal') ?: null,
            'action'          => $request->getPost('action') ?: null,
            'repair_pic'      => $request->getPost('repair_pic') ?: null,
            'keterangan'      => $request->getPost('keterangan') ?: null,
        ];
        
        $isHapusSemua = $request->getPost('hapus_semua') == '1';
        if ($isHapusSemua) {
            $existing = (new \App\Models\LaporanAbnormalModel())->find($idAbnormal);
            if ($existing) {
                if (!empty($existing['foto_perbaikan'])) {
                    @unlink(FCPATH . 'uploads/abnormal/' . $existing['foto_perbaikan']);
                    $data['foto_perbaikan'] = null;
                }
                if (!empty($existing['foto_perbaikan_2'])) {
                    @unlink(FCPATH . 'uploads/abnormal/' . $existing['foto_perbaikan_2']);
                    $data['foto_perbaikan_2'] = null;
                }
            }
        }

        // Handle foto_perbaikan
        $file1 = $request->getFile('foto_perbaikan');
        if ($file1 && $file1->isValid() && !$file1->hasMoved()) {
            $newName = 'repair_' . time() . '_1_' . uniqid() . '.' . $file1->getClientExtension();
            $file1->move(FCPATH . 'uploads/abnormal/', $newName);
            $data['foto_perbaikan'] = $newName;
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        // Handle foto_perbaikan_2
        $file2 = $request->getFile('foto_perbaikan_2');
        if ($file2 && $file2->isValid() && !$file2->hasMoved()) {
            $newName = 'repair_' . time() . '_2_' . uniqid() . '.' . $file2->getClientExtension();
            $file2->move(FCPATH . 'uploads/abnormal/', $newName);
            $data['foto_perbaikan_2'] = $newName;
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        if ((new \App\Models\LaporanAbnormalModel())->update($idAbnormal, $data)) {
            return ["status" => true, "message" => 'Rencana perbaikan Laporan Abnormal Overhaul berhasil diperbarui.'];
        }

        return ["status" => false, "message" => 'Gagal memperbarui Laporan Abnormal Overhaul.'];
    }

    /**
     * POST /abnormal/upload-foto-perbaikan
     * Upload foto setelah perbaikan (AJAX, JSON response)
     */
    public function uploadFotoPerbaikan($request)
    {
        $idAbnormal = (int) $request->getPost('id_abnormal');
        $slot = (int) $request->getPost('foto_slot') ?: 1;
        $dbColumn = ($slot === 2) ? 'foto_perbaikan_2' : 'foto_perbaikan';
        
        if ($idAbnormal <= 0) {
            return ['success' => false, 'message' => 'ID tidak valid.'];
        }

        $file = $request->getFile('foto_perbaikan');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return ['success' => false, 'message' => 'File tidak valid.'];
        }

        // Validasi tipe file (gambar saja)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return ['success' => false, 'message' => 'Hanya file gambar yang diizinkan (jpg, png, gif, webp).'];
        }

        $uploadPath = FCPATH . 'uploads/abnormal/';
        $newName = 'repair_' . time() . '_' . $slot . '_' . uniqid() . '.' . $file->getClientExtension();
        $file->move($uploadPath, $newName);

        // Simpan nama file ke laporan_abnormal
        $existing = (new \App\Models\LaporanAbnormalModel())->find($idAbnormal);
        
        // Hapus foto lama jika ada
        if (!empty($existing[$dbColumn])) {
            $oldPath = $uploadPath . $existing[$dbColumn];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        if ((new \App\Models\LaporanAbnormalModel())->update($idAbnormal, [$dbColumn => $newName, 'updated_at' => date('Y-m-d H:i:s')])) {
            return [
                'success'   => true,
                'message'   => 'Foto perbaikan ' . $slot . ' berhasil diupload.',
                'foto_url'  => base_url('uploads/abnormal/' . $newName),
                'foto_name' => $newName,
                'slot'      => $slot,
            ];
        }

        return ['success' => false, 'message' => 'Gagal menyimpan foto.'];
    }

    /**
     * POST /abnormal/delete-foto-perbaikan
     * Hapus foto setelah perbaikan (AJAX, JSON response)
     */
    public function deleteFotoPerbaikan($request)
    {
        $idAbnormal = (int) $request->getPost('id_abnormal');
        $slot = (int) $request->getPost('foto_slot') ?: 1;
        $dbColumn = ($slot === 2) ? 'foto_perbaikan_2' : 'foto_perbaikan';
        
        if ($idAbnormal <= 0) {
            return ['success' => false, 'message' => 'ID tidak valid.'];
        }
        
        $existing = (new \App\Models\LaporanAbnormalModel())->find($idAbnormal);
        if (!$existing) {
            return ['success' => false, 'message' => 'Data tidak ditemukan.'];
        }
        
        if (!empty($existing[$dbColumn])) {
            $oldPath = FCPATH . 'uploads/abnormal/' . $existing[$dbColumn];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
            (new \App\Models\LaporanAbnormalModel())->update($idAbnormal, [$dbColumn => null, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        
        return ['success' => true, 'message' => 'Foto berhasil dihapus.'];
    }

    /**
     * Membangun daftar 12 bulan terakhir untuk dropdown filter.
     * 
     * @return array Array dengan format ['Y-m' => 'NamaBulan YYYY']
     */
    private function buildBulanList(): array
    {
        $bulanList = [];
        $bulanIndo = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];
        
        for ($i = 0; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $val  = $time->format('Y-m');
            $label = $bulanIndo[$time->format('m')] . ' ' . $time->format('Y');
            $bulanList[$val] = $label;
        }
        
        return $bulanList;
    }

    /**
     * Menentukan daftar kategori berdasarkan lokasi.
     *
     * @param string $lokasi Lokasi (MFG1/MFG2)
     * @return array Daftar kategori
     */
    private function resolveKategoriList(string $lokasi): array
    {
        if ($lokasi === Lokasi::MFG2->value) {
            return ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'];
        }
        
        return ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'];
    }
}
