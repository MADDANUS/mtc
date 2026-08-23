<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalPreventiveModel extends Model
{
    protected $table         = 'jadwal_preventive';
    protected $primaryKey    = 'id_jadwal';
    protected $allowedFields = [
        'plant',
        'departemen',
        'kategori',
        'bulan_tahun',
        'periode_ke',
        'tanggal_rencana'
    ];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    public function getJadwalForChecklist(string $departemenName, string $kategoriName, string $bulanTahun): ?array
    {
        return $this->where('departemen', $departemenName)
                    ->where('kategori', $kategoriName)
                    ->where('bulan_tahun', $bulanTahun)
                    ->first();
    }
}
