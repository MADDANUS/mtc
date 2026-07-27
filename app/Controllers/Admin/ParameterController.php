<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ParameterCheckModel;

class ParameterController extends BaseController
{
    protected ParameterCheckModel $model;

    public function __construct()
    {
        $this->model = new ParameterCheckModel();
    }

    public function index()
    {
        $lokasi     = $this->request->getGet('lokasi') ?: 'MFG 1';
        $jenisCheck = $this->request->getGet('jenis_check') ?: 'Preventive';

        // Ambil daftar kategori unik untuk lokasi + jenis check ini
        // Urutkan kategori berdasarkan urutan terkecil (urutan pertama baris parameter)
        $kategoriQuery = $this->model->where('lokasi', $lokasi)
                                     ->where('jenis_check', $jenisCheck)
                                     ->select('kategori, MIN(urutan) as min_urut')
                                     ->groupBy('kategori')
                                     ->orderBy('min_urut', 'ASC')
                                     ->findAll();

        $daftarKategori = array_column($kategoriQuery, 'kategori');

        // Ambil parameter per kategori lengkap dengan rowspan dinamisnya
        $kategoriParameters = [];
        foreach ($daftarKategori as $kat) {
            $kategoriParameters[$kat] = $this->model->getFormRows($lokasi, $jenisCheck, $kat);
        }

        $selectedKategori = $this->request->getGet('kategori') ?: null;

        return view('admin/parameter/index', [
            'title'              => 'Master Parameter Check',
            'lokasi'             => $lokasi,
            'jenisCheck'         => $jenisCheck,
            'daftarKategori'     => $daftarKategori,
            'kategoriParameters' => $kategoriParameters,
            'selectedKategori'   => $selectedKategori,
        ]);
    }

