<?= view('layout/header', ['title' => $title]) ?>

<div class="dashboard-header mb-4 py-3">
    <div class="d-flex align-items-center justify-content-between position-relative" style="z-index: 2;">
        <div>
            <h3 class="fw-bold mb-1">Halo, <?= esc(ucwords(session('nama'))) ?>! 👋</h3>
            <p class="mb-0 opacity-75 small">Berikut adalah ringkasan seluruh data sistem pada platform MTCE saat ini.</p>
        </div>
        <div class="d-none d-md-block text-white opacity-50">
            <i class="bi bi-speedometer2" style="font-size: 3rem;"></i>
        </div>
    </div>
</div>

<div class="mb-5">
    <?= view('components/percentage_cards') ?>
</div>

<div class="row g-4 mb-5">
  <!-- Total User -->
  <div class="col-md-4">
    <div class="card-stat-premium grad-indigo py-3 px-4">
      <div class="text-white-50 small fw-bold text-uppercase tracking-wider mb-1" style="font-size:0.75rem;">Total User</div>
      <div class="value fs-2 fw-bolder mb-0"><?= (int) $totalUser ?></div>
      <i class="bi bi-people-fill watermark-icon" style="font-size:3.5rem; bottom:-10px;"></i>
    </div>
  </div>
  
  <!-- Total Mesin -->
  <div class="col-md-4">
    <div class="card-stat-premium grad-emerald py-3 px-4">
      <div class="text-white-50 small fw-bold text-uppercase tracking-wider mb-1" style="font-size:0.75rem;">Total Mesin</div>
      <div class="value fs-2 fw-bolder mb-0"><?= (int) $totalMesin ?></div>
      <i class="bi bi-hdd-rack-fill watermark-icon" style="font-size:3.5rem; bottom:-10px;"></i>
    </div>
  </div>
  
  <!-- Total Pengecekan -->
  <div class="col-md-4">
    <div class="card-stat-premium grad-cyan py-3 px-4">
      <div class="text-white-50 small fw-bold text-uppercase tracking-wider mb-1" style="font-size:0.75rem;">Total Pengecekan</div>
      <div class="value fs-2 fw-bolder mb-0"><?= (int) $totalTrans ?></div>
      <i class="bi bi-clipboard2-data-fill watermark-icon" style="font-size:3.5rem; bottom:-10px;"></i>
    </div>
  </div>
</div>

<h5 class="fw-bold mb-3 text-dark"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Akses Cepat Pengelolaan Master</h5>
<div class="row g-4">
  <div class="col-md-4">
    <div class="card card-hover h-100 p-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
      <div class="position-absolute top-0 end-0 p-3 opacity-10">
        <i class="bi bi-hdd-network-fill" style="font-size: 5rem; color: var(--accent);"></i>
      </div>
      <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-3 rounded-circle mb-3" style="width: 50px; height: 50px;">
          <i class="bi bi-hdd-network fs-4"></i>
      </div>
      <h5 class="fw-bold mb-2 text-dark">Master Mesin</h5>
      <p class="text-muted small mb-4">Kelola daftar seluruh mesin CNC & Bar Feeder beserta detail serial number di MFG 1 dan MFG 2.</p>
      <a href="<?= site_url('admin/mesin') ?>" class="btn btn-outline-primary mt-auto align-self-start fw-bold">Kelola Mesin &rarr;</a>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-hover h-100 p-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
      <div class="position-absolute top-0 end-0 p-3 opacity-10">
        <i class="bi bi-person-badge-fill" style="font-size: 5rem; color: var(--accent);"></i>
      </div>
      <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-3 rounded-circle mb-3" style="width: 50px; height: 50px;">
          <i class="bi bi-person-lines-fill fs-4"></i>
      </div>
      <h5 class="fw-bold mb-2 text-dark">Master User</h5>
      <p class="text-muted small mb-4">Manajemen akses dan hak akses (role) untuk seluruh PIC, Leader, dan sesama Administrator.</p>
      <a href="<?= site_url('admin/user') ?>" class="btn btn-outline-primary mt-auto align-self-start fw-bold">Kelola User &rarr;</a>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-hover h-100 p-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
      <div class="position-absolute top-0 end-0 p-3 opacity-10">
        <i class="bi bi-list-check" style="font-size: 5rem; color: var(--accent);"></i>
      </div>
      <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-3 rounded-circle mb-3" style="width: 50px; height: 50px;">
          <i class="bi bi-card-checklist fs-4"></i>
      </div>
      <h5 class="fw-bold mb-2 text-dark">Master Parameter Check</h5>
      <p class="text-muted small mb-4">Kelola dinamis form poin pengecekan rutin dan standar pemeriksaan operasional mesin.</p>
      <a href="<?= site_url('admin/parameter') ?>" class="btn btn-outline-primary mt-auto align-self-start fw-bold">Kelola Parameter &rarr;</a>
    </div>
  </div>
</div>



<?= view('layout/footer') ?>
