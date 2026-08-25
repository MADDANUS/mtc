<?php if (isset($percentageSummary)): 
    $transaksiModel = new \App\Models\TransaksiCheckModel();
    $bulanList = $transaksiModel->getAvailableBulan();
    $currentBulan = service('request')->getGet('bulan') ?: date('Y-m');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h5 class="m-0 fw-bold text-secondary" style="letter-spacing: 0.5px;"><i class="bi bi-bar-chart-fill me-2 text-primary"></i>Ringkasan Pencapaian</h5>
    <form action="" method="get" class="d-flex align-items-center mt-2 mt-md-0 bg-white px-3 py-1 rounded-pill shadow-sm border">
        <i class="bi bi-calendar-event text-primary me-2"></i>
        <label class="me-2 small fw-bold text-muted text-nowrap mb-0" style="font-size:0.75rem;">TARGET BULAN:</label>
        <select name="bulan" class="form-select form-select-sm border-0 fw-bold bg-transparent shadow-none" onchange="this.form.submit()" style="width: auto; cursor:pointer;">
            <option value="<?= date('Y-m') ?>">Bulan Berjalan</option>
            <?php foreach ($bulanList as $val): 
                // Skip if not valid
                if(empty($val['bulan'])) continue;
                $fmt = \CodeIgniter\I18n\Time::createFromFormat('Y-m', $val['bulan'])->toLocalizedString('MMMM yyyy');
            ?>
                <option value="<?= $val['bulan'] ?>" <?= $currentBulan === $val['bulan'] ? 'selected' : '' ?>><?= strtoupper($fmt) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<div class="row mb-4">
    <!-- Preventive Progress -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden detail-card" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); cursor: pointer; transition: transform 0.2s;" onclick="showDetailPencapaian('preventive', '<?= $currentBulan ?>')">
            <div class="card-body p-4 d-flex align-items-center">
                <!-- Doughnut Chart (CSS Based) -->
                <div class="position-relative d-inline-flex align-items-center justify-content-center me-4" style="width: 80px; height: 80px; border-radius: 50%; background: conic-gradient(#198754 <?= $percentageSummary['preventive']['coverage'] ?>%, #e9ecef 0);">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                        <span class="fs-6 fw-bold text-dark"><?= $percentageSummary['preventive']['coverage'] ?>%</span>
                    </div>
                </div>
                <!-- Text Data -->
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1 text-uppercase text-secondary" style="font-size: 0.8rem; letter-spacing: 1px;">Pencapaian Preventive</h5>
                    <?php 
                        $rawBulan = $percentageSummary['bulan']; 
                        $fmtBulan = preg_match('/^\d{4}-\d{2}$/', $rawBulan) ? date('m/Y', strtotime($rawBulan . '-01')) : $rawBulan;
                    ?>
                    <p class="text-muted mb-2 small"><i class="bi bi-calendar2-check me-1"></i> <?= $fmtBulan ?></p>
                    <div class="d-flex gap-2">
                        <div>
                            <span class="d-block fw-bold text-success fs-6"><?= $percentageSummary['preventive']['normal_count'] ?> <small class="fw-normal text-muted" style="font-size:0.7rem;">Normal</small></span>
                        </div>
                        <div class="border-start ps-2">
                            <span class="d-block fw-bold text-danger fs-6"><?= $percentageSummary['preventive']['abnormal_count'] ?> <small class="fw-normal text-muted" style="font-size:0.7rem;">Abnorm.</small></span>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block" style="font-size:0.7rem;">Dari <?= $percentageSummary['total_mesin'] ?> Total Mesin di Area Anda <span class="ms-1 badge bg-light text-primary border border-primary opacity-75">Lihat Detail <i class="bi bi-arrow-right-short"></i></span></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Overhaul Progress Plant 1 -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden detail-card" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); cursor: pointer; transition: transform 0.2s;" onclick="showDetailPencapaian('overhaul_plant1', '<?= $currentBulan ?>')">
            <div class="card-body p-4 d-flex align-items-center">
                <!-- Doughnut Chart (CSS Based) -->
                <div class="position-relative d-inline-flex align-items-center justify-content-center me-4" style="width: 80px; height: 80px; border-radius: 50%; background: conic-gradient(#0d6efd <?= $percentageSummary['overhaul_plant1']['coverage'] ?>%, #e9ecef 0);">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                        <span class="fs-6 fw-bold text-dark"><?= $percentageSummary['overhaul_plant1']['coverage'] ?>%</span>
                    </div>
                </div>
                <!-- Text Data -->
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1 text-uppercase text-secondary" style="font-size: 0.8rem; letter-spacing: 1px;">Pencapaian Overhaul (Plant 1)</h5>
                    <p class="text-muted mb-2 small" style="font-size:0.7rem;"><i class="bi bi-calendar-range me-1"></i> <?= $percentageSummary['periode_plant1'] ?></p>
                    <div class="d-flex gap-2">
                        <div>
                            <span class="d-block fw-bold text-success fs-6"><?= $percentageSummary['overhaul_plant1']['normal_count'] ?> <small class="fw-normal text-muted" style="font-size:0.7rem;">Normal</small></span>
                        </div>
                        <div class="border-start ps-2">
                            <span class="d-block fw-bold text-danger fs-6"><?= $percentageSummary['overhaul_plant1']['abnormal_count'] ?> <small class="fw-normal text-muted" style="font-size:0.7rem;">Abnorm.</small></span>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block" style="font-size:0.7rem;">Dari <?= $percentageSummary['total_mesin_ov_p1'] ?? 0 ?> Total Mesin Target <span class="ms-1 badge bg-light text-primary border border-primary opacity-75">Lihat Detail <i class="bi bi-arrow-right-short"></i></span></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Overhaul Progress Plant 2 -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden detail-card" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); cursor: pointer; transition: transform 0.2s;" onclick="showDetailPencapaian('overhaul_plant2', '<?= $currentBulan ?>')">
            <div class="card-body p-4 d-flex align-items-center">
                <!-- Doughnut Chart (CSS Based) -->
                <div class="position-relative d-inline-flex align-items-center justify-content-center me-4" style="width: 80px; height: 80px; border-radius: 50%; background: conic-gradient(#0dcaf0 <?= $percentageSummary['overhaul_plant2']['coverage'] ?>%, #e9ecef 0);">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                        <span class="fs-6 fw-bold text-dark"><?= $percentageSummary['overhaul_plant2']['coverage'] ?>%</span>
                    </div>
                </div>
                <!-- Text Data -->
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1 text-uppercase text-secondary" style="font-size: 0.8rem; letter-spacing: 1px;">Pencapaian Overhaul (Plant 2)</h5>
                    <p class="text-muted mb-2 small" style="font-size:0.7rem;"><i class="bi bi-calendar-range me-1"></i> <?= $percentageSummary['periode_plant2'] ?></p>
                    <div class="d-flex gap-2">
                        <div>
                            <span class="d-block fw-bold text-success fs-6"><?= $percentageSummary['overhaul_plant2']['normal_count'] ?> <small class="fw-normal text-muted" style="font-size:0.7rem;">Normal</small></span>
                        </div>
                        <div class="border-start ps-2">
                            <span class="d-block fw-bold text-danger fs-6"><?= $percentageSummary['overhaul_plant2']['abnormal_count'] ?> <small class="fw-normal text-muted" style="font-size:0.7rem;">Abnorm.</small></span>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block" style="font-size:0.7rem;">Dari <?= $percentageSummary['total_mesin_ov_p2'] ?? 0 ?> Total Mesin Target <span class="ms-1 badge bg-light text-primary border border-primary opacity-75">Lihat Detail <i class="bi bi-arrow-right-short"></i></span></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Pencapaian -->
