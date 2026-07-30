<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/checklist/form.php';
$content = file_get_contents($file);

// Fix button HTML syntax FOTO 1
$content = str_replace(
    'data-target-btn="btn-ambil-1-<?= (int) $r[\'id_parameter\'] ?>">
                                    style="display: <?= $hasF1 ? \'none\' : \'block\' ?>;">',
    'data-target-btn="btn-ambil-1-<?= (int) $r[\'id_parameter\'] ?>"
                                    style="display: <?= $hasF1 ? \'none\' : \'block\' ?>;">',
    $content
);

// Fix button HTML syntax FOTO 2
$content = str_replace(
    'data-target-btn="btn-ambil-2-<?= (int) $r[\'id_parameter\'] ?>">
                                    style="display: <?= $hasF2 ? \'none\' : \'block\' ?>;">',
    'data-target-btn="btn-ambil-2-<?= (int) $r[\'id_parameter\'] ?>"
                                    style="display: <?= $hasF2 ? \'none\' : \'block\' ?>;">',
    $content
);

// Fix preview div FOTO 1
$content = preg_replace(
    '/(<div class="foto-preview mt-1" id="foto-preview-1-<\?= \(int\) \$r\[\'id_parameter\'\] \?>") style="display:none; position:relative;">\s*<img src=""/s',
    '$1 style="display: <?= $hasF1 ? \'block\' : \'none\' ?>; position:relative;">
                              <img src="<?= $f1Url ?>"',
    $content
);

// Fix preview div FOTO 2
$content = preg_replace(
    '/(<div class="foto-preview mt-1" id="foto-preview-2-<\?= \(int\) \$r\[\'id_parameter\'\] \?>") style="display:none; position:relative;">\s*<img src=""/s',
    '$1 style="display: <?= $hasF2 ? \'block\' : \'none\' ?>; position:relative;">
                              <img src="<?= $f2Url ?>"',
    $content
);

file_put_contents($file, $content);
echo "Fixed syntax successfully.\n";
