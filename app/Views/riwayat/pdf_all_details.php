
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PDF Export - <?= esc($title) ?></title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; margin: 0; padding: 0; }
    .pdf-container { padding: 10px 15px; width: 100%; }
    table {
      width: 100% !important;
      max-width: 100% !important;
      border-collapse: collapse !important;
      margin-bottom: 8px;
      word-wrap: break-word;
      margin-left: 0 !important;
      margin-right: 0 !important;
    }
    /* Override untuk tabel keterangan agar tidak dipaksa full width */
    table.keterangan-table {
      width: auto !important;
      max-width: none !important;
      table-layout: auto !important;
      margin-bottom: 6px;
      margin-top: 4px;
      margin-left: auto !important;
      margin-right: 0 !important;
    }
    table.keterangan-table td {
      border: 1.5pt solid #000;
      padding: 2px 5px;
      font-size: 10px;
    }
    th, td { border: 1.5pt solid #000; padding: 4px; font-size: 11px; vertical-align: middle; }
    .kop-table-title { background-color: #92b0d6; text-align: center; font-weight: bold; font-size: 13px; letter-spacing: 1px; color: #000; }
    .text-center { text-align: center; }
    .text-start  { text-align: left; }
    .fw-bold     { font-weight: bold; }
  </style>
</head>
<body>
  <div class="pdf-container">
  
  <?php if (isset($percentageSummary) && isset($filters)): ?>
  <div style="border: 1px solid #000; padding: 10px; margin-bottom: 20px; font-size: 12px; background-color: #f8f9fa;">
    <strong style="font-size: 14px;">RINGKASAN LAPORAN (Berdasarkan Filter)</strong><br>
    <?php
      $jenis = ($filters['jenis_check'] ?? '') === 'Overhaul' ? 'overhaul' : 'preventive';
      $cov = $percentageSummary[$jenis];
    ?>
    <table style="width: 100%; margin-top: 5px; border: none;">
      <tr>
        <td style="width: 25%; border: none;">Total Mesin Terdaftar</td>
        <td style="width: 75%; border: none;">: <strong><?= $percentageSummary['total_mesin'] ?></strong> Mesin</td>
      </tr>
      <tr>
        <td style="border: none;">Mesin Telah Dicek</td>
        <td style="border: none;">: <strong><?= $cov['checked'] ?></strong> Mesin (Capaian: <strong><?= $cov['coverage'] ?>%</strong>)</td>
      </tr>
      <tr>
        <td style="border: none;">Kondisi Mesin</td>
        <td style="border: none;">: Normal = <strong><?= $cov['normal_count'] ?></strong> (<?= $cov['normal'] ?>%) | Abnormal = <strong><?= $cov['abnormal_count'] ?></strong> (<?= $cov['abnormal'] ?>%)</td>
      </tr>
    </table>
  </div>
  <?php endif; ?>
  
  <?php foreach ($allReports as $reportIndex => $report): ?>
<?php
  $header = $report['header'];
  $details = $report['details'];
  $isOverhaul = strtolower($header['jenis_check']) === 'overhaul';
?>

<?php if ($isOverhaul): ?>
  <?= view('partials/pdf_overhaul', ['header' => $header, 'details' => $details]) ?>
<?php else: ?>
  <?= view('partials/pdf_preventive', ['header' => $header, 'details' => $details]) ?>
<?php endif; ?>



<?php if ($reportIndex < count($allReports) - 1): ?>
  <div style="page-break-after: always;"></div>
<?php endif; ?>
<?php endforeach; ?>

</div>
</body>
</html>
