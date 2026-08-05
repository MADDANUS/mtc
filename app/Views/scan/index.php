<?= view('layout/header', ['title' => $title]) ?>

<div class="page-header">
  <div>
    <h5 class="mb-0"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Pindai QR Code Mesin</h5>
    <p class="text-muted small mb-0">Arahkan kamera ponsel Anda ke stiker QR Code yang tertempel pada mesin fisik.</p>
  </div>
</div>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card border-0 shadow-sm bg-white overflow-hidden p-4 text-center">
      <div id="reader-wrapper" style="position: relative;">
        <!-- Area Video Pemindai -->
        <div id="reader" class="border-0 bg-light rounded-3" style="width: 100%; min-height: 280px; overflow: hidden;"></div>
      </div>
      
      <div class="mt-3">
        <span class="badge bg-secondary p-2 d-inline-flex align-items-center gap-1">
          <i class="bi bi-camera-fill"></i> In-App Scanner
        </span>
        <p class="text-muted small mt-2 mb-0">
          Izin akses kamera diperlukan untuk menggunakan fitur scanner web ini.
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Local html5-qrcode -->
<script src="<?= base_url('assets/js/html5-qrcode.min.js') ?>" type="text/javascript"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Inisialisasi scanner
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
      // Verifikasi apakah QR Code berisi tautan valid ke scan mesin sistem kita
      if (decodedText.includes('/scan/mesin/')) {
        // Hentikan pemindaian kamera untuk mencegah redirect berulang
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
      // Abaikan kegagalan frame pembacaan berkala (karena kamera terus melacak)
    }

    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
  });
</script>

<style>
  /* Kustomisasi gaya elemen html5-qrcode untuk mencocokkan dengan minimal design */
  #reader {
    border: none !important;
  }
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
