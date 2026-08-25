<?php

function buildOverhaulExcelSheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $header, array $details)
{
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
    $sheet->getColumnDimension('A')->setWidth(2);
    foreach (range('B', 'Z') as $col) { $sheet->getColumnDimension($col)->setWidth(3.5); }
    foreach (['AA','AB','AC','AD','AE','AF'] as $col) { $sheet->getColumnDimension($col)->setWidth(3.5); }
    $sheet->getColumnDimension('AF')->setWidth(2);

    $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
    $borderNone = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE]]];

    $rawNamaTop = $header['nama_pic'] ?: ($header['nama_staff'] ?? 'MEMBER');
    $namaTopParts = explode(' - ', $rawNamaTop);
    $namaTopOnly = end($namaTopParts);
    $waktuMulai = strtotime($header['waktu_mulai']);
    $waktuSelesai = $header['waktu_selesai'] ? strtotime($header['waktu_selesai']) : null;

    // KOP INSPECTION REPORT
    $sheet->mergeCells("B2:G5");
    $logoPath = FCPATH . 'uploads/nsi_logo.png';
    if (file_exists($logoPath)) {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo NSI');
        $drawing->setPath($logoPath);
        $drawing->setCoordinates('B2');
        $drawing->setHeight(70);
        $drawing->setOffsetX(35);
        $drawing->setOffsetY(10);
        $drawing->setWorksheet($sheet);
    }
    
    $sheet->mergeCells("H2:AE2");
    $sheet->setCellValue('H2', "INSPECTION REPORT - " . strtoupper($header['kategori'] ?? 'MESIN CNC'));
    $sheet->getStyle("H2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF92B0D6');
    $sheet->getStyle("H2")->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle("H2")->getAlignment()->setHorizontal('center')->setVertical('center');
    
    $sheet->mergeCells("H3:S3"); $sheet->setCellValue('H3', 'NO. DOCUMENT');
    $sheet->mergeCells("T3:AE3"); $sheet->setCellValue('T3', 'NO REVISI');
    $sheet->getStyle("H3:AE3")->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getStyle("H3:AE3")->getFont()->setBold(true);
    
    $sheet->mergeCells("H4:S4"); $sheet->setCellValue('H4', 'FM-MTN-10');
    $sheet->mergeCells("T4:AE4"); $sheet->setCellValue('T4', '0');
    $sheet->getStyle("H4:AE4")->getAlignment()->setHorizontal('center')->setVertical('center');
    
    $sheet->mergeCells("B5:AE5"); $sheet->setCellValue('B5', ' Rev.:0/291124');
    $sheet->getStyle("B5")->getFont()->setSize(9);
    $sheet->getStyle("B2:AE5")->applyFromArray($borderThin);

    // KOP INFO
    $row = 6;
    $sheet->mergeCells("B{$row}:F{$row}"); $sheet->setCellValue("B{$row}", 'MAIN PIC');
    $sheet->mergeCells("G{$row}:K{$row}"); $sheet->setCellValue("G{$row}", $namaTopOnly);
    $sheet->mergeCells("L{$row}:P{$row}"); $sheet->setCellValue("L{$row}", 'NO MACHINE');
    $sheet->mergeCells("Q{$row}:U{$row}"); $sheet->setCellValue("Q{$row}", $header['no_mesin']);
    $sheet->mergeCells("V{$row}:Z{$row}"); $sheet->setCellValue("V{$row}", 'DATE');
    $sheet->mergeCells("AA{$row}:AE{$row}"); $sheet->setCellValue("AA{$row}", format_tanggal_indo(date('Y-m-d', $waktuMulai)));
    
    $row++;
    $sheet->mergeCells("B{$row}:F".($row+1)); $sheet->setCellValue("B{$row}", 'SUPPORT PIC');
    $sheet->getStyle("B{$row}")->getAlignment()->setVertical('top');
    $sheet->mergeCells("G{$row}:K".($row+1)); $sheet->setCellValue("G{$row}", $header['support_pic'] ?? '-');
    $sheet->getStyle("G{$row}")->getAlignment()->setVertical('top');
    $sheet->mergeCells("L{$row}:P{$row}"); $sheet->setCellValue("L{$row}", 'MACHINE TYPE');
    $sheet->mergeCells("Q{$row}:U{$row}"); $sheet->setCellValue("Q{$row}", $header['type_mesin']);
    $sheet->mergeCells("V{$row}:Z{$row}"); $sheet->setCellValue("V{$row}", 'START TIME');
    $sheet->mergeCells("AA{$row}:AE{$row}"); $sheet->setCellValue("AA{$row}", date('H:i:s', $waktuMulai));
    
    $row++;
    if (stripos($header['kategori'] ?? '', 'CNC') !== false) {
        $sheet->mergeCells("L{$row}:P{$row}"); $sheet->setCellValue("L{$row}", 'BAR FEEDER TYPE');
        $sheet->mergeCells("Q{$row}:U{$row}"); $sheet->setCellValue("Q{$row}", $header['bar_feeder_type'] ?? '-');
    } else {
        $sheet->mergeCells("L{$row}:P{$row}");
        $sheet->mergeCells("Q{$row}:U{$row}");
    }
    $sheet->mergeCells("V{$row}:Z{$row}"); $sheet->setCellValue("V{$row}", 'FINISH TIME');
    $sheet->mergeCells("AA{$row}:AE{$row}"); $sheet->setCellValue("AA{$row}", $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-');
    
    $sheet->getStyle("B6:AE{$row}")->applyFromArray($borderThin);
    $sheet->getStyle("B6:B{$row},L6:L{$row},V6:V{$row}")->getFont()->setBold(true);

    $row += 2;
    $isMfg2 = (strtolower($header['departemen_check']) === 'mfg 2');

    // TABEL ISI
    // B-C (NO), D-H (ITEM CHECK), I-N (POINT CHECK), O-S (STANDAR), T-V (HASIL), W-AE (ULASAN)
    $sheet->mergeCells("B{$row}:C{$row}"); $sheet->setCellValue("B{$row}", 'NO');
    $sheet->mergeCells("D{$row}:H{$row}"); $sheet->setCellValue("D{$row}", 'ITEM CHECK');
    $sheet->mergeCells("I{$row}:N{$row}"); $sheet->setCellValue("I{$row}", 'POINT CHECK');
    if (!$isMfg2) {
        $sheet->mergeCells("O{$row}:S{$row}"); $sheet->setCellValue("O{$row}", 'STANDAR ITEM');
        $sheet->mergeCells("T{$row}:V{$row}"); $sheet->setCellValue("T{$row}", 'HASIL');
        $sheet->mergeCells("W{$row}:AE{$row}"); $sheet->setCellValue("W{$row}", 'ULASAN');
    } else {
        // Distribute space: HASIL (O-Q), ULASAN (R-AE)
        $sheet->mergeCells("O{$row}:Q{$row}"); $sheet->setCellValue("O{$row}", 'HASIL');
        $sheet->mergeCells("R{$row}:AE{$row}"); $sheet->setCellValue("R{$row}", 'ULASAN');
    }
    
    $sheet->getStyle("B{$row}:AE{$row}")->getFont()->setBold(true);
    $sheet->getStyle("B{$row}:AE{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
    $sheet->getStyle("B{$row}:AE{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getStyle("B{$row}:AE{$row}")->applyFromArray($borderThin);

    $startDetailRow = $row + 1;
    $row++;

    foreach ($details as $d) {
        if (!empty($d['is_section_start'])) {
            $sheet->mergeCells("B{$row}:AE{$row}");
            $sheet->setCellValue("B{$row}", $d['dynamic_section_header'] ?? '');
            $sheet->getStyle("B{$row}")->getFont()->setBold(true);
            $sheet->getStyle("B{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
            $row++;
        }

        $sheet->mergeCells("B{$row}:C{$row}");
        if (!empty($d['show_no'])) {
            $sheet->setCellValue("B{$row}", $d['dynamic_no'] ?? '');
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
        }

        if (!empty($d['sub_item_check'])) {
            // Split D-H into D-E (Bagian) and F-H (Sub Item)
            $sheet->mergeCells("D{$row}:E{$row}");
            if (!empty($d['show_bagian'])) {
                $sheet->setCellValue("D{$row}", $d['bagian_check']);
                $sheet->getStyle("D{$row}")->getAlignment()->setVertical('center')->setWrapText(true);
            }
            $sheet->mergeCells("F{$row}:H{$row}");
            $sheet->setCellValue("F{$row}", $d['sub_item_check']);
            $sheet->getStyle("F{$row}")->getAlignment()->setVertical('center')->setWrapText(true);
        } else {
            $sheet->mergeCells("D{$row}:H{$row}");
            $sheet->setCellValue("D{$row}", $d['bagian_check']);
            $sheet->getStyle("D{$row}")->getAlignment()->setVertical('center')->setWrapText(true);
        }

        $sheet->mergeCells("I{$row}:N{$row}");
        if (!empty($d['show_point'])) {
            $sheet->setCellValue("I{$row}", $d['point_check'] ?? '');
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
        }

        if (!$isMfg2) {
            $sheet->mergeCells("O{$row}:S{$row}");
            if (!empty($d['show_standard'])) {
                $sheet->setCellValue("O{$row}", $d['standard_check'] ?? '');
                $sheet->getStyle("O{$row}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
            }
            $hasilCol = "T"; $hasilEnd = "V";
            $ulasanCol = "W"; $ulasanEnd = "AE";
        } else {
            $hasilCol = "O"; $hasilEnd = "Q";
            $ulasanCol = "R"; $ulasanEnd = "AE";
        }

        $sheet->mergeCells("{$hasilCol}{$row}:{$hasilEnd}{$row}");
        $hasil = $d['hasil_check'] ?? '';
        $sheet->setCellValue("{$hasilCol}{$row}", $hasil);
        if ($hasil === 'V') $sheet->getStyle("{$hasilCol}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FF008000');
        if ($hasil === 'Δ') $sheet->getStyle("{$hasilCol}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFB8860B');
        if ($hasil === 'X') $sheet->getStyle("{$hasilCol}{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFFF0000');
        $sheet->getStyle("{$hasilCol}{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
        
        $sheet->mergeCells("{$ulasanCol}{$row}:{$ulasanEnd}{$row}");
        $ulasanText = $d['ulasan'] ?? '-';
        $sheet->setCellValue("{$ulasanCol}{$row}", $ulasanText);
        $sheet->getStyle("{$ulasanCol}{$row}")->getAlignment()->setVertical('top')->setWrapText(true);

        $hasImage = false;
        if (!empty($d['foto_abnormal'])) {
            $imgPath = FCPATH . 'uploads/abnormal/' . $d['foto_abnormal'];
            if (file_exists($imgPath)) {
                $sheet->setCellValue("{$ulasanCol}{$row}", $ulasanText . "\n\n\n\n\n\n");
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Foto Abnormal');
                $drawing->setPath($imgPath);
                $drawing->setCoordinates($ulasanCol . $row);
                $drawing->setHeight(75);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(15);
                $drawing->setWorksheet($sheet);
                $hasImage = true;
            }
        }
        if (!empty($d['foto_abnormal_2'])) {
            $imgPath2 = FCPATH . 'uploads/abnormal/' . $d['foto_abnormal_2'];
            if (file_exists($imgPath2)) {
                $sheet->setCellValue("{$ulasanCol}{$row}", $ulasanText . "\n\n\n\n\n\n\n\n\n\n\n");
                $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing2->setName('Foto Abnormal 2');
                $drawing2->setPath($imgPath2);
                $drawing2->setCoordinates($ulasanCol . $row);
                $drawing2->setHeight(75);
                $drawing2->setOffsetX(5);
                $drawing2->setOffsetY(95);
                $drawing2->setWorksheet($sheet);
                $hasImage = true;
            }
        }
        if ($hasImage) {
            $sheet->getRowDimension($row)->setRowHeight(empty($d['foto_abnormal_2']) ? 80 : 160);
        }
        $row++;
    }

    $sheet->getStyle("B{$startDetailRow}:AE" . ($row - 1))->applyFromArray($borderThin);
    
    // NOTE AND RECOMMENDATION
    $row++;
    $note = $header['note_recommendation'] ?? '';
    if (!empty($note)) {
        $sheet->mergeCells("B{$row}:M" . ($row+3));
        $sheet->setCellValue("B{$row}", "NOTE AND RECOMMENDATION\n" . $note);
        $sheet->getStyle("B{$row}:M" . ($row+3))->applyFromArray($borderThin);
        $sheet->getStyle("B{$row}")->getAlignment()->setVertical('top')->setWrapText(true);
        $sheet->getStyle("B{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FA');
    }

    // KETERANGAN CHECK LIST (at the right)
    $ketStartRow = $row;
    $sheet->mergeCells("W{$ketStartRow}:AE{$ketStartRow}"); $sheet->setCellValue("W{$ketStartRow}", "KETERANGAN CHECK LIST");
    $sheet->getStyle("W{$ketStartRow}:AE{$ketStartRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
    $sheet->getStyle("W{$ketStartRow}:AE{$ketStartRow}")->getFont()->setBold(true);
    $sheet->getStyle("W{$ketStartRow}:AE{$ketStartRow}")->getAlignment()->setHorizontal('center')->setVertical('center');
    
    $ketStartRow++;
    $sheet->mergeCells("W{$ketStartRow}:X{$ketStartRow}"); $sheet->setCellValue("W{$ketStartRow}", "V");
    $sheet->getStyle("W{$ketStartRow}")->getFont()->setBold(true)->getColor()->setARGB('FF008000');
    $sheet->getStyle("W{$ketStartRow}")->getAlignment()->setHorizontal('center');
    $sheet->setCellValue("Y{$ketStartRow}", ":");
    $sheet->mergeCells("Z{$ketStartRow}:AE{$ketStartRow}"); $sheet->setCellValue("Z{$ketStartRow}", "OK");

    $ketStartRow++;
    $sheet->mergeCells("W{$ketStartRow}:X{$ketStartRow}"); $sheet->setCellValue("W{$ketStartRow}", "Δ");
    $sheet->getStyle("W{$ketStartRow}")->getFont()->setBold(true)->getColor()->setARGB('FFB8860B');
    $sheet->getStyle("W{$ketStartRow}")->getAlignment()->setHorizontal('center');
    $sheet->setCellValue("Y{$ketStartRow}", ":");
    $sheet->mergeCells("Z{$ketStartRow}:AE{$ketStartRow}"); $sheet->setCellValue("Z{$ketStartRow}", "PERLU TINDAKAN");

    $ketStartRow++;
    $sheet->mergeCells("W{$ketStartRow}:X{$ketStartRow}"); $sheet->setCellValue("W{$ketStartRow}", "X");
    $sheet->getStyle("W{$ketStartRow}")->getFont()->setBold(true)->getColor()->setARGB('FFFF0000');
    $sheet->getStyle("W{$ketStartRow}")->getAlignment()->setHorizontal('center');
    $sheet->setCellValue("Y{$ketStartRow}", ":");
    $sheet->mergeCells("Z{$ketStartRow}:AE{$ketStartRow}"); $sheet->setCellValue("Z{$ketStartRow}", "TIDAK ADA");
    
    $sheet->getStyle("W" . ($row) . ":AE{$ketStartRow}")->applyFromArray($borderThin);

    // SIGNATURES
    $row += 5;
    $sheet->mergeCells("S{$row}:X{$row}"); $sheet->setCellValue("S{$row}", "Dibuat Oleh\n\nPIC\n\n\n" . ($header['waktu_selesai'] ? "[ Selesai ]\n" : "\n") . $namaTopOnly);
    $sheet->mergeCells("Z{$row}:AE{$row}"); $sheet->setCellValue("Z{$row}", "Disetujui Oleh\n\nPIC LINE\n\n\n" . ($header['status'] === 'Approved' ? "[ Disetujui ]\n" . ($header['approver_nama'] ?? '') : "\n(...................)"));
    $sheet->getStyle("S{$row}:AE{$row}")->getAlignment()->setWrapText(true)->setHorizontal('center')->setVertical('center');
    $sheet->getStyle("S{$row}:X{$row}")->applyFromArray($borderThin);
    $sheet->getStyle("Z{$row}:AE{$row}")->applyFromArray($borderThin);
    $sheet->getRowDimension($row)->setRowHeight(110);
}
