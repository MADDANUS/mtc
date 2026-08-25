<?php
$db = \Config\Database::connect();
$users = $db->table('users')->where('role', 'leader')->get()->getResultArray();
echo json_encode($users, JSON_PRETTY_PRINT);
