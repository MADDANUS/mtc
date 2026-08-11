<?= view('layout/header', ['title' => $title]) ?>

<div class="dashboard-header mb-4">
    <div class="d-flex align-items-center justify-content-between position-relative" style="z-index: 2;">
        <div>
            <h2 class="fw-bold mb-1">Halo, <?= esc(ucwords(session('nama'))) ?>! 👋</h2>
            <p class="mb-0 opacity-75">Pantau kinerja maintenance dan status pengecekan dari seluruh PIC hari ini.</p>
        </div>
        <div class="d-none d-md-block text-white opacity-50">
            <i class="bi bi-graph-up-arrow" style="font-size: 4rem;"></i>
        </div>
    </div>
</div>

<?php
  $roleSession = session()->get('role');
  $isApproverRole = in_array($roleSession, ['sheadprd', 'sheadmtc', 'leader'], true);
?>

<?php if ($isApproverRole): ?>
<div class="row g-4 mb-4">
  <div class="col-md-<?= in_array($roleSession, ['sheadprd', 'sheadmtc']) ? '6' : '12' ?>">
    <div class="card-stat-premium grad-cyan p-4">
      <div class="text-white-50 small fw-bold text-uppercase tracking-wider mb-2">Inspection Report</div>
      <div class="value display-5 fw-bolder mb-0"><?= count($pendingOverhaul) ?></div>
      <i class="bi bi-clipboard-check watermark-icon"></i>
    </div>
  </div>
  <?php if (in_array($roleSession, ['sheadprd', 'sheadmtc'])): ?>
  <div class="col-md-6">
    <div class="card-stat-premium grad-emerald p-4">
      <div class="text-white-50 small fw-bold text-uppercase tracking-wider mb-2">Checklist Control</div>
      <div class="value display-5 fw-bolder mb-0"><?= count($pendingKontrol) ?></div>
      <i class="bi bi-grid-3x3-gap watermark-icon"></i>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php else: ?>
<div class="row g-4 mb-5">
  <div class="col-md-4">
    <div class="card-stat-premium grad-cyan p-4">
      <div class="text-white-50 small fw-bold text-uppercase tracking-wider mb-2">Total Pengecekan</div>
      <div class="value display-5 fw-bolder mb-0"><?= (int) $totalTransaksi ?></div>
      <i class="bi bi-file-earmark-bar-graph-fill watermark-icon"></i>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card-stat-premium grad-emerald p-4">
      <div class="text-white-50 small fw-bold text-uppercase tracking-wider mb-2">Rata-rata Durasi</div>
      <div class="value fs-3 fw-bolder mb-0 mt-2"><?= gmdate('i \m\e\n\i\t s \d\e\t\i\k', $rataDetik) ?></div>
      <i class="bi bi-stopwatch-fill watermark-icon"></i>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card-stat-premium grad-rose p-4 border-0">
      <div class="text-white-50 small fw-bold text-uppercase tracking-wider mb-2">Temuan Perlu Tindakan</div>
      <div class="value display-5 fw-bolder mb-0"><?= (int) $perluTindakan ?></div>
      <i class="bi bi-exclamation-triangle-fill watermark-icon"></i>
    </div>
  </div>
</div>



<?php endif; ?>

<?php
  $hasPendingOverhaul = !empty($pendingOverhaul);
  $hasPendingKontrol  = !empty($pendingKontrol);
  $showCard = $isApproverRole || $hasPendingOverhaul || $hasPendingKontrol;
?>

