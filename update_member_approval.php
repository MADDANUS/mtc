<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/ApprovalController.php';
$content = file_get_contents($file);

$oldMemberTx = <<<'EOD'
        } elseif ($role === 'member') {
            $txBuilder->where('tc.jenis_check', 'Preventive')
                      ->where('tc.status', 'Pending');
        }
EOD;

$newMemberTx = <<<'EOD'
        } elseif ($role === 'member') {
            $txBuilder->groupStart()
                        ->groupStart()
                            ->where('tc.jenis_check', 'Preventive')
                            ->where('tc.status', 'Pending')
                        ->groupEnd()
                        ->orGroupStart()
                            ->where('tc.jenis_check', 'Overhaul')
                            ->whereIn('tc.status', ['Pending', 'Approved L1', 'Approved L2'])
                        ->groupEnd()
                      ->groupEnd();
        }
EOD;

$content = str_replace($oldMemberTx, $newMemberTx, $content);
file_put_contents($file, $content);
echo "Updated ApprovalController for member.\n";
