<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require FCPATH . '../vendor/autoload.php';
// Boot framework
$app = \Config\Services::codeigniter();
$app->initialize();

$model = new \App\Models\TransaksiCheckModel();
$filters = [
    'plant' => 'Plant 1',
    'departemen' => 'MFG 1',
    'line' => 'Line 3',
    'status' => ['Approved', 'Approved Final', 'Approved L1', 'Approved L2'],
    'jenis_check' => 'Overhaul'
];

$res = $model->getRiwayatFiltered($filters);
echo "SQL: " . $model->db->getLastQuery()->getQuery() . "\n";
echo "Count: " . count($res) . "\n";
print_r(array_column($res, 'id_transaksi'));
