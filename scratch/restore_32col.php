<?php

function build32ColMicroGrid($sheet) {
    $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
    // 32-column micro-grid: B to AE. Plus A as left margin.
    $sheet->getColumnDimension('A')->setWidth(2);
    foreach (range('B', 'Z') as $col) {
        $sheet->getColumnDimension($col)->setWidth(3.5);
    }
    foreach (['AA', 'AB', 'AC', 'AD', 'AE'] as $col) {
        $sheet->getColumnDimension($col)->setWidth(3.5);
    }
}

function buildHeader32Col($sheet, $header, $isPreventive = false) {
    $dept = strtoupper($header['departemen']);
    $isMfg2 = ($dept === 'MFG 2');
    $title = $isPreventive ? "PREVENTIVE MAINTENANCE REPORT" : "INSPECTION REPORT";
    if (isset($header['mesin'])) {
        $title .= " - " . strtoupper($header['mesin']);
    }

    $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
    
    // Logo block
    $sheet->mergeCells("B2:H7");
    $logoPath = FCPATH . 'uploads/nsi_logo.png';
    if (file_exists($logoPath)) {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo NSI');
        $drawing->setPath($logoPath);
        $drawing->setCoordinates('C2');
        $drawing->setHeight(50);
        $drawing->setOffsetX(0);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($sheet);
    }
    
    // Title block
    $sheet->mergeCells("I2:AE5");
    $sheet->setCellValue('I2', $title);
    $sheet->getStyle('I2')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('I2')->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getStyle('I2:AE5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFB4C6E7'); // Light blue like screenshot

    // Doc, Rev, Hal
    $sheet->mergeCells("I6:Q6"); $sheet->setCellValue('I6', 'NO. DOCUMENT');
    $sheet->mergeCells("R6:W6"); $sheet->setCellValue('R6', 'NO. REVISI');
    $sheet->mergeCells("X6:AE6"); $sheet->setCellValue('X6', 'HALAMAN');
    $sheet->getStyle('I6:AE6')->getFont()->setSize(9);
    
    $docNo = $isPreventive ? 'FM-MTN-08' : 'FM-MTN-11';
    $sheet->mergeCells("I7:Q7"); $sheet->setCellValue('I7', $docNo);
    $sheet->mergeCells("R7:W7"); $sheet->setCellValue('R7', '1');
    $sheet->mergeCells("X7:AE7"); $sheet->setCellValue('X7', '1 dari 1'); // Simple pagination mock
    
    $sheet->getStyle('I6:AE7')->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getStyle('I7:AE7')->getFont()->setBold(true);

    // Rev line under logo
    $sheet->mergeCells("B8:H8");
    $sheet->setCellValue('B8', 'Rev.:0/291124');
    $sheet->getStyle('B8')->getFont()->setSize(9)->setItalic(true);
    
    $sheet->getStyle('B2:AE8')->applyFromArray($borderThin);

    // Info blocks
    $row = 9;
    $sheet->mergeCells("B9:F9"); $sheet->setCellValue('B9', 'MAIN PIC');
    $sheet->mergeCells("G9:Q9"); $sheet->setCellValue('G9', strtoupper($header['pic']));
    $sheet->mergeCells("R9:Z9"); $sheet->setCellValue('R9', 'NO. MACHIN : ' . strtoupper($header['no_mesin']));
    $sheet->mergeCells("AA9:AE9"); $sheet->setCellValue('AA9', 'DATE : ' . format_tanggal_indo($header['tanggal'] ?? date('Y-m-d')));
    $sheet->getStyle('B9:AE9')->getFont()->setBold(true);
    
    $row = 10;
    $sheet->mergeCells("B10:F11"); $sheet->setCellValue('B10', 'SUPPORT');
    $sheet->mergeCells("G10:Q10"); $sheet->setCellValue('G10', '1. ' . strtoupper($header['support_1'] ?? '-'));
    $sheet->mergeCells("G11:Q11"); $sheet->setCellValue('G11', '2. ' . strtoupper($header['support_2'] ?? '-'));
    
    $sheet->mergeCells("R10:Z10"); $sheet->setCellValue('R10', 'MACHINE T : ' . strtoupper($header['type_mesin'] ?? '-'));
    $sheet->mergeCells("AA10:AE10"); $sheet->setCellValue('AA10', 'START T : ' . date('H:i', strtotime($header['waktu_mulai'] ?? '00:00')) . ' WIB');
    $sheet->getStyle('B10:AE10')->getFont()->setBold(true);
    
    $sheet->mergeCells("R11:Z11");
    $sheet->mergeCells("AA11:AE11"); $sheet->setCellValue('AA11', 'FINISH TI : ' . date('H:i', strtotime($header['waktu_selesai'] ?? '00:00')) . ' WIB');
    $sheet->getStyle('AA11')->getFont()->setBold(true);

    $sheet->getStyle('B9:AE11')->applyFromArray($borderThin);
    
    return ['row' => 12, 'isMfg2' => $isMfg2];
}

