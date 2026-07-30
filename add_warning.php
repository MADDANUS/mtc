<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/admin/jadwal/index.php';
$content = file_get_contents($file);

$oldForm = '<form id="deleteEventForm" method="post" action="">';
$newForm = '<form id="deleteEventForm" method="post" action="" onsubmit="return confirm(\'⚠️ PERINGATAN! ⚠️\n\nApakah Anda sangat yakin ingin menghapus jadwal ini?\n\nJadwal yang sudah dihapus tidak dapat dikembalikan lagi!\');">';

$content = str_replace($oldForm, $newForm, $content);
file_put_contents($file, $content);
echo "Warning added to delete form.\n";
