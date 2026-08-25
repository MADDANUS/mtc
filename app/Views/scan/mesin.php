<?= view('layout/header', ['title' => $title]) ?>

<div class="page-header justify-content-center">
  <div class="text-center mb-2">
    <h5 class="mb-0"><i class="bi bi-qr-code text-primary me-2"></i>Mesin Fisik Terdeteksi</h5>
    <p class="text-muted small mb-0">Informasi mesin berhasil dipindai. Silakan pilih jenis pengecekan yang akan dilakukan.</p>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <!-- Kartu Informasi Mesin -->
    <div class="card border-0 shadow-sm bg-white mb-4">
      <div class="card-body p-4 text-center">
        <div class="bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3" style="width: 64px; height: 64px;">
          <i class="bi bi-gear-wide-connected" style="font-size: 1.85rem;"></i>
        </div>
        <h4 class="fw-bold mb-1 text-dark"><?= esc($mesin['no_mesin']) ?></h4>
        <p class="text-muted small mb-3"><?= esc($mesin['type_mesin']) ?></p>
        
        <div class="row g-2 pt-3 border-top text-start">
          <div class="col-6">
            <span class="text-muted small d-block">Serial Nomor</span>
            <span class="fw-semibold text-dark" style="font-size: 0.875rem;"><?= esc($mesin['serial_nomor']) ?></span>
          </div>
          <div class="col-6">
            <span class="text-muted small d-block">Departemen / Line</span>
            <div class="d-flex align-items-center gap-1 flex-wrap">
              <span class="badge bg-primary"><?= esc($mesin['departemen']) ?></span>
              <?php if (!empty($mesin['line'])): ?>
                <span class="badge bg-info text-dark"><?= esc($mesin['line']) ?></span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pilihan Aksi (Preventive / Overhaul) -->
    <div class="row justify-content-center g-4 mt-2">
      <?php $hasJenis = !empty($mesin['jenis']); ?>
      
      <!-- Card Preventive -->
      <div class="col-12">
        <a href="<?= site_url("checklist/plant/{$plantSlug}/{$departemenSlug}/checklist-report?id_mesin=" . (int)$mesin['id_mesin']) ?>" class="card shadow-sm border-0 rounded-4 overflow-hidden card-hover text-decoration-none transition w-100" style="transition: transform 0.2s, box-shadow 0.2s; border-left: 5px solid #0d6efd;">
          <div class="card-body p-3 d-flex align-items-center" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle me-3 flex-shrink-0" style="width: 50px; height: 50px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shield-fill-check" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M8 0c-.69 0-1.843.265-2.928.56-1.11.3-2.229.655-2.887.87a1.54 1.54 0 0 0-1.044 1.262c-.596 4.477.787 7.795 2.465 9.99a11.777 11.777 0 0 0 2.517 2.453c.386.273.744.482 1.048.625.28.132.581.24.829.24s.548-.108.829-.24a7.159 7.159 0 0 0 1.048-.625 11.775 11.775 0 0 0 2.517-2.453c1.678-2.195 3.061-5.513 2.465-9.99a1.541 1.541 0 0 0-1.044-1.263 62.467 62.467 0 0 0-2.887-.87C9.843.266 8.69 0 8 0zm2.146 5.146a.5.5 0 0 1 .708.708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647z"/>
              </svg>
            </div>
            <div class="flex-grow-1 text-start">
              <h6 class="fw-bold text-dark mb-1">Preventive Maintenance</h6>
              <p class="text-muted mb-0" style="font-size: 0.8rem;">Pengecekan rutin terencana untuk mencegah kerusakan.</p>
            </div>
            <div class="flex-shrink-0 ms-2 text-primary">
              <i class="bi bi-chevron-right fs-4"></i>
            </div>
          </div>
        </a>
      </div>

      <?php if ($role !== 'magang' && strtoupper(trim($mesin['jenis'] ?? '')) !== 'CAM'): ?>
      <!-- Card Overhaul -->
      <div class="col-12">
        <a href="<?= $hasJenis ? site_url("checklist/plant/{$plantSlug}/{$departemenSlug}/overhaul?id_mesin=" . (int)$mesin['id_mesin']) : '#' ?>" <?= !$hasJenis ? 'onclick="return blockEmptyJenis()"' : '' ?> class="card shadow-sm border-0 rounded-4 overflow-hidden card-hover text-decoration-none transition w-100" style="transition: transform 0.2s, box-shadow 0.2s; border-left: 5px solid #fd7e14;">
          <div class="card-body p-3 d-flex align-items-center" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="d-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle me-3 flex-shrink-0" style="width: 50px; height: 50px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-tools" viewBox="0 0 16 16">
                <path d="M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.11.55a1 1 0 0 0 .285.845l2 2a1 1 0 0 0 1.414 0l2-2a1 1 0 0 0 0-1.414l-2-2a1 1 0 0 0-.845-.285l-.55.11-.968-.968 2.617-2.654A3.003 3.003 0 0 0 16 3a3 3 0 1 0-5.878.851l-2.654 2.617-.968-.968.11-.55a1 1 0 0 0-.285-.845l-2-2a1 1 0 0 0-1.414 0l-2 2a1 1 0 0 0 0 1.414l2 2a1 1 0 0 0 .845.285l.55-.11.968.968-2.617 2.654A3.003 3.003 0 0 0 0 3a3 3 0 0 0 5.878-.851L3.22 0H1zm2 3a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm11 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
              </svg>
            </div>
            <div class="flex-grow-1 text-start">
              <h6 class="fw-bold text-dark mb-1">Overhaul Maintenance</h6>
              <p class="text-muted mb-0" style="font-size: 0.8rem;">Pemeriksaan besar mesin secara berkala.</p>
            </div>
            <div class="flex-shrink-0 ms-2 text-warning">
              <i class="bi bi-chevron-right fs-4"></i>
            </div>
          </div>
        </a>
      </div>
      <?php endif; ?>

      <!-- Tombol Kembali / Scan Ulang -->
      <div class="col-12 mt-4 text-center">
        <a href="<?= site_url('scan') ?>" class="btn btn-light border shadow-sm px-4 py-2 text-secondary fw-semibold">
          <i class="bi bi-arrow-left me-2"></i>Kembali Scan Ulang
        </a>
      </div>
    </div>
  </div>
</div>

<style>
  .card-hover:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow) !important;
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  function blockEmptyJenis() {
    Swal.fire({
      icon: 'warning',
      title: 'Perhatian!',
      text: 'Mesin ini tidak memiliki pengecekan overhaul, silahkan konfirmasi ke atasan.',
      confirmButtonText: 'Tutup'
    });
    return false;
  }
</script>

<?= view('layout/footer') ?>
