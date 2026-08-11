<?= view('layout/header', ['title' => $title]) ?>



<div class="d-flex align-items-center mb-3">
  <?php
    if (!empty($_GET['from']) && $_GET['from'] === 'approval') {
        $backUrl = site_url('approval');
        $backLabel = 'Kembali ke Approval';
    } else {
        $backUrl = site_url('kontrol?view=summary');
        if (!empty($_GET['qs_summary'])) {
            $backUrl = site_url('kontrol?' . $_GET['qs_summary']);
        }
        $backLabel = 'Kembali';
    }
  ?>
  <a href="<?= $backUrl ?>" class="btn btn-outline-secondary btn-sm me-3 shadow-sm rounded-pill px-3">
    <i class="bi bi-arrow-left me-1"></i> <?= $backLabel ?>
  </a>
  <div class="ms-auto d-flex gap-2">
    <?php if (!in_array(session()->get('role'), ['leader', 'sheadprd', 'sheadmtc'])): ?>
<a href="<?= site_url('kontrol/pdf?lokasi=' . urlencode($lokasi) . '&kategori=' . urlencode($kategori) . '&bulan=' . urlencode($bulan) . '&line=' . urlencode($line)) ?>" target="_blank" class="btn btn-sm btn-danger fw-semibold shadow-sm" title="Preview PDF">
      <i class="bi bi-eye-fill me-1"></i> Preview PDF
    </a>
<?php endif; ?>
  </div>
</div>

<table class="kop-table text-center shadow-sm">
  <tr>
    <td colspan="6" class="kop-table-title" style="padding: 10px;">CHECKLIST CONTROL - <?= strtoupper(esc($kategori)) ?></td>
  </tr>
  <tr>
    <td class="kop-label text-start">AREA</td>
    <td class="kop-val text-start"><?= esc($lokasi) ?> <?= $line ? '/ ' . esc($line) : '' ?></td>
    <td class="kop-label text-start">KATEGORI</td>
    <td class="kop-val text-start"><?= esc($kategori) ?></td>
    <td class="kop-label text-start">BULAN</td>
    <td class="kop-val text-start"><?= esc($bulanList[$bulan] ?? $bulan) ?></td>
  </tr>
</table>

<!-- KETERANGAN CHECK LIST -->
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

