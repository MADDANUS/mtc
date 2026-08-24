<?php

namespace App\Models;

use CodeIgniter\Model;

class MesinModel extends Model
{
    protected $table         = 'master_mesin';
    protected $primaryKey    = 'id_mesin';
    protected $allowedFields = ['no_mesin', 'type_mesin', 'serial_nomor', 'plant', 'departemen', 'line', 'bar_feeder_type', 'sn_barfeeder', 'jenis'];
    protected $useTimestamps = true;
    protected $returnType    = 'array';

    public function getMesinByLine(?string $departemen, ?string $line, ?string $jenis)
    {
        $builder = $this->select('*');
        
        if ($departemen) {
            $depts = array_map('trim', explode(',', $departemen));
            $this->whereIn('departemen', $depts);
        }
        
        if (!empty($line) && $line !== 'all') {
            $this->where('line', $line);
        }
        
        if (!empty($jenis) && $jenis !== 'all') {
            $this->where('jenis', $jenis);
        }
        
        return $this->findAll();
    }
    
    public function getDepartemenByLine(string $line)
    {
        return $this->select('departemen')->where('line', $line)->first();
    }

    public function getTotalMesinPerLine(): array
    {
        return $this->select('departemen, line, COUNT(id_mesin) as total')
                    ->groupBy('departemen, line')
                    ->findAll();
    }

    public function getByDepartemen(?string $departemen = null): array
    {
        if ($departemen) {
            $depts = array_map('trim', explode(',', $departemen));
            $this->whereIn('departemen', $depts);
        }
        return $this->orderBy('no_mesin', 'ASC')->findAll();
    }

    public function hasCamMachine(?string $departemen, ?string $line = null): bool
    {
        $builder = $this->select('id_mesin')
                        ->groupStart()
                            ->where('jenis', 'CAM')
                            ->orLike('jenis', 'CAM', 'both')
                            ->orLike('type_mesin', 'CAM', 'both')
                        ->groupEnd();
        
        if ($departemen) {
            $depts = array_map('trim', explode(',', $departemen));
            $builder->whereIn('departemen', $depts);
        }
        if ($line) {
            $builder->where('line', $line);
        }
        
        return $builder->countAllResults() > 0;
    }
}
