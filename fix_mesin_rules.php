<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/Admin/MesinController.php';
$content = file_get_contents($file);

$content = preg_replace('/\'type_mesin\'\s*=>\s*\'required\|max_length\[100\]\',/', '\'type_mesin\'      => ($this->request->getPost(\'lokasi\') === \'MFG 2\' ? \'permit_empty|max_length[100]\' : \'required|max_length[100]\'),', $content);
$content = preg_replace('/\'serial_nomor\'\s*=>\s*\'required\|max_length\[100\]\',/', '\'serial_nomor\'    => ($this->request->getPost(\'lokasi\') === \'MFG 2\' ? \'permit_empty|max_length[100]\' : \'required|max_length[100]\'),', $content);

file_put_contents($file, $content);
echo "Fixed rules in MesinController.php\n";
