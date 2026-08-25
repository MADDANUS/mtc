<?php
$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// Read massive_script_5645.txt
$m = file_get_contents('scratch/massive_script_5645.txt');

// Extract buildPreventiveExcelSheet from massive_script_5645.txt
preg_match('/private function buildPreventiveExcelSheet.*?(?=\n    private function buildOverhaulExcelSheet)/s', $m, $matches);
if (empty($matches)) {
    exit("Could not extract buildPreventiveExcelSheet from massive_script_5645.txt");
}
$goodPreventive = $matches[0];

// Extract buildPreventiveExcelSheet from current RiwayatController.php
preg_match('/private function buildPreventiveExcelSheet.*?(?=\n    private function buildOverhaulExcelSheet)/s', $c, $cMatches);
if (empty($cMatches)) {
    exit("Could not extract buildPreventiveExcelSheet from RiwayatController.php");
}
$currentPreventive = $cMatches[0];

// Replace it!
$c = str_replace($currentPreventive, $goodPreventive, $c);
file_put_contents($f, $c);
echo "Restored good Preventive logic!\n";
