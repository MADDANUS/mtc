<?php
$host = '127.0.0.1';
$db   = 'mtce_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    $tables = [
        'master_parameter' => 'kategori',
        'ceklis_preventive' => 'kategori',
        'jadwal_preventive' => 'kategori',
        'abnormalitas' => 'kategori',
        'approval_bulanan' => 'kategori'
    ];

    foreach ($tables as $table => $column) {
        try {
            $stmt = $pdo->prepare("UPDATE $table SET $column = 'Gearbox Cam' WHERE $column = 'Gearbox'");
            $stmt->execute();
            echo "Updated $table: " . $stmt->rowCount() . " rows affected.\n";
        } catch (\PDOException $e) {
            echo "Could not update $table: " . $e->getMessage() . "\n";
        }
    }

} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
