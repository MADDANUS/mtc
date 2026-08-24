<?php

namespace App\Models;

use CodeIgniter\Model;

class LogAktivitasUserModel extends Model
{
    protected $table            = 'log_aktivitas_user';
    protected $primaryKey       = 'id_log';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_user_target',
        'nama_user',
        'aksi',
        'keterangan',
        'detail',
        'dilakukan_oleh',
        'created_at'
    ];

    protected $useTimestamps = false; 

    public function getRiwayat()
    {
        return $this->select('log_aktivitas_user.*, users.nama as nama_admin')
                    ->join('users', 'users.id = log_aktivitas_user.dilakukan_oleh', 'left')
                    ->orderBy('log_aktivitas_user.created_at', 'DESC')
                    ->findAll();
    }
}
