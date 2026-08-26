<?= view('layout/header', ['title' => $title]) ?>
<style>
  .custom-suggestion-wrapper { position: relative; }
  .custom-suggestion-box {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    width: 100%;
    max-height: 150px;
    overflow-y: auto;
    background-color: #1f2937; /* Dark slate */
    border: 1px solid #374151;
    border-radius: 6px;
    z-index: 1050;
    display: none;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  }
  .custom-suggestion-box::before {
    content: '';
    position: absolute;
    top: -4px;
    left: 20px;
    width: 8px;
    height: 8px;
    background-color: #1f2937;
    transform: rotate(45deg);
    border-top: 1px solid #374151;
    border-left: 1px solid #374151;
  }
  .custom-suggestion-item {
    padding: 6px 12px;
    color: #e5e7eb;
    font-size: 0.8rem;
    cursor: pointer;
    transition: background-color 0.15s;
    position: relative;
    z-index: 2;
  }
  .custom-suggestion-item:hover, .custom-suggestion-item.active {
    background-color: #374151;
    color: #60a5fa; /* Light blue accent */
    font-weight: 500;
  }
  /* Custom scrollbar for webkit */
  .custom-suggestion-box::-webkit-scrollbar { width: 5px; }
  .custom-suggestion-box::-webkit-scrollbar-track { background: #1f2937; border-radius: 6px; }
  .custom-suggestion-box::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 6px; }
  .custom-suggestion-box::-webkit-scrollbar-thumb:hover { background: #6b7280; }
</style>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <h5 class="mb-0">Master Mesin</h5>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <?php if (has_any_role(['admin', 'member', 'leader mtc'])): ?>
      <!-- Form Impor Excel -->
      <form action="<?= site_url('admin/mesin/import') ?>" method="post" enctype="multipart/form-data" class="d-flex align-items-center gap-1 border rounded p-1 bg-white shadow-sm" style="max-height: 38px;">
        <?= csrf_field() ?>
        <input type="file" name="file_excel" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required class="form-control form-control-sm" style="max-width: 170px; border:none; padding: 2px 4px; font-size: 0.8rem;" title="Pilih file Excel untuk diimpor">
        <button type="submit" class="btn btn-sm btn-success py-1 px-2 fw-semibold" style="font-size: 0.8rem;">Impor</button>
      </form>
      <!-- Link Template -->
      <a href="<?= site_url('admin/mesin/template') ?>" class="btn btn-outline-secondary btn-sm py-2">
        Unduh Template
      </a>
      <!-- Link Ekspor Excel -->
      <a href="<?= site_url('admin/mesin/export') . (!empty($_GET) ? '?' . http_build_query($_GET) : '') ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 py-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
          <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
          <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
        </svg>
        Ekspor
      </a>
      <!-- Link Download Semua QR -->
      <a href="<?= site_url('admin/mesin/download-all-qr') ?>" target="_blank" class="btn btn-outline-info btn-sm d-flex align-items-center gap-1 py-2" title="Download Semua QR Code Mesin (PDF)">
        <i class="bi bi-qr-code"></i> Download Semua QR
      </a>
      <button type="button" id="btnBatchDeleteMesin" class="btn btn-danger btn-sm py-2 d-none">
        <i class="bi bi-trash"></i> Hapus Terpilih (<span id="batchCountMesin">0</span>)
      </button>
      <a href="<?= site_url('admin/mesin/create') ?>" class="btn btn-primary btn-sm py-2">+ Tambah Mesin</a>
    <?php endif; ?>
  </div>
</div>

<div class="card-stat p-3">
  <form method="get" action="<?= site_url('admin/mesin') ?>" id="filterForm">
    <div class="table-responsive">
      <table class="table table-sm align-middle paginated-table" data-rows-per-item="1">
        <thead>
          <tr>
            <?php if (has_any_role(['admin', 'member', 'leader mtc'])): ?>
            <th style="width: 40px;" class="text-center">
              <input type="checkbox" id="checkAllMesin" class="form-check-input">
            </th>
            <?php endif; ?>
            <th>No Mesin</th>
            <th>Type</th>
            <th>Serial Nomor</th>
            <th>Plant</th>
            <th>Departemen</th>
            <th>Line</th>
            <th>Bar Feeder</th>
            <th>SN Bar Feeder</th>
            <th>Jenis</th>
            <th class="text-end">Aksi</th>
          </tr>
          <tr style="background: rgba(0,0,0,0.02);">
            <?php if (has_any_role(['admin', 'member', 'leader mtc'])): ?>
            <td></td>
            <?php endif; ?>
            <td colspan="3">
              <div class="custom-suggestion-wrapper">
                <input type="text" id="mesinSearchInput" name="q" autocomplete="off" class="form-control form-control-sm" placeholder="Cari No / Type / Serial..." value="<?= esc($filters['q'] ?? '') ?>">
                <div id="mesinSuggestionBox" class="custom-suggestion-box"></div>
              </div>
              <?php if(isset($suggestions) && is_array($suggestions)): ?>
                <script>
                  window.mesinSuggestionsData = <?= json_encode($suggestions) ?>;
                </script>
              <?php endif; ?>
            </td>
            <td>
              <?php if (session()->get('role') === 'leader'): ?>
                <!-- Untuk leader, plant kita ambil berdasarkan departemen user atau biarkan all -->
                <input type="hidden" name="plant" value="<?= esc($filters['plant'] ?? 'all') ?>">
                -
              <?php else: ?>
                <select name="plant" id="filterPlan" class="form-select form-select-sm" onchange="document.getElementById('filterLine').value = 'all'; this.form.submit();">
                  <option value="all">Semua</option>
                  <option value="Plant 1" <?= ($filters['plant'] ?? '') === 'Plant 1' ? 'selected' : '' ?>>Plant 1</option>
                  <option value="Plant 2" <?= ($filters['plant'] ?? '') === 'Plant 2' ? 'selected' : '' ?>>Plant 2</option>
                </select>
              <?php endif; ?>
            </td>
            <td>
              <?php if (session()->get('role') === 'leader'): ?>
                <input type="text" class="form-control form-control-sm" value="<?= esc(session()->get('departemen')) ?>" readonly>
                <input type="hidden" id="filterDepartemen" name="departemen" value="<?= esc(session()->get('departemen')) ?>">
              <?php else: ?>
                <select name="departemen" id="filterDepartemen" class="form-select form-select-sm" onchange="document.getElementById('filterLine').value = 'all'; this.form.submit();">
                  <option value="all">Semua</option>
                  <option value="MFG 1" <?= ($filters['departemen'] ?? '') === 'MFG 1' ? 'selected' : '' ?>>MFG 1</option>
                  <option value="MFG 2" <?= ($filters['departemen'] ?? '') === 'MFG 2' ? 'selected' : '' ?>>MFG 2</option>
                </select>
              <?php endif; ?>
            </td>
            <td>
              <select name="line" id="filterLine" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all">Semua Line</option>
                <?php foreach ($allLines ?? [] as $ln): ?>
                  <option value="<?= esc($ln) ?>" <?= ($filters['line'] ?? '') === $ln ? 'selected' : '' ?>><?= esc($ln) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td></td>
            <td></td>
            <td>
              <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all">Semua Jenis</option>
                <?php 
                  $machineCategories = $allJenis ?? [];
                  foreach ($machineCategories as $cat): 
                ?>
                  <option value="<?= esc($cat) ?>" <?= ($filters['jenis'] ?? '') === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
                <?php endforeach; ?>
              </select>
            </td>
            <td class="text-end">
              <div class="d-flex gap-1 justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary py-1 px-2" title="Cari"><i class="bi bi-search"></i></button>
                <a href="<?= site_url('admin/mesin') ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Reset"><i class="bi bi-x-lg"></i></a>
              </div>
            </td>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($daftar)): ?>
          <tr>
            <td colspan="8" class="text-center py-5">
              <i class="bi bi-inboxes text-muted mb-2" style="font-size: 2rem; display: block;"></i>
              <p class="text-muted mb-0">Tidak ada data mesin yang sesuai dengan filter.</p>
              <a href="<?= site_url('admin/mesin') ?>" class="btn btn-sm btn-outline-secondary mt-3">Reset Filter</a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($daftar as $m): ?>
            <tr>
              <?php if (has_any_role(['admin', 'member', 'leader mtc'])): ?>
              <td class="text-center">
                <input type="checkbox" class="form-check-input check-item-mesin" value="<?= $m['id_mesin'] ?>">
              </td>
              <?php endif; ?>
              <td><?= esc($m['no_mesin']) ?></td>
              <td><?= esc($m['type_mesin']) ?></td>
              <td><?= esc($m['serial_nomor']) ?></td>
              <td><span class="badge bg-primary"><?= esc($m['plant'] ?? 'Plant 1') ?></span></td>
              <td><span class="badge bg-secondary"><?= esc($m['departemen']) ?></span></td>
              <td><span class="badge bg-info text-dark"><?= esc($m['line'] ?? '-') ?></span></td>
              <td><span class="text-muted small"><?= esc($m['bar_feeder_type'] ?? '-') ?></span></td>
              <td><span class="text-muted small"><?= esc($m['sn_barfeeder'] ?? '-') ?></span></td>
              <td><span class="badge bg-secondary"><?= esc($m['jenis'] ?? '-') ?></span></td>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <button type="button" class="btn btn-sm btn-outline-primary show-qr-btn"
                          data-id="<?= (int)$m['id_mesin'] ?>"
                          data-no="<?= esc($m['no_mesin']) ?>"
                          data-type="<?= esc($m['type_mesin']) ?>"
                          data-departemen="<?= esc($m['departemen']) ?>"
                          data-serial="<?= esc($m['serial_nomor']) ?>"
                          data-jenis="<?= esc($m['jenis']) ?>"
                          data-barfeeder="<?= esc($m['bar_feeder_type']) ?>"
                          data-snbarfeeder="<?= esc($m['sn_barfeeder']) ?>">
                    <i class="bi bi-qr-code"></i> QR
                  </button>
                  <?php if (has_any_role(['admin', 'member', 'leader mtc'])): ?>
                    <a href="<?= site_url('admin/mesin/edit/' . $m['id_mesin']) ?>" class="btn btn-outline-primary btn-sm py-1 px-2" title="Edit Mesin">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" 
                            onclick="openDeleteModal(<?= $m['id_mesin'] ?>, '<?= esc($m['no_mesin'], 'js') ?>')" title="Hapus Mesin">
                      <i class="bi bi-trash"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </form>
