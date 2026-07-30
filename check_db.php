<?php
$db = new mysqli('localhost', 'root', '', 'mtce_db');
$res = $db->query("SELECT * FROM master_mesin WHERE no_mesin = 'A01'");
print_r($res->fetch_assoc());
