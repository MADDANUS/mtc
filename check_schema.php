<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$tables = ['transaksi_check', 'laporan_abnormal', 'riwayat_mesin'];
foreach($tables as $table) {
    echo "TABLE: $table\n";
    $stmt = $db->query("DESCRIBE $table");
    foreach($stmt as $row) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . "\n";
    }
    echo "\n";
}
?>