<!-- GRID TABEL KONTROL -->
<div class="card border-0 shadow-sm bg-white overflow-hidden mb-4">
  

  <div class="card-body p-0">
    <div class="table-responsive" style="border: 1px solid var(--border-strong) !important; border-radius: var(--radius);">
      <table class="table align-middle text-center mb-0 kontrol-table paginated-table" data-rows-per-item="2" style="font-size: 0.85rem; border-collapse: collapse !important;">
        <thead>
          <tr>
            <th rowspan="3" style="width: 5%; font-weight:700; vertical-align: middle;">NO</th>
            <th rowspan="3" style="width: 25%; font-weight:700; vertical-align: middle; text-align: left;" class="ps-4">MESIN</th>
            <th colspan="5" style="width: 35%; font-weight:700;">WAKTU</th>
            <th rowspan="3" style="width: 12%; font-weight:700; vertical-align: middle;">OUT OF PLAN</th>
            <th rowspan="3" style="width: 16%; font-weight:700; vertical-align: middle; text-align: left;" class="ps-4">ULASAN</th>
            <th rowspan="3" style="width: 7%; font-weight:700; vertical-align: middle;">DETAIL</th>
          </tr>
          <tr>
            <th colspan="5" style="font-weight:700; text-transform: uppercase;">
              <?= isset($bulanList[$bulan]) ? strtoupper($bulanList[$bulan]) : strtoupper($bulan) ?>
            </th>
          </tr>
          <tr>
          <?php for ($col = 1; $col <= 5; $col++): ?>
            <th style="width: 7%; font-weight:700; font-size: 0.8rem; vertical-align: middle;">
              <?php if ($hasSchedule && !empty($columnDates[$col])): ?>
                <span class="d-block fw-bolder" style="color: #fef08a !important; font-size: 0.95rem;"><?= date('d', strtotime($columnDates[$col])) ?></span>
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
                  <?= (isset($lokasi) && $lokasi === 'MFG 2') ? esc($m['no_mesin']) : (!empty($m['jenis']) ? esc($m['jenis']) . ' ' . esc($m['no_mesin']) : esc($m['no_mesin'])) ?>
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
                    <span class="text-secondary fw-semibold" style="font-size: 0.65rem;"><?= format_tanggal_indo($row['out_of_plan']) ?></span>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>

                <!-- Ulasan (Top cell) -->
                <td class="text-start ps-4 py-2 text-muted" style="border-bottom: 1px solid #e7e5e4 !important; background-color: #fff;">
                  <?= nl2br(esc($row['ulasan'])) ?: '-' ?>
                  <?php if (!empty($row['photos'])): ?>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                    <?php foreach ($row['photos'] as $ph): ?>
                      <?php if (file_exists(FCPATH . 'uploads/abnormal/' . $ph)): ?>
                        <a href="<?= base_url('uploads/abnormal/' . $ph) ?>" target="_blank">
                          <img src="<?= base_url('uploads/abnormal/' . $ph) ?>" style="max-height: 40px; border-radius: 4px; border: 1px solid #dee2e6;">
                        </a>
                      <?php endif; ?>
                    <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- Detail (Top cell) -->
                <td rowspan="2" class="text-center align-middle" style="border-bottom: 2px solid #d6d3d1 !important; background-color: #fff;">
                  <?php 
                    $hasCheck = false;
                    for ($p = 1; $p <= 5; $p++) {
                      if (!empty($row['periodes'][$p]) && !empty($row['periodes'][$p]['status_check'])) {
                        $hasCheck = true;
                        break;
                      }
                    }
                  ?>
                  <?php if ($hasCheck): ?>
                    <?php 
                      $qsSummary = !empty($_GET['qs_summary']) ? '&qs_summary=' . urlencode($_GET['qs_summary']) : '';
                      $qsFrom = !empty($_GET['from']) ? '&from_origin=' . urlencode($_GET['from']) : '';
                    ?>
                    <a href="<?= site_url('riwayat/redirect-detail?id_mesin=' . rawurlencode($m['id_mesin']) . '&line=' . rawurlencode($line) . '&kategori=' . rawurlencode($kategori) . '&bulan=' . rawurlencode($bulan) . '&lokasi=' . rawurlencode($lokasi) . $qsSummary . $qsFrom) ?>" class="btn btn-sm btn-outline-primary fw-bold" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;" title="Lihat Laporan Full">
                      Detail
                    </a>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
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
    </div>
  </div>
</div>