    public function create()
    {
        return view('admin/parameter/form', [
            'title'     => 'Tambah Parameter Check',
            'parameter' => null,
            'prefill'   => [
                'lokasi'      => $this->request->getGet('lokasi') ?: 'MFG 1',
                'jenis_check' => $this->request->getGet('jenis_check') ?: 'Preventive',
                'kategori'    => $this->request->getGet('kategori') ?: '',
            ]
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'lokasi'         => $this->request->getPost('lokasi'),
            'jenis_check'    => $this->request->getPost('jenis_check'),
            'kategori'       => $this->request->getPost('kategori'),
            'section_check'  => $this->request->getPost('section_check') ?: null,
            'bagian_check'   => $this->request->getPost('bagian_check'),
            'sub_item_check' => $this->request->getPost('sub_item_check') ?: null,
            'point_check'    => $this->request->getPost('point_check'),
            'standard_check' => $this->request->getPost('standard_check'),
            'urutan'         => (int) $this->request->getPost('urutan'),
        ]);

        return redirect()->to('/admin/parameter?lokasi=' . urlencode($this->request->getPost('lokasi')) . '&jenis_check=' . urlencode($this->request->getPost('jenis_check')) . '&kategori=' . urlencode($this->request->getPost('kategori')))->with('success', 'Parameter check berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $parameter = $this->model->find($id);
        if (! $parameter) {
            return redirect()->to('/admin/parameter')->with('error', 'Parameter tidak ditemukan.');
        }

        return view('admin/parameter/form', [
            'title'     => 'Edit Parameter Check',
            'parameter' => $parameter,
            'prefill'   => null,
        ]);
    }

    public function update(int $id)
    {
        if (! $this->model->find($id)) {
            return redirect()->to('/admin/parameter')->with('error', 'Parameter tidak ditemukan.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'lokasi'         => $this->request->getPost('lokasi'),
            'jenis_check'    => $this->request->getPost('jenis_check'),
            'kategori'       => $this->request->getPost('kategori'),
            'section_check'  => $this->request->getPost('section_check') ?: null,
            'bagian_check'   => $this->request->getPost('bagian_check'),
            'sub_item_check' => $this->request->getPost('sub_item_check') ?: null,
            'point_check'    => $this->request->getPost('point_check'),
            'standard_check' => $this->request->getPost('standard_check'),
            'urutan'         => (int) $this->request->getPost('urutan'),
        ]);

        return redirect()->to('/admin/parameter?lokasi=' . urlencode($this->request->getPost('lokasi')) . '&jenis_check=' . urlencode($this->request->getPost('jenis_check')) . '&kategori=' . urlencode($this->request->getPost('kategori')))->with('success', 'Parameter check berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $parameter = $this->model->find($id);
        if (! $parameter) {
            return redirect()->to('/admin/parameter')->with('error', 'Parameter tidak ditemukan.');
        }

        $this->model->delete($id);
        return redirect()->to('/admin/parameter?lokasi=' . urlencode($parameter['lokasi']) . '&jenis_check=' . urlencode($parameter['jenis_check']))->with('success', 'Parameter check berhasil dihapus.');
    }

    public function fixUrutan()
    {
        $db = \Config\Database::connect();

        // Ambil semua kombinasi lokasi, jenis_check, kategori
        $combinations = $db->table('master_parameter_check')
                           ->select('lokasi, jenis_check, kategori')
                           ->groupBy('lokasi, jenis_check, kategori')
                           ->get()
                           ->getResultArray();

        $totalUpdated = 0;
        foreach ($combinations as $combo) {
            $params = $db->table('master_parameter_check')
                         ->where('lokasi', $combo['lokasi'])
                         ->where('jenis_check', $combo['jenis_check'])
                         ->where('kategori', $combo['kategori'])
                         ->orderBy('urutan', 'ASC')
                         ->orderBy('id_parameter', 'ASC')
                         ->get()
                         ->getResultArray();
            
            $index = 1;
            foreach ($params as $p) {
                $db->table('master_parameter_check')
                   ->where('id_parameter', $p['id_parameter'])
                   ->update(['urutan' => $index]);
                $index++;
                $totalUpdated++;
            }
        }

        return redirect()->to('/admin/parameter')->with('success', "Berhasil mereset penomoran urutan {$totalUpdated} parameter check menjadi mulai dari 1 per kategori!");
    }

    private function rules(): array
    {
        return [
            'lokasi'         => 'required|in_list[MFG 1,MFG 2]',
            'jenis_check'    => 'required|in_list[Preventive,Overhaul]',
            'kategori'       => 'required|max_length[100]',
            'section_check'  => 'permit_empty|max_length[150]',
            'bagian_check'   => 'required|max_length[150]',
            'sub_item_check' => 'permit_empty|max_length[150]',
            'point_check'    => 'required|max_length[150]',
            'standard_check' => 'required|max_length[150]',
            'urutan'         => 'required|is_natural',
        ];
    }
    public function export()
    {
        $parameters = $this->model->orderBy('lokasi', 'ASC')->orderBy('kategori', 'ASC')->orderBy('urutan', 'ASC')->findAll();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'Lokasi');
        $sheet->setCellValue('B1', 'Jenis Check');
        $sheet->setCellValue('C1', 'Kategori');
        $sheet->setCellValue('D1', 'Section Check');
        $sheet->setCellValue('E1', 'Bagian Check');
        $sheet->setCellValue('F1', 'Sub Item Check');
        $sheet->setCellValue('G1', 'Point Check');
        $sheet->setCellValue('H1', 'Standard Check');
        $sheet->setCellValue('I1', 'Urutan');
        
        // Data
        $row = 2;
        foreach ($parameters as $p) {
            $sheet->setCellValue('A' . $row, $p['lokasi']);
            $sheet->setCellValue('B' . $row, $p['jenis_check']);
            $sheet->setCellValue('C' . $row, $p['kategori']);
            $sheet->setCellValue('D' . $row, $p['section_check']);
            $sheet->setCellValue('E' . $row, $p['bagian_check']);
            $sheet->setCellValue('F' . $row, $p['sub_item_check']);
            $sheet->setCellValue('G' . $row, $p['point_check']);
            $sheet->setCellValue('H' . $row, $p['standard_check']);
            $sheet->setCellValue('I' . $row, $p['urutan']);
            $row++;
        }
        
        $filename = 'parameter_export_' . date('Ymd_His') . '.xlsx';
        
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
        $sheet->setCellValue('A1', 'Lokasi');
        $sheet->setCellValue('B1', 'Jenis Check');
        $sheet->setCellValue('C1', 'Kategori');
        $sheet->setCellValue('D1', 'Section Check');
        $sheet->setCellValue('E1', 'Bagian Check');
        $sheet->setCellValue('F1', 'Sub Item Check');
        $sheet->setCellValue('G1', 'Point Check');
        $sheet->setCellValue('H1', 'Standard Check');
        $sheet->setCellValue('I1', 'Urutan');
        
        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0070C0']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
        
        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'template_parameter.xlsx';
        
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
            return redirect()->to('/admin/parameter')->with('error', 'Silakan pilih file Excel yang valid.');
        }
        
        $extension = $file->getExtension();
        if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->to('/admin/parameter')->with('error', 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv');
        }
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            
            $successInsert = 0;
            $errors = [];
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $lokasi        = trim($sheet->getCell('A' . $row)->getValue() ?? '');
                $jenisCheck    = trim($sheet->getCell('B' . $row)->getValue() ?? '');
                $kategori      = trim($sheet->getCell('C' . $row)->getValue() ?? '');
                $sectionCheck  = trim($sheet->getCell('D' . $row)->getValue() ?? '');
                $bagianCheck   = trim($sheet->getCell('E' . $row)->getValue() ?? '');
                $subItemCheck  = trim($sheet->getCell('F' . $row)->getValue() ?? '');
                $pointCheck    = trim($sheet->getCell('G' . $row)->getValue() ?? '');
                $standardCheck = trim($sheet->getCell('H' . $row)->getValue() ?? '');
                $urutan        = trim($sheet->getCell('I' . $row)->getValue() ?? '');
                
                // Lewati baris kosong
                if (empty($lokasi) && empty($jenisCheck) && empty($kategori) && empty($bagianCheck)) {
                    continue;
                }
                
                if (empty($lokasi) || empty($jenisCheck) || empty($kategori) || empty($bagianCheck) || empty($pointCheck) || empty($standardCheck) || $urutan === '') {
                    $errors[] = "Baris {$row}: Lokasi, Jenis, Kategori, Bagian, Point, Standard, Urutan wajib diisi.";
                    continue;
                }
                
                if (! in_array($lokasi, ['MFG 1', 'MFG 2'], true)) {
                    $errors[] = "Baris {$row}: Lokasi '{$lokasi}' tidak valid. Harus 'MFG 1' atau 'MFG 2'.";
                    continue;
                }
                
                if (! in_array($jenisCheck, ['Preventive', 'Overhaul'], true)) {
                    $errors[] = "Baris {$row}: Jenis Check '{$jenisCheck}' tidak valid. Harus 'Preventive' atau 'Overhaul'.";
                    continue;
                }
                
                $this->model->insert([
                    'lokasi'         => $lokasi,
                    'jenis_check'    => $jenisCheck,
                    'kategori'       => $kategori,
                    'section_check'  => empty($sectionCheck) ? null : $sectionCheck,
                    'bagian_check'   => $bagianCheck,
                    'sub_item_check' => empty($subItemCheck) ? null : $subItemCheck,
                    'point_check'    => $pointCheck,
                    'standard_check' => $standardCheck,
                    'urutan'         => (int) $urutan,
                ]);
                $successInsert++;
            }
            
            $msg = "Impor selesai. Ditambahkan: {$successInsert} parameter.";
            if (! empty($errors)) {
                $msg .= " Beberapa baris dilewati:\n" . implode("\n", $errors);
                return redirect()->to('/admin/parameter')->with('error', $msg);
            }
            
            return redirect()->to('/admin/parameter')->with('success', $msg);
            
        } catch (\Exception $e) {
            return redirect()->to('/admin/parameter')->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }
}
