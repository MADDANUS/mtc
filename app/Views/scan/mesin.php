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
    <div class="row g-3">
      <?php $hasJenis = !empty($mesin['jenis']); ?>
      
      <!-- Opsi 1: Checklist Report -->
      <div class="col-12">
        <a href="<?= site_url("checklist/plant/{$plantSlug}/{$departemenSlug}/checklist-report?id_mesin=" . (int)$mesin['id_mesin']) ?>" 
           class="card card-hover text-decoration-none border-0 shadow-sm bg-white">
          <div class="card-body p-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px;">
                <i class="bi bi-clock-history" style="font-size: 1.25rem;"></i>
              </div>
              <div class="text-start">
                <h6 class="fw-bold mb-0 text-dark">Checklist Report</h6>
                <small class="text-muted" style="font-size: 0.775rem;">Pengecekan rutin berkala (Penerangan, Kabel, Air, dll.)</small>
              </div>
            </div>
            <i class="bi bi-arrow-right text-muted"></i>
          </div>
        </a>
      </div>

      <!-- Opsi 2: Overhaul -->
      <div class="col-12">
        <a href="<?= $hasJenis ? site_url("checklist/plant/{$plantSlug}/{$departemenSlug}/overhaul?id_mesin=" . (int)$mesin['id_mesin']) : '#' ?>" 
           <?= !$hasJenis ? 'onclick="return blockEmptyJenis()"' : '' ?>
           class="card card-hover text-decoration-none border-0 shadow-sm bg-white">
          <div class="card-body p-4 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
              <div class="bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px;">
                <i class="bi bi-tools" style="font-size: 1.25rem;"></i>
              </div>
              <div class="text-start">
                <h6 class="fw-bold mb-0 text-dark">Pengecekan Overhaul</h6>
                <small class="text-muted" style="font-size: 0.775rem;">Pembongkaran &amp; perawatan berat unit utama</small>
              </div>
            </div>
            <i class="bi bi-arrow-right text-muted"></i>
          </div>
        </a>
      </div>

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
