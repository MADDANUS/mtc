<?= view('layout/header', ['title' => $title]) ?>

<?php
// Helper to build the create form URL preserving the dynamic machine ID and Line
$getCreateUrl = function(string $categorySlug) use ($plantSlug, $departemenSlug, $jenisSlug, $idMesin, $line) {
    $url = "checklist/plant/{$plantSlug}/{$departemenSlug}/{$jenisSlug}/create/{$categorySlug}";
    $params = [];
    if (!empty($idMesin)) {
        $params[] = 'id_mesin=' . (int)$idMesin;
    }
    if (!empty($line)) {
        $params[] = 'line=' . urlencode($line);
    }
    if (!empty($params)) {
        $url .= '?' . implode('&', $params);
    }
    return site_url($url);
};
?>

<div class="page-header">
  <div>
    <?php
    $backUrl = site_url('checklist/plant/' . $plantSlug); // default
    if (!empty($idMesin)) {
        $backUrl = site_url("scan/mesin/{$idMesin}");
    } elseif (!empty($line)) {
        // Kembali ke halaman Pilih Line
        $backUrl = site_url("checklist/plant/{$plantSlug}/{$departemenSlug}/{$jenisSlug}");
    }
    ?>
    <a href="<?= $backUrl ?>" class="btn btn-sm btn-outline-secondary mb-2">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <h5 class="mb-0">
      Buat Pengecekan Baru — <span style="color:var(--accent)"><?= esc($jenisName) ?> <?= esc($departemenName) ?></span>
      <?php if (!empty($line)): ?>
        <span class="badge bg-primary ms-2" style="font-size:0.75rem;"><i class="bi bi-diagram-3 me-1"></i><?= esc($line) ?></span>
      <?php endif; ?>
      <?php if (!empty($idMesin)): ?>
        <span class="badge bg-info ms-2" style="font-size:0.75rem;"><i class="bi bi-qr-code me-1"></i>Mesin Terkunci</span>
      <?php endif; ?>
    </h5>
  </div>
</div>

