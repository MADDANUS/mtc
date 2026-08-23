<?php

namespace App\Controllers;

use App\Services\AbnormalService;
use App\Models\MasterMesinModel;
use App\Models\TransaksiCheckModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AbnormalController extends BaseController
{
    public function __construct()
    {
        // Constructor is empty now as model is handled by service
    }

    public function pdf()
    {
        $service = new AbnormalService();
        $data = $service->pdf($this->request);
        $html = view('abnormal/pdf', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_' . str_replace(' ', '_', $data['kategoriFilter']) . '_' . str_replace(' ', '_', $data['lokasiFilter']) . '.pdf', ['Attachment' => 0]);
    }

    public function pdfAllCategories()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllCategories($this->request);
        $html = view('abnormal/pdf_all', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Semua_Kategori_' . str_replace(' ', '_', $data['lokasiFilter']) . '_' . $data['bulanFilter'] . '.pdf', ['Attachment' => true]);
    }

    public function pdfAllSummary()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllSummary($this->request);
        $html = view('abnormal/pdf_all', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Ringkasan_Semua_Area_' . $data['bulanFilter'] . '.pdf', ['Attachment' => true]);
    }

    public function excelAllSummary()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllSummary($this->request);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary Abnormal');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE53935'] // Red for abnormal
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        $headers = ['No', 'Departemen', 'Kategori', 'Mesin', 'Point Check', 'Abnormal Condition', 'Type Sparepart', 'Tgl Pengecekan', 'PIC Cek', 'Progres', 'Tgl Progres', 'Action', 'PIC Action', 'Keterangan'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

        $rowNum = 2;
        $no = 1;
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
        ];

        foreach ($data['allReportsData'] as $item) {
            $departemen = $item['departemen'];
            $kategori = $item['kategori'];
            foreach ($item['reports'] as $r) {
                $pointCheckDisplay = $r['point_check'];
                if (!empty($r['bagian_check'])) {
                    $parts = [$r['bagian_check']];
                    if (!empty($r['sub_item_check'])) $parts[] = $r['sub_item_check'];
                    $parts[] = $r['point_check'];
                    $pointCheckDisplay = implode(' - ', $parts);
                }

                $sheet->setCellValue('A' . $rowNum, $no++);
                $sheet->setCellValue('B' . $rowNum, $departemen);
                $sheet->setCellValue('C' . $rowNum, $kategori);
                $sheet->setCellValue('D' . $rowNum, $r['no_mesin']);
                $sheet->setCellValue('E' . $rowNum, $pointCheckDisplay);
                $sheet->setCellValue('F' . $rowNum, $r['abnormal_condition']);
                $sheet->setCellValue('G' . $rowNum, $r['type_sparepart'] ?? '');
                $sheet->setCellValue('H' . $rowNum, $r['pengecekan_tanggal'] ?? '');
                $sheet->setCellValue('I' . $rowNum, $r['pengecekan_pic'] ?? '');
                $sheet->setCellValue('J' . $rowNum, $r['progres_stock'] ?? '');
                $sheet->setCellValue('K' . $rowNum, $r['progres_tanggal'] ?? '');
                $sheet->setCellValue('L' . $rowNum, $r['action'] ?? '');
                $sheet->setCellValue('M' . $rowNum, $r['repair_pic'] ?? '');
                $sheet->setCellValue('N' . $rowNum, $r['keterangan'] ?? '');
                
                $rowNum++;
            }
        }

        if ($rowNum > 2) {
            $sheet->getStyle('A2:N' . ($rowNum - 1))->applyFromArray($dataStyle);
            $sheet->getStyle('A2:A' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'N') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = "Laporan_Abnormal_Ringkasan_Semua_Area_" . $data['bulanFilter'] . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function index()
    {
        $service = new AbnormalService();
        $data = $service->index($this->request);
        if (isset($data['is_summary']) && $data['is_summary']) {
            return view('abnormal/summary', $data);
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'html' => $data['html'] ?? '',
                'currentPage' => $data['currentPage'] ?? 1,
                'totalPages' => $data['totalPages'] ?? 1,
                'totalItems' => $data['totalItems'] ?? 0,
                'perPage' => $data['perPage'] ?? 15,
                'startNo' => $data['startNo'] ?? 1,
            ]);
        }
        
        return view('abnormal/index', $data);
    }

    public function update()
    {
        $service = new AbnormalService();
        $result = $service->update($this->request);
        if ($result['status']) {
            return redirect()->to('/abnormal')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function overhaul()
    {
        $service = new AbnormalService();
        $data = $service->overhaul($this->request);
        if (isset($data['is_summary']) && $data['is_summary']) {
            return view('abnormal/summary_overhaul', $data);
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'html' => $data['html'] ?? '',
                'currentPage' => $data['currentPage'] ?? 1,
                'totalPages' => $data['totalPages'] ?? 1,
                'totalItems' => $data['totalItems'] ?? 0,
                'perPage' => $data['perPage'] ?? 15,
                'startNo' => $data['startNo'] ?? 1,
            ]);
        }
        
        return view('abnormal/index_overhaul', $data);
    }

    public function pdfOverhaul()
    {
        $service = new AbnormalService();
        $data = $service->pdfOverhaul($this->request);
        $html = view('abnormal/pdf_overhaul', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Overhaul_' . str_replace(' ', '_', $data['lokasiFilter']) . '.pdf', ['Attachment' => 0]);
    }

    public function pdfAllSummaryOverhaul()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllSummaryOverhaul($this->request);
        $html = view('abnormal/pdf_all_summary', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Overhaul_Ringkasan_' . $data['bulanFilter'] . '.pdf', ['Attachment' => true]);
    }

    public function updateOverhaul()
    {
        $service = new AbnormalService();
        $result = $service->updateOverhaul($this->request);
        if ($result['status']) {
            return redirect()->to('/abnormal/overhaul')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function uploadFotoPerbaikan()
    {
        $service = new AbnormalService();
        $result = $service->uploadFotoPerbaikan($this->request);
        return $this->response->setJSON($result);
    }

    public function testQuery() {
        $m = new \App\Models\LaporanAbnormalModel();
        $res = $m->getOverhaulLaporan('MFG 1', '2026-08', '', 15);
        return $this->response->setJSON($res);
    }

    public function deleteFotoPerbaikan()
    {
        $service = new AbnormalService();
        $result = $service->deleteFotoPerbaikan($this->request);
        return $this->response->setJSON($result);
    }
}
