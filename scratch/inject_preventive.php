<?php
$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// Read rewrite_preventive.php
$m = file_get_contents('scratch/rewrite_preventive.php');
$m = str_replace('<?php', '', $m);
$newPreventive = 'private ' . trim($m);

// Extract buildPreventiveExcelSheet from current RiwayatController.php
preg_match('/private function buildPreventiveExcelSheet.*?(?=\n    private function buildOverhaulExcelSheet)/s', $c, $cMatches);
if (empty($cMatches)) {
    exit("Could not extract buildPreventiveExcelSheet from RiwayatController.php");
}
$currentPreventive = $cMatches[0];

// Replace it!
$c = str_replace($currentPreventive, $newPreventive . "\n", $c);
file_put_contents($f, $c);
echo "Injected NEW buildPreventiveExcelSheet logic!\n";
