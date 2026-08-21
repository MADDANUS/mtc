<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MesinModel;
use App\Models\LineModel;
use App\Enums\Role;
use App\Enums\Lokasi;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Traits\AdminCrudTrait;

class MesinController extends BaseController
{
    use AdminCrudTrait;
    protected MesinModel $model;

    public function __construct()
    {
        $this->model = new MesinModel();
    }

    public function index()
    {
        $role = session()->get('role');
        $lokasiUser = session()->get('lokasi');
        $builder = $this->model->orderBy('lokasi', 'ASC')->orderBy('no_mesin', 'ASC');
        
        // Filter by user role (Leader only sees their own location)
        if ($role === Role::Leader->value && $lokasiUser) {
            $builder->where('lokasi', $lokasiUser);
        }

        // Get filter inputs
        $q = $this->request->getGet('q');
        $lokasi = $this->request->getGet('lokasi');
        $line = $this->request->getGet('line');
        $jenis = $this->request->getGet('jenis');

        if (!empty($q)) {
            $builder->groupStart()
                    ->like('no_mesin', $q)
                    ->orLike('type_mesin', $q)
                    ->orLike('serial_nomor', $q)
                    ->groupEnd();
        }

        if (!empty($lokasi) && $lokasi !== 'all') {
            // Ensure leader cannot override their own location restriction
            if ($role !== Role::Leader->value || ($role === Role::Leader->value && $lokasi === $lokasiUser)) {
                $builder->where('lokasi', $lokasi);
            }
        }

        if (!empty($line) && $line !== 'all') {
            $builder->where('line', $line);
        }

        if (!empty($jenis) && $jenis !== 'all') {
            if ($jenis === '-') {
                $builder->groupStart()
                        ->where('jenis', null)
                        ->orWhere('jenis', '')
                        ->groupEnd();
            } else {
                $builder->where('jenis', $jenis);
            }
        }

        // Fetch suggestions for no_mesin using a fresh model instance to avoid consuming the builder
        $mesinSuggestions = (new \App\Models\MesinModel())->select('no_mesin');
        if ($role === \App\Enums\Role::Leader->value && $lokasiUser) {
            $mesinSuggestions->where('lokasi', $lokasiUser);
        }
        $suggestions = $mesinSuggestions->groupBy('no_mesin')->orderBy('no_mesin', 'ASC')->findAll();
        $suggestionList = array_column($suggestions, 'no_mesin');

        // Save current url with query params to session so we can return to it after edit/delete
        session()->set('last_mesin_url', (string) current_url(true));

        $lineModel = new LineModel();
        $groupedLines = $lineModel->getLinesGroupedByLokasi();
        $filteredLines = [];
        if (!empty($lokasi) && $lokasi !== 'all') {
            $filteredLines = $groupedLines[$lokasi] ?? [];
        } else if ($role === \App\Enums\Role::Leader->value && $lokasiUser) {
            $filteredLines = $groupedLines[$lokasiUser] ?? [];
        } else {
            $filteredLines = $lineModel->getAllLineNames();
        }

        // Auto-reset line filter if the selected line doesn't belong to the selected lokasi
        if (!empty($line) && $line !== 'all' && !in_array($line, $filteredLines)) {
            $line = 'all';
        }

        return view('admin/mesin/index', [
            'title'  => 'Master Mesin',
            'daftar' => $builder->findAll(),
            'suggestions' => $suggestionList,
            'allLines' => $filteredLines,
            'filters' => [
                'q' => $q,
                'lokasi' => $lokasi,
                'line' => $line,
                'jenis' => $jenis
            ]
        ]);
    }

    public function create()
    {
        $lineModel = new LineModel();
        return view('admin/mesin/form', [
            'title'        => 'Tambah Mesin',
            'mesin'        => null,
            'linesGrouped' => $lineModel->getLinesGroupedByLokasi(),
        ]);
    }

