<?php
$f = 'app/Controllers/KontrolController.php';
$c = file_get_contents($f);

// We want to replace streamKontrolAllExcel with a full custom builder.
// First let's find the start of streamKontrolAllExcel.
$startStr = 'private function streamKontrolAllExcel(array $data): void';
$posStart = strpos($c, $startStr);
if ($posStart === false) {
    echo "Could not find streamKontrolAllExcel";
    exit;
}

$newFunc = <<<'PHP'
    private function streamKontrolAllExcel(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheetIdx = 0;
        $allGrids = $data['allGrids'] ?? [];
        $bulanStr = $data['bulan'] ?? '';
        $departemen = $data['departemen'] ?? '';
        $line = $data['line'] ?? '';

        $borderStyle = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
        $borderNone = ['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_NONE]]];
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
                $drawing->setHeight(65);
                $drawing->setOffsetX(10);
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

                    $noMesin = $mesinData['no_mesin'] ?? '-';
                    $jenis = $mesinData['jenis'] ?? '';
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

            // KETERANGAN CHECK LIST
            $sheet->mergeCells("F{$row}:H{$row}"); $sheet->setCellValue("F{$row}", 'KETERANGAN CHECK LIST');
            $sheet->getStyle("F{$row}:H{$row}")->getFont()->setBold(true);
            $sheet->getStyle("F{$row}:H{$row}")->getFill()->applyFromArray($headerFill);
            $sheet->getStyle("F{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
            
            $sheet->setCellValue("F{$row}", 'V'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("G{$row}", ':');
            $sheet->setCellValue("H{$row}", 'OK'); $sheet->getStyle("H{$row}")->getFont()->setBold(true);
            $row++;
            
            $sheet->setCellValue("F{$row}", 'Δ'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("G{$row}", ':');
            $sheet->setCellValue("H{$row}", 'PERLU TINDAKAN'); $sheet->getStyle("H{$row}")->getFont()->setBold(true);
            $row++;
            
            $sheet->setCellValue("F{$row}", 'X'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("G{$row}", ':');
            $sheet->setCellValue("H{$row}", 'TIDAK ADA'); $sheet->getStyle("H{$row}")->getFont()->setBold(true);
            $sheet->getStyle("F".($row-3).":H{$row}")->applyFromArray($borderStyle);

            $row += 2;

            // SIGNATURES
            $sheet->mergeCells("A{$row}:C{$row}"); 
            $sig1 = "Dibuat Oleh\n\nINSPECTOR\n\n\n";
            $sig1 .= (isset($approvalData['approved_l1_by']) ? "[ Disetujui ]\n" . ($approvalData['l1_name'] ?? '') : "\n( ........................................ )");
            $sig1 .= "\nTanggal: " . (isset($approvalData['approved_l1_at']) ? format_tanggal_indo($approvalData['approved_l1_at'], false, true) : '( ......................... )');
            $sheet->setCellValue("A{$row}", $sig1);

            $sheet->mergeCells("D{$row}:F{$row}"); 
            $sig2 = "Disetujui Oleh\n\nSEC.HEAD PRODUKSI\n\n\n";
            $sig2 .= (isset($approvalData['approved_l2_by']) ? "[ Disetujui ]\n" . ($approvalData['l2_name'] ?? '') : "\n( ........................................ )");
            $sig2 .= "\nTanggal: " . (isset($approvalData['approved_l2_at']) ? format_tanggal_indo($approvalData['approved_l2_at'], false, true) : '( ......................... )');
            $sheet->setCellValue("D{$row}", $sig2);

            $sheet->mergeCells("G{$row}:I{$row}"); 
            $sig3 = "Disetujui Oleh\n\nSEC.HEAD MTC\n\n\n";
            $sig3 .= (isset($approvalData['approved_final_by']) ? "[ Disetujui ]\n" . ($approvalData['final_name'] ?? '') : "\n( ........................................ )");
            $sig3 .= "\nTanggal: " . (isset($approvalData['approved_final_at']) ? format_tanggal_indo($approvalData['approved_final_at'], false, true) : '( ......................... )');
            $sheet->setCellValue("G{$row}", $sig3);
            
            $sheet->getStyle("A{$row}:I{$row}")->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$row}:C{$row}")->applyFromArray($borderStyle);
            $sheet->getStyle("D{$row}:F{$row}")->applyFromArray($borderStyle);
            $sheet->getStyle("G{$row}:I{$row}")->applyFromArray($borderStyle);
            $sheet->getRowDimension($row)->setRowHeight(110);

            $sheetIdx++;
        }

        $filename = 'Checklist_Control_All_' . str_replace(' ', '_', $departemen) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
PHP;

$c = substr($c, 0, $posStart) . ltrim($newFunc);
file_put_contents($f, $c);
echo "Rewrote streamKontrolAllExcel!";