<!-- KOTAK TANDA TANGAN (SIGNATURE BLOCK) -->
<div class="card border-0 shadow-sm bg-white mb-4">
  <div class="card-body p-4">
    <div class="row text-center align-items-end" style="min-height: 120px;">
      
      <!-- Dibuat Oleh (PIC LINE) -->
      <div class="col-4 border-end">
        <p class="mb-0 fw-semibold text-muted small">Dibuat Oleh</p>
        <p class="mb-2 fw-bold text-dark small">PIC LINE</p>
        <div class="mb-2" style="height: 50px; display: flex; align-items: center; justify-content: center;">
          <?php if (isset($approvalData['approved_l1_by'])): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill">
              <i class="bi bi-check-circle-fill me-1"></i> Disetujui
            </span>
          <?php else: ?>
            <span class="text-muted opacity-50"><i class="bi bi-dash-lg"></i></span>
          <?php endif; ?>
        </div>
        <h6 class="mb-0 fw-bold text-dark">
          <?php if (isset($approvalData['approved_l1_by'])): ?>
            <span class="text-decoration-underline"><?= esc($approvalData['pic_line_nama'] ?? $approvalData['l1_name']) ?></span>
          <?php else: ?>
            <span class="text-muted">( ........................................ )</span>
          <?php endif; ?>
        </h6>
        <span class="small text-muted">
          <?php if (isset($approvalData['approved_l1_at'])): ?>
            Tanggal: <?= format_tanggal_indo($approvalData['approved_l1_at'], false, true) ?>
          <?php else: ?>
            Tanggal: ( ......................... )
          <?php endif; ?>
        </span>
      </div>

      <!-- Disetujui Oleh (Leader/SHead Produksi) -->
      <div class="col-4 border-end">
        <p class="mb-0 fw-semibold text-muted small">Disetujui Oleh</p>
        <p class="mb-2 fw-bold text-dark small">SECTION HEAD PRODUKSI</p>
        <div class="mb-2" style="height: 50px; display: flex; align-items: center; justify-content: center;">
          <?php if (isset($approvalData['approved_l2_by'])): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill">
              <i class="bi bi-check-circle-fill me-1"></i> Disetujui
            </span>
          <?php else: ?>
            <span class="text-muted opacity-50"><i class="bi bi-dash-lg"></i></span>
          <?php endif; ?>
        </div>
        <h6 class="mb-0 fw-bold text-dark">
          <?php if (isset($approvalData['approved_l2_by'])): ?>
            <span class="text-decoration-underline">Mr. Rohmad</span>
          <?php else: ?>
            <span class="text-muted">( Mr. Rohmad )</span>
          <?php endif; ?>
        </h6>
        <span class="small text-muted">
          <?php if (isset($approvalData['approved_l2_at'])): ?>
            Tanggal: <?= format_tanggal_indo($approvalData['approved_l2_at'], false, true) ?>
          <?php else: ?>
            Tanggal: ( ......................... )
          <?php endif; ?>
        </span>
      </div>

      <!-- Disetujui Oleh (SHead MTC) -->
      <div class="col-4">
        <p class="mb-0 fw-semibold text-muted small">Disetujui Oleh</p>
        <p class="mb-2 fw-bold text-dark small">SECTION HEAD MTC</p>
        <div class="mb-2" style="height: 50px; display: flex; align-items: center; justify-content: center;">
          <?php if (isset($approvalData['approved_final_by'])): ?>
            <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold px-3 py-2 rounded-pill">
              <i class="bi bi-check-circle-fill me-1"></i> Disetujui
            </span>
          <?php else: ?>
            <span class="text-muted opacity-50"><i class="bi bi-dash-lg"></i></span>
          <?php endif; ?>
        </div>
        <h6 class="mb-0 fw-bold text-dark">
          <?php if (isset($approvalData['approved_final_by'])): ?>
            <span class="text-decoration-underline">Mr. Royadi</span>
          <?php else: ?>
            <span class="text-muted">( Mr. Royadi )</span>
          <?php endif; ?>
        </h6>
        <span class="small text-muted">
          <?php if (isset($approvalData['approved_final_at'])): ?>
            Tanggal: <?= format_tanggal_indo($approvalData['approved_final_at'], false, true) ?>
          <?php else: ?>
            Tanggal: ( ......................... )
          <?php endif; ?>
        </span>
      </div>

    </div>
  </div>
</div>

<?php
  $role = session()->get('role');
  $canApproveKontrol = false;
  if ($role === 'admin' && $approvalStatus !== 'Approved Final') $canApproveKontrol = true;
  elseif ($role === 'member' && $approvalStatus === 'Pending' && $allChecked) $canApproveKontrol = true;
  elseif ($role === 'sheadprd' && $approvalStatus === 'Approved L1') $canApproveKontrol = true;
  elseif ($role === 'sheadmtc' && $approvalStatus === 'Approved L2') $canApproveKontrol = true;
?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger mb-3 mt-3 shadow-sm alert-dismissible fade show" role="alert">
    <?= session()->getFlashdata('error') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>

<?php if ($role === 'member' && $approvalStatus === 'Pending' && !$allChecked): ?>
  <div class="alert alert-warning mt-3 mb-3 shadow-sm">
    <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Perhatian:</strong> Anda belum bisa menyetujui Checklist Control ini karena belum semua mesin/item diperiksa (PIC belum terisi semua). Selesaikan pengisian tabel di atas terlebih dahulu.
  </div>
<?php endif; ?>

