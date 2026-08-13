<?= view('layout/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <h5 class="mb-0"><?= esc($title) ?></h5>
  <a href="/admin/user" class="btn btn-sm btn-primary">Lihat Master User</a>
</div>

<?php
  $countBerhasil = count($results['berhasil']);
  $countDilewati = count($results['dilewati']);
  $countGagal    = count($results['gagal']);
?>

<!-- Ringkasan -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-success text-center">
      <div class="card-body">
        <h2 class="fw-bold text-success"><?= $countBerhasil ?></h2>
        <div class="text-muted">Akun Baru Berhasil Dibuat</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-warning text-center">
      <div class="card-body">
        <h2 class="fw-bold text-warning"><?= $countDilewati ?></h2>
        <div class="text-muted">Dilewati (Sudah Punya Akun)</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-danger text-center">
      <div class="card-body">
        <h2 class="fw-bold text-danger"><?= $countGagal ?></h2>
        <div class="text-muted">Gagal</div>
      </div>
    </div>
  </div>
</div>

<?php if ($countBerhasil > 0): ?>
<div class="alert alert-info mb-4">
  <strong>Password default untuk semua akun baru: <code>123456</code></strong><br>
  Minta setiap karyawan untuk login dan segera mengganti password mereka melalui profil akun.
</div>
<?php endif; ?>

<!-- Tabel Berhasil -->
<?php if ($countBerhasil > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-success text-white fw-semibold">
    ✅ Akun Berhasil Dibuat (<?= $countBerhasil ?>)
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-bordered table-hover mb-0">
      <thead class="table-light">
        <tr><th>ID / Username</th><th>Nama</th><th>Role</th><th>Line</th></tr>
      </thead>
      <tbody>
        <?php foreach ($results['berhasil'] as $r): ?>
        <tr>
          <td><code><?= esc($r['id_pic']) ?></code></td>
          <td><?= esc($r['nama_pic']) ?></td>
          <td><span class="badge bg-primary"><?= esc($r['role']) ?></span></td>
          <td><?= esc($r['line']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Tabel Dilewati -->
<?php if ($countDilewati > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-warning text-dark fw-semibold">
    ⚠️ Dilewati — Sudah Punya Akun (<?= $countDilewati ?>)
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-bordered mb-0">
      <thead class="table-light">
        <tr><th>ID PIC</th><th>Nama PIC</th><th>Keterangan</th></tr>
      </thead>
      <tbody>
        <?php foreach ($results['dilewati'] as $r): ?>
        <tr>
          <td><code><?= esc($r['id_pic']) ?></code></td>
          <td><?= esc($r['nama_pic']) ?></td>
          <td class="text-muted small"><?= esc($r['alasan']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- Tabel Gagal -->
<?php if ($countGagal > 0): ?>
<div class="card mb-4">
  <div class="card-header bg-danger text-white fw-semibold">
    ❌ Gagal (<?= $countGagal ?>)
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-bordered mb-0">
      <thead class="table-light">
        <tr><th>ID PIC</th><th>Nama PIC</th><th>Error</th></tr>
      </thead>
      <tbody>
        <?php foreach ($results['gagal'] as $r): ?>
        <tr>
          <td><code><?= esc($r['id_pic']) ?></code></td>
          <td><?= esc($r['nama_pic']) ?></td>
          <td class="text-danger small"><?= esc($r['alasan']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?= view('layout/footer') ?>