<div class="modal fade" id="modalDetailPencapaian" tabindex="-1" aria-labelledby="modalDetailPencapaianLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalDetailPencapaianLabel"><i class="bi bi-list-check me-2"></i>Detail <span id="detailJenisTitle"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <!-- Loader -->
        <div id="detailLoader" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Mengambil data mesin...</p>
        </div>
        
        <!-- Content -->
        <div id="detailContent" class="d-none">
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <div class="fw-bold text-secondary"><i class="bi bi-calendar-event me-1"></i> <span id="detailPeriodeText"></span></div>
                <div class="d-flex align-items-center gap-2">
                    <button id="btnAkhiriPeriode" class="btn btn-sm btn-danger d-none" onclick="akhiriPeriode()" title="Akhiri siklus saat ini"><i class="bi bi-stop-circle me-1"></i>Akhiri Periode</button>
                    <button id="btnAwaliPeriode" class="btn btn-sm btn-success d-none" onclick="awaliPeriode()" title="Mulai siklus baru"><i class="bi bi-play-circle me-1"></i>Awali Periode</button>
                    <div style="width: 250px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchMesin" class="form-control form-control-sm" placeholder="Cari mesin...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs px-3 pt-3" id="pencapaianTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold text-success" id="checked-tab" data-bs-toggle="tab" data-bs-target="#checked-pane" type="button" role="tab">
                        <i class="bi bi-check-circle-fill me-1"></i> Sudah Dicek (<span id="countChecked">0</span>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold text-danger" id="unchecked-tab" data-bs-toggle="tab" data-bs-target="#unchecked-pane" type="button" role="tab">
                        <i class="bi bi-x-circle-fill me-1"></i> Belum Dicek (<span id="countUnchecked">0</span>)
                    </button>
                </li>
            </ul>
            
            <!-- Tab Panes -->
            <div class="tab-content" id="pencapaianTabContent">
                <div class="tab-pane fade show active p-0" id="checked-pane" role="tabpanel" tabindex="0">
                    <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0 table-sm" id="tableChecked">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3 py-2">No Mesin</th>
                                    <th>Type</th>
                                    <th>Plant</th>
                                    <th>Departemen</th>
                                    <th>Line</th>
                                    <th class="text-center pe-3">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade p-0" id="unchecked-pane" role="tabpanel" tabindex="0">
                    <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                        <table class="table table-hover table-striped mb-0 table-sm" id="tableUnchecked">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3 py-2">No Mesin</th>
                                    <th>Type</th>
                                    <th>Plant</th>
                                    <th>Departemen</th>
                                    <th>Line</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.detail-card:hover { transform: translateY(-3px) scale(1.01); }
</style>

<script>
let allChecked = [];
let allUnchecked = [];
let currentPlant = null;

function showDetailPencapaian(jenis, bulan) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetailPencapaian'));
    modal.show();
    
    document.getElementById('detailLoader').classList.remove('d-none');
    document.getElementById('detailContent').classList.add('d-none');
    document.getElementById('searchMesin').value = '';
    document.getElementById('btnAkhiriPeriode').classList.add('d-none');
    document.getElementById('btnAwaliPeriode').classList.add('d-none');
    
    // Fetch data
    let url = `<?= site_url('dashboard/detail-pencapaian') ?>?jenis=${jenis}&bulan=${bulan}`;
    <?php if($val = service('request')->getGet('departemen') ?: session()->get('departemen')): ?>
    url += `&departemen=<?= urlencode($val) ?>`;
    <?php endif; ?>
    <?php if($val = session()->get('plant')): ?>
    url += `&plant=<?= urlencode($val) ?>`;
    <?php endif; ?>
    <?php if($val = session()->get('line')): ?>
    url += `&line=<?= urlencode($val) ?>`;
    <?php endif; ?>

    fetch(url)
        .then(response => response.json())
        .then(data => {
            document.getElementById('detailJenisTitle').innerText = data.jenis;
            document.getElementById('detailPeriodeText').innerText = data.periode;
            
            allChecked = data.sudah_dicek;
            allUnchecked = data.belum_dicek;
            currentPlant = data.plant;
            
            if (data.is_overhaul && data.plant && data.can_manage) {
                if (data.has_active_cycle) {
                    document.getElementById('btnAkhiriPeriode').classList.remove('d-none');
                } else {
                    document.getElementById('btnAwaliPeriode').classList.remove('d-none');
                }
            }
            
            document.getElementById('countChecked').innerText = allChecked.length;
            document.getElementById('countUnchecked').innerText = allUnchecked.length;
            
            renderTables(allChecked, allUnchecked);
            
            document.getElementById('detailLoader').classList.add('d-none');
            document.getElementById('detailContent').classList.remove('d-none');
        })
        .catch(err => {
            console.error(err);
            document.getElementById('detailLoader').innerHTML = '<div class="text-danger py-4"><i class="bi bi-exclamation-triangle fs-1"></i><p>Gagal memuat data.</p></div>';
        });
}

