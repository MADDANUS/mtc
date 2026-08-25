<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Riwayat Pengecekan</x:Name>
    <x:WorksheetOptions>
     <x:DisplayGridlines/>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </x:ExcelWorkbook>
</xml>
<![endif]-->
  <style>
    body { font-family: 'Arial', sans-serif; font-size: 11px; margin: 0; padding: 0; }
    .pdf-container { padding: 10px; }
    table {
      border-collapse: collapse;
      margin-bottom: 8px;
    }
    table, th, td { 
      border: 1.5pt solid #000000; 
    }
    th, td { 
      padding: 4px; 
      font-size: 11px; 
      vertical-align: middle; 
    }
    .kop-table-title { 
      background-color: #92b0d6; 
      text-align: center; 
      font-weight: bold; 
      font-size: 13px; 
      color: #000000; 
    }
    .text-center { text-align: center; }
    .text-start  { text-align: left; }
    .fw-bold     { font-weight: bold; }
  </style>
</head>
<body>
<div class="pdf-container">

<?php
  $isOverhaul = strtolower($header['jenis_check']) === 'overhaul';
?>

<?php if ($isOverhaul): ?>
  <?= view('partials/pdf_overhaul', ['header' => $header, 'details' => $details]) ?>
<?php else: ?>
  <?= view('partials/pdf_preventive', ['header' => $header, 'details' => $details]) ?>
<?php endif; ?>

</div>
</body>
</html>
