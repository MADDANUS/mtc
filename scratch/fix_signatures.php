<?php

$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

$newFooter = <<<'PHP'
    private function buildFooter32Col(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $row, $header, $isPreventive) {
        $borderStyle = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
        
        $row++;
        // Note and Recommendation
        $sheet->mergeCells("B{$row}:AF{$row}");
        $sheet->setCellValue("B{$row}", "NOTE AND RECOMMENDATION");
        $sheet->getStyle("B{$row}:AF{$row}")->applyFromArray($borderStyle);
        $row++;
        $sheet->mergeCells("B{$row}:AF{$row}");
        $sheet->setCellValue("B{$row}", $header['note_recommendation'] ?? '');
        $sheet->getStyle("B{$row}")->getAlignment()->setVertical('top')->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(40);
        $sheet->getStyle("B{$row}:AF{$row}")->applyFromArray($borderStyle);
        
        $row += 2;
        
        // Keterangan & Signature block side by side
        // Keterangan in B to I
        $sheet->mergeCells("B{$row}:I{$row}");
        $sheet->setCellValue("B{$row}", "KETERANGAN CHECK LIST");
        $sheet->getStyle("B{$row}:I{$row}")->getAlignment()->setHorizontal('center')->setBold(true);
        $sheet->getStyle("B{$row}:I{$row}")->applyFromArray($borderStyle);
        
        $rowK = $row + 1;
        $sheet->mergeCells("B{$rowK}:C{$rowK}"); $sheet->setCellValue("B{$rowK}", "V");
        $sheet->setCellValue("D{$rowK}", ":");
        $sheet->mergeCells("E{$rowK}:I{$rowK}"); $sheet->setCellValue("E{$rowK}", "OK");
        $sheet->getStyle("B{$rowK}:D{$rowK}")->getAlignment()->setHorizontal('center')->setBold(true);
        $rowK++;
        
        $sheet->mergeCells("B{$rowK}:C{$rowK}"); $sheet->setCellValue("B{$rowK}", "Δ");
        $sheet->setCellValue("D{$rowK}", ":");
        $sheet->mergeCells("E{$rowK}:I{$rowK}"); $sheet->setCellValue("E{$rowK}", "PERLU TINDAKAN");
        $sheet->getStyle("B{$rowK}:D{$rowK}")->getAlignment()->setHorizontal('center')->setBold(true);
        $rowK++;
        
        $sheet->mergeCells("B{$rowK}:C{$rowK}"); $sheet->setCellValue("B{$rowK}", "X");
        $sheet->setCellValue("D{$rowK}", ":");
        $sheet->mergeCells("E{$rowK}:I{$rowK}"); $sheet->setCellValue("E{$rowK}", "TIDAK ADA");
        $sheet->getStyle("B{$rowK}:D{$rowK}")->getAlignment()->setHorizontal('center')->setBold(true);
        
        $sheet->getStyle("B".($row+1).":I{$rowK}")->applyFromArray($borderStyle);

        // Signatures in K to AF based on Document Type
        if ($isPreventive) {
            // Preventive: 2 Columns (M:R, U:Z)
            $c1 = 'M'; $c1e = 'R';
            $c2 = 'U'; $c2e = 'Z';
            
            $sheet->mergeCells("{$c1}{$row}:{$c1e}{$row}"); $sheet->setCellValue("{$c1}{$row}", "DIBUAT OLEH");
            $sheet->mergeCells("{$c2}{$row}:{$c2e}{$row}"); $sheet->setCellValue("{$c2}{$row}", "DISETUJUI OLEH");
            $sheet->getStyle("{$c1}{$row}:{$c2e}{$row}")->getAlignment()->setHorizontal('center')->setBold(true);

            $row2 = $row + 1;
            $sheet->mergeCells("{$c1}{$row2}:{$c1e}{$row2}"); $sheet->setCellValue("{$c1}{$row2}", "PIC");
            $sheet->mergeCells("{$c2}{$row2}:{$c2e}{$row2}"); $sheet->setCellValue("{$c2}{$row2}", "PIC LINE");
            $sheet->getStyle("{$c1}{$row2}:{$c2e}{$row2}")->getAlignment()->setHorizontal('center')->setBold(true);

            $rowSpace = $row + 2;
            $sheet->mergeCells("{$c1}{$rowSpace}:{$c1e}".($rowSpace+2));
            $sheet->mergeCells("{$c2}{$rowSpace}:{$c2e}".($rowSpace+2));
            
            if (!empty($header['waktu_selesai'])) $sheet->setCellValue("{$c1}{$rowSpace}", "[ Selesai ]");
            if ($header['status'] === 'Approved') $sheet->setCellValue("{$c2}{$rowSpace}", "[ Disetujui ]");
            $sheet->getStyle("{$c1}{$rowSpace}:{$c2e}".($rowSpace+2))->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);

            $picNames = explode(' - ', $header['nama_staff'] ?? $header['nama_pic'] ?? '');
            $picName = end($picNames) ?: '(...........................)';
            $finalNames = explode(' - ', $header['approver_nama'] ?? '');
            $finalName = end($finalNames) ?: '(...........................)';

            if (empty($header['waktu_selesai'])) $picName = '(...........................)';
            if ($header['status'] !== 'Approved') $finalName = '(...........................)';

            $rowName = $row + 5;
            $sheet->mergeCells("{$c1}{$rowName}:{$c1e}{$rowName}"); $sheet->setCellValue("{$c1}{$rowName}", strtoupper($picName));
            $sheet->mergeCells("{$c2}{$rowName}:{$c2e}{$rowName}"); $sheet->setCellValue("{$c2}{$rowName}", strtoupper($finalName));
            $sheet->getStyle("{$c1}{$rowName}:{$c2e}{$rowName}")->getAlignment()->setHorizontal('center')->setBold(true);

            $sheet->getStyle("{$c1}{$row}:{$c1e}{$rowName}")->applyFromArray($borderStyle);
            $sheet->getStyle("{$c2}{$row}:{$c2e}{$rowName}")->applyFromArray($borderStyle);
            
        } else {
            // Overhaul: 4 Columns (K:O, P:T, U:Z, AA:AF)
            $c1 = 'K'; $c1e = 'O';
            $c2 = 'P'; $c2e = 'T';
            $c3 = 'U'; $c3e = 'Z';
            $c4 = 'AA'; $c4e = 'AF';

            $sheet->mergeCells("{$c1}{$row}:{$c1e}{$row}"); $sheet->setCellValue("{$c1}{$row}", "PREPARED");
            $sheet->mergeCells("{$c2}{$row}:{$c2e}{$row}"); $sheet->setCellValue("{$c2}{$row}", "CHECKED");
            $sheet->mergeCells("{$c3}{$row}:{$c3e}{$row}"); $sheet->setCellValue("{$c3}{$row}", "APPROVED");
            $sheet->mergeCells("{$c4}{$row}:{$c4e}{$row}"); $sheet->setCellValue("{$c4}{$row}", "APPROVED");
            $sheet->getStyle("{$c1}{$row}:{$c4e}{$row}")->getAlignment()->setHorizontal('center')->setBold(true);

            $row2 = $row + 1;
            $sheet->mergeCells("{$c1}{$row2}:{$c1e}{$row2}"); $sheet->setCellValue("{$c1}{$row2}", "INSPECTOR");
            $sheet->mergeCells("{$c2}{$row2}:{$c2e}{$row2}"); $sheet->setCellValue("{$c2}{$row2}", "LEADER PRODUKSI"); // From pdf_signature.php
            $sheet->mergeCells("{$c3}{$row2}:{$c3e}{$row2}"); $sheet->setCellValue("{$c3}{$row2}", "SEC.HEAD PRODUKSI");
            $sheet->mergeCells("{$c4}{$row2}:{$c4e}{$row2}"); $sheet->setCellValue("{$c4}{$row2}", "SEC.HEAD MTN");
            $sheet->getStyle("{$c1}{$row2}:{$c4e}{$row2}")->getAlignment()->setHorizontal('center')->setBold(true);

            $rowSpace = $row + 2;
            $sheet->mergeCells("{$c1}{$rowSpace}:{$c1e}".($rowSpace+2));
            $sheet->mergeCells("{$c2}{$rowSpace}:{$c2e}".($rowSpace+2));
            $sheet->mergeCells("{$c3}{$rowSpace}:{$c3e}".($rowSpace+2));
            $sheet->mergeCells("{$c4}{$rowSpace}:{$c4e}".($rowSpace+2));
            
            if (!empty($header['waktu_selesai'])) $sheet->setCellValue("{$c1}{$rowSpace}", "[ Selesai ]");
            if (!empty($header['approval_l1_by'])) $sheet->setCellValue("{$c2}{$rowSpace}", "[ Diperiksa ]");
            if (!empty($header['approval_l2_by'])) $sheet->setCellValue("{$c3}{$rowSpace}", "[ Disetujui ]");
            if ($header['status'] === 'Approved') $sheet->setCellValue("{$c4}{$rowSpace}", "[ Disetujui ]");
            $sheet->getStyle("{$c1}{$rowSpace}:{$c4e}".($rowSpace+2))->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);

            $picNames = explode(' - ', $header['nama_staff'] ?? $header['nama_pic'] ?? '');
            $picName = end($picNames) ?: '(...........................)';
            if (empty($header['waktu_selesai'])) $picName = '(...........................)';
            
            $l1Names = explode(' - ', $header['approver_l1_nama'] ?? '');
            $l1Name = end($l1Names) ?: '(...........................)';
            if (empty($header['approval_l1_by'])) $l1Name = '(...........................)';
            
            $l2Names = explode(' - ', $header['approver_l2_nama'] ?? '');
            $l2Name = end($l2Names) ?: '(...........................)';
            if (empty($header['approval_l2_by'])) $l2Name = '(...........................)';
            
            $finalNames = explode(' - ', $header['approver_nama'] ?? '');
            $finalName = end($finalNames) ?: '(...........................)';
            if ($header['status'] !== 'Approved') $finalName = '(...........................)';

            $rowName = $row + 5;
            $sheet->mergeCells("{$c1}{$rowName}:{$c1e}{$rowName}"); $sheet->setCellValue("{$c1}{$rowName}", strtoupper($picName));
            $sheet->mergeCells("{$c2}{$rowName}:{$c2e}{$rowName}"); $sheet->setCellValue("{$c2}{$rowName}", strtoupper($l1Name));
            $sheet->mergeCells("{$c3}{$rowName}:{$c3e}{$rowName}"); $sheet->setCellValue("{$c3}{$rowName}", strtoupper($l2Name));
            $sheet->mergeCells("{$c4}{$rowName}:{$c4e}{$rowName}"); $sheet->setCellValue("{$c4}{$rowName}", strtoupper($finalName));
            $sheet->getStyle("{$c1}{$rowName}:{$c4e}{$rowName}")->getAlignment()->setHorizontal('center')->setBold(true);

            $sheet->getStyle("{$c1}{$row}:{$c1e}{$rowName}")->applyFromArray($borderStyle);
            $sheet->getStyle("{$c2}{$row}:{$c2e}{$rowName}")->applyFromArray($borderStyle);
            $sheet->getStyle("{$c3}{$row}:{$c3e}{$rowName}")->applyFromArray($borderStyle);
            $sheet->getStyle("{$c4}{$row}:{$c4e}{$rowName}")->applyFromArray($borderStyle);
        }
    }
