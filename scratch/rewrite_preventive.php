<?php

function buildPreventiveExcelSheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $header, array $details)
{
    $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
    $sheet->getColumnDimension('A')->setWidth(2);
    foreach (range('B', 'Z') as $col) { $sheet->getColumnDimension($col)->setWidth(3.5); }
    foreach (['AA','AB','AC','AD','AE','AF'] as $col) { $sheet->getColumnDimension($col)->setWidth(3.5); }
    $sheet->getColumnDimension('AF')->setWidth(2);

    $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
    $borderOutline = ['borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];
    $borderNone = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE]]];

    // ROW 2-5: Kop Surat
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
    $title = "CHECKLIST REPORT - " . strtoupper($header['kategori'] ?? 'MESIN CNC') . " (" . strtoupper($header['departemen_check'] ?? '-') . ")";
    $sheet->setCellValue('H2', $title);
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

    // KOP INFO ROWS
    $waktuMulai = strtotime($header['waktu_mulai']);
    $waktuSelesai = $header['waktu_selesai'] ? strtotime($header['waktu_selesai']) : null;
    $row = 6;
    
    $sheet->mergeCells("B{$row}:E{$row}"); $sheet->setCellValue("B{$row}", 'DATE');
    $sheet->mergeCells("F{$row}:J{$row}"); $sheet->setCellValue("F{$row}", format_tanggal_indo(date('Y-m-d', $waktuMulai)));
    $sheet->mergeCells("K{$row}:O{$row}"); $sheet->setCellValue("K{$row}", 'MACHINE TYPE');
    $sheet->mergeCells("P{$row}:V{$row}"); $sheet->setCellValue("P{$row}", $header['type_mesin']);
    $sheet->mergeCells("W{$row}:AA{$row}"); $sheet->setCellValue("W{$row}", 'START TIME');
    $sheet->mergeCells("AB{$row}:AE{$row}"); $sheet->setCellValue("AB{$row}", date('H:i:s', $waktuMulai));
    $sheet->getStyle("B{$row}:AE{$row}")->applyFromArray($borderThin);
    $sheet->getStyle("B{$row},K{$row},W{$row}")->getFont()->setBold(true);

    $row++;
    $sheet->mergeCells("B{$row}:E{$row}"); $sheet->setCellValue("B{$row}", 'NO MACHINE');
    $sheet->mergeCells("F{$row}:J{$row}"); $sheet->setCellValue("F{$row}", $header['no_mesin']);
    $sheet->mergeCells("K{$row}:O{$row}"); $sheet->setCellValue("K{$row}", 'SERIAL NUMBER');
    $sheet->mergeCells("P{$row}:V{$row}"); $sheet->setCellValue("P{$row}", $header['serial_nomor'] ?? '-');
    $sheet->mergeCells("W{$row}:AA{$row}"); $sheet->setCellValue("W{$row}", 'FINISH TIME');
    $sheet->mergeCells("AB{$row}:AE{$row}"); $sheet->setCellValue("AB{$row}", $waktuSelesai ? date('H:i:s', $waktuSelesai) : '-');
    $sheet->getStyle("B{$row}:AE{$row}")->applyFromArray($borderThin);
    $sheet->getStyle("B{$row},K{$row},W{$row}")->getFont()->setBold(true);

    $row += 2;
    // HEADER TABEL (BAGIAN CHECK | POINT CHECK | STANDARD CHECK | HASIL | ULASAN)
    // Proportions: 25% (7 cols B-H), 20% (6 cols I-N), 20% (6 cols O-T), 10% (3 cols U-W), 25% (8 cols X-AE)
    $sheet->mergeCells("B{$row}:H{$row}"); $sheet->setCellValue("B{$row}", 'BAGIAN CHECK');
    $sheet->mergeCells("I{$row}:N{$row}"); $sheet->setCellValue("I{$row}", 'POINT CHECK');
    $sheet->mergeCells("O{$row}:T{$row}"); $sheet->setCellValue("O{$row}", 'STANDARD CHECK');
    $sheet->mergeCells("U{$row}:W{$row}"); $sheet->setCellValue("U{$row}", 'HASIL');
    $sheet->mergeCells("X{$row}:AE{$row}"); $sheet->setCellValue("X{$row}", 'ULASAN');
    
    $sheet->getStyle("B{$row}:AE{$row}")->getFont()->setBold(true);
    $sheet->getStyle("B{$row}:AE{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
    $sheet->getStyle("B{$row}:AE{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
    $sheet->getStyle("B{$row}:AE{$row}")->applyFromArray($borderThin);

    $startDetailRow = $row + 1;
    $row++;

    foreach ($details as $d) {
        $sheet->mergeCells("B{$row}:H{$row}"); 
        if (!empty($d['show_bagian'])) {
            $sheet->setCellValue("B{$row}", $d['bagian_check'] ?? '');
            $sheet->getStyle("B{$row}")->getAlignment()->setVertical('top')->setWrapText(true);
        }
        
        $sheet->mergeCells("I{$row}:N{$row}"); $sheet->setCellValue("I{$row}", $d['point_check'] ?? '');
        $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
        
        $sheet->mergeCells("O{$row}:T{$row}"); $sheet->setCellValue("O{$row}", $d['standard_check'] ?? '');
        $sheet->getStyle("O{$row}")->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
        
        $sheet->mergeCells("U{$row}:W{$row}"); 
        $hasil = $d['hasil_check'] ?? '';
        $sheet->setCellValue("U{$row}", $hasil);
        if ($hasil === 'V') $sheet->getStyle("U{$row}")->getFont()->setBold(true)->getColor()->setARGB('FF008000');
        if ($hasil === 'Δ') $sheet->getStyle("U{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFB8860B');
        if ($hasil === 'X') $sheet->getStyle("U{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFFF0000');
        $sheet->getStyle("U{$row}")->getAlignment()->setHorizontal('center')->setVertical('center');
        
        $sheet->mergeCells("X{$row}:AE{$row}");
        $ulasanText = $d['ulasan'] ?? '';
        $sheet->setCellValue("X{$row}", $ulasanText);
        $sheet->getStyle("X{$row}")->getAlignment()->setVertical('top')->setWrapText(true);

        $hasImage = false;
        if (!empty($d['foto_abnormal'])) {
            $imgPath = FCPATH . 'uploads/abnormal/' . $d['foto_abnormal'];
            if (file_exists($imgPath)) {
                $sheet->setCellValue("X{$row}", $ulasanText . "\n\n\n\n\n\n");
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Foto Abnormal');
                $drawing->setPath($imgPath);
                $drawing->setCoordinates('X' . $row);
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
                $sheet->setCellValue("X{$row}", $ulasanText . "\n\n\n\n\n\n\n\n\n\n\n");
                $drawing2 = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing2->setName('Foto Abnormal 2');
                $drawing2->setPath($imgPath2);
                $drawing2->setCoordinates('X' . $row);
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
    $rawNamaTop = $header['nama_pic'] ?: ($header['nama_staff'] ?? 'MEMBER');
    $namaTopParts = explode(' - ', $rawNamaTop);
    $namaTopOnly = end($namaTopParts);

    $sheet->mergeCells("S{$row}:X{$row}"); $sheet->setCellValue("S{$row}", "Dibuat Oleh\n\nPIC\n\n\n" . ($header['waktu_selesai'] ? "[ Selesai ]\n" : "\n") . $namaTopOnly);
    $sheet->mergeCells("Z{$row}:AE{$row}"); $sheet->setCellValue("Z{$row}", "Disetujui Oleh\n\nPIC LINE\n\n\n" . ($header['status'] === 'Approved' ? "[ Disetujui ]\n" . ($header['approver_nama'] ?? '') : "\n(...................)"));
    $sheet->getStyle("S{$row}:AE{$row}")->getAlignment()->setWrapText(true)->setHorizontal('center')->setVertical('center');
    $sheet->getStyle("S{$row}:X{$row}")->applyFromArray($borderThin);
    $sheet->getStyle("Z{$row}:AE{$row}")->applyFromArray($borderThin);
    $sheet->getRowDimension($row)->setRowHeight(110);
}