    public function store()
    {
        $noMesin = $this->request->getPost('no_mesin');
        $existing = $this->model->where('no_mesin', $noMesin)->first();

        if ($existing) {
            return $this->update((int) $existing['id_mesin']);
        }

        if (! $this->validate($this->rules())) {
            return $this->redirectValidationError();
        }

        $this->model->insert([
            'no_mesin'        => $this->request->getPost('no_mesin'),
            'type_mesin'      => $this->request->getPost('type_mesin'),
            'serial_nomor'    => $this->request->getPost('serial_nomor'),
            'lokasi'          => $this->request->getPost('lokasi'),
            'line'            => $this->request->getPost('line') ?: null,
            'bar_feeder_type' => $this->request->getPost('bar_feeder_type'),
            'jenis'           => $this->request->getPost('jenis') ?: null,
        ]);

        $idMesin = $this->model->getInsertID();

        // --- TAHAP 3: RIWAYAT MESIN OTOMATIS ---
        $lokasiTujuan = $this->request->getPost('lokasi');
        $lineTujuan = $this->request->getPost('line');
        $bulanIni = date('Y-m');
        $approvalModel = new \App\Models\ApprovalBulananModel();
        
        $approvalTujuan = $approvalModel->where('lokasi', $lokasiTujuan)
                                        ->where('line', $lineTujuan)
                                        ->where('bulan_tahun', $bulanIni)
                                        ->first();
                                        
        $tanggalMulai = date('Y-m-d'); // Hari ini
        if ($approvalTujuan && $approvalTujuan['status'] === 'Approved Final') {
            $tanggalMulai = date('Y-m-01', strtotime('+1 month')); // Lempar ke bulan depan
        }

        $riwayatModel = new \App\Models\RiwayatMesinModel();
        $riwayatModel->insert([
            'id_mesin' => $idMesin,
            'lokasi' => $lokasiTujuan,
            'line' => $lineTujuan,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => null
        ]);
        // ---------------------------------------

        $redirectUrl = session()->get('last_mesin_url') ?? '/admin/mesin';
        return $this->redirectSuccess($redirectUrl, 'Mesin berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $mesin = $this->model->find($id);
        if (! $mesin) {
            $redirectUrl = session()->get('last_mesin_url') ?? '/admin/mesin';
            return $this->redirectNotFound($redirectUrl, 'Mesin');
        }

        $lineModel = new LineModel();
        return view('admin/mesin/form', [
            'title'        => 'Edit Mesin',
            'mesin'        => $mesin,
            'linesGrouped' => $lineModel->getLinesGroupedByLokasi(),
        ]);
    }

    public function update(int $id)
    {
        $redirectUrl = session()->get('last_mesin_url') ?? '/admin/mesin';

        if (! $this->model->find($id)) {
            return $this->redirectNotFound($redirectUrl, 'Mesin');
        }

        $oldMesin = $this->model->find($id);

        if (! $this->validate($this->rules($id))) {
            return $this->redirectValidationError();
        }

        $newData = [
            'no_mesin'        => $this->request->getPost('no_mesin'),
            'type_mesin'      => $this->request->getPost('type_mesin'),
            'serial_nomor'    => $this->request->getPost('serial_nomor'),
            'lokasi'          => $this->request->getPost('lokasi'),
            'line'            => $this->request->getPost('line') ?: null,
            'bar_feeder_type' => $this->request->getPost('bar_feeder_type'),
            'jenis'           => $this->request->getPost('jenis') ?: null,
        ];

        $hasChanged = false;
        foreach ($newData as $key => $val) {
            $oldVal = $oldMesin[$key] ?? null;
            // Normalize null and empty strings for comparison
            $oldStr = trim(is_null($oldVal) ? '' : (string) $oldVal);
            $newStr = trim(is_null($val) ? '' : (string) $val);
            
            if (strcasecmp($oldStr, $newStr) !== 0) {
                $hasChanged = true;
                break;
            }
        }

        if (!$hasChanged) {
            return redirect()->to($redirectUrl)->with('info', 'Tidak ada perubahan pada data mesin.');
        }

        $this->model->update($id, $newData);

        // --- TAHAP 3: PENCATATAN RIWAYAT PINDAH ---
        $lokasiBaru = $this->request->getPost('lokasi');
        $lineBaru = $this->request->getPost('line') ?: null;

        if ($oldMesin['lokasi'] !== $lokasiBaru || $oldMesin['line'] !== $lineBaru) {
            $bulanIni = date('Y-m');
            $approvalModel = new \App\Models\ApprovalBulananModel();
            
            $approvalLama = $approvalModel->where('lokasi', $oldMesin['lokasi'])
                                          ->where('line', $oldMesin['line'])
                                          ->where('bulan_tahun', $bulanIni)
                                          ->first();
                                          
            $approvalBaru = $approvalModel->where('lokasi', $lokasiBaru)
                                          ->where('line', $lineBaru)
                                          ->where('bulan_tahun', $bulanIni)
                                          ->first();
                                          
            $isLamaFinal = ($approvalLama && $approvalLama['status'] === 'Approved Final');
            $isBaruFinal = ($approvalBaru && $approvalBaru['status'] === 'Approved Final');
            
            if ($isLamaFinal || $isBaruFinal) {
                // Tahan di line lama sampai akhir bulan ini
                $tanggalSelesaiLama = date('Y-m-t'); // Akhir bulan ini
                $tanggalMulaiBaru = date('Y-m-01', strtotime('+1 month')); // Bulan depan
            } else {
                // Pindah langsung untuk laporan bulan ini
                $tanggalSelesaiLama = date('Y-m-t', strtotime('-1 month')); // Akhir bulan lalu
                $tanggalMulaiBaru = date('Y-m-01'); // Awal bulan ini
            }
            
            $riwayatModel = new \App\Models\RiwayatMesinModel();
            
            // Bersihkan riwayat yang bentrok di masa depan
            $riwayatModel->where('id_mesin', $id)
                         ->where('tanggal_mulai >=', $tanggalMulaiBaru)
                         ->delete();

            // Tutup semua riwayat yang sedang berjalan (atau melewati tanggal mulai baru)
            $riwayatModel->where('id_mesin', $id)
                         ->groupStart()
                             ->where('tanggal_selesai', null)
                             ->orWhere('tanggal_selesai >=', $tanggalMulaiBaru)
                         ->groupEnd()
                         ->set(['tanggal_selesai' => $tanggalSelesaiLama])
                         ->update();
            
            // Buka riwayat baru
            $riwayatModel->insert([
                'id_mesin' => $id,
                'lokasi' => $lokasiBaru,
                'line' => $lineBaru,
                'tanggal_mulai' => $tanggalMulaiBaru,
                'tanggal_selesai' => null
            ]);
        }
        // -----------------------------------------

        // --- TAHAP 4: PENCATATAN AUDIT TRAIL ---
        $logModel = new \App\Models\LogMasterMesinModel();
        $newData = $this->model->find($id);
        $logModel->logChanges($id, $oldMesin, $newData, session()->get('user_id'));


        return $this->redirectSuccess($redirectUrl, 'Mesin berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $redirectUrl = session()->get('last_mesin_url') ?? '/admin/mesin';

        if (! $this->model->find($id)) {
            return $this->redirectNotFound($redirectUrl, 'Mesin');
        }

        $this->model->delete($id);
        return $this->redirectSuccess($redirectUrl, 'Mesin berhasil dihapus.');
    }

    public function export()
    {
        $role = session()->get('role');
        $lokasiUser = session()->get('lokasi');
        $builder = $this->model->orderBy('lokasi', 'ASC')->orderBy('no_mesin', 'ASC');
        
        if ($role === Role::Leader->value && $lokasiUser) {
            $builder->where('lokasi', $lokasiUser);
        }

        $q = $this->request->getGet('q');
        $lokasi = $this->request->getGet('lokasi');
        $line = $this->request->getGet('line');
        $jenis = $this->request->getGet('jenis');

        if (!empty($q)) {
            $builder->groupStart()
                    ->like('no_mesin', $q)
                    ->orLike('type_mesin', $q)
                    ->orLike('serial_nomor', $q)
                    ->groupEnd();
        }

        if (!empty($lokasi) && $lokasi !== 'all') {
            $builder->where('lokasi', $lokasi);
        }

        if (!empty($line) && $line !== 'all') {
            $builder->where('line', $line);
        }

        if (!empty($jenis) && $jenis !== 'all') {
            $builder->where('jenis', $jenis);
        }

        $mesin = $builder->findAll();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'No Mesin');
        $sheet->setCellValue('B1', 'Type Mesin');
        $sheet->setCellValue('C1', 'Serial Nomor');
        $sheet->setCellValue('D1', 'Lokasi');
        $sheet->setCellValue('E1', 'Line');
        $sheet->setCellValue('F1', 'Bar Feeder Type');
        $sheet->setCellValue('G1', 'Jenis');
        
        // Data
        $row = 2;
        foreach ($mesin as $m) {
            $sheet->setCellValue('A' . $row, $m['no_mesin']);
            $sheet->setCellValue('B' . $row, $m['type_mesin']);
            $sheet->setCellValue('C' . $row, $m['serial_nomor']);
            $sheet->setCellValue('D' . $row, $m['lokasi']);
            $sheet->setCellValue('E' . $row, $m['line']);
            $sheet->setCellValue('F' . $row, $m['bar_feeder_type']);
            $sheet->setCellValue('G' . $row, isset($m['jenis']) ? $m['jenis'] : '');
            $row++;
        }
        
        $filename = 'mesin_export_' . date('Ymd_His') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function template()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'No Mesin');
        $sheet->setCellValue('B1', 'Type Mesin');
        $sheet->setCellValue('C1', 'Serial Nomor');
        $sheet->setCellValue('D1', 'Lokasi');
        $sheet->setCellValue('E1', 'Line');
        $sheet->setCellValue('F1', 'Bar Feeder Type');
        $sheet->setCellValue('G1', 'Jenis');
        
        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0070C0']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'template_mesin.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function import()
    {
        $file = $this->request->getFile('file_excel');
        if (! $file || ! $file->isValid()) {
            return redirect()->to('/admin/mesin')->with('error', 'Silakan pilih file Excel yang valid.');
        }
        
        $extension = $file->getExtension();
        if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->to('/admin/mesin')->with('error', 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv');
        }
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            
            $successInsert = [];
            $successUpdate = [];
            $noChanges = [];
            $errors = [];
            $serialDalamFile = []; // Untuk deteksi duplikat serial dalam 1 file Excel
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $noMesin       = trim($sheet->getCell('A' . $row)->getValue() ?? '');
                $typeMesin     = trim($sheet->getCell('B' . $row)->getValue() ?? '');
                $serialNomor   = trim($sheet->getCell('C' . $row)->getValue() ?? '');
                $lokasi        = trim($sheet->getCell('D' . $row)->getValue() ?? '');
                $line          = trim($sheet->getCell('E' . $row)->getValue() ?? '');
                $barFeederType = trim($sheet->getCell('F' . $row)->getValue() ?? '');
                $jenis         = trim($sheet->getCell('G' . $row)->getValue() ?? '');
                
                // Lewati baris kosong
                if (empty($noMesin) && empty($typeMesin) && empty($serialNomor) && empty($lokasi)) {
                    continue;
                }
                
                if (empty($noMesin) || empty($lokasi)) {
                    $errors[] = "Baris {$row}: No Mesin dan Lokasi wajib diisi.";
                    continue;
                }
                
                if ($lokasi !== Lokasi::MFG2->value && empty($typeMesin)) {
                    $errors[] = "Baris {$row}: Type Mesin wajib diisi untuk lokasi selain MFG 2.";
                    continue;
                }
                
                // AUTO-CORRECT LOKASI
                $lokasi = strtoupper($lokasi);
                if ($lokasi === 'MFG1') $lokasi = 'MFG 1';
                if ($lokasi === 'MFG2') $lokasi = 'MFG 2';
                if ($lokasi === 'PLAN2' || $lokasi === 'PLAN 2') $lokasi = 'Plan 2';
                
                if (! in_array($lokasi, [Lokasi::MFG1->value, Lokasi::MFG2->value, Lokasi::PLAN2->value], true)) {
                    $errors[] = "Baris {$row}: Lokasi '{$lokasi}' tidak valid. Harus 'MFG 1', 'MFG 2', atau 'Plan 2'.";
                    continue;
                }

                // Jika serial_nomor kosong di Excel, gunakan no_mesin sebagai penggantinya
                if (empty($serialNomor)) {
                    $serialNomor = $noMesin;
                }

                // Cegah duplikat serial_nomor dalam 1 file Excel
                if (in_array($serialNomor, $serialDalamFile, true)) {
                    $errors[] = "Baris {$row}: Serial Nomor '{$serialNomor}' muncul lebih dari sekali dalam file Excel ini.";
                    continue;
                }
                $serialDalamFile[] = $serialNomor;
                
                // --- PATOKAN UTAMA: Cari berdasarkan serial_nomor ---
                $existing = $this->model->where('serial_nomor', $serialNomor)->first();
                
                if ($existing) {
                    $newData = [
                        'no_mesin'        => $noMesin,
                        'type_mesin'      => $typeMesin,
                        'serial_nomor'    => $serialNomor,
                        'lokasi'          => $lokasi,
                        'line'            => empty($line) ? null : $line,
                        'bar_feeder_type' => empty($barFeederType) ? null : $barFeederType,
                        'jenis'           => empty($jenis) ? null : $jenis,
                    ];
                    
                    $hasChanged = false;
                    foreach ($newData as $key => $val) {
                        $oldVal = $existing[$key] ?? null;
                        $oldStr = trim(is_null($oldVal) ? '' : (string) $oldVal);
                        $newStr = trim(is_null($val) ? '' : (string) $val);
                        
                        if (strcasecmp($oldStr, $newStr) !== 0) {
                            $hasChanged = true;
                            break;
                        }
                    }

                    if (!$hasChanged) {
                        $noChanges[] = "Baris {$row}: {$noMesin} - Tidak ada perubahan.";
                        continue;
                    }

                    // UPDATE: termasuk update no_mesin jika berubah di Excel
                    $this->model->update($existing['id_mesin'], $newData);
                    
                    // --- RIWAYAT MESIN OTOMATIS (UPDATE) ---
                    $lokasiBaru = $lokasi;
                    $lineBaru = empty($line) ? null : $line;
                    if ($existing['lokasi'] !== $lokasiBaru || $existing['line'] !== $lineBaru) {
                        $bulanIni = date('Y-m');
                        $approvalModel = new \App\Models\ApprovalBulananModel();
                        $approvalLama = $approvalModel->where('lokasi', $existing['lokasi'])
                                                      ->where('line', $existing['line'])
                                                      ->where('bulan_tahun', $bulanIni)
                                                      ->first();
                        $approvalBaru = $approvalModel->where('lokasi', $lokasiBaru)
                                                      ->where('line', $lineBaru)
                                                      ->where('bulan_tahun', $bulanIni)
                                                      ->first();
                                                      
                        $isLamaFinal = ($approvalLama && $approvalLama['status'] === 'Approved Final');
                        $isBaruFinal = ($approvalBaru && $approvalBaru['status'] === 'Approved Final');
                        
                        if ($isLamaFinal || $isBaruFinal) {
                            $tanggalSelesaiLama = date('Y-m-t');
                            $tanggalMulaiBaru = date('Y-m-01', strtotime('+1 month'));
                        } else {
                            $tanggalSelesaiLama = date('Y-m-t', strtotime('-1 month'));
                            $tanggalMulaiBaru = date('Y-m-01');
                        }
                        
                        $riwayatModel = new \App\Models\RiwayatMesinModel();
                        $riwayatModel->where('id_mesin', $existing['id_mesin'])
                                     ->where('tanggal_mulai >=', $tanggalMulaiBaru)
                                     ->delete();
                        $riwayatModel->where('id_mesin', $existing['id_mesin'])
                                     ->groupStart()
                                         ->where('tanggal_selesai', null)
                                         ->orWhere('tanggal_selesai >=', $tanggalMulaiBaru)
                                     ->groupEnd()
                                     ->set(['tanggal_selesai' => $tanggalSelesaiLama])
                                     ->update();
                        $riwayatModel->insert([
                            'id_mesin' => $existing['id_mesin'],
                            'lokasi' => $lokasiBaru,
                            'line' => $lineBaru,
                            'tanggal_mulai' => $tanggalMulaiBaru,
                            'tanggal_selesai' => null
                        ]);
                    }
                    
                    // --- AUDIT TRAIL EXCEL ---
                    $logModel = new \App\Models\LogMasterMesinModel();
                    $newData = $this->model->find($existing['id_mesin']);
                    $logModel->logChanges($existing['id_mesin'], $existing, $newData, session()->get('user_id'));

                    $successUpdate[] = "Baris {$row}: {$noMesin} - Berhasil terupdate.";
                } else {
                    // INSERT: mesin baru yang belum pernah ada di database
                    $this->model->insert([
                        'no_mesin'        => $noMesin,
                        'type_mesin'      => $typeMesin,
                        'serial_nomor'    => $serialNomor,
                        'lokasi'          => $lokasi,
                        'line'            => empty($line) ? null : $line,
                        'bar_feeder_type' => empty($barFeederType) ? null : $barFeederType,
                        'jenis'           => empty($jenis) ? null : $jenis,
                    ]);
                    $idMesin = $this->model->getInsertID();

                    // --- RIWAYAT MESIN OTOMATIS (INSERT) ---
                    $lokasiTujuan = $lokasi;
                    $lineTujuan = empty($line) ? null : $line;
                    $bulanIni = date('Y-m');
                    $approvalModel = new \App\Models\ApprovalBulananModel();
                    $approvalTujuan = $approvalModel->where('lokasi', $lokasiTujuan)
                                                    ->where('line', $lineTujuan)
                                                    ->where('bulan_tahun', $bulanIni)
                                                    ->first();
                    $tanggalMulai = date('Y-m-d');
                    if ($approvalTujuan && $approvalTujuan['status'] === 'Approved Final') {
                        $tanggalMulai = date('Y-m-01', strtotime('+1 month'));
                    }
                    $riwayatModel = new \App\Models\RiwayatMesinModel();
                    $riwayatModel->insert([
                        'id_mesin' => $idMesin,
                        'lokasi' => $lokasiTujuan,
                        'line' => $lineTujuan,
                        'tanggal_mulai' => $tanggalMulai,
                        'tanggal_selesai' => null
                    ]);

                    $successInsert[] = "Baris {$row}: {$noMesin} - Berhasil ditambahkan.";
                }
            }
            
            $totalSuccess = count($successInsert) + count($successUpdate);
            $totalNoChanges = count($noChanges);
            $totalErrors = count($errors);

            $msgHtml = "<strong>Impor Selesai!</strong><br><br>";
            
            if ($totalSuccess > 0) {
                $msgHtml .= "<b>✅ Berhasil ({$totalSuccess}):</b><ul class='text-start mb-2' style='font-size:0.85rem;'>";
                foreach (array_merge($successInsert, $successUpdate) as $msgLine) {
                    $msgHtml .= "<li>{$msgLine}</li>";
                }
                $msgHtml .= "</ul>";
            }

            if ($totalNoChanges > 0) {
                $msgHtml .= "<b>ℹ️ Tidak Ada Perubahan ({$totalNoChanges}):</b><ul class='text-start mb-2' style='font-size:0.85rem;'>";
                foreach ($noChanges as $msgLine) {
                    $msgHtml .= "<li>{$msgLine}</li>";
                }
                $msgHtml .= "</ul>";
            }

            if ($totalErrors > 0) {
                $msgHtml .= "<b>❌ Gagal ({$totalErrors}):</b><ul class='text-start mb-2 text-danger' style='font-size:0.85rem;'>";
                foreach ($errors as $msgLine) {
                    $msgHtml .= "<li>{$msgLine}</li>";
                }
                $msgHtml .= "</ul>";
                return redirect()->to('/admin/mesin')->with('persistent_error', $msgHtml);
            }
            
            return redirect()->to('/admin/mesin')->with('persistent_success', $msgHtml);
            
        } catch (\Exception $e) {

            return redirect()->to('/admin/mesin')->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }

