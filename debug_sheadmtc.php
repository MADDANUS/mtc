<?php
$db = new mysqli('localhost', 'root', '', 'mtce_db');

$res1 = $db->query("SELECT * FROM transaksi_check WHERE jenis_check IN ('Overhaul', 'Preventive') AND status = 'Approved L2'");
echo "transaksi_check: " . $res1->num_rows . "\n";
if ($res1->num_rows > 0) {
    while($row = $res1->fetch_assoc()) {
        print_r($row);
    }
}

$res2 = $db->query("SELECT * FROM approval_bulanan WHERE status = 'Approved L2'");
echo "approval_bulanan: " . $res2->num_rows . "\n";
if ($res2->num_rows > 0) {
    while($row = $res2->fetch_assoc()) {
        print_r($row);
    }
}
