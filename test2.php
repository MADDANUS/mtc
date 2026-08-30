<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$stmt = $db->query("SELECT DISTINCT jenis FROM master_mesin WHERE departemen = 'MFG 1'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
