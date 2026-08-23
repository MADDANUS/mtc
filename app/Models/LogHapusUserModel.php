<?php

namespace App\Models;

use CodeIgniter\Model;

class LogHapusUserModel extends Model
{
    protected $table         = 'log_hapus_user';
    protected $primaryKey    = 'id_log';
    protected $allowedFields = [
        'id_user', 'nama', 'username', 'role', 'plant', 'departemen', 'line',
        'waktu_dihapus', 'dihapus_oleh', 'alasan_dihapus'
    ];
}
