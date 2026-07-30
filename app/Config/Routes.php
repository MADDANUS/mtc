<?php
/**
 * Tambahkan baris-baris di bawah ini ke dalam file app/Config/Routes.php
 * milik project CodeIgniter 4 Anda (jangan replace seluruh file).
 */

// Default route ("/") -> redirect otomatis sesuai status login
$routes->get('/', static function () {
    if (session()->get('logged_in')) {
        return redirect()->to('/dashboard');
    }
    return redirect()->to('/login');
});

// Auth
$routes->get('login', 'Auth::loginForm');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('logout', 'Auth::logout');

// Dashboard (semua role login, konten beda per role di dalam controller)
$routes->get('dashboard', 'DashboardController::index', ['filter' => 'auth']);

// Checklist Dinamis (magang, member, admin bisa buat pengecekan)
$routes->group('checklist', ['filter' => 'role:admin,magang,member'], static function ($routes) {
    // 0. API Check Duplicate
    $routes->post('check-duplicate', 'ChecklistController::checkDuplicate');

    // 1. Pilih Lokasi (mfg1 / mfg2)
    $routes->get('/', 'ChecklistController::pilihLokasi');
    
    // 2. Route obsolete (dulu untuk Pilih Jenis), sekarang langsung redirect ke root checklist
    $routes->get('(:segment)', static function() {
        return redirect()->to('/checklist');
    });
    
    // 3. Pilih Kategori untuk tipe Preventive / Overhaul
    $routes->get('(:segment)/(:segment)', 'ChecklistController::indexKategori/$1/$2');
    
    // 4. Form Checklist (Create)
    $routes->get('(:segment)/(:segment)/create/(:segment)', 'ChecklistController::create/$1/$2/$3');
    
    // 5. Simpan Pengecekan
    $routes->post('(:segment)/(:segment)/store', 'ChecklistController::store/$1/$2');
});

// Riwayat & Detail Transaksi (semua role login, scoping data ditangani di controller)
$routes->group('riwayat', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'RiwayatController::index');
    $routes->get('redirect-detail', 'RiwayatController::redirectDetail');
    $routes->get('lokasi/(:segment)', 'RiwayatController::lokasi/$1');
    $routes->get('kategori/(:segment)', 'RiwayatController::kategori/$1');
    $routes->get('download-pdf-all/(:segment)', 'RiwayatController::downloadPdfAll/$1', ['filter' => 'role:member,admin,magang']);
    $routes->get('download-pdf/(:num)', 'RiwayatController::downloadPdf/$1', ['filter' => 'role:member,admin,magang']);
    $routes->get('(:num)', 'RiwayatController::detail/$1');
    $routes->post('approve/(:num)', 'RiwayatController::approve/$1', ['filter' => 'role:member,sheadprd,sheadmtc,admin,leader']);
    
    // Khusus Admin: Edit & Hapus Riwayat
    $routes->get('edit/(:num)', 'RiwayatController::edit/$1', ['filter' => 'role:admin']);
    $routes->post('update/(:num)', 'RiwayatController::update/$1', ['filter' => 'role:admin']);
    $routes->post('delete/(:num)', 'RiwayatController::delete/$1', ['filter' => 'role:admin']);
});

// Scan QR Code (magang, member, admin)
$routes->group('scan', ['filter' => 'role:magang,member,admin'], static function ($routes) {
    $routes->get('/', 'ScanController::index');
    $routes->get('mesin/(:num)', 'ScanController::mesin/$1');
});

// Checklist Control Bulanan (semua role login)
$routes->group('kontrol', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'KontrolController::index');
    $routes->get('pdf', 'KontrolController::pdf', ['filter' => 'role:member,admin']);
    $routes->get('pdf-all-categories', 'KontrolController::pdfAllCategories', ['filter' => 'role:member,admin']);
    $routes->get('pdf-all-summary', 'KontrolController::pdfAllSummary', ['filter' => 'role:member,admin']);
    $routes->post('update-cell', 'KontrolController::updateCell');
    $routes->post('approve', 'KontrolController::approveBulanan');
});

