<?php
$lines = file('app/Views/abnormal/summary.php');

$chartStart = 76;
$chartEnd = 303;

$chartLines = array_splice($lines, $chartStart, $chartEnd - $chartStart + 1);
$chartLines[] = "\n";
$chartLines[] = "<br>\n";

$footerLine = -1;
foreach ($lines as $i => $line) {
    if (strpos($line, "view('layout/footer')") !== false) {
        $footerLine = $i;
        break;
    }
}

if ($footerLine !== -1) {
    array_splice($lines, $footerLine, 0, $chartLines);
    file_put_contents('app/Views/abnormal/summary.php', implode("", $lines));
    echo "Success";
} else {
    echo "Footer not found";
}
?>
