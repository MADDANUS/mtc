<?php
// Script to replace 'Gearbox' with 'Gearbox Cam' in files and database

$files = [
    'app/Views/checklist/index.php',
    'app/Views/admin/jadwal/index.php',
    'app/Models/CeklisKontrolModel.php',
    'app/Services/KontrolService.php',
    'app/Services/ApprovalService.php',
    'app/Services/AbnormalService.php',
    'app/Controllers/RiwayatController.php',
    'app/Controllers/ChecklistController.php',
    'app/Controllers/Admin/JadwalController.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Replace exact matches
        $content = str_replace("'Gearbox'", "'Gearbox Cam'", $content);
        $content = str_replace('"Gearbox"', '"Gearbox Cam"', $content);
        $content = str_replace('>Gearbox<', '>Gearbox Cam<', $content);
        
        // For array keys or slugs (optional, let's keep it safe and just replace 'gearbox' => 'Gearbox Cam' or similar)
        $content = str_replace("'gearbox'        => 'Gearbox'", "'gearbox'        => 'Gearbox Cam'", $content);
        $content = str_replace("'gearbox' => 'Gearbox'", "'gearbox' => 'Gearbox Cam'", $content);
        
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}

// Database updates
$db = \Config\Database::connect();

$tables = [
    'master_parameter' => 'kategori',
    'ceklis_preventive' => 'kategori',
    'jadwal_preventive' => 'kategori',
    'abnormalitas' => 'kategori',
    'approval_bulanan' => 'kategori'
];

foreach ($tables as $table => $column) {
    if ($db->tableExists($table)) {
        $db->table($table)->where($column, 'Gearbox')->update([$column => 'Gearbox Cam']);
        echo "Updated table $table\n";
    }
}

echo "All done!\n";