<div class="row g-3">
  <?php if (strtolower($jenisSlug) === 'overhaul'): ?>
    <!-- OVERHAUL CARDS -->

    <!-- OVERHAUL CARDS (DYNAMIC) -->
    <?php if (!empty($categories)): ?>
      <?php foreach ($categories as $slug => $name): ?>
        <div class="col-6 col-md-3">
          <div class="card card-hover h-100 shadow-sm border-0">
            <div class="card-body p-2 d-flex flex-column text-center">
              <div class="mx-auto mb-2 mt-1" style="width:36px;height:36px;background:var(--accent-light);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-tools" style="color:var(--accent);font-size:1rem;"></i>
              </div>
              <div class="mb-2">
                <h6 class="mb-0 fw-bold text-uppercase" style="font-size:0.75rem; line-height:1.3;"><?= esc($name) ?></h6>
              </div>
              <a href="<?= $getCreateUrl($slug) ?>" class="btn btn-primary btn-sm w-100 mt-auto" style="font-size:0.7rem; padding:0.25rem 0.5rem;">
                <i class="bi bi-play-fill"></i> Mulai
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="alert alert-info">Belum ada kategori overhaul untuk departemen ini.</div>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <!-- PREVENTIVE CARDS (6 Kategori) -->

    <?php if (isset($categories['penerangan'])): ?>
    <!-- 1. Penerangan -->
    <div class="col-md-4">
      <div class="card card-hover h-100">
        <div class="card-body p-4 d-flex flex-column">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width:44px;height:44px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="bi bi-lightbulb-fill" style="color:var(--warning);font-size:1.2rem;"></i>
            </div>
            <div>
              <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);"><?= esc($jenisName) ?></div>
              <h6 class="mb-0" style="font-weight:700;">Penerangan</h6>
            </div>
          </div>
          <p style="font-size:0.83rem;color:var(--text-secondary);line-height:1.6;" class="mb-4">
            Cek lampu sorot, headstock room, cutting room, lampu area atas mesin, kelengkapan dan lumen.
          </p>
          <a href="<?= $getCreateUrl('penerangan') ?>" class="btn btn-primary w-100 mt-auto">
            <i class="bi bi-play-fill"></i> Mulai Pengecekan
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (isset($categories['kabel-dan-pipa'])): ?>
    <!-- 2. Kabel & Pipa -->
    <div class="col-md-4">
      <div class="card card-hover h-100">
        <div class="card-body p-4 d-flex flex-column">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width:44px;height:44px;background:var(--accent-light);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="bi bi-hdd-network-fill" style="color:var(--accent);font-size:1.2rem;"></i>
            </div>
            <div>
              <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);"><?= esc($jenisName) ?></div>
              <h6 class="mb-0" style="font-weight:700;">Kabel dan Pipa</h6>
            </div>
          </div>
          <p style="font-size:0.83rem;color:var(--text-secondary);line-height:1.6;" class="mb-4">
            Cek kabel mesin ke panel/bartop, selang hydraulic, saluran amano/vacuum, kabel, dan selang oli.
          </p>
          <a href="<?= $getCreateUrl('kabel-dan-pipa') ?>" class="btn btn-primary w-100 mt-auto">
            <i class="bi bi-play-fill"></i> Mulai Pengecekan
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (isset($categories['angin-bocor'])): ?>
    <!-- 3. Angin Bocor -->
    <div class="col-md-4">
      <div class="card card-hover h-100">
        <div class="card-body p-4 d-flex flex-column">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width:44px;height:44px;background:#e0f2fe;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="bi bi-wind" style="color:#0284c7;font-size:1.2rem;"></i>
            </div>
            <div>
              <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);"><?= esc($jenisName) ?></div>
              <h6 class="mb-0" style="font-weight:700;">Angin Bocor</h6>
            </div>
          </div>
          <p style="font-size:0.83rem;color:var(--text-secondary);line-height:1.6;" class="mb-4">
            Cek kebocoran solenoid, regulator, fitting, coupler, selang angin bartop/mesin, dan air gun.
          </p>
          <a href="<?= $getCreateUrl('angin-bocor') ?>" class="btn btn-primary w-100 mt-auto">
            <i class="bi bi-play-fill"></i> Mulai Pengecekan
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (isset($categories['bearing-cam'])): ?>
    <!-- 4. Bearing Cam -->
    <div class="col-md-4">
      <div class="card card-hover h-100">
        <div class="card-body p-4 d-flex flex-column">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width:44px;height:44px;background:#fce7f3;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="bi bi-gear-wide-connected" style="color:#db2777;font-size:1.2rem;"></i>
            </div>
            <div>
              <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);"><?= esc($jenisName) ?></div>
              <h6 class="mb-0" style="font-weight:700;">Bearing Cam</h6>
            </div>
          </div>
          <p style="font-size:0.83rem;color:var(--text-secondary);line-height:1.6;" class="mb-4">
            Cek noise &amp; temperature pada Bearing Spindle, Chucking, Center Shaft A/B/C, dan Bearing CAM.
          </p>
          <a href="<?= $getCreateUrl('bearing-cam') ?>" class="btn btn-primary w-100 mt-auto">
            <i class="bi bi-play-fill"></i> Mulai Pengecekan
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (isset($categories['gearbox'])): ?>
    <!-- 5. Gearbox -->
    <div class="col-md-4">
      <div class="card card-hover h-100">
        <div class="card-body p-4 d-flex flex-column">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width:44px;height:44px;background:#ede9fe;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="bi bi-gear-fill" style="color:#7c3aed;font-size:1.2rem;"></i>
            </div>
            <div>
              <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);"><?= esc($jenisName) ?></div>
              <h6 class="mb-0" style="font-weight:700;">Gearbox Cam</h6>
            </div>
          </div>
          <p style="font-size:0.83rem;color:var(--text-secondary);line-height:1.6;" class="mb-4">
            Cek kondisi oli (terlihat dari lubang pengisian), mata gear (tidak backloss), dan nok seal (tidak rembes).
          </p>
          <a href="<?= $getCreateUrl('gearbox') ?>" class="btn btn-primary w-100 mt-auto">
            <i class="bi bi-play-fill"></i> Mulai Pengecekan
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if (isset($categories['belt-cam'])): ?>
    <!-- 6. Belt Cam -->
    <div class="col-md-4">
      <div class="card card-hover h-100">
        <div class="card-body p-4 d-flex flex-column">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width:44px;height:44px;background:#d1fae5;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <i class="bi bi-arrow-repeat" style="color:var(--success);font-size:1.2rem;"></i>
            </div>
            <div>
              <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);"><?= esc($jenisName) ?></div>
              <h6 class="mb-0" style="font-weight:700;">Belt Cam</h6>
            </div>
          </div>
          <p style="font-size:0.83rem;color:var(--text-secondary);line-height:1.6;" class="mb-4">
            Cek sambungan &amp; kondisi belt pada Belt Spindle, Gearbox, Motor, Optional, Oil Pump, dan Belt CAM.
          </p>
          <a href="<?= $getCreateUrl('belt-cam') ?>" class="btn btn-primary w-100 mt-auto">
            <i class="bi bi-play-fill"></i> Mulai Pengecekan
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?= view('layout/footer') ?>
