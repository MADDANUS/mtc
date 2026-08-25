<?php
require 'vendor/autoload.php';

// Coba dapatkan HTML asli dengan koneksi ke DB langsung untuk menghindari error CI4 command line
$db = new mysqli("localhost", "root", "", "mtc");
$result = $db->query("SELECT id_transaksi FROM transaksi_check ORDER BY id_transaksi DESC LIMIT 1");
$row = $result->fetch_assoc();
$id = $row['id_transaksi'];

// Gunakan file_get_contents ke server dev lokal
$url = "http://localhost:8080/riwayat/download-excel-detail/" . $id;
echo "Testing ID: " . $id . "\n";

// Fetching raw HTML template
$ch = curl_init("http://localhost:8080/riwayat/" . $id); // Get to detail page to find pdf link if needed, but we can just use the view
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$detailHtml = curl_exec($ch);
curl_close($ch);
echo "Fetched detail page.\n";
?>
