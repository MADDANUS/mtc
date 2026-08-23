<?php
namespace App\Controllers;
use CodeIgniter\Controller;
class TestQueries extends Controller {
    public function index() {
        echo "1. Testing TransaksiCheckModel queries...\n";
        try {
            $model = new \App\Models\TransaksiCheckModel();
            $data = $model->getRiwayatFiltered([], null, 5);
            echo "   OK! Fetched " . count($data) . " riwayat check rows.\n";
        } catch (\Exception $e) {
            echo "   ERROR in TransaksiCheckModel: " . $e->getMessage() . "\n";
        }

        echo "2. Testing LaporanAbnormalModel queries...\n";
        try {
            $abnormalModel = new \App\Models\LaporanAbnormalModel();
            $data = $abnormalModel->getAbnormalFiltered();
            echo "   OK! Fetched " . count($data) . " abnormal rows.\n";
        } catch (\Exception $e) {
            echo "   ERROR in LaporanAbnormalModel: " . $e->getMessage() . "\n";
        }
        
        echo "3. Testing Dashboard logic...\n";
        try {
            $d = new \App\Models\MesinModel();
            $d->findAll(1);
            echo "   OK! Dashboard / MesinModel works.\n";
        } catch (\Exception $e) {
            echo "   ERROR in MesinModel: " . $e->getMessage() . "\n";
        }
    }
}
