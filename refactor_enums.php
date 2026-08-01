<?php

$files = [
    'app/Controllers/RiwayatController.php',
    'app/Controllers/LaporanController.php',
    'app/Controllers/DashboardController.php',
    'app/Controllers/ChecklistController.php',
    'app/Controllers/ScanController.php',
    'app/Controllers/KontrolController.php',
    'app/Controllers/AbnormalController.php',
    'app/Controllers/Admin/UserController.php',
    'app/Controllers/Admin/PicController.php',
    'app/Controllers/Admin/MesinController.php',
    'app/Controllers/Admin/JadwalController.php',
    'app/Controllers/Admin/ParameterController.php',
    'app/Services/AbnormalService.php',
    'app/Services/KontrolService.php',
    'app/Services/ApprovalService.php',
    'app/Services/RiwayatService.php'
];

$replacements = [
    "'admin'" => "Role::Admin->value",
    "'member'" => "Role::Member->value",
    "'leader'" => "Role::Leader->value",
    "'sheadprd'" => "Role::Sheadprd->value",
    "'sheadmtc'" => "Role::Sheadmtc->value",
    "'magang'" => "Role::Magang->value",
    "'MFG 1'" => "Lokasi::MFG1->value",
    "'MFG 2'" => "Lokasi::MFG2->value",
    "'Preventive'" => "JenisCheck::Preventive->value",
    "'Overhaul'" => "JenisCheck::Overhaul->value",
];

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    $content = file_get_contents($file);
    $original = $content;
    
    foreach ($replacements as $search => $replace) {
        // (?<=[=,\[\(\s>]) ensures it is preceded by =, ,, [, (, whitespace, or >
        // (?=[,\]\);\s]) ensures it is followed by ,, ], ), ;, or whitespace
        $pattern = "/(?<=[=,\[\(\s>])" . preg_quote($search) . "(?=[,\]\);\s])/";
        $content = preg_replace($pattern, $replace, $content);
    }
    
    if ($content !== $original) {
        $uses = [];
        if (strpos($content, 'Role::') !== false && strpos($content, 'use App\Enums\Role;') === false) {
            $uses[] = "use App\Enums\Role;";
        }
        if (strpos($content, 'Lokasi::') !== false && strpos($content, 'use App\Enums\Lokasi;') === false) {
            $uses[] = "use App\Enums\Lokasi;";
        }
        if (strpos($content, 'JenisCheck::') !== false && strpos($content, 'use App\Enums\JenisCheck;') === false) {
            $uses[] = "use App\Enums\JenisCheck;";
        }
        
        if (!empty($uses)) {
            $content = preg_replace('/(namespace App\\\\[a-zA-Z0-9_]+;)/', "$1\n\n" . implode("\n", $uses), $content, 1);
        }
        
        file_put_contents($file, $content);
        echo "Refactored: $file\n";
    }
}
echo "Done.\n";
