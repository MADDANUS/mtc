<?php
$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// Read rewrite_overhaul.php
$m = file_get_contents('scratch/rewrite_overhaul.php');
$m = str_replace('<?php', '', $m);
$newOverhaul = 'private ' . trim($m);

// Extract buildOverhaulExcelSheet from current RiwayatController.php
preg_match('/private function buildOverhaulExcelSheet.*?(?=\n    public function edit|\n    private function buildSearchFilters)/s', $c, $cMatches);
if (empty($cMatches)) {
    exit("Could not extract buildOverhaulExcelSheet from RiwayatController.php");
}
$currentOverhaul = $cMatches[0];

// Replace it!
$c = str_replace($currentOverhaul, $newOverhaul . "\n", $c);
file_put_contents($f, $c);
echo "Injected NEW buildOverhaulExcelSheet logic!\n";
