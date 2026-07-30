<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/approval/index.php';
$content = file_get_contents($file);
$content = preg_replace('/\r\n|\r/', "\n", $content);

$oldSearch = <<<'EOD'
            <!-- Filter Keterangan (Search) -->
            <th class="p-1" style="min-width:180px;">
              <input type="text" name="search" class="form-control form-control-sm fw-bold border-1" value="<?= esc($filterSearch ?? '') ?>" placeholder="Cari mesin, kategori..." style="font-size:0.75rem;" onchange="this.form.submit()">
            </th>
            <th class="p-1"></th>
EOD;
$oldSearch = preg_replace('/\r\n|\r/', "\n", $oldSearch);

$newSearch = <<<'EOD'
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
            <!-- Filter Lokasi -->
            <th class="p-1" style="min-width:130px;">
              <select name="lokasi" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Lokasi..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterLokasi === 'all') ? 'selected' : '' ?>>Semua Lokasi</option>
                <?php foreach ($uniqueLokasi as $loc): ?>
                  <option value="<?= esc($loc) ?>" <?= ($filterLokasi === $loc) ? 'selected' : '' ?>><?= esc($loc) ?></option>
                <?php endforeach; ?>
              </select>
            </th>
EOD;

$oldStatus = <<<'EOD'
            <!-- Filter Status -->
            <th class="p-1" style="min-width:130px;">
              <select name="status" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Status..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterStatus === 'all') ? 'selected' : '' ?>>Semua Status</option>
                <option value="Pending" <?= ($filterStatus === 'Pending') ? 'selected' : '' ?>>Pending</option>
                <option value="Approved L1" <?= ($filterStatus === 'Approved L1') ? 'selected' : '' ?>>Approved L1</option>
                <option value="Approved L2" <?= ($filterStatus === 'Approved L2') ? 'selected' : '' ?>>Approved L2</option>
              </select>
            </th>
EOD;
$oldStatus = preg_replace('/\r\n|\r/', "\n", $oldStatus);

$newStatus = <<<'EOD'
            <!-- Filter Status -->
            <th class="p-1" style="min-width:140px;">
              <select name="status" class="form-select form-select-sm fw-bold border-1 bg-white searchable-select" data-placeholder="Cari Status..." onchange="this.form.submit()" style="font-size:0.75rem;">
                <option value=""></option>
                <option value="all" <?= ($filterStatus === 'all') ? 'selected' : '' ?>>Semua Status</option>
                <option value="Pending_Overhaul" <?= ($filterStatus === 'Pending_Overhaul') ? 'selected' : '' ?>>Menunggu Leader</option>
                <option value="Pending_Preventive" <?= ($filterStatus === 'Pending_Preventive') ? 'selected' : '' ?>>Menunggu Member</option>
                <option value="Approved L1" <?= ($filterStatus === 'Approved L1') ? 'selected' : '' ?>>Menunggu SHead 1</option>
                <option value="Approved L2" <?= ($filterStatus === 'Approved L2') ? 'selected' : '' ?>>Menunggu SHead 2</option>
              </select>
            </th>
EOD;

$content = str_replace($oldSearch, $newSearch, $content);
$content = str_replace($oldStatus, $newStatus, $content);

file_put_contents($file, $content);
echo "View index.php updated successfully.\n";
