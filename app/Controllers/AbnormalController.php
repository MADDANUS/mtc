<?php

namespace App\Controllers;

use App\Services\AbnormalService;
use App\Models\MasterMesinModel;
use App\Models\TransaksiCheckModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AbnormalController extends BaseController
{
    public function __construct()
    {
        // Constructor is empty now as model is handled by service
    }

    public function pdf()
    {
        $service = new AbnormalService();
        $data = $service->pdf($this->request);
        $html = view('abnormal/pdf', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_' . str_replace(' ', '_', $data['kategoriFilter']) . '_' . str_replace(' ', '_', $data['lokasiFilter']) . '.pdf', ['Attachment' => 0]);
    }

    public function pdfAllCategories()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllCategories($this->request);
        $html = view('abnormal/pdf_all', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Semua_Kategori_' . str_replace(' ', '_', $data['lokasiFilter']) . '_' . $data['bulanFilter'] . '.pdf', ['Attachment' => true]);
    }

    public function pdfAllSummary()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllSummary($this->request);
        $html = view('abnormal/pdf_all', $data);
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Ringkasan_Semua_Area_' . $data['bulanFilter'] . '.pdf', ['Attachment' => true]);
    }

    public function excelAllSummary()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllSummary($this->request);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Summary Abnormal');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE53935'] // Red for abnormal
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ];

        $headers = ['No', 'Departemen', 'Kategori', 'Mesin', 'Point Check', 'Abnormal Condition', 'Type Sparepart', 'Tgl Pengecekan', 'PIC Cek', 'Progres', 'Tgl Progres', 'Action', 'PIC Action', 'Keterangan'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

        $rowNum = 2;
        $no = 1;
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
        ];

        foreach ($data['allReportsData'] as $item) {
            $departemen = $item['departemen'];
            $kategori = $item['kategori'];
            foreach ($item['reports'] as $r) {
                $pointCheckDisplay = $r['point_check'];
                if (!empty($r['bagian_check'])) {
                    $parts = [$r['bagian_check']];
                    if (!empty($r['sub_item_check'])) $parts[] = $r['sub_item_check'];
                    $parts[] = $r['point_check'];
                    $pointCheckDisplay = implode(' - ', $parts);
                }

                $sheet->setCellValue('A' . $rowNum, $no++);
                $sheet->setCellValue('B' . $rowNum, $departemen);
                $sheet->setCellValue('C' . $rowNum, $kategori);
                $sheet->setCellValue('D' . $rowNum, $r['no_mesin']);
                $sheet->setCellValue('E' . $rowNum, $pointCheckDisplay);
                $sheet->setCellValue('F' . $rowNum, $r['abnormal_condition']);
                $sheet->setCellValue('G' . $rowNum, $r['type_sparepart'] ?? '');
                $sheet->setCellValue('H' . $rowNum, $r['pengecekan_tanggal'] ?? '');
                $sheet->setCellValue('I' . $rowNum, $r['pengecekan_pic'] ?? '');
                $sheet->setCellValue('J' . $rowNum, $r['progres_stock'] ?? '');
                $sheet->setCellValue('K' . $rowNum, $r['progres_tanggal'] ?? '');
                $sheet->setCellValue('L' . $rowNum, $r['action'] ?? '');
                $sheet->setCellValue('M' . $rowNum, $r['repair_pic'] ?? '');
                $sheet->setCellValue('N' . $rowNum, $r['keterangan'] ?? '');
                
                $rowNum++;
            }
        }

        if ($rowNum > 2) {
            $sheet->getStyle('A2:N' . ($rowNum - 1))->applyFromArray($dataStyle);
            $sheet->getStyle('A2:A' . ($rowNum - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'N') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = "Laporan_Abnormal_Ringkasan_Semua_Area_" . $data['bulanFilter'] . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function index()
    {
        $service = new AbnormalService();
        $data = $service->index($this->request);
        if (isset($data['is_summary']) && $data['is_summary']) {
            return view('abnormal/summary', $data);
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'html' => $data['html'] ?? '',
                'currentPage' => $data['currentPage'] ?? 1,
                'totalPages' => $data['totalPages'] ?? 1,
                'totalItems' => $data['totalItems'] ?? 0,
                'perPage' => $data['perPage'] ?? 15,
                'startNo' => $data['startNo'] ?? 1,
            ]);
        }
        
        return view('abnormal/index', $data);
    }

    public function update()
    {
        $service = new AbnormalService();
        $result = $service->update($this->request);
        if ($result['status']) {
            return redirect()->to('/abnormal')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function overhaul()
    {
        $service = new AbnormalService();
        $data = $service->overhaul($this->request);
        if (isset($data['is_summary']) && $data['is_summary']) {
            return view('abnormal/summary_overhaul', $data);
        }
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'html' => $data['html'] ?? '',
                'currentPage' => $data['currentPage'] ?? 1,
                'totalPages' => $data['totalPages'] ?? 1,
                'totalItems' => $data['totalItems'] ?? 0,
                'perPage' => $data['perPage'] ?? 15,
                'startNo' => $data['startNo'] ?? 1,
            ]);
        }
        
        return view('abnormal/index_overhaul', $data);
    }

    public function pdfOverhaul()
    {
        $service = new AbnormalService();
        $data = $service->pdfOverhaul($this->request);
        $html = view('abnormal/pdf_overhaul', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Overhaul_' . str_replace(' ', '_', $data['lokasiFilter']) . '.pdf', ['Attachment' => 0]);
    }

    public function pdfAllSummaryOverhaul()
    {
        $service = new AbnormalService();
        $data = $service->pdfAllSummaryOverhaul($this->request);
        $html = view('abnormal/pdf_all_summary', $data);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream('Laporan_Abnormal_Overhaul_Ringkasan_' . $data['bulanFilter'] . '.pdf', ['Attachment' => true]);
    }

    public function updateOverhaul()
    {
        $service = new AbnormalService();
        $result = $service->updateOverhaul($this->request);
        if ($result['status']) {
            return redirect()->to('/abnormal/overhaul')->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function uploadFotoPerbaikan()
    {
        $service = new AbnormalService();
        $result = $service->uploadFotoPerbaikan($this->request);
        return $this->response->setJSON($result);
    }

    public function testQuery() {
        $m = new \App\Models\LaporanAbnormalModel();
        $res = $m->getOverhaulLaporan('MFG 1', '2026-08', '', 15);
        return $this->response->setJSON($res);
    }

    public function deleteFotoPerbaikan()
    {
        $service = new AbnormalService();
        $result = $service->deleteFotoPerbaikan($this->request);
        return $this->response->setJSON($result);
    }

    /**
     * GET /abnormal/chart-data
     * Endpoint JSON untuk grafik tren abnormalitas dinamis (ApexCharts).
     * Menerima GET params: plant, departemen, line, kategori
     * Mengembalikan series ApexCharts yang siap dipakai.
     */
    public function getChartData()
    {
        $plant      = $this->request->getGet('plant')       ?: null;
        $departemen = $this->request->getGet('departemen')  ?: null;
        $line       = $this->request->getGet('line')        ?: null;
        $kategori   = $this->request->getGet('kategori')    ?: null;
        $bulanRange = (int)($this->request->getGet('bulan_range') ?: 12);

        $model = new \App\Models\LaporanAbnormalModel();
        $rows  = $model->getChartData($plant, $departemen, $line, $kategori, $bulanRange);

        // Kumpulkan semua bulan unik & label (X-axis)
        $bulanSet    = [];
        $seriesIndex = []; // key = nama seri (kategori atau line)
        foreach ($rows as $r) {
            if ($r['bulan']) $bulanSet[$r['bulan']] = true;
        }
        ksort($bulanSet);
        $bulanKeys = array_keys($bulanSet);

        // Label bulan dalam format "Agu 2026" untuk X-axis
        $bulanLabels = array_map(function($b) {
            return date('M Y', strtotime($b . '-01'));
        }, $bulanKeys);

        // Tentukan apakah seri berdasarkan kategori atau line
        // Logika: jika line di-set spesifik => garis per kategori
        //         jika kategori di-set spesifik => garis per line
        //         selain itu => garis per kategori
        $seriesGroupKey = (!empty($line) && $line !== 'all') ? 'kategori' : (
            (!empty($kategori) && $kategori !== 'all') ? 'line' : 'kategori'
        );

        // Bangun map: [seriesName][bulan] = total
        $dataMap = [];
        foreach ($rows as $r) {
            $seriesName = $r[$seriesGroupKey] ?? '(Tidak Diketahui)';
            $bulan = $r['bulan'];
            if (!isset($dataMap[$seriesName])) {
                $dataMap[$seriesName] = [];
            }
            $dataMap[$seriesName][$bulan] = ($dataMap[$seriesName][$bulan] ?? 0) + (int)$r['total'];
        }

        // Konversi ke format series ApexCharts [{name:'...', data:[...]}]
        $series = [];
        foreach ($dataMap as $name => $bulanData) {
            $dataPoints = [];
            foreach ($bulanKeys as $b) {
                $dataPoints[] = $bulanData[$b] ?? 0;
            }
            $series[] = ['name' => $name, 'data' => $dataPoints];
        }

        // Urutkan series berdasarkan total tertinggi
        usort($series, function($a, $b) {
            return array_sum($b['data']) - array_sum($a['data']);
        });

        return $this->response->setJSON([
            'categories' => $bulanLabels,
            'series'     => $series,
            'groupBy'    => $seriesGroupKey,
        ]);
    }

    // ─── Excel: 1 kategori ───────────────────────────────────────────────
    public function excelPerKategori()
    {
        $service = new AbnormalService();
        $data    = $service->pdf($this->request); // reuse same data builder
        $this->streamAbnormalExcel($data['reports'], $data['lokasiFilter'], $data['kategoriFilter'], $data['bulanFilter']);
    }

    // ─── Excel: Semua kategori di 1 area ─────────────────────────────────
    public function excelAllCategories()
    {
        $service = new AbnormalService();
        $data    = $service->pdfAllCategories($this->request);
        $this->streamAbnormalAllExcel($data);
    }

    // ─── Helper: Single sheet Excel ───────────────────────────────────────
private function buildAbnormalExcelSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $reports, $itemLokasi, $kategoriFilter, $bulanFilter)
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

        private function streamAbnormalExcel(array $reports, string $departemen, string $kategori, string $bulan): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Abnormal Report');
        
        $this->buildAbnormalExcelSheet($sheet, $reports, $departemen, $kategori, $bulan);

        $filename = 'Laporan_Abnormal_' . str_replace(' ', '_', $kategori) . '_' . str_replace(' ', '_', $departemen) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
    // ─── Helper: Multi-sheet Excel all categories ─────────────────────────
        private function streamAbnormalAllExcel(array $data): void
    {
        $spreadsheet = new Spreadsheet();
        $sheetIdx = 0;
        foreach ($data['allCategoryReports'] ?? [] as $catData) {
            $kategori = $catData['kategori'] ?? 'Sheet';
            $reports  = $catData['reports']  ?? [];
            $sheet = $sheetIdx === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $sheet->setTitle(substr($kategori, 0, 31));

            $this->buildAbnormalExcelSheet($sheet, $reports, $data['lokasiFilter'] ?? '', $kategori, $data['bulanFilter'] ?? date('Y-m'));
            $sheetIdx++;
        }

        $spreadsheet->setActiveSheetIndex(0);
        $filename = 'Laporan_Abnormal_Semua_Kategori_' . str_replace(' ', '_', $data['lokasiFilter'] ?? '') . '_' . ($data['bulanFilter'] ?? date('Y-m')) . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
