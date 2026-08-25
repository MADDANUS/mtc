<?php
$f = 'app/Controllers/LaporanController.php';
$c = file_get_contents($f);

// Read rewrite_durasi.php
$m = file_get_contents('scratch/rewrite_durasi.php');
$m = str_replace('<?php', '', $m);
$newMethod = trim($m);

// Extract durasiExcel from current LaporanController.php
preg_match('/public function durasiExcel.*?(?=\n    \/\*| \}\n\})/s', $c, $cMatches);
if (!empty($cMatches)) {
    $c = str_replace($cMatches[0], $newMethod, $c);
} else {
    // try to match till the end of the class
    preg_match('/public function durasiExcel.*?\n    \}/s', $c, $cMatches2);
    if (!empty($cMatches2)) {
        $c = str_replace($cMatches2[0], $newMethod, $c);
    }
}

file_put_contents($f, $c);
echo "LaporanController refactored!\n";
