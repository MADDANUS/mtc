<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'mtce_db');
$res = $mysqli->query("SELECT DISTINCT plan, departemen, jenis FROM master_mesin WHERE departemen = 'MFG 2' AND jenis IS NOT NULL AND jenis != '-'");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
