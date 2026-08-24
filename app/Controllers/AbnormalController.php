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
    private function streamAbnormalExcel(array $reports, string $departemen, string $kategori, string $bulan): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Abnormal Report');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE53935']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $dataStyle = [
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];

        // Title & info
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', 'ABNORMAL REPORT CONDITION');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('A2', 'AREA'); $sheet->setCellValue('B2', $departemen);
        $sheet->setCellValue('C2', 'KATEGORI'); $sheet->setCellValue('D2', $kategori);
        $sheet->setCellValue('E2', 'BULAN'); $sheet->setCellValue('F2', $bulan);

        $headers = ['No', 'Mesin', 'Point Check', 'Abnormal Condition', 'Type Sparepart', 'Tgl Pengecekan', 'PIC Cek', 'Progres Stock', 'Tgl Progres', 'Action', 'PIC Action', 'Keterangan'];
        $col = 'A'; $hRow = 4;
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $hRow, $h);
            $col++;
        }
        $sheet->getStyle('A' . $hRow . ':L' . $hRow)->applyFromArray($headerStyle);

        $row = 5; $no = 1;
        foreach ($reports as $r) {
            $pointDisplay = $r['point_check'];
            if (!empty($r['bagian_check'])) {
                $parts = [$r['bagian_check']];
                if (!empty($r['sub_item_check'])) $parts[] = $r['sub_item_check'];
                $parts[] = $r['point_check'];
                $pointDisplay = implode(' - ', $parts);
            }
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $r['no_mesin'] ?? '-');
            $sheet->setCellValue('C' . $row, $pointDisplay);
            $sheet->setCellValue('D' . $row, $r['abnormal_condition'] ?? '-');
            $sheet->setCellValue('E' . $row, $r['type_sparepart'] ?? '');
            $sheet->setCellValue('F' . $row, $r['pengecekan_tanggal'] ?? '');
            $sheet->setCellValue('G' . $row, $r['pengecekan_pic'] ?? '');
            $sheet->setCellValue('H' . $row, $r['progres_stock'] ?? '');
            $sheet->setCellValue('I' . $row, $r['progres_tanggal'] ?? '');
            $sheet->setCellValue('J' . $row, $r['action'] ?? '');
            $sheet->setCellValue('K' . $row, $r['repair_pic'] ?? '');
            $sheet->setCellValue('L' . $row, $r['keterangan'] ?? '');
            $row++;
        }
        if ($row > 5) $sheet->getStyle('A5:L' . ($row - 1))->applyFromArray($dataStyle);
        foreach (range('A', 'L') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

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
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE53935']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $dataStyle = [
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ];

        $sheetIdx = 0;
        foreach ($data['allCategoryReports'] ?? [] as $catData) {
            $kategori = $catData['kategori'] ?? 'Sheet';
            $reports  = $catData['reports']  ?? [];
            $sheet = $sheetIdx === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $sheet->setTitle(substr($kategori, 0, 31));

            $sheet->mergeCells('A1:L1');
            $sheet->setCellValue('A1', 'ABNORMAL REPORT - ' . strtoupper($kategori));
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $headers = ['No', 'Mesin', 'Point Check', 'Abnormal Condition', 'Type Sparepart', 'Tgl Pengecekan', 'PIC Cek', 'Progres Stock', 'Tgl Progres', 'Action', 'PIC Action', 'Keterangan'];
            $col = 'A'; $hRow = 3;
            foreach ($headers as $h) { $sheet->setCellValue($col . $hRow, $h); $col++; }
            $sheet->getStyle('A' . $hRow . ':L' . $hRow)->applyFromArray($headerStyle);

            $row = 4; $no = 1;
            foreach ($reports as $r) {
                $pointDisplay = $r['point_check'];
                if (!empty($r['bagian_check'])) {
                    $parts = [$r['bagian_check']];
                    if (!empty($r['sub_item_check'])) $parts[] = $r['sub_item_check'];
                    $parts[] = $r['point_check'];
                    $pointDisplay = implode(' - ', $parts);
                }
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValue('B' . $row, $r['no_mesin'] ?? '-');
                $sheet->setCellValue('C' . $row, $pointDisplay);
                $sheet->setCellValue('D' . $row, $r['abnormal_condition'] ?? '-');
                $sheet->setCellValue('E' . $row, $r['type_sparepart'] ?? '');
                $sheet->setCellValue('F' . $row, $r['pengecekan_tanggal'] ?? '');
                $sheet->setCellValue('G' . $row, $r['pengecekan_pic'] ?? '');
                $sheet->setCellValue('H' . $row, $r['progres_stock'] ?? '');
                $sheet->setCellValue('I' . $row, $r['progres_tanggal'] ?? '');
                $sheet->setCellValue('J' . $row, $r['action'] ?? '');
                $sheet->setCellValue('K' . $row, $r['repair_pic'] ?? '');
                $sheet->setCellValue('L' . $row, $r['keterangan'] ?? '');
                $row++;
            }
            if ($row > 4) $sheet->getStyle('A4:L' . ($row - 1))->applyFromArray($dataStyle);
            foreach (range('A', 'L') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);
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