</div>

<!-- Modal QR Code -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 340px;">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title fw-bold" id="qrModalLabel">QR Code Mesin</h6>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <div class="bg-light p-3 rounded-4 mb-3 d-inline-block">
          <img id="qrImage" src="" alt="QR Code" class="img-fluid" style="width: 200px; height: 200px; display: block; margin: 0 auto; image-rendering: pixelated;">
        </div>
        <div class="mb-3">
          <h4 class="fw-bold mb-0 text-dark" id="qrNoMesin"></h4>
        </div>
        <table class="table table-sm table-bordered text-start mx-auto mb-0" style="max-width: 250px;">
          <tbody>
            <tr>
              <td class="text-muted" style="width: 35%; font-size:0.8rem;">Type</td>
              <td style="width: 5%; text-align:center;">:</td>
              <td class="fw-bold text-secondary" id="qrTypeMesin" style="font-size:0.85rem;"></td>
            </tr>
            <tr>
              <td class="text-muted" style="font-size:0.8rem;">S/N</td>
              <td style="text-align:center;">:</td>
              <td class="fw-bold text-secondary" id="qrSerialNomor" style="font-size:0.85rem;"></td>
            </tr>
            <tr id="qrBfContainer" class="d-none">
              <td class="text-muted" style="font-size:0.8rem;">Bar Feeder</td>
              <td style="text-align:center;">:</td>
              <td class="fw-bold text-secondary" id="qrBarFeeder" style="font-size:0.85rem;"></td>
            </tr>
            <tr id="qrSnBfContainer" class="d-none">
              <td class="text-muted" style="font-size:0.8rem;">S/N BF</td>
              <td style="text-align:center;">:</td>
              <td class="fw-bold text-secondary" id="qrSnBarFeeder" style="font-size:0.85rem;"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center">
        <button type="button" class="btn btn-sm btn-primary w-100 py-2 rounded-3" id="printQrBtn">
          <i class="bi bi-printer-fill me-1"></i> Cetak QR Code
        </button>
      </div>
    </div>
  </div>
