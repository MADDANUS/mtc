<?= view('layout/header', ['title' => $title]) ?>

<style>
  .form-header-box { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; }
  .keterangan-box { background:#fff; border:1px solid #dee2e6; border-radius:.5rem; }
  .keterangan-box table td { padding:.25rem .5rem; }

  /* Memaksa tabel agar tidak menyusut terlalu kecil di layar HP sehingga bisa digeser (swipe) */
  .checklist-table { min-width: 850px; }
</style>

<div class="page-header d-flex align-items-center">
  <div class="d-flex align-items-center gap-3">
    <?php
      if (isset($isEdit) && $isEdit) {
          if (isset($_GET['from']) && $_GET['from'] === 'approval') {
              $backUrl = site_url('approval');
          } else {
              $qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '?jenis_check=' . urlencode($jenisName);
              $realLokSlug = isset($_GET['from_lokasi']) ? $_GET['from_lokasi'] : $lokasiSlug;
              $backUrl = site_url('riwayat/lokasi/' . $realLokSlug . $qs);
          }
      } else {
          if (!empty($idMesin)) {
              if (strtolower($jenisSlug) === 'overhaul') {
                  $backUrl = site_url("scan/mesin/{$idMesin}");
              } else {
                  $backUrl = site_url("checklist/{$lokasiSlug}/{$jenisSlug}?id_mesin={$idMesin}");
              }
          } else {
              $backUrl = strtolower($jenisSlug) === 'overhaul' 
                  ? site_url("checklist") 
                  : site_url("checklist/{$lokasiSlug}/{$jenisSlug}");
          }
      }
    ?>
    <a href="<?= $backUrl ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <h5 class="mb-0">
      <i class="bi bi-clipboard-check me-2" style="color:var(--accent);"></i>Pengecekan <?= esc($jenisName) ?> — <strong><?= esc($categoryName) ?></strong> <span class="badge bg-secondary ms-1" style="font-size:0.7rem;"><?= esc($lokasiName) ?></span>
    </h5>
  </div>
</div>

<?php
$isEdit = $isEdit ?? false;
$editUrl = $isEdit ? site_url("riwayat/update/{$idTransaksi}") : site_url("checklist/{$lokasiSlug}/{$jenisSlug}/store");
?>

<form id="checklistForm" action="<?= $editUrl ?>" method="post" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>

  <!-- HEADER FORM: Mesin, Staff, Waktu Mulai -->
  <div class="form-header-box p-3 mb-3 shadow-sm border-0 bg-white">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">No Mesin (<?= esc($lokasiName) ?>)</label>
        <select name="id_mesin" id="id_mesin" class="form-select searchable-select" required <?= !empty($idMesin) ? 'disabled' : '' ?>>
          <option value="">-- Cari Mesin --</option>
          <?php foreach ($daftarMesin as $m): ?>
            <option value="<?= esc($m['id_mesin']) ?>" data-bar-feeder="<?= esc($m['bar_feeder_type'] ?? '') ?>" <?= (!empty($idMesin) && (int)$idMesin === (int)$m['id_mesin']) ? 'selected' : '' ?>>
              <?= esc($m['no_mesin']) ?> - <?= esc($m['type_mesin']) ?> - <?= esc($m['serial_nomor']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (!empty($idMesin)): ?>
          <input type="hidden" name="id_mesin" value="<?= (int)$idMesin ?>">
        <?php endif; ?>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">PIC</label>
        <select name="nama_pic" class="form-select searchable-select" required>
          <option value="">-- Cari PIC --</option>
          <?php if (isset($masterPic) && !empty($masterPic)): ?>
            <?php foreach ($masterPic as $pic): ?>
              <?php $picVal = esc($pic['id_pic'] . ' - ' . $pic['nama_pic']); ?>
              <option value="<?= $picVal ?>" <?= (isset($namaPic) && $namaPic === $picVal) ? 'selected' : '' ?>><?= $picVal ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Waktu Mulai</label>
        <input type="text" id="displayWaktuMulai" class="form-control" value="<?= esc($waktuMulaiDisplay) ?>" readonly>
        <!-- waktu_mulai dikirim apa adanya ke Controller store() saat submit -->
        <input type="hidden" name="waktu_mulai" value="<?= esc($waktuMulai) ?>">
        <input type="hidden" name="kategori" value="<?= esc($categoryName) ?>">
      </div>
      
      <?php if (strtolower($jenisSlug) === 'overhaul'): ?>
        <div id="overhaulAdditionalFields" class="col-12 p-0 m-0 d-none mt-3">
          <div class="row g-3 mt-0">
            <?php if (strtolower($lokasiSlug) === 'mfg1'): ?>
              <div class="col-md-6">
                <label class="form-label fw-semibold text-primary">Bar Feeder Type</label>
                <input type="text" name="bar_feeder_type" id="barFeederInput" class="form-control border-primary bg-primary bg-opacity-10" placeholder="Otomatis terisi dari master mesin..." value="<?= esc($barFeederType ?? '') ?>" readonly>
              </div>
            <?php endif; ?>
            <div class="col-md-<?= strtolower($lokasiSlug) === 'mfg1' ? '6' : '12' ?>">
              <label class="form-label fw-semibold text-primary">Support PIC (Maksimal 4 Orang)</label>
              <?php 
                $arrSupport = array_filter(array_map('trim', explode(',', $supportPic ?? '')));
              ?>
              <div class="col-12">
                <select name="support_pic[]" class="form-select searchable-select border-primary bg-primary bg-opacity-10" multiple data-max-items="4" data-placeholder="Pilih maksimal 4 PIC Support...">
                  <?php foreach ($masterPic as $pic): ?>
                    <?php $selected = in_array($pic['nama_pic'], $arrSupport) ? 'selected' : ''; ?>
                    <option value="<?= esc($pic['nama_pic']) ?>" <?= $selected ?>>
                      <?= esc($pic['nama_pic']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>
        </div>

      <?php endif; ?>
    </div>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
      const selectMesin = document.getElementById('id_mesin');
      if (!selectMesin) return;

      const inputBarFeeder = document.getElementById('barFeederInput');
      const additionalFields = document.getElementById('overhaulAdditionalFields');

      function updateFields() {
          if (additionalFields) {
              if (selectMesin.value && selectMesin.value !== "") {
                  additionalFields.classList.remove('d-none');
                  if (inputBarFeeder && selectMesin.selectedIndex > 0) {
                      const selectedOption = selectMesin.options[selectMesin.selectedIndex];
                      const barFeeder = selectedOption.getAttribute('data-bar-feeder');
                      inputBarFeeder.value = barFeeder || '';
                  }
              } else {
                  additionalFields.classList.add('d-none');
                  if (inputBarFeeder) inputBarFeeder.value = '';
              }
          }
      }

      function checkDuplicateOnChange() {
          const idMesin = selectMesin.value;
          if (!idMesin) return;

          const jenisCheck = "<?= esc($jenisSlug) ?>";
          const kategori  = "<?= esc($categorySlug ?? '') ?>";

          fetch('<?= site_url("checklist/check-duplicate") ?>', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  'X-Requested-With': 'XMLHttpRequest'
              },
              body: 'id_mesin=' + encodeURIComponent(idMesin)
                    + '&jenis_check=' + encodeURIComponent(jenisCheck)
                    + '&kategori='   + encodeURIComponent(kategori)
          })
          .then(res => {
              if (!res.ok) throw new Error('HTTP ' + res.status);
              return res.json();
          })
          .then(data => {
              if (data.duplicate) {
                  Swal.fire({
                      icon: 'warning',
                      title: 'Sudah Pernah Dicek!',
                      html: `
                          <p>Mesin ini sudah pernah dilakukan pengecekan <strong>${data.kategori || jenisCheck}</strong> pada bulan ini.</p>
                          <table class="table table-sm table-bordered mt-2 text-start" style="font-size:0.9rem;">
                              <tr><td class="fw-semibold" style="width:40%">Tanggal & Waktu</td><td>${data.tanggal || '-'}</td></tr>
                              <tr><td class="fw-semibold">PIC</td><td>${data.pic || '-'}</td></tr>
                          </table>
                          <p class="text-muted mt-2" style="font-size:0.85rem;">Apakah Anda yakin ingin mengisi form pengecekan lagi?</p>
                      `,
                      showCancelButton: true,
                      confirmButtonText: 'Ya, Lanjutkan',
                      cancelButtonText: 'Batal',
                      confirmButtonColor: '#0d6efd',
                      cancelButtonColor: '#dc3545',
                      allowOutsideClick: false
                  }).then(function(result) {
                      if (!result.isConfirmed) {
                          if (!selectMesin.disabled) {
                              if (selectMesin.tomselect) {
                                  selectMesin.tomselect.clear(true);
                              } else {
                                  selectMesin.value = '';
                              }
                          } else {
                              window.location.href = '<?= site_url("checklist") ?>';
                          }
                      }
                  });
              }
          })
          .catch(function(err) {
              console.error('Duplicate check error:', err);
          });
      }

      // TomSelect menggantikan <select> asli — event change biasa tidak aktif.
      // Kita harus tunggu TomSelect selesai init dulu.
      function bindEvents() {
          if (selectMesin.tomselect) {
              // Lepas listener lama supaya tidak dobel
              selectMesin.tomselect.off('change');
              selectMesin.tomselect.on('change', function(value) {
                  updateFields();
                  if (value) checkDuplicateOnChange();
              });
          } else {
              selectMesin.addEventListener('change', function() {
                  updateFields();
                  checkDuplicateOnChange();
              });
          }
      }

      // Tunggu TomSelect benar-benar selesai init (polling loop, max 3 detik)
      let _bindAttempts = 0;
      const _bindInterval = setInterval(function() {
          _bindAttempts++;
          if (selectMesin.tomselect) {
              clearInterval(_bindInterval);
              bindEvents();
              // Cek duplikat jika mesin sudah dipilih sejak awal
              if (selectMesin.value) {
                  updateFields();
                  checkDuplicateOnChange();
              }
          } else if (_bindAttempts > 30) { // max 3 detik (30 × 100ms)
              clearInterval(_bindInterval);
              // Fallback: pakai native change event
              selectMesin.addEventListener('change', function() {
                  updateFields();
                  checkDuplicateOnChange();
              });
          }
      }, 100);
  });
  </script>

  <div class="row g-3">
    <!-- TABEL CHECKLIST -->
    <div class="col-12 col-lg-9 order-2 order-lg-1" style="overflow: hidden;">
      <?php if (empty($rows)): ?>
        <div class="alert alert-info">Belum ada parameter check yang didefinisikan untuk kategori ini.</div>
      <?php else: ?>
        <?php if (strtolower($jenisSlug) === 'overhaul'): ?>
          <!-- OVERHAUL TABLE -->
          <div class="table-responsive">
            <table class="table table-bordered align-middle checklist-table bg-white shadow-sm rounded">
              <thead>
                <tr>
                  <th style="width:5%;">NO</th>
                  <th colspan="2" style="width:30%;">ITEM CHECK</th>
                  <th style="width:20%;">POINT CHECK</th>
                  <?php if (strtolower($lokasiSlug) !== 'mfg2'): ?>
                    <th style="width:15%;">STANDAR ITEM</th>
                  <?php endif; ?>
                  <th style="width:12%;">CHECK LIST</th>
                  <th style="<?= strtolower($lokasiSlug) === 'mfg2' ? 'width:33%;' : 'width:18%;' ?>">REMARK</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                  $itemIndex = 0;
                  $perPage = 49;
                ?>
                <?php foreach ($rows as $r): ?>
                  <?php 
                    $itemIndex++;
                    if (strtolower($categorySlug) === 'double-milling') {
                        if ($itemIndex <= 36) $pageNo = 1;
                        else $pageNo = 2;
                    } elseif (strtolower($categorySlug) === 'double-center-drill') {
                        if ($itemIndex <= 40) $pageNo = 1;
                        else $pageNo = 2;
                    } elseif (strtolower($categorySlug) === 'centering-grinding') {
                        if ($itemIndex <= 32) $pageNo = 1;
                        elseif ($itemIndex <= 72) $pageNo = 2;
                        else $pageNo = 3;
                    } else {
                        $pageNo = ceil($itemIndex / $perPage);
                    }
                    $rowCategory = $r['kategori'] ?? ''; 
                  ?>
                  <?php if ($r['is_section_start']): ?>
                    <tr class="section-header page-row-mfg2" data-page="<?= $pageNo ?>" data-kategori="<?= esc($rowCategory) ?>" style="background-color: #ffffff; font-weight: 700;">
                      <td colspan="7" class="py-2 px-3" style="color: #000000; font-size: 0.9rem; letter-spacing: 0.05em; text-transform: uppercase;">
                        <?= esc($r['dynamic_section_header']) ?>
                      </td>
                    </tr>
                  <?php endif; ?>
                  <tr class="page-row-mfg2" data-page="<?= $pageNo ?>" data-kategori="<?= esc($rowCategory) ?>">
                    <?php if ($r['show_no']): ?>
                      <td class="text-center fw-semibold text-muted" rowspan="<?= (int) $r['no_rowspan'] ?>"><?= esc($r['dynamic_no']) ?></td>
                    <?php endif; ?>

                    <?php if ($r['sub_item_check']): ?>
                      <?php if ($r['show_bagian']): ?>
                        <td class="bagian-cell" rowspan="<?= (int) $r['bagian_rowspan'] ?>"><?= esc($r['bagian_check']) ?></td>
                      <?php endif; ?>
                      <td><?= esc($r['sub_item_check']) ?></td>
                    <?php else: ?>
                      <td class="bagian-cell" colspan="2"><?= esc($r['bagian_check']) ?></td>
                    <?php endif; ?>

                    <?php if ($r['show_point']): ?>
                      <td rowspan="<?= (int) $r['point_rowspan'] ?>"><?= esc($r['point_check']) ?></td>
                    <?php endif; ?>

                    <?php if (strtolower($lokasiSlug) !== 'mfg2'): ?>
                      <?php if ($r['show_standard']): ?>
                        <td rowspan="<?= (int) $r['standard_rowspan'] ?>"><?= nl2br(esc($r['standard_check'])) ?></td>
                      <?php endif; ?>
                    <?php endif; ?>

                    <td>
                      <?php
                      $h = $detailsMap[$r['id_parameter']]['hasil_check'] ?? '';
                      $u = $detailsMap[$r['id_parameter']]['ulasan'] ?? '';
                      ?>
                      <div class="d-flex">
                        <div class="form-check form-check-inline">
                          <input class="form-check-input hasil-check-radio" type="radio"
                                 name="hasil_check[<?= (int) $r['id_parameter'] ?>]"
                                 id="v_<?= (int) $r['id_parameter'] ?>" value="V" <?= $h === 'V' ? 'checked' : '' ?> required
                                 data-param-id="<?= (int) $r['id_parameter'] ?>">
                          <label class="form-check-label text-success fw-bold" for="v_<?= (int) $r['id_parameter'] ?>">V</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input hasil-check-radio" type="radio"
                                 name="hasil_check[<?= (int) $r['id_parameter'] ?>]"
                                 id="d_<?= (int) $r['id_parameter'] ?>" value="Δ" <?= $h === 'Δ' ? 'checked' : '' ?> required
                                 data-param-id="<?= (int) $r['id_parameter'] ?>">
                          <label class="form-check-label text-warning fw-bold" for="d_<?= (int) $r['id_parameter'] ?>">Δ</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input hasil-check-radio" type="radio"
                                 name="hasil_check[<?= (int) $r['id_parameter'] ?>]"
                                 id="x_<?= (int) $r['id_parameter'] ?>" value="X" <?= $h === 'X' ? 'checked' : '' ?> required
                                 data-param-id="<?= (int) $r['id_parameter'] ?>">
                          <label class="form-check-label text-danger fw-bold" for="x_<?= (int) $r['id_parameter'] ?>">X</label>
                        </div>
                      </div>
                      <!-- Foto Abnormal (muncul saat Δ dipilih) -->
                      <div class="foto-abnormal-wrap mt-2" id="foto-wrap-<?= (int) $r['id_parameter'] ?>" style="display:<?= $h === 'Δ' ? 'block' : 'none' ?>; background: #fffcf2; border: 1px dashed #ffc107; padding: 8px; border-radius: 6px;">
                        <!-- FOTO 1 (Wajib) -->
                        <div class="mb-2">
                            <?php 
                            $f1 = $detailsMap[$r['id_parameter']]['foto_abnormal'] ?? null;
                            $hasF1 = !empty($f1);
                            $f1Url = $hasF1 ? base_url('uploads/abnormal/' . $f1) : '';
                            ?>
                            <input type="file" class="d-none foto-abnormal-input"
                                   name="foto_abnormal[<?= (int) $r['id_parameter'] ?>]"
                                   id="foto-input-1-<?= (int) $r['id_parameter'] ?>" accept="image/jpeg" <?= ($h === 'Δ' && !$hasF1) ? 'required' : '' ?>>
                            <button type="button" class="btn btn-sm btn-warning w-100 btn-ambil-foto main-btn-foto" id="btn-ambil-1-<?= (int) $r['id_parameter'] ?>"
                                    data-target-input="foto-input-1-<?= (int) $r['id_parameter'] ?>"
                                    data-target-preview="foto-preview-1-<?= (int) $r['id_parameter'] ?>"
                                    data-target-btn="btn-ambil-1-<?= (int) $r['id_parameter'] ?>"
                                    style="display: <?= $hasF1 ? 'none' : 'block' ?>;">
                              <i class="bi bi-camera-fill me-1"></i> Foto 1 <span class="text-danger">*</span>
                            </button>
                            <div class="foto-preview mt-1" id="foto-preview-1-<?= (int) $r['id_parameter'] ?>" style="display: <?= $hasF1 ? 'block' : 'none' ?>; position:relative;">
                              <img src="<?= $f1Url ?>" alt="Preview 1" class="preview-img-click" style="max-width:100%; max-height:100px; border-radius:4px; border:2px solid #ffc107; display:block; cursor:pointer;" title="Klik untuk memperbesar">
                              <div class="d-flex gap-1 mt-1">
                                  <button type="button" class="btn btn-xs btn-outline-warning btn-ambil-foto flex-grow-1"
                                          data-target-input="foto-input-1-<?= (int) $r['id_parameter'] ?>"
                                          data-target-preview="foto-preview-1-<?= (int) $r['id_parameter'] ?>"
                                          data-target-btn="btn-ambil-1-<?= (int) $r['id_parameter'] ?>">
                                    <i class="bi bi-arrow-repeat me-1"></i> Ulang
                                  </button>
                                  <button type="button" class="btn btn-xs btn-outline-danger btn-hapus-foto"
                                          data-target-input="foto-input-1-<?= (int) $r['id_parameter'] ?>"
                                          data-target-preview="foto-preview-1-<?= (int) $r['id_parameter'] ?>"
                                          data-target-btn="btn-ambil-1-<?= (int) $r['id_parameter'] ?>">
                                    <i class="bi bi-trash"></i> Hapus
                                  </button>
                              </div>
                            </div>
                        </div>
                        <!-- FOTO 2 (Opsional) -->
                        <div>
                            <?php 
                            $f2 = $detailsMap[$r['id_parameter']]['foto_abnormal_2'] ?? null;
                            $hasF2 = !empty($f2);
                            $f2Url = $hasF2 ? base_url('uploads/abnormal/' . $f2) : '';
                            ?>
                            <input type="file" class="d-none"
                                   name="foto_abnormal_2[<?= (int) $r['id_parameter'] ?>]"
                                   id="foto-input-2-<?= (int) $r['id_parameter'] ?>" accept="image/jpeg">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 btn-ambil-foto main-btn-foto" id="btn-ambil-2-<?= (int) $r['id_parameter'] ?>"
                                    data-target-input="foto-input-2-<?= (int) $r['id_parameter'] ?>"
                                    data-target-preview="foto-preview-2-<?= (int) $r['id_parameter'] ?>"
                                    data-target-btn="btn-ambil-2-<?= (int) $r['id_parameter'] ?>"
                                    style="display: <?= $hasF2 ? 'none' : 'block' ?>;">
                              <i class="bi bi-camera me-1"></i> Foto 2 (Ops)
                            </button>
                            <div class="foto-preview mt-1" id="foto-preview-2-<?= (int) $r['id_parameter'] ?>" style="display: <?= $hasF2 ? 'block' : 'none' ?>; position:relative;">
                              <img src="<?= $f2Url ?>" alt="Preview 2" class="preview-img-click" style="max-width:100%; max-height:100px; border-radius:4px; border:2px solid #6c757d; display:block; cursor:pointer;" title="Klik untuk memperbesar">
                              <div class="d-flex gap-1 mt-1">
                                  <button type="button" class="btn btn-xs btn-outline-secondary btn-ambil-foto flex-grow-1"
                                          data-target-input="foto-input-2-<?= (int) $r['id_parameter'] ?>"
                                          data-target-preview="foto-preview-2-<?= (int) $r['id_parameter'] ?>"
                                          data-target-btn="btn-ambil-2-<?= (int) $r['id_parameter'] ?>">
                                    <i class="bi bi-arrow-repeat me-1"></i> Ulang
                                  </button>
                                  <button type="button" class="btn btn-xs btn-outline-danger btn-hapus-foto"
                                          data-target-input="foto-input-2-<?= (int) $r['id_parameter'] ?>"
                                          data-target-preview="foto-preview-2-<?= (int) $r['id_parameter'] ?>"
                                          data-target-btn="btn-ambil-2-<?= (int) $r['id_parameter'] ?>">
                                    <i class="bi bi-trash"></i> Hapus
                                  </button>
                              </div>
                            </div>
                        </div>
                      </div>
                    </td>

                    <td>
                      <textarea class="form-control form-control-sm"
                                name="ulasan[<?= (int) $r['id_parameter'] ?>]"
                                placeholder="Tulis ulasan/keterangan..."
                                rows="1"
                                style="min-height: 38px; resize: vertical; font-size: 0.85rem;"><?= esc($u) ?></textarea>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <!-- PREVENTIVE TABLE -->
          <div class="table-responsive">
            <table class="table table-bordered align-middle checklist-table bg-white shadow-sm rounded">
              <thead>
                <tr>
                  <th style="width:15%;">BAGIAN CHECK</th>
                  <th style="width:20%;">POINT CHECK</th>
                  <th style="width:20%;">STANDARD CHECK</th>
                  <th style="width:15%;">CHECK LIST</th>
                  <th style="width:30%;">ULASAN</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $r): ?>
                  <tr>
                    <?php if ($r['show_bagian']): ?>
                      <td class="bagian-cell" rowspan="<?= (int) $r['bagian_rowspan'] ?>"><?= esc($r['bagian_check']) ?></td>
                    <?php endif; ?>

                    <?php if ($r['show_point']): ?>
                      <td rowspan="<?= (int) $r['point_rowspan'] ?>"><?= esc($r['point_check']) ?></td>
                    <?php endif; ?>

                    <td><?= esc($r['standard_check']) ?></td>

                    <td>
                      <?php
                      $h = $detailsMap[$r['id_parameter']]['hasil_check'] ?? '';
                      $u = $detailsMap[$r['id_parameter']]['ulasan'] ?? '';
                      ?>
                      <div class="d-flex">
                        <div class="form-check form-check-inline">
                          <input class="form-check-input hasil-check-radio" type="radio"
                                 name="hasil_check[<?= (int) $r['id_parameter'] ?>]"
                                 id="v_<?= (int) $r['id_parameter'] ?>" value="V" <?= $h === 'V' ? 'checked' : '' ?> required
                                 data-param-id="<?= (int) $r['id_parameter'] ?>">
                          <label class="form-check-label text-success fw-bold" for="v_<?= (int) $r['id_parameter'] ?>">V</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input hasil-check-radio" type="radio"
                                 name="hasil_check[<?= (int) $r['id_parameter'] ?>]"
                                 id="d_<?= (int) $r['id_parameter'] ?>" value="Δ" <?= $h === 'Δ' ? 'checked' : '' ?> required
                                 data-param-id="<?= (int) $r['id_parameter'] ?>">
                          <label class="form-check-label text-warning fw-bold" for="d_<?= (int) $r['id_parameter'] ?>">Δ</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input hasil-check-radio" type="radio"
                                 name="hasil_check[<?= (int) $r['id_parameter'] ?>]"
                                 id="x_<?= (int) $r['id_parameter'] ?>" value="X" <?= $h === 'X' ? 'checked' : '' ?> required
                                 data-param-id="<?= (int) $r['id_parameter'] ?>">
                          <label class="form-check-label text-danger fw-bold" for="x_<?= (int) $r['id_parameter'] ?>">X</label>
                        </div>
                      </div>
                      <!-- Foto Abnormal (muncul saat Δ dipilih) -->
                      <div class="foto-abnormal-wrap mt-2" id="foto-wrap-<?= (int) $r['id_parameter'] ?>" style="display:<?= $h === 'Δ' ? 'block' : 'none' ?>; background: #fffcf2; border: 1px dashed #ffc107; padding: 8px; border-radius: 6px;">
                        <!-- FOTO 1 (Wajib) -->
                        <div class="mb-2">
                            <?php 
                            $f1 = $detailsMap[$r['id_parameter']]['foto_abnormal'] ?? null;
                            $hasF1 = !empty($f1);
                            $f1Url = $hasF1 ? base_url('uploads/abnormal/' . $f1) : '';
                            ?>
                            <input type="file" class="d-none foto-abnormal-input"
                                   name="foto_abnormal[<?= (int) $r['id_parameter'] ?>]"
                                   id="foto-input-1-<?= (int) $r['id_parameter'] ?>" accept="image/jpeg" <?= ($h === 'Δ' && !$hasF1) ? 'required' : '' ?>>
                            <button type="button" class="btn btn-sm btn-warning w-100 btn-ambil-foto main-btn-foto" id="btn-ambil-1-<?= (int) $r['id_parameter'] ?>"
                                    data-target-input="foto-input-1-<?= (int) $r['id_parameter'] ?>"
                                    data-target-preview="foto-preview-1-<?= (int) $r['id_parameter'] ?>"
                                    data-target-btn="btn-ambil-1-<?= (int) $r['id_parameter'] ?>"
                                    style="display: <?= $hasF1 ? 'none' : 'block' ?>;">
                              <i class="bi bi-camera-fill me-1"></i> Foto 1 <span class="text-danger">*</span>
                            </button>
                            <div class="foto-preview mt-1" id="foto-preview-1-<?= (int) $r['id_parameter'] ?>" style="display: <?= $hasF1 ? 'block' : 'none' ?>; position:relative;">
                              <img src="<?= $f1Url ?>" alt="Preview 1" class="preview-img-click" style="max-width:100%; max-height:100px; border-radius:4px; border:2px solid #ffc107; display:block; cursor:pointer;" title="Klik untuk memperbesar">
                              <div class="d-flex gap-1 mt-1">
                                  <button type="button" class="btn btn-xs btn-outline-warning btn-ambil-foto flex-grow-1"
                                          data-target-input="foto-input-1-<?= (int) $r['id_parameter'] ?>"
                                          data-target-preview="foto-preview-1-<?= (int) $r['id_parameter'] ?>"
                                          data-target-btn="btn-ambil-1-<?= (int) $r['id_parameter'] ?>">
                                    <i class="bi bi-arrow-repeat me-1"></i> Ulang
                                  </button>
                                  <button type="button" class="btn btn-xs btn-outline-danger btn-hapus-foto"
                                          data-target-input="foto-input-1-<?= (int) $r['id_parameter'] ?>"
                                          data-target-preview="foto-preview-1-<?= (int) $r['id_parameter'] ?>"
                                          data-target-btn="btn-ambil-1-<?= (int) $r['id_parameter'] ?>">
                                    <i class="bi bi-trash"></i> Hapus
                                  </button>
                              </div>
                            </div>
                        </div>
                        <!-- FOTO 2 (Opsional) -->
                        <div>
                            <?php 
                            $f2 = $detailsMap[$r['id_parameter']]['foto_abnormal_2'] ?? null;
                            $hasF2 = !empty($f2);
                            $f2Url = $hasF2 ? base_url('uploads/abnormal/' . $f2) : '';
                            ?>
                            <input type="file" class="d-none"
                                   name="foto_abnormal_2[<?= (int) $r['id_parameter'] ?>]"
                                   id="foto-input-2-<?= (int) $r['id_parameter'] ?>" accept="image/jpeg">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 btn-ambil-foto main-btn-foto" id="btn-ambil-2-<?= (int) $r['id_parameter'] ?>"
                                    data-target-input="foto-input-2-<?= (int) $r['id_parameter'] ?>"
                                    data-target-preview="foto-preview-2-<?= (int) $r['id_parameter'] ?>"
                                    data-target-btn="btn-ambil-2-<?= (int) $r['id_parameter'] ?>"
                                    style="display: <?= $hasF2 ? 'none' : 'block' ?>;">
                              <i class="bi bi-camera me-1"></i> Foto 2 (Ops)
                            </button>
                            <div class="foto-preview mt-1" id="foto-preview-2-<?= (int) $r['id_parameter'] ?>" style="display: <?= $hasF2 ? 'block' : 'none' ?>; position:relative;">
                              <img src="<?= $f2Url ?>" alt="Preview 2" class="preview-img-click" style="max-width:100%; max-height:100px; border-radius:4px; border:2px solid #6c757d; display:block; cursor:pointer;" title="Klik untuk memperbesar">
                              <div class="d-flex gap-1 mt-1">
                                  <button type="button" class="btn btn-xs btn-outline-secondary btn-ambil-foto flex-grow-1"
                                          data-target-input="foto-input-2-<?= (int) $r['id_parameter'] ?>"
                                          data-target-preview="foto-preview-2-<?= (int) $r['id_parameter'] ?>"
                                          data-target-btn="btn-ambil-2-<?= (int) $r['id_parameter'] ?>">
                                    <i class="bi bi-arrow-repeat me-1"></i> Ulang
                                  </button>
                                  <button type="button" class="btn btn-xs btn-outline-danger btn-hapus-foto"
                                          data-target-input="foto-input-2-<?= (int) $r['id_parameter'] ?>"
                                          data-target-preview="foto-preview-2-<?= (int) $r['id_parameter'] ?>"
                                          data-target-btn="btn-ambil-2-<?= (int) $r['id_parameter'] ?>">
                                    <i class="bi bi-trash"></i> Hapus
                                  </button>
                              </div>
                            </div>
                        </div>
                      </div>
                    </td>

                    <td>
                      <textarea class="form-control form-control-sm"
                                name="ulasan[<?= (int) $r['id_parameter'] ?>]"
                                placeholder="Tulis ulasan/keterangan..."
                                rows="1"
                                style="min-height: 38px; resize: vertical; font-size: 0.85rem;"><?= esc($u) ?></textarea>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (strtolower($jenisSlug) === 'overhaul'): ?>
        <div class="mt-4 border rounded p-3 bg-light shadow-sm">
          <label class="form-label fw-bold text-secondary mb-2" style="letter-spacing: 0.5px;">NOTE AND RECOMMENDATION</label>
          <textarea name="note_recommendation" class="form-control" rows="4" placeholder="Ketikkan catatan atau rekomendasi di sini..."><?= esc($noteRecommendation ?? '') ?></textarea>
        </div>
      <?php endif; ?>
    </div>

    <!-- KETERANGAN CHECK LIST -->
    <div class="col-12 col-lg-3 order-1 order-lg-2">
      <div class="keterangan-box p-3 shadow-sm mb-3">
        <div class="fw-semibold mb-2 text-dark border-bottom pb-2">KETERANGAN CHECK LIST</div>
        <table class="table table-sm mb-0">
          <tr><td class="fw-bold text-success">V</td><td>:</td><td>OK</td></tr>
          <tr><td class="fw-bold text-warning">Δ</td><td>:</td><td>PERLU TINDAKAN</td></tr>
          <tr><td class="fw-bold text-danger">X</td><td>:</td><td>TIDAK ADA</td></tr>
        </table>
      </div>
      
      <?php if (strtolower($jenisSlug) === 'overhaul' && strtolower($lokasiSlug) === 'mfg2' && $itemIndex > $perPage): ?>
        <?php 
          if (strtolower($categorySlug) === 'double-milling') {
              $totalPages = 2;
          } elseif (strtolower($categorySlug) === 'double-center-drill') {
              $totalPages = 2;
          } elseif (strtolower($categorySlug) === 'centering-grinding') {
              $totalPages = 3;
          } else {
              $totalPages = ceil($itemIndex / $perPage); 
          }
        ?>
        <div class="card bg-light border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-bold text-primary mb-3">Navigasi Halaman</h6>
                <div class="d-grid gap-2" id="navPageContainer">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <button type="button" class="btn btn-outline-primary btn-nav-page <?= $p === 1 ? 'active' : '' ?>" data-target="<?= $p ?>">Halaman <?= $p ?></button>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Checklist Control is automatically populated in the background upon submission -->

  <?php if (!empty($rows)): ?>
    <div class="d-flex justify-content-end mt-4 mb-5 gap-3">
      <?php if (strtolower($jenisSlug) === 'overhaul' && strtolower($lokasiSlug) === 'mfg1'): ?>
        <button type="button" id="btnNext" class="btn btn-primary px-5 py-2 fw-semibold shadow-sm">Lanjut ke Bar Feeder <i class="bi bi-arrow-right ms-2"></i></button>
        <button type="button" id="btnPrev" class="btn btn-secondary px-4 py-2 fw-semibold shadow-sm" style="display:none;"><i class="bi bi-arrow-left me-2"></i> Kembali</button>
        <button type="submit" id="btnSubmit" class="btn btn-success px-5 py-2 fw-semibold shadow-sm" style="display:none;">Submit Pengecekan</button>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tr[data-kategori]');
            const btnNext = document.getElementById('btnNext');
            const btnPrev = document.getElementById('btnPrev');
            const btnSubmit = document.getElementById('btnSubmit');
            const navMesin = document.getElementById('btnMesinCnc');
            const navBarFeeder = document.getElementById('btnBarFeeder');

            let currentView = 'Mesin CNC';

            function updateView() {
                rows.forEach(row => {
                    if (row.getAttribute('data-kategori') === currentView || row.getAttribute('data-kategori') === '') {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (currentView === 'Mesin CNC') {
                    btnNext.style.display = '';
                    btnPrev.style.display = 'none';
                    btnSubmit.style.display = 'none';
                    if(navMesin) navMesin.classList.add('active');
                    if(navBarFeeder) navBarFeeder.classList.remove('active');
                } else {
                    btnNext.style.display = 'none';
                    btnPrev.style.display = '';
                    btnSubmit.style.display = '';
                    if(navMesin) navMesin.classList.remove('active');
                    if(navBarFeeder) navBarFeeder.classList.add('active');
                }
            }

            function validateCurrentView() {
                let isValid = true;
                let firstUnchecked = null;
                let missingItems = [];
                let currentNo = '';
                let currentBagian = '';
                
                rows.forEach(row => {
                    if (row.getAttribute('data-kategori') === currentView) {
                        const noCell = row.querySelector('.text-muted');
                        if (noCell) currentNo = noCell.innerText.trim();
                        
                        const bagianCell = row.querySelector('.bagian-cell');
                        if (bagianCell) currentBagian = bagianCell.innerText.trim();
                        
                        const radios = row.querySelectorAll('input[type="radio"]');
                        if (radios.length > 0) {
                            const isChecked = row.querySelector('input[type="radio"]:checked');
                            if (!isChecked) {
                                isValid = false;
                                row.classList.add('table-danger');
                                if (!firstUnchecked) firstUnchecked = row;
                                
                                let itemName = currentNo;
                                if (currentBagian) itemName += ' ' + currentBagian;
                                missingItems.push(itemName.trim() || 'Item tanpa nama');
                            } else {
                                if (isChecked.value === 'Δ') {
                                    const fileInput = row.querySelector('.foto-abnormal-input');
                                    if (fileInput && fileInput.hasAttribute('required') && fileInput.files.length === 0) {
                                        isValid = false;
                                        row.classList.add('table-danger');
                                        if (!firstUnchecked) firstUnchecked = row;
                                        let itemName = currentNo;
                                        if (currentBagian) itemName += ' ' + currentBagian;
                                        missingItems.push((itemName.trim() || 'Item tanpa nama') + ' (Foto Wajib)');
                                    } else {
                                        row.classList.remove('table-danger');
                                    }
                                } else {
                                    row.classList.remove('table-danger');
                                }
                            }
                        }
                    }
                });
                
                if (!isValid && firstUnchecked) {
                    let uniqueMissing = [...new Set(missingItems)];
                    let missingHtml = '<ul class="text-start" style="max-height: 200px; overflow-y: auto; font-size: 0.9rem;">';
                    uniqueMissing.forEach(item => {
                        missingHtml += '<li>' + item + '</li>';
                    });
                    missingHtml += '</ul>';
                    
                    if (typeof Swal !== 'undefined') {
                        firstUnchecked.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data Belum Lengkap!',
                            html: '<p>Terdapat isian yang belum diisi pada bagian <b>' + currentView + '</b>:</p>' + missingHtml,
                            confirmButtonText: 'Tutup',
                            returnFocus: false
                        });
                    } else {
                        firstUnchecked.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        alert('Mohon lengkapi poin pengecekan berikut pada ' + currentView + ':\n\n' + uniqueMissing.join('\n'));
                    }
                }
                
                return isValid;
            }

            function validateHeader() {
                let missingHeader = [];
                const idMesin = document.getElementById('id_mesin');
                const namaPic = document.querySelector('select[name="nama_pic"]');

                if (idMesin && !idMesin.value) missingHeader.push('No Mesin');
                if (namaPic && !namaPic.value) missingHeader.push('PIC');

                if (missingHeader.length > 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Belum Lengkap!',
                            text: 'Mohon isi ' + missingHeader.join(' dan ') + ' terlebih dahulu di bagian atas form.',
                            confirmButtonText: 'Tutup',
                            returnFocus: false
                        }).then(() => {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        });
                    } else {
                        alert('Mohon isi ' + missingHeader.join(' dan ') + ' terlebih dahulu di bagian atas form.');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                    return false;
                }
                return true;
            }

            if (rows.length > 0) {
                updateView();

                btnNext.addEventListener('click', () => {
                    if (!validateHeader()) return;
                    if (!validateCurrentView()) return;
                    currentView = 'Bar Feeder CNC';
                    updateView();
                    window.scrollTo(0, 0);
                });

                btnPrev.addEventListener('click', () => {
                    currentView = 'Mesin CNC';
                    updateView();
                    window.scrollTo(0, 0);
                });
                
                if(navMesin) navMesin.addEventListener('click', () => {
                    currentView = 'Mesin CNC';
                    updateView();
                });
                
                if(navBarFeeder) navBarFeeder.addEventListener('click', () => {
                    if (currentView === 'Mesin CNC') {
                        if (!validateHeader()) return;
                        if (!validateCurrentView()) return;
                    }
                    currentView = 'Bar Feeder CNC';
                    updateView();
                });
            } else {
                btnSubmit.style.display = '';
                btnNext.style.display = 'none';
            }
        });
        </script>
      <?php elseif (strtolower($jenisSlug) === 'overhaul' && strtolower($lokasiSlug) === 'mfg2' && $itemIndex > $perPage): ?>
        <button type="button" id="btnPrevPage" class="btn btn-secondary px-4 py-2 fw-semibold shadow-sm" style="display:none;"><i class="bi bi-arrow-left me-2"></i> Halaman Sebelumnya</button>
        <button type="button" id="btnNextPage" class="btn btn-primary px-5 py-2 fw-semibold shadow-sm">Halaman Selanjutnya <i class="bi bi-arrow-right ms-2"></i></button>
        <button type="submit" id="btnSubmitPage" class="btn btn-success px-5 py-2 fw-semibold shadow-sm" style="display:none;">Submit Pengecekan</button>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mfg2Rows = document.querySelectorAll('.page-row-mfg2');
            if (mfg2Rows.length === 0) return;

            const btnNext = document.getElementById('btnNextPage');
            const btnPrev = document.getElementById('btnPrevPage');
            const btnSubmit = document.getElementById('btnSubmitPage');
            const navButtons = document.querySelectorAll('.btn-nav-page');
            const totalPages = <?= $totalPages ?>;
            let currentPage = 1;

            function updatePageView() {
                mfg2Rows.forEach(row => {
                    if (parseInt(row.getAttribute('data-page')) === currentPage) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                navButtons.forEach(btn => {
                    if (parseInt(btn.getAttribute('data-target')) === currentPage) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });

                if (currentPage === 1) {
                    btnPrev.style.display = 'none';
                    btnNext.style.display = '';
                    btnSubmit.style.display = 'none';
                } else if (currentPage === totalPages) {
                    btnPrev.style.display = '';
                    btnNext.style.display = 'none';
                    btnSubmit.style.display = '';
                } else {
                    btnPrev.style.display = '';
                    btnNext.style.display = '';
                    btnSubmit.style.display = 'none';
                }
            }

            function validateCurrentPage() {
                let isValid = true;
                let firstUnchecked = null;
                let missingItems = [];
                let currentBagian = '';
                
                mfg2Rows.forEach(row => {
                    if (parseInt(row.getAttribute('data-page')) === currentPage) {
                        const bagianCell = row.querySelector('.bagian-cell');
                        if (bagianCell) currentBagian = bagianCell.innerText.trim();
                        
                        const radios = row.querySelectorAll('input[type="radio"]');
                        if (radios.length > 0) {
                            const isChecked = row.querySelector('input[type="radio"]:checked');
                            if (!isChecked) {
                                isValid = false;
                                row.classList.add('table-danger');
                                if (!firstUnchecked) firstUnchecked = row;
                                
                                missingItems.push(currentBagian || 'Item tanpa nama');
                            } else {
                                if (isChecked.value === 'Δ') {
                                    const fileInput = row.querySelector('.foto-abnormal-input');
                                    if (fileInput && fileInput.hasAttribute('required') && fileInput.files.length === 0) {
                                        isValid = false;
                                        row.classList.add('table-danger');
                                        if (!firstUnchecked) firstUnchecked = row;
                                        missingItems.push((currentBagian || 'Item tanpa nama') + ' (Foto Wajib)');
                                    } else {
                                        row.classList.remove('table-danger');
                                    }
                                } else {
                                    row.classList.remove('table-danger');
                                }
                            }
                        }
                    }
                });
                
                if (!isValid && firstUnchecked) {
                    let uniqueMissing = [...new Set(missingItems)];
                    let missingHtml = '<ul class="text-start" style="max-height: 200px; overflow-y: auto; font-size: 0.9rem;">';
                    uniqueMissing.forEach(item => {
                        missingHtml += '<li>' + item + '</li>';
                    });
                    missingHtml += '</ul>';
                    
                    if (typeof Swal !== 'undefined') {
                        firstUnchecked.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data Belum Lengkap!',
                            html: '<p>Terdapat isian yang belum diisi pada <b>Halaman ' + currentPage + '</b>:</p>' + missingHtml,
                            confirmButtonText: 'Tutup',
                            returnFocus: false
                        });
                    } else {
                        firstUnchecked.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        alert('Mohon lengkapi poin pengecekan berikut pada Halaman ' + currentPage + ':\n\n' + uniqueMissing.join('\n'));
                    }
                }
                
                return isValid;
            }

            function validateHeader() {
                let missingHeader = [];
                const idMesin = document.getElementById('id_mesin');
                const namaPic = document.querySelector('select[name="nama_pic"]');

                if (idMesin && !idMesin.value) missingHeader.push('No Mesin');
                if (namaPic && !namaPic.value) missingHeader.push('PIC');

                if (missingHeader.length > 0) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data Belum Lengkap!',
                            text: 'Mohon isi ' + missingHeader.join(' dan ') + ' terlebih dahulu di bagian atas form.',
                            confirmButtonText: 'Tutup',
                            returnFocus: false
                        }).then(() => {
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        });
                    } else {
                        alert('Mohon isi ' + missingHeader.join(' dan ') + ' terlebih dahulu di bagian atas form.');
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                    return false;
                }
                return true;
            }

            updatePageView();

            btnNext.addEventListener('click', () => {
                if (!validateHeader()) return;
                if (!validateCurrentPage()) return;
                if (currentPage < totalPages) {
                    currentPage++;
                    updatePageView();
                    window.scrollTo(0, 0);
                }
            });

            btnPrev.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updatePageView();
                    window.scrollTo(0, 0);
                }
            });

            navButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    let targetPage = parseInt(btn.getAttribute('data-target'));
                    if (targetPage > currentPage) {
                        if (!validateHeader()) return;
                        if (!validateCurrentPage()) return;
                    }
                    currentPage = targetPage;
                    updatePageView();
                    window.scrollTo(0, 0);
                });
            });
        });
        </script>
      <?php else: ?>
        <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold shadow-sm">Submit Pengecekan</button>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <script>
    function validateGlobalHeader() {
        let missingHeader = [];
        const idMesin = document.getElementById('id_mesin');
        const namaPic = document.querySelector('select[name="nama_pic"]');

        if (idMesin && !idMesin.value) missingHeader.push('No Mesin');
        if (namaPic && !namaPic.value) missingHeader.push('PIC');

        if (missingHeader.length > 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Data Belum Lengkap!',
                    text: 'Mohon isi ' + missingHeader.join(' dan ') + ' terlebih dahulu di bagian atas form.',
                    confirmButtonText: 'Tutup',
                    returnFocus: false
                }).then(() => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            } else {
                alert('Mohon isi ' + missingHeader.join(' dan ') + ' terlebih dahulu di bagian atas form.');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            return false;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('checklistForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                // 1. Check Header First
                if (!validateGlobalHeader()) {
                    e.preventDefault();
                    return false;
                }

                // 2. Check all checklist rows
                let missingItems = [];
                let firstUnchecked = null;
                let isValid = true;
                let currentNo = '';
                let currentBagian = '';

                const tableRows = form.querySelectorAll('tr');
                tableRows.forEach(row => {
                    const noCell = row.querySelector('.text-muted');
                    if (noCell) currentNo = noCell.innerText.trim();
                    
                    const bagianCell = row.querySelector('.bagian-cell');
                    if (bagianCell) currentBagian = bagianCell.innerText.trim();
                    
                    const radios = row.querySelectorAll('input[type="radio"]');
                    if (radios.length > 0) {
                        const isChecked = row.querySelector('input[type="radio"]:checked');
                        if (!isChecked) {
                            isValid = false;
                            row.classList.add('table-danger');
                            if (!firstUnchecked) firstUnchecked = row;
                            
                            let itemName = currentNo;
                            if (currentBagian) itemName += ' ' + currentBagian;
                            missingItems.push(itemName.trim() || 'Item tanpa nama');
                        } else {
                            if (isChecked.value === 'Δ') {
                                const fileInput = row.querySelector('.foto-abnormal-input');
                                if (fileInput && fileInput.hasAttribute('required') && fileInput.files.length === 0) {
                                    isValid = false;
                                    row.classList.add('table-danger');
                                    if (!firstUnchecked) firstUnchecked = row;
                                    let itemName = currentNo;
                                    if (currentBagian) itemName += ' ' + currentBagian;
                                    missingItems.push((itemName.trim() || 'Item tanpa nama') + ' (Foto Wajib)');
                                } else {
                                    row.classList.remove('table-danger');
                                }
                            } else {
                                row.classList.remove('table-danger');
                            }
                        }
                    }
                });

                if (!isValid && firstUnchecked) {
                    e.preventDefault(); // Stop submission

                    // Switch page/view if firstUnchecked is on another page
                    if (firstUnchecked.hasAttribute('data-kategori') && typeof currentView !== 'undefined' && typeof updateView === 'function') {
                        let targetView = firstUnchecked.getAttribute('data-kategori');
                        if (targetView && targetView !== currentView) {
                            currentView = targetView;
                            updateView();
                        }
                    }
                    
                    if (firstUnchecked.hasAttribute('data-page') && typeof currentPage !== 'undefined' && typeof updatePageView === 'function') {
                        let targetPage = parseInt(firstUnchecked.getAttribute('data-page'));
                        if (!isNaN(targetPage) && targetPage !== currentPage) {
                            currentPage = targetPage;
                            updatePageView();
                        }
                    }

                    let uniqueMissing = [...new Set(missingItems)];
                    let missingHtml = '<ul class="text-start" style="max-height: 200px; overflow-y: auto; font-size: 0.9rem;">';
                    uniqueMissing.forEach(item => {
                        missingHtml += '<li>' + item + '</li>';
                    });
                    missingHtml += '</ul>';
                    
                    if (typeof Swal !== 'undefined') {
                        firstUnchecked.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data Belum Lengkap!',
                            html: '<p>Terdapat isian yang belum diisi sebelum disubmit:</p>' + missingHtml,
                            confirmButtonText: 'Tutup',
                            returnFocus: false
                        });
                    } else {
                        firstUnchecked.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        alert('Mohon lengkapi isian berikut sebelum submit:\n\n' + uniqueMissing.join('\n'));
                    }
                } else {
                    // Validasi lolos, lakukan pengecekan duplikasi ke server
                    e.preventDefault(); // Tahan pengiriman form

                    const idMesin = document.getElementById('id_mesin').value;
                    const jenisCheck = "<?= esc($jenisSlug) ?>";
                    const kategori = "<?= esc($categorySlug ?? '') ?>";
                    
                    if (!idMesin) {
                        HTMLFormElement.prototype.submit.call(form);
                        return;
                    }

                    Swal.fire({
                        title: 'Memeriksa Data...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('<?= site_url("checklist/check-duplicate") ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'id_mesin=' + encodeURIComponent(idMesin) + '&jenis_check=' + encodeURIComponent(jenisCheck) + '&kategori=' + encodeURIComponent(kategori)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.duplicate) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Peringatan Duplikasi',
                                text: 'Mesin ini sudah pernah dilakukan pengecekan (' + data.kategori + ') pada bulan ini. Apakah Anda yakin ingin mensubmit form pengecekan lagi?',
                                showCancelButton: true,
                                confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Ya, Lanjutkan',
                                cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Batal',
                                confirmButtonColor: '#0d6efd',
                                cancelButtonColor: '#dc3545'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    HTMLFormElement.prototype.submit.call(form);
                                }
                            });
                        } else {
                            HTMLFormElement.prototype.submit.call(form);
                        }
                    })
                    .catch(err => {
                        console.error("Duplicate Check Error (Submit):", err);
                        // Jika gagal ngecek, biarkan submit
                        HTMLFormElement.prototype.submit.call(form);
                    });
                }
            });
        }
    });

    // ====== FOTO ABNORMAL: Show/Hide ======
    document.addEventListener('change', function(e) {
        const radio = e.target;
        if (!radio.classList.contains('hasil-check-radio')) return;

        const paramId = radio.getAttribute('data-param-id');
        const wrap     = document.getElementById('foto-wrap-' + paramId);
        const fileInput = document.getElementById('foto-input-' + paramId);
        const previewWrap = document.getElementById('foto-preview-' + paramId);

        if (radio.value === 'Δ') {
            wrap.style.display = 'block';
            fileInput.required = true;
        } else {
            wrap.style.display = 'none';
            fileInput.required = false;
            // Bersihkan file dan preview
            try {
                const dt = new DataTransfer();
                fileInput.files = dt.files;
            } catch(e) {}
            if (previewWrap) {
                previewWrap.style.display = 'none';
                const img = previewWrap.querySelector('img');
                if (img) img.src = '';
            }
        }
    });
  </script>
