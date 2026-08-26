<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LogAktivitasUserModel;

class LogUserController extends BaseController
{
    public function index()
    {
        $logModel = new LogAktivitasUserModel();
        
        $page = (int) $this->request->getVar('page') ?: 1;
        $perPage = (int) $this->request->getVar('per_page') ?: 15;
        
        $filterBulan = $this->request->getVar('filter_bulan') ?? date('Y-m');
        if ($filterBulan === 'all') $filterBulan = '';
        $filterAksi = $this->request->getVar('filter_aksi') ?: '';
        $filterUser = $this->request->getVar('filter_user') ?: '';
        $filterTargetUser = $this->request->getVar('filter_target_user') ?: '';

        if ($filterBulan) {
            $logModel->like('log_aktivitas_user.created_at', $filterBulan . '-', 'after');
        }
        if ($filterAksi) {
            $logModel->where('log_aktivitas_user.aksi', $filterAksi);
        }
        if ($filterUser) {
            $logModel->where('users.nama', $filterUser);
        }
        if ($filterTargetUser) {
            $logModel->where('log_aktivitas_user.nama_user', $filterTargetUser);
        }

        $totalItems = $logModel->countAllResults(false);
        $totalPages = (int) ceil($totalItems / $perPage);
        $logs = $logModel->select('log_aktivitas_user.*, users.nama as nama_admin')
                         ->join('users', 'users.id = log_aktivitas_user.dilakukan_oleh', 'left')
                         ->orderBy('log_aktivitas_user.created_at', 'DESC')
                         ->findAll($perPage, ($page - 1) * $perPage);

        // Fetch options
        $db = \Config\Database::connect();
        
        $aksiResult = $db->table('log_aktivitas_user')->select('aksi')->distinct()->get()->getResultArray();
        $availableAksi = array_column($aksiResult, 'aksi');

        $usersResult = $db->table('log_aktivitas_user')
                          ->select('users.nama')
                          ->join('users', 'users.id = log_aktivitas_user.dilakukan_oleh', 'left')
                          ->distinct()->orderBy('users.nama', 'ASC')->get()->getResultArray();
        $availableUsers = array_column($usersResult, 'nama');

        $targetUsersResult = $db->table('log_aktivitas_user')->select('nama_user')->distinct()->orderBy('nama_user', 'ASC')->get()->getResultArray();
        $availableTargetUsers = array_column($targetUsersResult, 'nama_user');

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
            'title' => 'Log Riwayat User',
            'logs'  => $logs,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'buildQuery' => $buildQuery,
            'filterBulan' => $filterBulan,
            'filterAksi' => $filterAksi,
            'filterUser' => $filterUser,
            'filterTargetUser' => $filterTargetUser,
            'availableAksi' => $availableAksi,
            'availableUsers' => $availableUsers,
            'availableTargetUsers' => $availableTargetUsers,
            'bulanList' => $bulanList
        ];
        
        return view('admin/log_user/index', $data);
    }
}
