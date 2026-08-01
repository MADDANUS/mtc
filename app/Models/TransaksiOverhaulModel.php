<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiOverhaulModel extends Model
{
    protected $table         = 'transaksi_overhaul';
    protected $primaryKey    = 'id_transaksi'; // Assume id_transaksi is the primary key or unique
    protected $allowedFields = [
        'id_transaksi',
        'bar_feeder_type',
        'support_pic',
        'note_recommendation'
    ];
}
