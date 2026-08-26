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
<!-- jsQR: decoder untuk gambar lokal -->
<script src="<?= base_url('assets/js/jsQR.min.js') ?>"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {

    function navigateToResult(url) {
      if (url.includes('/scan/mesin/')) {
        window.location.href = url;
      } else {
        alert("QR Code tidak valid! Pastikan Anda memindai stiker QR Code Mesin MTCE resmi.");
      }
    }

    // ── Decode file gambar dengan jsQR ─────────────────────────────────────
    function decodeFileWithJsQR(file) {
      var fr = new FileReader();
      fr.onload = function(e) {
        var img = new Image();
        img.onload = function() {
          var canvas = document.createElement('canvas');
          canvas.width  = img.naturalWidth;
          canvas.height = img.naturalHeight;
          var ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0);
          var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

          var code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: "attemptBoth" });
          if (code) {
            navigateToResult(code.data);
          }
        };
        img.src = e.target.result;
      };
      fr.readAsDataURL(file);
    }

    // ── Event delegation: tangkap semua file input di dalam #reader ────────
    // Ini bekerja meskipun html5-qrcode membuat ulang elemen input setiap saat,
    // karena listener dipasang di document, bukan di elemen spesifik.
    document.addEventListener('change', function(e) {
      var target = e.target;
      if (
        target.tagName === 'INPUT' &&
        target.type === 'file' &&
        document.getElementById('reader') &&
        document.getElementById('reader').contains(target)
      ) {
        if (target.files && target.files[0]) {
          decodeFileWithJsQR(target.files[0]);
        }
      }
    });

    // ── Scanner kamera utama ───────────────────────────────────────────────
    const html5QrcodeScanner = new Html5QrcodeScanner(
      "reader", 
      { fps: 15, qrbox: { width: 230, height: 230 }, aspectRatio: 1.0 },
      false
    );

    html5QrcodeScanner.render(
      function(decodedText) {
        html5QrcodeScanner.clear()
          .then(function()  { navigateToResult(decodedText); })
          .catch(function() { navigateToResult(decodedText); });
      },
      function() { /* abaikan error frame kamera */ }
    );
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
    color: #000 !important;
    border-radius: var(--radius-sm) !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    padding: 0.4rem 0.85rem !important;
    border: 1px solid #ccc !important;
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
