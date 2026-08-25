<?php
$f = 'app/Controllers/KontrolController.php';
$c = file_get_contents($f);

// Replace everything from "// KETERANGAN CHECK LIST & SIGNATURES" to the end of the class.
$startStr = '// KETERANGAN CHECK LIST & SIGNATURES (Side by side)';
$posStart = strpos($c, $startStr);

if ($posStart !== false) {
    $posEnd = strpos($c, '$sheetIdx++;', $posStart);
    
    $newFooter = <<<'PHP'
            // KETERANGAN CHECK LIST & SIGNATURES (Side by side)
            $row += 2;

            // 1. KETERANGAN CHECK LIST (Columns A-B, total width 37)
            $sheet->mergeCells("A{$row}:B{$row}"); 
            $sheet->setCellValue("A{$row}", 'KETERANGAN CHECK LIST');
            $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $r = $row + 1;
            $sheet->setCellValue("A{$r}", 'V'); $sheet->getStyle("A{$r}")->getFont()->setBold(true)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("B{$r}", ': OK'); $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $r++;
            $sheet->setCellValue("A{$r}", 'Δ'); $sheet->getStyle("A{$r}")->getFont()->setBold(true)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue("B{$r}", ': PERLU TINDAKAN'); $sheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $r++;
            $sheet->setCellValue("A{$r}", 'X'); $sheet->getStyle("A{$r}")->getFont()->setBold(true)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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

            
PHP;
    $c = substr($c, 0, $posStart) . ltrim($newFooter) . substr($c, $posEnd);
    file_put_contents($f, $c);
    echo "Footer symmetrically replaced successfully!";
} else {
    echo "Footer not found!";
}
