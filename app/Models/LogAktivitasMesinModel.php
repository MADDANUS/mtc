<?php

namespace App\Models;

use CodeIgniter\Model;

class LogAktivitasMesinModel extends Model
{
    protected $table            = 'log_aktivitas_mesin';
    protected $primaryKey       = 'id_log';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_mesin',
        'no_mesin',
        'aksi',
        'keterangan',
        'detail',
        'dilakukan_oleh',
        'created_at'
    ];

    protected $useTimestamps = false; 

    public function getRiwayat()
    {
        return $this->select('log_aktivitas_mesin.*, users.nama as nama_admin')
                    ->join('users', 'users.id = log_aktivitas_mesin.dilakukan_oleh', 'left')
                    ->orderBy('log_aktivitas_mesin.created_at', 'DESC')
                    ->findAll();
    }
}
