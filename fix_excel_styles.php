<?php
$file = 'app/Views/partials/pdf_preventive.php';
$content = file_get_contents($file);

$content = str_replace(
    'class="kop-table-title"', 
    'style="background-color:#92b0d6; text-align:center; font-weight:bold; font-size:13px; color:#000;" bgcolor="#92b0d6"', 
    $content
);
$content = str_replace('background-color:#f2f2f2;"', 'background-color:#f2f2f2;" bgcolor="#f2f2f2"', $content);
$content = str_replace('<th style="width:25%;', '<th width="25%" style="width:25%;', $content);
$content = str_replace('<th style="width:20%;', '<th width="20%" style="width:20%;', $content);
$content = str_replace('<th style="width:10%;', '<th width="10%" style="width:10%;', $content);

file_put_contents($file, $content);

$file2 = 'app/Views/partials/pdf_overhaul.php';
$content2 = file_get_contents($file2);
$content2 = str_replace(
    'class="kop-table-title"', 
    'style="background-color:#92b0d6; text-align:center; font-weight:bold; font-size:13px; color:#000;" bgcolor="#92b0d6"', 
    $content2
);
$content2 = str_replace('background-color:#f2f2f2;"', 'background-color:#f2f2f2;" bgcolor="#f2f2f2"', $content2);
$content2 = str_replace('<th style="width:25%;', '<th width="25%" style="width:25%;', $content2);
$content2 = str_replace('<th style="width:20%;', '<th width="20%" style="width:20%;', $content2);
$content2 = str_replace('<th style="width:10%;', '<th width="10%" style="width:10%;', $content2);
file_put_contents($file2, $content2);

echo "Done modifying styles.";
?>
