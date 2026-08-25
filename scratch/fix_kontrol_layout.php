<?php
$f = 'app/Controllers/KontrolController.php';
$c = file_get_contents($f);

// 1. Fix Logo offsets
$c = str_replace(
    "\$drawing->setHeight(65);\n                \$drawing->setOffsetX(10);\n                \$drawing->setOffsetY(10);",
    "\$drawing->setHeight(85);\n                \$drawing->setOffsetX(25);\n                \$drawing->setOffsetY(10);",
    $c
);

// 2. Fix Machine names
$c = str_replace(
    "\$noMesin = \$mesinData['no_mesin'] ?? '-';\n                    \$jenis = \$mesinData['jenis'] ?? '';",
    "\$noMesin = \$mesinData['mesin']['no_mesin'] ?? '-';\n                    \$jenis = \$mesinData['mesin']['jenis'] ?? '';",
    $c
);

// 3. Fix Signatures
$oldSigBlock = <<<'PHP'
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
PHP;

$newSigBlock = <<<'PHP'
            // SIGNATURES - Replicating Preventive/Overhaul layout perfectly!
            // Row 1: Prepared / Approved / Approved
            $sheet->mergeCells("A{$row}:C{$row}"); $sheet->setCellValue("A{$row}", "PREPARED");
            $sheet->mergeCells("D{$row}:F{$row}"); $sheet->setCellValue("D{$row}", "APPROVED");
            $sheet->mergeCells("G{$row}:I{$row}"); $sheet->setCellValue("G{$row}", "APPROVED");
            $sheet->getStyle("A{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);

            // Row 2: Roles
            $row2 = $row + 1;
            $sheet->mergeCells("A{$row2}:C{$row2}"); $sheet->setCellValue("A{$row2}", "INSPECTOR");
            $sheet->mergeCells("D{$row2}:F{$row2}"); $sheet->setCellValue("D{$row2}", "SEC.HEAD PRODUKSI");
            $sheet->mergeCells("G{$row2}:I{$row2}"); $sheet->setCellValue("G{$row2}", "SEC.HEAD MTC");
            $sheet->getStyle("A{$row2}:I{$row2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row2}:I{$row2}")->getFont()->setBold(true);

            // Row 3: Status (Disetujui)
            $rowSpace = $row + 2;
            $sheet->mergeCells("A{$rowSpace}:C".($rowSpace+2));
            $sheet->mergeCells("D{$rowSpace}:F".($rowSpace+2));
            $sheet->mergeCells("G{$rowSpace}:I".($rowSpace+2));

            if (isset($approvalData['approved_l1_by'])) {
                $sheet->setCellValue("A{$rowSpace}", "[ Disetujui ]");
            }
            if (isset($approvalData['approved_l2_by'])) {
                $sheet->setCellValue("D{$rowSpace}", "[ Disetujui ]");
            }
            if (isset($approvalData['approved_final_by'])) {
                $sheet->setCellValue("G{$rowSpace}", "[ Disetujui ]");
            }
            $sheet->getStyle("A{$rowSpace}:I".($rowSpace+2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);

            // Row 4: Names & Dates
            $rowName = $row + 5;
            $sheet->mergeCells("A{$rowName}:C{$rowName}");
            $picText = isset($approvalData['l1_name']) ? strtoupper($approvalData['l1_name']) : "(...........................)";
            if (isset($approvalData['approved_l1_by']) && isset($approvalData['approved_l1_at'])) {
                $picText .= "\nTgl: " . date('d/m/Y H:i', strtotime($approvalData['approved_l1_at']));
            }
            $sheet->setCellValue("A{$rowName}", $picText);

            $sheet->mergeCells("D{$rowName}:F{$rowName}");
            $l2Text = isset($approvalData['l2_name']) ? strtoupper($approvalData['l2_name']) : "(...........................)";
            if (isset($approvalData['approved_l2_by']) && isset($approvalData['approved_l2_at'])) {
                $l2Text .= "\nTgl: " . date('d/m/Y H:i', strtotime($approvalData['approved_l2_at']));
            }
            $sheet->setCellValue("D{$rowName}", $l2Text);

            $sheet->mergeCells("G{$rowName}:I{$rowName}");
            $finalText = isset($approvalData['final_name']) ? strtoupper($approvalData['final_name']) : "(...........................)";
            if (isset($approvalData['approved_final_by']) && isset($approvalData['approved_final_at'])) {
                $finalText .= "\nTgl: " . date('d/m/Y H:i', strtotime($approvalData['approved_final_at']));
            }
            $sheet->setCellValue("G{$rowName}", $finalText);

            $sheet->getStyle("A{$rowName}:I{$rowName}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            $sheet->getStyle("A{$rowName}:I{$rowName}")->getFont()->setBold(true);
            $sheet->getRowDimension($rowName)->setRowHeight(30);

            // Borders for signature blocks
            $sheet->getStyle("A{$row}:C{$rowName}")->applyFromArray($borderStyle);
            $sheet->getStyle("D{$row}:F{$rowName}")->applyFromArray($borderStyle);
            $sheet->getStyle("G{$row}:I{$rowName}")->applyFromArray($borderStyle);
            
            // Adjust row for next loop
            $row = $rowName;
PHP;

$c = str_replace($oldSigBlock, $newSigBlock, $c);

file_put_contents($f, $c);
echo "Fixed Logo offsets, Machine Names, and Signatures layout!";
