<?php

namespace App\Models;

use CodeIgniter\Model;

class ApprovalBulananModel extends Model
{
    protected $table         = 'approval_bulanan';
    protected $primaryKey    = 'id_approval';
    protected $allowedFields = [
        'bulan_tahun',
        'type',
        'kategori',
        'lokasi',
        'line',
        'status',
        'approved_by',
        'approval_l1_by',
        'approval_l2_by',
        'approval_final_by'
    ];

    public function getPendingKontrolByRole(string $role): array
    {
        $builder = $this->where('type', 'kontrol');
        
        if ($role === \App\Enums\Role::Sheadprd->value) {
            $builder->where('status', 'Approved L1');
        } elseif ($role === \App\Enums\Role::Sheadmtc->value) {
            $builder->where('status', 'Approved L2');
        } elseif ($role === \App\Enums\Role::Member->value) {
            $builder->where('status', 'Pending');
        } else {
            $builder->where('1=0');
        }
        return $builder->orderBy('updated_at', 'DESC')->findAll();
    }
}