</div>


<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Filter Line sekarang dirender server-side oleh PHP dari tabel master_line


    // Suggestion Logic
    const searchInput = document.getElementById('mesinSearchInput');
    const suggestionBox = document.getElementById('mesinSuggestionBox');
    let currentFocus = -1;

    if (searchInput && suggestionBox && window.mesinSuggestionsData) {
      const data = window.mesinSuggestionsData;

      function renderSuggestions(filterText = '') {
        suggestionBox.innerHTML = '';
        const lowerFilter = filterText.toLowerCase();
        
        let matches = data;
        if (lowerFilter) {
          matches = data.filter(item => item.toLowerCase().includes(lowerFilter));
        }

        if (matches.length === 0) {
          suggestionBox.style.display = 'none';
          return;
        }

        matches.forEach(item => {
          const div = document.createElement('div');
          div.className = 'custom-suggestion-item';
          div.textContent = item;
          div.addEventListener('mousedown', function(e) {
            e.preventDefault(); // prevent input blur
            searchInput.value = item;
            suggestionBox.style.display = 'none';
            searchInput.closest('form').submit(); // Auto submit on select
          });
          suggestionBox.appendChild(div);
        });
        
        suggestionBox.style.display = 'block';
        currentFocus = -1;
      }

      searchInput.addEventListener('input', function() {
        renderSuggestions(this.value);
      });

      searchInput.addEventListener('focus', function() {
        renderSuggestions(this.value);
      });

      searchInput.addEventListener('blur', function() {
        // Add timeout to allow mousedown on item to fire first
        setTimeout(() => { suggestionBox.style.display = 'none'; }, 150);
      });

      searchInput.addEventListener('keydown', function(e) {
        let items = suggestionBox.getElementsByClassName('custom-suggestion-item');
        if (items.length === 0 || suggestionBox.style.display === 'none') return;

        if (e.key === 'ArrowDown') {
          currentFocus++;
          addActive(items);
        } else if (e.key === 'ArrowUp') {
          currentFocus--;
          addActive(items);
        } else if (e.key === 'Enter') {
          if (currentFocus > -1 && items[currentFocus]) {
            e.preventDefault();
            items[currentFocus].click();
          }
        }
      });

      function addActive(items) {
        if (!items) return false;
        removeActive(items);
        if (currentFocus >= items.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (items.length - 1);
        items[currentFocus].classList.add('active');
        // Auto scroll
        items[currentFocus].scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }

      function removeActive(items) {
        for (let i = 0; i < items.length; i++) {
          items[i].classList.remove('active');
        }
      }
    }

    const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
    const qrImage = document.getElementById('qrImage');
    const qrNoMesin = document.getElementById('qrNoMesin');
    const qrSerialNomor = document.getElementById('qrSerialNomor');
    const qrTypeMesin = document.getElementById('qrTypeMesin');
    const qrBarFeeder = document.getElementById('qrBarFeeder');
    const qrSnBarFeeder = document.getElementById('qrSnBarFeeder');
    const qrBfContainer = document.getElementById('qrBfContainer');
    const qrSnBfContainer = document.getElementById('qrSnBfContainer');

    let currentPrintData = {};

    document.querySelectorAll('.show-qr-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const no = this.getAttribute('data-no');
        const serial = this.getAttribute('data-serial');
        const type = this.getAttribute('data-type');
        const jenis = this.getAttribute('data-jenis');
        const barfeeder = this.getAttribute('data-barfeeder');
        const snbarfeeder = this.getAttribute('data-snbarfeeder');

        currentPrintData = {
          no: no,
          serial: serial || no,
          type: type,
          jenis: jenis,
          barfeeder: barfeeder,
          snbarfeeder: snbarfeeder
        };

        // URL scan mesin MTC
        const scanUrl = "<?= site_url('scan/mesin/') ?>" + id;

        // Load QR Code menggunakan API lokal offline
        qrImage.src = "<?= site_url('admin/mesin/generate-qr?data=') ?>" + encodeURIComponent(scanUrl);

        qrSerialNomor.innerText = currentPrintData.serial;
        const jenisTitle = (jenis && jenis !== '-') ? (jenis + ' ') : '';
        qrNoMesin.innerText = (jenisTitle + currentPrintData.no).toUpperCase();
        qrTypeMesin.innerText = currentPrintData.type;

        if (jenis && jenis.trim().toUpperCase() === 'CNC') {
            if (barfeeder) {
                qrBarFeeder.innerText = barfeeder;
                qrBfContainer.classList.remove('d-none');
            } else {
                qrBfContainer.classList.add('d-none');
            }
            if (snbarfeeder) {
                qrSnBarFeeder.innerText = snbarfeeder;
                qrSnBfContainer.classList.remove('d-none');
            } else {
                qrSnBfContainer.classList.add('d-none');
            }
        } else {
            qrBfContainer.classList.add('d-none');
            qrSnBfContainer.classList.add('d-none');
        }

        qrModal.show();
      });
    });

    document.getElementById('printQrBtn').addEventListener('click', function() {
      const qrSrc = qrImage.src;
      const serial = currentPrintData.serial;
      const no = currentPrintData.no;
      const type = currentPrintData.type;
      const jenis = currentPrintData.jenis;
      const barfeeder = currentPrintData.barfeeder;
      const snbarfeeder = currentPrintData.snbarfeeder;

      const titleName = (jenis && jenis !== '-') ? (jenis + ' ') : '';
      const fullTitle = (titleName + no).toUpperCase();
      let extraHtml = '<table style="margin: 0 auto; text-align: left; font-size: 0.9rem; color: #4b5563; border-collapse: collapse; width: 100%;">';
      extraHtml += '<tr><td style="border: 1px solid #e5e7eb; padding: 6px 8px; width: 35%;">Type</td><td style="border: 1px solid #e5e7eb; padding: 6px 4px; text-align: center; width: 5%;">:</td><td style="border: 1px solid #e5e7eb; font-weight: bold; padding: 6px 8px; color: #111827;">' + type + '</td></tr>';
      extraHtml += '<tr><td style="border: 1px solid #e5e7eb; padding: 6px 8px;">S/N</td><td style="border: 1px solid #e5e7eb; padding: 6px 4px; text-align: center;">:</td><td style="border: 1px solid #e5e7eb; font-weight: bold; padding: 6px 8px; color: #111827;">' + serial + '</td></tr>';
      
      if (jenis && jenis.trim().toUpperCase() === 'CNC') {
         if (barfeeder && barfeeder !== '-') {
            extraHtml += '<tr><td style="border: 1px solid #e5e7eb; padding: 6px 8px;">Bar Feeder</td><td style="border: 1px solid #e5e7eb; padding: 6px 4px; text-align: center;">:</td><td style="border: 1px solid #e5e7eb; font-weight: bold; padding: 6px 8px; color: #111827;">' + barfeeder + '</td></tr>';
         }
         if (snbarfeeder && snbarfeeder !== '-') {
            extraHtml += '<tr><td style="border: 1px solid #e5e7eb; padding: 6px 8px;">S/N BF</td><td style="border: 1px solid #e5e7eb; padding: 6px 4px; text-align: center;">:</td><td style="border: 1px solid #e5e7eb; font-weight: bold; padding: 6px 8px; color: #111827;">' + snbarfeeder + '</td></tr>';
         }
      }
      extraHtml += '</table>';

      // Gunakan iframe tersembunyi agar lebih cepat
      let iframe = document.getElementById('printIframe');
      if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'printIframe';
        iframe.style.display = 'none';
        document.body.appendChild(iframe);
      }

      const doc = iframe.contentDocument || iframe.contentWindow.document;
      doc.open();
      doc.write(
        '<html><head><title>QR - ' + serial + '</title>' +
        '<style>' +
        '@media print { body { margin: 0; } }' +
        'body { font-family: Arial, sans-serif; text-align: center; padding: 20px; background: #fff; }' +
        '.card { border: 2px solid #e5e7eb; border-radius: 16px; padding: 20px; display: inline-block; }' +
        'img { width: 180px; height: 180px; display: block; margin: 0 auto 10px; }' +
        'h2 { margin: 0 0 4px 0; font-size: 1.1rem; font-weight: 700; color: #111827; }' +
        'p { margin: 0; }' +
        '</style></head><body>' +
        '<div class="card">' +
        '<img src="' + qrSrc + '">' +
        '<h2>' + fullTitle + '</h2>' +
        '<div style="height: 10px;"></div>' +
        extraHtml +
        '</div>' +
        '</body></html>'
      );
      doc.close();

      iframe.onload = function() {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
      };
    });


  });
