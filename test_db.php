<?php
$mysqli = new mysqli('127.0.0.1', 'root', '', 'mtce_db');
$res = $mysqli->query('SELECT plan, departemen, nama_line FROM master_line');
while($row = $res->fetch_assoc()) {
    print_r($row);
}
