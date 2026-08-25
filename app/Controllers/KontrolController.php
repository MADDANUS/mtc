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
        $data    = $service->summary($this->request);
        $this->streamKontrolSummaryExcel($data);
    }

    // ─── Helper: build Excel untuk 1 kategori ────────────────────────────
            private function streamKontrolSummaryExcel(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary Checklist Control');

        $bulan = $data['bulan'] ?? date('Y-m');
        $bulanList = $data['bulanList'] ?? [];
        $bulanLabel = $bulanList[$bulan] ?? $bulan;
        $summaryRows = $data['summaryRows'] ?? [];

        // Title
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'RINGKASAN CHECKLIST CONTROL');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'BULAN: ' . strtoupper($bulanLabel));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header style
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        // Data style
        $dataStyle = [
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];

        // Headers
        $row = 4;
        $headers = ['PLANT', 'DEPARTEMEN', 'LINE', 'KATEGORI', 'PROGRES PENGECEKAN', 'STATUS APPROVAL', 'BULAN'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $row, $h);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(30);

        // Data
        $row++;
        if (empty($summaryRows)) {
            $sheet->mergeCells("A{$row}:G{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada data ditemukan.');
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($dataStyle);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            foreach ($summaryRows as $r) {
                $sheet->setCellValue('A' . $row, $r['plant'] ?? '');
                $sheet->setCellValue('B' . $row, $r['departemen'] ?? '');
                $sheet->setCellValue('C' . $row, $r['line'] ?? '');
                $sheet->setCellValue('D' . $row, $r['kategori'] ?? '');
                
                // Progress
                $checked = $r['checked'] ?? 0;
                $total = $r['total'] ?? 0;
                $percent = $r['percent'] ?? 0;
                $sheet->setCellValue('E' . $row, "{$checked}/{$total} ({$percent}%)");
                
                // Status
                $sheet->setCellValue('F' . $row, $r['statusText'] ?? '');
                
                // Bulan
                $sheet->setCellValue('G' . $row, $bulanLabel);

                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($dataStyle);
                
                // Color codes for status and progress
                if ($percent == 100) {
                    $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FF198754'); // Success green
                } else {
                    $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FF0D6EFD'); // Primary blue
                }

                $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Checklist_Control_Ringkasan_' . $bulan . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
private function streamKontrolExcel(array $data): void
    {
        $wrapperData = [
            'bulan'      => $data['bulan'] ?? '',
            'departemen' => $data['departemen'] ?? '',
            'line'       => $data['line'] ?? '',
            'allGrids'   => [$data]
        ];
        $this->streamKontrolAllExcel($wrapperData);
    }

    // ─── Helper: build Excel untuk all-categories / all-summary ──────────
    private function streamKontrolAllExcel(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheetIdx = 0;
        $allGrids = $data['allGrids'] ?? [];
        $bulanStr = $data['bulan'] ?? '';
        $departemen = $data['departemen'] ?? '';
        $line = $data['line'] ?? '';

        $borderStyle = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
        $headerFill = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF2F2F2']];

        foreach ($allGrids as $gridData) {
            $kategori = $gridData['kategori'] ?? 'Sheet';
            $grid     = $gridData['grid']     ?? [];
            $hasSchedule = $gridData['hasSchedule'] ?? false;
            $columnDates = $gridData['columnDates'] ?? [];
            $approvalData = $gridData['approvalData'] ?? [];
            $itemLokasi = $gridData['departemen'] ?? $departemen;
            $itemLine = $gridData['line'] ?? $line;

            if ($sheetIdx === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }
            $sheet->setTitle(substr($kategori, 0, 31));

            // Setup columns A to I (9 columns)
            $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
            $sheet->getColumnDimension('A')->setWidth(5);   // NO
            $sheet->getColumnDimension('B')->setWidth(25);  // MESIN
            foreach (['C','D','E','F','G'] as $col) {
                $sheet->getColumnDimension($col)->setWidth(10); // WAKTU
            }
            $sheet->getColumnDimension('H')->setWidth(15);  // Out of Plan
            $sheet->getColumnDimension('I')->setWidth(30);  // ULASAN

            // Row 1-4: Logo (A1:A4)
            $sheet->mergeCells("A1:A4");
            $logoPath = FCPATH . 'uploads/nsi_logo.png';
            if (file_exists($logoPath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo NSI');
                $drawing->setPath($logoPath);
                $drawing->setCoordinates('A1');
                $drawing->setHeight(85);
                $drawing->setOffsetX(25);
                $drawing->setOffsetY(10);
                $drawing->setWorksheet($sheet);
            }

            // Row 1: Title
            $sheet->mergeCells("B1:I1");
            $sheet->setCellValue('B1', 'CHECKLIST CONTROL');
            $sheet->getStyle("B1")->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle("B1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            // Row 2: Subtitle
            $sheet->mergeCells("B2:I2");
            $deptText = isset($gridData['plant']) ? strtoupper($gridData['plant']) . ' - ' : '';
            $deptText .= strtoupper($itemLokasi) . ($itemLine ? ' / ' . strtoupper($itemLine) : '');
            $sheet->setCellValue('B2', strtoupper($kategori) . " (" . $deptText . ")");
            $sheet->getStyle("B2")->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle("B2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            // Row 3: DOC labels
            $sheet->mergeCells("B3:E3"); $sheet->setCellValue('B3', 'NO. DOCUMENT');
            $sheet->mergeCells("F3:I3"); $sheet->setCellValue('F3', 'NO REVISI');
            $sheet->getStyle("B3:I3")->getFont()->setBold(true);
            $sheet->getStyle("B3:I3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            // Row 4: DOC values
            $sheet->mergeCells("B4:E4"); $sheet->setCellValue('B4', 'FM-MTN-09');
            $sheet->mergeCells("F4:I4"); $sheet->setCellValue('F4', '0');
            $sheet->getStyle("B4:I4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            // Row 5: Rev
            $sheet->mergeCells("A5:I5");
            $sheet->setCellValue('A5', 'Rev.:0/2911/24');
            $sheet->getStyle("A5")->getFont()->setSize(9);
            
            $sheet->getStyle("A1:I5")->applyFromArray($borderStyle);

            // TABLE HEADERS
            $row = 7;
            $sheet->mergeCells("A{$row}:A" . ($row+2)); $sheet->setCellValue("A{$row}", 'NO');
            $sheet->mergeCells("B{$row}:B" . ($row+2)); $sheet->setCellValue("B{$row}", 'MESIN');
            $sheet->mergeCells("C{$row}:G{$row}"); $sheet->setCellValue("C{$row}", 'WAKTU');
            $sheet->mergeCells("H{$row}:H" . ($row+2)); $sheet->setCellValue("H{$row}", 'Out of Plan');
            $sheet->mergeCells("I{$row}:I" . ($row+2)); $sheet->setCellValue("I{$row}", 'ULASAN');

            $row++;
            $sheet->mergeCells("C{$row}:G{$row}"); 
            $sheet->setCellValue("C{$row}", strtoupper(format_bulan_indo($bulanStr)));
            
            $row++;
            for ($col = 1; $col <= 5; $col++) {
                $cellLetter = chr(ord('B') + $col);
                $val = ($hasSchedule && !empty($columnDates[$col])) ? date('d', strtotime($columnDates[$col])) : $col;
                $sheet->setCellValue("{$cellLetter}{$row}", $val);
            }
            
            $sheet->getStyle("A7:I{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A7:I{$row}")->getFill()->applyFromArray($headerFill);
            $sheet->getStyle("A7:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A7:I{$row}")->applyFromArray($borderStyle);

            $row++;
            $startDetailRow = $row;
            
            if (empty($grid)) {
                $sheet->mergeCells("A{$row}:I{$row}");
                $sheet->setCellValue("A{$row}", 'Belum ada data mesin terdaftar di ' . $itemLokasi);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($borderStyle);
                $row++;
            } else {
                $no = 1;
                foreach ($grid as $mesinId => $mesinData) {
                    $sheet->mergeCells("A{$row}:A".($row+1));
                    $sheet->setCellValue("A{$row}", $no++);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);

                    $noMesin = $mesinData['mesin']['no_mesin'] ?? '-';
                    $jenis = $mesinData['mesin']['jenis'] ?? '';
                    $sheet->setCellValue("B{$row}", ($itemLokasi === 'MFG 2') ? $noMesin : ($jenis ? $jenis . ' ' . $noMesin : $noMesin));
                    $sheet->getStyle("B{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("B{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                    $periodes = $mesinData['periodes'] ?? [];
                    for ($p = 1; $p <= 5; $p++) {
                        $cellLetter = chr(ord('B') + $p);
                        $status = isset($periodes[$p]) ? $periodes[$p]['status_check'] : '';
                        $sheet->setCellValue("{$cellLetter}{$row}", $status);
                        if ($status === 'V') $sheet->getStyle("{$cellLetter}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FF008000');
                        elseif ($status === 'Δ') $sheet->getStyle("{$cellLetter}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFB8860B');
                        elseif ($status === 'X') $sheet->getStyle("{$cellLetter}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFFF0000');
                        $sheet->getStyle("{$cellLetter}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    }

                    $oop = !empty($mesinData['out_of_plan']) ? "Out of Plan\n" . format_tanggal_indo($mesinData['out_of_plan']) : '-';
                    $sheet->mergeCells("H{$row}:H".($row+1));
                    $sheet->setCellValue("H{$row}", $oop);
                    $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                    if ($oop !== '-') $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB('FFFF0000')->setBold(true);

                    $ulasan = $mesinData['ulasan'] ?? '-';
                    $sheet->mergeCells("I{$row}:I".($row+1));
                    
                    // Check for images
                    $hasImage = false;
                    if (!empty($mesinData['photos'])) {
                        $imgText = $ulasan . "\n\n\n\n\n";
                        $sheet->setCellValue("I{$row}", $imgText);
                        $offsetX = 5;
                        foreach ($mesinData['photos'] as $idx => $ph) {
                            $imgPath = FCPATH . 'uploads/abnormal/' . $ph;
                            if (file_exists($imgPath)) {
                                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                $drawing->setName('Foto Abnormal ' . $idx);
                                $drawing->setPath($imgPath);
                                $drawing->setCoordinates("I{$row}");
                                $drawing->setHeight(50);
                                $drawing->setOffsetX($offsetX);
                                $drawing->setOffsetY(25);
                                $drawing->setWorksheet($sheet);
                                $offsetX += 60;
                                $hasImage = true;
                            }
                        }
                    } else {
                        $sheet->setCellValue("I{$row}", $ulasan);
                    }
                    $sheet->getStyle("I{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                    if ($hasImage) $sheet->getRowDimension($row)->setRowHeight(85);

                    $row++;
                    
                    // PIC ROW
                    $sheet->setCellValue("B{$row}", 'PIC');
                    $sheet->getStyle("B{$row}")->getFont()->setSize(9)->getColor()->setARGB('FF555555');
                    for ($p = 1; $p <= 5; $p++) {
                        $cellLetter = chr(ord('B') + $p);
                        $pic = isset($periodes[$p]) ? $periodes[$p]['pic_nama'] : '';
                        $picParts = explode(' - ', $pic);
                        $sheet->setCellValue("{$cellLetter}{$row}", end($picParts));
                        $sheet->getStyle("{$cellLetter}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("{$cellLetter}{$row}")->getFont()->setSize(8);
                    }
                    
                    $sheet->getStyle("A".($row-1).":I{$row}")->applyFromArray($borderStyle);
                    // Remove top border for the PIC row cells
                    $sheet->getStyle("B{$row}:G{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_NONE);
                    $row++;
                }
            }

            $row++;

            // KETERANGAN CHECK LIST & SIGNATURES (Side by side)
            $row += 2;

            // 1. KETERANGAN CHECK LIST (Columns A-B, total width 37)
            $sheet->mergeCells("A{$row}:B{$row}"); 
            $sheet->setCellValue("A{$row}", 'KETERANGAN CHECK LIST');
            $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $r = $row + 1;
            $sheet->setCellValue("A{$r}", 'V'); 
            $sheet->getStyle("A{$r}")->getFont()->setBold(true);
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("B{$r}", ': OK'); $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $r++;
            $sheet->setCellValue("A{$r}", 'Δ'); 
            $sheet->getStyle("A{$r}")->getFont()->setBold(true);
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("B{$r}", ': PERLU TINDAKAN'); $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $r++;
            $sheet->setCellValue("A{$r}", 'X'); 
            $sheet->getStyle("A{$r}")->getFont()->setBold(true);
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("B{$r}", ': TIDAK ADA'); $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $sheet->getStyle("A{$row}:B{$r}")->applyFromArray($borderStyle);

            // 2. SIGNATURES (Columns C-I, split symmetrically: C-E, F-H, I)
            $c1 = 'C'; $c1e = 'E'; // width 30
            $c2 = 'F'; $c2e = 'H'; // width 35
            $c3 = 'I'; $c3e = 'I'; // width 30

            // Row 1: PREPARED / APPROVED / APPROVED
            $sheet->mergeCells("{$c1}{$row}:{$c1e}{$row}"); $sheet->setCellValue("{$c1}{$row}", "PREPARED");
            $sheet->mergeCells("{$c2}{$row}:{$c2e}{$row}"); $sheet->setCellValue("{$c2}{$row}", "APPROVED");
            $sheet->setCellValue("{$c3}{$row}", "APPROVED");
            $sheet->getStyle("{$c1}{$row}:{$c3e}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$c1}{$row}:{$c3e}{$row}")->getFont()->setBold(true);

            // Row 2: Roles
            $row2 = $row + 1;
            $sheet->mergeCells("{$c1}{$row2}:{$c1e}{$row2}"); $sheet->setCellValue("{$c1}{$row2}", "INSPECTOR");
            $sheet->mergeCells("{$c2}{$row2}:{$c2e}{$row2}"); $sheet->setCellValue("{$c2}{$row2}", "SEC.HEAD PRODUKSI");
            $sheet->setCellValue("{$c3}{$row2}", "SEC.HEAD MTC");
            $sheet->getStyle("{$c1}{$row2}:{$c3e}{$row2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$c1}{$row2}:{$c3e}{$row2}")->getFont()->setBold(true);

            // Row 3 to 5: Space and [ Disetujui ]
            $rowSpace = $row + 2;
            $sheet->mergeCells("{$c1}{$rowSpace}:{$c1e}".($rowSpace+2));
            $sheet->mergeCells("{$c2}{$rowSpace}:{$c2e}".($rowSpace+2));
            $sheet->mergeCells("{$c3}{$rowSpace}:{$c3e}".($rowSpace+2));

            if (isset($approvalData['approved_l1_by'])) {
                $sheet->setCellValue("{$c1}{$rowSpace}", "[ Disetujui ]");
            }
            if (isset($approvalData['approved_l2_by'])) {
                $sheet->setCellValue("{$c2}{$rowSpace}", "[ Disetujui ]");
            }
            if (isset($approvalData['approved_final_by'])) {
                $sheet->setCellValue("{$c3}{$rowSpace}", "[ Disetujui ]");
            }
            $sheet->getStyle("{$c1}{$rowSpace}:{$c3e}".($rowSpace+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);

            // Row 6: Names & Dates
            $rowName = $row + 5;
            $sheet->mergeCells("{$c1}{$rowName}:{$c1e}{$rowName}");
            $picText = isset($approvalData['l1_name']) ? strtoupper($approvalData['l1_name']) : "(...........................)";
            if (isset($approvalData['approved_l1_by']) && isset($approvalData['approved_l1_at'])) {
                $picText .= "\nTgl: " . date('d/m/Y H:i', strtotime($approvalData['approved_l1_at']));
            }
            $sheet->setCellValue("{$c1}{$rowName}", $picText);

            $sheet->mergeCells("{$c2}{$rowName}:{$c2e}{$rowName}");
            $l2Text = isset($approvalData['l2_name']) ? strtoupper($approvalData['l2_name']) : "(...........................)";
            if (isset($approvalData['approved_l2_by']) && isset($approvalData['approved_l2_at'])) {
                $l2Text .= "\nTgl: " . date('d/m/Y H:i', strtotime($approvalData['approved_l2_at']));
            }
            $sheet->setCellValue("{$c2}{$rowName}", $l2Text);

            $finalText = isset($approvalData['final_name']) ? strtoupper($approvalData['final_name']) : "(...........................)";
            if (isset($approvalData['approved_final_by']) && isset($approvalData['approved_final_at'])) {
                $finalText .= "\nTgl: " . date('d/m/Y H:i', strtotime($approvalData['approved_final_at']));
            }
            $sheet->setCellValue("{$c3}{$rowName}", $finalText);

            $sheet->getStyle("{$c1}{$rowName}:{$c3e}{$rowName}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            $sheet->getStyle("{$c1}{$rowName}:{$c3e}{$rowName}")->getFont()->setBold(true);
            $sheet->getRowDimension($rowName)->setRowHeight(30);

            // Borders for Signatures
            $sheet->getStyle("{$c1}{$row}:{$c1e}{$rowName}")->applyFromArray($borderStyle);
            $sheet->getStyle("{$c2}{$row}:{$c2e}{$rowName}")->applyFromArray($borderStyle);
            $sheet->getStyle("{$c3}{$row}:{$c3e}{$rowName}")->applyFromArray($borderStyle);
            
            $sheet->getStyle("{$c1}{$row}:{$c3e}{$row}")->applyFromArray($borderStyle);
            $sheet->getStyle("{$c1}{$row2}:{$c3e}{$row2}")->applyFromArray($borderStyle);
            $sheet->getStyle("{$c1}{$rowSpace}:{$c3e}".($rowSpace+2))->applyFromArray($borderStyle);
            $sheet->getStyle("{$c1}{$rowName}:{$c3e}{$rowName}")->applyFromArray($borderStyle);

            $sheetIdx++;
        }

        $filename = 'Checklist_Control_' . str_replace(' ', '_', $kategori) . '_' . str_replace(' ', '_', $departemen) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
