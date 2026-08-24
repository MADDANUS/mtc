<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LogAuditLaporanModel;

class AuditLogController extends BaseController
{
    public function index()
    {
        if (!has_role(\App\Enums\Role::Admin->value)) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $model = new LogAuditLaporanModel();
        
        $paramModel = new \App\Models\ParameterCheckModel();
        $params = $paramModel->findAll();
        $paramMap = [];
        foreach ($params as $p) {
            $paramMap[$p['id_parameter']] = $p; // Full object
        }

        $mesinModel = new \App\Models\MesinModel();
        $mesins = $mesinModel->findAll();
        $mesinMap = [];
        foreach ($mesins as $m) {
            $mesinMap[$m['id_mesin']] = $m;
        }
        
        $page = (int) $this->request->getVar('page') ?: 1;
        $perPage = (int) $this->request->getVar('per_page') ?: 15;
        
        $totalItems = $model->countAllResults(false);
        $totalPages = (int) ceil($totalItems / $perPage);
        $logs = $model->orderBy('waktu_eksekusi', 'DESC')->findAll($perPage, ($page - 1) * $perPage);

        $buildQuery = function ($add = []) {
            $get = $_GET;
            $get = array_merge($get, $add);
            return '?' . http_build_query($get);
        };
        
        $data = [
            'title' => 'Log Riwayat Dokumen',
            'logs' => $logs,
            'paramMap' => $paramMap,
            'mesinMap' => $mesinMap,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'buildQuery' => $buildQuery
        ];

        return view('admin/audit_log/index', $data);
    }
}
