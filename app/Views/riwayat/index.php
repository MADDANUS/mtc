<?= view('layout/header', ['title' => $title]) ?>

<?php
// Helper functions for column sorting
$getSortUrl = function(string $column) use ($selectedFilters, $departemenSlug) {
    $params = $selectedFilters;
    unset($params['departemen']); // Remove departemen from query parameters as it's part of the route URL

    $currentSort = $selectedFilters['sort_by'] ?? 'id_transaksi';
    $currentOrder = $selectedFilters['order'] ?? 'desc';

    if ($currentSort === $column) {
        $params['order'] = ($currentOrder === 'asc') ? 'desc' : 'asc';
    } else {
        $params['sort_by'] = $column;
        $params['order'] = 'asc';
    }

    return site_url("riwayat/departemen/{$departemenSlug}") . '?' . http_build_query($params);
};

$getSortIcon = function(string $column) use ($selectedFilters) {
    $currentSort = $selectedFilters['sort_by'] ?? 'id_transaksi';
    $currentOrder = strtolower($selectedFilters['order'] ?? 'desc');

    if ($currentSort !== $column) {
        return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:0.75rem;"></i>';
    }

    return ($currentOrder === 'asc')
        ? '<i class="bi bi-sort-up text-primary ms-1" style="font-size:0.85rem;"></i>'
        : '<i class="bi bi-sort-down text-primary ms-1" style="font-size:0.85rem;"></i>';
};
?>

<div class="page-header d-flex flex-wrap align-items-center gap-3" style="justify-content: space-between;">
  <div class="d-flex flex-wrap align-items-center">
    <h3 class="fw-bold mb-0 me-2"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat <?= esc($jenisLabel ?? 'Pengecekan') ?></h3>
  </div>
  <div class="d-flex gap-2">
    <?php 
      // Buat URL download dengan filter saat ini
      $pdfParams = $selectedFilters ?? [];
      // Hapus filter departemen dari parameter GET karena sudah ada di URL segment
      unset($pdfParams['departemen']);
      $downloadUrl = site_url("riwayat/download-pdf-all/{$departemenSlug}") . '?' . http_build_query($pdfParams);
      $excelUrl = site_url("riwayat/export-excel/{$departemenSlug}") . '?' . http_build_query($pdfParams);
    ?>
    <?php if (!in_array(session()->get('role'), ['leader', 'sheadprd', 'sheadmtc'])): ?>
    <a href="<?= $excelUrl ?>" class="btn btn-sm btn-outline-success shadow-sm" target="_blank">
      <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
    </a>
    <a href="<?= $downloadUrl ?>" class="btn btn-sm btn-outline-danger shadow-sm" target="_blank">
      <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
    </a>
    <?php endif; ?>
    <?php if (in_array(session()->get('role'), ['staff', 'admin'], true)): ?>
      <a href="<?= site_url('checklist') ?>" class="btn btn-sm btn-primary shadow-sm">
        <i class="bi bi-plus-lg"></i> Buat Baru
      </a>
    <?php endif; ?>
  </div>
</div>


<!-- KARTU TABEL DAFTAR RIWAYAT -->
<form action="<?= site_url("riwayat/departemen/{$departemenSlug}") ?>" method="get" id="filterForm">
  <!-- Keep sorting parameters when changing filters -->
  <input type="hidden" name="sort_by" value="<?= esc($selectedFilters['sort_by'] ?? 'id_transaksi') ?>">
  <input type="hidden" name="order" value="<?= esc($selectedFilters['order'] ?? 'desc') ?>">
  <input type="hidden" name="jenis_check" value="<?= esc($selectedFilters['jenis_check'] ?? '') ?>">

