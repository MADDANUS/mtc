<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PicModel;

class PicController extends BaseController
{
    protected $picModel;

    public function __construct()
    {
        $this->picModel = new PicModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Master PIC',
            'pics'  => $this->picModel->findAll()
        ];
        return view('admin/pic/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah PIC',
        ];
        return view('admin/pic/create', $data);
    }

    public function store()
    {
        if (!$this->validate($this->picModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->picModel->insert([
            'id_pic'   => $this->request->getPost('id_pic'),
            'nama_pic' => $this->request->getPost('nama_pic'),
            'role_pic' => $this->request->getPost('role_pic') ?: 'Staff'
        ]);

        return redirect()->to('/admin/pic')->with('success', 'Data PIC berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $pic = $this->picModel->find($id);
        if (!$pic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title' => 'Edit PIC',
            'pic'   => $pic
        ];
        return view('admin/pic/edit', $data);
    }

    public function update($id)
    {
        $pic = $this->picModel->find($id);
        if (!$pic) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Validate except for unique constraint if ID is the same
        $rules = $this->picModel->getValidationRules();
        $postID = $this->request->getPost('id_pic');
        if ($postID === $id) {
            $rules['id_pic'] = 'required|max_length[20]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // If ID changed, we need to manually update since it's the primary key
        if ($postID !== $id) {
            $this->picModel->update($id, [
                'id_pic'   => $postID,
                'nama_pic' => $this->request->getPost('nama_pic'),
                'role_pic' => $this->request->getPost('role_pic') ?: 'Staff'
            ]);
        } else {
            $this->picModel->update($id, [
                'nama_pic' => $this->request->getPost('nama_pic'),
                'role_pic' => $this->request->getPost('role_pic') ?: 'Staff'
            ]);
        }

        return redirect()->to('/admin/pic')->with('success', 'Data PIC berhasil diperbarui.');
    }

    public function delete($id)
    {
        $this->picModel->delete($id);
        return redirect()->to('/admin/pic')->with('success', 'Data PIC berhasil dihapus.');
    }
    public function export()
    {
        $pics = $this->picModel->findAll();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'ID PIC');
        $sheet->setCellValue('B1', 'Nama PIC');
        
        // Data
        $row = 2;
        foreach ($pics as $p) {
            $sheet->setCellValue('A' . $row, $p['id_pic']);
            $sheet->setCellValue('B' . $row, $p['nama_pic']);
            $row++;
        }
        
        $filename = 'pic_export_' . date('Ymd_His') . '.xlsx';
        
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
        $sheet->setCellValue('A1', 'ID PIC');
        $sheet->setCellValue('B1', 'Nama PIC');
        $sheet->setCellValue('C1', 'Role PIC');
        
        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0070C0']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
        
        // Auto-size columns
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'template_pic.xlsx';
        
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
            return redirect()->to('/admin/pic')->with('error', 'Silakan pilih file Excel yang valid.');
        }
        
        $extension = $file->getExtension();
        if (! in_array($extension, ['xlsx', 'xls', 'csv'], true)) {
            return redirect()->to('/admin/pic')->with('error', 'Format file tidak didukung. Gunakan .xlsx, .xls, atau .csv');
        }
        
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestDataRow();
            
            $successInsert = 0;
            $successUpdate = 0;
            $errors = [];
            
            for ($row = 2; $row <= $highestRow; $row++) {
                $idPic   = trim($sheet->getCell('A' . $row)->getValue() ?? '');
                $namaPic = trim($sheet->getCell('B' . $row)->getValue() ?? '');
                $rolePic = trim($sheet->getCell('C' . $row)->getValue() ?? '');
                
                // Normalisasi Role PIC dari excel
                $rolePicLower = strtolower(str_replace(' ', '', $rolePic));
                if (strpos($rolePicLower, Role::Leader->value) !== false) {
                    if (strpos($rolePicLower, 'line1') !== false || $rolePicLower === 'leader1') $rolePic = 'leader1';
                    elseif (strpos($rolePicLower, 'line2') !== false || $rolePicLower === 'leader2') $rolePic = 'leader2';
                    elseif (strpos($rolePicLower, 'line3') !== false || $rolePicLower === 'leader3') $rolePic = 'leader3';
                    elseif (strpos($rolePicLower, 'cg') !== false) $rolePic = 'leadercg';
                    elseif (strpos($rolePicLower, 'sc') !== false || strpos($rolePicLower, 'second') !== false) $rolePic = 'leadersc';
                } elseif ($rolePicLower === Role::Magang->value) {
                    $rolePic = 'Magang';
                } else {
                    $rolePic = 'Staff';
                }
                
                if (empty($idPic) && empty($namaPic)) {
                    continue;
                }
                
                if (empty($idPic) || empty($namaPic)) {
                    $errors[] = "Baris {$row}: ID PIC dan Nama PIC wajib diisi.";
                    continue;
                }
                
                $existing = $this->picModel->where('id_pic', $idPic)->first();
                
                if ($existing) {
                    $this->picModel->update($idPic, [
                        'nama_pic' => $namaPic,
                        'role_pic' => empty($rolePic) ? null : $rolePic,
                    ]);
                    $successUpdate++;
                } else {
                    $this->picModel->insert([
                        'id_pic'   => $idPic,
                        'nama_pic' => $namaPic,
                        'role_pic' => empty($rolePic) ? null : $rolePic,
                    ]);
                    $successInsert++;
                }
            }
            
            $msg = "Impor selesai. Ditambahkan: {$successInsert}, Diperbarui: {$successUpdate}.";
            if (! empty($errors)) {
                $msg .= " Beberapa baris dilewati:\n" . implode("\n", $errors);
                return redirect()->to('/admin/pic')->with('error', $msg);
            }
            
            return redirect()->to('/admin/pic')->with('success', $msg);
            
        } catch (\Exception $e) {
            return redirect()->to('/admin/pic')->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }
    }
}
