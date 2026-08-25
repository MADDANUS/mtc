<?php
$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// Read version_3.txt to extract downloadExcelDetail and exportExcelAll
$v3 = file_get_contents('scratch/version_3.txt');
preg_match('/public function downloadExcelDetail.*?\}\n\n    public function exportExcelAll.*?\n    \}/s', $v3, $matches);
if (empty($matches)) {
    die("Could not extract downloadExcelDetail from version_3.txt");
}

$extractedFunctions = $matches[0];

$new32Col = <<<'PHP'

    private function build32ColMicroGrid(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet) {
        $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet->getColumnDimension('A')->setWidth(2);
        foreach (range('B', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setWidth(4);
        }
        foreach (['AA','AB','AC','AD','AE','AF'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(4);
        }
    }

    private function buildHeader32Col(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $header, $isPreventive) {
        $borderStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]
            ]
        ];

        // Logo in B2:G7
        $sheet->mergeCells("B2:G7");
        $logoPath = FCPATH . 'uploads/nsi_logo.png';
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo NSI');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('B2');
            $drawing->setHeight(100);
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(10);
            $drawing->setWorksheet($sheet);
        }

        // Title
        $kategori = strtoupper($header['kategori'] ?? 'MESIN CNC');
        $titlePrefix = $isPreventive ? "PREVENTIVE MAINTENANCE REPORT" : "INSPECTION REPORT";
        $title = "$titlePrefix - $kategori";
        $sheet->mergeCells("H2:AF5");
        $sheet->setCellValue('H2', $title);
        $sheet->getStyle("H2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF92B0D6');
        $sheet->getStyle("H2")->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle("H2")->getAlignment()->setHorizontal('center')->setVertical('center');

        // Document Info
        $docNo = $isPreventive ? 'FM-MTN-08' : 'FM-MTN-10';
        $sheet->mergeCells("H6:S6"); $sheet->setCellValue('H6', 'NO. DOCUMENT');
        $sheet->mergeCells("T6:Y6"); $sheet->setCellValue('T6', 'NO REVISI');
        $sheet->mergeCells("Z6:AF6"); $sheet->setCellValue('Z6', 'HALAMAN');
        $sheet->getStyle("H6:AF6")->getAlignment()->setHorizontal('center')->setVertical('center');
        
        $sheet->mergeCells("H7:S7"); $sheet->setCellValue('H7', $docNo);
        $sheet->mergeCells("T7:Y7"); $sheet->setCellValue('T7', '0');
        $sheet->mergeCells("Z7:AF7"); $sheet->setCellValue('Z7', '1 dari 1');
        $sheet->getStyle("H7:AF7")->getAlignment()->setHorizontal('center')->setVertical('center')->setBold(true);

        $sheet->mergeCells("B8:AF8");
        $sheet->setCellValue('B8', 'Rev.:0/291124');

        $sheet->getStyle("B2:AF8")->applyFromArray($borderStyle);

        // PIC, Date, Machine Info
        $namaTopParts = explode(' - ', $header['nama_staff'] ?? $header['nama_pic'] ?? '');
        $namaTopOnly = end($namaTopParts);
        $waktuMulai = !empty($header['waktu_mulai']) ? strtotime($header['waktu_mulai']) : time();
        $waktuSelesai = !empty($header['waktu_selesai']) ? strtotime($header['waktu_selesai']) : null;

        // Row 9
        $sheet->mergeCells("B9:H9"); $sheet->setCellValue('B9', 'MAIN PIC: ' . $namaTopOnly);
        $sheet->mergeCells("I9:Y9"); $sheet->setCellValue('I9', 'NO. MACHINE: ' . ($header['no_mesin'] ?? '-'));
        $sheet->mergeCells("Z9:AF9"); $sheet->setCellValue('Z9', 'DATE: ' . date('d/m/Y', $waktuMulai));

        // Row 10
        $sheet->mergeCells("B10:H11"); $sheet->setCellValue('B10', 'SUPPORT PIC: ' . ($header['support_pic'] ?? '-'));
        $sheet->getStyle('B10')->getAlignment()->setVertical('top');
        $sheet->mergeCells("I10:Y10"); $sheet->setCellValue('I10', 'MACHINE TYPE: ' . ($header['type_mesin'] ?? '-'));
        $sheet->mergeCells("Z10:AF10"); $sheet->setCellValue('Z10', 'START TIME: ' . date('H:i:s', $waktuMulai));

        // Row 11
        $sheet->mergeCells("I11:Y11"); 
        if (stripos($kategori, 'CNC') !== false) {
            $sheet->setCellValue('I11', 'BAR FEEDER TYPE: ' . ($header['bar_feeder_type'] ?? '-'));
        } else {
            $sheet->setCellValue('I11', 'SERIAL NUMBER: ' . ($header['serial_nomor'] ?? '-'));
        }
        $sheet->mergeCells("Z11:AF11"); $sheet->setCellValue('Z11', 'FINISH TIME: ' . ($waktuSelesai ? date('H:i:s', $waktuSelesai) : '-'));

        $sheet->getStyle("B9:AF11")->applyFromArray($borderStyle);
        $sheet->getStyle("B9:AF11")->getFont()->setBold(true);
        
        return 12; // Start details at row 12
    }

    private function buildFooter32Col(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $row, $header) {
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

        // Signatures in K to AF
        // K:O (5), P:T (5), U:Z (6), AA:AF (6)
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
        $sheet->mergeCells("{$c2}{$row2}:{$c2e}{$row2}"); $sheet->setCellValue("{$c2}{$row2}", "USER");
        $sheet->mergeCells("{$c3}{$row2}:{$c3e}{$row2}"); $sheet->setCellValue("{$c3}{$row2}", "SEC.HEAD PRODUKSI");
        $sheet->mergeCells("{$c4}{$row2}:{$c4e}{$row2}"); $sheet->setCellValue("{$c4}{$row2}", "SEC.HEAD MTN");
        $sheet->getStyle("{$c1}{$row2}:{$c4e}{$row2}")->getAlignment()->setHorizontal('center')->setBold(true);

        $rowSpace = $row + 2;
        $sheet->mergeCells("{$c1}{$rowSpace}:{$c1e}".($rowSpace+2));
        $sheet->mergeCells("{$c2}{$rowSpace}:{$c2e}".($rowSpace+2));
        $sheet->mergeCells("{$c3}{$rowSpace}:{$c3e}".($rowSpace+2));
        $sheet->mergeCells("{$c4}{$rowSpace}:{$c4e}".($rowSpace+2));
        
        if ($header['waktu_selesai']) $sheet->setCellValue("{$c1}{$rowSpace}", "[ Selesai ]");
        if ($header['status'] === 'Approved') $sheet->setCellValue("{$c4}{$rowSpace}", "[ Disetujui ]");
        $sheet->getStyle("{$c1}{$rowSpace}:{$c4e}".($rowSpace+2))->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);

        $picNames = explode(' - ', $header['nama_staff'] ?? $header['nama_pic'] ?? '');
        $picName = end($picNames) ?: '-';
        $l1Names = explode(' - ', $header['approver_l1_nama'] ?? '');
        $l1Name = end($l1Names) ?: '-';
        $l2Names = explode(' - ', $header['approver_l2_nama'] ?? '');
        $l2Name = end($l2Names) ?: '-';
        $finalNames = explode(' - ', $header['approver_nama'] ?? '');
        $finalName = end($finalNames) ?: '-';

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

    private function buildPreventiveExcelSheet(array $header, array $details, \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $this->build32ColMicroGrid($sheet);
        $row = $this->buildHeader32Col($sheet, $header, true);
        
        $borderStyle = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];

        $sheet->mergeCells("B{$row}:C".($row+1)); $sheet->setCellValue("B{$row}", 'NO');
        $sheet->mergeCells("D{$row}:I".($row+1)); $sheet->setCellValue("D{$row}", 'ITEM CHECK');
        $sheet->mergeCells("J{$row}:O".($row+1)); $sheet->setCellValue("J{$row}", 'POINT CHECK');
        $sheet->mergeCells("P{$row}:W".($row+1)); $sheet->setCellValue("P{$row}", 'STANDARD CHECK');
        $sheet->mergeCells("X{$row}:Y".($row+1)); $sheet->setCellValue("X{$row}", 'HASIL');
        $sheet->mergeCells("Z{$row}:AF".($row+1)); $sheet->setCellValue("Z{$row}", 'REMARK');
        
        $sheet->getStyle("B{$row}:AF".($row+1))->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle("B{$row}:AF".($row+1))->getFont()->setBold(true);
        $sheet->getStyle("B{$row}:AF".($row+1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
        $sheet->getStyle("B{$row}:AF".($row+1))->applyFromArray($borderStyle);
        
        $row += 2;
        foreach ($details as $idx => $d) {
            if (!empty($d['is_section_start'])) {
                $sheet->mergeCells("B{$row}:AF{$row}");
                $sheet->setCellValue("B{$row}", strtoupper($d['dynamic_section_header']));
                $sheet->getStyle("B{$row}:AF{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
                $sheet->getStyle("B{$row}:AF{$row}")->getFont()->setBold(true);
                $sheet->getStyle("B{$row}:AF{$row}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("B{$row}:AF{$row}")->applyFromArray($borderStyle);
                $row++;
            }
            
            $sheet->mergeCells("B{$row}:C{$row}"); $sheet->setCellValue("B{$row}", $d['dynamic_no'] ?? ($idx + 1));
            
            $subItem = $d['sub_item_check'] ?? '';
            $bagian = $d['bagian_check'] ?? '';
            $sheet->mergeCells("D{$row}:I{$row}");
            if (!empty($subItem)) {
                $sheet->setCellValue("D{$row}", $bagian . " - " . $subItem);
            } else {
                $sheet->setCellValue("D{$row}", $bagian);
            }
            
            $sheet->mergeCells("J{$row}:O{$row}"); $sheet->setCellValue("J{$row}", $d['point_check'] ?? '');
            $sheet->mergeCells("P{$row}:W{$row}"); $sheet->setCellValue("P{$row}", $d['standard_check'] ?? '');
            
            $sheet->mergeCells("X{$row}:Y{$row}"); $sheet->setCellValue("X{$row}", $d['hasil_check'] ?? '');
            $hasil = $d['hasil_check'] ?? '';
            if ($hasil === 'V' || $hasil === '√') $sheet->getStyle("X{$row}")->getFont()->getColor()->setARGB('FF008000');
            elseif ($hasil === 'Δ') $sheet->getStyle("X{$row}")->getFont()->getColor()->setARGB('FFB8860B');
            elseif ($hasil === 'X') $sheet->getStyle("X{$row}")->getFont()->getColor()->setARGB('FFFF0000');
            $sheet->getStyle("X{$row}")->getFont()->setBold(true);
            $sheet->getStyle("X{$row}")->getAlignment()->setHorizontal('center');
            
            $sheet->mergeCells("Z{$row}:AF{$row}");
            $hasImage = false;
            $offsetX = 5;
            if (!empty($d['foto_abnormal'])) {
                $imgPath = FCPATH . 'uploads/abnormal/' . $d['foto_abnormal'];
                if (file_exists($imgPath)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Foto 1');
                    $drawing->setPath($imgPath);
                    $drawing->setCoordinates("Z{$row}");
                    $drawing->setHeight(50);
                    $drawing->setOffsetX($offsetX);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($sheet);
                    $hasImage = true;
                    $offsetX += 60;
                }
            }
            if (!empty($d['foto_abnormal_2'])) {
                $imgPath = FCPATH . 'uploads/abnormal/' . $d['foto_abnormal_2'];
                if (file_exists($imgPath)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Foto 2');
                    $drawing->setPath($imgPath);
                    $drawing->setCoordinates("Z{$row}");
                    $drawing->setHeight(50);
                    $drawing->setOffsetX($offsetX);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($sheet);
                    $hasImage = true;
                }
            }
            
            if ($hasImage) {
                $sheet->getRowDimension($row)->setRowHeight(60);
                $ulasanText = $d['ulasan'] ?? '';
                if (trim($ulasanText) !== '') {
                    $sheet->setCellValue("Z{$row}", $ulasanText . "\n\n\n\n\n");
                }
            } else {
                $sheet->setCellValue("Z{$row}", $d['ulasan'] ?? '');
            }
            
            $sheet->getStyle("B{$row}:AF{$row}")->applyFromArray($borderStyle);
            $sheet->getStyle("B{$row}:AF{$row}")->getAlignment()->setVertical('center')->setWrapText(true);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal('center');
            $row++;
        }
        $this->buildFooter32Col($sheet, $row, $header);
    }

    private function buildOverhaulExcelSheet(array $header, array $details, \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $this->build32ColMicroGrid($sheet);
        $row = $this->buildHeader32Col($sheet, $header, false);
        
        $borderStyle = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];

        $sheet->mergeCells("B{$row}:C".($row+1)); $sheet->setCellValue("B{$row}", 'NO');
        $sheet->mergeCells("D{$row}:O".($row+1)); $sheet->setCellValue("D{$row}", 'ITEM CHECK');
        $sheet->mergeCells("P{$row}:W".($row+1)); $sheet->setCellValue("P{$row}", 'POINT CHECK');
        $sheet->mergeCells("X{$row}:Y".($row+1)); $sheet->setCellValue("X{$row}", 'HASIL');
        $sheet->mergeCells("Z{$row}:AF".($row+1)); $sheet->setCellValue("Z{$row}", 'REMARK');
        
        $sheet->getStyle("B{$row}:AF".($row+1))->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle("B{$row}:AF".($row+1))->getFont()->setBold(true);
        $sheet->getStyle("B{$row}:AF".($row+1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
        $sheet->getStyle("B{$row}:AF".($row+1))->applyFromArray($borderStyle);
        
        $row += 2;
        foreach ($details as $idx => $d) {
            if (!empty($d['is_section_start'])) {
                $sheet->mergeCells("B{$row}:AF{$row}");
                $sheet->setCellValue("B{$row}", strtoupper($d['dynamic_section_header']));
                $sheet->getStyle("B{$row}:AF{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
                $sheet->getStyle("B{$row}:AF{$row}")->getFont()->setBold(true);
                $sheet->getStyle("B{$row}:AF{$row}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("B{$row}:AF{$row}")->applyFromArray($borderStyle);
                $row++;
            }
            
            $sheet->mergeCells("B{$row}:C{$row}"); $sheet->setCellValue("B{$row}", $d['dynamic_no'] ?? ($idx + 1));
            
            $subItem = $d['sub_item_check'] ?? '';
            $bagian = $d['bagian_check'] ?? '';
            $sheet->mergeCells("D{$row}:O{$row}");
            if (!empty($subItem)) {
                $sheet->setCellValue("D{$row}", $bagian . " - " . $subItem);
            } else {
                $sheet->setCellValue("D{$row}", $bagian);
            }
            
            $sheet->mergeCells("P{$row}:W{$row}"); $sheet->setCellValue("P{$row}", $d['point_check'] ?? '');
            
            $sheet->mergeCells("X{$row}:Y{$row}"); $sheet->setCellValue("X{$row}", $d['hasil_check'] ?? '');
            $hasil = $d['hasil_check'] ?? '';
            if ($hasil === 'V' || $hasil === '√') $sheet->getStyle("X{$row}")->getFont()->getColor()->setARGB('FF008000');
            elseif ($hasil === 'Δ') $sheet->getStyle("X{$row}")->getFont()->getColor()->setARGB('FFB8860B');
            elseif ($hasil === 'X') $sheet->getStyle("X{$row}")->getFont()->getColor()->setARGB('FFFF0000');
            $sheet->getStyle("X{$row}")->getFont()->setBold(true);
            $sheet->getStyle("X{$row}")->getAlignment()->setHorizontal('center');
            
            $sheet->mergeCells("Z{$row}:AF{$row}");
            $hasImage = false;
            $offsetX = 5;
            if (!empty($d['foto_abnormal'])) {
                $imgPath = FCPATH . 'uploads/abnormal/' . $d['foto_abnormal'];
                if (file_exists($imgPath)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Foto 1');
                    $drawing->setPath($imgPath);
                    $drawing->setCoordinates("Z{$row}");
                    $drawing->setHeight(50);
                    $drawing->setOffsetX($offsetX);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($sheet);
                    $hasImage = true;
                    $offsetX += 60;
                }
            }
            if (!empty($d['foto_abnormal_2'])) {
                $imgPath = FCPATH . 'uploads/abnormal/' . $d['foto_abnormal_2'];
                if (file_exists($imgPath)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Foto 2');
                    $drawing->setPath($imgPath);
                    $drawing->setCoordinates("Z{$row}");
                    $drawing->setHeight(50);
                    $drawing->setOffsetX($offsetX);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($sheet);
                    $hasImage = true;
                }
            }
            
            if ($hasImage) {
                $sheet->getRowDimension($row)->setRowHeight(60);
                $ulasanText = $d['ulasan'] ?? '';
                if (trim($ulasanText) !== '') {
                    $sheet->setCellValue("Z{$row}", $ulasanText . "\n\n\n\n\n");
                }
            } else {
                $sheet->setCellValue("Z{$row}", $d['ulasan'] ?? '');
            }
            
            $sheet->getStyle("B{$row}:AF{$row}")->applyFromArray($borderStyle);
            $sheet->getStyle("B{$row}:AF{$row}")->getAlignment()->setVertical('center')->setWrapText(true);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal('center');
            $row++;
        }
        $this->buildFooter32Col($sheet, $row, $header);
    }
PHP;

// Find position of last brace in RiwayatController
$lastBrace = strrpos($c, '}');
$c = substr_replace($c, "\n\n" . $extractedFunctions . "\n\n" . $new32Col . "\n}", $lastBrace, 1);

file_put_contents($f, $c);
echo "Final layout successfully injected to clean file!";
