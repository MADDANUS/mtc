<?php
$db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', '');
$stmt = $db->prepare("UPDATE users SET role = REPLACE(role, 'leader_member', 'leader mtc') WHERE role LIKE '%leader_member%'");
$stmt->execute();
echo 'Rows updated: ' . $stmt->rowCount();
?>
