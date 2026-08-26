<?= view('layout/header', ['title' => $title]) ?>

<style>
  /* Memaksa tabel agar tidak menyusut terlalu kecil di layar HP sehingga bisa digeser (swipe) */
  .checklist-table { min-width: 850px; }
</style>

<div class="page-header d-flex align-items-center gap-3" style="justify-content: flex-start;">
  <?php if (isset($from) && $from === 'kontrol'): ?>
    <?php 
      $backUrl = site_url('kontrol') . '?departemen=' . urlencode($cb_lokasi ?? 'MFG 1') . '&line=' . urlencode($cb_line ?? '') . '&kategori=' . urlencode($cb_kategori ?? '') . '&bulan=' . urlencode($cb_bulan ?? '') . '&plant=' . urlencode($cb_plant ?? 'Plant 1');
      if (!empty($_GET['qs_summary'])) {
          $backUrl .= '&qs_summary=' . urlencode($_GET['qs_summary']);
      }
      if (!empty($_GET['from_origin'])) {
          $backUrl .= '&from=' . urlencode($_GET['from_origin']);
      }
    ?>
    <a href="<?= $backUrl ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Kembali ke Checklist Control
    </a>
  <?php elseif (isset($from) && $from === 'durasi'): ?>
    <?php 
      $backUrl = site_url('laporan/durasi');
      $qsParams = $_GET;
      unset($qsParams['from']);
      if (!empty($qsParams)) {
          $backUrl .= '?' . http_build_query($qsParams);
      }
    ?>
    <a href="<?= $backUrl ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Kembali ke Laporan Durasi
    </a>
  <?php elseif (isset($from) && $from === 'approval'): ?>
    <?php
      $backUrl = site_url('approval');
      if (!empty($_GET['qs_approval'])) {
          $backUrl .= '?' . $_GET['qs_approval'];
      }
    ?>
    <a href="<?= $backUrl ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Kembali ke Approval
    </a>
  <?php else: ?>
    <?php 
      $lokSlug = isset($_GET['from_lokasi']) ? $_GET['from_lokasi'] : strtolower(str_replace(' ', '', $header['departemen_check']));
      $qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '?jenis_check=' . urlencode($header['jenis_check']);
    ?>
    <a href="<?= site_url('riwayat/departemen/' . $lokSlug . $qs) ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
  <?php endif; ?>
    <div>
      <h5 class="mb-0 fw-bold text-uppercase"><i class="bi bi-clipboard-check me-2 text-primary"></i>Detail <?= strtolower($header['jenis_check']) === 'overhaul' ? 'Inspection' : 'Checklist' ?> Report</h5>
    </div>
  <div class="ms-auto d-flex align-items-center gap-2">
    <?php if (!has_any_role(['leader', 'sheadprd', 'sheadmtc'])): ?>
    <a href="<?= site_url('riwayat/download-excel-detail/' . $header['id_transaksi']) ?>" class="btn btn-sm btn-outline-success shadow-sm" target="_blank">
      <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
    </a>
    <a href="<?= site_url('riwayat/download-pdf/' . $header['id_transaksi']) ?>" class="btn btn-sm btn-outline-danger shadow-sm" target="_blank">
      <i class="bi bi-eye-fill me-1"></i> Preview PDF
    </a>
    <?php endif; ?>
  </div>
</div>

<?php 
  $rawNamaTop = $header['nama_pic'] ?: $header['nama_staff'];
  $namaTopParts = explode(' - ', $rawNamaTop);
  $namaTopOnly = end($namaTopParts);
  $waktuMulai = strtotime($header['waktu_mulai']);
  $waktuSelesai = $header['waktu_selesai'] ? strtotime($header['waktu_selesai']) : null;
?>



