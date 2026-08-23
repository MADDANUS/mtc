<?php
$file_path = 'app/Views/partials/pdf_overhaul.php';
$content = file_get_contents($file_path);

$old_header = "        <?php if (strtolower(\$header['departemen_check']) !== 'mfg 2'): ?>\n        <th style=\"width:15%; text-align:center; background-color:#f2f2f2;\">STANDAR ITEM</th>\n        <?php endif; ?>";
$new_header = "        <?php \$isCNC = (stripos(\$header['kategori'] ?? '', 'CNC') !== false); ?>\n        <?php if (\$isCNC): ?>\n        <th style=\"width:15%; text-align:center; background-color:#f2f2f2;\">STANDAR ITEM</th>\n        <?php endif; ?>";
$content = str_replace($old_header, $new_header, $content);

$old_col1 = "            <?php \$colSpan = strtolower(\$header['departemen_check']) === 'mfg 2' ? 6 : 7; ?>";
$new_col1 = "            <?php \$colSpan = \$isCNC ? 7 : 6; ?>";
$content = str_replace($old_col1, $new_col1, $content);

$old_data = "          <?php if (strtolower(\$header['departemen_check']) !== 'mfg 2'): ?>\n            <?php if (!empty(\$d['show_standard'])): ?>\n              <td rowspan=\"<?= \$d['standard_rowspan'] ?? 1 ?>\" style=\"text-align:center; vertical-align:middle;\"><?= nl2br(esc(\$d['standard_check'] ?? '')) ?></td>\n            <?php endif; ?>\n          <?php endif; ?>";
$new_data = "          <?php if (\$isCNC): ?>\n            <?php if (!empty(\$d['show_standard'])): ?>\n              <td rowspan=\"<?= \$d['standard_rowspan'] ?? 1 ?>\" style=\"text-align:center; vertical-align:middle;\"><?= nl2br(esc(\$d['standard_check'] ?? '')) ?></td>\n            <?php endif; ?>\n          <?php endif; ?>";
$content = str_replace($old_data, $new_data, $content);

$old_col2 = "        <?php \$bottomColSpan = strtolower(\$header['departemen_check']) === 'mfg 2' ? 6 : 7; ?>";
$new_col2 = "        <?php \$bottomColSpan = \$isCNC ? 7 : 6; ?>";
$content = str_replace($old_col2, $new_col2, $content);

file_put_contents($file_path, $content);
echo "Success\n";
