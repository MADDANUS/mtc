    </main>
  </div>
</div>
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script>
  // Mobile sidebar toggle functionality
  document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay')?.classList.toggle('show');
  });

  // Close sidebar when overlay is clicked
  document.getElementById('sidebarOverlay')?.addEventListener('click', function() {
    document.querySelector('.sidebar').classList.remove('show');
    this.classList.remove('show');
  });
</script>
<script src="<?= base_url('assets/js/sweetalert2.all.min.js') ?>"></script>
<script src="<?= base_url('assets/js/tom-select.complete.min.js') ?>"></script>
<script>
  document.querySelectorAll('.searchable-select').forEach((el) => {
    let maxItems = el.getAttribute('data-max-items');
    let config = {
      create: false,
      plugins: []
    };
    if (el.getAttribute('data-no-sort') !== 'true') {
      config.sortField = { field: "text", direction: "asc" };
    }
    if (maxItems) {
        config.maxItems = parseInt(maxItems);
    }
    if (!el.hasAttribute('multiple')) {
        config.plugins.push('dropdown_input');
    }
    new TomSelect(el, config);
  });

  // Logout confirmation
  const btnLogout = document.getElementById('btn-logout');
  if (btnLogout) {
    btnLogout.addEventListener('click', function(e) {
      e.preventDefault();
      const href = this.getAttribute('href');
      Swal.fire({
        title: 'Yakin ingin keluar?',
        text: 'Anda harus login kembali untuk masuk ke sistem.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Keluar!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = href;
        }
      });
    });
  }
</script>
  <script src="<?= base_url('js/table-pagination.js?v=' . time()) ?>"></script>
<script>
  <?php if (session()->getFlashdata('success')): ?>
    let audioSuccess = new Audio('<?= base_url('audio/success.ogg') ?>');
    audioSuccess.play().catch(e => console.log("Audio autoplay prevented"));
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: <?= json_encode(session()->getFlashdata('success')) ?>,
      timer: 3000,
      showConfirmButton: false
    });
  <?php endif; ?>
  
  <?php if (session()->getFlashdata('error')): ?>
    let audioError = new Audio('<?= base_url('audio/error.ogg') ?>');
    audioError.play().catch(e => console.log("Audio autoplay prevented"));
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: <?= json_encode(session()->getFlashdata('error')) ?>,
      timer: 3000,
      showConfirmButton: false
    });
  <?php endif; ?>

  <?php if (session()->getFlashdata('warning')): ?>
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan',
      html: <?= json_encode(session()->getFlashdata('warning')) ?>,
      showConfirmButton: true,
      confirmButtonColor: '#f59e0b'
    });
  <?php endif; ?>
</script>
</body>
</html>
