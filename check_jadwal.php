<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$stmt = $db->query("SELECT id_jadwal, plant, departemen, kategori, tanggal_rencana FROM jadwal_preventive ORDER BY id_jadwal DESC LIMIT 3");
foreach($stmt as $row) {
    print_r($row);
}
?>