</form>

<?= view('layout/footer') ?>

<!-- ====== CAMERA MODAL FULLSCREEN ====== -->
<div class="modal fade" id="cameraModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen m-0">
    <div class="modal-content bg-dark text-white border-0" style="height:100vh; display:flex; flex-direction:column;">

      <!-- Header -->
      <div style="flex:0 0 auto; background:#111; padding:12px 16px; display:flex; align-items:center; gap:12px; border-bottom:1px solid #333;">
        <i class="bi bi-camera-fill text-warning" style="font-size:1.3rem;"></i>
        <span class="fw-bold fs-5 me-auto">Foto Abnormal</span>
        <!-- Mode tabs -->
        <div class="btn-group" role="group">
          <button type="button" id="btnModeCamera" class="btn btn-warning btn-sm px-3">
            <i class="bi bi-camera-fill me-1"></i>Kamera
          </button>
          <button type="button" id="btnModeUpload" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-images me-1"></i>Galeri
          </button>
        </div>
        <button type="button" id="btnCloseCamera" class="btn-close btn-close-white ms-2"></button>
      </div>

      <!-- ===== KAMERA PANEL ===== -->
      <div id="camPanelCamera" style="flex:1 1 auto; position:relative; overflow:hidden; background:#000;">
        <!-- Loading -->
        <div id="camLoading" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;">
          <div class="spinner-border text-warning mb-3" style="width:3.5rem;height:3.5rem;"></div>
          <span style="font-size:1.1rem;">Membuka kamera...</span>
        </div>
        <!-- Error -->
        <div id="camError" style="position:absolute;inset:0;display:none;flex-direction:column;align-items:center;justify-content:center;color:#dc3545;padding:2rem;text-align:center;">
          <i class="bi bi-camera-video-off" style="font-size:5rem;"></i>
          <p class="mt-3 fs-5" id="camErrorMsg">Kamera tidak dapat diakses.</p>
          <small style="color:#888;">Gunakan tab <b>Galeri</b> untuk upload dari file lokal.</small>
        </div>
        <!-- Video -->
        <video id="camVideo" autoplay playsinline muted
               style="position:absolute;inset:0;width:100%;height:100%;object-fit:contain;display:none;background:#000;"></video>
        <!-- Preview after capture (img for object-fit support) -->
        <img id="camPreview" src="" alt=""
             style="position:absolute;inset:0;width:100%;height:100%;object-fit:contain;display:none;background:#000;">
        <!-- Size info badge -->
        <div id="camSizeBadge"
             style="position:absolute;bottom:12px;right:12px;background:rgba(0,0,0,.65);color:#fff;font-size:0.78rem;padding:4px 12px;border-radius:20px;display:none;">
        </div>
      </div>

      <!-- ===== GALERI / UPLOAD PANEL ===== -->
      <div id="camPanelUpload" style="flex:1 1 auto;display:none;flex-direction:column;align-items:center;justify-content:center;padding:24px;background:#111;overflow-y:auto;">
        <!-- Drop zone / pick button -->
        <label for="camFileInput" id="camDropZone"
               style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:100%;max-width:520px;min-height:200px;border:2px dashed #555;border-radius:16px;padding:2rem;cursor:pointer;transition:border-color .2s,background .2s;background:rgba(255,255,255,.03);">
          <i class="bi bi-images text-warning" style="font-size:5rem;"></i>
          <span class="mt-3 fw-semibold" style="font-size:1.2rem;">Ketuk untuk pilih foto</span>
          <small style="color:#888;margin-top:6px;">JPG · PNG · HEIC · WEBP</small>
        </label>
        <input type="file" id="camFileInput" accept="image/*" class="d-none">

        <!-- Upload preview -->
        <div id="camUploadPreviewWrap" style="display:none;width:100%;max-width:700px;margin-top:20px;text-align:center;">
          <img id="camUploadPreviewImg" src="" alt="Preview"
               style="max-width:100%;max-height:52vh;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.6);">
          <div id="camUploadInfo" style="margin-top:10px;color:#ffc107;font-size:0.9rem;"></div>
        </div>

        <!-- Compressing spinner -->
        <div id="camUploadCompressing" style="display:none;margin-top:20px;text-align:center;">
          <div class="spinner-border text-warning" style="width:2.5rem;height:2.5rem;"></div>
          <p style="margin-top:12px;color:#aaa;">Mengompresi foto...</p>
        </div>
      </div>

      <!-- Footer / Actions -->
      <div style="flex:0 0 auto;background:#111;border-top:1px solid #333;padding:20px 24px;display:flex;align-items:center;justify-content:center;gap:16px;min-height:110px;">
        <!-- Live: shutter -->
        <div id="actLive" style="display:none;text-align:center;">
          <button type="button" id="btnShutter" class="btn btn-warning rounded-circle"
                  style="width:90px;height:90px;font-size:2.5rem;box-shadow:0 0 0 8px rgba(255,193,7,.2);">
            <i class="bi bi-circle-fill"></i>
          </button>
          <p style="color:rgba(255,255,255,.45);font-size:.85rem;margin:10px 0 0;">Tekan untuk foto</p>
        </div>
        <!-- Camera confirm -->
        <div id="actConfirm" style="display:none;gap:14px;justify-content:center;flex-wrap:wrap;">
          <button type="button" id="btnRetake" class="btn btn-outline-light btn-lg px-5">
            <i class="bi bi-arrow-repeat me-2"></i>Foto Ulang
          </button>
          <button type="button" id="btnUsePhoto" class="btn btn-success btn-lg px-5">
            <i class="bi bi-check-circle me-2"></i>Gunakan Foto
          </button>
        </div>
        <!-- Upload confirm -->
        <div id="actUseUpload" style="display:none;">
          <button type="button" id="btnUseUploadPhoto" class="btn btn-success btn-lg px-5">
            <i class="bi bi-check-circle me-2"></i>Gunakan Foto Ini
          </button>
        </div>
        <!-- Processing -->
        <div id="actProcessing" style="display:none;text-align:center;">
          <div class="spinner-border text-warning" style="width:2.5rem;height:2.5rem;"></div>
          <p style="color:#aaa;margin:10px 0 0;font-size:.9rem;">Memproses foto...</p>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
