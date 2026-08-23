<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LogHapusUserModel;

class LogUserController extends BaseController
{
    public function index()
    {
        $logModel = new LogHapusUserModel();
        
        $data = [
            'title' => 'Log Hapus User',
            'logs'  => $logModel->orderBy('id_log', 'DESC')->findAll(),
        ];
        
        return view('admin/log_user/index', $data);
    }
}
