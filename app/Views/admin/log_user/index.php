<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1>Log Hapus User</h1>
    <p class="text-muted mb-0">Riwayat penghapusan akun user beserta alasannya.</p>
  </div>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="logUserTable">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Waktu Dihapus</th>
            <th>Nama User</th>
            <th>Username</th>
            <th>Role</th>
            <th>Dihapus Oleh</th>
            <th>Alasan Hapus</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($logs)): ?>
            <tr>
              <td colspan="7" class="text-center py-4">Belum ada riwayat penghapusan user.</td>
            </tr>
          <?php else: ?>
            <?php $i = 1; foreach($logs as $log): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td>
                  <?= date('d M Y, H:i', strtotime($log['deleted_at'])) ?>
                </td>
                <td>
                  <span class="fw-bold"><?= esc($log['nama']) ?></span>
                </td>
                <td><?= esc($log['username']) ?></td>
                <td>
                  <span class="badge bg-primary"><?= strtoupper(esc($log['role'])) ?></span>
                </td>
                <td>
                  <span class="badge bg-secondary"><?= esc($log['deleted_by_name']) ?></span>
                </td>
                <td>
                  <div class="text-wrap" style="max-width: 250px; font-size:0.85rem;">
                    <?= nl2br(esc($log['alasan'])) ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
