<?php
$mysqli = new mysqli('localhost', 'root', '', 'mtce_db');
$res = $mysqli->query("SELECT * FROM users WHERE role='sheadprd'");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
