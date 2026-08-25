<?php
  $rawNamaTop   = $header['nama_pic'] ?: $header['nama_staff'];
  $namaTopParts = explode(' - ', $rawNamaTop);
  $namaTopOnly  = end($namaTopParts);
  $waktuMulai   = strtotime($header['waktu_mulai']);
  $waktuSelesai = $header['waktu_selesai'] ? strtotime($header['waktu_selesai']) : null;
?>
<!-- KOP INSPECTION REPORT (2 tabel terpisah) -->
  <table style="margin-bottom:0 !important;">
    <tr>
      <td rowspan="4" style="width:15%; text-align:center; vertical-align:middle; padding:6px;">
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
      <td colspan="2" style="background-color:#92b0d6; text-align:center; font-weight:bold; font-size:13px; color:#000;" bgcolor="#92b0d6" style="padding:5px;">INSPECTION REPORT - <?= strtoupper(esc($header['kategori'] ?? 'MESIN CNC')) ?></td>
    </tr>
    <tr>
      <td style="width:45%; font-weight:bold; text-align:center;">NO. DOCUMENT</td>
      <td style="width:40%; font-weight:bold; text-align:center;">NO REVISI</td>
    </tr>
    <tr>
      <td style="text-align:center;">FM-MTN-10</td>
      <td style="text-align:center;">0</td>
    </tr>
    <tr>
      <td colspan="2" style="font-size:10px; padding:1px 5px; border-top:1.5pt solid #000; text-align:left;">Rev.:0/291124</td>
    </tr>
  </table>

  <table style="margin-top:0 !important; border-top:none;">
    <tr>
      <td style="width:19%; font-weight:bold; border-top:none;">MAIN PIC</td>
      <td style="border-top:none;"><?= esc($namaTopOnly) ?></td>
      <td style="width:19%; font-weight:bold; border-top:none;">NO MACHINE</td>
      <td style="border-top:none;"><?= esc($header['no_mesin']) ?></td>
      <td style="width:19%; font-weight:bold; border-top:none;">DATE</td>
      <td style="border-top:none;"><?= format_tanggal_indo(date('Y-m-d', $waktuMulai)) ?></td>
    </tr>
    <tr>
      <td style="font-weight:bold;" rowspan="2">SUPPORT PIC</td>
      <td rowspan="2" style="vertical-align:top;"><?= esc($header['support_pic'] ?? '-') ?></td>
      <td style="font-weight:bold;">MACHINE TYPE</td>
      <td><?= esc($header['type_mesin']) ?></td>
      <td style="font-weight:bold;">START TIME</td>
      <td><?= date('H:i:s', $waktuMulai) ?></td>
    </tr>
      <tr>
        <?php if (stripos($header['kategori'] ?? '', 'CNC') !== false): ?>
          <td style="font-weight:bold;">BAR FEEDER TYPE</td>
          <td><?= esc($header['bar_feeder_type'] ?? '-') ?></td>
        <?php else: ?>
          <td style="font-weight:bold;"></td>
          <td></td>
        <?php endif; ?>
        <td style="font-weight:bold;">FINISH TIME</td>
        <td><?= $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-' ?></td>
      </tr>
  </table>


  <!-- TABEL ISI INSPECTION REPORT -->
  <table style="margin-top:0;">
    <thead>
      <tr>
        <th style="width:5%; text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">NO</th>
        <th colspan="2" style="text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">ITEM CHECK</th>
        <th width="20%" style="width:20%; text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">POINT CHECK</th>
        <?php if (strtolower($header['departemen_check']) !== 'mfg 2'): ?>
        <th style="width:15%; text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">STANDAR ITEM</th>
        <?php endif; ?>
        <th width="10%" style="width:10%; text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">HASIL</th>
        <th width="20%" style="width:20%; text-align:center; background-color:#f2f2f2;" bgcolor="#f2f2f2">ULASAN</th>
      </tr>
    </thead>
    <?php $ov_tbody_opened = false; ?>
    <?php foreach ($details as $d): ?>
      <?php if (!empty($d['is_section_start']) || !empty($d['show_no'])): ?>
        <?php if ($ov_tbody_opened) echo "</tbody>"; ?>
        <tbody style="page-break-inside: avoid;">
        <?php $ov_tbody_opened = true; ?>
      <?php endif; ?>
        <?php if (!empty($d['is_section_start'])): ?>
          <tr>
            <?php $colSpan = (strtolower($header['departemen_check']) !== 'mfg 2') ? 7 : 6; ?>
            <td colspan="<?= $colSpan ?>" style="text-align:center; font-weight:bold; background-color:#f2f2f2;" bgcolor="#f2f2f2"><?= esc($d['dynamic_section_header'] ?? '') ?></td>
          </tr>
        <?php endif; ?>
        <tr>
          <?php if (!empty($d['show_no'])): ?>
            <td rowspan="<?= $d['no_rowspan'] ?? 1 ?>" style="text-align:center; font-weight:bold; vertical-align:middle;"><?= esc($d['dynamic_no'] ?? '') ?></td>
          <?php endif; ?>

          <?php if (!empty($d['sub_item_check'])): ?>
            <?php if (!empty($d['show_bagian'])): ?>
              <td rowspan="<?= $d['bagian_rowspan'] ?? 1 ?>" style="vertical-align:middle;"><?= esc($d['bagian_check']) ?></td>
            <?php endif; ?>
            <td style="vertical-align:middle;"><?= esc($d['sub_item_check']) ?></td>
          <?php else: ?>
            <td colspan="2" style="vertical-align:middle;"><?= esc($d['bagian_check']) ?></td>
          <?php endif; ?>

          <?php if (!empty($d['show_point'])): ?>
            <td rowspan="<?= $d['point_rowspan'] ?? 1 ?>" style="text-align:center; vertical-align:middle;"><?= esc($d['point_check'] ?? '') ?></td>
          <?php endif; ?>

          <?php if (strtolower($header['departemen_check']) !== 'mfg 2'): ?>
            <?php if (!empty($d['show_standard'])): ?>
              <td rowspan="<?= $d['standard_rowspan'] ?? 1 ?>" style="text-align:center; vertical-align:middle;"><?= nl2br(esc($d['standard_check'] ?? '')) ?></td>
            <?php endif; ?>
          <?php endif; ?>

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
            <?= esc($d['ulasan'] ?? '-') ?>
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
        <?php $bottomColSpan = (strtolower($header['departemen_check']) !== 'mfg 2') ? 7 : 6; ?>
        <td colspan="<?= $bottomColSpan ?>" style="border:none; padding:0;">
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

      <?php if ($ov_tbody_opened) echo "</tbody>"; ?>
    </table>
