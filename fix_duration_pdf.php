<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/laporan/durasi_pdf.php';
$content = file_get_contents($file);

$phpHelper = <<<'EOD'
<?php
function formatDurasiPdf($detik) {
    if ($detik === null) return '-';
    $jam = floor($detik / 3600);
    $menit = floor(($detik % 3600) / 60);
    $det = $detik % 60;
    if ($jam > 0) {
        return sprintf('%02d:%02d:%02d', $jam, $menit, $det);
    }
    return sprintf('%02d:%02d', $menit, $det);
}

function formatDurasiTextPdf($detik) {
    if ($detik === null) return '-';
    $jam = floor($detik / 3600);
    $menit = floor(($detik % 3600) / 60);
    $det = $detik % 60;
    
    $parts = [];
    if ($jam > 0) $parts[] = $jam . ' jam';
    if ($menit > 0 || $jam > 0) $parts[] = $menit . ' menit';
    $parts[] = $det . ' detik';
    return implode(' ', $parts);
}
?>
EOD;

$content = str_replace('</style>', "</style>\n$phpHelper", $content);

// Ganti format Rata-rata
$content = str_replace(
    "<?= gmdate('i \m\e\n\i\t s \d\e\t\i\k', \$rataDetik) ?>",
    "<?= formatDurasiTextPdf(\$rataDetik) ?>",
    $content
);

// Ganti format tabel
$content = str_replace(
    "<?= gmdate('i:s', (int) \$l['durasi_detik']) ?>",
    "<?= formatDurasiPdf((int) \$l['durasi_detik']) ?>",
    $content
);

file_put_contents($file, $content);
echo "View durasi_pdf.php fixed.\n";
