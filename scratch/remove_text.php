<?php
$files1 = [
    'app/Views/partials/pdf_overhaul.php',
    'app/Views/partials/pdf_preventive.php'
];

foreach ($files1 as $f) {
    $c = file_get_contents($f);
    $c = preg_replace('/<div style="font-size:0\.6rem;[^>]+>\s*<div>The Future<\/div>\s*<div>In Our Hands<\/div>\s*<\/div>/s', '', $c);
    file_put_contents($f, $c);
}

$files2 = [
    'app/Views/kontrol/pdf.php',
    'app/Views/kontrol/pdf_all.php'
];

foreach ($files2 as $f) {
    $c = file_get_contents($f);
    $c = preg_replace('/<div style="font-size:0\.6rem;[^>]+>\s*<div>The Future in Our<\/div>\s*<div>Hands<\/div>\s*<\/div>/s', '', $c);
    file_put_contents($f, $c);
}
echo "Done";
