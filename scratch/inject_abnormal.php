<?php
$f = 'app/Controllers/AbnormalController.php';
$c = file_get_contents($f);

// Read the helper
$m = file_get_contents('scratch/rewrite_abnormal.php');
$m = str_replace('<?php', '', $m);
$helper = 'private ' . trim($m);

// 1. Inject the helper before streamAbnormalExcel
$targetSearch = '    private function streamAbnormalExcel';
if (strpos($c, 'private function buildAbnormalExcelSheet') === false) {
    $c = str_replace($targetSearch, $helper . "\n\n" . $targetSearch, $c);
}

// 2. Refactor streamAbnormalExcel
$newStream = <<<'EOD'
    private function streamAbnormalExcel(array $reports, string $departemen, string $kategori, string $bulan): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Abnormal Report');
        
        $this->buildAbnormalExcelSheet($sheet, $reports, $departemen, $kategori, $bulan);

        $filename = 'Laporan_Abnormal_' . str_replace(' ', '_', $kategori) . '_' . str_replace(' ', '_', $departemen) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
EOD;

preg_match('/private function streamAbnormalExcel.*?(?=\n    \/\/ ─── Helper: Multi-sheet)/s', $c, $matches);
if (!empty($matches)) {
    $c = str_replace($matches[0], $newStream, $c);
}

// 3. Refactor streamAbnormalAllExcel
$newStreamAll = <<<'EOD'
    private function streamAbnormalAllExcel(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheetIdx = 0;
        foreach ($data['allCategoryReports'] ?? [] as $catData) {
            $kategori = $catData['kategori'] ?? 'Sheet';
            $reports  = $catData['reports']  ?? [];
            $sheet = $sheetIdx === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $sheet->setTitle(substr($kategori, 0, 31));

            $this->buildAbnormalExcelSheet($sheet, $reports, $data['lokasiFilter'] ?? '', $kategori, $data['bulanFilter'] ?? date('Y-m'));
            $sheetIdx++;
        }

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'Laporan_Abnormal_Semua_Kategori_' . str_replace(' ', '_', $data['lokasiFilter'] ?? '') . '_' . ($data['bulanFilter'] ?? date('Y-m')) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
EOD;

preg_match('/private function streamAbnormalAllExcel.*?\n    \}/s', $c, $matches2);
if (!empty($matches2)) {
    $c = str_replace($matches2[0], $newStreamAll, $c);
}

file_put_contents($f, $c);
echo "AbnormalController refactored!\n";
