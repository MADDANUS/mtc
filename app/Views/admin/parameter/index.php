<?= view('layout/header', ['title' => $title]) ?>

<div class="page-header">
  <div>
    <h5 class="mb-0"><i class="bi bi-sliders me-2 text-primary"></i>Master Parameter Pengecekan</h5>
    <p class="text-muted small mb-0">Kelola baris parameter checklist yang digunakan staff untuk melakukan pemeriksaan mesin.</p>
  </div>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <!-- Form Impor Excel -->
    <form action="<?= site_url('admin/parameter/import') ?>" method="post" enctype="multipart/form-data" class="d-flex align-items-center gap-1 border rounded p-1 bg-white shadow-sm" style="max-height: 38px;">
        <?= csrf_field() ?>
        <input type="file" name="file_excel" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required class="form-control form-control-sm" style="max-width: 170px; border:none; padding: 2px 4px; font-size: 0.8rem;" title="Pilih file Excel untuk diimpor">
        <button type="submit" class="btn btn-sm btn-success py-1 px-2 fw-semibold" style="font-size: 0.8rem;">Impor</button>
    </form>
    <!-- Link Template -->
    <a href="<?= site_url('admin/parameter/template') ?>" class="btn btn-outline-secondary btn-sm py-2">
        Unduh Template
    </a>
    <!-- Link Ekspor Excel -->
    <a href="<?= site_url('admin/parameter/export') ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 py-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
        </svg>
        Ekspor
    </a>
    <a href="<?= site_url('admin/parameter/create?departemen=' . urlencode($departemen) . '&jenis_check=' . urlencode($jenisCheck)) ?>" class="btn btn-sm btn-primary py-2">
      <i class="bi bi-plus-lg"></i> Tambah Parameter
    </a>
  </div>
</div>

<!-- NAVIGASI TAB LOKASI & JENIS CHECK -->
<div class="d-flex flex-wrap gap-2 mb-4">
  <?php
    $tabs = [
      ['MFG 1', 'Preventive', 'MFG 1 - Checklist Report', 'bi-shield-check'],
      ['MFG 1', 'Overhaul', 'MFG 1 - Overhaul', 'bi-tools'],
      ['MFG 2', 'Preventive', 'MFG 2 - Checklist Report', 'bi-shield-check'],
      ['MFG 2', 'Overhaul', 'MFG 2 - Overhaul', 'bi-tools'],
    ];
  ?>
  <?php foreach ($tabs as $t): ?>
    <?php
      $isActive = ($departemen === $t[0] && $jenisCheck === $t[1]);
      $btnClass = $isActive ? 'btn-primary' : 'btn-outline-secondary';
    ?>
    <a href="<?= site_url("admin/parameter?departemen=" . urlencode($t[0]) . "&jenis_check=" . urlencode($t[1])) ?>" class="btn btn-sm <?= $btnClass ?> d-flex align-items-center gap-1.5 px-3 py-2 fw-semibold rounded-3 shadow-none">
      <i class="bi <?= $t[3] ?>"></i> <?= $t[2] ?>
    </a>
  <?php endforeach; ?>
</div>

<!-- DAFTAR PARAMETER BERDASARKAN KATEGORI -->
<?php if (empty($daftarKategori)): ?>
  <div class="card p-5 border-0 shadow-sm bg-white text-center">
    <i class="bi bi-clipboard-minus text-muted" style="font-size: 3rem; display: block; margin-bottom: 0.5rem;"></i>
    <h6 class="fw-bold mb-1">Belum ada parameter check</h6>
    <p class="text-muted small mb-0">Belum ada parameter yang didefinisikan untuk <strong><?= esc($departemen) ?> (<?= esc($jenisCheck) ?>)</strong>.</p>
  </div>
