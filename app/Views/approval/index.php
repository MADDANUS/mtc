<?= view('layout/header', ['title' => $title]) ?>

<?php
$role = session()->get('role');

// Helper: build query string while keeping existing params
$buildQuery = function(array $override = []) use ($filterJenis, $filterBulan, $filterStatus, $filterLokasi, $filterKategori, $filterMesin, $perPage) {
    $params = [
        'jenis'    => $filterJenis,
        'bulan'    => $filterBulan,
        'status'   => $filterStatus,
        'lokasi'   => $filterLokasi,
        'kategori' => $filterKategori,
        'mesin'    => $filterMesin,
        'per_page' => $perPage ?? 15,
    ];
    foreach ($override as $k => $v) {
        $params[$k] = $v;
    }
    return '?' . http_build_query(array_filter($params, fn($v) => $v !== null && $v !== ''));
};
?>

<div class="page-header d-flex flex-wrap align-items-center gap-3" style="justify-content: space-between;">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <h3 class="fw-bold mb-0"><i class="bi bi-bell-fill me-2 text-primary"></i>Approval</h3>
    <?php if ($totalItems > 0): ?>
      <span class="badge bg-danger" style="font-size: 0.8rem;"><?= $totalItems ?> dokumen menunggu</span>
    <?php endif; ?>
  </div>
</div>

