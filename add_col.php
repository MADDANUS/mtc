<?php
$conn = new mysqli('localhost', 'root', '', 'mtce_db');
if ($conn->connect_error) die('Connection failed: ' . $conn->connect_error);
if (!$conn->query('ALTER TABLE master_mesin ADD COLUMN sn_barfeeder VARCHAR(100) NULL AFTER bar_feeder_type')) {
    echo 'Error: ' . $conn->error;
} else {
    echo 'Added column sn_barfeeder';
}
