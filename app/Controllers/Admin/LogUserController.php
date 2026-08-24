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
        
        $totalItems = $logModel->countAllResults(false);
        $totalPages = (int) ceil($totalItems / $perPage);
        $logs = $logModel->select('log_aktivitas_user.*, users.nama as nama_admin')
                         ->join('users', 'users.id = log_aktivitas_user.dilakukan_oleh', 'left')
                         ->orderBy('log_aktivitas_user.created_at', 'DESC')
                         ->findAll($perPage, ($page - 1) * $perPage);

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
            'buildQuery' => $buildQuery
        ];
        
        return view('admin/log_user/index', $data);
    }
}