function buildTblHeader32Col($sheet, $row, $isMfg2, $isPreventive = false) {
    $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
    
    $sheet->mergeCells("B{$row}:C" . ($row+1)); $sheet->setCellValue("B{$row}", 'NO');
    
    if ($isMfg2) {
        $sheet->mergeCells("D{$row}:Q" . ($row+1)); $sheet->setCellValue("D{$row}", 'ITEM CHECK');
        $sheet->mergeCells("R{$row}:Z" . ($row+1)); $sheet->setCellValue("R{$row}", 'POINT CHECK');
    } else {
        $sheet->mergeCells("D{$row}:K" . ($row+1)); $sheet->setCellValue("D{$row}", 'ITEM CHECK');
        $sheet->mergeCells("L{$row}:S" . ($row+1)); $sheet->setCellValue("L{$row}", 'POINT CHECK');
        $sheet->mergeCells("T{$row}:Z" . ($row+1)); $sheet->setCellValue("T{$row}", 'STANDARD ITEM');
    }
    
    $sheet->mergeCells("AA{$row}:AB" . ($row+1)); $sheet->setCellValue("AA{$row}", "CHE\nCK");
    $sheet->mergeCells("AC{$row}:AE" . ($row+1)); $sheet->setCellValue("AC{$row}", 'REMARK');

    $sheet->getStyle("B{$row}:AE" . ($row+1))->getFont()->setBold(true);
    $sheet->getStyle("B{$row}:AE" . ($row+1))->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
    $sheet->getStyle("B{$row}:AE" . ($row+1))->applyFromArray($borderThin);
    
    return $row + 2;
}

