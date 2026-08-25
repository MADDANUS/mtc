<?php
$a = file('scratch/massive_script_5645.txt');
$b = file('scratch/massive_script_5793.txt');
foreach ($a as $i => $line) {
    if (isset($b[$i]) && $line !== $b[$i]) {
        echo "Line $i:\n  A: " . trim($line) . "\n  B: " . trim($b[$i]) . "\n";
    }
}
if (count($a) !== count($b)) {
    echo "Length A: " . count($a) . " Length B: " . count($b) . "\n";
}
