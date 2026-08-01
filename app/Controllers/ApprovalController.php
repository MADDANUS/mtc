<?php

namespace App\Controllers;

use App\Services\ApprovalService;

class ApprovalController extends BaseController
{
    public function index()
    {
        $service = new ApprovalService();
        $data = $service->index($this->request);
        return view('approval/index', $data);
    }
}
