<?php
$file = 'c:/xampp/htdocs/mtce/app/Config/Routes.php';
$content = file_get_contents($file);
$content = preg_replace('/\r\n|\r/', "\n", $content);

$old = <<<'EOD'
// Laporan Durasi (member, admin)
$routes->get('laporan/durasi', 'LaporanController::durasi', ['filter' => 'role:member,admin']);
$routes->get('laporan/durasi-pdf', 'LaporanController::durasiPdf', ['filter' => 'role:member,admin']);

// Admin - Master Mesin (admin = full CRUD, member/sheadprd/sheadmtc = view-only)
EOD;

$new = <<<'EOD'
// Laporan Durasi (member, admin)
$routes->get('laporan/durasi', 'LaporanController::durasi', ['filter' => 'role:member,admin']);
$routes->get('laporan/durasi-pdf', 'LaporanController::durasiPdf', ['filter' => 'role:member,admin']);

// Approval Inbox (semua role kecuali magang)
$routes->get('approval', 'ApprovalController::index', ['filter' => 'role:admin,member,leader,sheadprd,sheadmtc']);

// Admin - Master Mesin (admin = full CRUD, member/sheadprd/sheadmtc = view-only)
EOD;

if (strpos($content, $old) !== false) {
    $content = str_replace($old, $new, $content);
    file_put_contents($file, $content);
    echo "Route approval berhasil dikembalikan.\n";
} else {
    echo "Gagal mengembalikan route approval.\n";
}