<?php if ($showCard): ?>
<div class="card border-0 border-start border-warning border-4 shadow-sm rounded-4 overflow-hidden mb-4">
  <div class="card-header bg-warning bg-opacity-10 pt-3 pb-2 px-4 d-flex align-items-center gap-2">
    <i class="bi bi-bell-fill text-warning fs-5"></i>
    <h5 class="fw-bold mb-0 text-dark">Menunggu Approval Anda</h5>
    <?php $totalPending = count($pendingOverhaul) + count($pendingKontrol); ?>
    <?php if ($totalPending > 0): ?>
      <span class="badge bg-warning text-dark ms-auto"><?= $totalPending ?> dokumen</span>
    <?php endif; ?>
  </div>
  <div class="card-body p-4">

    <!-- TABEL 1: INSPECTION REPORT (OVERHAUL) - DITAMPILKAN UNTUK SEMUA APPROVER -->
    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-clipboard-check me-2 text-primary"></i>Inspection Report</h6>
    <?php if ($hasPendingOverhaul): ?>
    <div class="table-responsive mb-4">
      <table class="table table-hover align-middle mb-0 border rounded-3 overflow-hidden">
        <thead class="table-primary">
          <tr>
            <th class="ps-3">No Mesin</th>
            <th>Kategori</th>
            <th>Tanggal</th>
            <th>PIC</th>
            <th>Status Saat Ini</th>
            <th class="text-end pe-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingOverhaul as $po): ?>
          <tr>
            <td class="ps-3 fw-semibold"><?= esc($po['no_mesin'] ?? $po['nama_mesin'] ?? '-') ?></td>
            <td><?= esc($po['kategori'] ?? '-') ?></td>
            <td class="text-muted small"><?= !empty($po['waktu_mulai']) ? format_tanggal_indo($po['waktu_mulai'], true) : '-' ?></td>
            <td><?= esc($po['nama_pic'] ?? '-') ?></td>
            <td>
              <?php 
                $st = $po['status'] ?? 'Pending'; 
                $myRole = session()->get('role');
              ?>
              <?php if ($st === 'Pending'): ?>
                <span class="badge bg-warning text-dark">
                  <?= in_array($myRole, ['leader', 'member']) ? 'Menunggu Approval Anda' : 'Pending (Leader)' ?>
                </span>
              <?php elseif ($st === 'Approved L1'): ?>
                <span class="badge bg-info text-dark">
                  <?= $myRole === 'sheadprd' ? 'Menunggu Approval Anda' : 'Approved L1' ?>
                </span>
              <?php elseif ($st === 'Approved L2'): ?>
                <span class="badge bg-primary">
                  <?= $myRole === 'sheadmtc' ? 'Menunggu Approval Anda' : 'Approved L2' ?>
                </span>
              <?php else: ?>
                <span class="badge bg-secondary"><?= esc($st) ?></span>
              <?php endif; ?>
            </td>
            <td class="text-end pe-3">
              <a href="<?= site_url('riwayat/' . $po['id_transaksi']) . '?from=approval' ?>" class="btn btn-sm btn-warning fw-bold rounded-pill px-3">
                <i class="bi bi-check-circle me-1"></i> Review & Approve
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="text-muted small fst-italic mb-4 p-3 bg-light rounded border border-light">
        <i class="bi bi-check2-circle me-1 text-success"></i> Belum ada dokumen Inspection Report yang perlu di-approval.
      </div>
    <?php endif; ?>

    <!-- TABEL 2: CHECKLIST CONTROL BULANAN - HANYA UNTUK SHEAD (1 & 2) ATAU JIKA ADA ISINYA -->
    <?php if (in_array(session()->get('role'), ['sheadprd', 'sheadmtc'], true) || $hasPendingKontrol): ?>
    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-grid-3x3-gap me-2 text-success"></i>Checklist Control Bulanan</h6>
    <?php if ($hasPendingKontrol): ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 border rounded-3 overflow-hidden">
        <thead class="table-success">
          <tr>
            <th class="ps-3">Lokasi</th>
            <th>Line</th>
            <th>Kategori</th>
            <th>Bulan</th>
            <th>Status Saat Ini</th>
            <th class="text-end pe-3">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingKontrol as $pk): ?>
          <tr>
            <td class="ps-3 fw-semibold"><?= esc($pk['lokasi'] ?? '-') ?></td>
            <td><?= esc($pk['line'] ?? '-') ?></td>
            <td><?= esc($pk['kategori'] ?? '-') ?></td>
                        <?php 
              $rawBulan = $pk['bulan_tahun'] ?? '';
              if (!empty($rawBulan)) {
                  $blnArr = explode('-', $rawBulan);
                  $namaBulanList = ['01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                  if (count($blnArr) >= 2 && isset($namaBulanList[$blnArr[1]])) {
                      $bulanFormat = $namaBulanList[$blnArr[1]] . ' ' . $blnArr[0];
                  } else {
                      $bulanFormat = $rawBulan;
                  }
              } else {
                  $bulanFormat = '-';
              }
            ?>
            <td class="text-muted small"><?= esc($bulanFormat) ?></td>
            <td>
              <?php 
                $st = $pk['status'] ?? 'Pending'; 
                $myRole = session()->get('role');
              ?>
              <?php if ($st === 'Pending'): ?>
                <span class="badge bg-warning text-dark">
                  <?= in_array($myRole, ['leader', 'member']) ? 'Menunggu Approval Anda' : 'Pending (Leader)' ?>
                </span>
              <?php elseif ($st === 'Approved L1'): ?>
                <span class="badge bg-info text-dark">
                  <?= $myRole === 'sheadprd' ? 'Menunggu Approval Anda' : 'Approved L1' ?>
                </span>
              <?php elseif ($st === 'Approved L2'): ?>
                <span class="badge bg-primary">
                  <?= $myRole === 'sheadmtc' ? 'Menunggu Approval Anda' : 'Approved L2' ?>
                </span>
              <?php else: ?>
                <span class="badge bg-secondary"><?= esc($st) ?></span>
              <?php endif; ?>
            </td>
            <td class="text-end pe-3">
              <?php
                $kontrolUrl = site_url('kontrol') . '?lokasi=' . urlencode($pk['lokasi'] ?? '') . '&line=' . urlencode($pk['line'] ?? '') . '&kategori=' . urlencode($pk['kategori'] ?? '') . '&bulan=' . urlencode($pk['bulan_tahun'] ?? '') . '&from=approval';
              ?>
              <a href="<?= $kontrolUrl ?>" class="btn btn-sm btn-success fw-bold rounded-pill px-3">
                <i class="bi bi-check-circle me-1"></i> Review & Approve
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <div class="text-muted small fst-italic mb-2 p-3 bg-light rounded border border-light">
        <i class="bi bi-check2-circle me-1 text-success"></i> Belum ada dokumen Checklist Control Bulanan yang perlu di-approval.
      </div>
    <?php endif; ?>
    <?php endif; ?>

  </div>
