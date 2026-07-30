<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/checklist/form.php';
$content = file_get_contents($file);

$content = str_replace(
    "if (name === 'csrf_test_name') continue;",
    "if (name === 'csrf_test_name' || name === 'waktu_mulai' || name === 'waktu_selesai') continue;",
    $content
);

file_put_contents($file, $content);
echo "Fixed autosave time logic\n";