    private function rules(?int $id = null): array
    {
        $noMesinRule = 'required|max_length[50]';
        $serialNomorRule = 'permit_empty|max_length[100]';
        
        if ($id) {
            $noMesinRule .= "|is_unique[master_mesin.no_mesin,id_mesin,{$id}]";
            $serialNomorRule .= "|is_unique[master_mesin.serial_nomor,id_mesin,{$id}]";
        } else {
            $noMesinRule .= "|is_unique[master_mesin.no_mesin]";
            $serialNomorRule .= "|is_unique[master_mesin.serial_nomor]";
        }

        $rules = [
            'no_mesin'        => $noMesinRule,
            'serial_nomor'    => $serialNomorRule,
            'type_mesin'      => 'permit_empty|max_length[100]',
            'lokasi'          => 'required|in_list[MFG 1,MFG 2,Plan 2]',
            'line'            => 'permit_empty|max_length[50]',
            'bar_feeder_type' => 'permit_empty|max_length[100]',
            'jenis'           => 'permit_empty|max_length[100]',
        ];

        return $rules;
    }

    public function downloadAllQr()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300'); // allow more time for downloading many QRs

        $role = session()->get('role');
        $lokasi = session()->get('lokasi');
        $builder = $this->model->orderBy('lokasi', 'ASC')->orderBy('no_mesin', 'ASC');
        
