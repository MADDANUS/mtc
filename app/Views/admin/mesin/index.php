<?= view('layout/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <h5 class="mb-0">Master Mesin</h5>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <?php if (session()->get('role') === 'admin'): ?>
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
      <a href="<?= site_url('admin/mesin/export') ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 py-2">
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
  <?php if (empty($daftar)): ?>
    <p class="text-muted mb-0">Belum ada data mesin.</p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
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
        </thead>
        <tbody>
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
                  <?php if (session()->get('role') === 'admin'): ?>
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
        </tbody>
      </table>
    </div>
  <?php endif; ?>
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

<script>
  document.addEventListener("DOMContentLoaded", function() {
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

        // Load QR Code menggunakan API qrserver gratis
        qrImage.src = "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" + encodeURIComponent(scanUrl);

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
          <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
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
  });
</script>

<?= view('layout/footer') ?>
