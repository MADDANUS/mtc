<?= view('layout/header', ['title' => $title]) ?>
<?php
function formatDurasi($detik) {
    if ($detik === null) return '-';
    $jam = floor($detik / 3600);
    $menit = floor(($detik % 3600) / 60);
    $det = $detik % 60;
    if ($jam > 0) {
        return sprintf('%02d:%02d:%02d', $jam, $menit, $det);
    }
    return sprintf('%02d:%02d', $menit, $det);
}

function formatDurasiText($detik) {
    if ($detik === null) return '-';
    $jam = floor($detik / 3600);
    $menit = floor(($detik % 3600) / 60);
    $det = $detik % 60;
    
    $parts = [];
    if ($jam > 0) $parts[] = $jam . ' jam';
    if ($menit > 0 || $jam > 0) $parts[] = $menit . ' menit';
    $parts[] = $det . ' detik';
    return implode(' ', $parts);
}
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="fw-bold mb-0">Laporan Durasi Pengecekan (Efisiensi)</h3>
  <?php
      $pdfUrl = site_url('laporan/durasi-pdf');
      if (!empty($selectedFilters)) {
          $pdfUrl .= '?' . http_build_query($selectedFilters);
      }
  ?>
  <?php if (!in_array(session()->get('role'), ['leader', 'sheadprd', 'sheadmtc'])): ?>
  <a href="<?= $pdfUrl ?>" target="_blank" class="btn btn-sm btn-danger fw-semibold shadow-sm" title="Download PDF">
      <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
  </a>
  <?php endif; ?>
</div>

<div class="card-stat p-3 mb-3" style="max-width:320px;">
  <div class="text-muted small">Rata-rata Durasi Semua Transaksi</div>
  <div class="value"><?= gmdate('i \m\e\n\i\t s \d\e\t\i\k', $rataDetik) ?></div>
</div>

