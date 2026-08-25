<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=mtce_db', 'root', '');
$stmt = $pdo->query('SELECT id, nama, plant, departemen, `line` FROM users WHERE id = 25');
$row = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump($row);
