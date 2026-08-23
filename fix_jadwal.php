<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$db->query("UPDATE jadwal_preventive SET plant = 'Plant 2' WHERE plant = '' OR plant IS NULL");
echo 'Done';
?>
