<?php
$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// Read massive_script_5645.txt
$m = file_get_contents('scratch/massive_script_5645.txt');

// Extract buildOverhaulExcelSheet from massive_script_5645.txt
preg_match('/private function buildOverhaulExcelSheet.*?(?=\nEOT;)/s', $m, $matches);
if (empty($matches)) {
    exit("Could not extract buildOverhaulExcelSheet from massive_script_5645.txt");
}
$goodOverhaul = $matches[0];

// Extract buildOverhaulExcelSheet from current RiwayatController.php
preg_match('/private function buildOverhaulExcelSheet.*?(?=\n    public function edit|\n    private function buildSearchFilters)/s', $c, $cMatches);
if (empty($cMatches)) {
    exit("Could not extract buildOverhaulExcelSheet from RiwayatController.php");
}
$currentOverhaul = $cMatches[0];

if ($goodOverhaul === $currentOverhaul) {
    echo "They are identical!\n";
} else {
    // Replace it!
    $c = str_replace($currentOverhaul, $goodOverhaul, $c);
    file_put_contents($f, $c);
    echo "Restored good Overhaul logic!\n";
}
