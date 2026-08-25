<?php
// Fix Keterangan table alignment
$file1 = 'app/Views/partials/pdf_preventive.php';
$c1 = file_get_contents($file1);
$c1 = str_replace('<table class="keterangan-table"', '<table align="right" class="keterangan-table"', $c1);
file_put_contents($file1, $c1);

$file2 = 'app/Views/partials/pdf_overhaul.php';
$c2 = file_get_contents($file2);
$c2 = str_replace('<table class="keterangan-table"', '<table align="right" class="keterangan-table"', $c2);
file_put_contents($file2, $c2);

// Fix Signature table width
$file3 = 'app/Views/partials/pdf_signature.php';
$c3 = file_get_contents($file3);
$c3 = str_replace('<table style="width:100%;', '<table width="100%" style="width:100%;', $c3);
file_put_contents($file3, $c3);

echo "Done fixing alignments.";
?>
