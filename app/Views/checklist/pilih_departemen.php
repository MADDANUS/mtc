<?= view('layout/header', ['title' => $title]) ?>

<div class="row mb-4">
  <div class="col-12 mb-2">
    <a href="<?= base_url('checklist') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
  </div>
  <div class="col-md-8 mx-auto text-center mb-3">
    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-check2-square me-2 text-primary"></i>Pilih Pengecekan Mesin</h5>
    <p class="text-muted small">Silakan pilih kategori dan departemen manufaktur (Manufacturing Line) untuk memulai pengecekan mesin.</p>
  </div>
</div>

<div class="row justify-content-center g-4">

  <!-- Card Preventive -->
  <div class="col-md-5">
    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden card-hover transition" style="transition: transform 0.2s, box-shadow 0.2s;">
      <div class="card-body p-4 d-flex flex-column text-center" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 5px solid var(--primary);">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-3 rounded-circle mb-3 mx-auto" style="width: 70px; height: 70px;">
          <i class="bi bi-calendar-check" style="font-size: 2rem; color: var(--primary);"></i>
        </div>
        <h6 class="card-title fw-bold text-dark mb-2">Preventive Maintenance</h6>
        <p class="card-text text-muted small mb-4">Melakukan pengecekan rutin / preventive maintenance untuk mesin di area produksi MFG 1 &amp; MFG 2.</p>
        
        <div class="row g-2 mt-auto">
          <div class="col-6">
            <a href="<?= site_url('checklist/plant/' . $plantSlug . '/mfg-1/checklist-report') ?>" class="btn btn-outline-primary w-100 py-2 fw-bold rounded-3">MFG 1</a>
          </div>
          <div class="col-6">
            <a href="<?= site_url('checklist/plant/' . $plantSlug . '/mfg-2/checklist-report') ?>" class="btn btn-outline-primary w-100 py-2 fw-bold rounded-3">MFG 2</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php if (!has_role('magang')): ?>
  <!-- Card Overhaul -->
  <div class="col-md-5">
    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden card-hover transition" style="transition: transform 0.2s, box-shadow 0.2s;">
      <div class="card-body p-4 d-flex flex-column text-center" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 5px solid #f43f5e;">
        <div class="d-inline-flex align-items-center justify-content-center bg-opacity-10 p-3 rounded-circle mb-3 mx-auto" style="width: 70px; height: 70px; background-color: rgba(244, 63, 94, 0.1); color: #f43f5e;">
          <i class="bi bi-tools" style="font-size: 2rem;"></i>
        </div>
        <h6 class="card-title fw-bold text-dark mb-2">Overhaul Maintenance</h6>
        <p class="card-text text-muted small mb-4">Melakukan pembongkaran / overhaul maintenance untuk mesin di area produksi MFG 1 &amp; MFG 2.</p>
        
        <div class="row g-2 mt-auto">
          <div class="col-6">
            <a href="<?= site_url('checklist/plant/' . $plantSlug . '/mfg-1/overhaul') ?>" class="btn btn-outline-danger w-100 py-2 fw-bold rounded-3" style="color: #f43f5e; border-color: #f43f5e;">MFG 1</a>
          </div>
          <div class="col-6">
            <a href="<?= site_url('checklist/plant/' . $plantSlug . '/mfg-2/overhaul') ?>" class="btn btn-outline-danger w-100 py-2 fw-bold rounded-3" style="color: #f43f5e; border-color: #f43f5e;">MFG 2</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<style>
  .card-hover:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow) !important;
  }
  .btn-outline-danger:hover {
    background-color: #f43f5e !important;
    color: #fff !important;
  }
</style>


<?= view('layout/footer') ?>
