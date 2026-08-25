function buildAbnormalExcelSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $reports, $itemLokasi, $kategoriFilter, $bulanFilter)
{
    $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(18);
    $sheet->getColumnDimension('C')->setWidth(18);
    $sheet->getColumnDimension('D')->setWidth(25);
    $sheet->getColumnDimension('E')->setWidth(12);
    $sheet->getColumnDimension('F')->setWidth(12);
    $sheet->getColumnDimension('G')->setWidth(15);
    $sheet->getColumnDimension('H')->setWidth(10);
    $sheet->getColumnDimension('I')->setWidth(12);
    $sheet->getColumnDimension('J')->setWidth(15);
    $sheet->getColumnDimension('K')->setWidth(15);
    $sheet->getColumnDimension('L')->setWidth(12);

    $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];

    // TITLE ROWS
    $sheet->mergeCells("A1:L1");
    $sheet->setCellValue('A1', "FORMULIR ABNORMAL REPORT CONDITION\nPREVENTIVE MAINTENANCE");
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setItalic(true);
    $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF7E600');
    $sheet->getRowDimension(1)->setRowHeight(40);

    // INFO ROW
    $bulanIndo = ['01' => 'JANUARI', '02' => 'FEBRUARI', '03' => 'MARET', '04' => 'APRIL', '05' => 'MEI', '06' => 'JUNI', '07' => 'JULI', '08' => 'AGUSTUS', '09' => 'SEPTEMBER', '10' => 'OKTOBER', '11' => 'NOVEMBER', '12' => 'DESEMBER'];
    $bulanVal = substr($bulanFilter, 5, 2);
    $bulanNama = $bulanIndo[$bulanVal] ?? '';

    $sheet->mergeCells("A2:G2");
    $sheet->setCellValue('A2', "AREA : " . strtoupper($itemLokasi) . " | JENIS PREVENTIVE : " . strtoupper($kategoriFilter) . " | BULAN " . $bulanNama);
    $sheet->getStyle('A2')->getFont()->setItalic(true);
    
    $sheet->mergeCells("H2:K2");
    $sheet->setCellValue('H2', "Rev.:0/2911/24");
    $sheet->getStyle('H2')->getAlignment()->setHorizontal('right');
    $sheet->getStyle('H2')->getFont()->setItalic(true);
    
    $sheet->setCellValue('L2', "FM-MTN-08");
    $sheet->getStyle('L2')->getAlignment()->setHorizontal('right');
    $sheet->getStyle('L2')->getFont()->setItalic(true);

    $sheet->getStyle('A2:L2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');

    // HEADERS
    $sheet->mergeCells("A3:A5"); $sheet->setCellValue('A3', 'NO');
    $sheet->mergeCells("B3:B5"); $sheet->setCellValue('B3', 'MESIN');
    $sheet->mergeCells("C3:C5"); $sheet->setCellValue('C3', 'POINT CHECK');
    $sheet->mergeCells("D3:D5"); $sheet->setCellValue('D3', 'ABNORMAL CONDITION');
    $sheet->mergeCells("E3:E5"); $sheet->setCellValue('E3', 'TYPE SPAREPART');
    
    $sheet->mergeCells("F3:G3"); $sheet->setCellValue('F3', 'PENGECEKAN');
    $sheet->mergeCells("F4:F5"); $sheet->setCellValue('F4', 'TANGGAL');
    $sheet->mergeCells("G4:G5"); $sheet->setCellValue('G4', 'PIC');

    $sheet->mergeCells("H3:K3"); $sheet->setCellValue('H3', 'RENCANA PERBAIKAN');
    $sheet->mergeCells("H4:I4"); $sheet->setCellValue('H4', 'PROGRES');
    $sheet->setCellValue('H5', 'STOCK');
    $sheet->setCellValue('I5', 'TANGGAL');
    
    $sheet->mergeCells("J4:J5"); $sheet->setCellValue('J4', 'ACTION');
    $sheet->mergeCells("K4:K5"); $sheet->setCellValue('K4', 'PIC');

    $sheet->mergeCells("L3:L5"); $sheet->setCellValue('L3', 'KETERANGAN');

    $sheet->getStyle('A3:L5')->getFont()->setBold(true);
    $sheet->getStyle('A3:L5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
    $sheet->getStyle('A3:L5')->getAlignment()->setHorizontal('center')->setVertical('center');

    $row = 6;
    if (empty($reports)) {
        $sheet->mergeCells("A{$row}:L{$row}");
        $sheet->setCellValue("A{$row}", 'Tidak ada temuan kondisi abnormal yang tercatat.');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center');
        $row++;
    } else {
        $no = 1;
        foreach ($reports as $r) {
            $sheet->setCellValue("A{$row}", $no++);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');

            $sheet->setCellValue("B{$row}", $r['no_mesin']);
            $sheet->getStyle("B{$row}")->getFont()->setBold(true);

            $pointCheckDisplay = $r['point_check'];
            if (!empty($r['bagian_check'])) {
                $parts = [$r['bagian_check']];
                if (!empty($r['sub_item_check'])) $parts[] = $r['sub_item_check'];
                $parts[] = $r['point_check'];
                $pointCheckDisplay = implode(' - ', $parts);
            }
            $sheet->setCellValue("C{$row}", $pointCheckDisplay);

            $abnormalText = $r['abnormal_condition'] ?? '';
            $sheet->setCellValue("D{$row}", $abnormalText);
            $sheet->getStyle("D{$row}")->getFont()->getColor()->setARGB('FFFF0000');
            $sheet->getStyle("D{$row}")->getFont()->setBold(true);

            $sheet->setCellValue("E{$row}", $r['type_sparepart'] ?? '-');
            
            $sheet->setCellValue("F{$row}", format_tanggal_indo($r['pengecekan_tanggal']));
            $sheet->setCellValue("G{$row}", $r['pengecekan_pic'] ?? '');
            
            $progres = $r['progres_stock'] ?? '';
            $sheet->setCellValue("H{$row}", $progres);
            if ($progres === 'Ready') $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB('FF008000');
            elseif ($progres === 'Indent') $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB('FFB8860B');
            elseif ($progres === 'Not Available') $sheet->getStyle("H{$row}")->getFont()->getColor()->setARGB('FFFF0000');
            
            $sheet->setCellValue("I{$row}", $r['progres_tanggal'] ? format_tanggal_indo($r['progres_tanggal']) : '-');
            $sheet->setCellValue("J{$row}", $r['action'] ?? '-');
            $sheet->setCellValue("K{$row}", $r['repair_pic'] ?? '-');
            $sheet->setCellValue("L{$row}", $r['keterangan'] ?? '-');

            $sheet->getStyle("B{$row}:L{$row}")->getAlignment()->setVertical('top')->setWrapText(true);

            // Images logic
            $hasImageD = false;
            if (!empty($r['foto_abnormal'])) {
                $imgPath = FCPATH . 'uploads/abnormal/' . $r['foto_abnormal'];
                if (file_exists($imgPath)) {
                    $sheet->setCellValue("D{$row}", $abnormalText . "\n\n\n\n\n");
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Foto Abnormal');
                    $drawing->setPath($imgPath);
                    $drawing->setCoordinates("D{$row}");
                    $drawing->setHeight(50);
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(20);
                    $drawing->setWorksheet($sheet);
                    $hasImageD = true;
                }
            }
            if (!empty($r['foto_abnormal_2'])) {
                $imgPath2 = FCPATH . 'uploads/abnormal/' . $r['foto_abnormal_2'];
                if (file_exists($imgPath2)) {
                    $sheet->setCellValue("D{$row}", $abnormalText . ($hasImageD ? "\n\n\n\n\n\n\n\n\n" : "\n\n\n\n\n"));
                    $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing2->setName('Foto Abnormal 2');
                    $drawing2->setPath($imgPath2);
                    $drawing2->setCoordinates("D{$row}");
                    $drawing2->setHeight(50);
                    $drawing2->setOffsetX(5);
                    $drawing2->setOffsetY($hasImageD ? 75 : 20);
                    $drawing2->setWorksheet($sheet);
                    $hasImageD = true;
                }
            }

            $hasImageJ = false;
            $actionText = $r['action'] ?? '-';
            if (!empty($r['foto_perbaikan'])) {
                $imgPath = FCPATH . 'uploads/abnormal/' . $r['foto_perbaikan'];
                if (file_exists($imgPath)) {
                    $sheet->setCellValue("J{$row}", $actionText . "\n\n\n\n\n");
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Foto Perbaikan');
                    $drawing->setPath($imgPath);
                    $drawing->setCoordinates("J{$row}");
                    $drawing->setHeight(50);
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(20);
                    $drawing->setWorksheet($sheet);
                    $hasImageJ = true;
                }
            }
            if (!empty($r['foto_perbaikan_2'])) {
                $imgPath2 = FCPATH . 'uploads/abnormal/' . $r['foto_perbaikan_2'];
                if (file_exists($imgPath2)) {
                    $sheet->setCellValue("J{$row}", $actionText . ($hasImageJ ? "\n\n\n\n\n\n\n\n\n" : "\n\n\n\n\n"));
                    $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing2->setName('Foto Perbaikan 2');
                    $drawing2->setPath($imgPath2);
                    $drawing2->setCoordinates("J{$row}");
                    $drawing2->setHeight(50);
                    $drawing2->setOffsetX(5);
                    $drawing2->setOffsetY($hasImageJ ? 75 : 20);
                    $drawing2->setWorksheet($sheet);
                    $hasImageJ = true;
                }
            }

            if ($hasImageD || $hasImageJ) {
                // If two images stacked, needs more height
                $twoImages = (!empty($r['foto_abnormal_2']) || !empty($r['foto_perbaikan_2']));
                $sheet->getRowDimension($row)->setRowHeight($twoImages ? 110 : 60);
            }

            $row++;
        }
    }

    $sheet->getStyle("A1:L" . ($row - 1))->applyFromArray($borderThin);
}
