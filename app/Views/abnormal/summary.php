<?= view('layout/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-end mb-4">
    <div>
        <h3 class="fw-bold mb-1"><i class="bi bi-table text-primary me-2"></i> Ringkasan Abnormal Report</h3>
        <p class="text-muted mb-0">Pantau Abnormal Report bulanan dan tindakan yang belum diselesaikan per area.</p>
    </div>
    
    <div class="d-flex gap-2">
        <form method="GET" action="<?= site_url('abnormal') ?>" class="d-flex flex-wrap gap-2 justify-content-end align-items-center" id="filterForm">
            <input type="hidden" name="view" value="summary">
            <?php
                $pdfUrl = site_url('abnormal/pdf-all-summary?bulan=' . urlencode($bulan));
                if (!empty($filterLokasi)) {
                    $pdfUrl .= '&filter_lokasi=' . urlencode($filterLokasi);
                }
                if (!empty($filterLine)) {
                    $pdfUrl .= '&filter_line=' . urlencode($filterLine);
                }
                if (!empty($filterKategori)) {
                    $pdfUrl .= '&filter_kategori=' . urlencode($filterKategori);
                }
            ?>
            <?php if (!in_array(session()->get('role'), ['sheadprd', 'sheadmtc', 'leader'], true)): ?>
            <?php
                $excelAbnormalUrl = site_url('abnormal/excel-all-summary?bulan=' . urlencode($bulan));
                if (!empty($filterLokasi)) $excelAbnormalUrl .= '&filter_lokasi=' . urlencode($filterLokasi);
                if (!empty($filterLine)) $excelAbnormalUrl .= '&filter_line=' . urlencode($filterLine);
                if (!empty($filterKategori)) $excelAbnormalUrl .= '&filter_kategori=' . urlencode($filterKategori);
            ?>
            <a href="<?= $excelAbnormalUrl ?>" class="btn btn-sm btn-outline-success fw-semibold shadow-sm" title="Export Excel">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
            </a>
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
                        <th style="width: 20%;">
                            <a href="<?= $getSortUrl('line') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                                LINE <?= $getSortIcon('line') ?>
                            </a>
                        </th>
                        <th style="width: 25%;">
                            <a href="<?= $getSortUrl('kategori') ?>" class="text-decoration-none text-secondary d-inline-flex align-items-center fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.08em;">
                                KATEGORI <?= $getSortIcon('kategori') ?>
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
                            <select name="filter_kategori" form="filterForm" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Kategori..." onchange="document.getElementById('filterForm').submit();">
                                <option value=""></option>
                                <option value="all" <?= ($filterKategori ?? '') === 'all' ? 'selected' : '' ?>>Semua Kategori</option>
                                <?php foreach ($availableCategories as $optCat): ?>
                                    <option value="<?= esc($optCat) ?>" <?= ($filterKategori ?? '') === $optCat ? 'selected' : '' ?>><?= esc($optCat) ?></option>
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
                            <a href="<?= site_url('abnormal') ?>" class="btn btn-sm btn-danger fw-bold px-3" title="Reset Filter" style="font-size: 0.75rem;">
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
                                <td class="ps-4 fw-bold text-dark"><?= esc($row['plant'] ?? 'Plant 1') ?></td>
                                <td class="fw-bold text-dark"><?= esc($row['departemen']) ?></td>
                                <td><span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25"><?= esc($row['line']) ?></span></td>
                                <td class="fw-medium text-dark"><?= esc($row['kategori']) ?></td>
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
                                    <a href="<?= site_url('abnormal?departemen=' . urlencode($row['departemen']) . '&line=' . urlencode($row['line']) . '&kategori=' . urlencode($row['kategori']) . '&bulan=' . urlencode($bulan)) ?>" class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3">
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

<!-- ═══════════════════════════════════════════════════════════════
     GRAFIK TREN ABNORMALITAS DINAMIS
     ═══════════════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>

<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-body p-0">
        <!-- Header Grafik -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-4 py-3 border-bottom">
            <div>
                <h6 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow text-danger me-2"></i>Tren Abnormalitas Bulanan</h6>
                <p class="text-muted mb-0" style="font-size:0.78rem;" id="chartSubtitle">Menampilkan data 12 bulan terakhir</p>
            </div>
            <!-- Filter interaktif -->
            <div class="d-flex flex-wrap gap-2 align-items-center" id="chartFilterBar">
                <!-- Plant -->
                <select id="chartPlant" class="form-select form-select-sm" style="width:auto; min-width:110px; font-size:0.8rem;">
                    <option value="">Semua Plant</option>
                    <option value="Plant 1">Plant 1</option>
                    <option value="Plant 2">Plant 2</option>
                </select>
                <!-- MFG / Departemen -->
                <select id="chartDepartemen" class="form-select form-select-sm" style="width:auto; min-width:110px; font-size:0.8rem;">
                    <option value="">Semua MFG</option>
                    <option value="MFG 1">MFG 1</option>
                    <option value="MFG 2">MFG 2</option>
                </select>
                <!-- Line (berubah dinamis berdasarkan MFG) -->
                <select id="chartLine" class="form-select form-select-sm" style="width:auto; min-width:110px; font-size:0.8rem;">
                    <option value="">Semua Line</option>
                    <option value="Line 1">Line 1</option>
                    <option value="Line 2">Line 2</option>
                    <option value="Line 3">Line 3</option>
                    <option value="CG">CG</option>
                    <option value="Second">Second</option>
                </select>
                <!-- Kategori -->
                <select id="chartKategori" class="form-select form-select-sm" style="width:auto; min-width:130px; font-size:0.8rem;">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($availableCategories as $cat): ?>
                    <option value="<?= esc($cat) ?>"><?= esc($cat) ?></option>
                    <?php endforeach; ?>
                </select>
                <!-- Rentang bulan -->
                <select id="chartBulanRange" class="form-select form-select-sm" style="width:auto; min-width:120px; font-size:0.8rem;">
                    <option value="6">6 Bulan Terakhir</option>
                    <option value="12" selected>12 Bulan Terakhir</option>
                </select>
                <!-- Badge keterangan mode -->
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1" id="chartModeBadge" style="font-size:0.72rem;">
                    <i class="bi bi-layers me-1"></i>Per Kategori
                </span>
            </div>
        </div>

        <!-- Container grafik ApexCharts -->
        <div id="abnormalTrendChart" style="min-height:340px; padding: 0 1rem;"></div>

        <!-- Pesan jika tidak ada data -->
        <div id="chartNoData" class="text-center py-5 d-none">
            <i class="bi bi-bar-chart-line text-muted" style="font-size:3rem; opacity:0.3;"></i>
            <p class="text-muted mt-2 mb-0">Tidak ada data abnormal untuk filter yang dipilih.</p>
        </div>
    </div>
</div>

<script>
(function() {
    // ───── Inisialisasi ─────────────────────────────────────────────
    const API_URL  = '<?= site_url("abnormal/chart-data") ?>';
    let chart      = null;
    let isLoading  = false;

    // Warna palet yang harmonis
    const PALETTE = [
        '#E53935', '#1E88E5', '#43A047', '#FB8C00',
        '#8E24AA', '#00ACC1', '#F06292', '#6D4C41',
        '#3949AB', '#00897B', '#FDD835', '#546E7A'
    ];

    // Sinkronisasi filter awal dengan state tabel (dari URL)
    <?php
        $urlPlant   = $filterPlant   ?? '';
        $urlLokasi  = $filterLokasi  ?? '';
        $urlLine    = $filterLine    ?? '';
        $urlKat     = $filterKategori ?? '';
    ?>
    document.getElementById('chartPlant').value      = '<?= ($urlPlant === 'all' || !$urlPlant) ? '' : esc($urlPlant) ?>';
    document.getElementById('chartDepartemen').value = '<?= ($urlLokasi === 'all' || !$urlLokasi) ? '' : esc($urlLokasi) ?>';
    document.getElementById('chartLine').value       = '<?= ($urlLine === 'all' || !$urlLine) ? '' : esc($urlLine) ?>';
    document.getElementById('chartKategori').value   = '<?= ($urlKat === 'all' || !$urlKat) ? '' : esc($urlKat) ?>';

    // ───── Buat instance ApexCharts ──────────────────────────────────
    function buildChartOptions(categories, series) {
        return {
            chart: {
                type: 'line',
                height: 320,
                toolbar: { show: true, tools: { download: true, selection: false, zoom: false, reset: false, pan: false, zoomin: false, zoomout: false } },
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
                fontFamily: 'Inter, sans-serif',
                background: 'transparent',
                foreColor: '#475569',
            },
            series: series,
            xaxis: {
                categories: categories,
                labels: { style: { fontSize: '11px' } },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                title: { text: 'Jumlah Abnormal', style: { fontSize: '11px', color: '#94a3b8' } },
                labels: { formatter: v => Math.round(v) },
                min: 0,
            },
            stroke: { curve: 'smooth', width: 2.5 },
            markers: { size: 4, strokeWidth: 0, hover: { size: 7 } },
            colors: PALETTE,
            legend: {
                position: 'bottom',
                fontSize: '12px',
                markers: { width: 10, height: 10, radius: 50 },
                itemMargin: { horizontal: 12 }
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: { formatter: v => v + ' titik abnormal' }
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 3,
                xaxis: { lines: { show: false } }
            },
            noData: { text: 'Memuat data...', align: 'center', verticalAlign: 'middle', style: { color: '#94a3b8' } }
        };
    }

    // ───── Fetch data & render/update grafik ─────────────────────────
    async function loadChart() {
        if (isLoading) return;
        isLoading = true;

        const plant      = document.getElementById('chartPlant').value;
        const departemen = document.getElementById('chartDepartemen').value;
        const line       = document.getElementById('chartLine').value;
        const kategori   = document.getElementById('chartKategori').value;
        const bulanRange = document.getElementById('chartBulanRange').value;

        const params = new URLSearchParams({ plant, departemen, line, kategori, bulan_range: bulanRange });

        try {
            const res  = await fetch(API_URL + '?' + params.toString());
            const json = await res.json();

            const hasSeries = json.series && json.series.length > 0 && json.categories && json.categories.length > 0;

            // Update subtitle & mode badge
            const groupBy = json.groupBy || 'kategori';
            const modeLabel = groupBy === 'line' ? 'Per Line' : 'Per Kategori';
            document.getElementById('chartModeBadge').innerHTML = `<i class="bi bi-layers me-1"></i>${modeLabel}`;

            let filterParts = [];
            if (plant) filterParts.push(plant);
            if (departemen) filterParts.push(departemen);
            if (line) filterParts.push(line);
            if (kategori) filterParts.push(kategori);
            const subtitle = filterParts.length ? 'Filter aktif: ' + filterParts.join(' › ') : 'Menampilkan semua area, semua kategori';
            document.getElementById('chartSubtitle').textContent = subtitle;

            if (!hasSeries) {
                document.getElementById('chartNoData').classList.remove('d-none');
                document.getElementById('abnormalTrendChart').style.display = 'none';
                if (chart) { chart.destroy(); chart = null; }
                return;
            }

            document.getElementById('chartNoData').classList.add('d-none');
            document.getElementById('abnormalTrendChart').style.display = 'block';

            if (!chart) {
                // Buat grafik baru pertama kali
                chart = new ApexCharts(
                    document.getElementById('abnormalTrendChart'),
                    buildChartOptions(json.categories, json.series)
                );
                chart.render();
            } else {
                // Update grafik yang sudah ada dengan animasi
                chart.updateOptions(buildChartOptions(json.categories, json.series), true, true);
            }

        } catch (err) {
            console.error('Chart load error:', err);
        } finally {
            isLoading = false;
        }
    }

    // ───── Event listeners pada semua dropdown ────────────────────────
    ['chartPlant', 'chartDepartemen', 'chartLine', 'chartKategori', 'chartBulanRange'].forEach(id => {
        document.getElementById(id).addEventListener('change', loadChart);
    });

    // ───── Filter Line berubah dinamis berdasarkan MFG yang dipilih ──
    document.getElementById('chartDepartemen').addEventListener('change', function() {
        const mfg  = this.value;
        const lineEl = document.getElementById('chartLine');
        const mfg1Lines = ['Line 1', 'Line 2', 'Line 3'];
        const mfg2Lines = ['CG', 'Second'];
        const allLines  = [...mfg1Lines, ...mfg2Lines];

        lineEl.innerHTML = '<option value="">Semua Line</option>';
        const linesToShow = mfg === 'MFG 1' ? mfg1Lines : (mfg === 'MFG 2' ? mfg2Lines : allLines);
        linesToShow.forEach(l => {
            const opt = document.createElement('option');
            opt.value = l; opt.textContent = l;
            lineEl.appendChild(opt);
        });
        loadChart(); // sudah terpanggil lewat change event di atas, ini redundan tapi aman
    });

    // ───── Load pertama saat halaman ready ───────────────────────────
    document.addEventListener('DOMContentLoaded', () => setTimeout(loadChart, 150));
})();
</script>

<br>
<?= view('layout/footer') ?>
