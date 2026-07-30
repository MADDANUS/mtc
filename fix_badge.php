<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/layout/header.php';
$content = file_get_contents($file);

$oldCode = <<<'EOD'
              } elseif ($role === 'sheadprd') {
                $__cnt = $__db->table('transaksi_check')->where('jenis_check', 'Overhaul')->where('status', 'Approved L1')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->whereIn('status', ['Pending','Approved L1'])->countAllResults();
              } elseif ($role === 'sheadmtc') {
                $__cnt = $__db->table('transaksi_check')->where('jenis_check', 'Overhaul')->where('status', 'Approved L2')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->whereIn('status', ['Approved L1','Approved L2'])->countAllResults();
              }
EOD;

$newCode = <<<'EOD'
              } elseif ($role === 'sheadprd') {
                $__cnt = $__db->table('transaksi_check')->whereIn('jenis_check', ['Overhaul', 'Preventive'])->where('status', 'Approved L1')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->where('status', 'Approved L1')->countAllResults();
              } elseif ($role === 'sheadmtc') {
                $__cnt = $__db->table('transaksi_check')->whereIn('jenis_check', ['Overhaul', 'Preventive'])->where('status', 'Approved L2')->countAllResults();
                $__cnt += $__db->table('approval_bulanan')->where('status', 'Approved L2')->countAllResults();
              }
EOD;

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Badge logic fixed.\n";