        if ($role === Role::Leader->value && $lokasi) {
            $builder->where('lokasi', $lokasi);
        }

        $mesin = $builder->findAll();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = view('admin/mesin/pdf_all_qr', ['mesin' => $mesin]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('Semua_QRCode_Mesin.pdf', ["Attachment" => true]);
        exit();
    }

    public function generateQr()
    {
        $data = $this->request->getGet('data');
        if (empty($data)) {
            return $this->response->setStatusCode(400)->setBody('Missing data parameter');
        }

        $options = new \chillerlan\QRCode\QROptions([
            'version'      => 5,
            'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
            'eccLevel'     => \chillerlan\QRCode\Common\EccLevel::L,
            'scale'        => 5,
            'outputBase64' => false,
        ]);
        
        $qrcode = new \chillerlan\QRCode\QRCode($options);
        
        return $this->response
            ->setContentType('image/png')
            ->setBody($qrcode->render($data));
    }

    public function getRiwayat(int $id)
    {
        $logModel = new \App\Models\LogMasterMesinModel();
        $riwayat = $logModel->getRiwayatByMesin($id);
        return $this->response->setJSON($riwayat);
    }

    public function deleteRiwayat()
    {
        // Hanya admin yang boleh hapus
        if (session()->get('role') !== \App\Enums\Role::Admin->value) {
            return $this->response->setJSON(['status' => false, 'message' => 'Akses ditolak.']);
        }

        $body = $this->request->getJSON(true);
        $rawIds = $body['ids'] ?? '';

        // Validasi: hanya angka dan koma
        if (!preg_match('/^[\d,]+$/', (string)$rawIds)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Input tidak valid.']);
        }

        $ids = array_filter(array_map('intval', explode(',', $rawIds)));
        if (empty($ids)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak ada ID yang dihapus.']);
        }

        $logModel = new \App\Models\LogMasterMesinModel();
        $logModel->whereIn('id_log', $ids)->delete();

        return $this->response->setJSON(['status' => true, 'message' => 'Riwayat berhasil dihapus.']);
    }
}
