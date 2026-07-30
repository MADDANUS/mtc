<?php
$files = [
    'c:/xampp/htdocs/mtce/app/Views/abnormal/index.php',
    'c:/xampp/htdocs/mtce/app/Views/abnormal/index_overhaul.php'
];

$old = "['member', 'sheadprd', 'sheadmtc', 'admin']";
$new = "['member', 'sheadprd', 'sheadmtc', 'admin', 'magang']";

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace($old, $new, $content);
        
        // Let's also make sure we change the comment if any:
        $oldComment = "<!-- MODAL QUICK EDIT ABNORMAL (LEADER & ADMIN ONLY) -->";
        $newComment = "<!-- MODAL QUICK EDIT ABNORMAL -->";
        $content = str_replace($oldComment, $newComment, $content);
        
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
