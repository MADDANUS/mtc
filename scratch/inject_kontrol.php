<?php
$f = 'app/Controllers/KontrolController.php';
$c = file_get_contents($f);

// Read the helper
$m = file_get_contents('scratch/rewrite_kontrol.php');
$m = str_replace('<?php', '', $m);
$helper = 'private ' . trim($m);

// 1. Inject the helper before streamKontrolAllExcel
$targetSearch = '    private function streamKontrolAllExcel';
if (strpos($c, 'private function buildKontrolExcelSheet') === false) {
    $c = str_replace($targetSearch, $helper . "\n\n" . $targetSearch, $c);
}

// 2. Refactor streamKontrolAllExcel
$newStreamAll = <<<'EOD'
    private function streamKontrolAllExcel(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheetIdx = 0;
        $allGrids = $data['allGrids'] ?? [];
        $bulan = $data['bulan'] ?? '';
        $departemen = $data['departemen'] ?? '';
        $line = $data['line'] ?? '';

        foreach ($allGrids as $gridData) {
            $kategori = $gridData['kategori'] ?? 'Sheet';
            if ($sheetIdx === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet();
            }
            $sheet->setTitle(substr($kategori, 0, 31));
            
            $this->buildKontrolExcelSheet($sheet, $gridData, $bulan, $departemen, $line);
            $sheetIdx++;
        }

        $filename = 'Checklist_Control_All_' . str_replace(' ', '_', $departemen) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
EOD;

preg_match('/private function streamKontrolAllExcel.*?(?=\n    \/\*)/s', $c, $matches);
if (!empty($matches)) {
    $c = str_replace($matches[0], $newStreamAll, $c);
} else {
    // If there's no comment after it, try to match till the end of class
    preg_match('/private function streamKontrolAllExcel.*?\n    \}/s', $c, $matches);
    if (!empty($matches)) {
        $c = str_replace($matches[0], $newStreamAll, $c);
    }
}

// 3. Refactor exportExcel
$newExport = <<<'EOD'
    public function exportExcel($kategori, $departemen, $line = null)
    {
        $data = $this->prepareDataExport($kategori, $departemen, $line);
        if (!$data) return redirect()->back()->with('error', 'Gagal memproses data export.');

        if ($kategori === 'all') {
            $this->streamKontrolAllExcel($data);
            return;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $item = [
            'kategori' => $kategori,
            'departemen' => $departemen,
            'line' => $line,
            'grid' => $data['grid'],
            'hasSchedule' => $data['hasSchedule'],
            'columnDates' => $data['columnDates'],
            'approvalData' => $data['approvalData']
        ];
        
        $this->buildKontrolExcelSheet($sheet, $item, $data['bulan'], $departemen, $line);

        $filename = 'Checklist_Control_' . str_replace(' ', '_', $kategori) . '_' . str_replace(' ', '_', $departemen) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
EOD;

preg_match('/public function exportExcel.*?(?=\n    \/\/ ─── Helper)/s', $c, $matches2);
if (!empty($matches2)) {
    $c = str_replace($matches2[0], $newExport, $c);
}

file_put_contents($f, $c);
echo "KontrolController refactored!\n";
