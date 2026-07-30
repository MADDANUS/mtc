<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/ChecklistController.php';
$content = file_get_contents($file);

$oldCode = <<<'EOD'
        return redirect()->to("/riwayat/lokasi/{$lokasiSlug}?jenis_check=" . urlencode($jenisName) . "&kategori=" . urlencode($kategoriName))
                          ->with('success', 'Pengecekan berhasil disimpan. Durasi pengerjaan: '
                              . $this->formatDurasi($waktuMulai, $waktuSelesai));
EOD;

$newCode = <<<'EOD'
        $roleSession = session()->get('role');
        if ($roleSession === 'magang') {
            $redirectUrl = "/riwayat/lokasi/{$lokasiSlug}?jenis_check=" . urlencode($jenisName) . "&kategori=" . urlencode($kategoriName);
        } else {
            $redirectUrl = "/approval";
        }

        return redirect()->to($redirectUrl)
                          ->with('success', 'Pengecekan berhasil disimpan. Durasi pengerjaan: '
                              . $this->formatDurasi($waktuMulai, $waktuSelesai));
EOD;

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Updated redirect in ChecklistController.\n";
