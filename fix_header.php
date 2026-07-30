<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/layout/header.php';
$content = file_get_contents($file);

$content = str_replace(
    '<?php $isFromApproval = (isset($_GET[\'from\']) && $_GET[\'from\'] === \'approval\'); ?>
        <a href="<?= site_url(\'approval\') ?>" class="menu-item <?= ($seg1 === \'approval\' || $isFromApproval) ? \'active\' : \'\' ?>">',
    '<a href="<?= site_url(\'approval\') ?>" class="menu-item <?= ($seg1 === \'approval\' || $isFromApproval) ? \'active\' : \'\' ?>">',
    $content
);

$content = str_replace(
    '<?php if (in_array($role, [\'admin\', \'member\', \'leader\', \'sheadprd\', \'sheadmtc\'], true)): ?>',
    '<?php $isFromApproval = (isset($_GET[\'from\']) && $_GET[\'from\'] === \'approval\'); ?>
      <?php if (in_array($role, [\'admin\', \'member\', \'leader\', \'sheadprd\', \'sheadmtc\'], true)): ?>',
    $content
);

file_put_contents($file, $content);
echo "Fixed undefined variable error.\n";
