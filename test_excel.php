<?php
require 'vendor/autoload.php';

$html = '
<table border="1">
    <tr><th style="background-color: #92b0d6; text-align: center;" colspan="2">Test Excel Export</th></tr>
    <tr><td>Item 1</td><td>Value 1</td></tr>
</table>';

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
$spreadsheet = $reader->loadFromString($html);
$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('test_excel.xlsx');
echo "Success";
?>