<?php if (strtolower($header['jenis_check']) === 'overhaul'): ?>
  <table class="kop-table text-center">
    <tr>
      <td colspan="7" class="kop-table-title" style="padding: 10px;">INSPECTION REPORT - <?= strtoupper(esc($header['kategori'] ?? 'MESIN CNC')) ?></td>
    </tr>
    <tr>
      <td class="kop-label text-start" style="width:12%;">MAIN PIC</td>
      <td class="kop-val text-start" colspan="2" style="width:28%;"><?= esc($namaTopOnly) ?></td>
      <td class="kop-label text-start" style="width:15%;">NO MACHINE</td>
      <td class="kop-val text-start" style="width:15%;"><?= esc($header['no_mesin']) ?></td>
      <td class="kop-label text-start" style="width:15%;">DATE</td>
      <td class="kop-val text-start" style="width:15%;"><?= format_tanggal_indo(date('Y-m-d', $waktuMulai)) ?></td>
    </tr>
    <tr>
      <td class="kop-label text-start" rowspan="2">SUPPORT PIC</td>
      <td class="kop-val text-start" colspan="2" rowspan="2" style="vertical-align: top;"><?= esc($header['support_pic'] ?? '-') ?></td>
      <td class="kop-label text-start">MACHINE TYPE</td>
      <td class="kop-val text-start"><?= esc($header['type_mesin']) ?></td>
      <td class="kop-label text-start">START TIME</td>
      <td class="kop-val text-start"><?= date('H:i:s', $waktuMulai) ?></td>
    </tr>
    <tr>
      <?php if (stripos($header['kategori'] ?? '', 'CNC') !== false): ?>
        <td class="kop-label text-start">BAR FEEDER TYPE</td>
        <td class="kop-val text-start"><?= esc($header['bar_feeder_type'] ?? '-') ?></td>
      <?php else: ?>
        <td class="kop-label text-start"></td>
        <td class="kop-val text-start"></td>
      <?php endif; ?>
      <td class="kop-label text-start">FINISH TIME</td>
      <td class="kop-val text-start"><?= $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-' ?></td>
    </tr>
  </table>

  <div class="d-flex justify-content-end gap-4 mb-3">
    <div>
      <span class="text-muted small me-1">Status:</span> 
      <?php $statusTop = $header['status'] ?? 'Pending'; ?>
      <?php if ($statusTop === 'Approved'): ?>
        <span class="badge bg-success">Approved</span>
      <?php elseif ($statusTop === 'Approved L1'): ?>
        <span class="badge bg-info text-dark text-uppercase">Approved L1</span>
      <?php elseif ($statusTop === 'Approved L2'): ?>
        <span class="badge bg-primary text-uppercase">Approved L2</span>
      <?php else: ?>
        <span class="badge bg-warning text-dark">Pending</span>
      <?php endif; ?>
    </div>
    <div>
      <span class="text-muted small me-1">Durasi:</span> 
      <span class="fw-bold"><?= $durasiDetik !== null ? gmdate('H:i:s', $durasiDetik) : '-' ?></span>
    </div>
  </div>

<?php else: ?>
  <table class="kop-table text-center">
    <tr>
      <td colspan="6" class="kop-table-title" style="padding: 10px;">CHECKLIST REPORT - <?= strtoupper(esc($header['kategori'] ?? 'MESIN CNC')) ?> (<?= strtoupper(esc($header['departemen_check'] ?? '-')) ?>)</td>
    </tr>
    <tr>
      <td class="kop-label text-start" style="width:16%;">DATE</td>
      <td class="kop-val text-start" style="width:17%;"><?= format_tanggal_indo(date('Y-m-d', $waktuMulai)) ?></td>
      <td class="kop-label text-start" style="width:16%;">MACHINE TYPE</td>
      <td class="kop-val text-start" style="width:17%;"><?= esc($header['type_mesin']) ?></td>
      <td class="kop-label text-start" style="width:16%;">START TIME</td>
      <td class="kop-val text-start" style="width:17%;"><?= date('H:i:s', $waktuMulai) ?></td>
    </tr>
    <tr>
      <td class="kop-label text-start">NO MACHINE</td>
      <td class="kop-val text-start"><?= esc($header['no_mesin']) ?></td>
      <td class="kop-label text-start">SERIAL NUMBER</td>
      <td class="kop-val text-start"><?= esc($header['serial_nomor'] ?? '-') ?></td>
      <td class="kop-label text-start">FINISH TIME</td>
      <td class="kop-val text-start"><?= $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-' ?></td>
    </tr>
  </table>

  <div class="d-flex justify-content-end gap-4 mb-3">
    <div>
      <span class="text-muted small me-1">Status:</span> 
      <?php $statusBot = $header['status'] ?? 'Pending'; ?>
      <?php if ($statusBot === 'Approved'): ?>
        <span class="badge bg-success">Approved</span>
      <?php elseif ($statusBot === 'Approved L1'): ?>
        <span class="badge bg-info text-dark text-uppercase">Approved L1</span>
      <?php elseif ($statusBot === 'Approved L2'): ?>
        <span class="badge bg-primary text-uppercase">Approved L2</span>
      <?php else: ?>
        <span class="badge bg-warning text-dark">Pending</span>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<?php 
  $status = $header['status'] ?? 'Pending';
  $role = session()->get('role');
