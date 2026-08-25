<?php
$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// Find patterns like: $sheet->getStyle("B{$row}:I{$row}")->getAlignment()->setHorizontal('center')->setBold(true);
// Replace with: $sheet->getStyle("B{$row}:I{$row}")->getAlignment()->setHorizontal('center'); $sheet->getStyle("B{$row}:I{$row}")->getFont()->setBold(true);

$c = preg_replace_callback(
    '/(.*?)->getAlignment\(\)->(setHorizontal\([^)]+\))->setBold\(true\);/',
    function ($matches) {
        $base = $matches[1];
        $align = $matches[2];
        return $base . "->getAlignment()->" . $align . "; " . $base . "->getFont()->setBold(true);";
    },
    $c
);

file_put_contents($f, $c);
echo "Fixed all getAlignment()->...->setBold(true) errors!";
