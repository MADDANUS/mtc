<?php
// 1. Update KontrolController (Ceklis Control Summary)
$fileKontrol = 'c:/xampp/htdocs/mtce/app/Controllers/KontrolController.php';
$contentKontrol = file_get_contents($fileKontrol);

$contentKontrol = str_replace(
    'if ($roleSession === \'sheadprd\' && (empty($status) || $status === \'Pending\')) {',
    'if ($roleSession === \'sheadprd\' && (empty($status) || in_array($status, [\'Pending\', \'Approved L1\'], true))) {',
    $contentKontrol
);

$contentKontrol = str_replace(
    'if ($roleSession === \'sheadmtc\' && (empty($status) || in_array($status, [\'Pending\', \'Approved L1\'], true))) {',
    'if ($roleSession === \'sheadmtc\' && (empty($status) || in_array($status, [\'Pending\', \'Approved L1\', \'Approved L2\'], true))) {',
    $contentKontrol
);

file_put_contents($fileKontrol, $contentKontrol);


// 2. Update ApprovalController (Approval Inbox for Ceklis Control)
$fileApproval = 'c:/xampp/htdocs/mtce/app/Controllers/ApprovalController.php';
$contentApproval = file_get_contents($fileApproval);

$contentApproval = str_replace(
    '$kontrolBuilder->whereIn(\'ab.status\', [\'Pending\', \'Approved L1\']);',
    '$kontrolBuilder->where(\'ab.status\', \'Approved L1\');',
    $contentApproval
);

$contentApproval = str_replace(
    '$kontrolBuilder->whereIn(\'ab.status\', [\'Approved L1\', \'Approved L2\']);',
    '$kontrolBuilder->where(\'ab.status\', \'Approved L2\');',
    $contentApproval
);

file_put_contents($fileApproval, $contentApproval);


// 3. Update RiwayatController (History logic for Inspection Report)
$fileRiwayat = 'c:/xampp/htdocs/mtce/app/Controllers/RiwayatController.php';
$contentRiwayat = file_get_contents($fileRiwayat);

$riwayatLogic = <<<'EOD'
        $rawStatus = $this->request->getGet('status');
        if ($rawStatus === 'all') {
            $statusFilter = null;
        } elseif ($rawStatus && $rawStatus !== 'all') {
            $statusFilter = $rawStatus;
        } else {
            // Default history visibility based on role
            $role = session()->get('role');
            if ($role === 'leader') {
                $statusFilter = ['Approved L1', 'Approved L2', 'Approved'];
            } elseif ($role === 'sheadprd') {
                $statusFilter = ['Approved L2', 'Approved'];
            } elseif ($role === 'sheadmtc') {
                $statusFilter = 'Approved';
            } else {
                $statusFilter = 'Approved';
            }
        }
EOD;

$contentRiwayat = preg_replace(
    '/\$rawStatus = \$this->request->getGet\(\'status\'\);.*?\$statusFilter = \'Approved\';\s*\}/s',
    $riwayatLogic,
    $contentRiwayat
);
file_put_contents($fileRiwayat, $contentRiwayat);


// 4. Update TransaksiCheckModel (Handle array in status filter)
$fileModel = 'c:/xampp/htdocs/mtce/app/Models/TransaksiCheckModel.php';
$contentModel = file_get_contents($fileModel);

// Remove the old Role-based Visibility Logic since we pass it via $filters['status'] now
$contentModel = preg_replace(
    '/\/\/ --- Role-based Visibility Logic ---.*?(?=if \(!empty\(\$filters\[\'lokasi\'\]\))/s',
    '',
    $contentModel
);

// Update status filter to handle array
$statusFilterUpdate = <<<'EOD'
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $builder->whereIn('transaksi_check.status', $filters['status']);
            } else {
                $builder->where('transaksi_check.status', $filters['status']);
            }
        }
EOD;

$contentModel = preg_replace(
    '/if \(!empty\(\$filters\[\'status\'\]\)\) \{\s*\$builder->where\(\'transaksi_check\.status\', \$filters\[\'status\'\]\);\s*\}/',
    $statusFilterUpdate,
    $contentModel
);
file_put_contents($fileModel, $contentModel);

echo "Sequential approval logic implemented successfully.\n";
