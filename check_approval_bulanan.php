<?php
$mysqli = new mysqli('localhost', 'root', '', 'mtce_db');
$res = $mysqli->query("SELECT * FROM approval_bulanan");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
