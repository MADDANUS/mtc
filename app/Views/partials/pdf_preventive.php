<?php
  $waktuMulai   = strtotime($header['waktu_mulai']);
  $waktuSelesai = $header['waktu_selesai'] ? strtotime($header['waktu_selesai']) : null;
?>
<?php
  /* Hitung durasi */
  $durasiStr = '-';
  if ($waktuSelesai && $waktuMulai) {
    $selisih   = $waktuSelesai - $waktuMulai;
    $durasiStr = sprintf('%02d:%02d:%02d', floor($selisih/3600), floor(($selisih%3600)/60), $selisih%60);
  }
?>

  <!-- KOP CHECKLIST REPORT persis sesuai referensi -->
  <table style="margin-bottom:0 !important;">
    <!-- ROW 1: Logo rowspan=4 + Judul -->
    <tr>
      <td rowspan="4" style="width:13%; text-align:center; vertical-align:middle; padding:6px;">
                <?php 
          $logoPath = FCPATH . 'uploads/nsi_logo.png';
          if (file_exists($logoPath)) {
              $logoData = base64_encode(file_get_contents($logoPath));
              echo '<img src="data:image/png;base64,' . $logoData . '" style="max-width: 80px; max-height: 80px; display: block; margin: 0 auto;">';
          } else {
              echo '<div style="font-weight:bold; color:blue; font-size:24px;">NSI</div>';
          }
        ?>
        
      </td>
      <td colspan="6" style="background-color:#92b0d6; text-align:center; font-weight:bold; font-size:13px; color:#000;" bgcolor="#92b0d6" style="padding:6px; font-size:13px;">CHECKLIST REPORT - <?= strtoupper(esc($header['kategori'] ?? 'MESIN CNC')) ?> (<?= strtoupper(esc($header['departemen_check'] ?? '-')) ?>)</td>
    </tr>
    <!-- ROW 2: Label dokumen -->
    <tr>
      <td colspan="3" style="font-weight:bold; text-align:center; width:45%;">NO. DOCUMENT</td>
      <td colspan="3" style="font-weight:bold; text-align:center; width:40%;">NO REVISI</td>
    </tr>
    <!-- ROW 3: Nilai dokumen -->
    <tr>
      <td colspan="3" style="text-align:center;">FM-MTN-10</td>
      <td colspan="3" style="text-align:center;">0</td>
    </tr>
    <!-- ROW 4: Rev -->
    <tr>
      <td colspan="6" style="font-size:10px; padding:2px 5px; border-top:none; text-align:left;">Rev.:0/291124</td>
    </tr>
  </table>

  <!-- KOP INFO ROWS (Table terpisah agar jumlah kolom tidak berbenturan dengan logo) -->
  <table style="margin-top:0 !important; border-top:none;">
    <!-- ROW INFO 1: DATE | MACHINE TYPE | START TIME -->
    <tr>
      <td style="font-weight:bold; width:16%; border-top:none;">DATE</td>
      <td style="width:17%; border-top:none;"><?= format_tanggal_indo(date('Y-m-d', $waktuMulai)) ?></td>
      <td style="font-weight:bold; width:16%; border-top:none;">MACHINE TYPE</td>
      <td style="width:17%; border-top:none;"><?= esc($header['type_mesin']) ?></td>
      <td style="font-weight:bold; width:16%; border-top:none;">START TIME</td>
      <td style="border-top:none;"><?= date('H:i:s', $waktuMulai) ?></td>
    </tr>
    <!-- ROW INFO 2: NO MACHINE | SERIAL NUMBER | FINISH TIME -->
    <tr>
      <td style="font-weight:bold;">NO MACHINE</td>
      <td><?= esc($header['no_mesin']) ?></td>
      <td style="font-weight:bold;">SERIAL NUMBER</td>
      <td><?= esc($header['serial_nomor'] ?? '-') ?></td>
      <td style="font-weight:bold;">FINISH TIME</td>
      <td><?= $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-' ?></td>
    </tr>
  </table>


  <!-- TABEL ISI CHECKLIST REPORT: BAGIAN CHECK | POINT CHECK | STANDARD CHECK | HASIL | ULASAN -->
  <table style="margin-top:0;">
    <thead>
      <tr>
        <th width="25%" style="width:25%; text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">BAGIAN CHECK</th>
        <th width="20%" style="width:20%; text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">POINT CHECK</th>
        <th width="20%" style="width:20%; text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">STANDARD CHECK</th>
        <th width="10%" style="width:10%; text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">HASIL</th>
        <th width="25%" style="width:25%; text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">ULASAN</th>
      </tr>
    </thead>
      <?php $tbody_opened = false; ?>
      <?php foreach ($details as $d): ?>
        <?php if (!empty($d['show_bagian'])): ?>
          <?php if ($tbody_opened) echo "</tbody>"; ?>
          <tbody style="page-break-inside: avoid;">
          <?php $tbody_opened = true; ?>
        <?php endif; ?>
        <tr>
          <?php if (!empty($d['show_bagian'])): ?>
            <td rowspan="<?= $d['bagian_rowspan'] ?? 1 ?>" style="vertical-align:top;"><?= esc($d['bagian_check'] ?? '') ?></td>
          <?php endif; ?>

          <td style="text-align:center; vertical-align:middle;"><?= esc($d['point_check'] ?? '') ?></td>
          <td style="text-align:center;"><?= esc($d['standard_check'] ?? '') ?></td>

          <td style="text-align:center;">
            <?php if (($d['hasil_check'] ?? '') === 'V'): ?>
              <strong style="color:green;">V</strong>
            <?php elseif (($d['hasil_check'] ?? '') === 'Δ'): ?>
              <strong style="color:#b8860b;">Δ</strong>
            <?php elseif (($d['hasil_check'] ?? '') === 'X'): ?>
              <strong style="color:red;">X</strong>
            <?php else: ?>
              <span style="color:#aaa;">-</span>
            <?php endif; ?>
          </td>
          <td>
            <?= esc($d['ulasan'] ?? '') ?>
            <?php 
              if (!empty($d['foto_abnormal'])):
                $imgPath = FCPATH . 'uploads/abnormal/' . $d['foto_abnormal'];
                if (file_exists($imgPath)):
                  $type = pathinfo($imgPath, PATHINFO_EXTENSION);
                  $base64 = base64_encode(file_get_contents($imgPath));
                  $src = 'data:image/' . $type . ';base64,' . $base64;
            ?>
              <br><img src="<?= $src ?>" style="max-height: 80px; margin-top: 5px; border: 1px solid #ccc;">
            <?php endif; endif; ?>

            <?php 
              if (!empty($d['foto_abnormal_2'])):
                $imgPath = FCPATH . 'uploads/abnormal/' . $d['foto_abnormal_2'];
                if (file_exists($imgPath)):
                  $type = pathinfo($imgPath, PATHINFO_EXTENSION);
                  $base64 = base64_encode(file_get_contents($imgPath));
                  $src = 'data:image/' . $type . ';base64,' . $base64;
            ?>
              <br><img src="<?= $src ?>" style="max-height: 80px; margin-top: 5px; border: 1px solid #ccc;">
            <?php endif; endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      
      <!-- APPEND BOTTOM ELEMENTS IN THE LAST TBODY -->
      <tr style="page-break-inside: avoid; page-break-before: avoid;">
        <td colspan="5" style="border:none; padding:0;">
          <?php if (!empty($header['note_recommendation'])): ?>
          <div style="margin-top:8px; border:1.5pt solid #000; padding:6px; background:#f8f9fa; font-size:11px; text-align:left;">
            <strong>NOTE AND RECOMMENDATION</strong><br>
            <span style="white-space:pre-wrap;"><?= esc($header['note_recommendation']) ?></span>
          </div>
          <?php endif; ?>

          <table align="right" class="keterangan-table" style="margin-top:8px; margin-left:auto; width:200px;">
            <tr>
              <td colspan="3" style="text-align:center; font-weight:bold; padding:2px 10px; background-color:#f2f2f2; font-size:8px;">KETERANGAN CHECK LIST</td>
            </tr>
            <tr>
              <td style="text-align:center; font-weight:bold; padding:2px 10px; font-size:8px; width:20px;">V</td>
              <td style="text-align:center; font-weight:bold; padding:2px 5px; font-size:8px; width:10px;">:</td>
              <td style="text-align:left; font-weight:bold; padding:2px 10px; font-size:8px;">OK</td>
            </tr>
            <tr>
              <td style="text-align:center; font-weight:bold; padding:2px 10px; font-size:8px;">&#916;</td>
              <td style="text-align:center; font-weight:bold; padding:2px 5px; font-size:8px;">:</td>
              <td style="text-align:left; font-weight:bold; padding:2px 10px; font-size:8px;">PERLU TINDAKAN</td>
            </tr>
            <tr>
              <td style="text-align:center; font-weight:bold; padding:2px 10px; font-size:8px;">X</td>
              <td style="text-align:center; font-weight:bold; padding:2px 5px; font-size:8px;">:</td>
              <td style="text-align:left; font-weight:bold; padding:2px 10px; font-size:8px;">TIDAK ADA</td>
            </tr>
          </table>

          <?= view('partials/pdf_signature', ['header' => $header]) ?>
        </td>
      </tr>

      <?php if ($tbody_opened) echo "</tbody>"; ?>
    </table>
