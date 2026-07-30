<?php
$db = \Config\Database::connect();
// Test the logic for deleting schedule
$schedule = ['lokasi' => 'MFG 1', 'kategori' => 'Penerangan', 'bulan_tahun' => '2026-07'];

$count = $db->table('transaksi_check')
    ->where('lokasi_check', $schedule['lokasi'])
    ->where('jenis_check', 'Preventive')
    ->where('kategori', $schedule['kategori'])
    ->like('created_at', $schedule['bulan_tahun'] . '-', 'after')
    ->countAllResults();

echo "Count for {$schedule['lokasi']} - {$schedule['kategori']} in {$schedule['bulan_tahun']}: $count\n";
