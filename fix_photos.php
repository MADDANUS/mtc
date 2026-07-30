<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/checklist/form.php';
$content = file_get_contents($file);

// 1. OVERHAUL: Replace FOTO 1 input
$content = preg_replace(
    '/(<input type="file" class="d-none foto-abnormal-input"\s*name="foto_abnormal\[<\?= \(int\) \$r\[\'id_parameter\'\] \?>\]"\s*id="foto-input-1-<\?= \(int\) \$r\[\'id_parameter\'\] \?>" accept="image\/jpeg" <\?= \$h === \'([^\']+)\' \? \'required\' : \'\' \?>\>)/is',
    '<?php 
                            $f1 = $detailsMap[$r[\'id_parameter\']][\'foto_abnormal\'] ?? null;
                            $hasF1 = !empty($f1);
                            $f1Url = $hasF1 ? base_url(\'uploads/abnormal/\' . $f1) : \'\';
                            ?>
                            <input type="file" class="d-none foto-abnormal-input"
                                   name="foto_abnormal[<?= (int) $r[\'id_parameter\'] ?>]"
                                   id="foto-input-1-<?= (int) $r[\'id_parameter\'] ?>" accept="image/jpeg" <?= ($h === \'$2\' && !$hasF1) ? \'required\' : \'\' ?>>',
    $content
);

// 2. OVERHAUL: Replace FOTO 1 button
$content = preg_replace(
    '/(<button type="button" class="btn btn-sm btn-warning w-100 btn-ambil-foto main-btn-foto" id="btn-ambil-1-<\?= \(int\) \$r\[\'id_parameter\'\] \?>"\s*data-target-input="foto-input-1-<\?= \(int\) \$r\[\'id_parameter\'\] \?>"\s*data-target-preview="foto-preview-1-<\?= \(int\) \$r\[\'id_parameter\'\] \?>"\s*data-target-btn="btn-ambil-1-<\?= \(int\) \$r\[\'id_parameter\'\] \?>">)/is',
    '$1
                                    style="display: <?= $hasF1 ? \'none\' : \'block\' ?>;">',
    $content
);

// 3. OVERHAUL: Replace FOTO 1 preview
$content = str_replace(
    '<div class="foto-preview mt-1" id="foto-preview-1-<?= (int) $r[\'id_parameter\'] ?>" style="display:none; position:relative;">
                              <img src="" alt="Preview 1" class="preview-img-click" style="max-width:100%; max-height:100px; border-radius:4px; border:2px solid #ffc107; display:block; cursor:pointer;" title="Klik untuk memperbesar">',
    '<div class="foto-preview mt-1" id="foto-preview-1-<?= (int) $r[\'id_parameter\'] ?>" style="display: <?= $hasF1 ? \'block\' : \'none\' ?>; position:relative;">
                              <img src="<?= $f1Url ?>" alt="Preview 1" class="preview-img-click" style="max-width:100%; max-height:100px; border-radius:4px; border:2px solid #ffc107; display:block; cursor:pointer;" title="Klik untuk memperbesar">',
    $content
);


// 4. OVERHAUL: Replace FOTO 2 input
$content = preg_replace(
    '/(<input type="file" class="d-none"\s*name="foto_abnormal_2\[<\?= \(int\) \$r\[\'id_parameter\'\] \?>\]"\s*id="foto-input-2-<\?= \(int\) \$r\[\'id_parameter\'\] \?>" accept="image\/jpeg">)/is',
    '<?php 
                            $f2 = $detailsMap[$r[\'id_parameter\']][\'foto_abnormal_2\'] ?? null;
                            $hasF2 = !empty($f2);
                            $f2Url = $hasF2 ? base_url(\'uploads/abnormal/\' . $f2) : \'\';
                            ?>
                            <input type="file" class="d-none"
                                   name="foto_abnormal_2[<?= (int) $r[\'id_parameter\'] ?>]"
                                   id="foto-input-2-<?= (int) $r[\'id_parameter\'] ?>" accept="image/jpeg">',
    $content
);

// 5. OVERHAUL: Replace FOTO 2 button
$content = preg_replace(
    '/(<button type="button" class="btn btn-sm btn-outline-secondary w-100 btn-ambil-foto main-btn-foto" id="btn-ambil-2-<\?= \(int\) \$r\[\'id_parameter\'\] \?>"\s*data-target-input="foto-input-2-<\?= \(int\) \$r\[\'id_parameter\'\] \?>"\s*data-target-preview="foto-preview-2-<\?= \(int\) \$r\[\'id_parameter\'\] \?>"\s*data-target-btn="btn-ambil-2-<\?= \(int\) \$r\[\'id_parameter\'\] \?>">)/is',
    '$1
                                    style="display: <?= $hasF2 ? \'none\' : \'block\' ?>;">',
    $content
);

// 6. OVERHAUL: Replace FOTO 2 preview
$content = str_replace(
    '<div class="foto-preview mt-1" id="foto-preview-2-<?= (int) $r[\'id_parameter\'] ?>" style="display:none; position:relative;">
                              <img src="" alt="Preview 2" class="preview-img-click" style="max-width:100%; max-height:100px; border-radius:4px; border:2px solid #6c757d; display:block; cursor:pointer;" title="Klik untuk memperbesar">',
    '<div class="foto-preview mt-1" id="foto-preview-2-<?= (int) $r[\'id_parameter\'] ?>" style="display: <?= $hasF2 ? \'block\' : \'none\' ?>; position:relative;">
                              <img src="<?= $f2Url ?>" alt="Preview 2" class="preview-img-click" style="max-width:100%; max-height:100px; border-radius:4px; border:2px solid #6c757d; display:block; cursor:pointer;" title="Klik untuk memperbesar">',
    $content
);

file_put_contents($file, $content);
echo "File updated successfully.\n";
