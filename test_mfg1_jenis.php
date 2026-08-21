<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'mtce_db');
$res = $mysqli->query("SELECT DISTINCT jenis FROM master_mesin WHERE departemen = 'MFG 1'");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