<div class="card border-0 shadow-sm bg-white mb-4">
    <div class="table-responsive text-nowrap">
      <table class="table align-middle table-hover paginated-table" data-ajax-pagination="true" data-total-items="<?= esc($totalItems ?? 0) ?>" data-per-page="<?= esc($perPage ?? 15) ?>" data-current-page="<?= esc($_GET['page_riwayat'] ?? 1) ?>">
                <thead class="table-light">
                    <!-- Baris Kolom dan Sorting -->
          <tr>
            <th style="width: 5%;" class="text-center align-middle">
              <a href="<?= $getSortUrl('id_transaksi') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                NO <?= $getSortIcon('id_transaksi') ?>
              </a>
            </th>
            <th style="width: 12%;" class="align-middle">
              <a href="<?= $getSortUrl('nama_staff') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                PIC <?= $getSortIcon('nama_staff') ?>
              </a>
            </th>
            <th style="width: 8%;" class="align-middle">
              <a href="#" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em; cursor: default;" onclick="return false;">
                PLAN
              </a>
            </th>
            <th style="width: 8%;" class="align-middle">
              <a href="<?= $getSortUrl('departemen_check') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                DEPARTEMEN <?= $getSortIcon('departemen_check') ?>
              </a>
            </th>
            <th style="width: 13%;" class="align-middle">
              <a href="<?= $getSortUrl('line') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                LINE <?= $getSortIcon('line') ?>
              </a>
            </th>
            <th style="width: 15%;" class="align-middle">
              <a href="<?= $getSortUrl('no_mesin') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                MESIN <?= $getSortIcon('no_mesin') ?>
              </a>
            </th>
            <th style="width: 15%;" class="align-middle">
                <a href="<?= $getSortUrl('kategori') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                  KATEGORI <?= $getSortIcon('kategori') ?>
                </a>
              </th>
              <th style="width: 10%;" class="align-middle text-center">
                <a href="#" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em; cursor: default;" onclick="return false;">
                  KONDISI
                </a>
              </th>
              <th style="width: 15%;" class="align-middle">
                <a href="<?= $getSortUrl('waktu_mulai') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                TARGET BULAN <?= $getSortIcon('waktu_mulai') ?>
              </a>
            </th>
            <th style="width: 12%;" class="align-middle">
              <a href="<?= $getSortUrl('status') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                STATUS <?= $getSortIcon('status') ?>
              </a>
            </th>
            <th style="width: 10%;" class="fw-bold text-uppercase text-secondary text-center align-middle" style="font-size: 0.72rem; letter-spacing: 0.08em;">Aksi</th>
          </tr>
          <!-- NEW FILTER ROW -->
          <tr class="bg-white">
            <th class="p-1"></th>
            <th class="p-1" style="min-width: 130px;">
              <select name="pic" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari PIC..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                <option value=""></option>
                <option value="all">Semua PIC</option>
                <?php if (!empty($availablePics)) foreach ($availablePics as $p): ?>
                  <option value="<?= esc($p) ?>" <?= isset($selectedFilters['pic']) && $selectedFilters['pic'] === $p ? 'selected' : '' ?>><?= esc($p) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
            <th class="p-1" style="min-width: 100px;">
              <select name="plan" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Plan..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                <option value=""></option>
                <option value="all">Semua Plan</option>
                <?php if (!empty($availablePlans)) foreach ($availablePlans as $pl): ?>
                  <option value="<?= esc($pl) ?>" <?= isset($selectedFilters['plan']) && $selectedFilters['plan'] === $pl ? 'selected' : '' ?>><?= esc($pl) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
            <th class="p-1" style="min-width: 130px;">
              <select class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Departemen..." onchange="window.location.href='<?= site_url('riwayat/departemen/') ?>' + (this.value === 'all' ? 'semua' : this.value) + '?jenis_check=<?= urlencode($selectedFilters['jenis_check'] ?? '') ?>'" style="font-size: 0.75rem;">
                <option value=""></option>
                <option value="all" <?= $departemenSlug === 'semua' ? 'selected' : '' ?>>Semua Departemen</option>
                <option value="mfg1" <?= $departemenSlug === 'mfg1' ? 'selected' : '' ?>>MFG 1</option>
                <option value="mfg2" <?= $departemenSlug === 'mfg2' ? 'selected' : '' ?>>MFG 2</option>
              </select>
            </th>
            <th class="p-1" style="min-width: 110px;">
              <?php if (!empty($userLine)): ?>
                <input type="text" class="form-control form-control-sm border-1 bg-light fw-bold text-uppercase px-2" value="<?= esc($userLine) ?>" readonly disabled>
                <input type="hidden" name="line" value="<?= esc($userLine) ?>">
              <?php else: ?>
                <select name="line" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Line..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                  <option value=""></option>
                  <option value="all">Semua Line</option>
                  <?php foreach ($availableLines as $l): ?>
                    <option value="<?= esc($l) ?>" <?= isset($selectedFilters['line']) && $selectedFilters['line'] === $l ? 'selected' : '' ?>><?= esc($l) ?></option>
                  <?php endforeach; ?>
                </select>
              <?php endif; ?>
            </th>
            <th class="p-1" style="min-width: 130px;">
              <select name="id_mesin" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Mesin..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                <option value=""></option>
                <option value="all">Semua Mesin</option>
                <?php foreach ($daftarMesin as $m): ?>
                  <option value="<?= esc($m['id_mesin']) ?>" <?= isset($selectedFilters['id_mesin']) && (int)$selectedFilters['id_mesin'] === (int)$m['id_mesin'] ? 'selected' : '' ?>><?= esc($m['no_mesin']) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
            <th class="p-1" style="min-width: 140px;">
                <select name="kategori" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Kategori..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                  <option value=""></option>
                  <option value="all">Semua Kategori</option>
                  <?php foreach ($categories as $slug => $name): ?>
                    <option value="<?= esc($name) ?>" <?= isset($selectedFilters['kategori']) && $selectedFilters['kategori'] === $name ? 'selected' : '' ?>><?= esc($name) ?></option>
                  <?php endforeach; ?>
                </select>
              </th>
              <th class="p-1">
                 <!-- No filter for Kondisi for now -->
              </th>
            <th class="p-1" style="min-width: 140px;">
              <select name="bulan" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-no-sort="true" data-placeholder="Cari Target Bulan..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                <option value=""></option>
                <option value="all">Semua Bulan</option>
                <?php if (!empty($bulanList)) foreach ($bulanList as $val => $label): ?>
                  <option value="<?= esc($val) ?>" <?= isset($selectedFilters['bulan']) && $selectedFilters['bulan'] === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
            <th class="p-1" style="min-width: 120px;">
              <select name="status" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Status..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                <option value=""></option>
                <option value="all">Semua Status</option>
                <option value="Pending" <?= isset($selectedFilters['status']) && $selectedFilters['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <?php $isPreventive = in_array(strtolower($selectedFilters['jenis_check'] ?? ''), ['preventive', 'checklist report', 'checklist-report']); ?>
                <?php if (!$isPreventive): ?>
                  <option value="Approved L1" <?= isset($selectedFilters['status']) && $selectedFilters['status'] === 'Approved L1' ? 'selected' : '' ?>>Aprv L1</option>
                  <option value="Approved L2" <?= isset($selectedFilters['status']) && $selectedFilters['status'] === 'Approved L2' ? 'selected' : '' ?>>Aprv L2</option>
                <?php endif; ?>
                <option value="Approved" <?= isset($selectedFilters['status']) && $selectedFilters['status'] === 'Approved' ? 'selected' : '' ?>>Final</option>
              </select>
            </th>
            <th class="p-1 text-center align-middle">
                <?php 
                  $resetUrl = site_url("riwayat/departemen/{$departemenSlug}");
                  if (!empty($selectedFilters['jenis_check'])) {
                      $resetUrl .= '?jenis_check=' . urlencode($selectedFilters['jenis_check']);
                  }
                ?>
                  <a href="<?= $resetUrl ?>" class="btn btn-sm btn-danger fw-bold px-3" title="Reset Filter" style="font-size: 0.75rem;">
                      <i class="bi bi-arrow-counterclockwise fw-bold"></i> Reset
                  </a>
            </th>
          </tr>
        </thead>
        <tbody>
          <?= view('riwayat/_rows', [
              'riwayat' => $riwayat,
              'startNo' => $startNo ?? 1,
              'departemenSlug' => $departemenSlug
          ]) ?>
        </tbody>
      </table>
    </div>
</div>
</form>

<?php if (session()->get('role') === 'admin'): ?>
<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
        <h6 class="modal-title fw-bold text-danger"><i class="bi bi-trash3 me-1.5"></i>Hapus Riwayat Pengecekan</h6>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
      </div>
      <form id="deleteForm" method="post" action="">
        <?= csrf_field() ?>
        <div class="modal-body px-4 pt-3 pb-2">
          <p class="text-muted mb-0" style="font-size:0.88rem;">Apakah Anda yakin ingin menghapus riwayat pengecekan ini? Data detail pengecekan dan Abnormal Report yang terkait juga akan dihapus secara permanen.</p>
        </div>
        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger btn-sm px-4 rounded-3">Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function confirmDelete(id) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.getElementById('deleteForm').action = '<?= site_url("riwayat/delete/") ?>' + id;
    modal.show();
}
</script>
<?php endif; ?>

<?= view('layout/footer') ?>
