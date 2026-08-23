<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');

echo "1. Checking Foreign Keys:\n";
$stmt = $db->query("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = 'mtce_db' AND (TABLE_NAME = 'transaksi_check' OR TABLE_NAME = 'laporan_abnormal')");
foreach ($stmt as $row) {
    echo "   " . $row['TABLE_NAME'] . " -> " . $row['COLUMN_NAME'] . " references " . $row['REFERENCED_TABLE_NAME'] . "\n";
}

echo "2. Testing COALESCE Query (similar to TransaksiCheckModel):\n";
$stmt = $db->query("SELECT tc.id_transaksi, COALESCE(mm.no_mesin, tc.ss_no_mesin) as no_mesin FROM transaksi_check tc LEFT JOIN master_mesin mm ON tc.id_mesin = mm.id_mesin LIMIT 5");
foreach ($stmt as $row) {
    echo "   Transaksi ID: " . $row['id_transaksi'] . " | Mesin: " . $row['no_mesin'] . "\n";
}

echo "3. Checking Log Tables exist:\n";
$stmt = $db->query("SHOW TABLES LIKE 'log_hapus_%'");
foreach ($stmt as $row) {
    echo "   Found table: " . $row[0] . "\n";
}
?>
