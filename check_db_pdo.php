<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$stmt = $db->query("SELECT * FROM approval_bulanan WHERE lokasi='MFG 2' AND line='SECOND' AND kategori='Penerangan'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
