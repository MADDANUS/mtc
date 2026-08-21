<?= view('layout/header', ['title' => $title]) ?>

<div class="mb-3">
  <a href="<?= site_url('checklist') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i> Kembali ke Pilih Departemen
  </a>
</div>

<div class="row justify-content-center mb-5">
  <div class="col-md-8 text-center">
    <h2 class="fw-bold text-dark mb-2">Pengecekan di <?= esc($departemenName) ?></h2>
    <p class="text-muted">Pilih jenis pemeriksaan mesin yang ingin Anda lakukan di <?= esc($departemenName) ?>.</p>
  </div>
</div>

<div class="row justify-content-center g-4">
  <!-- Card Preventive -->
  <div class="col-md-5">
    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden card-hover transition" style="transition: transform 0.2s, box-shadow 0.2s;">
      <div class="card-body p-5 d-flex flex-column text-center" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 5px solid #0d6efd;">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-4 rounded-circle mb-4 mx-auto" style="width: 80px; height: 80px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-shield-fill-check" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.777 11.777 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7.159 7.159 0 0 0 1.048-.625 11.775 11.775 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.541 1.541 0 0 0-1.044-1.263 62.467 62.467 0 0 0-2.887-.87C9.843.266 8.69 0 8 0zm2.146 5.146a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647z"/>
          </svg>
        </div>
        <h3 class="card-title fw-bold text-dark mb-3">Preventive Maintenance</h3>
        <p class="card-text text-muted mb-4">Pengecekan rutin terencana (harian/mingguan) untuk mencegah kerusakan mesin.</p>
        <a href="<?= site_url("checklist/{$departemenSlug}/checklist-report") ?>" class="btn btn-primary w-100 py-3 fw-bold rounded-3 mt-auto">Mulai Preventive &raquo;</a>
      </div>
    </div>
  </div>

  <?php if ($role !== 'magang'): ?>
  <!-- Card Overhaul -->
  <div class="col-md-5">
    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden card-hover transition" style="transition: transform 0.2s, box-shadow 0.2s;">
      <div class="card-body p-5 d-flex flex-column text-center" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 5px solid #fd7e14;">
        <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning p-4 rounded-circle mb-4 mx-auto" style="width: 80px; height: 80px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-tools" viewBox="0 0 16 16">
            <path d="M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.11.55a1 1 0 0 0 .285.845l2 2a1 1 0 0 0 1.414 0l2-2a1 1 0 0 0 0-1.414l-2-2a1 1 0 0 0-.845-.285l-.55.11-.968-.968 2.617-2.654A3.003 3.003 0 0 0 16 3a3 3 0 1 0-5.878.851l-2.654 2.617-.968-.968.11-.55a1 1 0 0 0-.285-.845l-2-2a1 1 0 0 0-1.414 0l-2 2a1 1 0 0 0 0 1.414l2 2a1 1 0 0 0 .845.285l.55-.11.968.968-2.617 2.654A3.003 3.003 0 0 0 0 3a3 3 0 0 0 5.878-.851L3.22 0H1zm2 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm11 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
          </svg>
        </div>
        <h3 class="card-title fw-bold text-dark mb-3">Overhaul Maintenance</h3>
        <p class="card-text text-muted mb-4">Pemeriksaan dan pembongkaran menyeluruh untuk perbaikan besar mesin secara berkala.</p>
        <a href="<?= site_url("checklist/{$departemenSlug}/overhaul") ?>" class="btn btn-warning text-white w-100 py-3 fw-bold rounded-3 mt-auto">Mulai Overhaul &raquo;</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<style>
  .card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15)!important;
  }
</style>

<?= view('layout/footer') ?>
