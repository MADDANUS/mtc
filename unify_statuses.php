<?php
// 1. Update KontrolController labels
$file = 'c:/xampp/htdocs/mtce/app/Controllers/KontrolController.php';
$content = file_get_contents($file);

$oldLogic = <<<'EOD'
                    if ($percent == 100) {
                        if (empty($status) || $status === 'Pending') {
                            $badgeClass = 'bg-warning text-dark';
                            $statusText = 'Menunggu Approval (L1)';
                        } elseif ($status === 'Approved L1') {
                            $badgeClass = 'bg-info text-dark';
                            $statusText = 'Approved L1 (Menunggu L2)';
                        } elseif ($status === 'Approved L2') {
                            $badgeClass = 'bg-primary';
                            $statusText = 'Approved L2 (Menunggu Final)';
                        } elseif ($status === 'Final' || $status === 'Approved Final') {
                            $badgeClass = 'bg-success';
                            $statusText = 'Selesai (Final)';
                        }
                    } else {
                        $badgeClass = 'bg-secondary';
                        $statusText = 'Belum Selesai';
                    }
EOD;

$newLogic = <<<'EOD'
                    if ($percent == 100) {
                        if (empty($status) || $status === 'Pending') {
                            $badgeClass = 'bg-warning text-dark';
                            $statusText = 'Siap Disubmit (100%)';
                        } elseif ($status === 'Approved L1') {
                            $badgeClass = 'bg-info text-dark';
                            $statusText = 'Menunggu SHead 1';
                        } elseif ($status === 'Approved L2') {
                            $badgeClass = 'bg-primary';
                            $statusText = 'Menunggu SHead 2';
                        } elseif ($status === 'Final' || $status === 'Approved Final') {
                            $badgeClass = 'bg-success';
                            $statusText = 'Selesai (Final)';
                        }
                    } else {
                        $badgeClass = 'bg-secondary';
                        $statusText = 'Belum Selesai (' . $percent . '%)';
                    }
EOD;

$content = str_replace($oldLogic, $newLogic, $content);
file_put_contents($file, $content);
echo "Updated KontrolController.\n";

// 2. Update RiwayatController
$file = 'c:/xampp/htdocs/mtce/app/Controllers/RiwayatController.php';
$content = file_get_contents($file);

$oldPreventive = <<<'EOD'
        if (strtolower($header['jenis_check']) === 'preventive' || strtolower($header['jenis_check']) === 'checklist report') {
            if ($currentStatus !== 'Pending') {
                return redirect()->back()->with('error', 'Laporan sudah disetujui sebelumnya.');
            }
            $picLineNama = $this->request->getPost('pic_line_nama');
            if (empty(trim($picLineNama))) {
                return redirect()->back()->with('error', 'Nama PIC Line wajib diisi.');
            }

            $newStatus = 'Approved';
            $updateData = [
                'status' => 'Approved',
                'approved_by' => $userId,
                'pic_line_nama' => trim($picLineNama),
                'approved_at' => $now,
            ];
        }
EOD;

$newPreventive = <<<'EOD'
        if (strtolower($header['jenis_check']) === 'preventive' || strtolower($header['jenis_check']) === 'checklist report') {
            if (in_array($role, ['member', 'admin'])) {
                if ($currentStatus !== 'Pending') {
                    return redirect()->back()->with('error', 'Laporan sudah disetujui sebelumnya.');
                }
                $picLineNama = $this->request->getPost('pic_line_nama');
                if (empty(trim($picLineNama))) {
                    return redirect()->back()->with('error', 'Nama PIC Line wajib diisi.');
                }
                $newStatus = 'Approved L1';
                $updateData = [
                    'status' => 'Approved L1',
                    'approval_l1_by' => $userId,
                    'pic_line_nama' => trim($picLineNama),
                    'approval_l1_at' => $now,
                ];
            } elseif ($role === 'sheadprd') {
                if ($transaksi['status'] !== 'Approved L1') {
                    return redirect()->back()->with('error', 'Laporan belum disetujui oleh Member.');
                }
                $newStatus = 'Approved L2';
                $updateData = [
                    'status' => 'Approved L2',
                    'approval_l2_by' => $userId,
                    'approval_l2_at' => $now,
                ];
            } elseif ($role === 'sheadmtc') {
                if ($transaksi['status'] !== 'Approved L2') {
                    return redirect()->back()->with('error', 'Laporan belum disetujui oleh S. Head Produksi.');
                }
                $newStatus = 'Approved';
                $updateData = [
                    'status' => 'Approved',
                    'approved_by' => $userId,
                    'approved_at' => $now,
                ];
            } else {
                return redirect()->back()->with('error', 'Role Anda tidak memiliki akses persetujuan.');
            }
        }
