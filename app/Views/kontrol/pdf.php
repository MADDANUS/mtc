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
    th, td { border: 1px solid #000; padding: 4px; font-size: 11px; }
    .text-center { text-align: center; }
    .text-start { text-align: left; }
    .text-end { text-align: right; }
    .fw-bold { font-weight: bold; }
    .bg-light { background-color: #f8f9fa; }
    .kop-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background-color: #fff; }
    .kop-table th, .kop-table td { border: 1px solid #000; padding: 6px 10px; vertical-align: middle; }
    .kop-table-title { background-color: #92b0d6; text-align: center; font-weight: bold; font-size: 14px; letter-spacing: 1px; color: #000; }
    .kop-logo { text-align: center; width: 12%; font-weight: bold; }
  </style>
</head>
<body>
<div class="pdf-container">
<table class="kop-table text-center">
  <tr>
    <td rowspan="4" class="kop-logo" style="width: 15%; padding-top: 15px;">
      <div style="width: 60px; height: 60px; border: 3px double #0000ff; border-radius: 50%; margin: 0 auto; position: relative;">
        <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #fff; padding: 0 4px; font-size: 1.5rem; font-weight: normal; color: #0000ff;">NSI</div>
      </div>
      <div style="font-size: 0.65rem; margin-top: 8px; font-style: italic; color: #0070c0;">
        <div style="margin-bottom: 2px;">The Future</div>
        <div>In Our Hands</div>
      </div>
    </td>
    <td colspan="3" class="kop-table-title" style="padding: 10px; font-size:18px;">CHECKLIST CONTROL</td>
  </tr>
  <tr>
    <td colspan="3" class="fw-bold" style="font-size:14px; background-color: #f2f2f2;"><?= strtoupper($kategori) ?> (<?= strtoupper($lokasi) ?>)</td>
  </tr>
  <tr>
    <td class="fw-bold" style="width: 35%;">NO. DOCUMENT</td>
    <td class="fw-bold" style="width: 25%;">NO REVISI</td>
    <td class="fw-bold" style="width: 25%;">HALAMAN</td>
  </tr>
  <tr>
    <td>FM-MTN-09</td>
    <td>0</td>
    <td>1 DARI 1</td>
  </tr>
  <tr>
    <td colspan="4" class="text-start" style="font-size:0.75rem; padding: 2px 5px; border: none; border-bottom: 1px solid #000;">Rev.:0/2911/24</td>
  </tr>
</table>



      <table class="table align-middle text-center kontrol-table" style="font-size: 0.85rem; border-collapse: collapse !important;" style="font-size: 0.85rem; border-collapse: collapse !important;">
        <thead>
          <tr>
            <th rowspan="3" style="width: 5%; font-weight:700; vertical-align: middle; background-color: #f2f2f2; color: #000;">NO</th>
            <th rowspan="3" style="width: 25%; font-weight:700; vertical-align: middle; text-align: left; background-color: #f2f2f2; color: #000;" class="ps-4">MESIN</th>
            <th colspan="5" style="width: 35%; font-weight:700; background-color: #f2f2f2; color: #000;">WAKTU</th>
            <th rowspan="3" style="width: 15%; font-weight:700; vertical-align: middle; background-color: #f2f2f2; color: #000;">OUT OF PLAN</th>
            <th rowspan="3" style="width: 20%; font-weight:700; vertical-align: middle; text-align: left; background-color: #f2f2f2; color: #000;" class="ps-4">ULASAN</th>
          </tr>
          <tr>
            <th colspan="5" style="font-weight:700; text-transform: uppercase; background-color: #f2f2f2; color: #000;">
              <?= isset($bulanList[$bulan]) ? strtoupper($bulanList[$bulan]) : strtoupper($bulan) ?>
            </th>
          </tr>
          <tr>
          <?php for ($col = 1; $col <= 5; $col++): ?>
            <th style="width: 7%; font-weight:700; font-size: 0.8rem; vertical-align: middle; background-color: #f2f2f2; color: #000;">
              <?php if ($hasSchedule && !empty($columnDates[$col])): ?>
                <span class="d-block fw-bolder" style="color: #000 !important; font-size: 0.95rem;"><?= date('d', strtotime($columnDates[$col])) ?></span>
              <?php else: ?>
                P<?= $col ?>
              <?php endif; ?>
            </th>
          <?php endfor; ?>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($grid)): ?>
            <tr>
              <td colspan="9" class="p-5 text-muted">
                <i class="bi bi-exclamation-circle text-secondary" style="font-size: 2rem; display:block; margin-bottom:0.5rem;"></i>
                Belum ada data mesin terdaftar di <?= esc($lokasi) ?>.
              </td>
            </tr>
          <?php else: ?>
            <?php $no = 1; foreach ($grid as $row): ?>
              <?php 
                $m = $row['mesin']; 
                $idMesin = (int)$m['id_mesin'];
              ?>
              <!-- BARIS NAMA MESIN & STATUS PERIODE -->
              <tr>
                <td rowspan="2" class="fw-bold font-monospace text-secondary" style="background-color: #faf9f6; border-bottom: 2px solid #d6d3d1 !important; vertical-align: middle !important;"><?= $no++ ?></td>
                <td class="text-start fw-bold text-dark ps-4 py-2" style="border-bottom: 1px solid #e7e5e4 !important; background-color: #fff;">
                  <?= esc($m['jenis']) ?> <?= esc($m['no_mesin']) ?>
                </td>
                
                <!-- Periode 1 s.d 5 Cells (Status Check) -->
                <?php for ($p = 1; $p <= 5; $p++): ?>
                  <?php 
                    $cell = $row['periodes'][$p]; 
                    $status = $cell ? $cell['status_check'] : '';
                    $badgeClass = '';
                    
                    if ($status === 'V') $badgeClass = 'bg-success';
                    elseif ($status === 'Δ') $badgeClass = 'bg-warning text-dark';
                    elseif ($status === 'X') $badgeClass = 'bg-danger';
                  ?>
                  <td class="p-1" style="transition: background-color 0.15s; border-bottom: 1px solid #e7e5e4 !important;"
                      data-id-kontrol="<?= $cell ? $cell['id_kontrol'] : '0' ?>"
                      data-id-mesin="<?= $idMesin ?>"
                      data-no-mesin="<?= esc($m['no_mesin']) ?>"
                      data-periode="<?= $p ?>"
                      data-status="<?= $status ?>"
                      data-pic="<?= $cell ? esc($cell['pic_nama']) : '' ?>"
                      data-out-of-plan="<?= $cell ? esc($cell['out_of_plan']) : '' ?>"
                      data-ulasan="<?= $cell ? esc($cell['ulasan']) : '' ?>">
                    <?php if ($status): ?>
                      <span class="badge <?= $badgeClass ?> rounded-circle d-inline-flex align-items-center justify-content-center fw-bold" style="width: 26px; height: 26px; font-size: 0.8rem; box-shadow: 0 1px 3px rgba(0,0,0,0.15);">
                        <?= $status ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted text-opacity-25"><i class="bi bi-plus-lg opacity-25"></i></span>
                    <?php endif; ?>
                  </td>
                <?php endfor; ?>

                <!-- Out of Plan (Top cell) -->
                <td class="font-monospace text-center py-2" style="font-size: 0.75rem; border-bottom: 1px solid #e7e5e4 !important; background-color: #fff;">
                  <?php if (!empty($row['out_of_plan'])): ?>
                    <span class="text-danger fw-bold d-block" style="font-size: 0.7rem;">Out of Plan</span>
                    <span class="text-secondary fw-semibold" style="font-size: 0.65rem;"><?= date('d-m-Y', strtotime($row['out_of_plan'])) ?></span>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>

                <!-- Ulasan (Top cell) -->
                <td class="text-start ps-4 py-2 text-muted text-truncate" style="max-width: 150px; border-bottom: 1px solid #e7e5e4 !important; background-color: #fff;" title="<?= esc($row['ulasan']) ?>">
                  <?= esc($row['ulasan']) ?: '-' ?>
                </td>
              </tr>

              <!-- BARIS PIC -->
              <tr>
                <td class="text-start text-secondary ps-4 py-1.5" style="font-size: 0.75rem; background-color: #faf9f6; border-bottom: 2px solid #d6d3d1 !important; border-top: 0 !important;">
                  <span class="fw-bold text-muted text-uppercase me-1.5" style="font-size:0.625rem; letter-spacing: 0.05em;">PIC</span>
                </td>
                
                <!-- Periode 1 s.d 5 Cells (PIC Nama) -->
                <?php for ($p = 1; $p <= 5; $p++): ?>
                  <?php 
                    $cell = $row['periodes'][$p]; 
                    $status = $cell ? $cell['status_check'] : '';
                    $pic = $cell ? $cell['pic_nama'] : '';
                  ?>
                  <td class="py-1.5 px-1 font-monospace" style="font-size: 0.68rem; transition: background-color 0.15s; border-bottom: 2px solid #d6d3d1 !important; background-color: #faf9f6;"
                      data-id-kontrol="<?= $cell ? $cell['id_kontrol'] : '0' ?>"
                      data-id-mesin="<?= $idMesin ?>"
                      data-no-mesin="<?= esc($m['no_mesin']) ?>"
                      data-periode="<?= $p ?>"
                      data-status="<?= $status ?>"
                      data-pic="<?= esc($pic) ?>"
                      data-out-of-plan="<?= $cell ? esc($cell['out_of_plan']) : '' ?>"
                      data-ulasan="<?= $cell ? esc($cell['ulasan']) : '' ?>">
                    <?php
                      $picParts = explode(' - ', $pic);
                      $picOnly = end($picParts);
                    ?>
                    <span class="fw-semibold text-dark"><?= esc($picOnly) ?: '-' ?></span>
                  </td>
                <?php endfor; ?>

                <!-- Out of Plan (Bottom Cell - Empty) -->
                <td style="border-bottom: 2px solid #d6d3d1 !important; background-color: #faf9f6;"></td>

                <!-- Ulasan (Bottom Cell - Empty) -->
                <td style="border-bottom: 2px solid #d6d3d1 !important; background-color: #faf9f6;"></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>


<table style="width: 100%; border: none; text-align: center; margin-top: 30px;">
  <tr>
    <td style="border: none; width: 33.33%; vertical-align: top;">
      <div style="margin-bottom: 5px; font-size: 0.85rem;">Dibuat Oleh</div>
      <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">PIC LINE</div>
      <?php if (isset($approvalData['approved_l1_by'])): ?>
        <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Disetujui ]</div>
      <?php else: ?>
        <div style="height: 20px; margin-bottom: 20px;"></div>
      <?php endif; ?>
      <div style="font-weight: bold; text-decoration: underline; font-size: 0.9rem;">
        <?= isset($approvalData['approved_l1_by']) ? esc($approvalData['pic_line_nama'] ?? $approvalData['l1_name']) : '( ........................................ )' ?>
      </div>
      <div style="font-size: 0.8rem; color: #555;">
        Tanggal: <?= isset($approvalData['approved_l1_at']) ? date('d-m-Y H:i', strtotime($approvalData['approved_l1_at'])) : '( ......................... )' ?>
      </div>
    </td>
    <td style="border: none; width: 33.33%; vertical-align: top;">
      <div style="margin-bottom: 5px; font-size: 0.85rem;">Disetujui Oleh</div>
      <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">SECTION HEAD PRODUKSI</div>
      <?php if (isset($approvalData['approved_l2_by'])): ?>
        <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Disetujui ]</div>
      <?php else: ?>
        <div style="height: 20px; margin-bottom: 20px;"></div>
      <?php endif; ?>
      <div style="font-weight: bold; text-decoration: underline; font-size: 0.9rem;">
        <?= isset($approvalData['approved_l2_by']) ? 'Mr. Rohmad' : '( Mr. Rohmad )' ?>
      </div>
      <div style="font-size: 0.8rem; color: #555;">
        Tanggal: <?= isset($approvalData['approved_l2_at']) ? date('d-m-Y H:i', strtotime($approvalData['approved_l2_at'])) : '( ......................... )' ?>
      </div>
    </td>
    <td style="border: none; width: 33.33%; vertical-align: top;">
      <div style="margin-bottom: 5px; font-size: 0.85rem;">Diketahui Oleh</div>
      <div style="font-weight: bold; font-size: 0.9rem; margin-bottom: 20px;">SECTION HEAD MAINTENANCE</div>
      <?php if (isset($approvalData['approved_final_by'])): ?>
        <div style="color: green; font-weight: bold; margin-bottom: 20px;">[ Disetujui ]</div>
      <?php else: ?>
        <div style="height: 20px; margin-bottom: 20px;"></div>
      <?php endif; ?>
      <div style="font-weight: bold; text-decoration: underline; font-size: 0.9rem;">
        <?= isset($approvalData['approved_final_by']) ? 'Mr. Muryanto' : '( Mr. Muryanto )' ?>
      </div>
      <div style="font-size: 0.8rem; color: #555;">
        Tanggal: <?= isset($approvalData['approved_final_at']) ? date('d-m-Y H:i', strtotime($approvalData['approved_final_at'])) : '( ......................... )' ?>
      </div>
    </td>
  </tr>
</table>
</div>
</body>
</html>





