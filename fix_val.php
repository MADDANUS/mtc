<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/checklist/form.php';
$content = file_get_contents($file);

$content = str_replace(
    'if (fileInput && fileInput.files.length === 0) {',
    'if (fileInput && fileInput.hasAttribute(\'required\') && fileInput.files.length === 0) {',
    $content
);

file_put_contents($file, $content);
echo "Validation fixed.\n";
