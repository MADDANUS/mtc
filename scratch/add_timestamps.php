<?php
$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// We want to replace the `if (!empty($header['...'])) $sheet->setCellValue(..., "[ Selesai ]");` lines
// with logic that appends the date below it.

// Let's create a helper function that formats the date
$helper = <<<'PHP'
function formatTgl($dateStr) {
    if (empty($dateStr)) return '';
    return date('d/m/y H:i', strtotime($dateStr));
}
PHP;

// For Preventive
$prevFind = <<<'PHP'
            if (!empty($header['waktu_selesai'])) $sheet->setCellValue("{$c1}{$rowSpace}", "[ Selesai ]");
            if ($header['status'] === 'Approved') $sheet->setCellValue("{$c2}{$rowSpace}", "[ Disetujui ]");
PHP;

$prevRep = <<<'PHP'
            if (!empty($header['waktu_selesai'])) {
                $tgl = date('d/m/Y H:i', strtotime($header['waktu_selesai']));
                $sheet->setCellValue("{$c1}{$rowSpace}", "[ Selesai ]\n$tgl");
            }
            if ($header['status'] === 'Approved' && !empty($header['approved_at'])) {
                $tgl = date('d/m/Y H:i', strtotime($header['approved_at']));
                $sheet->setCellValue("{$c2}{$rowSpace}", "[ Disetujui ]\n$tgl");
            } elseif ($header['status'] === 'Approved') {
                $sheet->setCellValue("{$c2}{$rowSpace}", "[ Disetujui ]");
            }
PHP;

$c = str_replace($prevFind, $prevRep, $c);

// For Kontrol
$konFind = <<<'PHP'
            if (!empty($header['waktu_selesai'])) $sheet->setCellValue("{$c1}{$rowSpace}", "[ Selesai ]");
            if (!empty($header['approval_l2_by'])) $sheet->setCellValue("{$c2}{$rowSpace}", "[ Disetujui ]");
            if ($header['status'] === 'Approved') $sheet->setCellValue("{$c3}{$rowSpace}", "[ Disetujui ]");
PHP;

$konRep = <<<'PHP'
            if (!empty($header['waktu_selesai'])) {
                $tgl = date('d/m/Y H:i', strtotime($header['waktu_selesai']));
                $sheet->setCellValue("{$c1}{$rowSpace}", "[ Selesai ]\n$tgl");
            }
            if (!empty($header['approval_l2_by']) && !empty($header['approval_l2_at'])) {
                $tgl = date('d/m/Y H:i', strtotime($header['approval_l2_at']));
                $sheet->setCellValue("{$c2}{$rowSpace}", "[ Disetujui ]\n$tgl");
            } elseif (!empty($header['approval_l2_by'])) {
                $sheet->setCellValue("{$c2}{$rowSpace}", "[ Disetujui ]");
            }
            if ($header['status'] === 'Approved' && !empty($header['approved_at'])) {
                $tgl = date('d/m/Y H:i', strtotime($header['approved_at']));
                $sheet->setCellValue("{$c3}{$rowSpace}", "[ Disetujui ]\n$tgl");
            } elseif ($header['status'] === 'Approved') {
                $sheet->setCellValue("{$c3}{$rowSpace}", "[ Disetujui ]");
            }
PHP;

$c = str_replace($konFind, $konRep, $c);


// For Overhaul
$ovFind = <<<'PHP'
            if (!empty($header['waktu_selesai'])) $sheet->setCellValue("{$c1}{$rowSpace}", "[ Selesai ]");
            if (!empty($header['approval_l1_by'])) $sheet->setCellValue("{$c2}{$rowSpace}", "[ Diperiksa ]");
            if (!empty($header['approval_l2_by'])) $sheet->setCellValue("{$c3}{$rowSpace}", "[ Disetujui ]");
            if ($header['status'] === 'Approved') $sheet->setCellValue("{$c4}{$rowSpace}", "[ Disetujui ]");
PHP;

$ovRep = <<<'PHP'
            if (!empty($header['waktu_selesai'])) {
                $tgl = date('d/m/Y H:i', strtotime($header['waktu_selesai']));
                $sheet->setCellValue("{$c1}{$rowSpace}", "[ Selesai ]\n$tgl");
            }
            if (!empty($header['approval_l1_by']) && !empty($header['approval_l1_at'])) {
                $tgl = date('d/m/Y H:i', strtotime($header['approval_l1_at']));
                $sheet->setCellValue("{$c2}{$rowSpace}", "[ Diperiksa ]\n$tgl");
            } elseif (!empty($header['approval_l1_by'])) {
                $sheet->setCellValue("{$c2}{$rowSpace}", "[ Diperiksa ]");
            }
            if (!empty($header['approval_l2_by']) && !empty($header['approval_l2_at'])) {
                $tgl = date('d/m/Y H:i', strtotime($header['approval_l2_at']));
                $sheet->setCellValue("{$c3}{$rowSpace}", "[ Disetujui ]\n$tgl");
            } elseif (!empty($header['approval_l2_by'])) {
                $sheet->setCellValue("{$c3}{$rowSpace}", "[ Disetujui ]");
            }
            if ($header['status'] === 'Approved' && !empty($header['approved_at'])) {
                $tgl = date('d/m/Y H:i', strtotime($header['approved_at']));
                $sheet->setCellValue("{$c4}{$rowSpace}", "[ Disetujui ]\n$tgl");
            } elseif ($header['status'] === 'Approved') {
                $sheet->setCellValue("{$c4}{$rowSpace}", "[ Disetujui ]");
            }
PHP;

$c = str_replace($ovFind, $ovRep, $c);

file_put_contents($f, $c);
echo "Timestamps appended correctly!";
