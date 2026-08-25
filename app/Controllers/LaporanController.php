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
        $laporan = (new \App\Models\TransaksiCheckModel())->getLaporanDurasi($filters);
        
        $totalDetik = 0;
        $countSelesai = 0;
        foreach ($laporan as $l) {
            if ($l['durasi_detik'] !== null) {
                $totalDetik += $l['durasi_detik'];
                $countSelesai++;
            }
        }
        $rataDetik = $countSelesai > 0 ? floor($totalDetik / $countSelesai) : 0;

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Durasi');

        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(25);
        $sheet->getColumnDimension('H')->setWidth(15);

        $borderThin = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];

        // Title row
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'LAPORAN DURASI PENGECEKAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Stat Box (like PDF)
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'Rata-rata Durasi Semua Transaksi (Berdasarkan Filter)');
        $sheet->getStyle('A3')->getFont()->getColor()->setARGB('FF666666');
        
        $sheet->mergeCells('A4:H4');
        $sheet->setCellValue('A4', gmdate('i \m\e\n\i\t s \d\e\t\i\k', $rataDetik));
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
        
        $sheet->getStyle('A3:H4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8F9FA');
        $sheet->getStyle('A3:H4')->getBorders()->getOutline()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

        // Column headers row 6
        $headers = ['NO', 'PIC', 'Mesin', 'Departemen / Line', 'Jenis Pengecekan', 'Waktu Mulai', 'Waktu Selesai', 'Durasi'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '6', $h);
            $col++;
        }
        $sheet->getStyle('A6:H6')->getFont()->setBold(true);
        $sheet->getStyle('A6:H6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
        $sheet->getStyle('A6:H6')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $row = 7;
        if (empty($laporan)) {
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->setCellValue("A{$row}", 'Belum ada data transaksi.');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row++;
        } else {
            $no  = 1;
            foreach ($laporan as $l) {
                $rawNamaDurasi = $l['nama_pic'] ?: ($l['nama_staff'] ?? '');
                $namaDurasiParts = explode(' - ', $rawNamaDurasi);
                $namaPicDurasi = end($namaDurasiParts) ?: $rawNamaDurasi;

                $deptLine = ($l['departemen_check'] ?? '-') . (!empty($l['line']) ? "\nLine: " . $l['line'] : '');
                $jenisCheck = $l['jenis_check'] === 'Preventive' ? 'Checklist Report' : 'Inspection Report';
                
                $mulai = !empty($l['waktu_mulai']) ? format_tanggal_indo($l['waktu_mulai'], true) . "\n" . date('H:i:s', strtotime($l['waktu_mulai'])) : '-';
                $selesai = !empty($l['waktu_selesai']) ? format_tanggal_indo($l['waktu_selesai'], true) . "\n" . date('H:i:s', strtotime($l['waktu_selesai'])) : 'Belum selesai';

                $durasiStr = '-';
                if ($l['durasi_detik'] !== null) {
                    $jam   = floor($l['durasi_detik'] / 3600);
                    $sisa  = $l['durasi_detik'] % 3600;
                    $menit = floor($sisa / 60);
                    $det   = $sisa % 60;
                    $waktuStrs = [];
                    if ($jam > 0) $waktuStrs[] = $jam . 'j';
                    if ($menit > 0) $waktuStrs[] = $menit . 'm';
                    if ($det > 0 || empty($waktuStrs)) $waktuStrs[] = $det . 's';
                    $durasiStr = implode(' ', $waktuStrs);
                }

                $sheet->setCellValue('A' . $row, $no++);
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->setCellValue('B' . $row, $namaPicDurasi);
                $sheet->setCellValue('C' . $row, $l['no_mesin'] ?? '-');
                
                $sheet->setCellValue('D' . $row, $deptLine);
                $sheet->getStyle('D' . $row)->getAlignment()->setWrapText(true);
                
                $sheet->setCellValue('E' . $row, $jenisCheck);
                $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                if ($jenisCheck === 'Checklist Report') {
                    $sheet->getStyle('E' . $row)->getFont()->getColor()->setARGB('FF0D6EFD');
                } else {
                    $sheet->getStyle('E' . $row)->getFont()->getColor()->setARGB('FF0DCAF0');
                }
                
                $sheet->setCellValue('F' . $row, $mulai);
                $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                
                $sheet->setCellValue('G' . $row, $selesai);
                $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                if ($selesai === 'Belum selesai') {
                    $sheet->getStyle('G' . $row)->getFont()->setItalic(true)->getColor()->setARGB('FF999999');
                }
                
                $sheet->setCellValue('H' . $row, $durasiStr);
                $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H' . $row)->getFont()->setBold(true);
                
                $row++;
            }
        }

        $sheet->getStyle('A6:H' . ($row - 1))->applyFromArray($borderThin);
        $sheet->getStyle('A6:H' . ($row - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        $filename = 'Laporan_Durasi_Pengecekan_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        exit;
    }
}
