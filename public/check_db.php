<?php
require 'vendor/autoload.php';
$app = Config\Services::codeigniter(new Config\App());
$app->initialize();
$db = \Config\Database::connect();
$query = $db->query("SELECT * FROM approval_bulanan WHERE lokasi='MFG 2' AND line='SECOND' AND kategori='Penerangan'");
print_r($query->getResultArray());
