<?php
$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// 1. We replace buildFooter32Col entirely
$footerStart = '    private function buildFooter32Col(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $row, $header, $isPreventive) {';
$footerEnd = '    private function buildPreventiveExcelSheet(';
$pos1 = strpos($c, $footerStart);
$pos2 = strpos($c, $footerEnd);
if ($pos1 !== false && $pos2 !== false) {
    $newFooter = <<<'PHP'
    private function buildFooter32Col(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $row, $header, $docType) {
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
        if ($docType === 'preventive') {
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
            
        } elseif ($docType === 'kontrol') {
            // Kontrol: 3 Columns (L:Q, S:X, Z:AE)
            $c1 = 'L'; $c1e = 'Q';
            $c2 = 'S'; $c2e = 'X';
            $c3 = 'Z'; $c3e = 'AE';

            $sheet->mergeCells("{$c1}{$row}:{$c1e}{$row}"); $sheet->setCellValue("{$c1}{$row}", "PREPARED");
            $sheet->mergeCells("{$c2}{$row}:{$c2e}{$row}"); $sheet->setCellValue("{$c2}{$row}", "APPROVED");
            $sheet->mergeCells("{$c3}{$row}:{$c3e}{$row}"); $sheet->setCellValue("{$c3}{$row}", "APPROVED");
            $sheet->getStyle("{$c1}{$row}:{$c3e}{$row}")->getAlignment()->setHorizontal('center')->setBold(true);

            $row2 = $row + 1;
            $sheet->mergeCells("{$c1}{$row2}:{$c1e}{$row2}"); $sheet->setCellValue("{$c1}{$row2}", "INSPECTOR");
            $sheet->mergeCells("{$c2}{$row2}:{$c2e}{$row2}"); $sheet->setCellValue("{$c2}{$row2}", "SEC.HEAD PRODUKSI");
            $sheet->mergeCells("{$c3}{$row2}:{$c3e}{$row2}"); $sheet->setCellValue("{$c3}{$row2}", "SEC.HEAD MTC"); // As requested by user: SEC.HEAD MTC (or MTN)
            $sheet->getStyle("{$c1}{$row2}:{$c3e}{$row2}")->getAlignment()->setHorizontal('center')->setBold(true);

            $rowSpace = $row + 2;
            $sheet->mergeCells("{$c1}{$rowSpace}:{$c1e}".($rowSpace+2));
            $sheet->mergeCells("{$c2}{$rowSpace}:{$c2e}".($rowSpace+2));
            $sheet->mergeCells("{$c3}{$rowSpace}:{$c3e}".($rowSpace+2));
            
            if (!empty($header['waktu_selesai'])) $sheet->setCellValue("{$c1}{$rowSpace}", "[ Selesai ]");
            if (!empty($header['approval_l2_by'])) $sheet->setCellValue("{$c2}{$rowSpace}", "[ Disetujui ]");
            if ($header['status'] === 'Approved') $sheet->setCellValue("{$c3}{$rowSpace}", "[ Disetujui ]");
            $sheet->getStyle("{$c1}{$rowSpace}:{$c3e}".($rowSpace+2))->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);

            $picNames = explode(' - ', $header['nama_staff'] ?? $header['nama_pic'] ?? '');
            $picName = end($picNames) ?: '(...........................)';
            if (empty($header['waktu_selesai'])) $picName = '(...........................)';
            
            $l2Names = explode(' - ', $header['approver_l2_nama'] ?? '');
            $l2Name = end($l2Names) ?: '(...........................)';
            if (empty($header['approval_l2_by'])) $l2Name = '(...........................)';
            
            $finalNames = explode(' - ', $header['approver_nama'] ?? '');
            $finalName = end($finalNames) ?: '(...........................)';
            if ($header['status'] !== 'Approved') $finalName = '(...........................)';

            $rowName = $row + 5;
            $sheet->mergeCells("{$c1}{$rowName}:{$c1e}{$rowName}"); $sheet->setCellValue("{$c1}{$rowName}", strtoupper($picName));
            $sheet->mergeCells("{$c2}{$rowName}:{$c2e}{$rowName}"); $sheet->setCellValue("{$c2}{$rowName}", strtoupper($l2Name));
            $sheet->mergeCells("{$c3}{$rowName}:{$c3e}{$rowName}"); $sheet->setCellValue("{$c3}{$rowName}", strtoupper($finalName));
            $sheet->getStyle("{$c1}{$rowName}:{$c3e}{$rowName}")->getAlignment()->setHorizontal('center')->setBold(true);

            $sheet->getStyle("{$c1}{$row}:{$c1e}{$rowName}")->applyFromArray($borderStyle);
            $sheet->getStyle("{$c2}{$row}:{$c2e}{$rowName}")->applyFromArray($borderStyle);
            $sheet->getStyle("{$c3}{$row}:{$c3e}{$rowName}")->applyFromArray($borderStyle);

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
            $sheet->mergeCells("{$c2}{$row2}:{$c2e}{$row2}"); $sheet->setCellValue("{$c2}{$row2}", "LEADER PRODUKSI"); 
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
    $c = substr_replace($c, $newFooter . "\n\n", $pos1, $pos2 - $pos1);
} else {
    die("Could not find buildFooter32Col string in RiwayatController.php");
}


// Replace $isPreventive variable inside buildPreventiveExcelSheet and buildOverhaulExcelSheet
// In buildPreventiveExcelSheet it calls: $this->buildFooter32Col($sheet, $row, $header, true);
$c = preg_replace('/\$this->buildFooter32Col\(\$sheet, \$row, \$header, (true|false|' . "'preventive'" . '|' . "'overhaul'" . ')\);/m', '$this->buildFooter32Col($sheet, $row, $header, $docType);', $c);

// Also we need to make sure buildPreventiveExcelSheet and buildOverhaulExcelSheet accept $docType.
$c = preg_replace('/private function buildPreventiveExcelSheet\(array \$header, array \$details, .*? \$sheet\)\s*\{/', "$0\n        \$docType = strtolower(\$header['jenis_check'] ?? 'preventive');", $c);
$c = preg_replace('/private function buildOverhaulExcelSheet\(array \$header, array \$details, .*? \$sheet\)\s*\{/', "$0\n        \$docType = strtolower(\$header['jenis_check'] ?? 'overhaul');", $c);


file_put_contents($f, $c);
echo "Added 3-column signature block for Kontrol and correctly wired document type parsing!";
