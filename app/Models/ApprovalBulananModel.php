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

    public function getApprovalWithUsers(string $lokasi, string $kategori, string $bulan, ?string $line): ?array
    {
        $builder = $this->select('approval_bulanan.*, u1.nama as l1_name, u2.nama as l2_name, u3.nama as final_name')
                        ->join('users u1', 'u1.id = approval_bulanan.approved_l1_by', 'left')
                        ->join('users u2', 'u2.id = approval_bulanan.approved_l2_by', 'left')
                        ->join('users u3', 'u3.id = approval_bulanan.approved_final_by', 'left')
                        ->where('approval_bulanan.type', 'kontrol')
                        ->where('approval_bulanan.lokasi', $lokasi)
                        ->where('approval_bulanan.kategori', $kategori)
                        ->where('approval_bulanan.bulan_tahun', $bulan);
        if ($line) {
            $builder->where('approval_bulanan.line', $line);
        }
        return $builder->first();
    }

    public function getAllApprovals(string $bulanTahun): array
    {
        return $this->where('type', 'kontrol')
                    ->where('bulan_tahun', $bulanTahun)
                    ->findAll();
    }

    public function getApprovalKontrol(string $lokasi, string $line, string $kategori, string $bulanTahun): ?array
    {
        return $this->where('type', 'kontrol')
                    ->where('lokasi', $lokasi)
                    ->where('line', $line)
                    ->where('kategori', $kategori)
                    ->where('bulan_tahun', $bulanTahun)
                    ->first();
    }

    public function deleteApprovalKontrol(string $lokasi, string $line, string $kategori, string $bulanTahun): bool
    {
        return $this->where('type', 'kontrol')
                    ->where('lokasi', $lokasi)
                    ->where('line', $line)
                    ->where('kategori', $kategori)
                    ->where('bulan_tahun', $bulanTahun)
                    ->delete();
    }

    public function getInboxApprovalKontrol(string $role): array
    {
        $builder = $this->select('approval_bulanan.id_approval AS doc_id, approval_bulanan.type AS jenis_check, approval_bulanan.kategori, approval_bulanan.lokasi, approval_bulanan.line, approval_bulanan.bulan_tahun AS doc_date, approval_bulanan.status, "kontrol" AS doc_source, NULL AS lokasi_check, NULL AS nama_pic, NULL AS nama_staff, NULL AS no_mesin, NULL AS type_mesin, NULL AS persen', false);

        if ($role === \App\Enums\Role::Sheadprd->value) {
            $builder->where('approval_bulanan.status', 'Approved L1');
        } elseif ($role === \App\Enums\Role::Sheadmtc->value) {
            $builder->where('approval_bulanan.status', 'Approved L2');
        } elseif (in_array($role, [\App\Enums\Role::Member->value, \App\Enums\Role::Admin->value])) {
            $builder->whereNotIn('approval_bulanan.status', ['Final', 'Approved Final']);
        }

        return $builder->orderBy('approval_bulanan.bulan_tahun', 'DESC')->findAll();
    }

    public function getExistingApprovals(string $bulan): array
    {
        return $this->select('lokasi, line, kategori')
                    ->where('type', 'kontrol')
                    ->where('bulan_tahun', $bulan)
                    ->findAll();
    }
}
