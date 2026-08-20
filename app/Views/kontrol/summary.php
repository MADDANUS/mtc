<?= view('layout/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-table text-primary me-2"></i> Ringkasan Checklist Control</h3>
        <p class="text-muted mb-0">Pantau progres pengisian Checklist Control bulanan di seluruh area.</p>
    </div>
    
    <div class="d-flex gap-2">
        <form method="GET" action="<?= site_url('kontrol') ?>" class="d-flex flex-wrap gap-2 justify-content-end align-items-center" id="filterForm">
            <input type="hidden" name="view" value="summary">
            <?php
                $pdfUrl = site_url('kontrol/pdf-all-summary?bulan=' . urlencode($bulan));
                if (!empty($filterLokasi)) $pdfUrl .= '&filter_lokasi=' . urlencode($filterLokasi);
                if (!empty($filterLine)) $pdfUrl .= '&filter_line=' . urlencode($filterLine);
                if (!empty($filterKategori)) $pdfUrl .= '&filter_kategori=' . urlencode($filterKategori);
            ?>
            <?php if (!in_array(session()->get('role'), ['sheadprd', 'sheadmtc', 'leader'], true)): ?>
            <a href="<?= $pdfUrl ?>" target="_blank" class="btn btn-sm btn-danger fw-semibold shadow-sm" title="Download PDF">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
            </a>
            <?php endif; ?>
            </form>
    </div>
</div>



<?php
// Helper functions for column sorting
$getSortUrl = function(string $column) use ($bulan, $filterLokasi, $filterLine, $filterKategori, $filterStatus, $sortBy, $order) {
    $params = [
        'view' => 'summary',
        'bulan' => $bulan,
        'filter_lokasi' => $filterLokasi,
        'filter_line' => $filterLine,
        'filter_kategori' => $filterKategori,
        'filter_status' => $filterStatus,
    ];

    if ($sortBy === $column) {
        $params['order'] = ($order === 'asc') ? 'desc' : 'asc';
    } else {
        $params['sort_by'] = $column;
        $params['order'] = 'asc';
    }

    return site_url('kontrol') . '?' . http_build_query($params);
};

$getSortIcon = function(string $column) use ($sortBy, $order) {
    if ($sortBy !== $column) {
        return '<i class="bi bi-arrow-down-up text-muted ms-1" style="font-size:0.75rem;"></i>';
    }

    return ($order === 'asc')
        ? '<i class="bi bi-sort-up text-primary ms-1" style="font-size:0.85rem;"></i>'
        : '<i class="bi bi-sort-down text-primary ms-1" style="font-size:0.85rem;"></i>';
};
?>

<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0 paginated-table">
                <thead class="table-light">
                    <!-- Baris Kolom dan Sorting -->
                    <tr>
                        <th class="ps-4" style="width: 15%;">
                            <a href="<?= $getSortUrl('lokasi') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                                LOKASI <?= $getSortIcon('lokasi') ?>
                            </a>
                        </th>
                        <th style="width: 15%;">
                            <a href="<?= $getSortUrl('line') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                                LINE <?= $getSortIcon('line') ?>
                            </a>
                        </th>
                        <th style="width: 20%;">
                            <a href="<?= $getSortUrl('kategori') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                                KATEGORI <?= $getSortIcon('kategori') ?>
                            </a>
                        </th>
                        <th class="fw-bold text-uppercase text-secondary align-middle" style="width: 20%; font-size: 0.72rem; letter-spacing: 0.08em;">
                            <a href="<?= $getSortUrl('percent') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase">
                                PROGRES PENGECEKAN <?= $getSortIcon('percent') ?>
                            </a>
                        </th>
                        <th style="width: 20%;">
                            <a href="<?= $getSortUrl('statusText') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                                STATUS APPROVAL <?= $getSortIcon('statusText') ?>
                            </a>
                        </th>
                        <th style="width: 15%;">
                            <span class="text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                                BULAN
                            </span>
                        </th>
                        <th class="pe-4 text-center fw-bold text-uppercase text-secondary align-middle" style="font-size: 0.72rem; letter-spacing: 0.08em;">Aksi</th>
                    </tr>
                    <!-- NEW FILTER ROW -->
                    <tr class="bg-white">
                        <th class="ps-4 py-2">
                            <select name="filter_lokasi" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Lokasi..." onchange="document.getElementById('filterForm').submit();">
                                <option value=""></option>
                                <option value="all" <?= ($filterLokasi ?? '') === 'all' ? 'selected' : '' ?>>Semua Lokasi</option>
                                <option value="MFG 1" <?= ($filterLokasi ?? '') === 'MFG 1' ? 'selected' : '' ?>>MFG 1</option>
                                <option value="MFG 2" <?= ($filterLokasi ?? '') === 'MFG 2' ? 'selected' : '' ?>>MFG 2</option>
                            </select>
                        </th>
                        <th class="py-2">
                              <select name="filter_line" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Line..." onchange="document.getElementById('filterForm').submit();">
                                  <option value=""></option>
                                  <option value="all" <?= ($filterLine ?? '') === 'all' ? 'selected' : '' ?>>Semua Line</option>
                                  <?php foreach ($availableLines as $optLine): ?>
                                      <option value="<?= esc($optLine) ?>" <?= ($filterLine ?? '') === $optLine ? 'selected' : '' ?>><?= esc($optLine) ?></option>
                                  <?php endforeach; ?>
                              </select>
                        </th>
                        <th class="py-2">
                              <select name="filter_kategori" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Kategori..." onchange="document.getElementById('filterForm').submit();">
                                  <option value=""></option>
                                  <option value="all" <?= ($filterKategori ?? '') === 'all' ? 'selected' : '' ?>>Semua Kategori</option>
                                  <?php foreach ($availableCategories as $optCat): ?>
                                      <option value="<?= esc($optCat) ?>" <?= ($filterKategori ?? '') === $optCat ? 'selected' : '' ?>><?= esc($optCat) ?></option>
                                  <?php endforeach; ?>
                              </select>
                        </th>
                        <th class="py-2"></th>
                        <th class="py-2">
                            <!-- Filter status approval dihilangkan -->
                        </th>
                        <th class="py-2">
                            <select name="bulan" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-no-sort="true" onchange="document.getElementById('filterForm').submit();">
                                <?php foreach ($bulanList as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $val === $bulan ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </th>
                        <th class="pe-4 py-2 text-center align-middle">
                            <a href="<?= site_url('kontrol?view=summary') ?>" class="btn btn-sm btn-danger fw-bold px-3" title="Reset Filter" style="font-size: 0.75rem;">
                                <i class="bi bi-arrow-counterclockwise fw-bold"></i> Reset
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if(empty($summaryRows)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($summaryRows as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= esc($row['lokasi']) ?></td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"><?= esc($row['line']) ?></span></td>
                                <td class="fw-medium text-dark"><?= esc($row['kategori']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            <div class="progress-bar <?= $row['percent'] == 100 ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $row['percent'] ?>%;" aria-valuenow="<?= $row['percent'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="small fw-bold <?= $row['percent'] == 100 ? 'text-success' : 'text-primary' ?>" style="min-width: 45px;"><?= $row['checked'] ?>/<?= $row['total'] ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?= $row['badgeClass'] ?> rounded-pill px-3 py-2"><?= $row['statusText'] ?></span>
                                </td>
                                <td class="fw-bold text-dark" style="font-size: 0.85rem;"><?= $bulanList[$bulan] ?? $bulan ?></td>
                                <td class="pe-4 text-end">
                                    <?php 
                                        $qsSummary = !empty($_SERVER['QUERY_STRING']) ? '&qs_summary=' . urlencode($_SERVER['QUERY_STRING']) : '';
                                    ?>
                                    <a href="<?= site_url('kontrol?lokasi=' . urlencode($row['lokasi']) . '&line=' . urlencode($row['line']) . '&kategori=' . urlencode($row['kategori']) . '&bulan=' . urlencode($bulan) . $qsSummary) ?>" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">
                                        Lihat Form
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($notCheckedRows)): ?>
            <div class="mt-4">
                <div class="alert alert-warning mb-0 shadow-sm border border-warning border-opacity-50" role="alert" style="border-radius: 12px;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-info-circle-fill text-warning fs-5 me-2"></i>
                        <h6 class="alert-heading fw-bold mb-0 text-dark">Belum Ada Pengecekan Sama Sekali</h6>
                    </div>
                    <p class="mb-2 text-dark" style="font-size: 0.85rem;">Berikut adalah daftar Line & Kategori yang belum memiliki riwayat pengecekan pada bulan yang dipilih:</p>
                    <ul class="mb-0 text-dark" style="font-size: 0.85rem; columns: 2; -webkit-columns: 2; -moz-columns: 2;">
                        <?php foreach ($notCheckedRows as $nc): ?>
                            <li class="mb-1"><strong><?= esc($nc['lokasi']) ?> - <?= esc($nc['line']) ?></strong> (<?= esc($nc['kategori']) ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= view('layout/footer') ?>