function akhiriPeriode() {
    if (!currentPlant) return;
    
    if (confirm(`PERINGATAN: Apakah Anda yakin ingin MENGAKHIRI siklus Overhaul untuk ${currentPlant} saat ini?`)) {
        document.getElementById('btnAkhiriPeriode').disabled = true;
        document.getElementById('btnAkhiriPeriode').innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        
        const formData = new FormData();
        formData.append('plant', currentPlant);
        
        fetch('<?= site_url('dashboard/akhiri-periode-overhaul') ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Gagal: ' + data.message);
                document.getElementById('btnAkhiriPeriode').disabled = false;
                document.getElementById('btnAkhiriPeriode').innerHTML = '<i class="bi bi-stop-circle me-1"></i>Akhiri Periode';
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan.');
            document.getElementById('btnAkhiriPeriode').disabled = false;
            document.getElementById('btnAkhiriPeriode').innerHTML = '<i class="bi bi-stop-circle me-1"></i>Akhiri Periode';
        });
    }
}

function awaliPeriode() {
    if (!currentPlant) return;
    
    if (confirm(`Apakah Anda yakin ingin MEMULAI siklus Overhaul BARU untuk ${currentPlant} mulai hari ini?`)) {
        document.getElementById('btnAwaliPeriode').disabled = true;
        document.getElementById('btnAwaliPeriode').innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        
        const formData = new FormData();
        formData.append('plant', currentPlant);
        
        fetch('<?= site_url('dashboard/awali-periode-overhaul') ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Gagal: ' + data.message);
                document.getElementById('btnAwaliPeriode').disabled = false;
                document.getElementById('btnAwaliPeriode').innerHTML = '<i class="bi bi-play-circle me-1"></i>Awali Periode';
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan.');
            document.getElementById('btnAwaliPeriode').disabled = false;
            document.getElementById('btnAwaliPeriode').innerHTML = '<i class="bi bi-play-circle me-1"></i>Awali Periode';
        });
    }
}

