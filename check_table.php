<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$stmt = $db->query('SHOW CREATE TABLE transaksi_check');
echo $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'];
?>
