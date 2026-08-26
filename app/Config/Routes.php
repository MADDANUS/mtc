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
$routes->get('dashboard/detail-pencapaian', 'DashboardController::detailPencapaian', ['filter' => 'auth']);
$routes->get('dashboard/detail-kategori-mesin', 'DashboardController::detailKategoriMesin', ['filter' => 'auth']);
$routes->post('dashboard/akhiri-periode-overhaul', 'DashboardController::akhiriPeriodeOverhaul', ['filter' => 'auth']);
$routes->post('dashboard/awali-periode-overhaul', 'DashboardController::awaliPeriodeOverhaul', ['filter' => 'auth']);

// Settings
$routes->get('ganti-password', 'Auth::gantiPasswordForm', ['filter' => 'auth']);
$routes->post('ganti-password', 'Auth::updatePassword', ['filter' => 'auth']);

// Checklist Dinamis (magang, member, admin bisa buat pengecekan)
$routes->group('checklist', ['filter' => 'role:admin,magang,member,leader mtc'], static function ($routes) {
    // 0. API Check Duplicate
    $routes->post('check-duplicate', 'ChecklistController::checkDuplicate');

    // 1. Pilih plant
    $routes->get('/', 'ChecklistController::pilihPlan');
    
    // 1b. Pilih Departemen
    $routes->get('plant/(:segment)', 'ChecklistController::pilihDepartemen/$1');
    
    // 2. Pilih Jenis Pengecekan
    $routes->get('plant/(:segment)/(:segment)', 'ChecklistController::pilihJenis/$1/$2');
    
    // 3. Pilih Kategori (Line / Kategori)
    $routes->get('plant/(:segment)/(:segment)/(:segment)', 'ChecklistController::indexKategori/$1/$2/$3');
    
    // 4. Form Checklist (Create)
    $routes->get('plant/(:segment)/(:segment)/(:segment)/create/(:segment)', 'ChecklistController::create/$1/$2/$3/$4');
    
    // 5. Simpan Pengecekan
    $routes->post('plant/(:segment)/(:segment)/(:segment)/store', 'ChecklistController::store/$1/$2/$3');
});

// Riwayat & Detail Transaksi (semua role login, scoping data ditangani di controller)
$routes->group('riwayat', ['filter' => 'auth'], static function ($routes) {
    $routes->post('bulk-delete', 'RiwayatController::bulkDelete');
    $routes->get('/', 'RiwayatController::index');
    $routes->get('redirect-detail', 'RiwayatController::redirectDetail');
    $routes->get('departemen/(:segment)', 'RiwayatController::departemen/$1');
    $routes->get('download-pdf-all/(:segment)', 'RiwayatController::downloadPdfAll/$1', ['filter' => 'role:member,leader mtc,admin,magang,leader mtc']);
    $routes->get('export-excel/(:segment)', 'RiwayatController::exportExcel/$1', ['filter' => 'role:member,leader mtc,admin,magang,leader mtc']);
    $routes->get('download-pdf/(:num)', 'RiwayatController::downloadPdf/$1', ['filter' => 'role:member,leader mtc,admin,magang,leader mtc']);
    $routes->get('download-excel-detail/(:num)', 'RiwayatController::downloadExcelDetail/$1', ['filter' => 'role:member,leader mtc,admin,magang,leader mtc']);
    $routes->get('(:num)', 'RiwayatController::detail/$1');
    $routes->post('approve/(:num)', 'RiwayatController::approve/$1', ['filter' => 'role:member,leader mtc,sheadprd,sheadmtc,admin,leader']);
    
    // Khusus Admin & Leader MTC: Edit & Hapus Riwayat
    $routes->get('edit/(:num)', 'RiwayatController::edit/$1', ['filter' => 'role:admin,leader mtc']);
    $routes->post('update/(:num)', 'RiwayatController::update/$1', ['filter' => 'role:admin,leader mtc']);
    $routes->post('delete/(:num)', 'RiwayatController::delete/$1', ['filter' => 'role:admin,leader mtc']);
    $routes->post('delete-approval/(:num)', 'RiwayatController::deleteApproval/$1', ['filter' => 'role:admin']);
});

// Scan QR Code (magang, member, admin)
$routes->group('scan', ['filter' => 'role:magang,member,leader mtc,admin'], static function ($routes) {
    $routes->get('/', 'ScanController::index');
    $routes->get('mesin/(:num)', 'ScanController::mesin/$1');
});

