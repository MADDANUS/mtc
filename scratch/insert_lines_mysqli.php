<?php
$mysqli = new mysqli("localhost", "root", "", "mtce_db");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}
$mysqli->query("INSERT INTO master_line (nama_line, departemen, plan) VALUES ('Line 1', 'MFG 1', 'Plan 2')");
$mysqli->query("INSERT INTO master_line (nama_line, departemen, plan) VALUES ('Line 1', 'MFG 2', 'Plan 2')");
echo "Inserted Plan 2 lines.\n";
