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
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="card-body p-4 d-flex align-items-center">
                <!-- Doughnut Chart (CSS Based) -->
                <div class="position-relative d-inline-flex align-items-center justify-content-center me-4" style="width: 100px; height: 100px; border-radius: 50%; background: conic-gradient(#198754 <?= $percentageSummary['preventive']['coverage'] ?>%, #e9ecef 0);">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 75px; height: 75px;">
                        <span class="fs-5 fw-bold text-dark"><?= $percentageSummary['preventive']['coverage'] ?>%</span>
                    </div>
                </div>
                <!-- Text Data -->
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1 text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 1px;">Pencapaian Preventive</h5>
                    <?php 
                        $rawBulan = $percentageSummary['bulan']; 
                        $fmtBulan = preg_match('/^\d{4}-\d{2}$/', $rawBulan) ? date('m/Y', strtotime($rawBulan . '-01')) : $rawBulan;
                    ?>
                    <p class="text-muted mb-2 small"><i class="bi bi-calendar2-check me-1"></i> <?= $fmtBulan ?></p>
                    <div class="d-flex gap-3">
                        <div>
                            <span class="d-block fw-bold text-success fs-5"><?= $percentageSummary['preventive']['normal_count'] ?> <small class="fw-normal text-muted fs-6">Normal (<?= $percentageSummary['preventive']['normal'] ?>%)</small></span>
                        </div>
                        <div class="border-start ps-3">
                            <span class="d-block fw-bold text-danger fs-5"><?= $percentageSummary['preventive']['abnormal_count'] ?> <small class="fw-normal text-muted fs-6">Abnormal (<?= $percentageSummary['preventive']['abnormal'] ?>%)</small></span>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">Dari <?= $percentageSummary['total_mesin'] ?> Total Mesin di Pabrik</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Overhaul Progress -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
            <div class="card-body p-4 d-flex align-items-center">
                <!-- Doughnut Chart (CSS Based) -->
                <div class="position-relative d-inline-flex align-items-center justify-content-center me-4" style="width: 100px; height: 100px; border-radius: 50%; background: conic-gradient(#0d6efd <?= $percentageSummary['overhaul']['coverage'] ?>%, #e9ecef 0);">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 75px; height: 75px;">
                        <span class="fs-5 fw-bold text-dark"><?= $percentageSummary['overhaul']['coverage'] ?>%</span>
                    </div>
                </div>
                <!-- Text Data -->
                <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1 text-uppercase text-secondary" style="font-size: 0.85rem; letter-spacing: 1px;">Pencapaian Overhaul</h5>
                    <p class="text-muted mb-2 small"><i class="bi bi-calendar-range me-1"></i> <?= $percentageSummary['semester'] ?></p>
                    <div class="d-flex gap-3">
                        <div>
                            <span class="d-block fw-bold text-success fs-5"><?= $percentageSummary['overhaul']['normal_count'] ?> <small class="fw-normal text-muted fs-6">Normal (<?= $percentageSummary['overhaul']['normal'] ?>%)</small></span>
                        </div>
                        <div class="border-start ps-3">
                            <span class="d-block fw-bold text-danger fs-5"><?= $percentageSummary['overhaul']['abnormal_count'] ?> <small class="fw-normal text-muted fs-6">Abnormal (<?= $percentageSummary['overhaul']['abnormal'] ?>%)</small></span>
                        </div>
                    </div>
                    <small class="text-muted mt-2 d-block">Dari <?= $percentageSummary['total_mesin'] ?> Total Mesin di Pabrik</small>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
