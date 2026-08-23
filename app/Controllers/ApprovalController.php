<?php

namespace App\Controllers;

use App\Services\ApprovalService;

class ApprovalController extends BaseController
{
    public function index()
    {
        $service = new ApprovalService();
        $data = $service->index($this->request);
        if ($data instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $data;
        }
        return view('approval/index', $data);
    }
}
