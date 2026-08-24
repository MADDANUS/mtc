<?php

namespace App\Controllers;

use App\Enums\Role;
use App\Enums\Departemen;

use App\Models\TransaksiCheckModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanController extends BaseController
{
    public function durasi()
    {
        $departemenName = has_role(Role::Leader->value) ? session()->get('departemen') : ($this->request->getGet('departemen') === 'all' ? null : ($this->request->getGet('departemen') ?: null));
        $userLine = has_role(Role::Leader->value) ? session()->get('line') : null;

        $filters = [
            'departemen'      => $departemenName,
            'id_mesin'    => $this->request->getGet('id_mesin') === 'all' ? null : ($this->request->getGet('id_mesin') ?: null),
            'line'        => $userLine ?: ($this->request->getGet('line') === 'all' ? null : ($this->request->getGet('line') ?: null)),
            'jenis_check' => $this->request->getGet('jenis_check') === 'all' ? null : ($this->request->getGet('jenis_check') ?: null),
            'bulan'       => $this->request->getGet('bulan') === 'all' ? null : ($this->request->getGet('bulan') ?: null),
            'pic'         => $this->request->getGet('pic') === 'all' ? null : ($this->request->getGet('pic') ?: null),
            'sort_by'     => $this->request->getGet('sort_by') ?: 'id_transaksi',
            'order'       => $this->request->getGet('order') ?: 'desc',
        ];
        
        $transaksiModel = new TransaksiCheckModel();
        
        $perPage = (int) ($this->request->getGet('per_page') ?: 15);
        $currentPage = (int) ($this->request->getGet('page_durasi') ?: 1);

        $laporan = $transaksiModel->getLaporanDurasi($filters, $perPage);
        
        $pager = $transaksiModel->pager;
        $totalItems = $pager ? $pager->getTotal('durasi') : 0;
        $totalPages = $pager ? $pager->getPageCount('durasi') : 1;
        $startNo = ($currentPage - 1) * $perPage + 1;

        $rataDetik = $transaksiModel->getRataRataDurasiFiltered($filters);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'html'        => view('laporan/_durasi_rows', [
                                    'laporan' => $laporan,
                                    'startNo' => $startNo
                                 ]),
                'currentPage' => $currentPage,
                'totalPages'  => $totalPages,
                'totalItems'  => $totalItems,
                'perPage'     => $perPage,
                'startNo'     => $startNo
            ]);
        }
        
        // Fetch dropdown options
        $mesinModel = new \App\Models\MesinModel();
        $daftarMesin = $mesinModel->getByDepartemen($departemenName);

        $availableLines = [];
        if ($departemenName === Departemen::MFG1->value) {
            $availableLines = ['Line 1', 'Line 2', 'Line 3'];
        } elseif ($departemenName === Departemen::MFG2->value) {
            $availableLines = ['CG', 'Second'];
        }

        $transaksiModel = new \App\Models\TransaksiCheckModel();
        $rawPics = $transaksiModel->getAvailablePics($departemenName ?: null);
        $availablePics = [];
        foreach ($rawPics as $row) {
            $raw = $row['nama_pic'] ?: $row['nama_staff'];
            $parts = explode(' - ', $raw);
            $name = end($parts);
            if ($name) {
                $availablePics[] = trim($name);
            }
        }
        $availablePics = array_unique($availablePics);
        sort($availablePics);

        // List bulan untuk dropdown filter
        $bulanList = [];
        // Mulai dari -1 untuk menambahkan 1 bulan ke depan (Curi Start)
        for ($i = -1; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $val  = $time->format('Y-m');
            $label = format_bulan_indo($val);
            $bulanList[$val] = $label;
        }

        return view('laporan/durasi', [
            'title'           => 'Laporan Durasi Pengecekan',
            'laporan'         => $laporan,
            'rataDetik'       => $rataDetik,
            'selectedFilters' => $filters,
            'daftarMesin'     => $daftarMesin,
            'availableLines'  => $availableLines,
            'availablePics'   => $availablePics,
            'bulanList'       => $bulanList,
            'userLine'        => $userLine,
            'startNo'         => $startNo,
            'totalItems'      => $totalItems,
            'perPage'         => $perPage,
        ]);
    }

    public function durasiPdf()
    {
        $departemenName = has_role(Role::Leader->value) ? session()->get('departemen') : ($this->request->getGet('departemen') === 'all' ? null : ($this->request->getGet('departemen') ?: null));
        $userLine = has_role(Role::Leader->value) ? session()->get('line') : null;

        $filters = [
            'departemen'      => $departemenName,
            'id_mesin'    => $this->request->getGet('id_mesin') === 'all' ? null : ($this->request->getGet('id_mesin') ?: null),
            'line'        => $userLine ?: ($this->request->getGet('line') === 'all' ? null : ($this->request->getGet('line') ?: null)),
            'jenis_check' => $this->request->getGet('jenis_check') === 'all' ? null : ($this->request->getGet('jenis_check') ?: null),
            'bulan'       => $this->request->getGet('bulan') === 'all' ? null : ($this->request->getGet('bulan') ?: null),
            'pic'         => $this->request->getGet('pic') === 'all' ? null : ($this->request->getGet('pic') ?: null),
            'sort_by'     => $this->request->getGet('sort_by') ?: 'id_transaksi',
            'order'       => $this->request->getGet('order') ?: 'desc',
        ];
        
        $laporan = (new TransaksiCheckModel())->getLaporanDurasi($filters);

        $totalDurasi = 0;
        $count       = 0;
        foreach ($laporan as $l) {
            if ($l['durasi_detik'] !== null) {
                $totalDurasi += (int) $l['durasi_detik'];
                $count++;
            }
        }
        $rataDetik = $count > 0 ? intdiv($totalDurasi, $count) : 0;
        
        $html = view('laporan/durasi_pdf', [
            'title'           => 'Laporan Durasi Pengecekan',
            'laporan'         => $laporan,
            'rataDetik'       => $rataDetik,
            'selectedFilters' => $filters
        ]);

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Laporan_Durasi_Pengecekan.pdf';
        $dompdf->stream($filename, ['Attachment' => 0]);
        return;
    }

    public function durasiExcel()
    {
        $departemenName = has_role(Role::Leader->value) ? session()->get('departemen') : ($this->request->getGet('departemen') === 'all' ? null : ($this->request->getGet('departemen') ?: null));
        $userLine = has_role(Role::Leader->value) ? session()->get('line') : null;

        $filters = [
            'departemen'  => $departemenName,
            'id_mesin'    => $this->request->getGet('id_mesin') === 'all' ? null : ($this->request->getGet('id_mesin') ?: null),
            'line'        => $userLine ?: ($this->request->getGet('line') === 'all' ? null : ($this->request->getGet('line') ?: null)),
            'jenis_check' => $this->request->getGet('jenis_check') === 'all' ? null : ($this->request->getGet('jenis_check') ?: null),
            'bulan'       => $this->request->getGet('bulan') === 'all' ? null : ($this->request->getGet('bulan') ?: null),
            'pic'         => $this->request->getGet('pic') === 'all' ? null : ($this->request->getGet('pic') ?: null),
            'sort_by'     => $this->request->getGet('sort_by') ?: 'id_transaksi',
            'order'       => $this->request->getGet('order') ?: 'desc',
        ];

        helper('tanggal');
        $laporan = (new TransaksiCheckModel())->getLaporanDurasi($filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Durasi');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $dataStyle = [
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ];

        // Title row
        $sheet->mergeCells('A1:K1');
        $sheet->setCellValue('A1', 'LAPORAN DURASI PENGECEKAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Column headers row 3
        $headers = ['No', 'PIC', 'Mesin', 'Departemen', 'Line', 'Jenis', 'Kategori', 'Waktu Mulai', 'Waktu Selesai', 'Durasi', 'Status'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }
        $sheet->getStyle('A3:K3')->applyFromArray($headerStyle);

        $row = 4;
        $no  = 1;
        foreach ($laporan as $l) {
            $durasiDetik = $l['durasi_detik'] ?? null;
            if ($durasiDetik !== null) {
                $jam   = floor($durasiDetik / 3600);
                $menit = floor(($durasiDetik % 3600) / 60);
                $det   = $durasiDetik % 60;
                $durasiStr = $jam > 0
                    ? sprintf('%02d:%02d:%02d', $jam, $menit, $det)
                    : sprintf('%02d:%02d', $menit, $det);
            } else {
                $durasiStr = '-';
            }
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $l['nama_pic'] ?? ($l['nama_staff'] ?? '-'));
            $sheet->setCellValue('C' . $row, $l['no_mesin'] ?? '-');
            $sheet->setCellValue('D' . $row, $l['departemen_check'] ?? '-');
            $sheet->setCellValue('E' . $row, $l['line'] ?? '-');
            $sheet->setCellValue('F' . $row, $l['jenis_check'] === 'Preventive' ? 'Checklist Report' : ($l['jenis_check'] ?? '-'));
            $sheet->setCellValue('G' . $row, $l['kategori'] ?? '-');
            $sheet->setCellValue('H' . $row, !empty($l['waktu_mulai']) ? date('d/m/Y H:i', strtotime($l['waktu_mulai'])) : '-');
            $sheet->setCellValue('I' . $row, !empty($l['waktu_selesai']) ? date('d/m/Y H:i', strtotime($l['waktu_selesai'])) : '-');
            $sheet->setCellValue('J' . $row, $durasiStr);
            $sheet->setCellValue('K' . $row, $l['status'] ?? '-');
            $row++;
        }

        if ($row > 4) {
            $sheet->getStyle('A4:K' . ($row - 1))->applyFromArray($dataStyle);
            $sheet->getStyle('A4:A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        foreach (range('A', 'K') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filename = 'Laporan_Durasi_Pengecekan_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
