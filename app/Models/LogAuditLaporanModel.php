<?php

namespace App\Models;

use CodeIgniter\Model;

class LogAuditLaporanModel extends Model
{
    protected $table         = 'log_audit_laporan';
    protected $primaryKey    = 'id_log';
    protected $allowedFields = [
        'kategori_dokumen', 'aksi', 'no_mesin', 
        'waktu_eksekusi', 'dieksekusi_oleh', 'alasan', 'detail_perubahan'
    ];
}