function renderTables(checked, unchecked) {
    const tbodyChecked = document.querySelector('#tableChecked tbody');
    const tbodyUnchecked = document.querySelector('#tableUnchecked tbody');
    
    tbodyChecked.innerHTML = '';
    if (checked.length === 0) {
        tbodyChecked.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada mesin yang sudah dicek</td></tr>';
    } else {
        checked.forEach(m => {
            let statusBadge = '';
            if (m.kondisi === 'V') {
                statusBadge = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Normal</span>';
            } else if (m.kondisi === 'Δ') {
                statusBadge = '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle me-1"></i>Abnormal</span>';
            } else if (m.kondisi === 'X') {
                statusBadge = '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Tidak Ada</span>';
            } else {
                statusBadge = '<span class="badge bg-secondary">' + m.kondisi + '</span>';
            }
            
            tbodyChecked.innerHTML += `
                <tr>
                    <td class="ps-3 fw-bold">${m.no_mesin}</td>
                    <td>${m.type_mesin || '-'}</td>
                    <td><span class="text-muted fw-medium">${m.plant || '-'}</span></td>
                    <td><span class="badge bg-secondary">${m.departemen}</span></td>
                    <td>${m.line || '-'}</td>
                    <td class="text-center pe-3">${statusBadge}</td>
                </tr>
            `;
        });
    }

    tbodyUnchecked.innerHTML = '';
    if (unchecked.length === 0) {
        tbodyUnchecked.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Semua mesin sudah dicek!</td></tr>';
    } else {
        unchecked.forEach(m => {
            tbodyUnchecked.innerHTML += `
                <tr>
                    <td class="ps-3 fw-bold">${m.no_mesin}</td>
                    <td>${m.type_mesin || '-'}</td>
                    <td><span class="text-muted fw-medium">${m.plant || '-'}</span></td>
                    <td><span class="badge bg-secondary">${m.departemen}</span></td>
                    <td>${m.line || '-'}</td>
                </tr>
            `;
        });
    }
}

document.getElementById('searchMesin').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    
    const filterFn = (m) => 
        (m.no_mesin || '').toLowerCase().includes(term) || 
        (m.type_mesin || '').toLowerCase().includes(term) ||
        (m.line || '').toLowerCase().includes(term);
        
    const filteredChecked = allChecked.filter(filterFn);
    const filteredUnchecked = allUnchecked.filter(filterFn);
    
    renderTables(filteredChecked, filteredUnchecked);
});
</script>
<?php endif; ?>
