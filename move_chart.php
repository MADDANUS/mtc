<?php
$file = "app/Views/abnormal/summary.php";
$lines = file($file);

$chartStart = -1;
$chartEnd = -1;

for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], 'GRAFIK TREN ABNORMALITAS DINAMIS') !== false) {
        $chartStart = $i - 1; 
    }
    if ($chartStart !== -1 && strpos($lines[$i], '</script>') !== false) {
        $chartEnd = $i;
        break;
    }
}

if ($chartStart !== -1 && $chartEnd !== -1) {
    $chartLines = array_splice($lines, $chartStart, $chartEnd - $chartStart + 1);
    
    $footerLine = -1;
    for ($i = 0; $i < count($lines); $i++) {
        if (strpos($lines[$i], "view('layout/footer')") !== false) {
            $footerLine = $i;
            break;
        }
    }
    
    if ($footerLine !== -1) {
        array_splice($lines, $footerLine, 0, $chartLines);
        file_put_contents($file, implode("", $lines));
        echo "Successfully moved chart to bottom.";
    } else {
        echo "Could not find footer.";
    }
} else {
    echo "Could not find chart block boundaries.";
}
?>