</div>
<?php endif; ?>


<?php if (!$isApproverRole): ?>
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
  <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-primary me-2"></i>Pengecekan Terbaru (Semua PIC)</h5>
    <a href="<?= site_url('laporan/durasi') ?>" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">Lihat Laporan Lengkap</a>
  </div>
  <div class="card-body px-0 pt-0 pb-2">
    <?php if (empty($terbaru)): ?>
      <div class="text-center py-5">
        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-3 mb-0">Belum ada pengecekan.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width: 5%;" class="ps-4 text-center">NO</th>
              <th>PIC</th>
              <th>MESIN</th>
              <th>LINE</th>
              <th>JENIS</th>
              <th>WAKTU MULAI</th>
              <th>Durasi</th>
              <th>Status</th>
              <th class="pe-4 text-end">Aksi</th>
            </tr>
          </thead>
          <tbody class="border-top-0">
            <?php $no = 1; ?>
            <?php foreach ($terbaru as $t): ?>
              <tr>
                <td class="ps-4 fw-semibold text-secondary text-center"><?= $no++ ?></td>
                <td>
                  <?php 
                    $rawNama = trim($t['nama_pic'] ?? '');
                    $namaStaff = trim($t['nama_staff'] ?? '');
                    
                    if (empty($rawNama) || $rawNama === $namaStaff) {
                        $picName = 'Belum Ada PIC';
                    } else {
                        $namaParts = explode(' - ', $rawNama);
                        $picName = trim(end($namaParts));
                    }
                  ?>
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle me-2 bg-primary bg-opacity-10 text-primary fw-bold" style="width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:0.8rem;">
                      <?= strtoupper(substr($picName, 0, 1)) ?>
                    </div>
                    <span class="fw-medium text-dark"><?= esc($picName) ?></span>
                  </div>
                </td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"><?= esc($t['no_mesin']) ?></span></td>
                <td><span class="fw-medium text-muted"><?= esc($t['line'] ?? '-') ?></span></td>
                <td>
                  <?php if (strtolower($t['jenis_check']) === 'overhaul'): ?>
                    <span class="badge bg-primary">Inspection Report</span>
                  <?php else: ?>
                    <span class="badge bg-info text-dark">Checklist Report</span>
                  <?php endif; ?>
                </td>
                <td class="text-muted small"><?= format_tanggal_indo($t['waktu_mulai'], true, true) ?></td>
                <td class="fw-medium"><?= $t['durasi_detik'] !== null ? gmdate('i:s', (int) $t['durasi_detik']) . ' m' : '-' ?></td>
                <td>
                  <?php if (($t['status'] ?? 'Pending') === 'Approved'): ?>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2">Approved</span>
                  <?php else: ?>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-2">Pending</span>
                  <?php endif; ?>
                </td>
                <td class="pe-4 text-end">
                  <a href="<?= site_url('riwayat/' . $t['id_transaksi']) ?>" class="btn btn-sm btn-light text-primary border-0 bg-primary bg-opacity-10 fw-bold rounded-pill px-3">Detail</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<div class="mt-5">
  <?= view('components/percentage_cards') ?>
</div>

<?= view('layout/footer') ?>
