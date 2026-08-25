<?php

namespace App\Controllers;

use App\Services\KontrolService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class KontrolController extends BaseController
{
    public function updateCell()
    {
        $service = new KontrolService();
        $result = $service->updateCell($this->request);
        return $this->response->setJSON($result);
    }

    public function pdf()
    {
        $service = new KontrolService();
        $data = $service->pdf($this->request);
        $html = view('kontrol/pdf', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('Checklist_Control_' . str_replace(' ', '_', $data['kategori']) . '_' . str_replace(' ', '_', $data['departemen']) . '.pdf', ['Attachment' => 0]);
    }

    public function pdfAllCategories()
    {
        $service = new KontrolService();
        $data = $service->pdfAllCategories($this->request);
        $html = view('kontrol/pdf_all', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('Checklist_Control_Semua_Kategori_' . str_replace(' ', '_', $data['departemen']) . '_' . $data['bulan'] . '.pdf', ['Attachment' => true]);
    }

    public function pdfAllSummary()
    {
        $service = new KontrolService();
        $data = $service->pdfAllSummary($this->request);
        $html = view('kontrol/pdf_all', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('Checklist_Control_Ringkasan_Semua_Area_' . $data['bulan'] . '.pdf', ['Attachment' => true]);
    }

    public function index()
    {
        $service = new KontrolService();
        $data = $service->index($this->request);
        if (isset($data['is_summary']) && $data['is_summary']) {
            return view('kontrol/summary', $data);
        }
        return view('kontrol/index', $data);
    }

    public function approveBulanan()
    {
        $service = new KontrolService();
        $result = $service->approveBulanan($this->request);
        if ($result['status']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function deleteApprovalBulanan()
    {
        $service = new KontrolService();
        $result = $service->deleteApprovalBulanan($this->request);
        if ($result['status']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    // ─── Excel: 1 kategori ───────────────────────────────────────────────
    public function excelPerKategori()
    {
        $service = new KontrolService();
        $data    = $service->pdf($this->request); // reuse same data builder
        $this->streamKontrolExcel($data);
    }

    // ─── Excel: Semua kategori di 1 departemen/line ───────────────────────
    public function excelAllCategories()
    {
        $service = new KontrolService();
        $data    = $service->pdfAllCategories($this->request);
        $this->streamKontrolAllExcel($data);
    }

    // ─── Excel: Ringkasan semua area ─────────────────────────────────────
    public function excelAllSummary()
    {
        $service = new KontrolService();
        $data    = $service->pdfAllSummary($this->request);
        $this->streamKontrolAllExcel($data);
    }

    // ─── Helper: build Excel untuk 1 kategori ────────────────────────────
    private function streamKontrolExcel(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $departemen = $data['departemen'] ?? '-';
        $kategori   = $data['kategori']   ?? '-';
        $bulan      = $data['bulan']      ?? '-';
        $line       = $data['line']       ?? '-';

        $sheet->setTitle('Checklist Control');

        $titleStyle = [
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $dataStyle = [
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];

        // Header info
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'CHECKLIST CONTROL - ' . strtoupper($kategori));
        $sheet->getStyle('A1')->applyFromArray($titleStyle);
        $sheet->setCellValue('A2', 'AREA'); $sheet->setCellValue('B2', $departemen . ($line ? ' / ' . $line : ''));
        $sheet->setCellValue('C2', 'BULAN'); $sheet->setCellValue('D2', $bulan);

        // Column headers
        $row = 4;
        $headers = ['No', 'Mesin', 'W1', 'W2', 'W3', 'W4', 'W5', 'Out of Plan', 'Ulasan', 'PIC'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $row, $h);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray($headerStyle);

        $row++;
        $no = 1;
        $grid = $data['grid'] ?? [];
        foreach ($grid as $mesinId => $mesinData) {
            $noMesin = $mesinData['no_mesin'] ?? '-';
            $checks  = $mesinData['checks']   ?? [];
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $noMesin);
            for ($w = 1; $w <= 5; $w++) {
                $check = $checks[$w] ?? null;
                $status = $check['status_check'] ?? '';
                if ($status === 'OK') $status = 'V';
                elseif ($status === 'Perlu Tindakan') $status = 'Δ';
                elseif ($status === 'Tidak Ada') $status = 'X';
                $colLetter = chr(ord('C') + ($w - 1));
                $sheet->setCellValue($colLetter . $row, $status);
            }
            $lastCheck = end($checks);
            $sheet->setCellValue('H' . $row, $mesinData['out_of_plan'] ?? '');
            $sheet->setCellValue('I' . $row, $mesinData['ulasan'] ?? '');
            $sheet->setCellValue('J' . $row, $mesinData['pic_nama'] ?? '');
            $row++;
        }
        if ($row > 5) {
            $sheet->getStyle('A5:J' . ($row - 1))->applyFromArray($dataStyle);
        }
        foreach (range('A', 'J') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filename = 'Checklist_Control_' . str_replace(' ', '_', $kategori) . '_' . str_replace(' ', '_', $departemen) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

    // ─── Helper: build Excel untuk all-categories / all-summary ──────────
    private function streamKontrolAllExcel(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $dataStyle = [
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];

        $sheetIdx = 0;
        $allGrids = $data['allGrids'] ?? [];
        foreach ($allGrids as $gridData) {
            $kategori = $gridData['kategori'] ?? 'Sheet';
            $grid     = $gridData['grid']     ?? [];

            if ($sheetIdx === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }
            $sheet->setTitle(substr($kategori, 0, 31));

            $titleRow = 1;
            $sheet->mergeCells('A' . $titleRow . ':J' . $titleRow);
            $sheet->setCellValue('A' . $titleRow, 'CHECKLIST CONTROL - ' . strtoupper($kategori));
            $sheet->getStyle('A' . $titleRow)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $titleRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $hRow = 3;
            $headers = ['No', 'Mesin', 'W1', 'W2', 'W3', 'W4', 'W5', 'Out of Plan', 'Ulasan', 'PIC'];
            $col = 'A';
            foreach ($headers as $h) {
                $sheet->setCellValue($col . $hRow, $h);
                $col++;
            }
            $sheet->getStyle('A' . $hRow . ':J' . $hRow)->applyFromArray($headerStyle);

            $row = 4;
            $no  = 1;
            foreach ($grid as $mesinId => $mesinData) {
                $noMesin = $mesinData['no_mesin'] ?? '-';
                $checks  = $mesinData['checks']   ?? [];
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $noMesin);
                for ($w = 1; $w <= 5; $w++) {
                    $check  = $checks[$w] ?? null;
                    $status = $check['status_check'] ?? '';
                    if ($status === 'OK') $status = 'V';
                    elseif ($status === 'Perlu Tindakan') $status = 'Δ';
                    elseif ($status === 'Tidak Ada') $status = 'X';
                    $cLet = chr(ord('C') + ($w - 1));
                    $sheet->setCellValue($cLet . $row, $status);
                }
                $sheet->setCellValue('H' . $row, $mesinData['out_of_plan'] ?? '');
                $sheet->setCellValue('I' . $row, $mesinData['ulasan'] ?? '');
                $sheet->setCellValue('J' . $row, $mesinData['pic_nama'] ?? '');
                $row++;
            }
            if ($row > 4) {
                $sheet->getStyle('A4:J' . ($row - 1))->applyFromArray($dataStyle);
            }
            foreach (range('A', 'J') as $c) {
                $sheet->getColumnDimension($c)->setAutoSize(true);
            }
            $sheetIdx++;
        }

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'Checklist_Control_' . str_replace(' ', '_', $data['departemen'] ?? 'Semua') . '_' . ($data['bulan'] ?? date('Y-m')) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
