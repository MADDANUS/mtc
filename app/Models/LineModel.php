<?php

namespace App\Models;

use CodeIgniter\Model;

class LineModel extends Model
{
    protected $table      = 'master_line';
    protected $primaryKey = 'id_line';
    protected $returnType = 'array';
    protected $allowedFields = ['nama_line', 'lokasi'];

    /**
     * Mengembalikan array lines dikelompokkan per lokasi.
     * Contoh: ['MFG 1' => ['Line 1', 'Line 2'], 'MFG 2' => ['CG', 'Second']]
     */
    public function getLinesGroupedByLokasi(): array
    {
        $rows = $this->orderBy('lokasi')->orderBy('nama_line')->findAll();
        $grouped = [];
        foreach ($rows as $row) {
            $lokasi = $row['lokasi'];
            // Normalisasi huruf besar/kecil agar selalu cocok dengan key JavaScript di frontend
            if (strcasecmp($lokasi, 'mfg 1') === 0) $lokasi = 'MFG 1';
            if (strcasecmp($lokasi, 'mfg 2') === 0) $lokasi = 'MFG 2';
            if (strcasecmp($lokasi, 'plan 2') === 0) $lokasi = 'Plan 2';
            
            $grouped[$lokasi][] = $row['nama_line'];
        }
        return $grouped;
    }

    /**
     * Mengembalikan semua nama line (flat, unik) sebagai array string.
     */
    public function getAllLineNames(): array
    {
        $rows = $this->orderBy('lokasi')->orderBy('nama_line')->findAll();
        return array_column($rows, 'nama_line');
    }
}
