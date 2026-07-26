<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PDF Export - Laporan Abnormal</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; margin: 0; padding: 0; }
    .pdf-container { padding: 10px 15px; width: 100%; }
    table { width: 100% !important; max-width: 100% !important; border-collapse: collapse !important; margin-bottom: 20px; table-layout: fixed !important; word-wrap: break-word; margin-left: 0 !important; margin-right: 0 !important; }
    th, td { border: 1px solid #000; padding: 4px; font-size: 11px; }
    .text-center { text-align: center; }
    .text-start { text-align: left; }
    .text-end { text-align: right; }
    .fw-bold { font-weight: bold; }
    .bg-light { background-color: #f8f9fa; }
    .page-title { text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 20px; text-transform: uppercase; }
  </style>
</head>
<body>
<div class="pdf-container">
<div class="page-title">
  LAPORAN ABNORMAL CONDITION<br>
  <span style="font-size: 12px; font-weight: normal;"><?= strtoupper($lokasiFilter) ?> - <?= strtoupper($kategoriFilter) ?></span>
</div>



      <table class="table align-middle text-center abnormal-table" style="font-size: 0.8rem; border-collapse: collapse;">
        <thead>
          <tr class="table-light" style="background-color: #f2f2f2;">
            <th rowspan="3" style="width: 3%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">NO</th>
            <th rowspan="3" style="width: 15%; font-weight:800; text-align: left; border-bottom: 2px solid #cbd5e1 !important;" class="ps-3">MESIN</th>
            <th rowspan="3" style="width: 12%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">POINT CHECK</th>
            <th rowspan="3" style="width: 15%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">ABNORMAL CONDITION</th>
            <th rowspan="3" style="width: 10%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">TYPE SPAREPART</th>
            <th colspan="2" style="width: 12%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PENGECEKAN</th>
            <th colspan="4" style="width: 25%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">RENCANA PERBAIKAN</th>
            <th rowspan="3" style="width: 8%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">KETERANGAN</th>
          </tr>
          <tr class="table-light" style="background-color: #f2f2f2;">
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">TANGGAL</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PIC</th>
            <th colspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PROGRES</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">ACTION</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PIC</th>
          </tr>
          <tr class="table-light" style="background-color: #f2f2f2;">
            <th style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">STOCK</th>
            <th style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">TANGGAL</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($reports)): ?>
            <tr>
              <td colspan="12" class="p-5 text-muted">
                <i class="bi bi-shield-check text-success" style="font-size: 2.5rem; display:block; margin-bottom:0.5rem;"></i>
                Tidak ada temuan kondisi abnormal yang tercatat.
              </td>
            </tr>
          <?php else: ?>
            <?php $no = 1; foreach ($reports as $r): ?>
              <?php 
                $canEdit = in_array(session()->get('role'), ['member', 'sheadprd', 'sheadmtc', 'admin'], true);
                $rowClass = $canEdit ? 'row-editable' : '';
              ?>
              <tr class="<?= $rowClass ?>" 
                  style="<?= $canEdit ? 'cursor: pointer;' : '' ?> transition: background-color 0.15s;"
                  data-id-abnormal="<?= $r['id_abnormal'] ?>"
                  data-mesin="<?= esc($r['no_mesin'] . ' - ' . $r['type_mesin'] . ' (' . $r['lokasi'] . ')') ?>"
                  data-point-check="<?= esc($r['point_check']) ?>"
                  data-abnormal-condition="<?= esc($r['abnormal_condition']) ?>"
                  data-type-sparepart="<?= esc($r['type_sparepart'] ?? '') ?>"
                  data-progres-stock="<?= esc($r['progres_stock'] ?? '') ?>"
                  data-progres-tanggal="<?= esc($r['progres_tanggal'] ?? '') ?>"
                  data-action="<?= esc($r['action'] ?? '') ?>"
                  data-repair-pic="<?= esc($r['repair_pic'] ?? '') ?>"
                  data-keterangan="<?= esc($r['keterangan'] ?? '') ?>">
                
                <td class="fw-bold font-monospace text-secondary" style="background-color: #f8fafc;"><?= $no++ ?></td>
                <td class="text-start fw-bold text-dark ps-3"><?= esc($r['no_mesin']) ?> - <?= esc($r['type_mesin']) ?></td>
                <td><?= esc($r['point_check']) ?></td>
                <td class="text-danger fw-semibold"><?= esc($r['abnormal_condition']) ?></td>
                <td><?= esc($r['type_sparepart']) ?: '<span class="text-muted small">-</span>' ?></td>
                
                <!-- Pengecekan -->
                <td class="font-monospace"><?= date('d-m-Y', strtotime($r['pengecekan_tanggal'])) ?></td>
                <td><span class="fw-semibold text-dark"><?= esc($r['pengecekan_pic']) ?></span></td>
                
                <!-- Rencana Perbaikan -->
                <td>
                  <?php if ($r['progres_stock'] === 'Ready'): ?>
                    <span class="badge bg-success">Ready</span>
                  <?php elseif ($r['progres_stock'] === 'Indent'): ?>
                    <span class="badge bg-warning text-dark">Indent</span>
                  <?php elseif ($r['progres_stock'] === 'Not Available'): ?>
                    <span class="badge bg-danger">Not Available</span>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td class="font-monospace"><?= $r['progres_tanggal'] ? date('d-m-Y', strtotime($r['progres_tanggal'])) : '<span class="text-muted">-</span>' ?></td>
                <td class="text-start"><?= esc($r['action']) ?: '<span class="text-muted">-</span>' ?></td>
                <td><span class="fw-semibold text-dark"><?= esc($r['repair_pic']) ?: '<span class="text-muted">-</span>' ?></span></td>
                
                <td><?= esc($r['keterangan']) ?: '<span class="text-muted">-</span>' ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>






<table style="width: 100%; border: none; text-align: center; margin-top: 30px;">
  <tr>
    <td style="border: none; width: 50%; vertical-align: top;">
      <div style="margin-bottom: 5px; font-size: 0.85rem;">Dibuat Oleh</div>
      <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">PIC</div>
      <div style="height: 20px; margin-bottom: 20px;"></div>
      <div style="font-weight: bold; text-decoration: underline; font-size: 0.9rem;">
        ( ........................................ )
      </div>
    </td>
    <td style="border: none; width: 50%; vertical-align: top;">
      <div style="margin-bottom: 5px; font-size: 0.85rem;">Disetujui Oleh</div>
      <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">LEADER / SECTION HEAD</div>
      <div style="height: 20px; margin-bottom: 20px;"></div>
      <div style="font-weight: bold; text-decoration: underline; font-size: 0.9rem;">
        ( ........................................ )
      </div>
    </td>
  </tr>
</table>
</div>
</body>
</html>