?>



<div class="d-flex justify-content-end mb-2">
  <table class="table table-sm table-bordered text-center mb-0 bg-white shadow-sm" style="width: auto; font-size: 0.75rem;">
    <thead style="background-color: #0f172a; color: #ffffff; border-bottom: 2px solid #0275d8;">
      <tr>
        <th colspan="3" class="py-1 px-3 text-uppercase" style="letter-spacing: 0.05em; line-height: 1.2;">KETERANGAN CHECK LIST</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="fw-bold px-3 py-0 align-middle">V</td>
        <td class="px-2 py-0 align-middle">:</td>
        <td class="text-start px-3 py-0 align-middle">OK</td>
      </tr>
      <tr>
        <td class="fw-bold px-3 py-0 align-middle">&#916;</td>
        <td class="px-2 py-0 align-middle">:</td>
        <td class="text-start px-3 py-0 align-middle">PERLU TINDAKAN</td>
      </tr>
      <tr>
        <td class="fw-bold px-3 py-0 align-middle">X</td>
        <td class="px-2 py-0 align-middle">:</td>
        <td class="text-start px-3 py-0 align-middle">TIDAK ADA</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="card-stat p-3" style="overflow: hidden;">
  <?php if (strtolower($header['jenis_check']) === 'overhaul'): ?>
    <!-- OVERHAUL DETAIL TABLE -->
    <div class="table-responsive">
    <table class="table table-bordered align-middle checklist-table bg-white">
      <thead>
        <tr>
          <th style="width:5%;">NO</th>
          <th colspan="2" style="width:30%;">ITEM CHECK</th>
          <th style="width:20%;">POINT CHECK</th>
          <?php $isCNC = (stripos($header['kategori'] ?? '', 'CNC') !== false); ?>
          <?php if ($isCNC): ?>
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
              <?php $colSpan = $isCNC ? 7 : 6; ?>
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

            <?php if ($isCNC): ?>
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
            <td>
              <?= esc($d['ulasan'] ?? '-') ?>
              <?php if (!empty($d['foto_abnormal'])): ?>
                <div class="mt-2">
                  <a href="<?= base_url('uploads/abnormal/' . $d['foto_abnormal']) ?>" target="_blank">
                    <img src="<?= base_url('uploads/abnormal/' . $d['foto_abnormal']) ?>" alt="Foto 1" style="max-height: 80px; border-radius: 4px; border: 1px solid #ccc;">
                  </a>
                </div>
              <?php endif; ?>
              <?php if (!empty($d['foto_abnormal_2'])): ?>
                <div class="mt-2">
                  <a href="<?= base_url('uploads/abnormal/' . $d['foto_abnormal_2']) ?>" target="_blank">
                    <img src="<?= base_url('uploads/abnormal/' . $d['foto_abnormal_2']) ?>" alt="Foto 2" style="max-height: 80px; border-radius: 4px; border: 1px solid #ccc;">
                  </a>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
    
    <div class="mt-4 border rounded p-3 bg-light shadow-sm">
      <label class="form-label fw-bold text-secondary mb-2" style="letter-spacing: 0.5px;">NOTE AND RECOMMENDATION</label>
      <p class="mb-0" style="white-space: pre-wrap;"><?= !empty($header['note_recommendation']) ? esc($header['note_recommendation']) : '-' ?></p>
    </div>
  <?php else: ?>
    <!-- PREVENTIVE DETAIL TABLE -->
    <div class="table-responsive">
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
            <td>
              <?= esc($d['ulasan'] ?? '-') ?>
              <?php if (!empty($d['foto_abnormal'])): ?>
                <div class="mt-2">
                  <a href="<?= base_url('uploads/abnormal/' . $d['foto_abnormal']) ?>" target="_blank">
                    <img src="<?= base_url('uploads/abnormal/' . $d['foto_abnormal']) ?>" alt="Foto 1" style="max-height: 80px; border-radius: 4px; border: 1px solid #ccc;">
                  </a>
                </div>
              <?php endif; ?>
              <?php if (!empty($d['foto_abnormal_2'])): ?>
                <div class="mt-2">
                  <a href="<?= base_url('uploads/abnormal/' . $d['foto_abnormal_2']) ?>" target="_blank">
                    <img src="<?= base_url('uploads/abnormal/' . $d['foto_abnormal_2']) ?>" alt="Foto 2" style="max-height: 80px; border-radius: 4px; border: 1px solid #ccc;">
                  </a>
                </div>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  <?php endif; ?>
