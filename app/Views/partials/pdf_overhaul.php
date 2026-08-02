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
        <div style="width:40px; height:40px; border:2px double #0000ff; border-radius:50%; margin:0 auto; position:relative;">
          <div style="position:absolute; top:-9px; left:50%; transform:translateX(-50%); background:#fff; padding:0 3px; font-size:1rem; font-weight:normal; color:#0000ff;">NSI</div>
        </div>
        <div style="font-size:0.6rem; margin-top:4px; font-style:italic; color:#0070c0; line-height:1.3; text-align:center;">
          <div>The Future</div><div>In Our Hands</div>
        </div>
      </td>
      <td colspan="2" class="kop-table-title" style="padding:5px;">INSPECTION REPORT - <?= strtoupper(esc($header['kategori'] ?? 'MESIN CNC')) ?></td>
    </tr>
    <tr>
      <td style="width:45%; font-weight:bold; text-align:center;">NO. DOCUMENT</td>
      <td style="width:40%; font-weight:bold; text-align:center;">NO REVISI</td>
    </tr>
    <tr>
      <td style="text-align:center;">FM-MTN-11</td>
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
      <td style="border-top:none;"><?= date('Y-m-d', $waktuMulai) ?></td>
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
      <td style="font-weight:bold;">BAR FEEDER TYPE</td>
      <td><?= esc($header['bar_feeder_type'] ?? '-') ?></td>
      <td style="font-weight:bold;">FINISH TIME</td>
      <td><?= $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-' ?></td>
    </tr>
  </table>

  <!-- KETERANGAN CHECK LIST - standalone, tidak dipaksa full width -->
  <table class="keterangan-table">
    <tr>
      <td colspan="3" style="text-align:center; font-weight:bold; padding:2px 10px; background-color:#f2f2f2; font-size:8px;">KETERANGAN CHECK LIST</td>
    </tr>
    <tr>
      <td style="text-align:center; font-weight:bold; padding:2px 10px; font-size:8px;">V</td>
      <td style="text-align:center; font-weight:bold; padding:2px 5px; font-size:8px;">:</td>
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

  <!-- TABEL ISI INSPECTION REPORT -->
  <table style="margin-top:0;">
    <thead>
      <tr>
        <th style="width:5%; text-align:center; background-color:#f2f2f2;">NO</th>
        <th colspan="2" style="text-align:center; background-color:#f2f2f2;">ITEM CHECK</th>
        <th style="width:20%; text-align:center; background-color:#f2f2f2;">POINT CHECK</th>
        <?php if (strtolower($header['lokasi_check']) !== 'mfg 2'): ?>
        <th style="width:15%; text-align:center; background-color:#f2f2f2;">STANDAR ITEM</th>
        <?php endif; ?>
        <th style="width:10%; text-align:center; background-color:#f2f2f2;">HASIL</th>
        <th style="width:20%; text-align:center; background-color:#f2f2f2;">ULASAN</th>
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
            <?php $colSpan = strtolower($header['lokasi_check']) === 'mfg 2' ? 6 : 7; ?>
            <td colspan="<?= $colSpan ?>" style="text-align:center; font-weight:bold; background-color:#f2f2f2;"><?= esc($d['dynamic_section_header'] ?? '') ?></td>
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

          <?php if (strtolower($header['lokasi_check']) !== 'mfg 2'): ?>
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
      <?php if ($ov_tbody_opened) echo "</tbody>"; ?>
    </table>

  <?php if (!empty($header['note_recommendation'])): ?>
  <div style="margin-top:8px; border:1.5pt solid #000; padding:6px; background:#f8f9fa; font-size:11px;">
    <strong>NOTE AND RECOMMENDATION</strong><br>
    <span style="white-space:pre-wrap;"><?= esc($header['note_recommendation']) ?></span>
  </div>
  <?php endif; ?>

