<?php
require 'app/Config/Paths.php';
$paths = new Config\Paths();
// No CI4 bootstrap, I will just connect to DB
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$stmt = $db->query("SELECT DISTINCT jenis FROM master_mesin WHERE plant = 'Plant 1' AND departemen = 'MFG 1' AND jenis IS NOT NULL AND jenis NOT IN ('-', 'CAM')");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = [];
$departemenName = 'MFG 1';
foreach ($results as $row) {
    $jenis = trim($row['jenis']);
    echo "Processing: '$jenis'\n";
    if (!empty($jenis)) {
        if (strtoupper($jenis) === 'CNC' && $departemenName === 'MFG 1') {
            $jenis = 'Mesin CNC & Bar Feeder';
            echo "Aliased to: $jenis\n";
        }
        
        // Simple slug simulation
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $jenis));
        $slug = trim($slug, '-');
        
        $categories[$slug] = strtoupper($jenis);
    }
}
print_r($categories);
