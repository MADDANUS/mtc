<?php
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$db = \Config\Database::connect();
$res = $db->query("SELECT id_parameter, kategori, jenis_check, departemen FROM master_parameter_check WHERE departemen = 'MFG 1' AND jenis_check = 'Overhaul' LIMIT 10")->getResultArray();
print_r($res);
