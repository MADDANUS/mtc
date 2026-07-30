<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/admin/mesin/index.php';
$content = file_get_contents($file);

$oldButton = "href=\"<?= site_url('admin/mesin/export') ?>\"";
$newButton = "href=\"<?= site_url('admin/mesin/export') . (!empty(\$_GET) ? '?' . http_build_query(\$_GET) : '') ?>\"";

$content = str_replace($oldButton, $newButton, $content);
file_put_contents($file, $content);
echo "View index.php updated.\n";
