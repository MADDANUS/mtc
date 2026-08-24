<?php

namespace App\Models;

use CodeIgniter\Model;

class PeriodeOverhaulModel extends Model
{
    protected $table      = 'periode_overhaul';
    protected $primaryKey = 'id';
    protected $allowedFields = ['plant', 'tanggal_mulai', 'tanggal_selesai', 'status', 'diakhiri_oleh', 'created_at'];
    protected $useTimestamps = false;

    /**
     * Ambil siklus Aktif untuk plant tertentu.
     */
    public function getAktif(string $plant): ?array
    {
        return $this->where('plant', $plant)
                    ->where('status', 'Aktif')
                    ->orderBy('tanggal_mulai', 'ASC')
                    ->first();
    }

    /**
     * Akhiri siklus aktif (tanpa membuat yang baru otomatis).
     */
    public function akhiriPeriode(string $plant, int $userId): bool
    {
        $aktif = $this->getAktif($plant);
        if (!$aktif) {
            return false;
        }

        // Tutup siklus lama
        $this->update($aktif['id'], [
            'status'          => 'Selesai',
            'tanggal_selesai' => date('Y-m-d'),
            'diakhiri_oleh'   => $userId,
        ]);

        return true;
    }

    /**
     * Mulai siklus baru.
     */
    public function awaliPeriode(string $plant): bool
    {
        // Pastikan tidak ada yang aktif
        if ($this->getAktif($plant)) {
            return false;
        }

        // Buat siklus baru
        $this->insert([
            'plant'           => $plant,
            'tanggal_mulai'   => date('Y-m-d'),
            'tanggal_selesai' => null,
            'status'          => 'Aktif',
            'diakhiri_oleh'   => null,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        return true;
    }
}