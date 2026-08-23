<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1>Log Hapus Mesin</h1>
    <p class="text-muted mb-0">Riwayat penghapusan data mesin beserta alasannya.</p>
  </div>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="logMesinTable">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Waktu Dihapus</th>
            <th>No Mesin</th>
            <th>Tipe / Serial</th>
            <th>Dihapus Oleh</th>
            <th>Alasan Hapus</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($logs)): ?>
            <tr>
              <td colspan="6" class="text-center py-4">Belum ada riwayat penghapusan mesin.</td>
            </tr>
          <?php else: ?>
            <?php $i = 1; foreach($logs as $log): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td>
                  <?= date('d M Y, H:i', strtotime($log['deleted_at'])) ?>
                </td>
                <td>
                  <span class="fw-bold"><?= esc($log['no_mesin']) ?></span><br>
                  <span class="text-muted small">Plant: <?= esc($log['plant']) ?></span>
                </td>
                <td>
                  <?= esc($log['type_mesin']) ?><br>
                  <span class="text-muted small">SN: <?= esc($log['serial_nomor']) ?></span>
                </td>
                <td>
                  <span class="badge bg-secondary"><?= esc($log['deleted_by_name']) ?></span>
                </td>
                <td>
                  <div class="text-wrap" style="max-width: 300px; font-size:0.85rem;">
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
