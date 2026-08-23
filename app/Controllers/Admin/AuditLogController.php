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
        
        $data = [
            'title' => 'Log Riwayat Dokumen',
            'logs' => $model->orderBy('waktu_eksekusi', 'DESC')->findAll()
        ];

        return view('admin/audit_log/index', $data);
    }
}
