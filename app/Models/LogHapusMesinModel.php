<?php

namespace App\Models;

use CodeIgniter\Model;

class LogHapusMesinModel extends Model
{
    protected $table         = 'log_hapus_mesin';
    protected $primaryKey    = 'id_log';
    protected $allowedFields = [
        'id_mesin', 'no_mesin', 'type_mesin', 'jenis', 'serial_nomor', 
        'plant', 'bar_feeder_type', 'departemen', 'line', 
        'waktu_dihapus', 'dihapus_oleh', 'alasan_dihapus'
    ];
}
