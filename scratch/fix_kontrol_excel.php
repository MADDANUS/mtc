<?php
$f = 'app/Controllers/KontrolController.php';
$c = file_get_contents($f);

// 1. Remove EDIT_AS_ONECELL
$c = preg_replace('/\$drawing->setEditAs\(\\\\PhpOffice\\\\PhpSpreadsheet\\\\Worksheet\\\\Drawing::EDIT_AS_ONECELL\);\s*/', '', $c);

// 2. Replace streamKontrolExcel entirely
// We know it starts at "private function streamKontrolExcel(array $data): void" and ends before "private function buildKontrolExcelSheet"
$startStr = 'private function streamKontrolExcel(array $data): void';
$endStr = 'private function buildKontrolExcelSheet';

$posStart = strpos($c, $startStr);
$posEnd = strpos($c, $endStr, $posStart);

if ($posStart !== false && $posEnd !== false) {
    // The new body
    $newBody = <<<'PHP'
private function streamKontrolExcel(array $data): void
    {
        $wrapperData = [
            'bulan'      => $data['bulan'] ?? '',
            'departemen' => $data['departemen'] ?? '',
            'line'       => $data['line'] ?? '',
            'allGrids'   => [$data]
        ];
        $this->streamKontrolAllExcel($wrapperData);
    }

    // ─── Helper: build Excel untuk all-categories / all-summary ──────────
    
PHP;
    
    $c = substr($c, 0, $posStart) . $newBody . substr($c, $posEnd);
}

// 3. One more thing: I changed the OffsetY for KontrolController to 30 in the previous step, and rowheight to 85.
$c = str_replace('setOffsetY(20)', 'setOffsetY(30)', $c);
$c = str_replace('setRowHeight(70)', 'setRowHeight(85)', $c);

file_put_contents($f, $c);
echo "Rewrote streamKontrolExcel completely and cleanly!";
