<?php
// Boot CI4 to render the view
define('FCPATH', __DIR__ . '/public' . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
$app = Config\Services::codeigniter();
$app->initialize();

$riwayatService = new \App\Services\RiwayatService();
// We'll test with the first transaction we can find. Let's find an ID.
$db = \Config\Database::connect();
$id = $db->table('transaksi_check')->select('id_transaksi')->orderBy('id_transaksi', 'DESC')->get()->getRow()->id_transaksi;

$data = $riwayatService->getPdfData($id);
$data['title'] = 'Test';
$html = view('riwayat/detail_pdf', $data);

// Also try the preg_replace
$htmlFixed = preg_replace('/&(?![A-Za-z0-9#]+;)/', '&amp;', $html);

$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($htmlFixed);
$errors = libxml_get_errors();
foreach ($errors as $error) {
    echo "Line " . $error->line . ": " . $error->message;
}
libxml_clear_errors();
?>
