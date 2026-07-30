<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/Admin/JadwalController.php';
$content = file_get_contents($file);

$oldCode = <<<'EOD'
        if (!$schedule) {
            return redirect()->back()->with('error', 'Jadwal tidak ditemukan.');
        }

        $this->jadwalModel->delete($id);
EOD;

$newCode = <<<'EOD'
        if (!$schedule) {
            return redirect()->back()->with('error', 'Jadwal tidak ditemukan.');
        }

        // Cek apakah sudah ada checklist (transaksi_check) yang dibuat untuk jadwal ini
        $transaksiModel = new \App\Models\TransaksiCheckModel();
        $bulanTahun = $schedule['bulan_tahun']; // e.g., '2026-07'
        
        $cekTransaksi = $transaksiModel->where('jenis_check', 'Preventive')
                                       ->where('lokasi_check', $schedule['lokasi'])
                                       ->where('kategori', $schedule['kategori'])
                                       ->like('created_at', $bulanTahun . '-', 'after')
                                       ->first();

        if ($cekTransaksi) {
            return redirect()->back()->with('error', 'Gagal dihapus! Sudah ada mesin yang diisi pengecekannya (checklist) pada jadwal kategori ini.');
        }

        $this->jadwalModel->delete($id);
EOD;

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Validation added to delete function.\n";
