<?php
// update_plant_data.php

$mysqli = new mysqli("localhost", "root", "", "mtce_db");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$tables = [
    'master_mesin',
    'master_line',
    'users',
    'riwayat_mesin',
    'ceklis_kontrol',
    'laporan_abnormal',
    'approval_bulanan',
    'jadwal_preventive',
    'transaksi_check',
    'master_parameter_check'
];

foreach ($tables as $table) {
    // Check if the column exists to be safe
    $result = $mysqli->query("SHOW COLUMNS FROM `$table` LIKE 'plant'");
    if ($result && $result->num_rows > 0) {
        $sql1 = "UPDATE `$table` SET `plant` = REPLACE(`plant`, 'Plan 1', 'Plant 1') WHERE `plant` LIKE '%Plan 1%'";
        $sql2 = "UPDATE `$table` SET `plant` = REPLACE(`plant`, 'Plan 2', 'Plant 2') WHERE `plant` LIKE '%Plan 2%'";
        
        $mysqli->query($sql1);
        $mysqli->query($sql2);
        echo "Updated $table\n";
    }
}

$mysqli->close();
echo "Done.\n";
?>
