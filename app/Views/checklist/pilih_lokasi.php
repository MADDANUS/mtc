<?= view('layout/header', ['title' => $title]) ?>

<div class="row justify-content-center my-4">
  <div class="col-md-8 text-center mb-3">
    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-check2-square me-2 text-primary"></i>Pilih Pengecekan Mesin</h5>
    <p class="text-muted small">Silakan pilih kategori dan lokasi manufaktur (Manufacturing Line) untuk memulai pengecekan mesin.</p>
  </div>
</div>

<div class="row justify-content-center g-4">
  <!-- Area Scanner Langsung -->
  <div class="col-md-10 mb-2">
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-2">
      <div class="card-body p-4 text-center bg-white">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Pindai QR Code Mesin</h5>
        <div id="reader-wrapper" style="position: relative; max-width: 450px; margin: 0 auto;">
          <div id="reader" class="border-0 bg-light rounded-3 shadow-sm" style="width: 100%; min-height: 250px; overflow: hidden;"></div>
        </div>
        <p class="text-muted small mt-3 mb-0">
          Arahkan kamera ke stiker QR pada mesin fisik untuk langsung memulai pengecekan mesin tanpa perlu memilih manual.
        </p>
      </div>
    </div>
  </div>

  <!-- Card Preventive -->
  <div class="col-md-5">
    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden card-hover transition" style="transition: transform 0.2s, box-shadow 0.2s;">
      <div class="card-body p-4 d-flex flex-column text-center" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 5px solid var(--primary);">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-3 rounded-circle mb-3 mx-auto" style="width: 70px; height: 70px;">
          <i class="bi bi-calendar-check" style="font-size: 2rem; color: var(--primary);"></i>
        </div>
        <h6 class="card-title fw-bold text-dark mb-2">Checklist Report</h6>
        <p class="card-text text-muted small mb-4">Melakukan pengecekan rutin / preventive maintenance untuk mesin di area produksi MFG 1 &amp; MFG 2.</p>
        
        <div class="row g-2 mt-auto">
          <div class="col-6">
            <a href="<?= site_url('checklist/mfg1/checklist-report') ?>" class="btn btn-outline-primary w-100 py-2 fw-bold rounded-3">MFG 1</a>
          </div>
          <div class="col-6">
            <a href="<?= site_url('checklist/mfg2/checklist-report') ?>" class="btn btn-outline-primary w-100 py-2 fw-bold rounded-3">MFG 2</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Card Overhaul -->
  <div class="col-md-5">
    <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden card-hover transition" style="transition: transform 0.2s, box-shadow 0.2s;">
      <div class="card-body p-4 d-flex flex-column text-center" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 5px solid #f43f5e;">
        <div class="d-inline-flex align-items-center justify-content-center bg-opacity-10 p-3 rounded-circle mb-3 mx-auto" style="width: 70px; height: 70px; background-color: rgba(244, 63, 94, 0.1); color: #f43f5e;">
          <i class="bi bi-tools" style="font-size: 2rem;"></i>
        </div>
        <h6 class="card-title fw-bold text-dark mb-2">Inspection Report</h6>
        <p class="card-text text-muted small mb-4">Melakukan pembongkaran / overhaul maintenance untuk mesin di area produksi MFG 1 &amp; MFG 2.</p>
        
        <div class="row g-2 mt-auto">
          <div class="col-6">
            <a href="<?= site_url('checklist/mfg1/overhaul') ?>" class="btn btn-outline-danger w-100 py-2 fw-bold rounded-3" style="color: #f43f5e; border-color: #f43f5e;">MFG 1</a>
          </div>
          <div class="col-6">
            <a href="<?= site_url('checklist/mfg2/overhaul') ?>" class="btn btn-outline-danger w-100 py-2 fw-bold rounded-3" style="color: #f43f5e; border-color: #f43f5e;">MFG 2</a>
          </div>
        </div>
      </div>
    </div>
  </div>
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

<!-- CDN html5-qrcode -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const html5QrcodeScanner = new Html5QrcodeScanner(
      "reader", 
      { 
        fps: 15, 
        qrbox: { width: 230, height: 230 },
        aspectRatio: 1.0
      },
      /* verbose= */ false
    );

    function onScanSuccess(decodedText, decodedResult) {
      if (decodedText.includes('/scan/mesin/')) {
        html5QrcodeScanner.clear().then(() => {
          window.location.href = decodedText;
        }).catch(() => {
          window.location.href = decodedText;
        });
      } else {
        alert("QR Code tidak valid! Pastikan Anda memindai stiker QR Code Mesin MTCE resmi.");
      }
    }

    function onScanFailure(error) {
      // Abaikan kegagalan frame
    }

    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
  });
</script>

<style>
  #reader { border: none !important; }
  #reader__dashboard_section_csr button {
    background-color: var(--accent) !important;
    border-color: var(--accent) !important;
    color: #fff !important;
    border-radius: var(--radius-sm) !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    padding: 0.4rem 0.85rem !important;
    border: none !important;
    box-shadow: var(--shadow-sm) !important;
    transition: background 0.15s !important;
  }
  #reader__dashboard_section_csr button:hover {
    background-color: var(--accent-hover) !important;
  }
  #reader__dashboard_section_csr select {
    border-radius: var(--radius-sm) !important;
    border: 1.5px solid var(--border) !important;
    padding: 0.35rem 0.65rem !important;
    font-size: 0.825rem !important;
  }
  #reader__status_span {
    font-size: 0.8rem !important;
    color: var(--text-secondary) !important;
  }
</style>

<?= view('layout/footer') ?>
