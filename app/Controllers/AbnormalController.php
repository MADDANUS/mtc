<?php

namespace App\Controllers;

use App\Models\LaporanAbnormalModel;
use App\Models\MesinModel;

class AbnormalController extends BaseController
{
    protected LaporanAbnormalModel $abnormalModel;
    protected MesinModel $mesinModel;

    public function __construct()
    {
        $this->abnormalModel = new LaporanAbnormalModel();
        $this->mesinModel    = new MesinModel();
    }

    /**
     * GET /abnormal
     */
     

        public function pdf()
    {
        $lokasiFilter   = $this->request->getGet('lokasi') ?: 'MFG 1';
        $searchFilter   = $this->request->getGet('search') ?: '';
        $kategoriFilter = $this->request->getGet('kategori') ?: 'Penerangan';
        $bulanFilter    = $this->request->getGet('bulan') ?: date('Y-m');

        $db = \Config\Database::connect();
        $builder = $db->table('laporan_abnormal')
                      ->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                      ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                      ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left');

        if (!empty($lokasiFilter)) {
            $builder->where('master_mesin.lokasi', $lokasiFilter);
        }

        if (!empty($kategoriFilter)) {
            $builder->where('transaksi_check.kategori', $kategoriFilter);
        }

        if (!empty($bulanFilter)) {
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

        if ($lokasiFilter === 'MFG 2') {
            $categories = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'];
        } else {
            $categories = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'];
        }

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

        $html = view('abnormal/pdf', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_' . str_replace(' ', '_', $kategoriFilter) . '_' . str_replace(' ', '_', $lokasiFilter) . '.pdf', ['Attachment' => true]);
        return;
    }

    public function pdfAllCategories()
    {
        $lokasiFilter   = $this->request->getGet('lokasi') ?: 'MFG 1';
        $searchFilter   = $this->request->getGet('search') ?: '';
        $bulanFilter    = $this->request->getGet('bulan') ?: date('Y-m');

        if ($lokasiFilter === 'MFG 2') {
            $categories = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'];
        } else {
            $categories = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'];
        }

        $allReportsData = [];
        $db = \Config\Database::connect();
        
        foreach ($categories as $cat) {
            $builder = $db->table('laporan_abnormal')
                          ->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                          ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                          ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                          ->where('master_mesin.lokasi', $lokasiFilter)
                          ->where('transaksi_check.kategori', $cat);

            if (!empty($bulanFilter)) {
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

        $html = view('abnormal/pdf_all', $data);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Laporan_Abnormal_Semua_Kategori_' . str_replace(' ', '_', $lokasiFilter) . '_' . $bulanFilter . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        return;
    }

    public function pdfAllSummary()
    {
        $bulanFilter = $this->request->getGet('bulan') ?: date('Y-m');
        $filterLokasi = $this->request->getGet('filter_lokasi') === 'all' ? '' : ($this->request->getGet('filter_lokasi') ?: '');
        $filterLine = $this->request->getGet('filter_line') === 'all' ? '' : ($this->request->getGet('filter_line') ?: '');
        $filterKategori = $this->request->getGet('filter_kategori') === 'all' ? '' : ($this->request->getGet('filter_kategori') ?: '');
        
        $lokasiList = ['MFG 1', 'MFG 2'];
        
        $allReportsData = [];
        $db = \Config\Database::connect();
        
        foreach ($lokasiList as $lokasi) {
            if (!empty($filterLokasi) && $lokasi !== $filterLokasi) continue;
            
            $categories = ($lokasi === 'MFG 2') 
                ? ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'] 
                : ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'];
                
            foreach ($categories as $cat) {
                if (!empty($filterKategori) && $cat !== $filterKategori) continue;
                
                $builder = $db->table('laporan_abnormal')
                              ->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                              ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                              ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                              ->where('master_mesin.lokasi', $lokasi)
                              ->where('transaksi_check.kategori', $cat);

                if (!empty($bulanFilter)) {
                    $builder->like('laporan_abnormal.pengecekan_tanggal', $bulanFilter . '-', 'after');
                }

                if (!empty($filterLine)) {
                    $builder->where('master_mesin.line', $filterLine);
                }

                $reports = $builder->orderBy('laporan_abnormal.pengecekan_tanggal', 'DESC')
                                   ->orderBy('laporan_abnormal.id_abnormal', 'DESC')
                                   ->get()
                                   ->getResultArray();

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

        $html = view('abnormal/pdf_all', $data);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'Laporan_Abnormal_Ringkasan_Semua_Area_' . $bulanFilter . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        return;
    }

    public function index()
    {
        // Jika parameter view=summary atau tidak ada parameter spesifik, tampilkan halaman ringkasan
        if ($this->request->getGet('view') === 'summary' || (!$this->request->getGet('lokasi') && !$this->request->getGet('search') && !$this->request->getGet('kategori'))) {
            return $this->summary();
        }

        $lokasiFilter   = $this->request->getGet('lokasi') ?: 'MFG 1';
        $searchFilter   = $this->request->getGet('search') ?: '';
        $kategoriFilter = $this->request->getGet('kategori') ?: 'Penerangan';
        $bulanFilter    = $this->request->getGet('bulan') ?: date('Y-m');

        // Build query
        $db = \Config\Database::connect();
        $builder = $db->table('laporan_abnormal')
                      ->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                      ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                      ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left');

        if (!empty($lokasiFilter)) {
            $builder->where('master_mesin.lokasi', $lokasiFilter);
        }

        if (!empty($kategoriFilter)) {
            $builder->where('transaksi_check.kategori', $kategoriFilter);
        }

        if (!empty($bulanFilter)) {
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

        if ($lokasiFilter === 'MFG 2') {
            $categories = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor'];
        } else {
            $categories = ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'];
        }

        if (!in_array($kategoriFilter, $categories)) {
            $kategoriFilter = 'Penerangan';
        }

        // Buat list bulan untuk filter
        $bulanList = [];
        $bulanIndo = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
        for ($i = 0; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $val  = $time->format('Y-m');
            $label = $bulanIndo[$time->format('m')] . ' ' . $time->format('Y');
            $bulanList[$val] = $label;
        }

        // Cek semua terisi
        $allFilled = true;
        foreach ($reports as $r) {
            if (empty($r['action'])) {
                $allFilled = false;
                break;
            }
        }

        $allPics = (new \App\Models\PicModel())->orderBy('nama_pic', 'ASC')->findAll();
        $masterPic = array_filter($allPics, function($p) {
            return strpos(strtolower(str_replace(' ', '', $p['role_pic'] ?? '')), 'leader') === false;
        });

        return view('abnormal/index', [
            'title'          => 'Laporan Abnormal Condition',
            'reports'        => $reports,
            'lokasiFilter'   => $lokasiFilter,
            'searchFilter'   => $searchFilter,
            'kategoriFilter' => $kategoriFilter,
            'bulanFilter'    => $bulanFilter,
            'categories'     => $categories,
            'bulanList'      => $bulanList,
            'allFilled'      => $allFilled,
            'masterPic'      => $masterPic,
        ]);
    }

    /**
     * Halaman Ringkasan (Summary) Abnormal
     */
    private function summary()
    {
        $bulan = $this->request->getGet('bulan') ?: date('Y-m');
        $filterLokasi = $this->request->getGet('filter_lokasi') === 'all' ? '' : ($this->request->getGet('filter_lokasi') ?: '');
        $filterLine = $this->request->getGet('filter_line') === 'all' ? '' : ($this->request->getGet('filter_line') ?: '');
        $filterKategori = $this->request->getGet('filter_kategori') === 'all' ? '' : ($this->request->getGet('filter_kategori') ?: '');
        $filterStatus = $this->request->getGet('filter_status') === 'all' ? '' : ($this->request->getGet('filter_status') ?: '');
        $sortBy = $this->request->getGet('sort_by') ?: 'lokasi';
        $order = strtolower($this->request->getGet('order') ?: 'asc');

        $db = \Config\Database::connect();

        // Ambil semua data master mesin
        $mesinQuery = $db->table('master_mesin')
                         ->select('lokasi, line')
                         ->groupBy('lokasi, line')
                         ->get()->getResultArray();

        $linesByLokasi = [];
        foreach($mesinQuery as $m) {
            $linesByLokasi[$m['lokasi']][] = $m['line'];
        }

        // Hitung abnormal terbuka (belum ada action) dan total abnormal per lokasi, line, kategori
        $allAbnormal = $db->table('laporan_abnormal')
                           ->select('master_mesin.lokasi, master_mesin.line, transaksi_check.kategori, 
                                     SUM(CASE WHEN laporan_abnormal.action IS NULL OR laporan_abnormal.action = \'\' THEN 1 ELSE 0 END) as totalOpen,
                                     COUNT(laporan_abnormal.id_abnormal) as totalAll')
                           ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                           ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                           ->like('laporan_abnormal.pengecekan_tanggal', $bulan . '-', 'after')
                           ->groupBy('master_mesin.lokasi, master_mesin.line, transaksi_check.kategori')
                           ->get()->getResultArray();
                           
        $abnormalData = [];
        foreach($allAbnormal as $oa) {
            $kategori = $oa['kategori'] ?: 'Penerangan'; // Default fallback
            $abnormalData[$oa['lokasi']][$oa['line']][$kategori] = [
                'totalOpen' => (int) $oa['totalOpen'],
                'totalAll'  => (int) $oa['totalAll']
            ];
        }

        $kategoriByLokasi = [
            'MFG 1' => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor', 'Bearing Cam', 'Gearbox', 'Belt Cam'],
            'MFG 2' => ['Penerangan', 'Kabel dan Pipa', 'Angin Bocor']
        ];

        // List bulan
        $bulanList = [];
        $bulanIndo = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
        for ($i = 0; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $val  = $time->format('Y-m');
            $label = $bulanIndo[$time->format('m')] . ' ' . $time->format('Y');
            $bulanList[$val] = $label;
        }

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

        return view('abnormal/summary', [
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
        ]);
    }

    /**
     * POST /abnormal/update
     */
    public function update()
    {
        $idAbnormal = (int) $this->request->getPost('id_abnormal');
        if ($idAbnormal <= 0) {
            return redirect()->back()->with('error', 'Laporan Abnormal tidak valid.');
        }

        $data = [
            'type_sparepart'  => $this->request->getPost('type_sparepart') ?: null,
            'progres_stock'   => $this->request->getPost('progres_stock') ?: null,
            'progres_tanggal' => $this->request->getPost('progres_tanggal') ?: null,
            'action'          => $this->request->getPost('action') ?: null,
            'repair_pic'      => $this->request->getPost('repair_pic') ?: null,
            'keterangan'      => $this->request->getPost('keterangan') ?: null,
        ];
        
        $isHapusSemua = $this->request->getPost('hapus_semua') == '1';
        if ($isHapusSemua) {
            $existing = $this->abnormalModel->find($idAbnormal);
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
        $file1 = $this->request->getFile('foto_perbaikan');
        if ($file1 && $file1->isValid() && !$file1->hasMoved()) {
            $newName = 'repair_' . time() . '_1_' . uniqid() . '.' . $file1->getClientExtension();
            $file1->move(FCPATH . 'uploads/abnormal/', $newName);
            $data['foto_perbaikan'] = $newName;
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        // Handle foto_perbaikan_2
        $file2 = $this->request->getFile('foto_perbaikan_2');
        if ($file2 && $file2->isValid() && !$file2->hasMoved()) {
            $newName = 'repair_' . time() . '_2_' . uniqid() . '.' . $file2->getClientExtension();
            $file2->move(FCPATH . 'uploads/abnormal/', $newName);
            $data['foto_perbaikan_2'] = $newName;
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        if ($this->abnormalModel->update($idAbnormal, $data)) {
            return redirect()->to('/abnormal')->with('success', 'Rencana perbaikan Laporan Abnormal berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui Laporan Abnormal.');
    }

    /**
     * GET /abnormal/overhaul
     */
    public function overhaul()
    {
        $viewType     = $this->request->getGet('view') ?: 'list';
        if ($viewType === 'summary') {
            return $this->summaryOverhaul();
        }

        $lokasiFilter = $this->request->getGet('lokasi') ?: 'MFG 1';
        $searchFilter = $this->request->getGet('search') ?: '';
        $bulanFilter  = $this->request->getGet('bulan') ?: date('Y-m');

        $db = \Config\Database::connect();
        $builder = $db->table('laporan_abnormal')
                      ->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                      ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                      ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left');
                      
        $builder->where('transaksi_check.jenis_check', 'Overhaul');

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

        $allPics2 = (new \App\Models\PicModel())->orderBy('nama_pic', 'ASC')->findAll();
        $masterPic = array_filter($allPics2, function($p) {
            return strpos(strtolower(str_replace(' ', '', $p['role_pic'] ?? '')), 'leader') === false;
        });

        $bulanList = [];
        $bulanIndo = ['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'];
        for ($i = 0; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $val  = $time->format('Y-m');
            $label = $bulanIndo[$time->format('m')] . ' ' . $time->format('Y');
            $bulanList[$val] = $label;
        }

        $data = [
            'title'          => 'Laporan Abnormal Overhaul',
            'reports'        => $reports,
            'lokasiFilter'   => $lokasiFilter,
            'searchFilter'   => $searchFilter,
            'bulanFilter'    => $bulanFilter,
            'masterPic'      => $masterPic,
            'bulanList'      => $bulanList,
        ];

        return view('abnormal/index_overhaul', $data);
    }

    protected function summaryOverhaul()
    {
        $bulanFilter    = $this->request->getGet('bulan') ?: date('Y-m');
        $filterLokasi   = $this->request->getGet('filter_lokasi') ?: '';
        $filterLine     = $this->request->getGet('filter_line') ?: '';
        $filterStatus   = $this->request->getGet('filter_status') ?: '';
        $sortBy         = $this->request->getGet('sort_by') ?: 'lokasi';
        $order          = $this->request->getGet('order') ?: 'asc';

        $db = \Config\Database::connect();
        
        $linesByLokasi = [
            'MFG 1' => ['Brother', 'Milling', 'Kasahara', 'Knurling', 'Osl', 'Centering Grinding', 'Double Milling', 'Double Center Drill'],
            'MFG 2' => ['Brother', 'Osl', 'Kasahara', 'Buffing', 'Thread', 'Burnishing']
        ];

        $bulan = date('Y-m');
        if (!empty($bulanFilter)) {
            $bulan = $bulanFilter;
        }

        $builder = $db->table('laporan_abnormal')
                      ->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                      ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                      ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left');
                      
        $builder->where('transaksi_check.jenis_check', 'Overhaul');

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

    public function pdfOverhaul()
    {
        $lokasiFilter   = $this->request->getGet('lokasi') ?: 'MFG 1';
        $searchFilter   = $this->request->getGet('search') ?: '';
        $bulanFilter    = $this->request->getGet('bulan') ?: date('Y-m');

        $db = \Config\Database::connect();
        $builder = $db->table('laporan_abnormal')
                      ->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi, transaksi_check.kategori')
                      ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                      ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                      ->where('transaksi_check.jenis_check', 'Overhaul');

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
            'kategoriFilter' => 'Overhaul' // Dummy untuk file PDF jika diperlukan
        ];

        $html = view('abnormal/pdf', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $filenameLokasi = $lokasiFilter === 'all' ? 'Semua_Area' : str_replace(' ', '_', $lokasiFilter);
        $filenameBulan = $bulanFilter === 'all' ? 'Semua_Bulan' : $bulanFilter;
        $dompdf->stream('Laporan_Abnormal_Overhaul_' . $filenameLokasi . '_' . $filenameBulan . '.pdf', ['Attachment' => true]);
        return;
    }

    public function pdfAllSummaryOverhaul()
    {
        $bulanFilter = $this->request->getGet('bulan') ?: date('Y-m');
        $db = \Config\Database::connect();
        
        $linesByLokasi = [
            'MFG 1' => ['Brother', 'Milling', 'Kasahara', 'Knurling', 'Osl', 'Centering Grinding', 'Double Milling', 'Double Center Drill'],
            'MFG 2' => ['Brother', 'Osl', 'Kasahara', 'Buffing', 'Thread', 'Burnishing']
        ];
        
        $allData = [];
        
        foreach (['MFG 1', 'MFG 2'] as $lokasi) {
            $builder = $db->table('laporan_abnormal')
                          ->select('laporan_abnormal.*, master_mesin.no_mesin, master_mesin.type_mesin, master_mesin.lokasi')
                          ->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')
                          ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                          ->where('transaksi_check.jenis_check', 'Overhaul')
                          ->where('master_mesin.lokasi', $lokasi)
                          ->like('laporan_abnormal.pengecekan_tanggal', $bulanFilter . '-', 'after');
                          
            $reports = $builder->get()->getResultArray();
            
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

        $allAbnormal = $db->table('laporan_abnormal')
                             ->select('SUM(CASE WHEN laporan_abnormal.action IS NULL OR laporan_abnormal.action = \'\' THEN 1 ELSE 0 END) as totalOpen,
                                     COUNT(laporan_abnormal.id_abnormal) as totalAll')
                             ->join('transaksi_check', 'transaksi_check.id_transaksi = laporan_abnormal.id_transaksi', 'left')
                             ->where('transaksi_check.jenis_check', 'Overhaul')
                             ->like('laporan_abnormal.pengecekan_tanggal', $bulanFilter . '-', 'after')
                             ->get()->getRowArray();
                             
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

        $html = view('abnormal/pdf_all_summary', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $filename = 'Laporan_Abnormal_Overhaul_Ringkasan_' . $bulanFilter . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        return;
    }

    public function updateOverhaul()
    {
        $idAbnormal = (int) $this->request->getPost('id_abnormal');
        if ($idAbnormal <= 0) {
            return redirect()->back()->with('error', 'Laporan Abnormal tidak valid.');
        }

        $data = [
            'type_sparepart'  => $this->request->getPost('type_sparepart') ?: null,
            'progres_stock'   => $this->request->getPost('progres_stock') ?: null,
            'progres_tanggal' => $this->request->getPost('progres_tanggal') ?: null,
            'action'          => $this->request->getPost('action') ?: null,
            'repair_pic'      => $this->request->getPost('repair_pic') ?: null,
            'keterangan'      => $this->request->getPost('keterangan') ?: null,
        ];
        
        $isHapusSemua = $this->request->getPost('hapus_semua') == '1';
        if ($isHapusSemua) {
            $existing = $this->abnormalModel->find($idAbnormal);
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
        $file1 = $this->request->getFile('foto_perbaikan');
        if ($file1 && $file1->isValid() && !$file1->hasMoved()) {
            $newName = 'repair_' . time() . '_1_' . uniqid() . '.' . $file1->getClientExtension();
            $file1->move(FCPATH . 'uploads/abnormal/', $newName);
            $data['foto_perbaikan'] = $newName;
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        // Handle foto_perbaikan_2
        $file2 = $this->request->getFile('foto_perbaikan_2');
        if ($file2 && $file2->isValid() && !$file2->hasMoved()) {
            $newName = 'repair_' . time() . '_2_' . uniqid() . '.' . $file2->getClientExtension();
            $file2->move(FCPATH . 'uploads/abnormal/', $newName);
            $data['foto_perbaikan_2'] = $newName;
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        if ($this->abnormalModel->update($idAbnormal, $data)) {
            return redirect()->to('/abnormal/overhaul')->with('success', 'Rencana perbaikan Laporan Abnormal Overhaul berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui Laporan Abnormal Overhaul.');
    }

    /**
     * POST /abnormal/upload-foto-perbaikan
     * Upload foto setelah perbaikan (AJAX, JSON response)
     */
    public function uploadFotoPerbaikan()
    {
        $idAbnormal = (int) $this->request->getPost('id_abnormal');
        $slot = (int) $this->request->getPost('foto_slot') ?: 1;
        $dbColumn = ($slot === 2) ? 'foto_perbaikan_2' : 'foto_perbaikan';
        
        if ($idAbnormal <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID tidak valid.']);
        }

        $file = $this->request->getFile('foto_perbaikan');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON(['success' => false, 'message' => 'File tidak valid.']);
        }

        // Validasi tipe file (gambar saja)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya file gambar yang diizinkan (jpg, png, gif, webp).']);
        }

        $uploadPath = FCPATH . 'uploads/abnormal/';
        $newName = 'repair_' . time() . '_' . $slot . '_' . uniqid() . '.' . $file->getClientExtension();
        $file->move($uploadPath, $newName);

        // Simpan nama file ke laporan_abnormal
        $existing = $this->abnormalModel->find($idAbnormal);
        
        // Hapus foto lama jika ada
        if (!empty($existing[$dbColumn])) {
            $oldPath = $uploadPath . $existing[$dbColumn];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        if ($this->abnormalModel->update($idAbnormal, [$dbColumn => $newName, 'updated_at' => date('Y-m-d H:i:s')])) {
            return $this->response->setJSON([
                'success'   => true,
                'message'   => 'Foto perbaikan ' . $slot . ' berhasil diupload.',
                'foto_url'  => base_url('uploads/abnormal/' . $newName),
                'foto_name' => $newName,
                'slot'      => $slot,
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Gagal menyimpan foto.']);
    }

    /**
     * POST /abnormal/delete-foto-perbaikan
     * Hapus foto setelah perbaikan (AJAX, JSON response)
     */
    public function deleteFotoPerbaikan()
    {
        $idAbnormal = (int) $this->request->getPost('id_abnormal');
        $slot = (int) $this->request->getPost('foto_slot') ?: 1;
        $dbColumn = ($slot === 2) ? 'foto_perbaikan_2' : 'foto_perbaikan';
        
        if ($idAbnormal <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID tidak valid.']);
        }
        
        $existing = $this->abnormalModel->find($idAbnormal);
        if (!$existing) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
        }
        
        if (!empty($existing[$dbColumn])) {
            $oldPath = FCPATH . 'uploads/abnormal/' . $existing[$dbColumn];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $this->abnormalModel->update($idAbnormal, [$dbColumn => null, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        
        return $this->response->setJSON(['success' => true, 'message' => 'Foto berhasil dihapus.']);
    }

}