EOD;

$content = str_replace($oldPreventive, $newPreventive, $content);
file_put_contents($file, $content);
echo "Updated RiwayatController.\n";

// 3. Update ApprovalController to include Preventive for SHead 1 & 2
$file = 'c:/xampp/htdocs/mtce/app/Controllers/ApprovalController.php';
$content = file_get_contents($file);

$content = str_replace(
    '$txBuilder->where(\'tc.jenis_check\', \'Overhaul\')
                      ->where(\'tc.status\', \'Approved L1\');',
    '$txBuilder->whereIn(\'tc.jenis_check\', [\'Overhaul\', \'Preventive\'])
                      ->where(\'tc.status\', \'Approved L1\');',
    $content
);

$content = str_replace(
    '$txBuilder->where(\'tc.jenis_check\', \'Overhaul\')
                      ->where(\'tc.status\', \'Approved L2\');',
    '$txBuilder->whereIn(\'tc.jenis_check\', [\'Overhaul\', \'Preventive\'])
                      ->where(\'tc.status\', \'Approved L2\');',
    $content
);

file_put_contents($file, $content);
echo "Updated ApprovalController.\n";

// 4. Update approval/index.php labels
$file = 'c:/xampp/htdocs/mtce/app/Views/approval/index.php';
$content = file_get_contents($file);

$oldBadgeLogic = <<<'EOD'
                $status = $doc['status'] ?? 'Pending';
                if ($status === 'Approved' || $status === 'Approved Final') {
                    $statusBadge = '<span class="badge bg-success">Approved</span>';
                } elseif ($status === 'Approved L1') {
                    $statusBadge = '<span class="badge bg-info text-dark">Approved L1</span>';
                } elseif ($status === 'Approved L2') {
                    $statusBadge = '<span class="badge bg-primary">Approved L2</span>';
                } elseif ($status === 'Belum Selesai') {
                    $persen = $doc['persen'] ?? 0;
                    $statusBadge = '<span class="badge bg-secondary">Belum Selesai</span>'
                                 . ' <small class="text-muted" style="font-size:0.75rem;">' . $persen . '%</small>';
                } else {
                    $statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
                }
EOD;

$newBadgeLogic = <<<'EOD'
                $status = $doc['status'] ?? 'Pending';
                if ($status === 'Approved' || $status === 'Approved Final' || $status === 'Final') {
                    $statusBadge = '<span class="badge bg-success">Selesai (Final)</span>';
                } elseif ($status === 'Approved L1') {
                    $statusBadge = '<span class="badge bg-info text-dark">Menunggu SHead 1</span>';
                } elseif ($status === 'Approved L2') {
                    $statusBadge = '<span class="badge bg-primary">Menunggu SHead 2</span>';
                } elseif ($status === 'Belum Selesai') {
                    $persen = $doc['persen'] ?? 0;
                    if ($persen == 100) {
                        $statusBadge = '<span class="badge bg-warning text-dark">Siap Disubmit (100%)</span>';
                    } else {
                        $statusBadge = '<span class="badge bg-secondary">Belum Selesai (' . $persen . '%)</span>';
                    }
                } else {
                    if (($doc['jenis_check'] ?? '') === 'Overhaul') {
                        $statusBadge = '<span class="badge bg-warning text-dark">Menunggu Leader</span>';
                    } else {
                        $statusBadge = '<span class="badge bg-warning text-dark">Menunggu Member</span>';
                    }
                }
EOD;

$content = str_replace($oldBadgeLogic, $newBadgeLogic, $content);
file_put_contents($file, $content);
echo "Updated approval/index.php.\n";

// 5. Update dashboard/leader.php labels
$file = 'c:/xampp/htdocs/mtce/app/Views/dashboard/leader.php';
$content = file_get_contents($file);

$oldDashBadge1 = <<<'EOD'
              <?php if ($st === 'Pending'): ?>
                <span class="badge bg-warning text-dark">Pending (Menunggu Approval)</span>
              <?php elseif ($st === 'Approved L1'): ?>
                <span class="badge bg-info text-dark">Approved L1 (Menunggu L2)</span>
              <?php elseif ($st === 'Approved L2'): ?>
                <span class="badge bg-primary">Approved L2 (Menunggu Final)</span>
              <?php else: ?>
                <span class="badge bg-success"><?= esc($st) ?></span>
              <?php endif; ?>
EOD;

$newDashBadge1 = <<<'EOD'
              <?php if ($st === 'Pending'): ?>
                <span class="badge bg-warning text-dark">
                  <?= (isset($pk) ? 'Siap Disubmit (100%)' : ($po['jenis_check'] === 'Overhaul' ? 'Menunggu Leader' : 'Menunggu Member')) ?>
                </span>
              <?php elseif ($st === 'Approved L1'): ?>
                <span class="badge bg-info text-dark">Menunggu SHead 1</span>
              <?php elseif ($st === 'Approved L2'): ?>
                <span class="badge bg-primary">Menunggu SHead 2</span>
              <?php elseif ($st === 'Belum Selesai'): ?>
                <span class="badge bg-secondary">Belum Selesai (<?= esc($pk['persen'] ?? '0') ?>%)</span>
              <?php else: ?>
                <span class="badge bg-success">Selesai (Final)</span>
              <?php endif; ?>
EOD;

// Because leader.php might have this badge structure in multiple places (for Overhaul and Ceklis Control), we replace them all.
// Actually, they might be slightly different. Let's just use regex or replace the blocks exactly.
$content = str_replace($oldDashBadge1, $newDashBadge1, $content);

// In leader.php for Overhaul:
$oldOverhaulBadge = <<<'EOD'
              <?php if ($st === 'Pending'): ?>
                <span class="badge bg-warning text-dark">Pending (Menunggu Approval)</span>
              <?php elseif ($st === 'Approved L1'): ?>
                <span class="badge bg-info text-dark">Approved L1 (Menunggu L2)</span>
              <?php elseif ($st === 'Approved L2'): ?>
                <span class="badge bg-primary">Approved L2 (Menunggu Final)</span>
              <?php else: ?>
                <span class="badge bg-success">Approved Final</span>
              <?php endif; ?>
EOD;

$content = str_replace($oldOverhaulBadge, $newDashBadge1, $content);

file_put_contents($file, $content);
echo "Updated dashboard/leader.php.\n";

// 6. Update riwayat/index.php labels
$file = 'c:/xampp/htdocs/mtce/app/Views/riwayat/index.php';
$content = file_get_contents($file);

$oldRiwayatStatus = <<<'EOD'
              <?php if ($status === 'Pending'): ?>
                <span class="badge bg-warning text-dark">Pending</span>
              <?php elseif ($status === 'Approved L1'): ?>
                <span class="badge bg-info text-dark">Approved L1</span>
              <?php elseif ($status === 'Approved L2'): ?>
                <span class="badge bg-primary">Approved L2</span>
              <?php else: ?>
                <span class="badge bg-success">Approved Final</span>
              <?php endif; ?>
EOD;

$newRiwayatStatus = <<<'EOD'
              <?php if ($status === 'Pending'): ?>
                <span class="badge bg-warning text-dark"><?= ($row['jenis_check'] === 'Overhaul' ? 'Menunggu Leader' : 'Menunggu Member') ?></span>
              <?php elseif ($status === 'Approved L1'): ?>
                <span class="badge bg-info text-dark">Menunggu SHead 1</span>
              <?php elseif ($status === 'Approved L2'): ?>
                <span class="badge bg-primary">Menunggu SHead 2</span>
              <?php else: ?>
                <span class="badge bg-success">Selesai (Final)</span>
              <?php endif; ?>
EOD;

$content = str_replace($oldRiwayatStatus, $newRiwayatStatus, $content);

// Also update filter dropdown in riwayat/index.php
$oldFilter = <<<'EOD'
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="Pending" <?= $filters['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Approved L1" <?= $filters['status'] === 'Approved L1' ? 'selected' : '' ?>>Approved L1</option>
            <option value="Approved L2" <?= $filters['status'] === 'Approved L2' ? 'selected' : '' ?>>Approved L2</option>
            <option value="Approved" <?= in_array($filters['status'], ['Approved', 'Approved Final']) ? 'selected' : '' ?>>Approved Final</option>
          </select>
EOD;

$newFilter = <<<'EOD'
          <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="Pending" <?= $filters['status'] === 'Pending' ? 'selected' : '' ?>>Menunggu PIC (Leader/Member)</option>
            <option value="Approved L1" <?= $filters['status'] === 'Approved L1' ? 'selected' : '' ?>>Menunggu SHead 1</option>
            <option value="Approved L2" <?= $filters['status'] === 'Approved L2' ? 'selected' : '' ?>>Menunggu SHead 2</option>
            <option value="Approved" <?= in_array($filters['status'], ['Approved', 'Approved Final']) ? 'selected' : '' ?>>Selesai (Final)</option>
          </select>
EOD;

$content = str_replace($oldFilter, $newFilter, $content);

file_put_contents($file, $content);
echo "Updated riwayat/index.php.\n";

