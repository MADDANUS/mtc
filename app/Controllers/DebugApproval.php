<?php
namespace App\Controllers;
use CodeIgniter\Controller;

class DebugApproval extends Controller
{
    public function index()
    {
        $service = new \App\Services\ApprovalService();
        session()->set('role', \App\Enums\Role::Admin->value);
        $data = $service->index($this->request);

        return $this->response->setJSON([
            'docs' => $data['docs']
        ]);
    }
}

