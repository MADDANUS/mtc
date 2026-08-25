<?php
$lines = file('app/Views/abnormal/summary.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'GRAFIK TREN ABNORMALITAS DINAMIS') !== false) echo "Chart Start: " . ($i-1) . "\n";
    if (strpos($line, '</script>') !== false && $i > 250 && $i < 350) echo "Chart End: " . $i . "\n";
    if (strpos($line, 'view(\'layout/footer\')') !== false) echo "Footer: " . $i . "\n";
}
?>