<?php else: ?>
  <?php 
    $activeKategori = $selectedKategori ?? ($daftarKategori[0] ?? null);
  ?>

  <!-- TABS PILIHAN KATEGORI (Penerangan, Kabel & Pipa, etc.) -->
  <div class="d-flex flex-wrap gap-2 mb-4 border-bottom pb-3">
    <?php foreach ($daftarKategori as $katName): ?>
      <?php
        $isKatActive = ($activeKategori === $katName);
        $katBtnClass = $isKatActive ? 'btn-primary' : 'btn-light text-dark';
      ?>
      <a href="<?= site_url("admin/parameter?departemen=" . urlencode($departemen) . "&jenis_check=" . urlencode($jenisCheck) . "&kategori=" . urlencode($katName)) ?>" class="btn btn-sm <?= $katBtnClass ?> px-3 py-2.5 fw-semibold rounded-3 shadow-none">
        <i class="bi bi-folder2-open me-1"></i> <?= esc($katName) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if ($activeKategori): ?>
    <?php $params = $kategoriParameters[$activeKategori]; ?>
    <div class="card border-0 shadow-sm bg-white mb-4 overflow-hidden">
      <!-- Header Kategori -->
      <div class="card-header bg-light border-0 py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
          <i class="bi bi-folder-fill text-warning"></i> <?= esc($activeKategori) ?>
          <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem;"><?= count($params) ?> Baris</span>
        </h6>
        <a href="<?= site_url('admin/parameter/create?departemen=' . urlencode($departemen) . '&jenis_check=' . urlencode($jenisCheck) . '&kategori=' . urlencode($activeKategori)) ?>" class="btn btn-xs btn-outline-primary py-1.5 px-2.5 fw-semibold" style="font-size: 0.72rem;">
          <i class="bi bi-plus-lg"></i> Tambah Item ke Kategori ini
        </a>
      </div>

      <!-- Tabel Parameter Sesuai Form -->
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table align-middle mb-0" style="font-size: 0.825rem;">
            <?php if (strtolower($jenisCheck) === 'overhaul'): ?>
              <!-- OVERHAUL STYLE TABLE -->
              <thead>
                <tr class="table-light">
                  <th style="width: 5%;" class="ps-4 fw-bold text-uppercase text-secondary text-center">NO</th>
                  <th style="width: 15%;" class="fw-bold text-uppercase text-secondary">Section</th>
                  <th colspan="2" style="width: 38%;" class="fw-bold text-uppercase text-secondary">Item Check</th>
                  <th style="width: 17%;" class="fw-bold text-uppercase text-secondary">Point Check</th>
                  <?php if (!($departemen === 'MFG 2' && strtolower($jenisCheck) === 'overhaul')): ?>
                  <th style="width: 12%;" class="fw-bold text-uppercase text-secondary">Standar Item</th>
                  <?php endif; ?>
                  <th style="width: 5%;" class="fw-bold text-uppercase text-secondary text-center">Urut</th>
                  <th style="width: 8%;" class="pe-4 fw-bold text-uppercase text-secondary text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($params as $p): ?>
                  <?php if (isset($p['is_section_start']) && $p['is_section_start']): ?>
                    <tr style="background-color: #f8fafc; font-weight: 700; border-left: 4px solid var(--accent);">
                      <?php $colSpan = ($departemen === 'MFG 2' && strtolower($jenisCheck) === 'overhaul') ? 7 : 8; ?>
                      <td colspan="<?= $colSpan ?>" class="ps-4 text-primary font-monospace py-2" style="font-size: 0.8rem; letter-spacing: 0.05em; text-transform: uppercase;">
                        [SECTION] <?= esc($p['section_check']) ?>
                      </td>
                    </tr>
                  <?php endif; ?>
                  <tr>
                    <?php if ($p['show_no']): ?>
                      <td class="text-center fw-semibold text-muted font-monospace ps-4" rowspan="<?= (int) $p['no_rowspan'] ?>">
                        <?= esc($p['dynamic_no']) ?>
                      </td>
                    <?php endif; ?>

                    <td class="text-muted"><?= esc($p['section_check'] ?: '-') ?></td>

                    <?php if ($p['sub_item_check']): ?>
                      <?php if ($p['show_bagian']): ?>
                        <td class="fw-semibold text-dark" rowspan="<?= (int) $p['bagian_rowspan'] ?>">
                          <?= esc($p['bagian_check']) ?>
                        </td>
                      <?php endif; ?>
                      <td><?= esc($p['sub_item_check']) ?></td>
                    <?php else: ?>
                      <td class="fw-semibold text-dark" colspan="2">
                        <?= esc($p['bagian_check']) ?>
                      </td>
                    <?php endif; ?>

                    <?php if ($p['show_point']): ?>
                      <td rowspan="<?= (int) $p['point_rowspan'] ?>"><?= esc($p['point_check']) ?></td>
                    <?php endif; ?>

                    <?php if (!($departemen === 'MFG 2' && strtolower($jenisCheck) === 'overhaul')): ?>
                    <?php if ($p['show_standard']): ?>
                      <td rowspan="<?= (int) $p['standard_rowspan'] ?>"><?= nl2br(esc($p['standard_check'])) ?></td>
                    <?php endif; ?>
                    <?php endif; ?>

                    <td class="text-center font-monospace text-muted"><?= (int) $p['urutan'] ?></td>
                    <td class="pe-4 text-end">
                      <div class="d-inline-flex gap-1">
                        <a href="<?= site_url('admin/parameter/edit/' . $p['id_parameter']) ?>" class="btn btn-sm btn-outline-secondary py-0.5 px-2" style="font-size: 0.72rem;">Edit</a>
                        <a href="<?= site_url('admin/parameter/delete/' . $p['id_parameter']) ?>" class="btn btn-sm btn-outline-danger py-0.5 px-2" style="font-size: 0.72rem;" onclick="return confirm('Hapus parameter ini?');">Hapus</a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            <?php else: ?>
              <!-- PREVENTIVE STYLE TABLE -->
              <thead>
                <tr class="table-light">
                  <th style="width: 25%;" class="ps-4 fw-bold text-uppercase text-secondary">Bagian Check</th>
                  <th style="width: 25%;" class="fw-bold text-uppercase text-secondary">Point Check</th>
                  <th style="width: 30%;" class="fw-bold text-uppercase text-secondary">Standard Check</th>
                  <th style="width: 10%;" class="fw-bold text-uppercase text-secondary text-center">Urutan</th>
                  <th style="width: 10%;" class="pe-4 fw-bold text-uppercase text-secondary text-end">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($params as $p): ?>
                  <tr>
                    <?php if ($p['show_bagian']): ?>
                      <td class="ps-4 fw-semibold text-dark" rowspan="<?= (int) $p['bagian_rowspan'] ?>">
                        <?= esc($p['bagian_check']) ?>
                      </td>
                    <?php endif; ?>

                    <?php if ($p['show_point']): ?>
                      <td rowspan="<?= (int) $p['point_rowspan'] ?>"><?= esc($p['point_check']) ?></td>
                    <?php endif; ?>

                    <td><?= nl2br(esc($p['standard_check'])) ?></td>
                    <td class="text-center font-monospace text-muted"><?= (int) $p['urutan'] ?></td>
                    <td class="pe-4 text-end">
                      <div class="d-inline-flex gap-1">
                        <a href="<?= site_url('admin/parameter/edit/' . $p['id_parameter']) ?>" class="btn btn-sm btn-outline-secondary py-0.5 px-2" style="font-size: 0.72rem;">Edit</a>
                        <a href="<?= site_url('admin/parameter/delete/' . $p['id_parameter']) ?>" class="btn btn-sm btn-outline-danger py-0.5 px-2" style="font-size: 0.72rem;" onclick="return confirm('Hapus parameter ini?');">Hapus</a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            <?php endif; ?>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?= view('layout/footer') ?>