// Checklist Control Bulanan (semua role kecuali magang)
$routes->group('kontrol', ['filter' => 'role:admin,member,leader mtc,leader,sheadprd,sheadmtc'], static function ($routes) {
    $routes->get('/', 'KontrolController::index');
    $routes->get('pdf', 'KontrolController::pdf', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('pdf-all-categories', 'KontrolController::pdfAllCategories', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('pdf-all-summary', 'KontrolController::pdfAllSummary', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('excel', 'KontrolController::excelPerKategori', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('excel-all-categories', 'KontrolController::excelAllCategories', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('excel-all-summary', 'KontrolController::excelAllSummary', ['filter' => 'role:member,leader mtc,admin']);
    $routes->post('update-cell', 'KontrolController::updateCell');
    $routes->post('approve', 'KontrolController::approveBulanan');
    $routes->post('delete-approval', 'KontrolController::deleteApprovalBulanan');
});

// Abnormal Report Condition (semua role login)
$routes->group('abnormal', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/', 'AbnormalController::index');
    $routes->get('pdf', 'AbnormalController::pdf', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('pdf-all-categories', 'AbnormalController::pdfAllCategories', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('pdf-all-summary', 'AbnormalController::pdfAllSummary', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('excel', 'AbnormalController::excelPerKategori', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('excel-all-categories', 'AbnormalController::excelAllCategories', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('excel-all-summary', 'AbnormalController::excelAllSummary', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('chart-data', 'AbnormalController::getChartData', ['filter' => 'auth']);
    $routes->post('update', 'AbnormalController::update');
    $routes->post('approve', 'AbnormalController::approveBulanan');
    
    // Abnormal Khusus Overhaul
    $routes->get('overhaul', 'AbnormalController::overhaul', ['filter' => 'role:admin,member,leader mtc,leader,sheadprd,sheadmtc']);
    $routes->get('overhaul/pdf', 'AbnormalController::pdfOverhaul', ['filter' => 'role:member,leader mtc,admin']);
    $routes->get('overhaul/pdf-all-summary', 'AbnormalController::pdfAllSummaryOverhaul', ['filter' => 'role:member,leader mtc,admin']);
    $routes->post('overhaul/update', 'AbnormalController::updateOverhaul');
    
    // Upload Foto Perbaikan (semua yang bisa akses abnormal)
    $routes->post('upload-foto-perbaikan', 'AbnormalController::uploadFotoPerbaikan');
    $routes->post('delete-foto-perbaikan', 'AbnormalController::deleteFotoPerbaikan');
});

// Laporan Durasi (member, admin)
$routes->get('admin/user/export', 'Admin\UserController::export', ['filter' => 'role:admin,sheadmtc,sheadprd,leader,leader mtc']);
$routes->get('admin/audit-log', 'Admin\AuditLogController::index', ['filter' => 'role:admin']);
  $routes->get('admin/log-mesin', 'Admin\LogMesinController::index', ['filter' => 'role:admin']);
  $routes->get('admin/log-user', 'Admin\LogUserController::index', ['filter' => 'role:admin']);
  $routes->get('testqueries', 'TestQueries::index');
  $routes->get('laporan/durasi', 'LaporanController::durasi', ['filter' => 'role:member,leader mtc,admin']);
  $routes->get('laporan/durasi-pdf', 'LaporanController::durasiPdf', ['filter' => 'role:member,leader mtc,admin']);
  $routes->get('laporan/durasi-excel', 'LaporanController::durasiExcel', ['filter' => 'role:member,leader mtc,admin']);

// Approval Inbox (semua role kecuali magang)
$routes->get('approval', 'ApprovalController::index', ['filter' => 'role:admin,member,leader mtc,leader,sheadprd,sheadmtc']);

$routes->group('admin/mesin', ['filter' => 'role:admin,member,leader mtc,sheadprd,sheadmtc,leader', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'MesinController::index');
    $routes->get('create', 'MesinController::create');
    $routes->post('store', 'MesinController::store');
    $routes->get('edit/(:num)', 'MesinController::edit/$1');
    $routes->post('update/(:num)', 'MesinController::update/$1');
    $routes->post('delete-batch', 'MesinController::deleteBatch');
    $routes->post('delete/(:num)', 'MesinController::delete/$1');
    $routes->get('export', 'MesinController::export');
    $routes->get('template', 'MesinController::template');
    $routes->post('import', 'MesinController::import');
    $routes->get('download-all-qr', 'MesinController::downloadAllQr');
    $routes->get('riwayat/(:num)', 'MesinController::getRiwayat/$1');
    $routes->post('riwayat/delete', 'MesinController::deleteRiwayat');

    $routes->get('generate-qr', 'MesinController::generateQr');
});

// Admin - Master User (admin only)
$routes->group('admin/user', ['filter' => 'role:admin,leader mtc', 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'UserController::index');
    $routes->get('create', 'UserController::create');
    $routes->post('store', 'UserController::store');
    $routes->get('edit/(:num)', 'UserController::edit/$1');
    $routes->post('update/(:num)', 'UserController::update/$1');
    $routes->post('delete-batch', 'UserController::deleteBatch');
    $routes->post('delete/(:num)', 'UserController::delete/$1');
    $routes->get('export', 'UserController::export');
    $routes->post('import', 'UserController::import');
    $routes->get('toggle-active/(:num)', 'UserController::toggleActive/$1');
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

