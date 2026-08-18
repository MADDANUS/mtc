<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CleanupCeklis extends BaseCommand {
    protected $group       = 'Maintenance';
    protected $name        = 'db:cleanup_ceklis';
    protected $description = 'Clean up orphaned ceklis_kontrol records';

    public function run(array $params) {
        $db = \Config\Database::connect();
        $ceklisKontrol = $db->table('ceklis_kontrol')->get()->getResultArray();
        $deleted = 0;

        foreach ($ceklisKontrol as $ck) {
            $tx = $db->table('transaksi_check')
                     ->where('id_mesin', $ck['id_mesin'])
                     ->where('kategori', $ck['kategori'])
                     ->where('status', 'Approved')
                     ->where("DATE(COALESCE(waktu_selesai, created_at)) = ", $ck['tanggal_check'])
                     ->get()
                     ->getRowArray();

            if (!$tx) {
                $db->table('ceklis_kontrol')->where('id_kontrol', $ck['id_kontrol'])->delete();
                CLI::write("Deleted orphaned ceklis_kontrol ID: " . $ck['id_kontrol'] . " (Mesin: " . $ck['id_mesin'] . " PIC: " . $ck['pic_nama'] . ")");
                $deleted++;
            }
        }

        CLI::write("Total deleted: $deleted");
    }
}