<div class="card border-0 shadow-sm bg-white mb-4">
  <form action="<?= site_url('approval') ?>" method="get" id="filterForm">
    <div class="table-responsive text-nowrap">
      <table class="table align-middle table-hover mb-0">
        <thead class="table-light">
          <!-- Baris Header Kolom -->
          <tr>
            <th style="width:4%;" class="text-center fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">#</th>
            <th style="width:12%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Tipe Dokumen</th>
            <th style="width:12%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Kategori</th>
            <th style="width:16%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">No Mesin</th>
            <th style="width:11%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Lokasi / Line</th>
            <th style="width:12%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Dibuat Oleh</th>
            <th style="width:12%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Tanggal / Bulan</th>
            <th style="width:11%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Status</th>
            <th style="width:10%;" class="fw-bold text-uppercase text-secondary text-center" style="font-size:0.72rem; letter-spacing:0.08em;">Aksi</th>
          </tr>

          <!-- Baris Filter -->
          <tr class="bg-white">
            <th class="p-1"></th>
            <!-- Filter Tipe Dokumen -->
            <th class="p-1" style="min-width:160px;">
              <select name="jenis" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Tipe..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterJenis === 'all') ? 'selected' : '' ?>>Semua Tipe</option>
                <?php if (in_array($role, ['member', 'sheadprd', 'sheadmtc', 'admin'])): ?>
                <option value="Preventive" <?= ($filterJenis === 'Preventive') ? 'selected' : '' ?>>Checklist Report</option>
                <?php endif; ?>
                <?php if (in_array($role, ['member', 'leader', 'sheadprd', 'sheadmtc', 'admin'])): ?>
                <option value="Overhaul" <?= ($filterJenis === 'Overhaul') ? 'selected' : '' ?>>Inspection Report</option>
                <?php endif; ?>
                <?php if (in_array($role, ['member', 'sheadprd', 'sheadmtc', 'admin'])): ?>
                <option value="kontrol" <?= ($filterJenis === 'kontrol') ? 'selected' : '' ?>>Checklist Control</option>
                <?php endif; ?>
              </select>
            </th>
            <!-- Filter Kategori -->
            <th class="p-1" style="min-width:130px;">
              <select name="kategori" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Kategori..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterKategori === 'all') ? 'selected' : '' ?>>Semua Kategori</option>
                <?php foreach ($uniqueKategori as $kat): ?>
                  <option value="<?= esc($kat) ?>" <?= ($filterKategori === $kat) ? 'selected' : '' ?>><?= esc($kat) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
            <!-- Filter Mesin -->
            <th class="p-1" style="min-width:160px;">
              <select name="mesin" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari No Mesin..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterMesin === 'all') ? 'selected' : '' ?>>Semua Mesin</option>
                <?php foreach ($uniqueMesin as $mNo => $mLabel): ?>
                  <option value="<?= esc($mNo) ?>" <?= ($filterMesin === $mNo) ? 'selected' : '' ?>><?= esc($mLabel) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
            <!-- Filter Lokasi -->
            <th class="p-1" style="min-width:130px;">
              <select name="lokasi" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Lokasi/Line..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterLokasi === 'all') ? 'selected' : '' ?>>Semua Lokasi</option>
                <?php foreach ($uniqueLokasi as $loc): ?>
                  <option value="<?= esc($loc) ?>" <?= ($filterLokasi === $loc) ? 'selected' : '' ?>><?= esc($loc) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
            <!-- Filter Dibuat Oleh -->
            <th class="p-1" style="min-width:130px;">
              <select name="pic" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari PIC..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterPic === 'all') ? 'selected' : '' ?>>Semua PIC</option>
                <?php foreach ($uniquePic as $pic): ?>
                <option value="<?= esc($pic) ?>" <?= ($filterPic === $pic) ? 'selected' : '' ?>><?= esc($pic) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
            <!-- Filter Bulan -->
            <th class="p-1" style="min-width:150px;">
              <select name="bulan" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-no-sort="true" data-placeholder="Cari Bulan..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterBulan === 'all') ? 'selected' : '' ?>>Semua Bulan</option>
                <?php foreach ($bulanList as $val => $label): ?>
                  <option value="<?= esc($val) ?>" <?= ($filterBulan === $val) ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
            <!-- Filter Status -->
            <th class="p-1" style="min-width:140px;">
              <select name="status" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Status..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterStatus === 'all') ? 'selected' : '' ?>>Semua Status</option>
                <option value="Pending" <?= ($filterStatus === 'Pending') ? 'selected' : '' ?>>Menunggu Member (Prev)</option>
                <option value="Pending_Overhaul" <?= ($filterStatus === 'Pending_Overhaul') ? 'selected' : '' ?>>Menunggu Leader PRD</option>
                <option value="Approved L1" <?= ($filterStatus === 'Approved L1') ? 'selected' : '' ?>>Menunggu SHead PRD</option>
                <option value="Approved L2" <?= ($filterStatus === 'Approved L2') ? 'selected' : '' ?>>Menunggu SHead MTC</option>
              </select>
            </th>
            <!-- Reset -->
            <th class="p-1 text-center align-middle">
              <a href="<?= site_url('approval') ?>" class="btn btn-sm btn-danger fw-bold px-3" title="Reset Filter" style="font-size:0.75rem;">
                <i class="bi bi-arrow-counterclockwise fw-bold"></i> Reset
              </a>
            </th>
          </tr>
        </thead>

        <tbody>
          <?php if (empty($docs)): ?>
            <tr>
              <td colspan="9" class="text-center py-5 text-muted">
                <i class="bi bi-check2-all mb-2 text-success" style="font-size: 2.5rem; display: block;"></i>
                <span class="fw-semibold">Semua dokumen sudah disetujui!</span><br>
                <small>Tidak ada dokumen yang menunggu persetujuan Anda saat ini.</small>
              </td>
            </tr>
          <?php else: ?>
            <?php $no = (($currentPage - 1) * $perPage) + 1; ?>
            <?php foreach ($docs as $doc): ?>
              <?php
                $isKontrol  = ($doc['doc_source'] === 'kontrol');
                $isOverhaul = (!$isKontrol && strtolower($doc['jenis_check']) === 'overhaul');
                $isPreventive = (!$isKontrol && !$isOverhaul);

                // Label tipe
                if ($isKontrol) {
                    $tipeBadge = '<span class="badge" style="background:#6366f1;">Checklist Control</span>';
                } elseif ($isOverhaul) {
                    $tipeBadge = '<span class="badge bg-secondary">Inspection Report</span>';
                } else {
                    $tipeBadge = '<span class="badge bg-primary">Checklist Report</span>';
                }

                // Keterangan
                if ($isKontrol) {
                    $keterangan = esc($doc['kategori']); // Tetap untuk JS
                    $tdKategori = esc($doc['kategori']);
                    $tdMesin    = '-';
                    $lokasiLine = esc($doc['lokasi'] ?? '-') . ($doc['line'] ? ' / ' . esc($doc['line']) : '');
                    $dibuatOleh = '-';
                    $blnArr = explode('-', $doc['doc_date']);
                    $namaBulan = ['01'=>'Juli', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'];
                    // Fix bug in array for January, it should be 'Januari' not 'Juli'
                    $namaBulan['01'] = 'Januari';
                    if (count($blnArr) >= 2 && isset($namaBulan[$blnArr[1]])) {
                        $tanggal = esc($namaBulan[$blnArr[1]] . ' ' . $blnArr[0]);
                    } else {
                        $tanggal = esc($doc['doc_date']);
                    }
                    $linkDetail = site_url('kontrol')
                        . '?lokasi='   . urlencode($doc['lokasi']    ?? '')
                        . '&kategori=' . urlencode($doc['kategori']  ?? '')
                        . '&bulan='    . urlencode(substr($doc['doc_date'] ?? '', 0, 7))
                        . (!empty($doc['line']) ? '&line=' . urlencode($doc['line']) : '')
                        . '&from=approval'
                        . (!empty($_SERVER['QUERY_STRING']) ? '&qs_approval=' . urlencode($_SERVER['QUERY_STRING']) : '');
                } else {
                    $keterangan = esc($doc['no_mesin'] ?? '') . ' — ' . esc($doc['type_mesin'] ?? '') . ' (' . esc($doc['kategori'] ?? '') . ')'; // Tetap untuk JS
                    $tdKategori = esc($doc['kategori'] ?? '-');
                    $tdMesin    = esc($doc['no_mesin'] ?? '');
                    $lokasiLine = esc($doc['lokasi_check'] ?? '-') . ($doc['line'] ? ' / ' . esc($doc['line']) : '');
                    $rawPic = $doc['nama_pic'] ?: $doc['nama_staff'];
                    $parts = explode(' - ', $rawPic ?? '');
                    $dibuatOleh = esc(end($parts));
                    $tanggal = !empty($doc['doc_date']) ? esc(format_tanggal_indo($doc['doc_date'], true, true)) : '-';
                    $linkDetail = site_url('riwayat/' . $doc['doc_id']) . '?from=approval'
                        . (!empty($_SERVER['QUERY_STRING']) ? '&qs_approval=' . urlencode($_SERVER['QUERY_STRING']) : '');
                }

                // Status badge
                $status = $doc['status'] ?? 'Pending';
                if ($status === 'Approved' || $status === 'Approved Final' || $status === 'Final') {
                    $statusBadge = '<span class="badge bg-success">Selesai (Final)</span>';
                } elseif ($doc['status'] === 'Approved L1') {
                    $statusBadge = '<span class="badge bg-info text-dark">Menunggu SHead PRD</span>';
                } elseif ($doc['status'] === 'Approved L2') {
                    $statusBadge = '<span class="badge bg-primary">Menunggu SHead MTC</span>';
                } elseif ($status === 'Belum Selesai') {
                    $persen = $doc['persen'] ?? 0;
                    if ($persen == 100) {
                        $statusBadge = '<span class="badge bg-warning text-dark">Menunggu Member (100%)</span>';
                    } else {
                        $statusBadge = '<span class="badge bg-secondary">Belum Selesai (' . $persen . '%)</span>';
                    }
                } else {
                    if (($doc['jenis_check'] ?? '') === 'Overhaul') {
                        $statusBadge = '<span class="badge bg-warning text-dark">Menunggu Leader PRD</span>';
                    } else {
                        $statusBadge = '<span class="badge bg-warning text-dark">Menunggu Member</span>';
                    }
                }
              ?>
              <tr>
                <td class="fw-semibold text-muted text-center"><?= $no++ ?></td>
                <td><?= $tipeBadge ?></td>
                <td style="font-size:0.85rem; white-space:normal; font-weight:600;"><?= $tdKategori ?></td>
                <td style="font-size:0.85rem; white-space:normal; color:var(--text-secondary);"><?= $tdMesin ?></td>
                <td style="font-size:0.82rem; color:var(--text-secondary);"><?= $lokasiLine ?></td>
                <td style="font-size:0.85rem;"><?= $dibuatOleh ?></td>
                <td style="font-size:0.8rem; color:var(--text-secondary);"><?= $tanggal ?></td>
                <td><?= $statusBadge ?></td>
                <td class="text-center">
                  <div class="d-flex gap-1 justify-content-center flex-wrap">
                    <?php if ($status === 'Belum Selesai'): ?>
                      <a href="<?= $linkDetail ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:0.8rem;">
                        <i class="bi bi-eye me-1"></i>Review
                      </a>
                    <?php else: ?>
                      <a href="<?= $linkDetail ?>" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:0.8rem;">
                        <i class="bi bi-eye me-1"></i>Review
                      </a>
                    <?php endif; ?>

                                        <?php if (session()->get('role') === 'admin' && ($doc['doc_source'] ?? '') === 'transaksi'): ?>
                      <a href="<?= site_url('riwayat/edit/' . $doc['doc_id']) ?>?from=approval" class="btn btn-sm btn-outline-warning py-1 px-2" style="font-size:0.8rem;" title="Edit">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <button type="button"
                        class="btn btn-sm btn-outline-danger py-1 px-2"
                        style="font-size:0.8rem;"
                        title="Hapus"
                        onclick="konfirmasiHapus(<?= $doc['doc_id'] ?>, '<?= esc($keterangan, 'js') ?>')">
                        <i class="bi bi-trash"></i>
                      </button>
                    <?php elseif (session()->get('role') === 'admin' && ($doc['doc_source'] ?? '') === 'kontrol'): ?>
                      <button type="button"
                        class="btn btn-sm btn-outline-danger py-1 px-2"
                        style="font-size:0.8rem;"
                        title="Hapus Approval"
                        onclick="konfirmasiHapusKontrol('<?= esc($doc['lokasi'], 'js') ?>', '<?= esc($doc['line'], 'js') ?>', '<?= esc($doc['kategori'], 'js') ?>', '<?= esc(substr($doc['doc_date'] ?? '', 0, 7), 'js') ?>', '<?= esc($keterangan, 'js') ?>')">
                        <i class="bi bi-trash"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </form><!-- end filterForm -->

  <!-- Pagination -->
  <!-- Pagination -->
  <?php if ($totalItems > 0): ?>
  <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center">
          <span class="text-muted small me-2">Tampilkan:</span>
          <select name="per_page" form="filterForm" class="form-select form-select-sm text-center" style="width:60px;" onchange="this.form.submit()">
            <option value="15" <?= $perPage == 15 ? 'selected' : '' ?>>15</option>
            <option value="30" <?= $perPage == 30 ? 'selected' : '' ?>>30</option>
            <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
          </select>
          <span class="text-muted small ms-2">baris</span>
        </div>
        <span class="text-muted small">
          Menampilkan <?= (($currentPage-1)*$perPage)+1 ?>–<?= min($currentPage*$perPage, $totalItems) ?> dari <?= $totalItems ?> dokumen
        </span>
    </div>
    <?php if ($totalPages > 1): ?>
    <nav>
      <ul class="pagination pagination-sm mb-0 gap-1">
        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
          <a class="page-link rounded-2" href="<?= site_url('approval') . $buildQuery(['page' => $currentPage - 1]) ?>">
            <i class="bi bi-chevron-left" style="font-size:0.7rem;"></i>
          </a>
        </li>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
            <a class="page-link rounded-2" href="<?= site_url('approval') . $buildQuery(['page' => $p]) ?>"><?= $p ?></a>
          </li>
        <?php endfor; ?>
        <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
          <a class="page-link rounded-2" href="<?= site_url('approval') . $buildQuery(['page' => $currentPage + 1]) ?>">
            <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i>
          </a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

  <!-- Modal Konfirmasi Hapus -->
  <div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-1">Anda akan menghapus laporan:</p>
          <p class="fw-bold text-danger" id="namaHapus"></p>
          <p class="text-muted small">Tindakan ini tidak dapat dibatalkan. Semua data checklist dan laporan abnormal terkait akan ikut terhapus.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <form id="formHapus" method="POST" class="d-inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger btn-sm">
              <i class="bi bi-trash me-1"></i>Ya, Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
    <!-- Modal Konfirmasi Hapus Kontrol -->
  <div class="modal fade" id="modalHapusKontrol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Approval</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-1">Anda akan menghapus persetujuan untuk:</p>
          <p class="fw-bold text-danger" id="namaHapusKontrol"></p>
          <p class="text-muted small">Data checklist tidak akan hilang, status akan kembali ke "Belum Selesai".</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <form id="formHapusKontrol" action="<?= site_url('kontrol/delete-approval') ?>" method="POST" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="lokasi" id="del_lokasi">
            <input type="hidden" name="line" id="del_line">
            <input type="hidden" name="kategori" id="del_kategori">
            <input type="hidden" name="bulan_tahun" id="del_bulan">
            <button type="submit" class="btn btn-danger btn-sm">
              <i class="bi bi-trash me-1"></i>Ya, Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
  function konfirmasiHapus(id, nama) {
    document.getElementById('namaHapus').textContent = nama;
    document.getElementById('formHapus').action = '<?= site_url('riwayat/delete/') ?>' + id;
    new bootstrap.Modal(document.getElementById('modalHapus')).show();
  }
  
  function konfirmasiHapusKontrol(lokasi, line, kategori, bulan, nama) {
    document.getElementById('namaHapusKontrol').textContent = 'Checklist Control: ' + nama;
    document.getElementById('del_lokasi').value = lokasi;
    document.getElementById('del_line').value = line;
    document.getElementById('del_kategori').value = kategori;
    document.getElementById('del_bulan').value = bulan;
    new bootstrap.Modal(document.getElementById('modalHapusKontrol')).show();
  }
  </script>

<?= view('layout/footer') ?>
