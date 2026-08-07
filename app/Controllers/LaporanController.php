<?php

namespace App\Controllers;

use App\Enums\Role;
use App\Enums\Lokasi;

use App\Models\TransaksiCheckModel;

class LaporanController extends BaseController
{
    public function durasi()
    {
        $role = session()->get('role');
        $lokasiName = ($role === Role::Leader->value) ? session()->get('lokasi') : ($this->request->getGet('lokasi') === 'all' ? null : ($this->request->getGet('lokasi') ?: null));
        $userLine = ($role === Role::Leader->value) ? session()->get('line') : null;

        $filters = [
            'lokasi'      => $lokasiName,
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
        $daftarMesin = $mesinModel->getByLokasi($lokasiName);

        $availableLines = [];
        if ($lokasiName === Lokasi::MFG1->value) {
            $availableLines = ['Line 1', 'Line 2', 'Line 3'];
        } elseif ($lokasiName === Lokasi::MFG2->value) {
            $availableLines = ['CG', 'Second'];
        }

        $transaksiModel = new \App\Models\TransaksiCheckModel();
        $rawPics = $transaksiModel->getAvailablePics($lokasiName ?: null);
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
        $role = session()->get('role');
        $lokasiName = ($role === Role::Leader->value) ? session()->get('lokasi') : ($this->request->getGet('lokasi') === 'all' ? null : ($this->request->getGet('lokasi') ?: null));
        $userLine = ($role === Role::Leader->value) ? session()->get('line') : null;

        $filters = [
            'lokasi'      => $lokasiName,
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
}
