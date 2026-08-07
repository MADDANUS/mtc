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
        <div style="width:40px; height:40px; border:2px double #0000ff; border-radius:50%; margin:0 auto; position:relative;">
          <div style="position:absolute; top:-9px; left:50%; transform:translateX(-50%); background:#fff; padding:0 3px; font-size:1rem; font-weight:normal; color:#0000ff;">NSI</div>
        </div>
        <div style="font-size:0.6rem; margin-top:4px; font-style:italic; color:#0070c0; line-height:1.3; text-align:center;">
          <div>The Future</div><div>In Our Hands</div>
        </div>
      </td>
      <td colspan="6" class="kop-table-title" style="padding:6px; font-size:13px;">CHECKLIST REPORT - <?= strtoupper(esc($header['kategori'] ?? 'MESIN CNC')) ?></td>
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
    <!-- ROW INFO 1: NO MACHINE | DATE | LOKASI -->
    <tr>
      <td style="font-weight:bold; width:16%; border-top:none;">NO MACHINE</td>
      <td style="width:17%; border-top:none;"><?= esc($header['no_mesin']) ?></td>
      <td style="font-weight:bold; width:16%; border-top:none;">DATE</td>
      <td style="width:17%; border-top:none;"><?= format_tanggal_indo(date('Y-m-d', $waktuMulai)) ?></td>
      <td style="font-weight:bold; width:16%; border-top:none;">LOKASI</td>
      <td style="border-top:none;"><?= esc($header['lokasi_check'] ?? '-') ?></td>
    </tr>
    <!-- ROW INFO 2: MACHINE TYPE | START TIME | DURASI -->
    <tr>
      <td style="font-weight:bold;">MACHINE TYPE</td>
      <td><?= esc($header['type_mesin']) ?></td>
      <td style="font-weight:bold;">START TIME</td>
      <td><?= date('H:i:s', $waktuMulai) ?></td>
      <td style="font-weight:bold;">DURASI</td>
      <td><?= $durasiStr ?></td>
    </tr>
    <!-- ROW INFO 3: SERIAL NUMBER | FINISH TIME -->
    <tr>
      <td style="font-weight:bold;">SERIAL NUMBER</td>
      <td><?= esc($header['serial_nomor'] ?? '-') ?></td>
      <td style="font-weight:bold;">FINISH TIME</td>
      <td><?= $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-' ?></td>
      <td colspan="2">&nbsp;</td>
    </tr>
  </table>

  <!-- KETERANGAN CHECK LIST - standalone agar tidak dipaksa full width -->
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

  <!-- TABEL ISI CHECKLIST REPORT: BAGIAN CHECK | POINT CHECK | STANDARD CHECK | HASIL | ULASAN -->
  <table style="margin-top:0;">
    <thead>
      <tr>
        <th style="width:25%; text-align:center; background-color:#f2f2f2;">BAGIAN CHECK</th>
        <th style="width:20%; text-align:center; background-color:#f2f2f2;">POINT CHECK</th>
        <th style="width:20%; text-align:center; background-color:#f2f2f2;">STANDARD CHECK</th>
        <th style="width:10%; text-align:center; background-color:#f2f2f2;">HASIL</th>
        <th style="width:25%; text-align:center; background-color:#f2f2f2;">ULASAN</th>
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
      <?php if ($tbody_opened) echo "</tbody>"; ?>
    </table>

  <?php if (!empty($header['note_recommendation'])): ?>
  <div style="margin-top:8px; border:1.5pt solid #000; padding:6px; background:#f8f9fa; font-size:11px;">
    <strong>NOTE AND RECOMMENDATION</strong><br>
    <span style="white-space:pre-wrap;"><?= esc($header['note_recommendation']) ?></span>
  </div>
  <?php endif; ?>

