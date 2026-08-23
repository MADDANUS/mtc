<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Fetch all approved transactions that need backfill
$stmt = $db->query("SELECT * FROM transaksi_check WHERE ss_approved_name IS NULL OR ss_approval_l1_name IS NULL OR ss_approval_l2_name IS NULL");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updatedCount = 0;

foreach ($transactions as $t) {
    $updateFields = [];
    $updateValues = [];

    // Check final approver
    if (!empty($t['approved_by']) && empty($t['ss_approved_name'])) {
        // Find name in users or log_hapus_user
        $stmtUser = $db->prepare("SELECT nama FROM users WHERE id = ?");
        $stmtUser->execute([$t['approved_by']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        $name = '';
        if ($user) {
            $name = $user['nama'];
        } else {
            $stmtLog = $db->prepare("SELECT nama FROM log_hapus_user WHERE id_user = ?");
            $stmtLog->execute([$t['approved_by']]);
            $log = $stmtLog->fetch(PDO::FETCH_ASSOC);
            if ($log) $name = $log['nama'];
        }

        if ($name) {
            $updateFields[] = "ss_approved_name = ?";
            $updateValues[] = $name;
        }
    }

    // Check L1 approver
    if (!empty($t['approval_l1_by']) && empty($t['ss_approval_l1_name'])) {
        $stmtUser = $db->prepare("SELECT nama FROM users WHERE id = ?");
        $stmtUser->execute([$t['approval_l1_by']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        $name = '';
        if ($user) {
            $name = $user['nama'];
        } else {
            $stmtLog = $db->prepare("SELECT nama FROM log_hapus_user WHERE id_user = ?");
            $stmtLog->execute([$t['approval_l1_by']]);
            $log = $stmtLog->fetch(PDO::FETCH_ASSOC);
            if ($log) $name = $log['nama'];
        }

        if ($name) {
            $updateFields[] = "ss_approval_l1_name = ?";
            $updateValues[] = $name;
        }
    }

    // Check L2 approver
    if (!empty($t['approval_l2_by']) && empty($t['ss_approval_l2_name'])) {
        $stmtUser = $db->prepare("SELECT nama FROM users WHERE id = ?");
        $stmtUser->execute([$t['approval_l2_by']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        $name = '';
        if ($user) {
            $name = $user['nama'];
        } else {
            $stmtLog = $db->prepare("SELECT nama FROM log_hapus_user WHERE id_user = ?");
            $stmtLog->execute([$t['approval_l2_by']]);
            $log = $stmtLog->fetch(PDO::FETCH_ASSOC);
            if ($log) $name = $log['nama'];
        }

        if ($name) {
            $updateFields[] = "ss_approval_l2_name = ?";
            $updateValues[] = $name;
        }
    }

    if (count($updateFields) > 0) {
        $sql = "UPDATE transaksi_check SET " . implode(', ', $updateFields) . " WHERE id_transaksi = ?";
        $updateValues[] = $t['id_transaksi'];
        $stmtUpdate = $db->prepare($sql);
        $stmtUpdate->execute($updateValues);
        $updatedCount++;
    }
}

echo "Successfully backfilled snapshot names for $updatedCount transactions.\n";
