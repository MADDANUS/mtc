<?= view('layout/header', ['title' => $title]) ?>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card border-0 shadow-sm rounded-4 mt-4">
      <div class="card-body p-4 p-md-5">
        <div class="text-center mb-4">
          <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-3" style="width: 64px; height: 64px;">
            <i class="bi bi-key" style="font-size: 2rem;"></i>
          </div>
          <h4 class="fw-bold mb-1">Ganti Password</h4>
          <p class="text-muted small">Silakan perbarui password Anda untuk keamanan akun.</p>
        </div>

        <form action="<?= site_url('ganti-password') ?>" method="post">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label fw-semibold small">Password Lama</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
              <input type="password" name="password_lama" class="form-control border-start-0 ps-0" placeholder="Masukkan password lama" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small">Password Baru</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
              <input type="password" name="password_baru" class="form-control border-start-0 ps-0" placeholder="Minimal 6 karakter" required minlength="6">
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-semibold small">Konfirmasi Password Baru</label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-check text-muted"></i></span>
              <input type="password" name="konfirmasi_password" class="form-control border-start-0 ps-0" placeholder="Ketik ulang password baru" required minlength="6">
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 shadow-sm">
            Simpan Password
          </button>
        </form>

      </div>
    </div>
  </div>
</div>

<?= view('layout/footer') ?>