// Laporan Abnormal Condition (semua role login)
$routes->group('abnormal', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'AbnormalController::index');
    $routes->get('pdf', 'AbnormalController::pdf', ['filter' => 'role:member,admin']);
    $routes->get('pdf-all-categories', 'AbnormalController::pdfAllCategories', ['filter' => 'role:member,admin']);
    $routes->get('pdf-all-summary', 'AbnormalController::pdfAllSummary', ['filter' => 'role:member,admin']);
    $routes->post('update', 'AbnormalController::update');
    $routes->post('approve', 'AbnormalController::approveBulanan');
    
    // Abnormal Khusus Overhaul
    $routes->get('overhaul', 'AbnormalController::overhaul');
    $routes->get('overhaul/pdf', 'AbnormalController::pdfOverhaul', ['filter' => 'role:member,admin']);
    $routes->get('overhaul/pdf-all-summary', 'AbnormalController::pdfAllSummaryOverhaul', ['filter' => 'role:member,admin']);
    $routes->post('overhaul/update', 'AbnormalController::updateOverhaul');
    
    // Upload Foto Perbaikan (semua yang bisa akses abnormal)
    $routes->post('upload-foto-perbaikan', 'AbnormalController::uploadFotoPerbaikan');
    $routes->post('delete-foto-perbaikan', 'AbnormalController::deleteFotoPerbaikan');
});

// Laporan Durasi (member, admin)
$routes->get('laporan/durasi', 'LaporanController::durasi', ['filter' => 'role:member,admin']);
$routes->get('laporan/durasi-pdf', 'LaporanController::durasiPdf', ['filter' => 'role:member,admin']);

// Approval Inbox (semua role kecuali magang)
$routes->get('approval', 'ApprovalController::index', ['filter' => 'role:admin,member,leader,sheadprd,sheadmtc']);

// Admin - Master Mesin (admin = full CRUD, member/sheadprd/sheadmtc = view-only)
$routes->group('admin/mesin', ['filter' => 'role:admin,member,sheadprd,sheadmtc,leader', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'MesinController::index');
    $routes->get('create', 'MesinController::create');
    $routes->post('store', 'MesinController::store');
    $routes->get('edit/(:num)', 'MesinController::edit/$1');
    $routes->post('update/(:num)', 'MesinController::update/$1');
    $routes->get('delete/(:num)', 'MesinController::delete/$1');
    $routes->get('export', 'MesinController::export');
    $routes->get('template', 'MesinController::template');
    $routes->post('import', 'MesinController::import');
    $routes->get('download-all-qr', 'MesinController::downloadAllQr');
});

// Admin - Master User (admin only)
$routes->group('admin/user', ['filter' => 'role:admin', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'UserController::index');
    $routes->get('create', 'UserController::create');
    $routes->post('store', 'UserController::store');
    $routes->get('edit/(:num)', 'UserController::edit/$1');
    $routes->post('update/(:num)', 'UserController::update/$1');
    $routes->get('delete/(:num)', 'UserController::delete/$1');
    $routes->get('export', 'UserController::export');
    $routes->post('import', 'UserController::import');
});

// Admin - Master Parameter Check (admin = full CRUD, sheadmtc = view-only)
$routes->group('admin/parameter', ['filter' => 'role:admin,sheadmtc', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'ParameterController::index');
    $routes->get('fixUrutan', 'ParameterController::fixUrutan');
    $routes->get('create', 'ParameterController::create');
    $routes->post('store', 'ParameterController::store');
    $routes->get('edit/(:num)', 'ParameterController::edit/$1');
    $routes->post('update/(:num)', 'ParameterController::update/$1');
    $routes->get('delete/(:num)', 'ParameterController::delete/$1');
    $routes->get('export', 'ParameterController::export');
    $routes->get('template', 'ParameterController::template');
    $routes->post('import', 'ParameterController::import');
});

// Admin - Master PIC (admin only)
$routes->group('admin/pic', ['filter' => 'role:admin', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'PicController::index');
    $routes->get('create', 'PicController::create');
    $routes->post('store', 'PicController::store');
    $routes->get('edit/(:segment)', 'PicController::edit/$1');
    $routes->post('update/(:segment)', 'PicController::update/$1');
    $routes->get('delete/(:segment)', 'PicController::delete/$1');
    $routes->get('export', 'PicController::export');
    $routes->get('template', 'PicController::template');
    $routes->post('import', 'PicController::import');
});

// Jadwal Preventive (semua role login, CRUD di controller dibatasi per role)
$routes->group('admin/jadwal', ['filter' => 'auth', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'JadwalController::index');
    $routes->get('events', 'JadwalController::events');
    $routes->post('store', 'JadwalController::store');
    $routes->post('delete/(:num)', 'JadwalController::delete/$1');
    $routes->get('export', 'JadwalController::export');
    $routes->get('template', 'JadwalController::template');
    $routes->post('import', 'JadwalController::import');
});

/**
 * Daftarkan alias filter berikut di app/Config/Filters.php, di dalam
 * property $aliases:
 *
 *   public array $aliases = [
 *       ...
 *       'auth' => \App\Filters\AuthFilter::class,
 *       'role' => \App\Filters\RoleFilter::class,
 *   ];
 */

