<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/dashboard/staff.php';
$content = file_get_contents($file);
$content = str_replace(
    '<a href="<?= site_url(\'riwayat\') ?>" class="btn btn-sm btn-outline-secondary fw-bold rounded-pill px-3">Lihat Semua Riwayat</a>',
    '<a href="<?= site_url(\'riwayat/lokasi/semua?jenis_check=Preventive\') ?>" class="btn btn-sm btn-outline-secondary fw-bold rounded-pill px-3">Lihat Semua Riwayat</a>',
    $content
);
file_put_contents($file, $content);
echo "Fixed link\n";
