<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MesinModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class MesinController extends BaseController
{
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
            $builder->where('jenis', $jenis);
        }

        // Save current url with query params to session so we can return to it after edit/delete
        session()->set('last_mesin_url', (string) current_url(true));

        return view('admin/mesin/index', [
            'title'  => 'Master Mesin',
            'daftar' => $builder->findAll(),
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
        return view('admin/mesin/form', [
            'title' => 'Tambah Mesin',
            'mesin' => null,
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
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

        $redirectUrl = session()->get('last_mesin_url') ?? '/admin/mesin';
        return redirect()->to($redirectUrl)->with('success', 'Mesin berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $mesin = $this->model->find($id);
        if (! $mesin) {
            $redirectUrl = session()->get('last_mesin_url') ?? '/admin/mesin';
            return redirect()->to($redirectUrl)->with('error', 'Mesin tidak ditemukan.');
        }

        return view('admin/mesin/form', [
            'title' => 'Edit Mesin',
            'mesin' => $mesin,
        ]);
    }

    public function update(int $id)
    {
        $redirectUrl = session()->get('last_mesin_url') ?? '/admin/mesin';

        if (! $this->model->find($id)) {
            return redirect()->to($redirectUrl)->with('error', 'Mesin tidak ditemukan.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'no_mesin'        => $this->request->getPost('no_mesin'),
            'type_mesin'      => $this->request->getPost('type_mesin'),
            'serial_nomor'    => $this->request->getPost('serial_nomor'),
            'lokasi'          => $this->request->getPost('lokasi'),
            'line'            => $this->request->getPost('line') ?: null,
            'bar_feeder_type' => $this->request->getPost('bar_feeder_type'),
            'jenis'           => $this->request->getPost('jenis') ?: null,
        ]);

        return redirect()->to($redirectUrl)->with('success', 'Mesin berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $redirectUrl = session()->get('last_mesin_url') ?? '/admin/mesin';

        if (! $this->model->find($id)) {
            return redirect()->to($redirectUrl)->with('error', 'Mesin tidak ditemukan.');
        }

        $this->model->delete($id);
        return redirect()->to($redirectUrl)->with('success', 'Mesin berhasil dihapus.');
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
            
            $successInsert = 0;
            $successUpdate = 0;
            $errors = [];
            
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
                
                if ($lokasi !== Lokasi::MFG2->value && (empty($typeMesin) || empty($serialNomor))) {
                    $errors[] = "Baris {$row}: Type Mesin dan Serial Nomor wajib diisi untuk lokasi selain MFG 2.";
                    continue;
                }
                
                if (! in_array($lokasi, [Lokasi::MFG1->value, Lokasi::MFG2->value], true)) {
                    $errors[] = "Baris {$row}: Lokasi '{$lokasi}' tidak valid. Harus Lokasi::MFG1->value atau 'MFG 2'.";
                    continue;
                }
                
                $existing = $this->model->where('no_mesin', $noMesin)->first();
                
                if ($existing) {
                    $this->model->update($existing['id_mesin'], [
                        'type_mesin'      => $typeMesin,
                        'serial_nomor'    => $serialNomor,
                        'lokasi'          => $lokasi,
                        'line'            => empty($line) ? null : $line,
                        'bar_feeder_type' => empty($barFeederType) ? null : $barFeederType,
                        'jenis'           => empty($jenis) ? null : $jenis,
                    ]);
                    $successUpdate++;
                } else {
                    $this->model->insert([
                        'no_mesin'        => $noMesin,
                        'type_mesin'      => $typeMesin,
                        'serial_nomor'    => $serialNomor,
                        'lokasi'          => $lokasi,
                        'line'            => empty($line) ? null : $line,
                        'bar_feeder_type' => empty($barFeederType) ? null : $barFeederType,
                        'jenis'           => empty($jenis) ? null : $jenis,
                    ]);
                    $successInsert++;
                }
            }
            
            $msg = "Impor selesai. Ditambahkan: {$successInsert}, Diperbarui: {$successUpdate}.";
            if (! empty($errors)) {
                $msg .= " Beberapa baris dilewati:\n" . implode("\n", $errors);
                return redirect()->to('/admin/mesin')->with('error', $msg);
            }
            
            return redirect()->to('/admin/mesin')->with('success', $msg);
            
        } catch (\Exception $e) {
            return redirect()->to('/admin/mesin')->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }

    private function rules(): array
    {
        return [
            'no_mesin'        => 'required|max_length[50]',
            'type_mesin'      => ($this->request->getPost('lokasi') === Lokasi::MFG2->value ? 'permit_empty|max_length[100]' : 'required|max_length[100]'),
            'serial_nomor'    => ($this->request->getPost('lokasi') === Lokasi::MFG2->value ? 'permit_empty|max_length[100]' : 'required|max_length[100]'),
            'lokasi'          => 'required|in_list[MFG 1,MFG 2]',
            'line'            => 'permit_empty|string|max_length[50]',
            'bar_feeder_type' => 'permit_empty|string|max_length[100]',
            'jenis'           => 'permit_empty|string|max_length[100]',
        ];
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
}
