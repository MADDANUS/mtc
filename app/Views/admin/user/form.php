<?= view('layout/header', ['title' => $title]) ?>

<h5 class="mb-3"><?= esc($title) ?></h5>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger shadow-sm border-0 mb-4"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger shadow-sm border-0 mb-4">
    <ul class="mb-0">
      <?php foreach (session()->getFlashdata('errors') as $error): ?>
        <li><?= esc($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success shadow-sm border-0 mb-4"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="card-stat p-3" style="max-width:600px;">
  <form action="<?= $user ? site_url('admin/user/update/' . $user['id']) : site_url('admin/user/store') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input type="text" name="nama" class="form-control" required
             value="<?= esc(old('nama', $user['nama'] ?? '')) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" required
             value="<?= esc(old('username', $user['username'] ?? '')) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">
        Password <?= $user ? '<span class="text-muted small">(kosongkan jika tidak diubah)</span>' : '' ?>
      </label>
      <input type="password" name="password" class="form-control" <?= $user ? '' : 'required' ?>>
    </div>
    <div class="mb-3">
      <label class="form-label">Role</label>
      <?php $roleVal = old('role', $user['role'] ?? 'magang'); ?>
      <select name="role" class="form-select" required>
        <option value="magang" <?= $roleVal === 'magang' ? 'selected' : '' ?>>PIC (Magang)</option>
        <option value="member" <?= $roleVal === 'member' ? 'selected' : '' ?>>PIC MTC (Member)</option>
        <option value="sheadprd" <?= $roleVal === 'sheadprd' ? 'selected' : '' ?>>Section Head Produksi</option>
        <option value="leader" <?= $roleVal === 'leader' ? 'selected' : '' ?>>Leader Produksi</option>
        <option value="sheadmtc" <?= $roleVal === 'sheadmtc' ? 'selected' : '' ?>>Section Head MTC</option>
        <option value="admin" <?= $roleVal === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    <div class="mb-4">
      <label class="form-label">Line <span class="text-muted small">(khusus Leader)</span></label>
      <?php $lineVal = old('line', $user['line'] ?? ''); ?>
      <select name="line" class="form-select">
        <option value="">-- Semua Line --</option>
        <?php foreach ($linesGrouped ?? [] as $lokasi => $lines): ?>
          <optgroup label="<?= esc($lokasi) ?>">
            <?php foreach ($lines as $line): ?>
              <option value="<?= esc($line) ?>" <?= $lineVal === $line ? 'selected' : '' ?>><?= esc($line) ?></option>
            <?php endforeach; ?>
          </optgroup>
        <?php endforeach; ?>
      </select>
    </div>


    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= site_url('admin/user') ?>" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>

<?= view('layout/footer') ?>
