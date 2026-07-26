
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PDF Export - <?= esc($title) ?></title>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'DejaVu Sans', sans-serif;
      font-size: 11px;
      margin: 0;
      padding: 0;
    }
    .pdf-container {
      padding: 10px 15px;
      width: 100%;
    }
    table {
      width: 100% !important;
      max-width: 100% !important;
      border-collapse: collapse !important;
      margin-bottom: 20px;
      table-layout: fixed !important;
      word-wrap: break-word;
      margin-left: 0 !important;
      margin-right: 0 !important;
    }
    th, td {
      border: 1px solid #000;
      padding: 4px;
      font-size: 11px;
    }
    .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: #fff; }
    .kop-table th, .kop-table td { border: 1px solid #000; padding: 6px 10px; vertical-align: middle; }
    .kop-table-title { background-color: #92b0d6; text-align: center; font-weight: bold; font-size: 14px; letter-spacing: 1px; color: #000; }
    .kop-logo { text-align: center; width: 12%; font-weight: bold; }
    .kop-label { font-weight: bold; font-size: 11px; width: 14%; }
    .kop-val { width: 15%; }
    
    .text-center { text-align: center; }
    .text-start { text-align: left; }
    .text-end { text-align: right; }
    .fw-bold { font-weight: bold; }
    .bg-light { background-color: #f8f9fa; }
  </style>
</head>
<body>
<div class="pdf-container">




<?php 
  $rawNamaTop = $header['nama_pic'] ?: $header['nama_staff'];
  $namaTopParts = explode(' - ', $rawNamaTop);
  $namaTopOnly = end($namaTopParts);
  $waktuMulai = strtotime($header['waktu_mulai']);
  $waktuSelesai = $header['waktu_selesai'] ? strtotime($header['waktu_selesai']) : null;
?>

<?php if (strtolower($header['jenis_check']) === 'overhaul'): ?>
  <table class="kop-table text-center" style="margin-bottom: 0 !important;">
    <tr>
      <td rowspan="3" class="kop-logo" style="width: 15%; padding-top: 15px;">
        <div style="width: 60px; height: 60px; border: 3px double #0000ff; border-radius: 50%; margin: 0 auto; position: relative;">
          <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #fff; padding: 0 4px; font-size: 1.5rem; font-weight: normal; color: #0000ff;">NSI</div>
        </div>
        <div style="font-size: 0.65rem; margin-top: 8px; font-style: italic; color: #0070c0;">
          <div style="margin-bottom: 2px;">The Future</div>
          <div>In Our Hands</div>
        </div>
      </td>
      <td colspan="3" class="kop-table-title" style="padding: 10px;">CHECKLIST REPORT - <?= strtoupper(esc($header['kategori'] ?? 'MESIN CNC')) ?></td>
    </tr>
    <tr>
      <td class="fw-bold" style="width: 35%;">NO. DOCUMENT</td>
      <td class="fw-bold" style="width: 25%;">NO REVISI</td>
      <td class="fw-bold" style="width: 25%;">HALAMAN</td>
    </tr>
    <tr>
      <td>FM-MTN-11</td>
      <td>0</td>
      <td>1 DARI 1</td>
    </tr>
    <tr>
      <td colspan="4" class="text-start" style="font-size:0.75rem; padding: 2px 5px; border-top: 1px solid #000;">Rev.:0/291124</td>
    </tr>
  </table>

  <table class="kop-table text-center" style="margin-top: 0 !important; border-top: none;">
    <tr>
      <td class="kop-label text-start" style="width:19%; border-top: none;">MAIN PIC</td>
      <td class="kop-val text-start" style="border-top: none;"><?= esc($namaTopOnly) ?></td>
      <td class="kop-label text-start" style="width:19%; border-top: none;">NO MACHINE</td>
      <td class="kop-val text-start" style="border-top: none;"><?= esc($header['no_mesin']) ?></td>
      <td class="kop-label text-start" style="width:19%; border-top: none;">DATE</td>
      <td class="kop-val text-start" style="border-top: none;"><?= date('Y-m-d', $waktuMulai) ?></td>
    </tr>
    <tr>
      <td class="kop-label text-start" rowspan="2">SUPPORT PIC</td>
      <td class="kop-val text-start" rowspan="2" style="vertical-align: top;"><?= esc($header['support_pic'] ?? '-') ?></td>
      <td class="kop-label text-start">MACHINE TYPE</td>
      <td class="kop-val text-start"><?= esc($header['type_mesin']) ?></td>
      <td class="kop-label text-start">START TIME</td>
      <td class="kop-val text-start"><?= date('H:i:s', $waktuMulai) ?></td>
    </tr>
    <tr>
      <td class="kop-label text-start">BAR FEEDER TYPE</td>
      <td class="kop-val text-start"><?= esc($header['bar_feeder_type'] ?? '-') ?></td>
      <td class="kop-label text-start">FINISH TIME</td>
      <td class="kop-val text-start"><?= $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-' ?></td>
    </tr>
  </table>

  
  </div>

<?php else: ?>
  <table class="kop-table text-center" style="margin-bottom: 0 !important;">
    <tr>
      <td rowspan="3" class="kop-logo" style="width: 15%; padding-top: 15px;">
        <div style="width: 60px; height: 60px; border: 3px double #0000ff; border-radius: 50%; margin: 0 auto; position: relative;">
          <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #fff; padding: 0 4px; font-size: 1.5rem; font-weight: normal; color: #0000ff;">NSI</div>
        </div>
        <div style="font-size: 0.65rem; margin-top: 8px; font-style: italic; color: #0070c0;">
          <div style="margin-bottom: 2px;">The Future</div>
          <div>In Our Hands</div>
        </div>
      </td>
      <td colspan="3" class="kop-table-title" style="padding: 10px;">CHECKLIST REPORT - <?= strtoupper(esc($header['kategori'] ?? 'MESIN CNC')) ?></td>
    </tr>
    <tr>
      <td class="fw-bold" style="width: 35%;">NO. DOCUMENT</td>
      <td class="fw-bold" style="width: 25%;">NO REVISI</td>
      <td class="fw-bold" style="width: 25%;">HALAMAN</td>
    </tr>
    <tr>
      <td>FM-MTN-10</td>
      <td>0</td>
      <td>1 DARI 1</td>
    </tr>
    <tr>
      <td colspan="4" class="text-start" style="font-size:0.75rem; padding: 2px 5px; border-top: 1px solid #000;">Rev.:0/291124</td>
    </tr>
  </table>

  <table class="kop-table text-center" style="margin-top: 0 !important; border-top: none;">
    <tr>
      <td class="kop-label text-start" style="width:19%; border-top: none;">NO MACHINE</td>
      <td class="kop-val text-start" style="border-top: none;"><?= esc($header['no_mesin']) ?></td>
      <td class="kop-label text-start" style="width:19%; border-top: none;">DATE</td>
      <td class="kop-val text-start" style="border-top: none;"><?= date('Y-m-d', $waktuMulai) ?></td>
      <td class="kop-label text-start" style="width:19%; border-top: none;">LOKASI</td>
      <td class="kop-val text-start" style="border-top: none;"><?= esc($header['lokasi_check']) ?></td>
    </tr>
    <tr>
      <td class="kop-label text-start">MACHINE TYPE</td>
      <td class="kop-val text-start"><?= esc($header['type_mesin']) ?></td>
      <td class="kop-label text-start">START TIME</td>
      <td class="kop-val text-start"><?= date('H:i:s', $waktuMulai) ?></td>
      <td class="kop-label text-start">DURASI</td>
      <td class="kop-val text-start"><?= $durasiDetik !== null ? gmdate('H:i:s', $durasiDetik) : '-' ?></td>
    </tr>
    <tr>
      <td class="kop-label text-start">SERIAL NUMBER</td>
      <td class="kop-val text-start"><?= esc($header['serial_nomor'] ?? '-') ?></td>
      <td class="kop-label text-start">FINISH TIME</td>
      <td class="kop-val text-start" colspan="3"><?= $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-' ?></td>
    </tr>
  </table>

  
<?php endif; ?>

<?php 
  $status = $header['status'] ?? 'Pending';
  $role = session()->get('role');
?>




  <?php if (strtolower($header['jenis_check']) === 'overhaul'): ?>
    <!-- OVERHAUL DETAIL TABLE -->
    <table class="table table-bordered align-middle checklist-table bg-white">
      <thead>
        <tr>
          <th style="width:5%;">NO</th>
          <th colspan="2" style="width:30%;">ITEM CHECK</th>
          <th style="width:20%;">POINT CHECK</th>
          <?php if (strtolower($header['lokasi_check']) !== 'mfg 2'): ?>
          <th style="width:15%;">STANDAR ITEM</th>
          <?php endif; ?>
          <th style="width:10%;">HASIL</th>
          <th style="width:20%;">ULASAN</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($details as $d): ?>
          <?php if ($d['is_section_start']): ?>
            <tr class="section-header">
              <?php $colSpan = strtolower($header['lokasi_check']) === 'mfg 2' ? 6 : 7; ?>
              <td colspan="<?= $colSpan ?>"><?= esc($d['dynamic_section_header']) ?></td>
            </tr>
          <?php endif; ?>
          <tr>
            <?php if ($d['show_no']): ?>
              <td class="text-center fw-semibold text-muted" rowspan="<?= (int) $d['no_rowspan'] ?>"><?= esc($d['dynamic_no']) ?></td>
            <?php endif; ?>

            <?php if ($d['sub_item_check'] !== null && $d['sub_item_check'] !== ''): ?>
              <?php if ($d['show_bagian']): ?>
                <td class="bagian-cell" rowspan="<?= (int) $d['bagian_rowspan'] ?>"><?= esc($d['bagian_check']) ?></td>
              <?php endif; ?>
              <td><?= esc($d['sub_item_check']) ?></td>
            <?php else: ?>
              <td class="bagian-cell" colspan="2"><?= esc($d['bagian_check']) ?></td>
            <?php endif; ?>

            <?php if ($d['show_point']): ?>
              <td rowspan="<?= (int) $d['point_rowspan'] ?>"><?= esc($d['point_check']) ?></td>
            <?php endif; ?>

            <?php if (strtolower($header['lokasi_check']) !== 'mfg 2'): ?>
            <?php if ($d['show_standard']): ?>
              <td rowspan="<?= (int) $d['standard_rowspan'] ?>"><?= nl2br(esc($d['standard_check'])) ?></td>
            <?php endif; ?>
            <?php endif; ?>

            <td class="text-center">
              <?php if ($d['hasil_check'] === 'V'): ?>
                <span class="text-success fw-bold">V</span>
              <?php elseif ($d['hasil_check'] === 'Δ'): ?>
                <span class="text-warning fw-bold">Δ</span>
              <?php elseif ($d['hasil_check'] === 'X'): ?>
                <span class="text-danger fw-bold">X</span>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td><?= esc($d['ulasan'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    
    <div class="mt-4 border rounded p-3 bg-light shadow-sm">
      <label class="form-label fw-bold text-secondary mb-2" style="letter-spacing: 0.5px;">NOTE AND RECOMMENDATION</label>
      <p class="mb-0" style="white-space: pre-wrap;"><?= !empty($header['note_recommendation']) ? esc($header['note_recommendation']) : '-' ?></p>
    </div>
  <?php else: ?>
    <!-- PREVENTIVE DETAIL TABLE -->
    <table class="table table-bordered align-middle checklist-table bg-white">
      <thead>
        <tr>
          <th>BAGIAN CHECK</th>
          <th>POINT CHECK</th>
          <th>STANDARD CHECK</th>
          <th style="width:10%;">HASIL</th>
          <th>ULASAN</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($details as $d): ?>
          <tr>
            <?php if ($d['show_bagian']): ?>
              <td class="bagian-cell" rowspan="<?= (int) $d['bagian_rowspan'] ?>"><?= esc($d['bagian_check']) ?></td>
            <?php endif; ?>

            <?php if ($d['show_point']): ?>
              <td rowspan="<?= (int) $d['point_rowspan'] ?>"><?= esc($d['point_check']) ?></td>
            <?php endif; ?>

            <td><?= esc($d['standard_check']) ?></td>
            <td class="text-center">
              <?php if ($d['hasil_check'] === 'V'): ?>
                <span class="text-success fw-bold">V</span>
              <?php elseif ($d['hasil_check'] === 'Δ'): ?>
                <span class="text-warning fw-bold">Δ</span>
              <?php elseif ($d['hasil_check'] === 'X'): ?>
                <span class="text-danger fw-bold">X</span>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td><?= esc($d['ulasan'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- KOTAK TANDA TANGAN (SIGNATURE BLOCK) -->
<div style="margin-top: 20px;">
    <?php
      $isOverhaul = (strtolower(str_replace(' ', '-', $header['jenis_check'])) === 'overhaul');
    ?>

    <?php if ($isOverhaul): ?>
    <?php 
      $rawNamaOv = $header['nama_pic'] ?: ($header['nama_staff'] ?? 'MEMBER');
      $namaOvParts = explode(' - ', $rawNamaOv);
      $namaOvOnly = end($namaOvParts);
    ?>
    <table style="width: 100%; border: none; text-align: center; margin-top: 20px;">
      <tr>
        <td style="border: none; width: 25%; vertical-align: top;">
          <div style="margin-bottom: 5px; font-size: 0.85rem;">Prepared</div>
          <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">INSPECTOR</div>
          <?php if (!empty($header['waktu_selesai'])): ?>
            <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Selesai ]</div>
          <?php else: ?>
            <div style="height: 20px; margin-bottom: 20px;"></div>
          <?php endif; ?>
          <div style="font-weight: bold; text-decoration: underline; font-size: 0.9rem;">
            <?= esc($namaOvOnly) ?>
          </div>
          <div style="font-size: 0.8rem; color: #555;">
            Tgl: <?= !empty($header['waktu_selesai']) ? date('d-m-Y H:i', strtotime($header['waktu_selesai'])) : '-' ?>
          </div>
        </td>
        <td style="border: none; width: 25%; vertical-align: top;">
          <div style="margin-bottom: 5px; font-size: 0.85rem;">Checked</div>
          <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">LEADER PRODUKSI</div>
          <?php if (!empty($header['approval_l1_by'])): ?>
            <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Diperiksa ]</div>
          <?php else: ?>
            <div style="height: 20px; margin-bottom: 20px;"></div>
          <?php endif; ?>
          <div style="font-weight: bold; font-size: 0.9rem;">
            <?php if (!empty($header['approval_l1_by'])): ?>
              <span style="text-decoration: underline;"><?= esc($header['leader_nama'] ?? $header['approver_l1_nama']) ?></span>
            <?php else: ?>
              <span style="color: #999;">( .................................. )</span>
            <?php endif; ?>
          </div>
          <div style="font-size: 0.8rem; color: #555;">
            Tgl: <?= !empty($header['approval_l1_at']) ? date('d-m-Y H:i', strtotime($header['approval_l1_at'])) : '( ..................... )' ?>
          </div>
        </td>
        <td style="border: none; width: 25%; vertical-align: top;">
          <div style="margin-bottom: 5px; font-size: 0.85rem;">Approved</div>
          <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">SECTION HEAD PRODUKSI</div>
          <?php if (!empty($header['approval_l2_by'])): ?>
            <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Disetujui ]</div>
          <?php else: ?>
            <div style="height: 20px; margin-bottom: 20px;"></div>
          <?php endif; ?>
          <div style="font-weight: bold; font-size: 0.9rem;">
            <?php if (!empty($header['approval_l2_by'])): ?>
              <span style="text-decoration: underline;"><?= esc($header['approver_l2_nama']) ?></span>
            <?php else: ?>
              <span style="color: #999;">( Mr. Rohmad )</span>
            <?php endif; ?>
          </div>
          <div style="font-size: 0.8rem; color: #555;">
            Tgl: <?= !empty($header['approval_l2_at']) ? date('d-m-Y H:i', strtotime($header['approval_l2_at'])) : '( ..................... )' ?>
          </div>
        </td>
        <td style="border: none; width: 25%; vertical-align: top;">
          <div style="margin-bottom: 5px; font-size: 0.85rem;">Approved</div>
          <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">SECTION HEAD MTC</div>
          <?php if ($header['status'] === 'Approved'): ?>
            <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Disetujui ]</div>
          <?php else: ?>
            <div style="height: 20px; margin-bottom: 20px;"></div>
          <?php endif; ?>
          <div style="font-weight: bold; font-size: 0.9rem;">
            <?php if ($header['status'] === 'Approved'): ?>
              <span style="text-decoration: underline;"><?= esc($header['approver_nama']) ?></span>
            <?php else: ?>
              <span style="color: #999;">( Mr. Royadi )</span>
            <?php endif; ?>
          </div>
          <div style="font-size: 0.8rem; color: #555;">
            Tgl: <?= ($header['status'] === 'Approved') ? date('d-m-Y H:i', strtotime($header['approved_at'])) : '( ..................... )' ?>
          </div>
        </td>
      </tr>
    </table>
    
    <?php else: ?>
    <!-- SIGNATURE BLOCK PREVENTIVE (SINGLE-LEVEL) -->
    <?php 
      $rawNamaPic = $header['nama_pic'] ?: ($header['nama_staff'] ?? 'MEMBER');
      $namaPicParts = explode(' - ', $rawNamaPic);
      $namaPicOnly = end($namaPicParts);
    ?>
    <table style="width: 100%; border: none; text-align: center; margin-top: 20px;">
      <tr>
        <td style="border: none; width: 50%; vertical-align: top;">
          <div style="margin-bottom: 5px; font-size: 0.85rem;">Dibuat Oleh</div>
          <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">PIC</div>
          <?php if (!empty($header['waktu_selesai'])): ?>
            <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Selesai ]</div>
          <?php else: ?>
            <div style="height: 20px; margin-bottom: 20px;"></div>
          <?php endif; ?>
          <div style="font-weight: bold; text-decoration: underline; font-size: 0.9rem;">
            <?= esc($namaPicOnly) ?>
          </div>
          <div style="font-size: 0.8rem; color: #555;">
            Tgl: <?= !empty($header['waktu_selesai']) ? date('d-m-Y H:i', strtotime($header['waktu_selesai'])) : '-' ?>
          </div>
        </td>
        <td style="border: none; width: 50%; vertical-align: top;">
          <div style="margin-bottom: 5px; font-size: 0.85rem;">Disetujui Oleh</div>
          <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">PIC LINE</div>
          <?php if ($header['status'] === 'Approved'): ?>
            <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Disetujui ]</div>
          <?php else: ?>
            <div style="height: 20px; margin-bottom: 20px;"></div>
          <?php endif; ?>
          <div style="font-weight: bold; font-size: 0.9rem;">
            <?php if ($header['status'] === 'Approved'): ?>
              <span style="text-decoration: underline;"><?= esc($header['pic_line_nama'] ?? $header['approver_nama']) ?></span>
            <?php else: ?>
              <span style="color: #999;">( ........................................ )</span>
            <?php endif; ?>
          </div>
          <div style="font-size: 0.8rem; color: #555;">
            Tgl: <?= ($header['status'] === 'Approved') ? date('d-m-Y H:i', strtotime($header['approved_at'])) : '( ..................... )' ?>
          </div>
        </td>
      </tr>
    </table>
    <?php endif; ?>


</div>
</body>
</html>

