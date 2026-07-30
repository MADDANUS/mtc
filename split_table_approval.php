<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/approval/index.php';
$content = file_get_contents($file);
$content = preg_replace('/\r\n|\r/', "\n", $content);

// 1. Header Columns Replace
$oldHeaders = <<<'EOD'
            <th style="width:4%;" class="text-center fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">#</th>
            <th style="width:14%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Tipe Dokumen</th>
            <th style="width:20%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Keterangan</th>
            <th style="width:10%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Lokasi / Line</th>
            <th style="width:12%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Dibuat Oleh</th>
            <th style="width:13%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Tanggal / Bulan</th>
            <th style="width:12%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Status</th>
            <th style="width:10%;" class="fw-bold text-uppercase text-secondary text-center" style="font-size:0.72rem; letter-spacing:0.08em;">Aksi</th>
EOD;

$newHeaders = <<<'EOD'
            <th style="width:4%;" class="text-center fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">#</th>
            <th style="width:12%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Tipe Dokumen</th>
            <th style="width:12%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Kategori</th>
            <th style="width:16%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">No Mesin</th>
            <th style="width:11%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Lokasi / Line</th>
            <th style="width:12%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Dibuat Oleh</th>
            <th style="width:12%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Tanggal / Bulan</th>
            <th style="width:11%;" class="fw-bold text-uppercase text-secondary" style="font-size:0.72rem; letter-spacing:0.08em;">Status</th>
            <th style="width:10%;" class="fw-bold text-uppercase text-secondary text-center" style="font-size:0.72rem; letter-spacing:0.08em;">Aksi</th>
EOD;

// 2. Filter Row Replace
$oldFilters = <<<'EOD'
            <!-- Filter Kategori & Mesin -->
            <th class="p-1" style="min-width:200px;">
              <div class="d-flex flex-column gap-1">
                <select name="kategori" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Kategori..." onchange="this.form.submit()" style="font-size:0.75rem;">
                  <option value=""></option>
                  <option value="all" <?= ($filterKategori === 'all') ? 'selected' : '' ?>>Semua Kategori</option>
                  <?php foreach ($uniqueKategori as $kat): ?>
                    <option value="<?= esc($kat) ?>" <?= ($filterKategori === $kat) ? 'selected' : '' ?>><?= esc($kat) ?></option>
                  <?php endforeach; ?>
                </select>
                <select name="mesin" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="No Mesin..." onchange="this.form.submit()" style="font-size:0.75rem;">
                  <option value=""></option>
                  <option value="all" <?= ($filterMesin === 'all') ? 'selected' : '' ?>>Semua Mesin</option>
                  <?php foreach ($uniqueMesin as $mNo => $mLabel): ?>
                    <option value="<?= esc($mNo) ?>" <?= ($filterMesin === $mNo) ? 'selected' : '' ?>><?= esc($mLabel) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </th>
EOD;

$newFilters = <<<'EOD'
            <!-- Filter Kategori -->
            <th class="p-1" style="min-width:130px;">
              <select name="kategori" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Kategori..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterKategori === 'all') ? 'selected' : '' ?>>Semua Kategori</option>
                <?php foreach ($uniqueKategori as $kat): ?>
                  <option value="<?= esc($kat) ?>" <?= ($filterKategori === $kat) ? 'selected' : '' ?>><?= esc($kat) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
            <!-- Filter Mesin -->
            <th class="p-1" style="min-width:160px;">
              <select name="mesin" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="No Mesin..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterMesin === 'all') ? 'selected' : '' ?>>Semua Mesin</option>
                <?php foreach ($uniqueMesin as $mNo => $mLabel): ?>
                  <option value="<?= esc($mNo) ?>" <?= ($filterMesin === $mNo) ? 'selected' : '' ?>><?= esc($mLabel) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
EOD;

// 3. Data Rows Replace 
$oldDataRow1 = <<<'EOD'
                // Keterangan
                if ($isKontrol) {
                    $keterangan = esc($doc['kategori']);
EOD;

$newDataRow1 = <<<'EOD'
                // Keterangan
                if ($isKontrol) {
                    $keterangan = esc($doc['kategori']); // Tetap untuk JS
                    $tdKategori = esc($doc['kategori']);
                    $tdMesin    = '-';
EOD;

$oldDataRow2 = <<<'EOD'
                } else {
                    $keterangan = esc($doc['no_mesin'] ?? '') . ' - ' . esc($doc['type_mesin'] ?? '') . ' (' . esc($doc['kategori'] ?? '') . ')';
EOD;

$newDataRow2 = <<<'EOD'
                } else {
                    $keterangan = esc($doc['no_mesin'] ?? '') . ' - ' . esc($doc['type_mesin'] ?? '') . ' (' . esc($doc['kategori'] ?? '') . ')'; // Tetap untuk JS
                    $tdKategori = esc($doc['kategori'] ?? '-');
                    $tdMesin    = esc($doc['no_mesin'] ?? '') . (!empty($doc['type_mesin']) ? ' - ' . esc($doc['type_mesin']) : '');
EOD;

$oldDataTds = <<<'EOD'
              <tr>
                <td class="fw-semibold text-muted text-center"><?= $no++ ?></td>
                <td><?= $tipeBadge ?></td>
                <td style="font-size:0.85rem; white-space:normal;"><?= $keterangan ?></td>
                <td style="font-size:0.82rem; color:var(--text-secondary);"><?= $lokasiLine ?></td>
EOD;

$newDataTds = <<<'EOD'
              <tr>
                <td class="fw-semibold text-muted text-center"><?= $no++ ?></td>
                <td><?= $tipeBadge ?></td>
                <td style="font-size:0.85rem; white-space:normal; font-weight:600;"><?= $tdKategori ?></td>
                <td style="font-size:0.85rem; white-space:normal; color:var(--text-secondary);"><?= $tdMesin ?></td>
                <td style="font-size:0.82rem; color:var(--text-secondary);"><?= $lokasiLine ?></td>
EOD;

$oldColspan = '<td colspan="8" class="text-center py-5 text-muted">';
$newColspan = '<td colspan="9" class="text-center py-5 text-muted">';

$content = str_replace($oldHeaders, $newHeaders, $content);
$content = str_replace($oldFilters, $newFilters, $content);
$content = str_replace($oldDataRow1, $newDataRow1, $content);
$content = str_replace($oldDataRow2, $newDataRow2, $content);
$content = str_replace($oldDataTds, $newDataTds, $content);
$content = str_replace($oldColspan, $newColspan, $content);

file_put_contents($file, $content);
echo "Tabel berhasil dipecah menjadi Kategori dan No Mesin.\n";
