<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$stmt = $db->query("UPDATE jadwal_preventive SET plant = 'Plant 1' WHERE plant IS NULL OR plant = ''");
echo 'Rows updated: ' . $stmt->rowCount();
?>
