<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PDF Export - Abnormal Report</title>
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; margin: 0; padding: 0; }
    .pdf-container { padding: 10px 15px; width: 100%; }
    table { width: 100% !important; max-width: 100% !important; border-collapse: collapse !important; margin-bottom: 20px; table-layout: fixed !important; word-wrap: break-word; margin-left: 0 !important; margin-right: 0 !important; }
    th, td { border: 1.5pt solid #000; padding: 4px; font-size: 11px; }
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
      <?php
        $bulanIndo = [
            '01' => 'JANUARI', '02' => 'FEBRUARI', '03' => 'MARET',
            '04' => 'APRIL', '05' => 'MEI', '06' => 'JUNI',
            '07' => 'JULI', '08' => 'AGUSTUS', '09' => 'SEPTEMBER',
            '10' => 'OKTOBER', '11' => 'NOVEMBER', '12' => 'DESEMBER'
        ];
        $bulanVal = substr($bulanFilter, 5, 2);
        $bulanNama = isset($bulanIndo[$bulanVal]) ? $bulanIndo[$bulanVal] : '';
      ?>

<?php $totalReports = count($allReportsData); $currentIndex = 0; ?>
<?php foreach ($allReportsData as $item): ?>
  <?php 
    $kategoriFilter = $item['kategori'];
    $itemLokasi = $item['lokasi'] ?? $lokasiFilter;
    $reports = $item['reports'];
    $currentIndex++;
  ?>
      <table class="table align-middle text-center abnormal-table" style="font-size: 0.8rem; border-collapse: collapse; table-layout: fixed; width: 100%; word-wrap: break-word;">
        <thead>
          <tr style="line-height: 0; height: 0;">
            <td style="width: 40px; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 13%; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 11%; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 17%; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 8%; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 8%; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 6%; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 5%; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 8%; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 7%; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 7%; border: none; padding: 0; margin: 0; height: 0;"></td>
            <td style="width: 8%; border: none; padding: 0; margin: 0; height: 0;"></td>
          </tr>
          <tr style="background-color: #f7e600;">
            <th colspan="12" style="text-align: center; border: 1.5pt solid #000; padding: 10px;">
              <div style="font-size: 16px; font-style: italic; font-weight: bold;">FORMULIR ABNORMAL REPORT CONDITION</div>
              <div style="font-size: 16px; font-style: italic; font-weight: bold;">PREVENTIVE MAINTENANCE</div>
            </th>
          </tr>
          <tr style="background-color: #f2f2f2;">
            <th colspan="7" style="text-align: left; border: 1.5pt solid #000; padding: 4px; font-style: italic; font-size: 11px;">
              AREA : <?= strtoupper($itemLokasi) ?> | JENIS PREVENTIVE : <?= strtoupper($kategoriFilter) ?> | BULAN <?= $bulanNama ?>
            </th>
            <th colspan="4" style="text-align: right; border: 1.5pt solid #000; padding: 4px; font-style: italic; font-size: 11px;">
              Rev.:0/2911/24
            </th>
            <th colspan="1" style="text-align: right; border: 1.5pt solid #000; padding: 4px; font-style: italic; font-size: 11px;">
              FM-MTN-08
            </th>
          </tr>
          <tr class="table-light" style="background-color: #f2f2f2;">
            <th rowspan="3" style="width: 40px; white-space: nowrap; font-weight:800; border: 1.5pt solid #000;">NO</th>
            <th rowspan="3" style="width: 13%; font-weight:800; text-align: left; border: 1.5pt solid #000;" class="ps-3">MESIN</th>
            <th rowspan="3" style="width: 11%; font-weight:800; border: 1.5pt solid #000;">POINT CHECK</th>
            <th rowspan="3" style="width: 17%; font-weight:800; border: 1.5pt solid #000;">ABNORMAL CONDITION</th>
            <th rowspan="3" style="width: 8%; font-weight:800; border: 1.5pt solid #000;">TYPE SPAREPART</th>
            <th colspan="2" style="width: 14%; font-weight:800; border: 1.5pt solid #000;">PENGECEKAN</th>
            <th colspan="4" style="width: 27%; font-weight:800; border: 1.5pt solid #000;">RENCANA PERBAIKAN</th>
            <th rowspan="3" style="width: 8%; font-weight:800; border: 1.5pt solid #000; white-space: nowrap;">KETERANGAN</th>
          </tr>
          <tr class="table-light" style="background-color: #f2f2f2;">
            <th rowspan="2" style="font-weight:800; border: 1.5pt solid #000;">TANGGAL</th>
            <th rowspan="2" style="font-weight:800; border: 1.5pt solid #000;">PIC</th>
            <th colspan="2" style="font-weight:800; border: 1.5pt solid #000;">PROGRES</th>
            <th rowspan="2" style="font-weight:800; border: 1.5pt solid #000;">ACTION</th>
            <th rowspan="2" style="font-weight:800; border: 1.5pt solid #000;">PIC</th>
          </tr>
          <tr class="table-light" style="background-color: #f2f2f2;">
            <th style="font-weight:800; border: 1.5pt solid #000;">STOCK</th>
            <th style="font-weight:800; border: 1.5pt solid #000;">TANGGAL</th>
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
                  <?php 
                    $pointCheckDisplay = esc($r['point_check']);
                    if (!empty($r['bagian_check'])) {
                        $parts = [esc($r['bagian_check'])];
                        if (!empty($r['sub_item_check'])) {
                            $parts[] = esc($r['sub_item_check']);
                        }
                        $parts[] = esc($r['point_check']);
                        $pointCheckDisplay = implode(' - ', $parts);
                    }
                  ?>
                  data-point-check="<?= $pointCheckDisplay ?>"
                  data-abnormal-condition="<?= esc($r['abnormal_condition']) ?>"
                  data-type-sparepart="<?= esc($r['type_sparepart'] ?? '') ?>"
                  data-progres-stock="<?= esc($r['progres_stock'] ?? '') ?>"
                  data-progres-tanggal="<?= esc($r['progres_tanggal'] ?? '') ?>"
                  data-action="<?= esc($r['action'] ?? '') ?>"
                  data-repair-pic="<?= esc($r['repair_pic'] ?? '') ?>"
                  data-keterangan="<?= esc($r['keterangan'] ?? '') ?>">
                
                <td class="fw-bold font-monospace text-secondary text-center" style="width: 40px; max-width: 40px; white-space: nowrap; background-color: #f8fafc;"><?= $no++ ?></td>
                <td class="text-start fw-bold text-dark ps-3"><?= esc($r['no_mesin']) ?></td>
                <td><?= $pointCheckDisplay ?></td>
                <td class="text-danger fw-semibold">
                  <?= esc($r['abnormal_condition']) ?>
                  <?php 
                    if (!empty($r['foto_abnormal'])):
                      $imgPath = FCPATH . 'uploads/abnormal/' . $r['foto_abnormal'];
                      if (file_exists($imgPath)):
                        $type = pathinfo($imgPath, PATHINFO_EXTENSION);
                        $base64 = base64_encode(file_get_contents($imgPath));
                        $src = 'data:image/' . $type . ';base64,' . $base64;
                  ?>
                    <br><img src="<?= $src ?>" style="width: 150px; max-height: 80px; object-fit: contain; margin-top: 2px; border: 1px solid #ccc;">
                  <?php endif; endif; ?>
                  
                  <?php 
                    if (!empty($r['foto_abnormal_2'])):
                      $imgPath = FCPATH . 'uploads/abnormal/' . $r['foto_abnormal_2'];
                      if (file_exists($imgPath)):
                        $type = pathinfo($imgPath, PATHINFO_EXTENSION);
                        $base64 = base64_encode(file_get_contents($imgPath));
                        $src = 'data:image/' . $type . ';base64,' . $base64;
                  ?>
                    <br><img src="<?= $src ?>" style="width: 150px; max-height: 80px; object-fit: contain; margin-top: 2px; border: 1px solid #ccc;">
                  <?php endif; endif; ?>
                </td>
                <td><?= esc($r['type_sparepart']) ?: '<span class="text-muted small">-</span>' ?></td>
                
                <!-- Pengecekan -->
                <td class="font-monospace"><?= format_tanggal_indo($r['pengecekan_tanggal']) ?></td>
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
                <td class="font-monospace"><?= $r['progres_tanggal'] ? format_tanggal_indo($r['progres_tanggal']) : '<span class="text-muted">-</span>' ?></td>
                <td class="text-start">
                  <?= esc($r['action']) ?: '<span class="text-muted">-</span>' ?>
                  <?php 
                    if (!empty($r['foto_perbaikan'])):
                      $imgPath = FCPATH . 'uploads/abnormal/' . $r['foto_perbaikan'];
                      if (file_exists($imgPath)):
                        $type = pathinfo($imgPath, PATHINFO_EXTENSION);
                        $base64 = base64_encode(file_get_contents($imgPath));
                        $src = 'data:image/' . $type . ';base64,' . $base64;
                  ?>
                    <br><img src="<?= $src ?>" style="max-height: 50px; margin-top: 2px; border: 1px solid #ccc;">
                  <?php endif; endif; ?>

                  <?php 
                    if (!empty($r['foto_perbaikan_2'])):
                      $imgPath = FCPATH . 'uploads/abnormal/' . $r['foto_perbaikan_2'];
                      if (file_exists($imgPath)):
                        $type = pathinfo($imgPath, PATHINFO_EXTENSION);
                        $base64 = base64_encode(file_get_contents($imgPath));
                        $src = 'data:image/' . $type . ';base64,' . $base64;
                  ?>
                    <br><img src="<?= $src ?>" style="max-height: 50px; margin-top: 2px; border: 1px solid #ccc;">
                  <?php endif; endif; ?>
                </td>
                <td><span class="fw-semibold text-dark"><?= esc($r['repair_pic']) ?: '<span class="text-muted">-</span>' ?></span></td>
                
                <td><?= esc($r['keterangan']) ?: '<span class="text-muted">-</span>' ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

<?php if ($currentIndex < $totalReports): ?>
  <div style="page-break-after: always;"></div>
<?php endif; ?>

<?php endforeach; ?>





</div>
</body>
</html>



