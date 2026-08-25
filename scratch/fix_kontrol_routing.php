<?php
$f = 'app/Controllers/KontrolController.php';
$c = file_get_contents($f);

// 1. Fix excelAllCategories and excelAllSummary
$oldControllerMethods = <<<'PHP'
    // ─── Excel: Semua kategori di 1 departemen/line ───────────────────────
    public function excelAllCategories()
    {
        $service = new KontrolService();
        $data    = $service->pdfAllCategories($this->request);
        $this->streamKontrolSummaryExcel($data);
    }

    // ─── Excel: Ringkasan semua area ─────────────────────────────────────
    public function excelAllSummary()
    {
        $service = new KontrolService();
        $data    = $service->pdfAllSummary($this->request);
        $this->streamKontrolSummaryExcel($data);
    }
PHP;

$newControllerMethods = <<<'PHP'
    // ─── Excel: Semua kategori di 1 departemen/line ───────────────────────
    public function excelAllCategories()
    {
        $service = new KontrolService();
        $data    = $service->pdfAllCategories($this->request);
        $this->streamKontrolAllExcel($data);
    }

    // ─── Excel: Ringkasan semua area (Summary Dashboard) ─────────────────
    public function excelAllSummary()
    {
        $service = new KontrolService();
        $data    = $service->summary($this->request);
        $this->streamKontrolSummaryExcel($data);
    }
PHP;

if (strpos($c, 'public function excelAllCategories()') !== false) {
    // Manually replace just in case the old block string doesn't match exactly
    $c = preg_replace('/public function excelAllCategories\(\).*?\{.*?\}/s', 
        "public function excelAllCategories()\n    {\n        \$service = new KontrolService();\n        \$data    = \$service->pdfAllCategories(\$this->request);\n        \$this->streamKontrolAllExcel(\$data);\n    }", 
        $c);
        
    $c = preg_replace('/public function excelAllSummary\(\).*?\{.*?\}/s', 
        "public function excelAllSummary()\n    {\n        \$service = new KontrolService();\n        \$data    = \$service->summary(\$this->request);\n        \$this->streamKontrolSummaryExcel(\$data);\n    }", 
        $c);
}

// 2. Add streamKontrolSummaryExcel function
// We can insert it before streamKontrolExcel
$insertPos = strpos($c, 'private function streamKontrolExcel(array $data): void');
if ($insertPos !== false) {
    $summaryFunc = <<<'PHP'
    private function streamKontrolSummaryExcel(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary Checklist Control');

        $bulan = $data['bulan'] ?? date('Y-m');
        $bulanList = $data['bulanList'] ?? [];
        $bulanLabel = $bulanList[$bulan] ?? $bulan;
        $summaryRows = $data['summaryRows'] ?? [];

        // Title
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'RINGKASAN CHECKLIST CONTROL');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'BULAN: ' . strtoupper($bulanLabel));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header style
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F172A']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        // Data style
        $dataStyle = [
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];

        // Headers
        $row = 4;
        $headers = ['PLANT', 'DEPARTEMEN', 'LINE', 'KATEGORI', 'PROGRES PENGECEKAN', 'STATUS APPROVAL', 'BULAN'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $row, $h);
            $col++;
        }
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray($headerStyle);
        $sheet->getRowDimension($row)->setRowHeight(30);

        // Data
        $row++;
        if (empty($summaryRows)) {
            $sheet->mergeCells("A{$row}:G{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada data ditemukan.');
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($dataStyle);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            foreach ($summaryRows as $r) {
                $sheet->setCellValue('A' . $row, $r['plant'] ?? '');
                $sheet->setCellValue('B' . $row, $r['departemen'] ?? '');
                $sheet->setCellValue('C' . $row, $r['line'] ?? '');
                $sheet->setCellValue('D' . $row, $r['kategori'] ?? '');
                
                // Progress
                $checked = $r['checked'] ?? 0;
                $total = $r['total'] ?? 0;
                $percent = $r['percent'] ?? 0;
                $sheet->setCellValue('E' . $row, "{$checked}/{$total} ({$percent}%)");
                
                // Status
                $sheet->setCellValue('F' . $row, $r['statusText'] ?? '');
                
                // Bulan
                $sheet->setCellValue('G' . $row, $bulanLabel);

                $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($dataStyle);
                
                // Color codes for status and progress
                if ($percent == 100) {
                    $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FF198754'); // Success green
                } else {
                    $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB('FF0D6EFD'); // Primary blue
                }

                $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Checklist_Control_Ringkasan_' . $bulan . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }

PHP;
    $c = substr($c, 0, $insertPos) . $summaryFunc . substr($c, $insertPos);
    file_put_contents($f, $c);
    echo "Summary function added and method routings fixed!\n";
} else {
    echo "Could not find streamKontrolExcel insertion point.\n";
}

