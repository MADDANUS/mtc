<?php
// Mock CI4 environment just to get the DOMDocument error
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

$doc = new DOMDocument();
libxml_use_internal_errors(true);
$doc->loadHTML($html);
$errors = libxml_get_errors();
foreach ($errors as $error) {
    echo $error->message . "\n";
}
libxml_clear_errors();
?>
