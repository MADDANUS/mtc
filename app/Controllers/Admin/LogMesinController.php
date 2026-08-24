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
        
        $totalItems = $logModel->countAllResults(false);
        $totalPages = (int) ceil($totalItems / $perPage);
        $logs = $logModel->select('log_aktivitas_mesin.*, users.nama as nama_admin')
                         ->join('users', 'users.id = log_aktivitas_mesin.dilakukan_oleh', 'left')
                         ->orderBy('log_aktivitas_mesin.created_at', 'DESC')
                         ->findAll($perPage, ($page - 1) * $perPage);

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
            'buildQuery' => $buildQuery
        ];
        
        return view('admin/log_mesin/index', $data);
    }
}
