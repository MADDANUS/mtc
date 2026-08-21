<?php
define('FCPATH', __DIR__ . '/../public/');
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . '/bootstrap.php';
$db = \Config\Database::connect();
$db->table('master_line')->insert(['nama_line' => 'Line 1', 'departemen' => 'MFG 1', 'plan' => 'Plan 2']);
$db->table('master_line')->insert(['nama_line' => 'Line 1', 'departemen' => 'MFG 2', 'plan' => 'Plan 2']);
echo "Inserted Plan 2 lines.\n";
