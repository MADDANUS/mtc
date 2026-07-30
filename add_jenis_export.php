<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/Admin/MesinController.php';
$content = file_get_contents($file);
$content = preg_replace('/\r\n|\r/', "\n", $content);

$oldData = <<<'EOD'
        // Header
        $sheet->setCellValue('A1', 'No Mesin');
        $sheet->setCellValue('B1', 'Type Mesin');
        $sheet->setCellValue('C1', 'Serial Nomor');
        $sheet->setCellValue('D1', 'Lokasi');
        $sheet->setCellValue('E1', 'Line');
        $sheet->setCellValue('F1', 'Bar Feeder Type');
        
        // Data
        $row = 2;
        foreach ($mesin as $m) {
            $sheet->setCellValue('A' . $row, $m['no_mesin']);
            $sheet->setCellValue('B' . $row, $m['type_mesin']);
            $sheet->setCellValue('C' . $row, $m['serial_nomor']);
            $sheet->setCellValue('D' . $row, $m['lokasi']);
            $sheet->setCellValue('E' . $row, $m['line']);
            $sheet->setCellValue('F' . $row, $m['bar_feeder_type']);
            $row++;
        }
EOD;
$oldData = preg_replace('/\r\n|\r/', "\n", $oldData);

$newData = <<<'EOD'
        // Header
        $sheet->setCellValue('A1', 'No Mesin');
        $sheet->setCellValue('B1', 'Type Mesin');
        $sheet->setCellValue('C1', 'Serial Nomor');
        $sheet->setCellValue('D1', 'Lokasi');
        $sheet->setCellValue('E1', 'Line');
        $sheet->setCellValue('F1', 'Bar Feeder Type');
        $sheet->setCellValue('G1', 'Jenis');
        
        // Data
        $row = 2;
        foreach ($mesin as $m) {
            $sheet->setCellValue('A' . $row, $m['no_mesin']);
            $sheet->setCellValue('B' . $row, $m['type_mesin']);
            $sheet->setCellValue('C' . $row, $m['serial_nomor']);
            $sheet->setCellValue('D' . $row, $m['lokasi']);
            $sheet->setCellValue('E' . $row, $m['line']);
            $sheet->setCellValue('F' . $row, $m['bar_feeder_type']);
            $sheet->setCellValue('G' . $row, isset($m['jenis']) ? $m['jenis'] : '');
            $row++;
        }
EOD;

if (strpos($content, $oldData) !== false) {
    $content = str_replace($oldData, $newData, $content);
    file_put_contents($file, $content);
    echo "Export updated with Jenis.\n";
} else {
    echo "Could not find the block to replace.\n";
}
