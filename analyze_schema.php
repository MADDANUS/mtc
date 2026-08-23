<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');

$tables = ['users', 'master_mesin', 'transaksi_check', 'laporan_abnormal', 'riwayat_mesin'];
foreach($tables as $table) {
    try {
        $stmt = $db->query("SHOW CREATE TABLE $table");
        foreach($stmt as $row) {
            echo $row['Create Table'] . PHP_EOL . PHP_EOL;
        }
    } catch (Exception $e) {}
}
?>
