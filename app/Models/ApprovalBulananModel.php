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
        'plant',
        'departemen',
        'line',
        'status',
        'approved_l1_by',
        'approved_l1_at',
        'approved_l2_by',
        'approved_l2_at',
        'approved_final_by',
        'approved_final_at',
        'created_at',
        'updated_at'
    ];

    public function getPendingKontrolByRole(): array
    {
        // Panggil getInboxApprovalKontrol karena logika filter Plant/Dept/Line-nya sudah terpusat di sana
        return $this->getInboxApprovalKontrol();
    }

    public function getApprovalWithUsers(string $departemen, string $kategori, string $bulan, ?string $line, ?string $plant = null): ?array
    {
        $builder = $this->select('approval_bulanan.*, u1.nama as l1_name, u2.nama as l2_name, u3.nama as final_name')
                        ->join('users u1', 'u1.id = approval_bulanan.approved_l1_by', 'left')
                        ->join('users u2', 'u2.id = approval_bulanan.approved_l2_by', 'left')
                        ->join('users u3', 'u3.id = approval_bulanan.approved_final_by', 'left')
                        ->where('approval_bulanan.type', 'kontrol')
                        ->where('approval_bulanan.departemen', $departemen)
                        ->where('approval_bulanan.kategori', $kategori)
                        ->where('approval_bulanan.bulan_tahun', $bulan);
        if ($line) {
            $builder->where('approval_bulanan.line', $line);
        }
        if ($plant) {
            $builder->where('approval_bulanan.plantt', $plant);
        }
        return $builder->first();
    }

    public function getAllApprovals(string $bulanTahun): array
    {
        return $this->where('type', 'kontrol')
                    ->where('bulan_tahun', $bulanTahun)
                    ->findAll();
    }

    public function getApprovalKontrol(string $departemen, string $line, string $kategori, string $bulanTahun, ?string $plant = null): ?array
    {
        $builder = $this->where('type', 'kontrol')
                        ->where('departemen', $departemen)
                        ->where('line', $line)
                        ->where('kategori', $kategori)
                        ->where('bulan_tahun', $bulanTahun);
        if ($plant) {
            $builder->where('plant', $plant);
        }
        return $builder->first();
    }

    public function deleteApprovalKontrol(string $departemen, string $line, string $kategori, string $bulanTahun, ?string $plant = null): bool
    {
        $builder = $this->where('type', 'kontrol')
                        ->where('departemen', $departemen)
                        ->where('line', $line)
                        ->where('kategori', $kategori)
                        ->where('bulan_tahun', $bulanTahun);
        if ($plant) {
            $builder->where('plant', $plant);
        }
        return $builder->delete();
    }

    public function getInboxApprovalKontrol(): array
    {
        $builder = $this->select('approval_bulanan.id_approval AS doc_id, approval_bulanan.type AS jenis_check, approval_bulanan.kategori, approval_bulanan.plant, approval_bulanan.departemen, approval_bulanan.line, approval_bulanan.bulan_tahun AS doc_date, approval_bulanan.status, "kontrol" AS doc_source, NULL AS departemen_check, NULL AS nama_pic, NULL AS nama_staff, NULL AS no_mesin, NULL AS type_mesin, NULL AS persen', false);

        if (has_any_role([\App\Enums\Role::Member->value, \App\Enums\Role::LeaderMember->value, \App\Enums\Role::Admin->value])) {
            $builder->whereNotIn('approval_bulanan.status', ['Final', 'Approved Final']);
        } else {
            $addedConditions = false;
            $builder->groupStart();
            
            if (has_role(\App\Enums\Role::Sheadprd->value)) {
                $builder->orGroupStart()
                            ->where('approval_bulanan.status', 'Approved L1');
                
                $userPlans = session()->get('plant');
                if ($userPlans) {
                    $plansArray = array_map('trim', explode(',', $userPlans));
                    $builder->whereIn('approval_bulanan.plant', $plansArray);
                }
                
                $userDepts = session()->get('departemen');
                if ($userDepts) {
                    $deptsArray = array_map('trim', explode(',', $userDepts));
                    $builder->whereIn('approval_bulanan.departemen', $deptsArray);
                }
                
                if (($userLines = session()->get('line')) && $userLines !== '-') {
                    $linesArray = array_map('trim', explode(',', $userLines));
                    $builder->whereIn('approval_bulanan.line', $linesArray);
                }
                $builder->groupEnd();
                $addedConditions = true;
            }
            
            if (has_role(\App\Enums\Role::Sheadmtc->value)) {
                $builder->orGroupStart()
                            ->where('approval_bulanan.status', 'Approved L2');
                
                $userPlans = session()->get('plant');
                if ($userPlans) {
                    $plansArray = array_map('trim', explode(',', $userPlans));
                    $builder->whereIn('approval_bulanan.plant', $plansArray);
                }
                
                $builder->groupEnd();
                $addedConditions = true;
            }
            
            if (!$addedConditions) {
                return [];
            }
            $builder->groupEnd();
        }

        return $builder->orderBy('approval_bulanan.bulan_tahun', 'DESC')->findAll();
    }

    public function getExistingApprovals(?string $bulan = null): array
    {
        $builder = $this->select('bulan_tahun, plant, departemen, line, kategori, status')
                    ->where('type', 'kontrol');
        
        if ($bulan) {
            $builder->where('bulan_tahun', $bulan);
        }
        
        return $builder->findAll();
    }
}
