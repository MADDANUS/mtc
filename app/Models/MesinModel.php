<?php

namespace App\Models;

use CodeIgniter\Model;

class MesinModel extends Model
{
    protected $table         = 'master_mesin';
    protected $primaryKey    = 'id_mesin';
    protected $allowedFields = ['no_mesin', 'type_mesin', 'serial_nomor', 'lokasi', 'line', 'bar_feeder_type', 'jenis'];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    /**
     * Dropdown mesin untuk lokasi tertentu (dipakai di form MFG 1 Preventive).
     */
    public function getByLokasi(?string $lokasi = null): array
    {
        if ($lokasi) {
            $this->where('lokasi', $lokasi);
        }
        return $this->orderBy('no_mesin', 'ASC')->findAll();
    }

    public function getLokasiByLine(string $line): ?array
    {
        return $this->select('lokasi')->where('line', $line)->first();
    }

    public function getTotalMesinPerLine(): array
    {
        return $this->select('lokasi, line, COUNT(id_mesin) as total')
                    ->groupBy('lokasi, line')
                    ->findAll();
    }
}
