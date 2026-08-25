<?php

$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// Remove the 32-column grid helper functions.
$c = preg_replace('/private function build32ColMicroGrid.*?(?=\n    private function buildPreventiveExcelSheet)/s', '', $c);

$newHelpers = <<<'PHP'
    private function setupPdfLikeExcel(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $isMfg2) {
        $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        
        if ($isMfg2) {
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(30);
            $sheet->getColumnDimension('G')->setWidth(0); // unused
        } else {
            $sheet->getColumnDimension('E')->setWidth(20);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(30);
        }
    }

    private function buildPdfLikeHeader(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $header, $isPreventive, $isMfg2) {
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
        $borderOutline = ['borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];

        $kategori = strtoupper($header['kategori'] ?? 'MESIN CNC');
        $title = $isPreventive ? "PREVENTIVE MAINTENANCE REPORT - $kategori" : "INSPECTION REPORT - $kategori";
        $docNo = $isPreventive ? 'FM-MTN-08' : 'FM-MTN-10';

        $lastCol = $isMfg2 ? 'F' : 'G';

        // Row 1-4: Logo & Title
        $sheet->mergeCells("A1:A4");
        $logoPath = FCPATH . 'uploads/nsi_logo.png';
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo NSI');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setHeight(80);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }
        $sheet->getStyle("A1:A4")->applyFromArray($borderThin);

        $sheet->mergeCells("B1:{$lastCol}1");
        $sheet->setCellValue('B1', $title);
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('B1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF92B0D6');
        $sheet->getStyle('B1')->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle("B1:{$lastCol}1")->applyFromArray($borderThin);

        // Merge cells for DOcument & Revision
        if ($isMfg2) {
            $sheet->mergeCells("B2:C2"); $sheet->setCellValue('B2', 'NO. DOCUMENT');
            $sheet->mergeCells("D2:F2"); $sheet->setCellValue('D2', 'NO REVISI');
            $sheet->mergeCells("B3:C3"); $sheet->setCellValue('B3', $docNo);
            $sheet->mergeCells("D3:F3"); $sheet->setCellValue('D3', '0');
        } else {
            $sheet->mergeCells("B2:D2"); $sheet->setCellValue('B2', 'NO. DOCUMENT');
            $sheet->mergeCells("E2:G2"); $sheet->setCellValue('E2', 'NO REVISI');
            $sheet->mergeCells("B3:D3"); $sheet->setCellValue('B3', $docNo);
            $sheet->mergeCells("E3:G3"); $sheet->setCellValue('E3', '0');
        }
        $sheet->getStyle("B2:{$lastCol}3")->getFont()->setBold(true);
        $sheet->getStyle("B2:{$lastCol}3")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("B2:{$lastCol}3")->applyFromArray($borderThin);

        $sheet->mergeCells("B4:{$lastCol}4");
        $sheet->setCellValue('B4', 'Rev.:0/291124');
        $sheet->getStyle('B4')->getFont()->setSize(9);
        $sheet->getStyle("B4:{$lastCol}4")->applyFromArray($borderThin);

        // Row 5: MAIN PIC, NO MACHINE, DATE
        $namaTopParts = explode(' - ', $header['nama_staff'] ?? $header['nama_pic'] ?? '');
        $namaTopOnly = end($namaTopParts);
        $waktuMulai = !empty($header['waktu_mulai']) ? strtotime($header['waktu_mulai']) : time();
        $waktuSelesai = !empty($header['waktu_selesai']) ? strtotime($header['waktu_selesai']) : null;

        $sheet->setCellValue('A5', 'MAIN PIC');
        $sheet->setCellValue('B5', $namaTopOnly);
        if ($isMfg2) {
            $sheet->mergeCells("C5:D5"); $sheet->setCellValue('C5', 'NO MACHINE');
            $sheet->setCellValue('E5', $header['no_mesin'] ?? '-');
            $sheet->setCellValue('F5', 'DATE'); // Will be appended with date below
            // Since it's 6 cols, we adjust slightly
            $sheet->setCellValue('F5', 'DATE: ' . format_tanggal_indo(date('Y-m-d', $waktuMulai)));
        } else {
            $sheet->setCellValue('C5', 'NO MACHINE');
            $sheet->setCellValue('D5', $header['no_mesin'] ?? '-');
            $sheet->setCellValue('E5', 'DATE');
            $sheet->mergeCells("F5:G5"); $sheet->setCellValue('F5', format_tanggal_indo(date('Y-m-d', $waktuMulai)));
        }
        $sheet->getStyle("A5")->getFont()->setBold(true);
        $sheet->getStyle("C5")->getFont()->setBold(true);
        if (!$isMfg2) $sheet->getStyle("E5")->getFont()->setBold(true);

        // Row 6: SUPPORT PIC, MACHINE TYPE, START TIME
        $sheet->mergeCells("A6:A7"); $sheet->setCellValue('A6', 'SUPPORT PIC');
        $sheet->mergeCells("B6:B7"); $sheet->setCellValue('B6', $header['support_pic'] ?? '-');
        $sheet->getStyle("A6:B7")->getAlignment()->setVertical('top');
        $sheet->getStyle("A6")->getFont()->setBold(true);

        if ($isMfg2) {
            $sheet->mergeCells("C6:D6"); $sheet->setCellValue('C6', 'MACHINE TYPE');
            $sheet->setCellValue('E6', $header['type_mesin'] ?? '-');
            $sheet->setCellValue('F6', 'START: ' . date('H:i:s', $waktuMulai));
        } else {
            $sheet->setCellValue('C6', 'MACHINE TYPE');
            $sheet->setCellValue('D6', $header['type_mesin'] ?? '-');
            $sheet->setCellValue('E6', 'START TIME');
            $sheet->mergeCells("F6:G6"); $sheet->setCellValue('F6', date('H:i:s', $waktuMulai));
            $sheet->getStyle("E6")->getFont()->setBold(true);
        }
        $sheet->getStyle("C6")->getFont()->setBold(true);

        // Row 7: BAR FEEDER TYPE, FINISH TIME
        if (stripos($kategori, 'CNC') !== false) {
            if ($isMfg2) {
                $sheet->mergeCells("C7:D7"); $sheet->setCellValue('C7', 'BAR FEEDER TYPE');
                $sheet->setCellValue('E7', $header['bar_feeder_type'] ?? '-');
            } else {
                $sheet->setCellValue('C7', 'BAR FEEDER TYPE');
                $sheet->setCellValue('D7', $header['bar_feeder_type'] ?? '-');
            }
        }
        $sheet->getStyle("C7")->getFont()->setBold(true);

        if ($isMfg2) {
            $sheet->setCellValue('F7', 'FINISH: ' . ($waktuSelesai ? date('H:i:s', $waktuSelesai) : '-'));
        } else {
            $sheet->setCellValue('E7', 'FINISH TIME');
            $sheet->mergeCells("F7:G7"); $sheet->setCellValue('F7', $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-');
            $sheet->getStyle("E7")->getFont()->setBold(true);
        }
        
        $sheet->getStyle("A5:{$lastCol}7")->applyFromArray($borderThin);
        return 9;
    }

    private function buildPdfLikeTblHeader(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $row, $isMfg2) {
        $lastCol = $isMfg2 ? 'F' : 'G';
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
        
        $sheet->setCellValue("A{$row}", 'NO');
        $sheet->mergeCells("B{$row}:C{$row}"); $sheet->setCellValue("B{$row}", 'ITEM CHECK');
        $sheet->setCellValue("D{$row}", 'POINT CHECK');
        
        if ($isMfg2) {
            $sheet->setCellValue("E{$row}", 'HASIL');
            $sheet->setCellValue("F{$row}", 'ULASAN');
        } else {
            $sheet->setCellValue("E{$row}", 'STANDAR ITEM');
            $sheet->setCellValue("F{$row}", 'HASIL');
            $sheet->setCellValue("G{$row}", 'ULASAN');
        }
        
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($borderThin);
        
        return $row + 1;
    }

    private function buildPdfLikeFooter(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $row, $header, $isMfg2) {
        $lastCol = $isMfg2 ? 'F' : 'G';
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
        $borderOutline = ['borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];

        $row++;
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", 'NOTE AND RECOMMENDATION:');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10);
        $row++;
        
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->setCellValue("A{$row}", $header['note_recommendation'] ?? '-');
        $sheet->getStyle("A{$row}")->getAlignment()->setVertical('top')->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(40);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($borderOutline);
        
        $row++;
        
        // Split bottom block into Keterangan and Signature Block
        // Keterangan in A to B, Signatures in C to G (or E to F if MFG2)
        
        $sigStartCol = 'C';
        $sigEndCol = $lastCol;
        
        // Draw Keterangan Checklist
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'KETERANGAN CHECK LIST');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($borderOutline);
        $rowK = $row + 1;
        $sheet->setCellValue("A{$rowK}", '√ : OK');
        $sheet->setCellValue("B{$rowK}", 'Δ : PERLU TINDAKAN');
        $rowK++;
        $sheet->setCellValue("A{$rowK}", 'X : TIDAK ADA');
        
        // Draw Signature Block exactly like the screenshot:
        // row 1: PREPARED | CHECKED | APPROVED | APPROVED
        // row 2: INSPECTOR | USER | SEC.HEAD MFG 2 | SEC.HEAD MTN
        // row 3-5: Blank (grey?) or just blank. PDF has blank space. Screenshot has grey box with a line.
        // row 6: Names.
        
        // Wait, if it's C to G, we have 5 columns for 4 signatures.
        // Let's use columns for signatures:
        if ($isMfg2) {
            $cols = ['C', 'D', 'E', 'F'];
            foreach($cols as $c) $sheet->getColumnDimension($c)->setWidth(15);
        } else {
            $cols = ['C', 'D', 'E', 'F']; // We can merge E:F or F:G. Let's merge for the last two.
            // Actually, let's just use C, D, E, and merge F:G.
        }
        
        $c1 = $isMfg2 ? 'C' : 'C';
        $c2 = $isMfg2 ? 'D' : 'D';
        $c3 = $isMfg2 ? 'E' : 'E';
        $c4 = $isMfg2 ? 'F' : 'F';
        if (!$isMfg2) {
            $sheet->mergeCells("F{$row}:G{$row}");
            $sheet->mergeCells("F".($row+1).":G".($row+1));
            $sheet->mergeCells("F".($row+2).":G".($row+4));
            $sheet->mergeCells("F".($row+5).":G".($row+5));
        }

        $sheet->setCellValue("{$c1}{$row}", 'PREPARED');
        $sheet->setCellValue("{$c2}{$row}", 'CHECKED');
        $sheet->setCellValue("{$c3}{$row}", 'APPROVED');
        $sheet->setCellValue("{$c4}{$row}", 'APPROVED');
        $sheet->getStyle("{$c1}{$row}:{$lastCol}{$row}")->getFont()->setBold(true)->setSize(9);
        
        $row2 = $row + 1;
        $sheet->setCellValue("{$c1}{$row2}", 'INSPECTOR');
        $sheet->setCellValue("{$c2}{$row2}", 'USER');
        $sheet->setCellValue("{$c3}{$row2}", 'SEC.HEAD MFG 2');
        $sheet->setCellValue("{$c4}{$row2}", 'SEC.HEAD MTN');
        
        // Names connected from web
        $picNames = explode(' - ', $header['nama_staff'] ?? $header['nama_pic'] ?? '');
        $picName = end($picNames) ?: '-';

        $l1Names = explode(' - ', $header['approver_l1_nama'] ?? '');
        $l1Name = end($l1Names) ?: '-';

        $l2Names = explode(' - ', $header['approver_l2_nama'] ?? '');
        $l2Name = end($l2Names) ?: '-';

        $finalNames = explode(' - ', $header['approver_nama'] ?? '');
        $finalName = end($finalNames) ?: '-';
        
        $rowSpace = $row + 2;
        $sheet->mergeCells("{$c1}{$rowSpace}:{$c1}".($rowSpace+2));
        $sheet->mergeCells("{$c2}{$rowSpace}:{$c2}".($rowSpace+2));
        $sheet->mergeCells("{$c3}{$rowSpace}:{$c3}".($rowSpace+2));
        // C4 merged earlier if not mfg2
        if ($isMfg2) $sheet->mergeCells("{$c4}{$rowSpace}:{$c4}".($rowSpace+2));
        
        // Paint it grey like screenshot
        $sheet->getStyle("{$c1}{$rowSpace}:{$lastCol}".($rowSpace+2))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');

        $rowName = $row + 5;
        $sheet->setCellValue("{$c1}{$rowName}", strtoupper($picName));
        $sheet->setCellValue("{$c2}{$rowName}", strtoupper($l1Name));
        $sheet->setCellValue("{$c3}{$rowName}", strtoupper($l2Name));
        $sheet->setCellValue("{$c4}{$rowName}", strtoupper($finalName));
        
        $sheet->getStyle("{$c1}{$row}:{$lastCol}{$rowName}")->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle("{$c1}{$row}:{$lastCol}{$rowName}")->applyFromArray($borderThin);
        $sheet->getStyle("{$c1}{$rowName}:{$lastCol}{$rowName}")->getFont()->setBold(true);
    }
PHP;

$newPreventive = <<<'PHP'
    private function buildPreventiveExcelSheet(array $header, array $details, \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $dept = strtolower($header['departemen_check'] ?? $header['departemen'] ?? '');
        $isMfg2 = ($dept === 'mfg 2' || $dept === 'mfg2');
        
        $this->setupPdfLikeExcel($sheet, $isMfg2);
        $row = $this->buildPdfLikeHeader($sheet, $header, true, $isMfg2);
        $row = $this->buildPdfLikeTblHeader($sheet, $row, $isMfg2);
        
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        $lastCol = $isMfg2 ? 'F' : 'G';
        
        $currentBagian = '';
        foreach ($details as $idx => $d) {
            $bagianName = $d['dynamic_section_header'] ?? $d['bagian_check'] ?? 'Lainnya';
            if (!empty($d['is_section_start'])) {
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->setCellValue("A{$row}", strtoupper($d['dynamic_section_header']));
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($borderThin);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
                $currentBagian = $bagianName;
                $row++;
            }
            
            $sheet->setCellValue("A{$row}", $d['dynamic_no'] ?? ($idx + 1));
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
            
            $subItem = $d['sub_item_check'] ?? '';
            $bagian = $d['bagian_check'] ?? '';
            if (!empty($subItem)) {
                $sheet->setCellValue("B{$row}", $bagian);
                $sheet->setCellValue("C{$row}", $subItem);
            } else {
                $sheet->mergeCells("B{$row}:C{$row}");
                $sheet->setCellValue("B{$row}", $bagian);
            }
            
            $sheet->setCellValue("D{$row}", $d['point_check'] ?? '');
            
            if ($isMfg2) {
                $val = $d['hasil_check'] ?? '';
                if ($val === 'V' || $val === '√') { $val = 'V'; $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FF0D6EFD'); $sheet->getStyle("E{$row}")->getFont()->setBold(true); }
                elseif ($val === 'Δ') { $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FFFFA500'); $sheet->getStyle("E{$row}")->getFont()->setBold(true); }
                elseif ($val === 'X') { $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FFDC3545'); $sheet->getStyle("E{$row}")->getFont()->setBold(true); }
                $sheet->setCellValue("E{$row}", $val);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal('center');
                $sheet->setCellValue("F{$row}", $d['ulasan'] ?? '');
            } else {
                $sheet->setCellValue("E{$row}", $d['standard_check'] ?? '');
                
                $val = $d['hasil_check'] ?? '';
                if ($val === 'V' || $val === '√') { $val = 'V'; $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB('FF0D6EFD'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); }
                elseif ($val === 'Δ') { $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB('FFFFA500'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); }
                elseif ($val === 'X') { $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB('FFDC3545'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); }
                $sheet->setCellValue("F{$row}", $val);
                $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal('center');
                
                $sheet->setCellValue("G{$row}", $d['ulasan'] ?? '');
            }
            
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($borderThin);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setVertical('center')->setWrapText(true);
            $row++;
        }
        
        $this->buildPdfLikeFooter($sheet, $row, $header, $isMfg2);
    }
PHP;

$newOverhaul = <<<'PHP'
    private function buildOverhaulExcelSheet(array $header, array $details, \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $dept = strtolower($header['departemen_check'] ?? $header['departemen'] ?? '');
        $isMfg2 = ($dept === 'mfg 2' || $dept === 'mfg2');
        
        $this->setupPdfLikeExcel($sheet, $isMfg2);
        $row = $this->buildPdfLikeHeader($sheet, $header, false, $isMfg2);
        $row = $this->buildPdfLikeTblHeader($sheet, $row, $isMfg2);
        
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        $lastCol = $isMfg2 ? 'F' : 'G';
        
        $currentBagian = '';
        foreach ($details as $idx => $d) {
            $bagianName = $d['dynamic_section_header'] ?? $d['bagian_check'] ?? 'Lainnya';
            if (!empty($d['is_section_start'])) {
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->setCellValue("A{$row}", strtoupper($d['dynamic_section_header']));
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($borderThin);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
                $currentBagian = $bagianName;
                $row++;
            }
            
            $sheet->setCellValue("A{$row}", $d['dynamic_no'] ?? ($idx + 1));
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
            
            $subItem = $d['sub_item_check'] ?? '';
            $bagian = $d['bagian_check'] ?? '';
            if (!empty($subItem)) {
                $sheet->setCellValue("B{$row}", $bagian);
                $sheet->setCellValue("C{$row}", $subItem);
            } else {
                $sheet->mergeCells("B{$row}:C{$row}");
                $sheet->setCellValue("B{$row}", $bagian);
            }
            
            $sheet->setCellValue("D{$row}", $d['point_check'] ?? '');
            
            if ($isMfg2) {
                $val = $d['hasil_check'] ?? '';
                if ($val === 'V' || $val === '√') { $val = 'V'; $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FF0D6EFD'); $sheet->getStyle("E{$row}")->getFont()->setBold(true); }
                elseif ($val === 'Δ') { $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FFFFA500'); $sheet->getStyle("E{$row}")->getFont()->setBold(true); }
                elseif ($val === 'X') { $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FFDC3545'); $sheet->getStyle("E{$row}")->getFont()->setBold(true); }
                $sheet->setCellValue("E{$row}", $val);
                $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal('center');
                $sheet->setCellValue("F{$row}", $d['ulasan'] ?? '');
            } else {
                $sheet->setCellValue("E{$row}", $d['standard_check'] ?? '');
                
                $val = $d['hasil_check'] ?? '';
                if ($val === 'V' || $val === '√') { $val = 'V'; $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB('FF0D6EFD'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); }
                elseif ($val === 'Δ') { $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB('FFFFA500'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); }
                elseif ($val === 'X') { $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB('FFDC3545'); $sheet->getStyle("F{$row}")->getFont()->setBold(true); }
                $sheet->setCellValue("F{$row}", $val);
                $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal('center');
                
                $sheet->setCellValue("G{$row}", $d['ulasan'] ?? '');
            }
            
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($borderThin);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setVertical('center')->setWrapText(true);
            $row++;
        }
        
        $this->buildPdfLikeFooter($sheet, $row, $header, $isMfg2);
    }
PHP;

if (strpos($c, 'private function setupPdfLikeExcel') === false) {
    preg_match('/private function buildPreventiveExcelSheet.*?\{/s', $c, $m, PREG_OFFSET_CAPTURE);
    if (!empty($m)) {
        $c = substr_replace($c, $newHelpers . "\n\n" . $m[0][0], $m[0][1], strlen($m[0][0]));
    }
}

// Replace buildPreventiveExcelSheet
preg_match('/private function buildPreventiveExcelSheet.*?(?=\n    private function buildOverhaulExcelSheet|\n\})/s', $c, $pMatches);
if (!empty($pMatches)) {
    $c = str_replace($pMatches[0], $newPreventive, $c);
}

// Replace buildOverhaulExcelSheet
preg_match('/private function buildOverhaulExcelSheet.*?(?=\n\})/s', $c, $oMatches);
if (!empty($oMatches)) {
    $c = str_replace($oMatches[0], $newOverhaul, $c);
}

file_put_contents($f, $c);
echo "PDF Layout applied!";
