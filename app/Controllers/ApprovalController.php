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
        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Cache-Control', 'post-check=0, pre-check=0', false)
            ->setHeader('Pragma', 'no-cache')
            ->setBody(view('approval/index', $data));
    }
}
