<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LogHapusMesinModel;

class LogMesinController extends BaseController
{
    public function index()
    {
        $logModel = new LogHapusMesinModel();
        
        $data = [
            'title' => 'Log Hapus Mesin',
            'logs'  => $logModel->orderBy('id_log', 'DESC')->findAll(),
        ];
        
        return view('admin/log_mesin/index', $data);
    }
}
