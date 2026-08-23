<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PDF Export - Checklist Control</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; margin: 0; padding: 0; }
    .pdf-container { padding: 10px 15px; width: 100%; }
    table { width: 100% !important; max-width: 100% !important; border-collapse: collapse !important; margin-bottom: 20px; table-layout: fixed !important; word-wrap: break-word; margin-left: 0 !important; margin-right: 0 !important; }
    table.keterangan-table { width: auto !important; max-width: none !important; table-layout: auto !important; margin-bottom: 6px; margin-left: auto !important; margin-right: 0 !important; }
    table.keterangan-table td { border: 1.5pt solid #000; padding: 2px 6px; font-size: 10px; }
    th, td { border: 1.5pt solid #000; padding: 4px; font-size: 11px; }
    .text-center { text-align: center; }
    .text-start { text-align: left; }
    .text-end { text-align: right; }
    .fw-bold { font-weight: bold; }
    .bg-light { background-color: #f8f9fa; }
    .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: #fff; }
    .kop-table th, .kop-table td { border: 1.5pt solid #000; padding: 3px 8px; vertical-align: middle; }
    .kop-table-title { background-color: #92b0d6; text-align: center; font-weight: bold; font-size: 14px; letter-spacing: 1px; color: #000; }
    .kop-logo { text-align: center; width: 12%; font-weight: bold; }
  </style>
</head>
<body>
<div class="pdf-container">
<table style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:0;">

  <!-- ROW 1: Logo (rowspan=4) + CHECKLIST CONTROL -->
  <tr>
    <td rowspan="4" style="width:15%; border:1.5pt solid #000; text-align:center; vertical-align:middle; padding:6px 4px;">
      <div style="width:44px; height:44px; border:2px double #0000ff; border-radius:50%; margin:0 auto; position:relative;">
        <div style="position:absolute; top:-10px; left:50%; transform:translateX(-50%); background:#fff; padding:0 3px; font-size:1.1rem; font-weight:normal; color:#0000ff;">NSI</div>
      </div>
      <div style="font-size:0.6rem; margin-top:6px; font-style:italic; color:#0070c0; line-height:1.4; text-align:center;">
        <div>The Future in Our</div>
        <div>Hands</div>
      </div>
    </td>
    <td colspan="8" style="border:1.5pt solid #000; text-align:center; font-weight:bold; font-size:16px; padding:6px; letter-spacing:0.5px;">CHECKLIST CONTROL</td>
  </tr>

  <!-- ROW 2: Kategori + Departemen -->
  <tr>
    <td colspan="8" style="border:1.5pt solid #000; text-align:center; font-weight:bold; font-size:13px; padding:4px;"><?= strtoupper($kategori) ?> (<?= isset($plant) ? strtoupper($plant) . ' - ' : '' ?><?= strtoupper($departemen) ?>)</td>
  </tr>

  <!-- ROW 3: Label NO. DOCUMENT / NO. REVISI -->
  <tr>
    <td colspan="4" style="border:1.5pt solid #000; text-align:center; font-weight:bold; padding:3px 4px;">NO. DOCUMENT</td>
    <td colspan="4" style="border:1.5pt solid #000; text-align:center; font-weight:bold; padding:3px 4px;">NO. REVISI</td>
  </tr>

  <!-- ROW 4: Nilai FM-MTN-09 / 0 -->
  <tr>
    <td colspan="4" style="border:1.5pt solid #000; text-align:center; padding:3px 4px;">FM-MTN-09</td>
    <td colspan="4" style="border:1.5pt solid #000; text-align:center; padding:3px 4px;">0</td>
  </tr>

  <!-- ROW 5: Rev - full width colspan=9 (termasuk kolom logo) -->
  <tr>
    <td colspan="9" style="border:1.5pt solid #000; border-top:none; text-align:left; font-size:10px; padding:1px 5px;">Rev.:0/2911/24</td>
  </tr>
</table>



<table style="width:100%; border-collapse:collapse; font-size:11px; margin-top:0;">

  <thead>
  <!-- HEADER ISI Row 1: NO | MESIN | WAKTU(5) | Out of Plan | ULASAN -->
  <tr>
    <th rowspan="3" style="border:1.5pt solid #000; width:5%; text-align:center; vertical-align:middle; background-color:#f2f2f2; font-weight:700; padding:3px;">NO</th>
    <th rowspan="3" style="border:1.5pt solid #000; width:22%; text-align:center; vertical-align:middle; background-color:#f2f2f2; font-weight:700; padding:3px;">MESIN</th>
    <th colspan="5" style="border:1.5pt solid #000; text-align:center; background-color:#f2f2f2; font-weight:700; padding:3px;">WAKTU</th>
    <th rowspan="3" style="border:1.5pt solid #000; width:14%; text-align:center; vertical-align:middle; background-color:#f2f2f2; font-weight:700; padding:3px;">Out of Plan</th>
    <th rowspan="3" style="border:1.5pt solid #000; width:20%; text-align:center; vertical-align:middle; background-color:#f2f2f2; font-weight:700; padding:3px;">ULASAN</th>
  </tr>

  <!-- HEADER ISI Row 2: Nama Bulan -->
  <tr>
      <th colspan="5" style="border:1.5pt solid #000; text-align:center; background-color:#f2f2f2; font-weight:700; padding:3px; text-transform:uppercase;">
        <?= strtoupper(format_bulan_indo($bulan)) ?>
      </th>
  </tr>

  <!-- HEADER ISI Row 3: Nomor Periode / Tanggal -->
  <tr>
    <?php for ($col = 1; $col <= 5; $col++): ?>
    <th style="border:1.5pt solid #000; width:7%; text-align:center; background-color:#f2f2f2; font-weight:700; padding:3px;">
      <?php if ($hasSchedule && !empty($columnDates[$col])): ?>
        <?= date('d', strtotime($columnDates[$col])) ?>
      <?php else: ?>
        <?= $col ?>
      <?php endif; ?>
    </th>
    <?php endfor; ?>
  </tr>
  </thead>
  <!-- BODY TABEL ISI -->
  <?php if (empty($grid)): ?>
  <tbody>
  <tr>
    <td colspan="9" style="border:1.5pt solid #000; text-align:center; padding:10px;">Belum ada data mesin terdaftar di <?= esc($departemen) ?>.</td>
  </tr>
  </tbody>
  <?php else: ?>
    <?php 
      $no = 1; 
      $totalGridRows = count($grid);
      $loopGridIndex = 0;
      foreach ($grid as $row): 
        $loopGridIndex++;
        $isLastRow = ($loopGridIndex === $totalGridRows);
    ?>
      <?php $m = $row['mesin']; $idMesin = (int)$m['id_mesin']; ?>
      <tbody style="page-break-inside: avoid;">
      <!-- BARIS STATUS CHECK -->
      <tr style="page-break-after: avoid;">
        <td style="border:1.5pt solid #000; border-bottom:none; text-align:center; vertical-align:middle; font-weight:bold;"><?= $no++ ?></td>
        <td style="border:1.5pt solid #000; text-align:left; font-weight:bold; padding-left:6px; padding-top:2px; padding-bottom:2px;"><?= (isset($departemen) && $departemen === 'MFG 2') ? esc($m['no_mesin']) : (!empty($m['jenis']) ? esc($m['jenis']) . ' ' . esc($m['no_mesin']) : esc($m['no_mesin'])) ?></td>
        <?php for ($p = 1; $p <= 5; $p++): ?>
          <?php
            $cell = $row['periodes'][$p];
            $status = $cell ? $cell['status_check'] : '';
          ?>
          <td style="border:1.5pt solid #000; text-align:center; padding:2px;">
            <?php if ($status === 'V'): ?>
              <span style="color:green; font-weight:bold;">V</span>
            <?php elseif ($status === 'Δ'): ?>
              <span style="color:#b8860b; font-weight:bold;">Δ</span>
            <?php elseif ($status === 'X'): ?>
              <span style="color:red; font-weight:bold;">X</span>
            <?php else: ?>
              <span style="color:#ccc;">-</span>
            <?php endif; ?>
          </td>
        <?php endfor; ?>
        <!-- Out of Plan -->
        <td style="border:1.5pt solid #000; text-align:center; font-size:0.75rem; padding:2px;">
          <?php if (!empty($row['out_of_plan'])): ?>
            <span style="color:red; font-weight:bold; display:block;">Out of Plan</span>
            <span style="font-size:0.65rem;"><?= format_tanggal_indo($row['out_of_plan']) ?></span>
          <?php else: ?>-<?php endif; ?>
        </td>
        <!-- Ulasan -->
        <td style="border:1.5pt solid #000; text-align:left; padding:2px 4px; font-size:0.75rem;">
          <?= esc($row['ulasan']) ?: '-' ?>
          <?php if (!empty($row['photos'])): ?>
            <?php foreach ($row['photos'] as $ph): ?>
              <?php 
                $imgPath = FCPATH . 'uploads/abnormal/' . $ph;
                if (file_exists($imgPath)): 
                  $type = pathinfo($imgPath, PATHINFO_EXTENSION);
                  $base64 = base64_encode(file_get_contents($imgPath));
                  $src = 'data:image/' . $type . ';base64,' . $base64;
              ?>
                <br><img src="<?= $src ?>" style="max-height: 50px; margin-top: 2px; border: 1px solid #ccc;">
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </td>
      </tr>
      <!-- BARIS PIC -->
      <tr>
        <td style="border:1.5pt solid #000; border-top:none; text-align:center;"></td>
        <td style="border:1.5pt solid #000; text-align:left; font-size:0.7rem; padding:1px 6px; color:#555;">PIC</td>
        <?php for ($p = 1; $p <= 5; $p++): ?>
          <?php
            $cell = $row['periodes'][$p];
            $pic = $cell ? $cell['pic_nama'] : '';
            $picParts = explode(' - ', $pic);
            $picOnly = end($picParts);
          ?>
          <td style="border:1.5pt solid #000; text-align:center; font-size:0.68rem; padding:1px;"><?= esc($picOnly) ?: '-' ?></td>
        <?php endfor; ?>
        <td style="border:1.5pt solid #000;"></td>
        <td style="border:1.5pt solid #000;"></td>
      </tr>
      <?php if ($isLastRow): ?>
      <tr style="page-break-inside: avoid; page-break-before: avoid;">
        <td colspan="9" style="border:none; padding:0;">
          
          <!-- KETERANGAN CHECK LIST dipindah ke bawah -->
          <table class="keterangan-table" style="margin-top:10px; margin-left:auto; width:200px;">
            <tr>
              <td colspan="3" style="text-align:center; font-weight:bold; padding:3px 20px; background-color:#f2f2f2;">KETERANGAN CHECK LIST</td>
            </tr>
            <tr>
              <td style="text-align:center; font-weight:bold; width:22px;">V</td>
              <td style="text-align:center; width:14px;">:</td>
              <td style="text-align:left; font-weight:bold; padding-left:8px;">OK</td>
            </tr>
            <tr>
              <td style="text-align:center; font-weight:bold;">&#916;</td>
              <td style="text-align:center;">:</td>
              <td style="text-align:left; font-weight:bold; padding-left:8px;">PERLU TINDAKAN</td>
            </tr>
            <tr>
              <td style="text-align:center; font-weight:bold;">X</td>
              <td style="text-align:center;">:</td>
              <td style="text-align:left; font-weight:bold; padding-left:8px;">TIDAK ADA</td>
            </tr>
          </table>

          <!-- SIGNATURE BOX dimasukkan ke tabel -->
          <table style="width: 100%; border-collapse: collapse; text-align: center; margin-top: 10px;">
            <tr>
              <td style="border: 1.5pt solid #000; width: 33.33%; vertical-align: top; padding: 10px;">
                <div style="margin-bottom: 5px; font-size: 0.85rem;">Dibuat Oleh</div>
                <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">PIC LINE</div>
                <?php if (isset($approvalData['approved_l1_by'])): ?>
                  <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Disetujui ]</div>
                <?php else: ?>
                  <div style="height: 20px; margin-bottom: 20px;"></div>
                <?php endif; ?>
                <div style="font-weight: bold; text-decoration: underline; font-size: 0.9rem;">
                  <?= isset($approvalData['approved_l1_by']) ? esc($approvalData['l1_name'] ?? '') : '( ........................................ )' ?>
                </div>
                <div style="font-size: 0.8rem; color: #555;">
                  Tanggal: <?= isset($approvalData['approved_l1_at']) ? format_tanggal_indo($approvalData['approved_l1_at'], false, true) : '( ......................... )' ?>
                </div>
              </td>
              <td style="border: 1.5pt solid #000; width: 33.33%; vertical-align: top; padding: 10px;">
                <div style="margin-bottom: 5px; font-size: 0.85rem;">Disetujui Oleh</div>
                <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">SECTION HEAD PRODUKSI</div>
                <?php if (isset($approvalData['approved_l2_by'])): ?>
                  <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Disetujui ]</div>
                <?php else: ?>
                  <div style="height: 20px; margin-bottom: 20px;"></div>
                <?php endif; ?>
                <div style="font-size: 0.9rem;">
                  <?= isset($approvalData['approved_l2_by']) ? '<span style="text-decoration: underline;">' . esc($approvalData['l2_name'] ?? 'Section Head') . '</span>' : '<span style="color: #999;">( ........................................ )</span>' ?>
                </div>
                <div style="font-size: 0.8rem; color: #555;">
                  Tanggal: <?= isset($approvalData['approved_l2_at']) ? format_tanggal_indo($approvalData['approved_l2_at'], false, true) : '( ......................... )' ?>
                </div>
              </td>
              <td style="border: 1.5pt solid #000; width: 33.33%; vertical-align: top; padding: 10px;">
                <div style="margin-bottom: 5px; font-size: 0.85rem;">Disetujui Oleh</div>
                <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">SECTION HEAD MTC</div>
                <?php if (isset($approvalData['approved_final_by'])): ?>
                  <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Disetujui ]</div>
                <?php else: ?>
                  <div style="height: 20px; margin-bottom: 20px;"></div>
                <?php endif; ?>
                <div style="font-size: 0.9rem;">
                  <?= isset($approvalData['approved_final_by']) ? '<span style="text-decoration: underline;">' . esc($approvalData['final_name'] ?? 'Section Head MTC') . '</span>' : '<span style="color: #999;">( ........................................ )</span>' ?>
                </div>
                <div style="font-size: 0.8rem; color: #555;">
                  Tanggal: <?= isset($approvalData['approved_final_at']) ? format_tanggal_indo($approvalData['approved_final_at'], false, true) : '( ......................... )' ?>
                </div>
              </td>
            </tr>
          </table>

        </td>
      </tr>
      <?php endif; ?>

      </tbody>
    <?php endforeach; ?>
  <?php endif; ?>
</table>
</div>
</body>
</html>





