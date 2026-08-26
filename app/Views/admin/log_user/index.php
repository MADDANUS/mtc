<?= view('layout/header', ['title' => 'Log Riwayat User']) ?>

<div class="page-header">
  <div>
    <h1>Log Riwayat User</h1>
    <p class="text-muted mb-0">Riwayat pencatatan (tambah, ubah, dan hapus) data user.</p>
  </div>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="logUserTable">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Waktu Kejadian</th>
            <th>Nama User</th>
            <th>Aksi</th>
            <th>Keterangan</th>
            <th>Dilakukan Oleh</th>
            <th>Detail</th>
          </tr>
          <tr class="bg-white">
            <th class="py-2"></th>
            <th class="py-2">
                <select name="filter_bulan" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Bulan..." onchange="document.getElementById('filterForm').submit();">
                    <option value="all">Semua Bulan</option>
                    <?php foreach ($bulanList as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $val === $filterBulan ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </th>
            <th class="py-2">
                <select name="filter_target_user" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Target User..." onchange="document.getElementById('filterForm').submit();">
                    <option value="">Semua User</option>
                    <?php foreach ($availableTargetUsers as $user): ?>
                        <option value="<?= esc($user) ?>" <?= $user === $filterTargetUser ? 'selected' : '' ?>><?= esc($user) ?></option>
                    <?php endforeach; ?>
                </select>
            </th>
            <th class="py-2">
                <select name="filter_aksi" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Aksi..." onchange="document.getElementById('filterForm').submit();">
                    <option value="">Semua Aksi</option>
                    <?php foreach ($availableAksi as $aksi): ?>
                        <option value="<?= esc($aksi) ?>" <?= $aksi === $filterAksi ? 'selected' : '' ?>><?= esc($aksi) ?></option>
                    <?php endforeach; ?>
                </select>
            </th>
            <th class="py-2"></th>
            <th class="py-2">
                <select name="filter_user" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Admin..." onchange="document.getElementById('filterForm').submit();">
                    <option value="">Semua Admin</option>
                    <?php foreach ($availableUsers as $user): ?>
                        <option value="<?= esc($user) ?>" <?= $user === $filterUser ? 'selected' : '' ?>><?= esc($user) ?></option>
                    <?php endforeach; ?>
                </select>
            </th>
            <th class="py-2 text-center align-middle">
                <a href="<?= site_url('admin/log-user') ?>" class="btn btn-sm btn-danger fw-bold px-3" title="Reset Filter" style="font-size: 0.75rem;">
                    <i class="bi bi-arrow-counterclockwise fw-bold"></i> Reset
                </a>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($logs)): ?>
            <tr>
              <td colspan="7" class="text-center py-4">Belum ada riwayat aktivitas user.</td>
            </tr>
          <?php else: ?>
            <?php $i = 1; foreach($logs as $log): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td>
                  <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
                </td>
                <td>
                  <span class="fw-bold"><?= esc($log['nama_user']) ?></span>
                </td>
                <td>
                  <?php if ($log['aksi'] === 'CREATE'): ?>
                    <span class="badge bg-success">CREATE</span>
                  <?php elseif ($log['aksi'] === 'UPDATE'): ?>
                    <span class="badge bg-warning text-dark">UPDATE</span>
                  <?php elseif ($log['aksi'] === 'DELETE'): ?>
                    <span class="badge bg-danger">DELETE</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="text-wrap" style="max-width: 250px; font-size:0.85rem;">
                    <?= nl2br(esc($log['keterangan'])) ?>
                  </div>
                </td>
                <td>
                  <span class="badge bg-secondary"><?= esc($log['nama_admin'] ?? 'System') ?></span>
                </td>
                <td>
                  <?php
                    $detail = json_decode($log['detail'], true);
                    if ($log['aksi'] === 'UPDATE' && isset($detail['perubahan'])):
                  ?>
                    <button type="button" class="btn btn-sm btn-outline-info" 
                            onclick="lihatDataSnapshot(<?= htmlspecialchars(json_encode($detail['perubahan']), ENT_QUOTES, 'UTF-8') ?>, 'UPDATE')">
                      <i class="bi bi-eye"></i> Lihat Data
                    </button>
                  <?php elseif ($log['aksi'] === 'DELETE' && isset($detail['data_sebelum'])): ?>
                    <button type="button" class="btn btn-sm btn-outline-info" 
                            onclick="lihatDataSnapshot(<?= htmlspecialchars(json_encode($detail['data_sebelum']), ENT_QUOTES, 'UTF-8') ?>, 'DELETE')">
                      <i class="bi bi-eye"></i> Lihat Data
                    </button>
                  <?php elseif ($log['aksi'] === 'CREATE' && isset($detail['data_baru'])): ?>
                    <span class="text-muted" style="font-size:0.85rem;">Data awal dibuat</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <form id="filterForm" action="<?= site_url('admin/log-user') ?>" method="get"></form>
    
    <!-- Pagination -->
    <?php if (isset($totalItems) && $totalItems > 0): ?>
    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top mt-2 flex-wrap gap-2">
        <div class="d-flex align-items-center">
            <span class="text-muted small me-2">Tampilkan:</span>
            <select name="per_page" form="filterForm" class="form-select form-select-sm text-center" style="width:60px;" onchange="this.form.submit()">
                <option value="15" <?= $perPage == 15 ? 'selected' : '' ?>>15</option>
                <option value="30" <?= $perPage == 30 ? 'selected' : '' ?>>30</option>
                <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
            </select>
            <span class="text-muted small ms-2">baris</span>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">
                Menampilkan <?= (($currentPage-1)*$perPage)+1 ?> - <?= min($currentPage*$perPage, $totalItems) ?> dari <?= $totalItems ?> data
            </span>
            <nav>
            <ul class="pagination pagination-sm mb-0 gap-1">
                <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link rounded-2" <?= $currentPage <= 1 ? 'tabindex="-1" aria-disabled="true"' : 'href="'.site_url('admin/log-user') . $buildQuery(['page' => $currentPage - 1]).'"' ?>>
                    <i class="bi bi-chevron-left" style="font-size:0.7rem;"></i>
                </a>
                </li>
                <?php 
                $startPage = max(1, $currentPage - 2);
                $endPage = min(max(1, $totalPages), $currentPage + 2);
                for ($p = $startPage; $p <= $endPage; $p++): 
                ?>
                <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                    <a class="page-link rounded-2" href="<?= site_url('admin/log-user') . $buildQuery(['page' => $p]) ?>"><?= $p ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?= $currentPage >= max(1, $totalPages) ? 'disabled' : '' ?>">
                <a class="page-link rounded-2" <?= $currentPage >= max(1, $totalPages) ? 'tabindex="-1" aria-disabled="true"' : 'href="'.site_url('admin/log-user') . $buildQuery(['page' => $currentPage + 1]).'"' ?>>
                    <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i>
                </a>
                </li>
            </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Modal Detail Snapshot -->
