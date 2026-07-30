<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/RiwayatController.php';
$content = file_get_contents($file);

$content = str_replace(
    'else { $statusFilter = [\'Approved\', \'Approved Final\']; }',
    'elseif ($role === \'magang\') { $statusFilter = null; } else { $statusFilter = [\'Approved\', \'Approved Final\']; }',
    $content
);

file_put_contents($file, $content);
echo "Updated RiwayatController for magang.\n";
