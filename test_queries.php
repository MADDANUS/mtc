<?php
require 'vendor/autoload.php';
// Initialize CI4 environment
$pathsConfig = new \Config\Paths();
require rtrim($pathsConfig->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Models\TransaksiCheckModel;
use App\Models\LaporanAbnormalModel;
use App\Models\RiwayatMesinModel;
use CodeIgniter\Test\Fabricator;

echo "1. Testing TransaksiCheckModel queries...\n";
try {
    $model = new TransaksiCheckModel();
    // Test a standard query that uses COALESCE and LEFT JOINs
    $data = $model->getRiwayatFiltered([], null, 5);
    echo "   OK! Fetched " . count($data) . " riwayat check rows.\n";
    if (count($data) > 0) {
        echo "   Sample no_mesin: " . $data[0]['no_mesin'] . "\n";
    }
} catch (\Exception $e) {
    echo "   ERROR in TransaksiCheckModel: " . $e->getMessage() . "\n";
}

echo "2. Testing LaporanAbnormalModel queries...\n";
try {
    $abnormalModel = new LaporanAbnormalModel();
    $data = $abnormalModel->getAbnormalFiltered();
    echo "   OK! Fetched " . count($data) . " abnormal rows.\n";
    if (count($data) > 0) {
        echo "   Sample no_mesin: " . $data[0]['no_mesin'] . "\n";
    }
} catch (\Exception $e) {
    echo "   ERROR in LaporanAbnormalModel: " . $e->getMessage() . "\n";
}

echo "3. Testing RiwayatMesinModel queries...\n";
try {
    $rmModel = new RiwayatMesinModel();
    $data = $rmModel->findAll(5);
    echo "   OK! Fetched " . count($data) . " riwayat mesin rows.\n";
} catch (\Exception $e) {
    echo "   ERROR in RiwayatMesinModel: " . $e->getMessage() . "\n";
}

echo "DONE TESTING.\n";
?>
