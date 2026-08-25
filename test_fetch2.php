<?php
require 'vendor/autoload.php';

$db = new mysqli("localhost", "root", "", "mtce_db");
$result = $db->query("SELECT id_transaksi FROM transaksi_check ORDER BY id_transaksi DESC LIMIT 1");
$row = $result->fetch_assoc();
$id = $row['id_transaksi'];

// Gunakan file_get_contents ke server dev lokal
$url = "http://localhost:8080/riwayat/download-excel-detail/" . $id;

$ch = curl_init($url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$detailHtml = curl_exec($ch);
curl_close($ch);

// Fix the HTML like in the controller
$htmlFixed = preg_replace('/&(?![A-Za-z0-9#]+;)/', '&amp;', $detailHtml);

$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($htmlFixed);
$errors = libxml_get_errors();
foreach ($errors as $error) {
    echo "Line " . $error->line . ": " . $error->message . "\n";
}
libxml_clear_errors();

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
try {
    $spreadsheet = $reader->loadFromString($htmlFixed);
    echo "PhpSpreadsheet Reader success!\n";
} catch (Exception $e) {
    echo "PhpSpreadsheet Reader failed: " . $e->getMessage() . "\n";
}

?>
