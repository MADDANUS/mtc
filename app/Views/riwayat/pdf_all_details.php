
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

<?= view('partials/pdf_signature', ['header' => $header]) ?>

<?php if ($reportIndex < count($allReports) - 1): ?>
  <div style="page-break-after: always;"></div>
<?php endif; ?>
<?php endforeach; ?>

</div>
</body>
</html>