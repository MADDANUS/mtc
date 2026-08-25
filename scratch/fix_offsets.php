<?php
$f = 'app/Controllers/RiwayatController.php';
$c = file_get_contents($f);

// 1. Fix Logo Offset
// Original: $drawing->setOffsetX(10); $drawing->setOffsetY(10);
// Let's replace the first occurrence (Logo) with OffsetX(30)
$c = preg_replace('/->setCoordinates\(\'B2\'\);\s*\$drawing->setHeight\(100\);\s*\$drawing->setOffsetX\(10\);\s*\$drawing->setOffsetY\(10\);/', 
                  "->setCoordinates('B2');\n            \$drawing->setHeight(95);\n            \$drawing->setOffsetX(30);\n            \$drawing->setOffsetY(5);", 
                  $c);

// 2. Fix Abnormal Photo Offset and Cell Alignment
// Replace ->setOffsetY(5) with ->setOffsetY(30) for photos
// But only for the abnormal ones. Let's just do a generic replace for all setOffsetY(5) except we just did the logo
$c = str_replace('setOffsetY(5)', 'setOffsetY(30)', $c);

// 3. Fix Row Height and Vertical Alignment
// Original: $sheet->getRowDimension($row)->setRowHeight(60);
// New: $sheet->getRowDimension($row)->setRowHeight(85);
$c = str_replace('setRowHeight(60)', 'setRowHeight(85)', $c);

// Original: $sheet->getStyle("B{$row}:AF{$row}")->getAlignment()->setVertical('center')->setWrapText(true);
// New: $sheet->getStyle("B{$row}:AF{$row}")->getAlignment()->setVertical('top')->setWrapText(true);
$c = str_replace("setVertical('center')->setWrapText(true)", "setVertical('top')->setWrapText(true)", $c);

file_put_contents($f, $c);
echo "Adjusted Logo Offset and Abnormal Photo layout!";
