<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/layout/header.php';
$content = file_get_contents($file);

$oldLogic = <<<'EOD'
              } elseif ($role === 'sheadprd') {
                $__cnt = $__db->table('transaksi_check')->where('jenis_check', 'Overhaul')->where('status', 'Approved L1')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->whereIn('status', ['Pending','Approved L1'])->countAllResults();
              } elseif ($role === 'sheadmtc') {
                $__cnt = $__db->table('transaksi_check')->where('jenis_check', 'Overhaul')->where('status', 'Approved L2')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->whereIn('status', ['Approved L1','Approved L2'])->countAllResults();
              }
EOD;

// Fix normalization for cross-platform newlines
$oldLogicNormalized = preg_replace('/\r\n|\r|\n/', "\n", $oldLogic);
$contentNormalized = preg_replace('/\r\n|\r|\n/', "\n", $content);

$newLogic = <<<'EOD'
              } elseif ($role === 'sheadprd') {
                $__cnt = $__db->table('transaksi_check')->whereIn('jenis_check', ['Overhaul', 'Preventive'])->where('status', 'Approved L1')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->where('status', 'Approved L1')->countAllResults();
              } elseif ($role === 'sheadmtc') {
                $__cnt = $__db->table('transaksi_check')->whereIn('jenis_check', ['Overhaul', 'Preventive'])->where('status', 'Approved L2')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->where('status', 'Approved L2')->countAllResults();
              }
EOD;

if (strpos($contentNormalized, $oldLogicNormalized) !== false) {
    $content = str_replace($oldLogicNormalized, $newLogic, $contentNormalized);
    file_put_contents($file, $content);
    echo "Success: Badge logic updated safely.\n";
} else {
    echo "Error: Old logic not found in the file.\n";
}
