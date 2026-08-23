<?php
$mysqli = new mysqli('localhost', 'root', '', 'mtce_db');
if ($mysqli->connect_error) {
    die('Connect Error: ' . $mysqli->connect_error);
}

$query = "DELETE FROM ceklis_kontrol 
          WHERE NOT EXISTS (
              SELECT 1 FROM transaksi_check 
              WHERE transaksi_check.id_mesin = ceklis_kontrol.id_mesin 
              AND transaksi_check.kategori = ceklis_kontrol.kategori 
              AND transaksi_check.status IN ('Approved L1', 'Approved L2', 'Approved', 'Approved Final', 'Final')
          )";

if ($mysqli->query($query)) {
    echo "Deleted " . $mysqli->affected_rows . " rows.\n";
} else {
    echo "Error: " . $mysqli->error;
}
