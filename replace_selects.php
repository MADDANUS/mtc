<?php
$file = 'app/Models/TransaksiCheckModel.php';
$content = file_get_contents($file);

// Replace master_mesin.no_mesin with COALESCE(master_mesin.no_mesin, transaksi_check.ss_no_mesin) as no_mesin where appropriate
// But wait, some already have 'master_mesin.no_mesin as nama_mesin'. Let's replace 'master_mesin.no_mesin' -> 'COALESCE(master_mesin.no_mesin, transaksi_check.ss_no_mesin)' 
// However, we must be careful not to replace it in ->join or WHERE clauses if possible.
// Actually, replacing 'master_mesin.no_mesin,' with 'COALESCE(master_mesin.no_mesin, transaksi_check.ss_no_mesin) as no_mesin,' is safe for selects.

$replacements = [
    'master_mesin.no_mesin,' => 'COALESCE(master_mesin.no_mesin, transaksi_check.ss_no_mesin) as no_mesin,',
    'master_mesin.no_mesin as nama_mesin' => 'COALESCE(master_mesin.no_mesin, transaksi_check.ss_no_mesin) as nama_mesin',
    'users.nama as nama_staff' => 'COALESCE(users.nama, transaksi_check.nama_pic) as nama_staff',
];

$replaced = strtr($content, $replacements);

if ($replaced !== $content) {
    file_put_contents($file, $replaced);
    echo "Replaced fields in TransaksiCheckModel.\n";
}
?>
