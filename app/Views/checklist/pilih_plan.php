<?= view('layout/header', ['title' => $title ?? 'Pilih plant Pengecekan']) ?>

<div class="container-fluid pt-3 px-4 pb-3">

    <!-- Header Section -->
    <div class="row mb-3">
        <div class="col-12 text-center">
            <h3 class="fw-bold text-dark mb-2">Pilih plant Mesin</h3>
            <p class="text-muted mb-0 mx-auto" style="max-width: 600px; font-size: 0.9rem;">
                Silakan pilih plant untuk melanjutkan ke pengecekan, atau langsung scan QR Code mesin.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <!-- Top Center: QR Scanner -->
    <div class="row justify-content-center mb-3">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm bg-white overflow-hidden p-4 text-center h-100" style="transition: transform 0.2s, box-shadow 0.2s;">
                <div id="reader-wrapper" style="position: relative; width: 100%; margin: 0 auto;">
                    <div id="reader" class="border-0 bg-light rounded-3 shadow-sm" style="width: 100%; min-height: 250px; overflow: hidden;"></div>
                </div>
                
                <div class="mt-3">
                    <span class="badge bg-secondary p-2 d-inline-flex align-items-center gap-1">
                        <i class="bi bi-camera-fill"></i> In-App Scanner
                    </span>
                    <p class="text-muted small mt-2 mb-0">
                        Arahkan kamera ke stiker QR Code mesin MTCE Anda untuk langsung menuju form pengecekan.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom: plant Choices -->
    <div class="row justify-content-center g-4">
        <!-- Plant 1 Card -->
        <div class="col-md-5 col-lg-4">
            <a href="<?= base_url('checklist/plant/plant-1') ?>" class="text-decoration-none h-100 d-block">
                <div class="card h-100 border-0 shadow-sm hover-elevate rounded-4 overflow-hidden" 
                     style="transition: transform 0.2s, box-shadow 0.2s;">
                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center"
                         style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 4px solid var(--primary);">
                        
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-3 rounded-circle mb-3 mx-auto" style="width: 70px; height: 70px;">
                            <i class="bi bi-building" style="font-size: 2rem; color: var(--primary);"></i>
                        </div>
                        
                        <h4 class="fw-bold text-dark mb-0">Plant 1</h4>
                    </div>
                </div>
            </a>
        </div>

        <!-- Plant 2 Card -->
        <div class="col-md-5 col-lg-4">
            <a href="<?= base_url('checklist/plant/plant-2') ?>" class="text-decoration-none h-100 d-block">
                <div class="card h-100 border-0 shadow-sm hover-elevate rounded-4 overflow-hidden" 
                     style="transition: transform 0.2s, box-shadow 0.2s;">
                    <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center text-center"
                         style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-top: 4px solid var(--info);">
                        
                        <div class="d-inline-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info p-3 rounded-circle mb-3 mx-auto" style="width: 70px; height: 70px;">
                            <i class="bi bi-buildings" style="font-size: 2rem; color: var(--info);"></i>
                        </div>
                        
                        <h4 class="fw-bold text-dark mb-0">Plant 2</h4>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Local html5-qrcode -->
<script src="<?= base_url('assets/js/html5-qrcode.min.js') ?>" type="text/javascript"></script>
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
.hover-elevate:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}
#reader { border: none !important; }
#reader__dashboard_section_csr button {
    background-color: var(--primary) !important;
    border-color: var(--primary) !important;
    color: #fff !important;
    border-radius: var(--radius-sm) !important;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    padding: 0.4rem 0.85rem !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    transition: background 0.15s !important;
}
#reader__dashboard_section_csr button:hover {
    background-color: #0d6efd !important;
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
