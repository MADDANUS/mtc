<?= view('layout/header', ['title' => $title]) ?>

<div class="page-header">
  <div>
    <a href="<?= site_url('checklist') ?>" class="btn btn-sm btn-outline-secondary mb-2">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <h5 class="mb-0 fw-bold">
      <i class="bi bi-diagram-3 me-2 text-primary"></i>Pilih Line — <span style="color:var(--accent)"><?= esc($jenisName) ?> <?= esc($lokasiName) ?></span>
    </h5>
    <p class="text-muted small mt-1 mb-0">Pilih area / line tempat mesin yang akan diperiksa.</p>
  </div>
</div>

<div class="row g-3">
  <?php if (!empty($lines)): ?>
    <?php foreach ($lines as $lineName): ?>
      <div class="col-6 col-md-3">
        <a href="<?= site_url("checklist/{$lokasiSlug}/{$jenisSlug}?line=" . urlencode($lineName)) ?>"
           class="card card-hover h-100 shadow-sm border-0 rounded-4 text-decoration-none"
           style="transition: transform 0.2s, box-shadow 0.2s;">
          <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center"
               style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 4px solid var(--primary); min-height: 130px;">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3"
                 style="width: 52px; height: 52px;">
              <i class="bi bi-diagram-3-fill" style="font-size: 1.5rem;"></i>
            </div>
            <h6 class="fw-bold text-dark mb-0" style="font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.05em;"><?= esc($lineName) ?></h6>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="col-md-8">
      <div class="alert alert-warning text-center rounded-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        Belum ada Line yang terdaftar untuk lokasi <strong><?= esc($lokasiName) ?></strong>.
        <br><small>Silakan tambahkan data Line di menu <a href="<?= site_url('admin/line') ?>">Master Line</a> terlebih dahulu.</small>
      </div>
    </div>
  <?php endif; ?>
</div>

<style>
  .card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
  }
</style>

<?= view('layout/footer') ?>
