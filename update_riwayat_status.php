<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/RiwayatController.php';
$content = file_get_contents($file);

$content = str_replace(
    '$statusFilter = [\'Approved L1\', \'Approved L2\', \'Approved\'];',
    '$statusFilter = [\'Approved L1\', \'Approved L2\', \'Approved\', \'Approved Final\'];',
    $content
);
$content = str_replace(
    '$statusFilter = [\'Approved L2\', \'Approved\'];',
    '$statusFilter = [\'Approved L2\', \'Approved\', \'Approved Final\'];',
    $content
);
$content = preg_replace(
    '/elseif \(\$role === \'sheadmtc\'\) \{\s*\$statusFilter = \'Approved\';/s',
    'elseif ($role === \'sheadmtc\') { $statusFilter = [\'Approved\', \'Approved Final\'];',
    $content
);
$content = preg_replace(
    '/else \{\s*\$statusFilter = \'Approved\';\s*\}/s',
    'else { $statusFilter = [\'Approved\', \'Approved Final\']; }',
    $content
);

file_put_contents($file, $content);
echo "Updated RiwayatController status filters to include Approved Final.\n";
