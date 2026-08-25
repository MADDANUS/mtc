<?php
require 'vendor/autoload.php';

$html = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Test</title>
</head>
<body>
<div class="pdf-container">
  <table><tr><td>Test & Test</td></tr></table>
</div>
</body>
</html>';

$htmlFixed = preg_replace('/&(?![A-Za-z0-9#]+;)/', '&amp;', $html);

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
try {
    $spreadsheet = $reader->loadFromString($htmlFixed);
    echo "Success loading fixed HTML\n";
} catch (Exception $e) {
    echo "Failed fixed: " . $e->getMessage() . "\n";
}
?>
