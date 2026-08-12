<?= view('layout/header', ['title' => $title]) ?>

<div class="dashboard-header mb-4 py-3">
    <div class="d-flex align-items-center justify-content-between position-relative" style="z-index: 2;">
        <div>
            <h3 class="fw-bold mb-1">Halo, <?= esc(ucwords(session('nama'))) ?>! 👋</h3>
            <p class="mb-0 opacity-75 small">Selamat bekerja, pastikan semua pengecekan berjalan sesuai standar hari ini.</p>
        </div>
        <div class="d-none d-md-block text-white opacity-50">
            <i class="bi bi-tools" style="font-size: 3rem;"></i>
        </div>
    </div>
</div>

<div class="mb-5">
    <?= view('components/percentage_cards') ?>
</div>

<div class="row g-4 mb-5">
  <div class="col-md-4">
    <div class="card-stat-premium grad-cyan py-3 px-4">
      <div class="text-white-50 small fw-bold text-uppercase tracking-wider mb-1" style="font-size:0.75rem;">Pengecekan Hari Ini</div>
      <div class="value fs-2 fw-bolder mb-0"><?= (int) $hariIni ?></div>
      <i class="bi bi-calendar-check-fill watermark-icon" style="font-size:3.5rem; bottom:-10px;"></i>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card-stat-premium grad-indigo py-3 px-4">
      <div class="text-white-50 small fw-bold text-uppercase tracking-wider mb-1" style="font-size:0.75rem;">7 Hari Terakhir</div>
      <div class="value fs-2 fw-bolder mb-0"><?= (int) $minggu ?></div>
      <i class="bi bi-bar-chart-fill watermark-icon" style="font-size:3.5rem; bottom:-10px;"></i>
    </div>
  </div>
  <?php if (session()->get('role') !== 'leader'): ?>
  <div class="col-md-4">
    <div class="card-stat-premium bg-white border-0 py-3 px-4 d-flex flex-column justify-content-center h-100" style="border: 2px dashed var(--accent) !important;">
      <div class="text-center">
        <i class="bi bi-plus-circle-dotted text-primary mb-2 d-block" style="font-size: 2.5rem;"></i>
        <a href="<?= site_url('checklist') ?>" class="btn btn-primary fw-bold rounded-pill px-4 py-2 shadow-sm">Buat Pengecekan Baru</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
  <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-text text-primary me-2"></i>Riwayat Pengecekan Saya</h5>
    <a href="<?= site_url('riwayat/lokasi/semua?jenis_check=Preventive') ?>" class="btn btn-sm btn-outline-secondary fw-bold rounded-pill px-3">Lihat Semua Riwayat</a>
  </div>
  <div class="card-body px-0 pt-0 pb-2">
    <?php if (empty($riwayatTerbaru)): ?>
      <div class="text-center py-5">
        <i class="bi bi-clipboard-x text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-3 mb-0">Anda belum melakukan pengecekan apapun.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4 text-center">NO</th>
              <th>Mesin</th>
              <th>Waktu Mulai</th>
              <th>Waktu Selesai</th>
              <th>Status</th>
              <th class="pe-4 text-end">Aksi</th>
            </tr>
          </thead>
          <tbody class="border-top-0">
            <?php $no = 1; ?>
            <?php foreach ($riwayatTerbaru as $r): ?>
              <tr>
                <td class="ps-4 fw-semibold text-secondary text-center"><?= $no++ ?></td>
                <td>
                  <div class="fw-bold text-dark"><?= esc($r['no_mesin']) ?></div>
                  <div class="text-muted small"><?= esc($r['type_mesin']) ?></div>
                </td>
                <td class="text-muted small"><?= format_tanggal_indo($r['waktu_mulai'], true, true) ?></td>
                <td class="text-muted small"><?= $r['waktu_selesai'] ? format_tanggal_indo($r['waktu_selesai'], true, true) : '-' ?></td>
                <td>
                  <?php if (($r['status'] ?? 'Pending') === 'Approved'): ?>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2">Approved</span>
                  <?php else: ?>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-2">Pending</span>
                  <?php endif; ?>
                </td>
                <td class="pe-4 text-end">
                  <a href="<?= site_url('riwayat/' . $r['id_transaksi']) ?>" class="btn btn-sm btn-light text-primary border-0 bg-primary bg-opacity-10 fw-bold rounded-pill px-3">Detail</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>



<?= view('layout/footer') ?>
