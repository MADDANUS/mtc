<?= view('layout/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-table text-primary me-2"></i> Ringkasan Abnormal Report</h3>
        <p class="text-muted mb-0">Pantau Abnormal Report bulanan dan tindakan yang belum diselesaikan per area.</p>
    </div>
    
    <div class="d-flex gap-2">
        <form id="filterForm" action="<?= site_url('abnormal/overhaul') ?>" method="get" class="mb-0">
            <input type="hidden" name="view" value="summary">
            <?php
                $pdfUrl = site_url('abnormal/pdfAllSummaryOverhaul?bulan=' . urlencode($bulan));
                if (!empty($filterLokasi)) {
                    $pdfUrl .= '&filter_lokasi=' . urlencode($filterLokasi);
                }
                if (!empty($filterLine)) {
                    $pdfUrl .= '&filter_line=' . urlencode($filterLine);
                }
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
$getSortUrl = function(string $column) use ($bulan, $filterPlant, $filterLokasi, $filterLine, $filterStatus, $sortBy, $order) {
    $params = [
        'view' => 'summary',
        'bulan' => $bulan,
        'filter_plant' => $filterPlant,
        'filter_lokasi' => $filterLokasi,
        'filter_line' => $filterLine,
        'filter_status' => $filterStatus,
    ];

    if ($sortBy === $column) {
        $params['order'] = ($order === 'asc') ? 'desc' : 'asc';
    } else {
        $params['sort_by'] = $column;
        $params['order'] = 'asc';
    }

    return site_url('abnormal') . '?' . http_build_query($params);
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
                            <span class="text-white d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                                PLANT
                            </span>
                        </th>
                        <th style="width: 20%;">
                            <a href="<?= $getSortUrl('departemen') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                                DEPARTEMEN <?= $getSortIcon('departemen') ?>
                            </a>
                        </th>
                        <th style="width: 25%;">
                            <a href="<?= $getSortUrl('line') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                                LINE <?= $getSortIcon('line') ?>
                            </a>
                        </th>
                        <th class="fw-bold text-uppercase text-secondary align-middle" style="width: 25%; font-size: 0.72rem; letter-spacing: 0.08em;">
                            <a href="<?= $getSortUrl('statusText') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase">
                                STATUS PERBAIKAN <?= $getSortIcon('statusText') ?>
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
                            <select name="filter_plant" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Plant..." onchange="document.getElementById('filterForm').submit();">
                                <option value="all" <?= ($filterPlant ?? '') === 'all' ? 'selected' : '' ?>>Semua Plant</option>
                                <option value="Plant 1" <?= ($filterPlant ?? '') === 'Plant 1' ? 'selected' : '' ?>>Plant 1</option>
                                <option value="Plant 2" <?= ($filterPlant ?? '') === 'Plant 2' ? 'selected' : '' ?>>Plant 2</option>
                            </select>
                        </th>
                        <th class="py-2">
                            <select name="filter_lokasi" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Departemen..." onchange="document.getElementById('filterForm').submit();">
                                <option value=""></option>
                                <option value="all" <?= ($filterLokasi ?? '') === 'all' ? 'selected' : '' ?>>Semua Departemen</option>
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
                            <select name="filter_status" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Status..." onchange="document.getElementById('filterForm').submit();">
                                <option value=""></option>
                                <option value="all" <?= ($filterStatus ?? '') === 'all' ? 'selected' : '' ?>>Semua Status</option>
                                <option value="Belum Perbaikan" <?= ($filterStatus ?? '') === 'Belum Perbaikan' ? 'selected' : '' ?>>Belum Perbaikan</option>
                                <option value="Sudah Perbaikan" <?= ($filterStatus ?? '') === 'Sudah Perbaikan' ? 'selected' : '' ?>>Sudah Perbaikan</option>
                            </select>
                        </th>
                        <th class="py-2">
                            <select name="bulan" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-no-sort="true" onchange="document.getElementById('filterForm').submit();">
                                <?php foreach ($bulanList as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $val === $bulan ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </th>
                        <th class="pe-4 py-2 text-center align-middle">
                            <a href="<?= site_url('abnormal/overhaul') ?>" class="btn btn-sm btn-danger fw-bold px-3" title="Reset Filter" style="font-size: 0.75rem;">
                                <i class="bi bi-arrow-counterclockwise fw-bold"></i> Reset
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if(empty($summaryRows)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada data ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($summaryRows as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= esc($row['plant'] ?? 'Plant 1') ?></td>
                                <td class="fw-bold text-dark"><?= esc($row['departemen']) ?></td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"><?= esc($row['line']) ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge <?= $row['badgeClass'] ?> rounded-pill px-3 py-2"><?= $row['statusText'] ?></span>
                                        <?php if ($row['totalOpen'] > 0): ?>
                                            <span class="badge bg-danger rounded-circle p-1" title="<?= $row['totalOpen'] ?> Laporan Belum Diselesaikan">
                                                <?= $row['totalOpen'] ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="fw-bold text-dark" style="font-size: 0.85rem;"><?= $bulanList[$bulan] ?? $bulan ?></td>
                                <td class="pe-4 text-end">
                                    <a href="<?= site_url('abnormal/overhaul?departemen=' . urlencode($row['departemen']) . '&line=' . urlencode($row['line']) . '&bulan=' . urlencode($bulan)) ?>" class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3">
                                        Lihat Data
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
