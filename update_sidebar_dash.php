<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/dashboard/leader.php';
$content = file_get_contents($file);

// 1. Add ?from=approval to Inspection Report link
$content = str_replace(
    '<a href="<?= site_url(\'riwayat/\' . $po[\'id_transaksi\']) ?>" class="btn btn-sm btn-warning fw-bold rounded-pill px-3">',
    '<a href="<?= site_url(\'riwayat/\' . $po[\'id_transaksi\']) . \'?from=approval\' ?>" class="btn btn-sm btn-warning fw-bold rounded-pill px-3">',
    $content
);

// 2. Add ?from=approval to Ceklis Control link
$content = str_replace(
    '$kontrolUrl = site_url(\'kontrol\') . \'?lokasi=\' . urlencode($pk[\'lokasi\'] ?? \'\') . \'&line=\' . urlencode($pk[\'line\'] ?? \'\') . \'&kategori=\' . urlencode($pk[\'kategori\'] ?? \'\') . \'&bulan=\' . urlencode($pk[\'bulan_tahun\'] ?? \'\');',
    '$kontrolUrl = site_url(\'kontrol\') . \'?lokasi=\' . urlencode($pk[\'lokasi\'] ?? \'\') . \'&line=\' . urlencode($pk[\'line\'] ?? \'\') . \'&kategori=\' . urlencode($pk[\'kategori\'] ?? \'\') . \'&bulan=\' . urlencode($pk[\'bulan_tahun\'] ?? \'\') . \'&from=approval\';',
    $content
);

// 3. Format bulan_tahun
$bulanFormatLogic = <<<'EOD'
            <?php 
              $rawBulan = $pk['bulan_tahun'] ?? '';
              if (!empty($rawBulan)) {
                  $blnArr = explode('-', $rawBulan);
                  $namaBulanList = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                  if (count($blnArr) >= 2 && isset($namaBulanList[$blnArr[1]])) {
                      $bulanFormat = $namaBulanList[$blnArr[1]] . ' ' . $blnArr[0];
                  } else {
                      $bulanFormat = $rawBulan;
                  }
              } else {
                  $bulanFormat = '-';
              }
            ?>
            <td class="text-muted small"><?= esc($bulanFormat) ?></td>
EOD;

$content = str_replace(
    '<td class="text-muted small"><?= esc($pk[\'bulan_tahun\'] ?? \'-\') ?></td>',
    $bulanFormatLogic,
    $content
);

file_put_contents($file, $content);
echo "Updated dashboard/leader.php\n";

// Update header.php to handle ?from=approval in sidebar
$fileHeader = 'c:/xampp/htdocs/mtce/app/Views/layout/header.php';
$contentHeader = file_get_contents($fileHeader);

$contentHeader = str_replace(
    '<a href="<?= site_url(\'approval\') ?>" class="menu-item <?= $seg1 === \'approval\' ? \'active\' : \'\' ?>">',
    '<?php $isFromApproval = (isset($_GET[\'from\']) && $_GET[\'from\'] === \'approval\'); ?>
        <a href="<?= site_url(\'approval\') ?>" class="menu-item <?= ($seg1 === \'approval\' || $isFromApproval) ? \'active\' : \'\' ?>">',
    $contentHeader
);

$contentHeader = str_replace(
    '$isLaporanOpen = ($seg1 === \'riwayat\' || $seg1 === \'kontrol\' || $seg1 === \'abnormal\' || $seg1 === \'laporan\');',
    '$isLaporanOpen = (!$isFromApproval) && ($seg1 === \'riwayat\' || $seg1 === \'kontrol\' || $seg1 === \'abnormal\' || $seg1 === \'laporan\');',
    $contentHeader
);

file_put_contents($fileHeader, $contentHeader);
echo "Updated header.php\n";
