<?php
$file = 'c:/xampp/htdocs/mtce/app/Config/Routes.php';
$content = file_get_contents($file);
$content = preg_replace('/\r\n|\r/', "\n", $content);

$oldRoute = <<<'EOD'
    // Upload Foto Perbaikan (semua yang bisa akses abnormal)
    $routes->post('upload-foto-perbaikan', 'AbnormalController::uploadFotoPerbaikan');
});
EOD;

$newRoute = <<<'EOD'
    // Upload Foto Perbaikan (semua yang bisa akses abnormal)
    $routes->post('upload-foto-perbaikan', 'AbnormalController::uploadFotoPerbaikan');
    $routes->post('delete-foto-perbaikan', 'AbnormalController::deleteFotoPerbaikan');
});
EOD;

if (strpos($content, $oldRoute) !== false) {
    $content = str_replace($oldRoute, $newRoute, $content);
    file_put_contents($file, $content);
    echo "Route delete-foto-perbaikan berhasil ditambahkan.\n";
} else {
    echo "Gagal menemukan blok rute yang akan diganti.\n";
}