<?php if ($canApproveKontrol): ?>
<div class="card border-success mt-3 mb-3 shadow-sm">
  <div class="card-body d-flex justify-content-between align-items-center p-3">
    <div>
      <h6 class="mb-1 text-dark fw-bold">Setujui Checklist Control Bulanan</h6>
      <p class="text-muted small mb-0">Klik tombol Approve jika data checklist control sudah diperiksa dan valid.</p>
    </div>
    <form action="<?= site_url('kontrol/approve') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui Checklist Control ini?');">
      <?= csrf_field() ?>
      <input type="hidden" name="lokasi" value="<?= esc($lokasi) ?>">
      <input type="hidden" name="line" value="<?= esc($line) ?>">
      <input type="hidden" name="kategori" value="<?= esc($kategori) ?>">
      <input type="hidden" name="bulan_tahun" value="<?= esc($bulan) ?>">
      <div class="d-flex align-items-center gap-2">
        <div style="min-width: 250px;">
          <?php if ($role === 'member'): ?>
            <select name="pic_line_nama" class="form-select form-select-sm searchable-select" required>
              <option value="">|-- Cari PIC Line --</option>
              <?php foreach ($staffPicList ?? [] as $pic): ?>
                <option value="<?= esc($pic['nama_pic']) ?>"><?= esc($pic['nama_pic']) ?></option>
              <?php endforeach; ?>
            </select>
          <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-success px-4 py-2 fw-semibold shadow-sm">
          <i class="bi bi-check-circle-fill me-2"></i> Approve (<?= esc($role) ?>)
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if (session()->get('role') === 'admin' && isset($approvalData['id_approval'])): ?>
<div class="card border-danger mt-3 mb-3 shadow-sm">
  <div class="card-body d-flex justify-content-between align-items-center p-3">
    <div>
      <h6 class="mb-1 text-danger fw-bold"><i class="bi bi-trash"></i> Hapus Approval Control</h6>
      <p class="text-muted small mb-0">Hapus approval ini agar statusnya kembali ke "Belum Selesai". Data checklist tidak akan hilang.</p>
    </div>
    <form action="<?= site_url('kontrol/delete-approval') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus approval ini? Status akan kembali ke Belum Selesai.');">
      <?= csrf_field() ?>
      <input type="hidden" name="lokasi" value="<?= esc($lokasi) ?>">
      <input type="hidden" name="line" value="<?= esc($line) ?>">
      <input type="hidden" name="kategori" value="<?= esc($kategori) ?>">
      <input type="hidden" name="bulan_tahun" value="<?= esc($bulan) ?>">
      <button type="submit" class="btn btn-danger px-4 py-2 fw-semibold shadow-sm">
        <i class="bi bi-trash me-2"></i> Hapus Approval
      </button>
    </form>
  </div>
</div>
<?php endif; ?>

<style>
  .kontrol-table {
    border-collapse: collapse !important;
    width: 100% !important;
    font-family: 'Inter', sans-serif !important;
  }
  .kontrol-table th, .kontrol-table td {
    border: 1px solid #e7e5e4 !important;
    vertical-align: middle !important;
  }
  .kontrol-table th {
    background: linear-gradient(135deg, #0f766e 0%, #115e59 100%) !important;
    color: #ffffff !important;
    padding: 0.85rem 1rem !important;
    border: 1px solid #134e4a !important;
    font-size: 0.82rem !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .kontrol-table td {
    padding: 0.85rem 1rem !important;
    font-size: 0.875rem !important;
  }
  .bg-success {
    background-color: #0d9488 !important;
    color: #ffffff !important;
  }
  .bg-warning {
    background-color: #f59e0b !important;
    color: #ffffff !important;
  }
  .bg-danger {
    background-color: #dc2626 !important;
    color: #ffffff !important;
  }
</style>



<?php if (service('request')->getGet('auto') == 1): ?>
<script>
  setTimeout(function() {
    window.location.href = "<?= site_url('abnormal') ?>";
  }, 1500);
</script>
<?php endif; ?>

<?= view('layout/footer') ?>






