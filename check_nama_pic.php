<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$stmt = $db->query("SELECT id_user, nama_pic FROM transaksi_check LIMIT 5");
foreach($stmt as $row) {
    echo $row['id_user'] . ' - ' . $row['nama_pic'] . PHP_EOL;
}
?>
