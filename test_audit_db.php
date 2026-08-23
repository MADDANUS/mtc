<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');

echo "1. Checking LogAuditLaporan table:\n";
$stmt = $db->query("SHOW COLUMNS FROM log_audit_laporan");
$columns = [];
foreach ($stmt as $row) {
    $columns[] = $row['Field'];
}
echo "   Columns: " . implode(', ', $columns) . "\n";

echo "2. Simulating a quick insert using basic SQL (since CI Model is structurally verified via PHP -l):\n";
$insert = $db->prepare("INSERT INTO log_audit_laporan (kategori_dokumen, aksi, no_mesin, waktu_eksekusi, dieksekusi_oleh, alasan, detail_perubahan) VALUES (?, ?, ?, ?, ?, ?, ?)");
$insert->execute(['Test Doc', 'Edit', 'TEST-01', date('Y-m-d H:i:s'), 'Test Admin', 'Alasan testing bug', '{"old": "1", "new": "2"}']);
echo "   Insert Status: " . ($insert->rowCount() > 0 ? "Success" : "Failed") . "\n";

echo "3. Querying inserted data:\n";
$stmt = $db->query("SELECT * FROM log_audit_laporan ORDER BY id_log DESC LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "   Fetched ID: " . $row['id_log'] . " | Alasan: " . $row['alasan'] . "\n";

echo "4. Cleaning up test data...\n";
$db->query("DELETE FROM log_audit_laporan WHERE no_mesin = 'TEST-01'");
echo "   Cleanup complete.\n";
echo "DONE.";
?>
