<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/layout/header.php';
$content = file_get_contents($file);
$content = preg_replace('/\r\n|\r/', "\n", $content);

$insertCode = <<<'EOD'
      <?php $isFromApproval = (isset($_GET['from']) && $_GET['from'] === 'approval'); ?>
      <?php if (in_array($role, ['admin', 'member', 'leader', 'sheadprd', 'sheadmtc'], true)): ?>
        <a href="<?= site_url('approval') ?>" class="menu-item <?= ($seg1 === 'approval' || $isFromApproval) ? 'active' : '' ?>">
          <i class="bi bi-bell-fill"></i>Approval
          <?php
            // Hitung jumlah dokumen pending untuk badge
            try {
              $__db = \Config\Database::connect();
              $__cnt = 0;
              if ($role === 'leader') {
                $__line = session()->get('line');
                $__q = $__db->table('transaksi_check tc')
                  ->join('master_mesin mm', 'mm.id_mesin = tc.id_mesin', 'left')
                  ->where('tc.jenis_check', 'Overhaul')->where('tc.status', 'Pending');
                if ($__line) $__q->where('mm.line', $__line);
                $__cnt = $__q->countAllResults();
              } elseif ($role === 'sheadprd') {
                $__cnt = $__db->table('transaksi_check')->whereIn('jenis_check', ['Overhaul', 'Preventive'])->where('status', 'Approved L1')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->where('status', 'Approved L1')->countAllResults();
              } elseif ($role === 'sheadmtc') {
                $__cnt = $__db->table('transaksi_check')->whereIn('jenis_check', ['Overhaul', 'Preventive'])->where('status', 'Approved L2')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->where('status', 'Approved L2')->countAllResults();
              } elseif ($role === 'member') {
                $__cnt = $__db->table('transaksi_check')->where('jenis_check', 'Preventive')->where('status', 'Pending')->countAllResults();
              } elseif ($role === 'admin') {
                $__cnt = $__db->table('transaksi_check')->whereNotIn('status', ['Approved'])->countAllResults();
              }
              if ($__cnt > 0): ?>
                <span class="badge bg-danger ms-auto" style="font-size:0.65rem;"><?= $__cnt ?></span>
              <?php endif;
            } catch (\Throwable $e) { /* silent */ }
          ?>
        </a>
      <?php endif; ?>
EOD;

$target = "      <?php endif; ?>\n\n      <!-- HISTORY MENU (COLLAPSE) -->";
$replacement = "      <?php endif; ?>\n\n" . $insertCode . "\n\n      <!-- HISTORY MENU (COLLAPSE) -->";

if (strpos($content, $target) !== false) {
    $content = str_replace($target, $replacement, $content);
    file_put_contents($file, $content);
    echo "Approval menu successfully injected.\n";
} else {
    echo "Failed to find the target string.\n";
}