</div>

<!-- KOTAK TANDA TANGAN (SIGNATURE BLOCK) -->
<div class="card border-0 shadow-sm bg-white mt-3 mb-4">
  <div class="card-body p-4">
    <?php
      $isOverhaul = (strtolower(str_replace(' ', '-', $header['jenis_check'])) === 'overhaul');
    ?>

    <?php if ($isOverhaul): ?>
    <div class="row text-center align-items-end" style="min-height: 120px;">
      <!-- 1. Dibuat Oleh (Member) -->
      <div class="col-3 border-end">
        <p class="mb-0 fw-semibold text-muted small">Prepared</p>
        <p class="mb-2 fw-bold text-dark small">INSPECTOR</p>
        <div class="mb-2" style="height: 50px; display: flex; align-items: center; justify-content: center;">
          <?php if (!empty($header['waktu_selesai'])): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Selesai</span>
          <?php else: ?>
            <span class="text-muted opacity-50"><i class="bi bi-dash-lg"></i></span>
          <?php endif; ?>
        </div>
        <?php
          $rawNamaOv = $header['nama_pic'] ?: ($header['nama_staff'] ?? 'MEMBER');
          $namaOvParts = explode(' - ', $rawNamaOv);
          $namaOvOnly = end($namaOvParts);
        ?>
        <h6 class="mb-0 fw-bold text-dark">
          <span class="text-decoration-underline" style="font-size:0.9rem;"><?= esc($namaOvOnly) ?></span>
        </h6>
        <span class="small text-muted" style="font-size:0.75rem;">
          Tgl: <?= !empty($header['waktu_selesai']) ? format_tanggal_indo($header['waktu_selesai'], false, true) : '-' ?>
        </span>
      </div>

      <!-- 2. Diperiksa Oleh (Leader) -->
      <div class="col-3 border-end">
        <p class="mb-0 fw-semibold text-muted small">Checked</p>
        <p class="mb-2 fw-bold text-dark small">LEADER PRODUKSI</p>
        <div class="mb-2" style="height: 50px; display: flex; align-items: center; justify-content: center;">
          <?php if (!empty($header['approval_l1_by'])): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Diperiksa</span>
          <?php else: ?>
            <span class="text-muted opacity-50"><i class="bi bi-dash-lg"></i></span>
          <?php endif; ?>
        </div>
        <h6 class="mb-0 fw-bold text-dark">
          <?php if (!empty($header['approval_l1_by'])): ?>
            <span class="text-decoration-underline" style="font-size:0.9rem;"><?= esc($header['approver_l1_nama']) ?></span>
          <?php else: ?>
            <span class="text-muted">( ........................................ )</span>
          <?php endif; ?>
        </h6>
        <span class="small text-muted" style="font-size:0.75rem;">
          <?php if (!empty($header['approval_l1_at'])): ?>
            Tgl: <?= format_tanggal_indo($header['approval_l1_at'], false, true) ?>
          <?php else: ?>
            Tgl: ( ......................... )
          <?php endif; ?>
        </span>
      </div>

      <!-- 3. Disetujui Oleh (SHead Produksi) -->
      <div class="col-3 border-end">
        <p class="mb-0 fw-semibold text-muted small">Approved</p>
        <p class="mb-2 fw-bold text-dark small">SECTION HEAD PRODUKSI</p>
        <div class="mb-2" style="height: 50px; display: flex; align-items: center; justify-content: center;">
          <?php if (!empty($header['approval_l2_by'])): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Disetujui</span>
          <?php else: ?>
            <span class="text-muted opacity-50"><i class="bi bi-dash-lg"></i></span>
          <?php endif; ?>
        </div>
        <h6 class="mb-0 fw-bold text-dark">
          <?php if (!empty($header['approval_l2_by'])): ?>
            <span class="text-decoration-underline" style="font-size:0.9rem;"><?= esc($header['approver_l2_nama']) ?></span>
          <?php else: ?>
            <span class="text-muted">( ........................................ )</span>
          <?php endif; ?>
        </h6>
        <span class="small text-muted" style="font-size:0.75rem;">
          <?php if (!empty($header['approval_l2_at'])): ?>
            Tgl: <?= format_tanggal_indo($header['approval_l2_at'], false, true) ?>
          <?php else: ?>
            Tgl: ( ......................... )
          <?php endif; ?>
        </span>
      </div>

      <!-- 4. Disetujui Oleh (SHead MTC) -->
      <div class="col-3">
        <p class="mb-0 fw-semibold text-muted small">Approved</p>
        <p class="mb-2 fw-bold text-dark small">SECTION HEAD MTC</p>
        <div class="mb-2" style="height: 50px; display: flex; align-items: center; justify-content: center;">
          <?php if ($header['status'] === 'Approved'): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Disetujui</span>
          <?php else: ?>
            <span class="text-muted opacity-50"><i class="bi bi-dash-lg"></i></span>
          <?php endif; ?>
        </div>
        <h6 class="mb-0 fw-bold text-dark">
          <?php if ($header['status'] === 'Approved'): ?>
            <span class="text-decoration-underline" style="font-size:0.9rem;"><?= esc($header['approver_nama']) ?></span>
          <?php else: ?>
            <span class="text-muted">( ........................................ )</span>
          <?php endif; ?>
        </h6>
        <span class="small text-muted" style="font-size:0.75rem;">
          <?php if ($header['status'] === 'Approved'): ?>
            Tgl: <?= format_tanggal_indo($header['approved_at'], false, true) ?>
          <?php else: ?>
            Tgl: ( ......................... )
          <?php endif; ?>
        </span>
      </div>
    </div>
    
    <?php else: ?>
    <!-- SIGNATURE BLOCK PREVENTIVE (SINGLE-LEVEL) -->
    <div class="row text-center align-items-end" style="min-height: 130px;">
      <!-- Dibuat Oleh (Creator) -->
      <div class="col-6 border-end">
        <p class="mb-0 fw-semibold text-muted small">Dibuat Oleh</p>
        <p class="mb-2 fw-bold text-dark small">PIC</p>
        <div class="mb-2" style="height: 60px; display: flex; align-items: center; justify-content: center;">
          <?php if (!empty($header['waktu_selesai'])): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill">
              <i class="bi bi-check-circle-fill me-1"></i> Selesai
            </span>
          <?php else: ?>
            <span class="text-muted opacity-50"><i class="bi bi-dash-lg"></i></span>
          <?php endif; ?>
        </div>
        <?php 
          $rawNamaPic = $header['nama_pic'] ?: ($header['nama_staff'] ?? 'MEMBER');
          $namaPicParts = explode(' - ', $rawNamaPic);
          $namaPicOnly = end($namaPicParts);
        ?>
        <h6 class="mb-0 fw-bold text-dark text-decoration-underline"><?= esc($namaPicOnly) ?></h6>
        <span class="small text-muted">Tanggal: <?= !empty($header['waktu_selesai']) ? format_tanggal_indo($header['waktu_selesai'], false, true) : '-' ?></span>
      </div>

      <!-- Disetujui Oleh (Approver) -->
      <div class="col-6">
        <p class="mb-0 fw-semibold text-muted small">Disetujui Oleh</p>
        <p class="mb-2 fw-bold text-dark small">PIC LINE</p>
        <div class="mb-2" style="height: 60px; display: flex; align-items: center; justify-content: center;">
          <?php if ($header['status'] === 'Approved'): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill">
              <i class="bi bi-check-circle-fill me-1"></i> Disetujui
            </span>
          <?php else: ?>
            <span class="text-muted opacity-50"><i class="bi bi-dash-lg"></i></span>
          <?php endif; ?>
        </div>
        <h6 class="mb-0 fw-bold text-dark">
          <?php if ($header['status'] === 'Approved'): ?>
            <span class="text-decoration-underline"><?= esc($header['approver_nama']) ?></span>
          <?php else: ?>
            <span class="text-muted">( ........................................ )</span>
          <?php endif; ?>
        </h6>
        <span class="small text-muted">
          <?php if ($header['status'] === 'Approved'): ?>
            Tanggal: <?= format_tanggal_indo($header['approved_at'], false, true) ?>
          <?php else: ?>
            Tanggal: ( ......................... )
          <?php endif; ?>
        </span>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php 
  // LOGIKA MENAMPILKAN TOMBOL APPROVE
  $canApprove = false;
  $statusLaporan = $header['status'];
  if ($isOverhaul) {
      if (has_role('admin') && $statusLaporan !== 'Approved') $canApprove = true;
      elseif (has_role('leader') && $statusLaporan === 'Pending') $canApprove = true;
      elseif (has_role('sheadprd') && $statusLaporan === 'Approved L1') $canApprove = true;
      elseif (has_role('sheadmtc') && $statusLaporan === 'Approved L2') $canApprove = true;
    } else {
        if (has_any_role(['member', 'leader mtc', 'admin']) && $statusLaporan === 'Pending') {
            $canApprove = true;
        }
    }
