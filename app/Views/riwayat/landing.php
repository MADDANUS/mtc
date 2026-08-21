<?= view('layout/header', ['title' => $title]) ?>

<div class="row justify-content-center my-4">
  <div class="col-md-8 text-center mb-3">
    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-clock-history me-2 text-primary"></i>Pilih Riwayat Pengecekan</h5>
    <p class="text-muted small">Silakan pilih kategori pengecekan mesin untuk melihat riwayat.</p>
  </div>
</div>

<div class="row justify-content-center g-4">
  <!-- Card Preventive -->
  <div class="col-md-5">
    <a href="<?= site_url('riwayat/departemen/mfg1?jenis_check=Preventive') ?>" class="text-decoration-none">
      <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden card-hover transition" style="transition: transform 0.2s, box-shadow 0.2s;">
        <div class="card-body p-5 d-flex flex-column text-center" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 5px solid var(--primary);">
          <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-4 rounded-circle mb-4 mx-auto" style="width: 80px; height: 80px;">
            <i class="bi bi-calendar-check" style="font-size: 2.5rem; color: var(--primary);"></i>
          </div>
          <h5 class="card-title fw-bold text-dark mb-3">Checklist Report</h5>
          <p class="card-text text-muted mb-0">Melihat daftar riwayat pengecekan rutin / preventive maintenance mesin produksi.</p>
        </div>
      </div>
    </a>
  </div>

  <!-- Card Overhaul -->
  <div class="col-md-5">
    <a href="<?= site_url('riwayat/departemen/mfg1?jenis_check=Overhaul') ?>" class="text-decoration-none">
      <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden card-hover transition" style="transition: transform 0.2s, box-shadow 0.2s;">
        <div class="card-body p-5 d-flex flex-column text-center" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 5px solid #f43f5e;">
          <div class="d-inline-flex align-items-center justify-content-center bg-opacity-10 p-4 rounded-circle mb-4 mx-auto" style="width: 80px; height: 80px; background-color: rgba(244, 63, 94, 0.1); color: #f43f5e;">
            <i class="bi bi-tools" style="font-size: 2.5rem;"></i>
          </div>
          <h5 class="card-title fw-bold text-dark mb-3">Inspection Report</h5>
          <p class="card-text text-muted mb-0">Melihat daftar riwayat pembongkaran / overhaul maintenance mesin produksi.</p>
        </div>
      </div>
    </a>
  </div>
</div>

<style>
  .card-hover:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow) !important;
  }
</style>

<?= view('layout/footer') ?>
