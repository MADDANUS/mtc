<?php $db = new PDO('mysql:host=localhost;dbname=mtce_db', 'root', ''); $stmt = $db->query('SELECT DISTINCT plant FROM jadwal_preventive'); print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
