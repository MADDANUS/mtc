<?php

namespace App\Models;

use CodeIgniter\Model;

class LogMasterMesinModel extends Model
{
    protected $table            = 'log_master_mesin';
    protected $primaryKey       = 'id_log';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_mesin',
        'kolom_diubah',
        'nilai_lama',
        'nilai_baru',
        'diubah_oleh',
        'created_at'
    ];

    protected $useTimestamps = false; 

    /**
     * Membandingkan data lama dan baru, lalu mencatat perubahan.
     */
    public function logChanges(int $idMesin, array $oldData, array $newData, ?int $idAdmin)
    {
        $fieldsToCheck = ['no_mesin', 'type_mesin', 'serial_nomor', 'bar_feeder_type', 'lokasi', 'line', 'jenis'];

        foreach ($fieldsToCheck as $field) {
            $oldValue = $oldData[$field] ?? null;
            $newValue = $newData[$field] ?? null;

            $oldValueStr = (string)$oldValue;
            $newValueStr = (string)$newValue;

            if ($oldValueStr !== $newValueStr) {
                $this->insert([
                    'id_mesin'     => $idMesin,
                    'kolom_diubah' => $field,
                    'nilai_lama'   => $oldValue,
                    'nilai_baru'   => $newValue,
                    'diubah_oleh'  => $idAdmin,
                ]);
            }
        }
    }

    public function getRiwayatByMesin(int $idMesin)
    {
        return $this->select('log_master_mesin.*, users.nama as nama_admin')
                    ->join('users', 'users.id = log_master_mesin.diubah_oleh', 'left')
                    ->where('log_master_mesin.id_mesin', $idMesin)
                    ->orderBy('log_master_mesin.created_at', 'DESC')
                    ->findAll();
    }
}