// ====== CAMERA MODULE (Foto Abnormal) ======
(function () {
    // === Kompresi config ===
    const MAX_W = 1440, MAX_H = 1440, JPEG_Q = 0.84;

    let _stream = null;
    let _capturedBlob = null;
    let _uploadBlob   = null;
    let _currentMode  = 'camera'; // 'camera' | 'upload'
    let _targetInputId   = null;
    let _targetPreviewId = null;

    // Elements
    const modalEl       = document.getElementById('cameraModal');
    const camModal      = new bootstrap.Modal(modalEl);
    const camVideo      = document.getElementById('camVideo');
    const camPreview    = document.getElementById('camPreview');
    const camLoading    = document.getElementById('camLoading');
    const camError      = document.getElementById('camError');
    const camErrorMsg   = document.getElementById('camErrorMsg');
    const camSizeBadge  = document.getElementById('camSizeBadge');
    const panelCamera   = document.getElementById('camPanelCamera');
    const panelUpload   = document.getElementById('camPanelUpload');
    const camFileInput  = document.getElementById('camFileInput');
    const camDropZone   = document.getElementById('camDropZone');
    const uploadPreviewWrap  = document.getElementById('camUploadPreviewWrap');
    const uploadPreviewImg   = document.getElementById('camUploadPreviewImg');
    const uploadInfo         = document.getElementById('camUploadInfo');
    const uploadCompressing  = document.getElementById('camUploadCompressing');
    const actLive       = document.getElementById('actLive');
    const actConfirm    = document.getElementById('actConfirm');
    const actUseUpload  = document.getElementById('actUseUpload');
    const actProcessing = document.getElementById('actProcessing');
    const btnModeCamera = document.getElementById('btnModeCamera');
    const btnModeUpload = document.getElementById('btnModeUpload');
    const btnClose      = document.getElementById('btnCloseCamera');
    const btnShutter    = document.getElementById('btnShutter');
    const btnRetake     = document.getElementById('btnRetake');
    const btnUsePhoto   = document.getElementById('btnUsePhoto');
    const btnUseUpload  = document.getElementById('btnUseUploadPhoto');

    // ===== UTILS =====
    function fmt(b) { return b < 1048576 ? (b/1024).toFixed(0)+' KB' : (b/1048576).toFixed(1)+' MB'; }

    function compressVideo(videoEl, cb) {
        const w = videoEl.videoWidth, h = videoEl.videoHeight;
        if (!w || !h) return;
        const s = Math.min(MAX_W/w, MAX_H/h, 1);
        const cw = Math.round(w*s), ch = Math.round(h*s);
        const tmp = document.createElement('canvas');
        tmp.width = cw; tmp.height = ch;
        tmp.getContext('2d').drawImage(videoEl, 0, 0, cw, ch);
        tmp.toBlob(blob => cb(blob, cw, ch), 'image/jpeg', JPEG_Q);
    }

    function compressImg(imgEl, cb) {
        const w = imgEl.naturalWidth, h = imgEl.naturalHeight;
        const s = Math.min(MAX_W/w, MAX_H/h, 1);
        const cw = Math.round(w*s), ch = Math.round(h*s);
        const tmp = document.createElement('canvas');
        tmp.width = cw; tmp.height = ch;
        tmp.getContext('2d').drawImage(imgEl, 0, 0, cw, ch);
        tmp.toBlob(blob => cb(blob, cw, ch), 'image/jpeg', JPEG_Q);
    }

    function showEl(el, v) { el.style.display = v; }
    function hideEl(el)    { el.style.display = 'none'; }

    // ===== CAMERA =====
    function stopStream() {
        if (_stream) { _stream.getTracks().forEach(t => t.stop()); _stream = null; }
    }

    function setCamView(state) {
        // state: loading | error | live | confirm | processing
        showEl(camLoading,  state === 'loading'    ? 'flex'  : 'none');
        showEl(camError,    state === 'error'      ? 'flex'  : 'none');
        showEl(camVideo,    state === 'live'       ? 'block' : 'none');
        showEl(camPreview,  state === 'confirm'    ? 'block' : 'none');
        showEl(camSizeBadge,state === 'confirm'    ? 'block' : 'none');
        // Footer
        showEl(actLive,       state === 'live'      ? 'block' : 'none');
        showEl(actConfirm,    state === 'confirm'   ? 'flex'  : 'none');
        showEl(actUseUpload,  'none');
        showEl(actProcessing, state === 'processing'? 'block' : 'none');
    }

    async function startCamera() {
        setCamView('loading');
        _capturedBlob = null;
        stopStream();
        try {
            _stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: {ideal:'environment'}, width:{ideal:1920}, height:{ideal:1080} }
            });
            camVideo.srcObject = _stream;
            camVideo.onloadedmetadata = () => { camVideo.play(); setCamView('live'); };
        } catch (err) {
            stopStream();
            camErrorMsg.textContent = 'Kamera tidak dapat diakses: ' + err.message;
            setCamView('error');
        }
    }

    btnShutter.addEventListener('click', function () {
        if (!_stream) return;
        setCamView('processing');
        compressVideo(camVideo, (blob, cw, ch) => {
            _capturedBlob = blob;
            stopStream();
            camPreview.src = URL.createObjectURL(blob);
            camSizeBadge.textContent = cw + '×' + ch + ' · ' + fmt(blob.size);
            setCamView('confirm');
        });
    });

    btnRetake.addEventListener('click', () => { _capturedBlob = null; startCamera(); });

    // ===== UPLOAD / GALERI =====
    function setUploadView(state) {
        // state: idle | compressing | ready
        showEl(camDropZone,         state !== 'ready'      ? 'flex'  : 'none');
        showEl(uploadCompressing,   state === 'compressing'? 'flex'  : 'none');
        showEl(uploadPreviewWrap,   state === 'ready'      ? 'block' : 'none');
        showEl(actUseUpload,        state === 'ready'      ? 'block' : 'none');
        showEl(actLive,             'none');
        showEl(actConfirm,          'none');
        showEl(actProcessing,       'none');
    }

    camFileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        _uploadBlob = null;
        setUploadView('compressing');
        const url = URL.createObjectURL(file);
        const img  = new Image();
        img.onload = () => {
            URL.revokeObjectURL(url);
            compressImg(img, (blob, cw, ch) => {
                _uploadBlob = blob;
                uploadPreviewImg.src = URL.createObjectURL(blob);
                uploadInfo.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i>' +
                    'Dikompres: <b>' + cw + '×' + ch + '</b> · <b>' + fmt(blob.size) + '</b> ' +
                    '<span style="color:#888;">(dari ' + fmt(file.size) + ')</span>';
                setUploadView('ready');
            });
        };
        img.src = url;
    });

    // ===== MODE SWITCH =====
    function switchMode(mode) {
        _currentMode = mode;
        if (mode === 'camera') {
            showEl(panelCamera, 'block');
            showEl(panelUpload, 'none');
            btnModeCamera.className = 'btn btn-warning btn-sm px-3';
            btnModeUpload.className = 'btn btn-outline-secondary btn-sm px-3';
            startCamera();
        } else {
            stopStream();
            showEl(panelCamera, 'none');
            showEl(panelUpload, 'flex');
            btnModeCamera.className = 'btn btn-outline-secondary btn-sm px-3';
            btnModeUpload.className = 'btn btn-warning btn-sm px-3';
            _uploadBlob = null;
            camFileInput.value = '';
            setUploadView('idle');
        }
    }

    btnModeCamera.addEventListener('click', () => switchMode('camera'));
    btnModeUpload.addEventListener('click', () => switchMode('upload'));

    let _targetBtnId = null;

    // ===== USE PHOTO (common) =====
    function applyBlob(blob) {
        if (!blob) return;
        const file = new File([blob], 'foto_' + Date.now() + '.jpg', {type:'image/jpeg'});
        const inp = document.getElementById(_targetInputId);
        if (inp) {
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                inp.files = dt.files;
            } catch(e) { console.warn('DataTransfer unsupported', e); }
        }
        const wrap = document.getElementById(_targetPreviewId);
        if (wrap) {
            const img = wrap.querySelector('img');
            if (img) img.src = URL.createObjectURL(blob);
            wrap.style.display = 'block';
        }
        const mainBtn = document.getElementById(_targetBtnId);
        if (mainBtn) mainBtn.style.display = 'none';
        
        camModal.hide();
    }

    btnUsePhoto.addEventListener('click',  () => applyBlob(_capturedBlob));
    btnUseUpload.addEventListener('click', () => applyBlob(_uploadBlob));

    // ===== CLOSE =====
    btnClose.addEventListener('click', () => { stopStream(); camModal.hide(); });
    modalEl.addEventListener('hidden.bs.modal', () => {
        stopStream();
        _capturedBlob = null; _uploadBlob = null;
        setCamView('loading');
        camPreview.src = '';
    });

    // ===== EVENT LISTENERS =====
    document.addEventListener('click', function (e) {
        // Trigger Ambil/Ulang Foto
        const btnCam = e.target.closest('.btn-ambil-foto');
        if (btnCam) {
            e.preventDefault(); e.stopPropagation();
            _targetInputId   = btnCam.getAttribute('data-target-input');
            _targetPreviewId = btnCam.getAttribute('data-target-preview');
            _targetBtnId     = btnCam.getAttribute('data-target-btn');
            camModal.show();
            switchMode('camera');
            return;
        }
        
        // Trigger Hapus Foto
        const btnDel = e.target.closest('.btn-hapus-foto');
        if (btnDel) {
            e.preventDefault(); e.stopPropagation();
            const inputId = btnDel.getAttribute('data-target-input');
            const previewId = btnDel.getAttribute('data-target-preview');
            const btnId = btnDel.getAttribute('data-target-btn');
            
            const inp = document.getElementById(inputId);
            if(inp) inp.value = '';
            const preview = document.getElementById(previewId);
            if(preview) preview.style.display = 'none';
            const mainBtn = document.getElementById(btnId);
            if(mainBtn) mainBtn.style.display = 'block';
            return;
        }

        // Trigger Zoom Foto Preview
        const imgClick = e.target.closest('.preview-img-click');
        if (imgClick) {
            e.preventDefault(); e.stopPropagation();
            if (imgClick.src) {
                window.open(imgClick.src, '_blank');
            }
            return;
        }
    });

})();
</script>
<script>
// ====== AUTO-SAVE FORM ======
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checklistForm');
    if (!form) return;

    // Do not auto-save on edit mode, only on new form
    const isEdit = <?= isset($isEdit) && $isEdit ? 'true' : 'false' ?>;
    if (isEdit) return;

    const jenisSlug = "<?= esc($jenisSlug ?? '') ?>";
    const lokasiSlug = "<?= esc($lokasiSlug ?? '') ?>";
    const categorySlug = "<?= esc($categorySlug ?? '') ?>";

    function getStorageKey() {
        return `autosave_mtce_${lokasiSlug}_${jenisSlug}_${categorySlug}`;
    }

    function saveFormData() {
        const key = getStorageKey();
        if (!key) return;
        
        const formData = new FormData(form);
        const data = {};
        for (let [name, value] of formData.entries()) {
            const field = form.querySelector(`[name="${name}"]`);
            if (field && field.type === 'file') continue;
            
            if (data[name] !== undefined) {
                if (!Array.isArray(data[name])) data[name] = [data[name]];
                data[name].push(value);
            } else {
                data[name] = value;
            }
        }
        localStorage.setItem(key, JSON.stringify(data));
    }

    function loadFormData() {
        const key = getStorageKey();
        if (!key) return;
        
        const saved = localStorage.getItem(key);
        if (saved) {
            try {
                const data = JSON.parse(saved);
                
                // Cek jika user masuk via scan (URL bawa id_mesin)
                const urlParams = new URLSearchParams(window.location.search);
                const urlIdMesin = urlParams.get('id_mesin');
                
                if (urlIdMesin && data["id_mesin"] && urlIdMesin !== data["id_mesin"]) {
                    // Mesin yang di-scan BEDA dengan draf yang tersimpan.
                    // Jangan load draf mesin lain ke mesin yang baru di-scan!
                    localStorage.removeItem(key);
                    return;
                }

                const lastActivity = data['_last_activity'] || 0;
                const now = Date.now();
                let gapMinutes = 0;
                
                if (lastActivity > 0) {
                    const lastDate = new Date(lastActivity).toLocaleDateString();
                    const nowDate = new Date(now).toLocaleDateString();
                    if (lastDate !== nowDate) {
                        localStorage.removeItem(key);
                        return; // Beda hari, hapus total autosave
                    }
                    gapMinutes = (now - lastActivity) / (1000 * 60);
                } else if (data['waktu_mulai']) {
                    const todayDate = "<?= date('Y-m-d') ?>";
                    if (!data['waktu_mulai'].startsWith(todayDate)) {
                        localStorage.removeItem(key);
                        return; // Fallback lama
                    }
                    gapMinutes = 999; // Force waktu_mulai to reset
                }

                let hasData = false;
                for (let name in data) {
                    if (name === '_last_activity' || name === 'csrf_test_name' || name === 'waktu_selesai') continue;
                    
                    if (name === 'waktu_mulai') {
                        if (gapMinutes <= 10) {
                            const displayEl = document.getElementById('displayWaktuMulai');
                            if (displayEl) displayEl.value = data[name];
                        } else {
                            continue; // Jeda > 10 menit, waktu_mulai direset
                        }
                    }
                    
                    const value = data[name];
                    const fields = form.querySelectorAll(`[name="${name}"]`);
                    if (!fields.length) continue;
                    
                    hasData = true;
                    if (fields.length === 1 && fields[0].type !== 'radio' && fields[0].type !== 'checkbox') {
                        fields[0].value = value;
                    } else {
                        fields.forEach(field => {
                            if (field.type === 'radio' || field.type === 'checkbox') {
                                if (Array.isArray(value)) {
                                    field.checked = value.includes(field.value);
                                } else {
                                    field.checked = (field.value === value);
                                }
                            }
                        });
                    }
                }
                
                const picSelect = document.querySelector('select[name="pic_line_nama"]');
                if (picSelect && picSelect.tomselect && data["pic_line_nama"]) {
                    picSelect.tomselect.setValue(data["pic_line_nama"], true);
                }

                const mesinSelect = document.querySelector('select[name="id_mesin"]');
                if (mesinSelect && mesinSelect.tomselect && data["id_mesin"]) {
                    if (!urlIdMesin) { // Hanya override jika URL tidak melock mesin
                        mesinSelect.tomselect.setValue(data["id_mesin"], true);
                    }
                }
                
                const selectNamaPic = document.querySelector('select[name="nama_pic"]');
                if (selectNamaPic && selectNamaPic.tomselect && data["nama_pic"]) {
                    selectNamaPic.tomselect.setValue(data["nama_pic"], true);
                }

                if (hasData) {
                    // Tampilkan notifikasi kecil bahwa data berhasil dikembalikan
                    const Toast = Swal.mixin({
                      toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true
                    });
                    Toast.fire({ icon: 'info', title: 'Data isian sebelumnya berhasil dipulihkan.' });
                }
            } catch(e) {
                console.error("Autosave load error:", e);
            }
        }
    }

    // Load data after a short delay so TomSelect is ready
    setTimeout(loadFormData, 400);

    form.addEventListener('input', saveFormData);
    form.addEventListener('change', saveFormData);
    
    // Clear storage on submit
    form.addEventListener('submit', function() {
        const key = getStorageKey();
        if (key) localStorage.removeItem(key);
    });
});
</script>