<div class="card-stat p-3">
  <?php if (empty($laporan) && empty($selectedFilters['lokasi']) && empty($selectedFilters['line']) && empty($selectedFilters['id_mesin']) && empty($selectedFilters['jenis_check']) && empty($selectedFilters['pic']) && empty($selectedFilters['bulan'])): ?>
    <p class="text-muted mb-0">Belum ada data transaksi.</p>
  <?php else: ?>
    <div class="table-responsive text-nowrap">
      <form id="filterForm" action="<?= site_url('laporan/durasi') ?>" method="GET">
        <table class="table table-sm align-middle table-hover paginated-table">
          <thead>
            <tr>
              <th class="text-center">NO</th>
              <th>PIC</th>
              <th>Mesin</th>
              <th>Lokasi / Line</th>
              <th>Jenis Pengecekan</th>
              <th>Waktu Mulai</th>
              <th>Waktu Selesai</th>
              <th>Durasi</th>
              <th></th>
            </tr>
            <!-- NEW FILTER ROW -->
            <tr class="bg-white">
              <th class="p-1"></th>
              <th class="p-1">
                <select name="pic" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari PIC..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                  <option value=""></option>
                  <option value="all">Semua PIC</option>
                  <?php if (!empty($availablePics)) foreach ($availablePics as $p): ?>
                    <option value="<?= esc($p) ?>" <?= isset($selectedFilters['pic']) && $selectedFilters['pic'] === $p ? 'selected' : '' ?>><?= esc($p) ?></option>
                  <?php endforeach; ?>
                </select>
              </th>
              <th class="p-1">
                <select name="id_mesin" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Mesin..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                  <option value=""></option>
                  <option value="all">Semua Mesin</option>
                  <?php if (!empty($daftarMesin)) foreach ($daftarMesin as $m): ?>
                    <option value="<?= esc($m['id_mesin']) ?>" <?= isset($selectedFilters['id_mesin']) && (int)$selectedFilters['id_mesin'] === (int)$m['id_mesin'] ? 'selected' : '' ?>><?= esc($m['no_mesin']) ?></option>
                  <?php endforeach; ?>
                </select>
              </th>
              <th class="p-1">
                <div class="d-flex gap-1">
                  <select name="lokasi" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Lokasi..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                    <option value=""></option>
                    <option value="all">Semua</option>
                    <option value="MFG 1" <?= isset($selectedFilters['lokasi']) && $selectedFilters['lokasi'] === 'MFG 1' ? 'selected' : '' ?>>MFG 1</option>
                    <option value="MFG 2" <?= isset($selectedFilters['lokasi']) && $selectedFilters['lokasi'] === 'MFG 2' ? 'selected' : '' ?>>MFG 2</option>
                  </select>
                  <select name="line" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Line..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                    <option value=""></option>
                    <option value="all">Semua</option>
                    <?php if (!empty($availableLines)) foreach ($availableLines as $l): ?>
                      <option value="<?= esc($l) ?>" <?= isset($selectedFilters['line']) && $selectedFilters['line'] === $l ? 'selected' : '' ?>><?= esc($l) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </th>
              <th class="p-1">
                <select name="jenis_check" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Jenis..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                  <option value=""></option>
                  <option value="all">Semua Jenis</option>
                  <option value="Preventive" <?= isset($selectedFilters['jenis_check']) && $selectedFilters['jenis_check'] === 'Preventive' ? 'selected' : '' ?>>Checklist Report</option>
                  <option value="Overhaul" <?= isset($selectedFilters['jenis_check']) && $selectedFilters['jenis_check'] === 'Overhaul' ? 'selected' : '' ?>>Inspection Report</option>
                </select>
              </th>
              <th class="p-1">
                <select name="bulan" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-no-sort="true" data-placeholder="Cari Bulan..." onchange="this.form.submit()" style="font-size: 0.75rem;">
                  <option value=""></option>
                  <option value="all">Semua Bulan</option>
                  <?php if (!empty($bulanList)) foreach ($bulanList as $val => $label): ?>
                    <option value="<?= esc($val) ?>" <?= isset($selectedFilters['bulan']) && $selectedFilters['bulan'] === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </th>
              <th class="p-1"></th>
              <th class="p-1"></th>
              <th class="p-1 text-center align-middle">
                <a href="<?= site_url('laporan/durasi') ?>" class="btn btn-sm btn-danger fw-bold px-2 py-1" title="Reset Filter" style="font-size: 0.7rem;">
                  <i class="bi bi-arrow-counterclockwise fw-bold"></i>
                </a>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; ?>
            <?php foreach ($laporan as $l): ?>
              <tr>
                <td class="text-center fw-semibold text-muted"><?= $no++ ?></td>
                <?php 
                  $rawNamaDurasi = $l['nama_pic'] ?: $l['nama_staff'];
                  $namaDurasiParts = explode(' - ', $rawNamaDurasi);
                  $namaDurasiOnly = end($namaDurasiParts);
                ?>
                <td><?= esc($namaDurasiOnly) ?></td>
                <td><?= esc($l['no_mesin']) ?> - <?= esc($l['type_mesin']) ?></td>
                <td><?= esc($l['lokasi_check']) ?> / <?= esc($l['line'] ?? '-') ?></td>
                <td>
                  <?php if (strtolower($l['jenis_check']) === 'overhaul'): ?>
                    <span class="badge bg-primary">Inspection Report</span>
                  <?php else: ?>
                    <span class="badge bg-info text-dark">Checklist Report</span>
                  <?php endif; ?>
                </td>
                <td><?= esc($l['waktu_mulai']) ?></td>
                <td><?= esc($l['waktu_selesai'] ?? '-') ?></td>
                <td>
                  <?php if ($l['durasi_detik'] !== null): ?>
                    <?= formatDurasi((int) $l['durasi_detik']) ?>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td>
                  <?php 
                    $qsDurasi = !empty($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : '';
                  ?>
                  <a href="<?= site_url('riwayat/' . $l['id_transaksi']) . '?from=durasi' . $qsDurasi ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if(empty($laporan)): ?>
              <tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data yang sesuai dengan filter.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </form>
    </div>
  <?php endif; ?>
</div>

<?= view('layout/footer') ?>