function buildFooter32Col($sheet, $row, $approvalData) {
    $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
    
    $row++; // Add blank row space
    
    // NOTE AND RECOMMENDATION
    $sheet->mergeCells("B{$row}:I{$row}"); $sheet->setCellValue("B{$row}", 'NOTE AND RECOMMENDATION');
    $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setSize(9);
    $row++;
    
    // Box with dotted lines
    $boxStart = $row;
    for ($i = 0; $i < 4; $i++) {
        $sheet->mergeCells("B{$row}:AE{$row}");
        $sheet->setCellValue("B{$row}", '..........................................................................................................................................................................................................................................');
        $sheet->getStyle("B{$row}")->getFont()->getColor()->setARGB('FF999999');
        $sheet->getStyle("B{$row}")->getAlignment()->setVertical('bottom');
        $row++;
    }
    $sheet->getStyle("B{$boxStart}:AE" . ($row-1))->applyFromArray($borderThin);
    
    $row++; // Space
    
    // KETERANGAN CHECK LIST + SIGNATURES side-by-side
    $sigRow = $row;
    
    // KETERANGAN
    $sheet->mergeCells("C{$row}:G{$row}"); $sheet->setCellValue("C{$row}", 'KETERANGAN CHECK LIST');
    $sheet->getStyle("C{$row}")->getFont()->setBold(true)->setSize(9);
    $sheet->getStyle("C{$row}:G{$row}")->getAlignment()->setHorizontal('center');
    $row++;
    $sheet->setCellValue("C{$row}", '√'); $sheet->getStyle("C{$row}")->getFont()->setBold(true)->getColor()->setARGB('FF0D6EFD'); $sheet->setCellValue("D{$row}", ':'); $sheet->mergeCells("E{$row}:G{$row}"); $sheet->setCellValue("E{$row}", 'OK');
    $row++;
    $sheet->setCellValue("C{$row}", 'Δ'); $sheet->getStyle("C{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFFFA500'); $sheet->setCellValue("D{$row}", ':'); $sheet->mergeCells("E{$row}:G{$row}"); $sheet->setCellValue("E{$row}", 'PERLU TINDAKAN');
    $row++;
    $sheet->setCellValue("C{$row}", 'X'); $sheet->getStyle("C{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFDC3545'); $sheet->setCellValue("D{$row}", ':'); $sheet->mergeCells("E{$row}:G{$row}"); $sheet->setCellValue("E{$row}", 'TIDAK ADA');
    $sheet->getStyle("C{$sigRow}:G{$row}")->applyFromArray($borderThin);
    $sheet->getStyle("C".($sigRow+1).":C{$row}")->getAlignment()->setHorizontal('center');
    
    // SIGNATURES
    $row = $sigRow;
    $sheet->mergeCells("O{$row}:S{$row}"); $sheet->setCellValue("O{$row}", 'PREPARED');
    $sheet->mergeCells("T{$row}:X{$row}"); $sheet->setCellValue("T{$row}", 'CHECKED');
    $sheet->mergeCells("Y{$row}:AB{$row}"); $sheet->setCellValue("Y{$row}", 'APPROVED');
    $sheet->mergeCells("AC{$row}:AE{$row}"); $sheet->setCellValue("AC{$row}", 'APPROVED');
    $sheet->getStyle("O{$row}:AE{$row}")->getFont()->setBold(true)->setSize(9);
    $sheet->getStyle("O{$row}:AE{$row}")->getAlignment()->setHorizontal('center');
    $row++;
    
    $sheet->mergeCells("O{$row}:S{$row}"); $sheet->setCellValue("O{$row}", 'INSPECTOR');
    $sheet->mergeCells("T{$row}:X{$row}"); $sheet->setCellValue("T{$row}", 'USER');
    $sheet->mergeCells("Y{$row}:AB{$row}"); $sheet->setCellValue("Y{$row}", 'SEC.HEAD MFG 2');
    $sheet->mergeCells("AC{$row}:AE{$row}"); $sheet->setCellValue("AC{$row}", 'SEC.HEAD MTN');
    $sheet->getStyle("O{$row}:AE{$row}")->getFont()->setBold(true)->setSize(8);
    $sheet->getStyle("O{$row}:AE{$row}")->getAlignment()->setHorizontal('center');
    $row++;
    
    // Blank boxes for signatures
    $boxStartRow = $row;
    $sheet->mergeCells("O{$row}:S".($row+2)); 
    $sheet->mergeCells("T{$row}:X".($row+2)); 
    $sheet->mergeCells("Y{$row}:AB".($row+2)); 
    $sheet->mergeCells("AC{$row}:AE".($row+2)); 
    $row += 3;
    
    $sheet->mergeCells("O{$row}:S{$row}"); $sheet->setCellValue("O{$row}", strtoupper(explode(' - ', $approvalData['pic_name'] ?? '')[1] ?? 'PIC'));
    $sheet->mergeCells("T{$row}:X{$row}"); $sheet->setCellValue("T{$row}", strtoupper(explode(' - ', $approvalData['l1_name'] ?? '')[1] ?? '-'));
    $sheet->mergeCells("Y{$row}:AB{$row}"); $sheet->setCellValue("Y{$row}", strtoupper(explode(' - ', $approvalData['l2_name'] ?? '')[1] ?? '-'));
    $sheet->mergeCells("AC{$row}:AE{$row}"); $sheet->setCellValue("AC{$row}", strtoupper(explode(' - ', $approvalData['final_name'] ?? '')[1] ?? '-'));
    $sheet->getStyle("O{$row}:AE{$row}")->getFont()->setBold(true)->setSize(9);
    $sheet->getStyle("O{$row}:AE{$row}")->getAlignment()->setHorizontal('center');
    
    $sheet->getStyle("O{$sigRow}:AE{$row}")->applyFromArray($borderThin);
}

// -------------------------------------------------------------
// REWRITE FUNCTIONS to be injected into RiwayatController
// -------------------------------------------------------------
$newPreventive = <<<'PHP'
    private function buildPreventiveExcelSheet(array $header, array $details, \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        // Require helper file logic directly or assume it's already there? We will inject the logic directly!
        $this->build32ColMicroGrid($sheet);
        $res = $this->buildHeader32Col($sheet, $header, true);
        $row = $res['row'];
        $isMfg2 = $res['isMfg2'];
        
        $row = $this->buildTblHeader32Col($sheet, $row, $isMfg2, true);
        
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        
        $currentBagian = '';
        foreach ($details as $idx => $d) {
            $bagianName = $d['bagian_check'] ?? 'Lainnya';
            if ($bagianName !== $currentBagian) {
                $sheet->mergeCells("B{$row}:C{$row}");
                $sheet->mergeCells("D{$row}:AE{$row}");
                $sheet->setCellValue("D{$row}", strtoupper($bagianName));
                $sheet->getStyle("D{$row}")->getFont()->setBold(true);
                $sheet->getStyle("B{$row}:AE{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9ECEF');
                $sheet->getStyle("B{$row}:AE{$row}")->applyFromArray($borderThin);
                $currentBagian = $bagianName;
                $row++;
            }
            
            $sheet->mergeCells("B{$row}:C{$row}"); $sheet->setCellValue("B{$row}", ($idx + 1));
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal('center');
            
            if ($isMfg2) {
                $sheet->mergeCells("D{$row}:Q{$row}"); $sheet->setCellValue("D{$row}", $d['sub_item_check'] ?? '-');
                $sheet->mergeCells("R{$row}:Z{$row}"); $sheet->setCellValue("R{$row}", $d['point_check'] ?? '-');
            } else {
                $sheet->mergeCells("D{$row}:K{$row}"); $sheet->setCellValue("D{$row}", $d['sub_item_check'] ?? '-');
                $sheet->mergeCells("L{$row}:S{$row}"); $sheet->setCellValue("L{$row}", $d['point_check'] ?? '-');
                $sheet->mergeCells("T{$row}:Z{$row}"); $sheet->setCellValue("T{$row}", $d['standard_check'] ?? '-');
            }
            
            $sheet->mergeCells("AA{$row}:AB{$row}");
            $val = $d['hasil_check'] ?? '';
            $sheet->setCellValue("AA{$row}", $val);
            if ($val === 'V') $sheet->getStyle("AA{$row}")->getFont()->getColor()->setARGB('FF0D6EFD');
            elseif ($val === 'Δ') $sheet->getStyle("AA{$row}")->getFont()->getColor()->setARGB('FFFFA500');
            elseif ($val === 'X') $sheet->getStyle("AA{$row}")->getFont()->getColor()->setARGB('FFDC3545');
            $sheet->getStyle("AA{$row}")->getAlignment()->setHorizontal('center');
            
            $sheet->mergeCells("AC{$row}:AE{$row}"); 
            $sheet->setCellValue("AC{$row}", $d['keterangan'] ?? '');
            
            $sheet->getStyle("B{$row}:AE{$row}")->applyFromArray($borderThin);
            $sheet->getStyle("B{$row}:AE{$row}")->getAlignment()->setVertical('center')->setWrapText(true);
            $row++;
        }
        
        $this->buildFooter32Col($sheet, $row, $header);
    }
PHP;

$newOverhaul = <<<'PHP'
    private function buildOverhaulExcelSheet(array $header, array $details, \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $this->build32ColMicroGrid($sheet);
        $res = $this->buildHeader32Col($sheet, $header, false);
        $row = $res['row'];
        $isMfg2 = $res['isMfg2'];
        
        $row = $this->buildTblHeader32Col($sheet, $row, $isMfg2, false);
        
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        
        $currentBagian = '';
        foreach ($details as $idx => $d) {
            $bagianName = $d['bagian_check'] ?? 'Lainnya';
            if ($bagianName !== $currentBagian) {
                $sheet->mergeCells("B{$row}:C{$row}");
                $sheet->mergeCells("D{$row}:AE{$row}");
                $sheet->setCellValue("D{$row}", strtoupper($bagianName));
                $sheet->getStyle("D{$row}")->getFont()->setBold(true);
                $sheet->getStyle("B{$row}:AE{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9ECEF');
                $sheet->getStyle("B{$row}:AE{$row}")->applyFromArray($borderThin);
                $currentBagian = $bagianName;
                $row++;
            }
            
            $sheet->mergeCells("B{$row}:C{$row}"); $sheet->setCellValue("B{$row}", ($idx + 1));
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal('center');
            
            if ($isMfg2) {
                $sheet->mergeCells("D{$row}:Q{$row}"); $sheet->setCellValue("D{$row}", $d['sub_item_check'] ?? '-');
                $sheet->mergeCells("R{$row}:Z{$row}"); $sheet->setCellValue("R{$row}", $d['point_check'] ?? '-');
            } else {
                $sheet->mergeCells("D{$row}:K{$row}"); $sheet->setCellValue("D{$row}", $d['sub_item_check'] ?? '-');
                $sheet->mergeCells("L{$row}:S{$row}"); $sheet->setCellValue("L{$row}", $d['point_check'] ?? '-');
                $sheet->mergeCells("T{$row}:Z{$row}"); $sheet->setCellValue("T{$row}", $d['standard_check'] ?? '-');
            }
            
            $sheet->mergeCells("AA{$row}:AB{$row}");
            $val = $d['hasil_check'] ?? '';
            $sheet->setCellValue("AA{$row}", $val);
            if ($val === 'V' || $val === '√') {
                $sheet->setCellValue("AA{$row}", '√');
                $sheet->getStyle("AA{$row}")->getFont()->getColor()->setARGB('FF0D6EFD');
            }
            elseif ($val === 'Δ') $sheet->getStyle("AA{$row}")->getFont()->getColor()->setARGB('FFFFA500');
            elseif ($val === 'X') $sheet->getStyle("AA{$row}")->getFont()->getColor()->setARGB('FFDC3545');
            $sheet->getStyle("AA{$row}")->getAlignment()->setHorizontal('center');
            
            $sheet->mergeCells("AC{$row}:AE{$row}"); 
            $sheet->setCellValue("AC{$row}", $d['keterangan'] ?? '');
            
            $sheet->getStyle("B{$row}:AE{$row}")->applyFromArray($borderThin);
            $sheet->getStyle("B{$row}:AE{$row}")->getAlignment()->setVertical('center')->setWrapText(true);
            $row++;
        }
        
        $this->buildFooter32Col($sheet, $row, $header);
    }
PHP;

$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// Inject helper functions
$helpers = <<<'PHP'
    private function build32ColMicroGrid($sheet) {
        $sheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet->getColumnDimension('A')->setWidth(2);
        foreach (range('B', 'Z') as $col) $sheet->getColumnDimension($col)->setWidth(3.5);
        foreach (['AA', 'AB', 'AC', 'AD', 'AE'] as $col) $sheet->getColumnDimension($col)->setWidth(3.5);
    }

    private function buildHeader32Col($sheet, $header, $isPreventive = false) {
        $dept = strtoupper($header['departemen'] ?? '');
        $isMfg2 = ($dept === 'MFG 2');
        $title = $isPreventive ? "PREVENTIVE MAINTENANCE REPORT" : "INSPECTION REPORT";
        if (!empty($header['no_mesin'])) $title .= " - MESIN " . strtoupper($header['no_mesin']);
        
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        
        $sheet->mergeCells("B2:H7");
        $logoPath = FCPATH . 'uploads/nsi_logo.png';
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo NSI');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('C2');
            $drawing->setHeight(60);
            $drawing->setOffsetX(0);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }
        
        $sheet->mergeCells("I2:AE5"); $sheet->setCellValue('I2', $title);
        $sheet->getStyle('I2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('I2')->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle('I2:AE5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFB4C6E7');
        
        $sheet->mergeCells("I6:Q6"); $sheet->setCellValue('I6', 'NO. DOCUMENT');
        $sheet->mergeCells("R6:W6"); $sheet->setCellValue('R6', 'NO. REVISI');
        $sheet->mergeCells("X6:AE6"); $sheet->setCellValue('X6', 'HALAMAN');
        
        $sheet->mergeCells("I7:Q7"); $sheet->setCellValue('I7', $isPreventive ? 'FM-MTN-08' : 'FM-MTN-11');
        $sheet->mergeCells("R7:W7"); $sheet->setCellValue('R7', '1');
        $sheet->mergeCells("X7:AE7"); $sheet->setCellValue('X7', '1 dari 1');
        
        $sheet->getStyle('I6:AE7')->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle('I7:AE7')->getFont()->setBold(true);
        
        $sheet->mergeCells("B8:H8"); $sheet->setCellValue('B8', 'Rev.:0/291124');
        $sheet->getStyle('B8')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('B2:AE8')->applyFromArray($borderThin);
        
        $sheet->mergeCells("B9:F9"); $sheet->setCellValue('B9', 'MAIN PIC');
        $sheet->mergeCells("G9:Q9"); $sheet->setCellValue('G9', strtoupper(explode(' - ', $header['pic_name'] ?? '')[1] ?? 'PIC'));
        $sheet->mergeCells("R9:Z9"); $sheet->setCellValue('R9', 'NO. MACHIN : ' . strtoupper($header['no_mesin'] ?? '-'));
        $sheet->mergeCells("AA9:AE9"); $sheet->setCellValue('AA9', 'DATE : ' . (!empty($header['waktu_mulai']) ? format_tanggal_indo($header['waktu_mulai']) : date('d F Y')));
        $sheet->getStyle('B9:AE9')->getFont()->setBold(true);
        
        $sheet->mergeCells("B10:F11"); $sheet->setCellValue('B10', 'SUPPORT');
        $sheet->mergeCells("G10:Q10"); $sheet->setCellValue('G10', '1. ' . strtoupper($header['support_1'] ?? '-'));
        $sheet->mergeCells("G11:Q11"); $sheet->setCellValue('G11', '2. ' . strtoupper($header['support_2'] ?? '-'));
        $sheet->mergeCells("R10:Z10"); $sheet->setCellValue('R10', 'MACHINE T : ' . strtoupper($header['type_mesin'] ?? '-'));
        $sheet->mergeCells("AA10:AE10"); $sheet->setCellValue('AA10', 'START T : ' . (!empty($header['waktu_mulai']) ? date('H.i', strtotime($header['waktu_mulai'])) : '00.00') . ' WIB');
        $sheet->getStyle('B10:AE10')->getFont()->setBold(true);
        
        $sheet->mergeCells("R11:Z11");
        $sheet->mergeCells("AA11:AE11"); $sheet->setCellValue('AA11', 'FINISH TI : ' . (!empty($header['waktu_selesai']) ? date('H.i', strtotime($header['waktu_selesai'])) : '00.00') . ' WIB');
        $sheet->getStyle('AA11')->getFont()->setBold(true);
        
        $sheet->getStyle('B9:AE11')->applyFromArray($borderThin);
        return ['row' => 12, 'isMfg2' => $isMfg2];
    }

    private function buildTblHeader32Col($sheet, $row, $isMfg2, $isPreventive = false) {
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        $sheet->mergeCells("B{$row}:C" . ($row+1)); $sheet->setCellValue("B{$row}", 'NO');
        if ($isMfg2) {
            $sheet->mergeCells("D{$row}:Q" . ($row+1)); $sheet->setCellValue("D{$row}", 'ITEM CHECK');
            $sheet->mergeCells("R{$row}:Z" . ($row+1)); $sheet->setCellValue("R{$row}", 'POINT CHECK');
        } else {
            $sheet->mergeCells("D{$row}:K" . ($row+1)); $sheet->setCellValue("D{$row}", 'ITEM CHECK');
            $sheet->mergeCells("L{$row}:S" . ($row+1)); $sheet->setCellValue("L{$row}", 'POINT CHECK');
            $sheet->mergeCells("T{$row}:Z" . ($row+1)); $sheet->setCellValue("T{$row}", 'STANDARD ITEM');
        }
        $sheet->mergeCells("AA{$row}:AB" . ($row+1)); $sheet->setCellValue("AA{$row}", "CHE\nCK");
        $sheet->mergeCells("AC{$row}:AE" . ($row+1)); $sheet->setCellValue("AC{$row}", 'REMARK');
        $sheet->getStyle("B{$row}:AE" . ($row+1))->getFont()->setBold(true);
        $sheet->getStyle("B{$row}:AE" . ($row+1))->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
        $sheet->getStyle("B{$row}:AE" . ($row+1))->applyFromArray($borderThin);
        return $row + 2;
    }

    private function buildFooter32Col($sheet, $row, $approvalData) {
        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]];
        $row++;
        $sheet->mergeCells("B{$row}:I{$row}"); $sheet->setCellValue("B{$row}", 'NOTE AND RECOMMENDATION');
        $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setSize(9);
        $row++;
        $boxStart = $row;
        for ($i = 0; $i < 4; $i++) {
            $sheet->mergeCells("B{$row}:AE{$row}");
            $sheet->setCellValue("B{$row}", '..........................................................................................................................................................................................................................................');
            $sheet->getStyle("B{$row}")->getFont()->getColor()->setARGB('FF999999');
            $sheet->getStyle("B{$row}")->getAlignment()->setVertical('bottom');
            $row++;
        }
        $sheet->getStyle("B{$boxStart}:AE" . ($row-1))->applyFromArray($borderThin);
        $row++;
        $sigRow = $row;
        $sheet->mergeCells("C{$row}:G{$row}"); $sheet->setCellValue("C{$row}", 'KETERANGAN CHECK LIST');
        $sheet->getStyle("C{$row}")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("C{$row}:G{$row}")->getAlignment()->setHorizontal('center');
        $row++;
        $sheet->setCellValue("C{$row}", '√'); $sheet->getStyle("C{$row}")->getFont()->setBold(true)->getColor()->setARGB('FF0D6EFD'); $sheet->setCellValue("D{$row}", ':'); $sheet->mergeCells("E{$row}:G{$row}"); $sheet->setCellValue("E{$row}", 'OK');
        $row++;
        $sheet->setCellValue("C{$row}", 'Δ'); $sheet->getStyle("C{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFFFA500'); $sheet->setCellValue("D{$row}", ':'); $sheet->mergeCells("E{$row}:G{$row}"); $sheet->setCellValue("E{$row}", 'PERLU TINDAKAN');
        $row++;
        $sheet->setCellValue("C{$row}", 'X'); $sheet->getStyle("C{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFDC3545'); $sheet->setCellValue("D{$row}", ':'); $sheet->mergeCells("E{$row}:G{$row}"); $sheet->setCellValue("E{$row}", 'TIDAK ADA');
        $sheet->getStyle("C{$sigRow}:G{$row}")->applyFromArray($borderThin);
        $sheet->getStyle("C".($sigRow+1).":C{$row}")->getAlignment()->setHorizontal('center');
        
        $row = $sigRow;
        $sheet->mergeCells("O{$row}:S{$row}"); $sheet->setCellValue("O{$row}", 'PREPARED');
        $sheet->mergeCells("T{$row}:X{$row}"); $sheet->setCellValue("T{$row}", 'CHECKED');
        $sheet->mergeCells("Y{$row}:AB{$row}"); $sheet->setCellValue("Y{$row}", 'APPROVED');
        $sheet->mergeCells("AC{$row}:AE{$row}"); $sheet->setCellValue("AC{$row}", 'APPROVED');
        $sheet->getStyle("O{$row}:AE{$row}")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("O{$row}:AE{$row}")->getAlignment()->setHorizontal('center');
        $row++;
        $sheet->mergeCells("O{$row}:S{$row}"); $sheet->setCellValue("O{$row}", 'INSPECTOR');
        $sheet->mergeCells("T{$row}:X{$row}"); $sheet->setCellValue("T{$row}", 'USER');
        $sheet->mergeCells("Y{$row}:AB{$row}"); $sheet->setCellValue("Y{$row}", 'SEC.HEAD MFG 2');
        $sheet->mergeCells("AC{$row}:AE{$row}"); $sheet->setCellValue("AC{$row}", 'SEC.HEAD MTN');
        $sheet->getStyle("O{$row}:AE{$row}")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("O{$row}:AE{$row}")->getAlignment()->setHorizontal('center');
        $row++;
        $sheet->mergeCells("O{$row}:S".($row+2)); 
        $sheet->mergeCells("T{$row}:X".($row+2)); 
        $sheet->mergeCells("Y{$row}:AB".($row+2)); 
        $sheet->mergeCells("AC{$row}:AE".($row+2)); 
        $row += 3;
        $sheet->mergeCells("O{$row}:S{$row}"); $sheet->setCellValue("O{$row}", strtoupper(explode(' - ', $approvalData['pic_name'] ?? '')[1] ?? 'PIC'));
        $sheet->mergeCells("T{$row}:X{$row}"); $sheet->setCellValue("T{$row}", strtoupper(explode(' - ', $approvalData['l1_name'] ?? '')[1] ?? '-'));
        $sheet->mergeCells("Y{$row}:AB{$row}"); $sheet->setCellValue("Y{$row}", strtoupper(explode(' - ', $approvalData['l2_name'] ?? '')[1] ?? '-'));
        $sheet->mergeCells("AC{$row}:AE{$row}"); $sheet->setCellValue("AC{$row}", strtoupper(explode(' - ', $approvalData['final_name'] ?? '')[1] ?? '-'));
        $sheet->getStyle("O{$row}:AE{$row}")->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle("O{$row}:AE{$row}")->getAlignment()->setHorizontal('center');
        $sheet->getStyle("O{$sigRow}:AE{$row}")->applyFromArray($borderThin);
    }
PHP;

if (strpos($c, 'private function build32ColMicroGrid') === false) {
    preg_match('/private function buildPreventiveExcelSheet.*?\{/s', $c, $m, PREG_OFFSET_CAPTURE);
    if (!empty($m)) {
        $c = substr_replace($c, $helpers . "\n\n" . $m[0][0], $m[0][1], strlen($m[0][0]));
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
echo "32-Column Grid Restored and Re-engineered!";
