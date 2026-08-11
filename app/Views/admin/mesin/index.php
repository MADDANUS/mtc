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
    <?php if (in_array(session()->get('role'), ['admin', 'member'])): ?>
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
            <th>No Mesin</th>
            <th>Type</th>
            <th>Serial Nomor</th>
            <th>Lokasi</th>
            <th>Line</th>
            <th>Bar Feeder</th>
            <th>Jenis</th>
            <th class="text-end">Aksi</th>
          </tr>
          <tr style="background: rgba(0,0,0,0.02);">
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
                <input type="text" class="form-control form-control-sm" value="<?= esc(session()->get('lokasi')) ?>" readonly>
                <input type="hidden" id="filterLokasi" name="lokasi" value="<?= esc(session()->get('lokasi')) ?>">
              <?php else: ?>
                <select name="lokasi" id="filterLokasi" class="form-select form-select-sm" onchange="this.form.submit()">
                  <option value="all">Semua Lokasi</option>
                  <option value="MFG 1" <?= ($filters['lokasi'] ?? '') === 'MFG 1' ? 'selected' : '' ?>>MFG 1</option>
                  <option value="MFG 2" <?= ($filters['lokasi'] ?? '') === 'MFG 2' ? 'selected' : '' ?>>MFG 2</option>
                </select>
              <?php endif; ?>
            </td>
            <td>
              <select name="line" id="filterLine" class="form-select form-select-sm" onchange="this.form.submit()" data-selected="<?= esc($filters['line'] ?? 'all') ?>">
                <option value="all">Semua Line</option>
              </select>
            </td>
            <td></td>
            <td>
              <select name="jenis" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="all">Semua Jenis</option>
                <?php 
                  $machineCategories = [
                      'THREAD', 'DOUBLE MILLING', 'MILLING', 'DOUBLE CENTER DRILL', 'OSL', 
                      'KNURLING', 'BROTHER', 'BURNISHING', 'BUFFING', 'CENTERING GRINDING',
                      'CNC', 'CAM', '-'
                  ];
                  foreach ($machineCategories as $cat): 
                ?>
                  <option value="<?= $cat ?>" <?= ($filters['jenis'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
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
              <td><?= esc($m['no_mesin']) ?></td>
              <td><?= esc($m['type_mesin']) ?></td>
              <td><?= esc($m['serial_nomor']) ?></td>
              <td><span class="badge bg-secondary"><?= esc($m['lokasi']) ?></span></td>
              <td><span class="badge bg-info text-dark"><?= esc($m['line'] ?? '-') ?></span></td>
              <td><span class="text-muted small"><?= esc($m['bar_feeder_type'] ?? '-') ?></span></td>
              <td><span class="badge bg-secondary"><?= esc($m['jenis'] ?? '-') ?></span></td>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <button type="button" class="btn btn-sm btn-outline-primary show-qr-btn"
                          data-id="<?= (int)$m['id_mesin'] ?>"
                          data-no="<?= esc($m['no_mesin']) ?>"
                          data-type="<?= esc($m['type_mesin']) ?>"
                          data-lokasi="<?= esc($m['lokasi']) ?>">
                    <i class="bi bi-qr-code"></i> QR
                  </button>
                  <?php if (in_array(session()->get('role'), ['admin', 'member'])): ?>
                    <button type="button" class="btn btn-outline-info btn-sm py-1 px-2 btn-riwayat-mesin" 
                            data-id="<?= $m['id_mesin'] ?>" 
                            data-no="<?= esc($m['no_mesin']) ?>"
                            title="Riwayat Mesin">
                      <i class="bi bi-clock-history"></i>
                    </button>
                    <a href="<?= site_url('admin/mesin/edit/' . $m['id_mesin']) ?>" class="btn btn-outline-primary btn-sm py-1 px-2" title="Edit Mesin">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="<?= site_url('admin/mesin/delete/' . $m['id_mesin']) ?>" class="btn btn-outline-danger btn-sm py-1 px-2"
                       onclick="return confirm('Hapus mesin <?= esc($m['no_mesin'], 'js') ?>?');" title="Hapus Mesin">
                      <i class="bi bi-trash"></i>
                    </a>
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
        <h6 class="fw-bold mb-1" id="qrNoMesin"></h6>
        <p class="text-muted small mb-2" id="qrTypeMesin"></p>
        <span class="badge bg-primary" id="qrLokasiMesin" style="background-color: var(--accent) !important;"></span>
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center">
        <button type="button" class="btn btn-sm btn-primary w-100 py-2 rounded-3" id="printQrBtn">
          <i class="bi bi-printer-fill me-1"></i> Cetak QR Code
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Riwayat -->
<div class="modal fade" id="riwayatModal" tabindex="-1" aria-labelledby="riwayatModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title fw-bold" id="riwayatModalLabel">Riwayat Mesin</h6>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="riwayatModalBody">
        <div class="text-center text-muted" id="riwayatLoading">
          <div class="spinner-border spinner-border-sm me-1" role="status"></div> Memuat riwayat...
        </div>
        <div id="riwayatContent" class="timeline-container d-none">
          <!-- Timeline items will be injected here -->
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.timeline-container { position: relative; padding-left: 1.5rem; }
.timeline-container::before {
    content: ''; position: absolute; left: 0.35rem; top: 0; bottom: 0;
    width: 2px; background: #e9ecef;
}
.timeline-item { position: relative; margin-bottom: 1.5rem; }
.timeline-item:last-child { margin-bottom: 0; }
.timeline-item::before {
    content: ''; position: absolute; left: -1.45rem; top: 0.25rem;
    width: 10px; height: 10px; border-radius: 50%;
    background: var(--bs-primary, #0d6efd);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
}
.timeline-date { font-size: 0.85rem; font-weight: 600; color: #343a40; margin-bottom: 0.1rem; }
.timeline-admin { font-size: 0.75rem; color: #6c757d; margin-bottom: 0.4rem; display: block; }
.timeline-changes { margin: 0; padding-left: 0; list-style-type: none; }
.timeline-changes li { font-size: 0.85rem; position: relative; margin-bottom: 0.3rem; padding-left: 1.3rem; color: #495057; }
.timeline-changes li::before {
    content: '🔄'; position: absolute; left: 0; top: 0; font-size: 0.75rem;
}
.val-lama { text-decoration: line-through; color: #dc3545; }
.val-baru { color: #198754; font-weight: 500; }
</style>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Dynamic Filter Line Logic
    const filterLokasi = document.getElementById('filterLokasi');
    const filterLine = document.getElementById('filterLine');
    if (filterLokasi && filterLine) {
      const linesData = {
          'MFG 1': ['Line 1', 'Line 2', 'Line 3'],
          'MFG 2': ['CG', 'Second']
      };
      const selectedLokasi = filterLokasi.value;
      const selectedLine = filterLine.getAttribute('data-selected');
      
      filterLine.innerHTML = '<option value="all">Semua Line</option>';
      if (linesData[selectedLokasi]) {
          linesData[selectedLokasi].forEach(line => {
              const option = document.createElement('option');
              option.value = line;
              option.textContent = line;
              if (line === selectedLine) {
                  option.selected = true;
              }
              filterLine.appendChild(option);
          });
      }
    }

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
    const qrTypeMesin = document.getElementById('qrTypeMesin');
    const qrLokasiMesin = document.getElementById('qrLokasiMesin');

    document.querySelectorAll('.show-qr-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const no = this.getAttribute('data-no');
        const type = this.getAttribute('data-type');
        const lokasi = this.getAttribute('data-lokasi');

        // URL scan mesin MTCE
        const scanUrl = "<?= site_url('scan/mesin/') ?>" + id;

        // Load QR Code menggunakan API lokal offline
        qrImage.src = "<?= site_url('admin/mesin/generate-qr?data=') ?>" + encodeURIComponent(scanUrl);

        qrNoMesin.innerText = no;
        qrTypeMesin.innerText = type;
        qrLokasiMesin.innerText = lokasi;

        qrModal.show();
      });
    });

    document.getElementById('printQrBtn').addEventListener('click', function() {
      const no = qrNoMesin.innerText;
      const type = qrTypeMesin.innerText;
      const lokasi = qrLokasiMesin.innerText;
      const qrSrc = qrImage.src;

      // Buka popup window baru khusus cetak
      const printWin = window.open('', '_blank', 'width=450,height=550');
      printWin.document.write(`
        <html>
        <head>
          <title>Cetak QR Code - \${no}</title>
          <style>
            body {
              font-family: 'Inter', sans-serif;
              text-align: center;
              padding: 20px;
              margin: 0;
              display: flex;
              align-items: center;
              justify-content: center;
              height: 100vh;
              box-sizing: border-box;
              background-color: #ffffff;
            }
            .card {
              border: 3px solid #e5e7eb;
              border-radius: 24px;
              padding: 30px;
              max-width: 320px;
              background: #ffffff;
              box-sizing: border-box;
            }
            .logo {
              font-size: 0.75rem;
              font-weight: 700;
              letter-spacing: 0.12em;
              color: #4f46e5;
              margin-bottom: 25px;
              text-transform: uppercase;
            }
            .qr-wrapper {
              background: #f9fafb;
              padding: 15px;
              border-radius: 16px;
              display: inline-block;
              margin-bottom: 25px;
              border: 1px solid #f3f4f6;
            }
            .qr-img {
              width: 210px;
              height: 210px;
              display: block;
            }
            h2 {
              margin: 0 0 6px 0;
              font-size: 1.6rem;
              font-weight: 700;
              color: #111827;
            }
            p {
              margin: 0 0 18px 0;
              font-size: 0.85rem;
              color: #6b7280;
            }
            .badge {
              background: #4f46e5;
              color: #ffffff;
              padding: 6px 14px;
              font-size: 0.75rem;
              font-weight: 600;
              border-radius: 50px;
              text-transform: uppercase;
              letter-spacing: 0.05em;
            }
          </style>
        </head>
        <body>
          <div class="card">
            <div class="logo">MTCE SYSTEM QR</div>
            <div class="qr-wrapper">
              <img class="qr-img" src="\${qrSrc}">
            </div>
            <h2>\${no}</h2>
            <p>\${type}</p>
            <span class="badge">\${lokasi}</span>
          </div>
          <script>
            window.onload = function() {
              window.print();
              setTimeout(function() { window.close(); }, 500);
            };
          <\/script>
        </body>
        </html>
      `);
      printWin.document.close();
    });

    // --- RIWAYAT MODAL LOGIC ---
    const riwayatModal = new bootstrap.Modal(document.getElementById('riwayatModal'));
    const btnRiwayat = document.querySelectorAll('.btn-riwayat-mesin');
    
    btnRiwayat.forEach(btn => {
      btn.addEventListener('click', function() {
        const idMesin = this.getAttribute('data-id');
        const noMesin = this.getAttribute('data-no');
        
        document.getElementById('riwayatModalLabel').innerText = `Riwayat Mesin: ${noMesin}`;
        document.getElementById('riwayatLoading').classList.remove('d-none');
        const contentDiv = document.getElementById('riwayatContent');
        contentDiv.classList.add('d-none');
        contentDiv.innerHTML = '';
        
        riwayatModal.show();
        
        fetch(`<?= site_url('admin/mesin/riwayat/') ?>${idMesin}`)
          .then(res => res.json())
          .then(data => {
            document.getElementById('riwayatLoading').classList.add('d-none');
            contentDiv.classList.remove('d-none');
            
            if (!data || data.length === 0) {
              contentDiv.innerHTML = '<div class="text-center text-muted"><i class="bi bi-info-circle"></i> Tidak ada riwayat.</div>';
              return;
            }
            
            // Group by waktu
            const grouped = {};
            data.forEach(item => {
                const timeKey = item.created_at.substring(0, 16) + '_' + item.diubah_oleh;
                if (!grouped[timeKey]) {
                    grouped[timeKey] = {
                        date: new Date(item.created_at).toLocaleString('id-ID', {day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute:'2-digit'}),
                        admin: item.nama_admin || 'Sistem',
                        changes: []
                    };
                }
                grouped[timeKey].changes.push(item);
            });
            
            let html = '';
            for (const key in grouped) {
                const group = grouped[key];
                html += `
                <div class="timeline-item">
                    <div class="timeline-date">${group.date}</div>
                    <span class="timeline-admin"><i class="bi bi-person-fill"></i> Oleh: ${group.admin}</span>
                    <ul class="timeline-changes">
                `;
                
                group.changes.forEach(change => {
                    const fieldLabel = change.kolom_diubah.replace(/_/g, ' ').toUpperCase();
                    const oldVal = change.nilai_lama || '-';
                    const newVal = change.nilai_baru || '-';
                    html += `<li><strong>${fieldLabel}</strong> diubah dari <span class="val-lama">${oldVal}</span> menjadi <span class="val-baru">${newVal}</span></li>`;
                });
                
                html += `</ul></div>`;
            }
            
            contentDiv.innerHTML = html;
          })
          .catch(err => {
            document.getElementById('riwayatLoading').classList.add('d-none');
            contentDiv.classList.remove('d-none');
            contentDiv.innerHTML = '<div class="text-center text-muted"><i class="bi bi-info-circle"></i> Tidak ada riwayat.</div>';
          });
      });
    });
  });
</script>

<?= view('layout/footer') ?>
