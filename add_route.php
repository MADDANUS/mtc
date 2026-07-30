<?php
$file = 'c:/xampp/htdocs/mtce/app/Config/Routes.php';
$content = file_get_contents($file);

$content = str_replace(
    '$routes->post(\'approve\', \'KontrolController::approveBulanan\');',
    '$routes->post(\'approve\', \'KontrolController::approveBulanan\');
    $routes->post(\'delete-approval\', \'KontrolController::deleteApprovalBulanan\', [\'filter\' => \'role:admin\']);',
    $content
);

file_put_contents($file, $content);
echo "Added route.\n";
