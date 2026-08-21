<?php

namespace App\Models;

use CodeIgniter\Model;

class LineModel extends Model
{
    protected $table      = 'master_line';
    protected $primaryKey = 'id_line';
    protected $returnType = 'array';
    protected $allowedFields = ['plan', 'nama_line', 'departemen'];

    /**
     * Mengembalikan array lines dikelompokkan per departemen.
     * Contoh: ['MFG 1' => ['Line 1', 'Line 2'], 'MFG 2' => ['CG', 'Second']]
     */
    public function getLinesGroupedByDepartemen(): array
    {
        $rows = $this->orderBy('plan')->orderBy('departemen')->orderBy('nama_line')->findAll();
        $grouped = [];
        foreach ($rows as $row) {
            $plan = $row['plan'] ?? 'Plan 1';
            $departemen = $row['departemen'];
            // Normalisasi
            if (strcasecmp($departemen, 'mfg 1') === 0) $departemen = 'MFG 1';
            if (strcasecmp($departemen, 'mfg 2') === 0) $departemen = 'MFG 2';
            
            $grouped[$plan][$departemen][] = $row['nama_line'];
        }
        
        // Ensure uniqueness
        foreach ($grouped as $plan => $departemens) {
            foreach ($departemens as $dept => $lines) {
                $grouped[$plan][$dept] = array_values(array_unique($lines));
            }
        }
        
        return $grouped;
    }

    /**
     * Mengembalikan semua nama line (flat, unik) sebagai array string.
     */
    public function getAllLineNames(): array
    {
        $rows = $this->orderBy('departemen')->orderBy('nama_line')->findAll();
        return array_column($rows, 'nama_line');
    }
}
