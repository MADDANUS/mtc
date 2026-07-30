<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/KontrolController.php';
$content = file_get_contents($file);

$func = <<<'EOD'
    /**
     * POST /kontrol/delete-approval
     * Menghapus record approval (Khusus Admin)
     */
    public function deleteApprovalBulanan()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $lokasi   = $this->request->getPost('lokasi');
        $line     = $this->request->getPost('line');
        $kategori = $this->request->getPost('kategori');
        $bulan    = $this->request->getPost('bulan_tahun');

        $db = \Config\Database::connect();
        $db->table('approval_bulanan')
           ->where('type', 'kontrol')
           ->where('lokasi', $lokasi)
           ->where('line', $line)
           ->where('kategori', $kategori)
           ->where('bulan_tahun', $bulan)
           ->delete();

        return redirect()->back()->with('success', 'Data approval Checklist Control berhasil dihapus (Reset ke Belum Selesai).');
    }
}
EOD;

$content = preg_replace('/\}\s*$/', "\n" . $func . "\n", $content);
file_put_contents($file, $content);
echo "Added deleteApprovalBulanan.\n";
