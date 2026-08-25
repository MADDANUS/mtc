<?php
function buildKontrolExcelSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $item, $bulan, $departemen, $line)
{
    $kategori = $item['kategori'] ?? 'CNC';
    $grid = $item['grid'] ?? [];
    $hasSchedule = $item['hasSchedule'] ?? false;
    $columnDates = $item['columnDates'] ?? [];
    $approvalData = $item['approvalData'] ?? [];
    $itemLokasi = $item['departemen'] ?? $departemen;
    $itemLine = $item['line'] ?? $line;

    $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(25);
    foreach (['C','D','E','F','G'] as $c) { $sheet->getColumnDimension($c)->setWidth(10); }
    $sheet->getColumnDimension('H')->setWidth(15);
    $sheet->getColumnDimension('I')->setWidth(30);

    $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
    $borderNone = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE]]];

    // KOP CHECKLIST CONTROL
    $sheet->mergeCells("A1:A4");
    $logoPath = FCPATH . 'uploads/nsi_logo.png';
    if (file_exists($logoPath)) {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo NSI');
        $drawing->setPath($logoPath);
        $drawing->setCoordinates('A1');
        $drawing->setHeight(60);
        $drawing->setOffsetX(10);
        $drawing->setOffsetY(10);
        $drawing->setWorksheet($sheet);
    }
    
    $sheet->mergeCells("B1:I1");
    $sheet->setCellValue('B1', 'CHECKLIST CONTROL');
    $sheet->getStyle("B1")->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle("B1")->getAlignment()->setHorizontal('center')->setVertical('center');
    
    $sheet->mergeCells("B2:I2");
    $deptText = isset($item['plant']) ? strtoupper($item['plant']) . ' - ' : '';
    $deptText .= strtoupper($itemLokasi) . ($itemLine ? ' / ' . strtoupper($itemLine) : '');
    $sheet->setCellValue('B2', strtoupper($kategori) . " (" . $deptText . ")");
    $sheet->getStyle("B2")->getFont()->setBold(true)->setSize(13);
    $sheet->getStyle("B2")->getAlignment()->setHorizontal('center')->setVertical('center');
    
    $sheet->mergeCells("B3:E3"); $sheet->setCellValue('B3', 'NO. DOCUMENT');
    $sheet->mergeCells("F3:I3"); $sheet->setCellValue('F3', 'NO REVISI');
    $sheet->getStyle("B3:I3")->getFont()->setBold(true);
    
    $sheet->mergeCells("B4:E4"); $sheet->setCellValue('B4', 'FM-MTN-09');
    $sheet->mergeCells("F4:I4"); $sheet->setCellValue('F4', '0');
    
    $sheet->mergeCells("A5:I5"); $sheet->setCellValue('A5', ' Rev.:0/2911/24');
    $sheet->getStyle("A5")->getFont()->setSize(9);
    $sheet->getStyle("A1:I5")->applyFromArray($borderThin);
    $sheet->getStyle("B3:I4")->getAlignment()->setHorizontal('center')->setVertical('center');

    // TABEL HEADER
    $row = 7;
    $sheet->mergeCells("A{$row}:A" . ($row+2)); $sheet->setCellValue("A{$row}", 'NO');
    $sheet->mergeCells("B{$row}:B" . ($row+2)); $sheet->setCellValue("B{$row}", 'MESIN');
    $sheet->mergeCells("C{$row}:G{$row}"); $sheet->setCellValue("C{$row}", 'WAKTU');
    $sheet->mergeCells("H{$row}:H" . ($row+2)); $sheet->setCellValue("H{$row}", 'Out of Plan');
    $sheet->mergeCells("I{$row}:I" . ($row+2)); $sheet->setCellValue("I{$row}", 'ULASAN');

    $row++;
    $sheet->mergeCells("C{$row}:G{$row}"); $sheet->setCellValue("C{$row}", strtoupper(format_bulan_indo($bulan)));
    
    $row++;
    for ($col = 1; $col <= 5; $col++) {
        $cellLetter = chr(ord('B') + $col);
        $val = ($hasSchedule && !empty($columnDates[$col])) ? date('d', strtotime($columnDates[$col])) : $col;
        $sheet->setCellValue("{$cellLetter}{$row}", $val);
    }
    
    $sheet->getStyle("A7:I{$row}")->getFont()->setBold(true);
    $sheet->getStyle("A7:I{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
    $sheet->getStyle("A7:I{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getStyle("A7:I{$row}")->applyFromArray($borderThin);

    $row++;
    $startDetailRow = $row;
    
    if (empty($grid)) {
        $sheet->mergeCells("A{$row}:I{$row}");
        $sheet->setCellValue("A{$row}", 'Belum ada data mesin terdaftar di ' . $itemLokasi);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($borderThin);
        $row++;
    } else {
        $no = 1;
        foreach ($grid as $mesinId => $mesinData) {
            // MAIN ROW
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);

            $noMesin = $mesinData['no_mesin'] ?? '-';
            $jenis = $mesinData['jenis'] ?? '';
            $sheet->setCellValue("B{$row}", ($itemLokasi === 'MFG 2') ? $noMesin : ($jenis ? $jenis . ' ' . $noMesin : $noMesin));
            $sheet->getStyle("B{$row}")->getFont()->setBold(true);

            $periodes = $mesinData['periodes'] ?? [];
            for ($p = 1; $p <= 5; $p++) {
                $cellLetter = chr(ord('B') + $p);
                $status = isset($periodes[$p]) ? $periodes[$p]['status_check'] : '';
                $sheet->setCellValue("{$cellLetter}{$row}", $status);
                if ($status === 'V') $sheet->getStyle("{$cellLetter}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FF008000');
                elseif ($status === 'Δ') $sheet->getStyle("{$cellLetter}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFB8860B');
                elseif ($status === 'X') $sheet->getStyle("{$cellLetter}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFFF0000');
                $sheet->getStyle("{$cellLetter}{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
            }

            $oop = !empty($mesinData['out_of_plan']) ? "Out of Plan\n" . format_tanggal_indo($mesinData['out_of_plan']) : '-';
            $sheet->setCellValue("H{$row}", $oop);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
            if ($oop !== '-') $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB('FFFF0000');

            $ulasan = $mesinData['ulasan'] ?? '-';
            $sheet->setCellValue("I{$row}", $ulasan);
            $sheet->getStyle("I{$row}")->getAlignment()->setVertical('top')->setWrapText(true);
            
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
                        $drawing->setOffsetY(20);
                        $drawing->setWorksheet($sheet);
                        $offsetX += 60;
                        $hasImage = true;
                    }
                }
            }
            if ($hasImage) $sheet->getRowDimension($row)->setRowHeight(70);

            $row++;
            
            // PIC ROW
            $sheet->setCellValue("B{$row}", 'PIC');
            $sheet->getStyle("B{$row}")->getFont()->setSize(9)->getColor()->setARGB('FF555555');
            for ($p = 1; $p <= 5; $p++) {
                $cellLetter = chr(ord('B') + $p);
                $pic = isset($periodes[$p]) ? $periodes[$p]['pic_nama'] : '';
                $picParts = explode(' - ', $pic);
                $sheet->setCellValue("{$cellLetter}{$row}", end($picParts));
                $sheet->getStyle("{$cellLetter}{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
                $sheet->getStyle("{$cellLetter}{$row}")->getFont()->setSize(8);
            }
            
            // Apply border per machine block
            $sheet->getStyle("A".($row-1).":I{$row}")->applyFromArray($borderThin);
            $sheet->getStyle("A{$row}")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
            $sheet->getStyle("H{$row}:I{$row}")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
            $row++;
        }
    }

    $row++;

    // KETERANGAN CHECK LIST
    $sheet->mergeCells("F{$row}:H{$row}"); $sheet->setCellValue("F{$row}", 'KETERANGAN CHECK LIST');
    $sheet->getStyle("F{$row}:H{$row}")->getFont()->setBold(true);
    $sheet->getStyle("F{$row}:H{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
    $sheet->getStyle("F{$row}:H{$row}")->getAlignment()->setHorizontal('center');
    $row++;
    
    $sheet->setCellValue("F{$row}", 'V'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal('center');
    $sheet->setCellValue("G{$row}", ':');
    $sheet->setCellValue("H{$row}", 'OK'); $sheet->getStyle("H{$row}")->getFont()->setBold(true);
    $row++;
    
    $sheet->setCellValue("F{$row}", 'Δ'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal('center');
    $sheet->setCellValue("G{$row}", ':');
    $sheet->setCellValue("H{$row}", 'PERLU TINDAKAN'); $sheet->getStyle("H{$row}")->getFont()->setBold(true);
    $row++;
    
    $sheet->setCellValue("F{$row}", 'X'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal('center');
    $sheet->setCellValue("G{$row}", ':');
    $sheet->setCellValue("H{$row}", 'TIDAK ADA'); $sheet->getStyle("H{$row}")->getFont()->setBold(true);
    $sheet->getStyle("F".($row-3).":H{$row}")->applyFromArray($borderThin);

    $row += 2;

    // SIGNATURES
    $sheet->mergeCells("A{$row}:C{$row}"); $sheet->setCellValue("A{$row}", "Dibuat Oleh\n\nPIC LINE\n\n\n" . (isset($approvalData['approved_l1_by']) ? "[ Disetujui ]\n" . ($approvalData['l1_name'] ?? '') : "\n( ........................................ )") . "\nTanggal: " . (isset($approvalData['approved_l1_at']) ? format_tanggal_indo($approvalData['approved_l1_at'], false, true) : '( ......................... )'));
    $sheet->mergeCells("D{$row}:F{$row}"); $sheet->setCellValue("D{$row}", "Disetujui Oleh\n\nSECTION HEAD PRODUKSI\n\n\n" . (isset($approvalData['approved_l2_by']) ? "[ Disetujui ]\n" . ($approvalData['l2_name'] ?? '') : "\n( ........................................ )") . "\nTanggal: " . (isset($approvalData['approved_l2_at']) ? format_tanggal_indo($approvalData['approved_l2_at'], false, true) : '( ......................... )'));
    $sheet->mergeCells("G{$row}:I{$row}"); $sheet->setCellValue("G{$row}", "Disetujui Oleh\n\nSECTION HEAD MTC\n\n\n" . (isset($approvalData['approved_final_by']) ? "[ Disetujui ]\n" . ($approvalData['final_name'] ?? '') : "\n( ........................................ )") . "\nTanggal: " . (isset($approvalData['approved_final_at']) ? format_tanggal_indo($approvalData['approved_final_at'], false, true) : '( ......................... )'));
    
    $sheet->getStyle("A{$row}:I{$row}")->getAlignment()->setWrapText(true)->setHorizontal('center')->setVertical('center');
    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray($borderThin);
    $sheet->getStyle("D{$row}:F{$row}")->applyFromArray($borderThin);
    $sheet->getStyle("G{$row}:I{$row}")->applyFromArray($borderThin);
    $sheet->getRowDimension($row)->setRowHeight(110);
}