PHP;

// Replace the buildFooter32Col function
$startStr = '    private function buildFooter32Col(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $row, $header) {';
$endStr = '    private function buildPreventiveExcelSheet';

$posStart = strpos($c, $startStr);
$posEnd = strpos($c, $endStr);

if ($posStart === false || $posEnd === false) {
    die("Could not find buildFooter32Col to replace");
}

$c = substr_replace($c, $newFooter . "\n\n", $posStart, $posEnd - $posStart);

// Update calls to buildFooter32Col to pass $isPreventive
$c = str_replace('$this->buildFooter32Col($sheet, $row, $header);', '', $c); // Remove the old calls so we don't duplicate when replacing

// Actually, it's easier to just do a string replace on the exact lines in buildPreventiveExcelSheet and buildOverhaulExcelSheet
// Let's reload $c because str_replace above is too generic and I removed it blindly
$c = file_get_contents($f);
$c = substr_replace($c, $newFooter . "\n\n", $posStart, $posEnd - $posStart);

// Let's replace the call in buildPreventiveExcelSheet
$c = preg_replace(
    '/\$this->buildFooter32Col\(\$sheet, \$row, \$header\);/m',
    '$this->buildFooter32Col($sheet, $row, $header, true);',
    $c,
    1 // Only the first one (in buildPreventiveExcelSheet)
);

// Replace the call in buildOverhaulExcelSheet
$c = preg_replace(
    '/\$this->buildFooter32Col\(\$sheet, \$row, \$header\);/m',
    '$this->buildFooter32Col($sheet, $row, $header, false);',
    $c,
    1 // The second one (in buildOverhaulExcelSheet)
);


file_put_contents($f, $c);
echo "Signatures made dynamic based on document type and approval status!";
