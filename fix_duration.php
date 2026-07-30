<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/laporan/durasi.php';
$content = file_get_contents($file);

// Kita buat fungsi inline di view (atau bisa juga di helper, tapi inline lebih cepat)
$phpHelper = <<<'EOD'
<?php
function formatDurasi($detik) {
    if ($detik === null) return '-';
    $jam = floor($detik / 3600);
    $menit = floor(($detik % 3600) / 60);
    $det = $detik % 60;
    if ($jam > 0) {
        return sprintf('%02d:%02d:%02d', $jam, $menit, $det);
    }
    return sprintf('%02d:%02d', $menit, $det);
}

function formatDurasiText($detik) {
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

$content = preg_replace('/(<?= view\(\'layout\/header\', \[\'title\' => \$title\]\) \?>)/', "$1\n$phpHelper", $content);

// Ganti format Rata-rata
$content = str_replace(
    "<?= gmdate('i \m\e\n\i\t s \d\e\t\i\k', \$rataDetik) ?>",
    "<?= formatDurasiText(\$rataDetik) ?>",
    $content
);

// Ganti format tabel
$content = str_replace(
    "<?= gmdate('i:s', (int) \$l['durasi_detik']) ?>",
    "<?= formatDurasi((int) \$l['durasi_detik']) ?>",
    $content
);

file_put_contents($file, $content);
echo "View durasi.php fixed.\n";
