<?php
$files = [
    'c:/xampp/htdocs/mtce/app/Views/riwayat/detail.php',
    'c:/xampp/htdocs/mtce/app/Views/kontrol/index.php',
    'c:/xampp/htdocs/mtce/app/Views/approval/index.php',
    'c:/xampp/htdocs/mtce/app/Controllers/ApprovalController.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $newContent = str_replace('Approval Inbox', 'Approval', $content);
        if ($newContent !== $content) {
            file_put_contents($file, $newContent);
            echo "Updated: $file\n";
        }
    }
}
echo "Done.\n";