<div class="modal fade" id="snapshotModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Snapshot Data Terhapus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-sm table-bordered" id="snapshotTable">
          <tbody>
            <!-- Diisi via JS -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
function lihatDataSnapshot(data, tipe = 'DELETE') {
    const title = document.querySelector('#snapshotModal .modal-title');
    const table = document.querySelector('#snapshotTable');
    
    if (tipe === 'UPDATE') {
        title.innerHTML = 'Detail Perubahan Data';
        table.innerHTML = `
            <thead>
                <tr class="table-light">
                    <th>Kolom</th>
                    <th>Data Lama</th>
                    <th>Data Baru</th>
                </tr>
            </thead>
            <tbody></tbody>
        `;
        const tbody = table.querySelector('tbody');
        for (const [key, value] of Object.entries(data)) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <th class="w-25 bg-light">${key.toUpperCase()}</th>
                <td><span class="text-muted text-decoration-line-through">${value.lama}</span></td>
                <td><span class="text-success fw-bold">${value.baru}</span></td>
            `;
            tbody.appendChild(tr);
        }
    } else {
        title.innerHTML = 'Snapshot Data Terhapus';
        table.innerHTML = `<tbody></tbody>`;
        const tbody = table.querySelector('tbody');
        for (const [key, value] of Object.entries(data)) {
            if (value !== null && value !== '' && key !== 'password') {
                const tr = document.createElement('tr');
                tr.innerHTML = `<th class="w-25 bg-light">${key.toUpperCase()}</th><td>${value}</td>`;
                tbody.appendChild(tr);
            }
        }
    }
    
    new bootstrap.Modal(document.getElementById('snapshotModal')).show();
}
</script>

<?= view('layout/footer') ?>
