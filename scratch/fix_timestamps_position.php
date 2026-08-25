<?php
$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// Remove the injected timestamps from rowSpace
$c = preg_replace('/\[ Selesai \]\\\\n\$tgl/', '[ Selesai ]', $c);
$c = preg_replace('/\[ Disetujui \]\\\\n\$tgl/', '[ Disetujui ]', $c);
$c = preg_replace('/\[ Diperiksa \]\\\\n\$tgl/', '[ Diperiksa ]', $c);
$c = preg_replace('/\$tgl = date\([^;]+;\s+/', '', $c); // Remove the date calculation lines

// Now we want to append the timestamp to the NAME cell (rowName)
// The name is currently set like:
// $sheet->setCellValue("{$c1}{$rowName}", strtoupper($picName));

$fixNameTimestamps = <<<'PHP'
            $picText = strtoupper($picName);
            if (!empty($header['waktu_selesai'])) $picText .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['waktu_selesai']));
            $sheet->setCellValue("{$c1}{$rowName}", $picText);

            $finalText = strtoupper($finalName);
            if ($header['status'] === 'Approved' && !empty($header['approved_at'])) $finalText .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['approved_at']));
            $sheet->setCellValue("{$c2}{$rowName}", $finalText);
            
            $sheet->getStyle("{$c1}{$rowName}:{$c2e}{$rowName}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
            $sheet->getRowDimension($rowName)->setRowHeight(30);
PHP;
// Replace Preventive Name logic
$prevNameFind = <<<'PHP'
            $sheet->mergeCells("{$c1}{$rowName}:{$c1e}{$rowName}"); $sheet->setCellValue("{$c1}{$rowName}", strtoupper($picName));
            $sheet->mergeCells("{$c2}{$rowName}:{$c2e}{$rowName}"); $sheet->setCellValue("{$c2}{$rowName}", strtoupper($finalName));
            $sheet->getStyle("{$c1}{$rowName}:{$c2e}{$rowName}")->getAlignment()->setHorizontal('center'); $sheet->getStyle("{$c1}{$rowName}:{$c2e}{$rowName}")->getFont()->setBold(true);
PHP;
$prevNameRep = <<<'PHP'
            $sheet->mergeCells("{$c1}{$rowName}:{$c1e}{$rowName}"); 
            $picText = strtoupper($picName);
            if (!empty($header['waktu_selesai'])) $picText .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['waktu_selesai']));
            $sheet->setCellValue("{$c1}{$rowName}", $picText);

            $sheet->mergeCells("{$c2}{$rowName}:{$c2e}{$rowName}");
            $finalText = strtoupper($finalName);
            if ($header['status'] === 'Approved' && !empty($header['approved_at'])) $finalText .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['approved_at']));
            $sheet->setCellValue("{$c2}{$rowName}", $finalText);
            
            $sheet->getStyle("{$c1}{$rowName}:{$c2e}{$rowName}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
            $sheet->getStyle("{$c1}{$rowName}:{$c2e}{$rowName}")->getFont()->setBold(true);
            $sheet->getRowDimension($rowName)->setRowHeight(30);
PHP;
$c = str_replace($prevNameFind, $prevNameRep, $c);

// Replace Kontrol Name logic
$konNameFind = <<<'PHP'
            $sheet->mergeCells("{$c1}{$rowName}:{$c1e}{$rowName}"); $sheet->setCellValue("{$c1}{$rowName}", strtoupper($picName));
            $sheet->mergeCells("{$c2}{$rowName}:{$c2e}{$rowName}"); $sheet->setCellValue("{$c2}{$rowName}", strtoupper($l2Name));
            $sheet->mergeCells("{$c3}{$rowName}:{$c3e}{$rowName}"); $sheet->setCellValue("{$c3}{$rowName}", strtoupper($finalName));
            $sheet->getStyle("{$c1}{$rowName}:{$c3e}{$rowName}")->getAlignment()->setHorizontal('center'); $sheet->getStyle("{$c1}{$rowName}:{$c3e}{$rowName}")->getFont()->setBold(true);
PHP;
$konNameRep = <<<'PHP'
            $sheet->mergeCells("{$c1}{$rowName}:{$c1e}{$rowName}");
            $picText = strtoupper($picName);
            if (!empty($header['waktu_selesai'])) $picText .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['waktu_selesai']));
            $sheet->setCellValue("{$c1}{$rowName}", $picText);

            $sheet->mergeCells("{$c2}{$rowName}:{$c2e}{$rowName}");
            $l2Text = strtoupper($l2Name);
            if (!empty($header['approval_l2_by']) && !empty($header['approval_l2_at'])) $l2Text .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['approval_l2_at']));
            $sheet->setCellValue("{$c2}{$rowName}", $l2Text);

            $sheet->mergeCells("{$c3}{$rowName}:{$c3e}{$rowName}");
            $finalText = strtoupper($finalName);
            if ($header['status'] === 'Approved' && !empty($header['approved_at'])) $finalText .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['approved_at']));
            $sheet->setCellValue("{$c3}{$rowName}", $finalText);
            
            $sheet->getStyle("{$c1}{$rowName}:{$c3e}{$rowName}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
            $sheet->getStyle("{$c1}{$rowName}:{$c3e}{$rowName}")->getFont()->setBold(true);
            $sheet->getRowDimension($rowName)->setRowHeight(30);
PHP;
$c = str_replace($konNameFind, $konNameRep, $c);

// Replace Overhaul Name logic
$ovNameFind = <<<'PHP'
            $sheet->mergeCells("{$c1}{$rowName}:{$c1e}{$rowName}"); $sheet->setCellValue("{$c1}{$rowName}", strtoupper($picName));
            $sheet->mergeCells("{$c2}{$rowName}:{$c2e}{$rowName}"); $sheet->setCellValue("{$c2}{$rowName}", strtoupper($l1Name));
            $sheet->mergeCells("{$c3}{$rowName}:{$c3e}{$rowName}"); $sheet->setCellValue("{$c3}{$rowName}", strtoupper($l2Name));
            $sheet->mergeCells("{$c4}{$rowName}:{$c4e}{$rowName}"); $sheet->setCellValue("{$c4}{$rowName}", strtoupper($finalName));
            $sheet->getStyle("{$c1}{$rowName}:{$c4e}{$rowName}")->getAlignment()->setHorizontal('center'); $sheet->getStyle("{$c1}{$rowName}:{$c4e}{$rowName}")->getFont()->setBold(true);
PHP;
$ovNameRep = <<<'PHP'
            $sheet->mergeCells("{$c1}{$rowName}:{$c1e}{$rowName}");
            $picText = strtoupper($picName);
            if (!empty($header['waktu_selesai'])) $picText .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['waktu_selesai']));
            $sheet->setCellValue("{$c1}{$rowName}", $picText);

            $sheet->mergeCells("{$c2}{$rowName}:{$c2e}{$rowName}");
            $l1Text = strtoupper($l1Name);
            if (!empty($header['approval_l1_by']) && !empty($header['approval_l1_at'])) $l1Text .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['approval_l1_at']));
            $sheet->setCellValue("{$c2}{$rowName}", $l1Text);

            $sheet->mergeCells("{$c3}{$rowName}:{$c3e}{$rowName}");
            $l2Text = strtoupper($l2Name);
            if (!empty($header['approval_l2_by']) && !empty($header['approval_l2_at'])) $l2Text .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['approval_l2_at']));
            $sheet->setCellValue("{$c3}{$rowName}", $l2Text);

            $sheet->mergeCells("{$c4}{$rowName}:{$c4e}{$rowName}");
            $finalText = strtoupper($finalName);
            if ($header['status'] === 'Approved' && !empty($header['approved_at'])) $finalText .= "\nTgl: " . date('d/m/Y H:i', strtotime($header['approved_at']));
            $sheet->setCellValue("{$c4}{$rowName}", $finalText);
            
            $sheet->getStyle("{$c1}{$rowName}:{$c4e}{$rowName}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
            $sheet->getStyle("{$c1}{$rowName}:{$c4e}{$rowName}")->getFont()->setBold(true);
            $sheet->getRowDimension($rowName)->setRowHeight(30);
PHP;
$c = str_replace($ovNameFind, $ovNameRep, $c);

file_put_contents($f, $c);
echo "Moved timestamps directly under the approver names!";
