<?php
$lines = file('app/Views/abnormal/summary.php');
foreach ($lines as $i => $line) {
    if (strpos($line, 'GRAFIK TREN ABNORMALITAS DINAMIS') !== false) echo "Chart start at line " . $i . "\n";
    if (strpos($line, '<!-- Container grafik ApexCharts -->') !== false) echo "Apex container at line " . $i . "\n";
    if (strpos($line, '<table class="table table-hover') !== false) echo "Table start at line " . $i . "\n";
    if (strpos($line, 'view(\'layout/footer\')') !== false) echo "Footer at line " . $i . "\n";
}
?>