</script>
<!-- Modal Konfirmasi Hapus Mesin -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="" method="post" id="deleteForm">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Mesin</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Anda yakin ingin menghapus mesin <strong id="deleteMesinLabel"></strong> secara permanen?</p>
          <div class="mb-3">
            <label for="deleteReason" class="form-label">Keterangan / Alasan Dihapus <span class="text-danger">*</span></label>
            <textarea class="form-control" name="alasan" id="deleteReason" rows="3" required placeholder="Tuliskan alasan mengapa mesin ini dihapus..."></textarea>
          </div>
          <div class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-triangle"></i> Data mesin akan dipindahkan ke Log Riwayat Terhapus dan dihapus dari master secara fisik.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Ya, Hapus Permanen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function openDeleteModal(id, noMesin) {
      document.getElementById('deleteMesinLabel').innerText = noMesin;
      document.getElementById('deleteForm').action = '<?= site_url('admin/mesin/delete/') ?>' + id;
      document.getElementById('deleteReason').value = '';
      new bootstrap.Modal(document.getElementById('deleteModal')).show();
  }

  document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAllMesin');
    const checkItems = document.querySelectorAll('.check-item-mesin');
    const btnBatchDelete = document.getElementById('btnBatchDeleteMesin');
    const batchCount = document.getElementById('batchCountMesin');

    function updateBatchDeleteUI() {
      const checkedCount = document.querySelectorAll('.check-item-mesin:checked').length;
      if (checkedCount > 0) {
        btnBatchDelete.classList.remove('d-none');
        batchCount.innerText = checkedCount;
      } else {
        btnBatchDelete.classList.add('d-none');
      }
      if (checkAll) {
         checkAll.checked = (checkedCount === checkItems.length && checkItems.length > 0);
      }
    }

    if (checkAll) {
      checkAll.addEventListener('change', function() {
        checkItems.forEach(item => item.checked = this.checked);
        updateBatchDeleteUI();
      });
    }

    checkItems.forEach(item => {
      item.addEventListener('change', updateBatchDeleteUI);
    });

    if (btnBatchDelete) {
      btnBatchDelete.addEventListener('click', function() {
        const checkedItems = document.querySelectorAll('.check-item-mesin:checked');
        const ids = Array.from(checkedItems).map(item => item.value);

        if (ids.length === 0) return;

        Swal.fire({
          title: 'Hapus ' + ids.length + ' Mesin?',
          text: 'Masukkan keterangan/alasan penghapusan massal ini:',
          input: 'textarea',
          inputPlaceholder: 'Tuliskan alasan...',
          inputAttributes: {
            'aria-label': 'Tuliskan alasan'
          },
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          confirmButtonText: 'Ya, Hapus Permanen',
          cancelButtonText: 'Batal',
          preConfirm: (alasan) => {
            if (!alasan) {
              Swal.showValidationMessage('Alasan penghapusan harus diisi!');
            }
            return alasan;
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Menghapus...',
              text: 'Mohon tunggu',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            fetch('<?= site_url('admin/mesin/delete-batch') ?>', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: JSON.stringify({ ids: ids, alasan: result.value })
            })
            .then(response => response.json())
            .then(data => {
              if (data.status) {
                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                  window.location.reload();
                });
              } else {
                Swal.fire('Gagal!', data.message || 'Terjadi kesalahan', 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire('Gagal!', 'Terjadi kesalahan sistem', 'error');
            });
          }
        });
      });
    }
  });
</script>

<?= view('layout/footer') ?>
