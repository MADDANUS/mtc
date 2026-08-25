<?php
$logoPhp = <<<'EOT'
        <?php 
          $logoPath = FCPATH . 'uploads/nsi_logo.png';
          if (file_exists($logoPath)) {
              $logoData = base64_encode(file_get_contents($logoPath));
              echo '<img src="data:image/png;base64,' . $logoData . '" style="max-width: 80px; max-height: 80px; display: block; margin: 0 auto;">';
          } else {
              echo '<div style="font-weight:bold; color:blue; font-size:24px;">NSI</div>';
          }
        ?>
EOT;

$files1 = [
    'app/Views/partials/pdf_overhaul.php',
    'app/Views/partials/pdf_preventive.php'
];

foreach ($files1 as $f) {
    $c = file_get_contents($f);
    $c = preg_replace('/<div style="width:40px; height:40px;.*?<\/div>\s*<\/div>/s', $logoPhp, $c);
    file_put_contents($f, $c);
}

$files2 = [
    'app/Views/kontrol/pdf.php',
    'app/Views/kontrol/pdf_all.php'
];

foreach ($files2 as $f) {
    $c = file_get_contents($f);
    $c = preg_replace('/<div style="width:44px; height:44px;.*?<\/div>\s*<\/div>/s', $logoPhp, $c);
    file_put_contents($f, $c);
}
echo "Done";