?>

<?php if ($canApprove): ?>
<div class="card border-success mt-4 mb-3 shadow-sm">
  <div class="card-body d-flex justify-content-between align-items-center p-3">
    <div>
      <h6 class="mb-1 text-dark fw-bold">Setujui Laporan Pengecekan</h6>
      <p class="text-muted small mb-0">
        Anda akan menyetujui laporan ini sebagai
        <strong class="text-success"><?= esc(session()->get('nama')) ?></strong>.
        Klik Approve jika laporan sudah diperiksa dan valid.
      </p>
    </div>
    <form action="<?= site_url('riwayat/approve/' . (int) $header['id_transaksi']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui laporan ini sebagai <?= esc(session()->get('nama')) ?>?');">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-success px-4 py-2 fw-semibold shadow-sm">
        <i class="bi bi-check-circle-fill me-2"></i> Approve
      </button>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (session()->get('role') === 'admin' && $statusLaporan !== 'Pending'): ?>
<div class="card border-danger mt-3 mb-3 shadow-sm">
  <div class="card-body d-flex justify-content-between align-items-center p-3">
    <div>
      <h6 class="mb-1 text-danger fw-bold"><i class="bi bi-trash"></i> Hapus Approval Laporan</h6>
      <p class="text-muted small mb-0">Hapus approval ini agar statusnya kembali ke "Pending". Data checklist tidak akan hilang, namun data Abnormal Report dan sinkronisasi ke ceklis kontrol akan dibatalkan.</p>
    </div>
    <form action="<?= site_url('riwayat/delete-approval/' . (int) $header['id_transaksi']) ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus approval ini? Status akan kembali ke Pending.');">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-danger px-4 py-2 fw-semibold shadow-sm">
        <i class="bi bi-trash me-2"></i> Hapus Approval
      </button>
    </form>
  </div>
</div>
<?php endif; ?>

<?= view('layout/footer') ?>


