<?php
$files = ['app/Models/TransaksiCheckModel.php', 'app/Models/LaporanAbnormalModel.php', 'app/Models/RiwayatMesinModel.php'];
foreach($files as $file) {
    $c = file_get_contents($file);
    $c = str_replace("protected \$allowedFields = [", "protected \$allowedFields = [\n        'ss_no_mesin',", $c);
    file_put_contents($file, $c);
}
echo "Done";
?>
