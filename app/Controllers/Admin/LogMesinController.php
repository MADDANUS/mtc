<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LogAktivitasMesinModel;

class LogMesinController extends BaseController
{
    public function index()
    {
        $logModel = new LogAktivitasMesinModel();
        
        $page = (int) $this->request->getVar('page') ?: 1;
        $perPage = (int) $this->request->getVar('per_page') ?: 15;
        
        $filterBulan = $this->request->getVar('filter_bulan') ?? date('Y-m');
        if ($filterBulan === 'all') $filterBulan = '';
        $filterAksi = $this->request->getVar('filter_aksi') ?: '';
        $filterUser = $this->request->getVar('filter_user') ?: '';
        $filterMesin = $this->request->getVar('filter_mesin') ?: '';

        if ($filterBulan) {
            $logModel->like('log_aktivitas_mesin.created_at', $filterBulan . '-', 'after');
        }
        if ($filterAksi) {
            $logModel->where('log_aktivitas_mesin.aksi', $filterAksi);
        }
        if ($filterUser) {
            $logModel->where('users.nama', $filterUser);
        }
        if ($filterMesin) {
            $logModel->where('log_aktivitas_mesin.no_mesin', $filterMesin);
        }

        $totalItems = $logModel->countAllResults(false);
        $totalPages = (int) ceil($totalItems / $perPage);
        $logs = $logModel->select('log_aktivitas_mesin.*, users.nama as nama_admin')
                         ->join('users', 'users.id = log_aktivitas_mesin.dilakukan_oleh', 'left')
                         ->orderBy('log_aktivitas_mesin.created_at', 'DESC')
                         ->findAll($perPage, ($page - 1) * $perPage);

        // Fetch options
        $db = \Config\Database::connect();
        
        $aksiResult = $db->table('log_aktivitas_mesin')->select('aksi')->distinct()->get()->getResultArray();
        $availableAksi = array_column($aksiResult, 'aksi');

        $usersResult = $db->table('log_aktivitas_mesin')
                          ->select('users.nama')
                          ->join('users', 'users.id = log_aktivitas_mesin.dilakukan_oleh', 'left')
                          ->distinct()->orderBy('users.nama', 'ASC')->get()->getResultArray();
        $availableUsers = array_column($usersResult, 'nama');

        $mesinsResult = $db->table('log_aktivitas_mesin')->select('no_mesin')->distinct()->orderBy('no_mesin', 'ASC')->get()->getResultArray();
        $availableMesins = array_column($mesinsResult, 'no_mesin');

        // Build bulanList
        $bulanList = [];
        for ($i = -1; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $bulanList[$time->format('Y-m')] = format_bulan_indo($time->format('Y-m'));
        }

        $buildQuery = function ($add = []) {
            $get = $_GET;
            $get = array_merge($get, $add);
            return '?' . http_build_query($get);
        };
        
        $data = [
            'title' => 'Log Riwayat Mesin',
            'logs'  => $logs,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'buildQuery' => $buildQuery,
            'filterBulan' => $filterBulan,
            'filterAksi' => $filterAksi,
            'filterUser' => $filterUser,
            'filterMesin' => $filterMesin,
            'availableAksi' => $availableAksi,
            'availableUsers' => $availableUsers,
            'availableMesins' => $availableMesins,
            'bulanList' => $bulanList
        ];
        
        return view('admin/log_mesin/index', $data);
    }
}
