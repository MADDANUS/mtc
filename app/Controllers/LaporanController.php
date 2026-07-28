<?php

namespace App\Controllers;

use App\Models\TransaksiCheckModel;

class LaporanController extends BaseController
{
    public function durasi()
    {
        $role = session()->get('role');
        $lokasiName = ($role === 'leader') ? session()->get('lokasi') : ($this->request->getGet('lokasi') === 'all' ? null : ($this->request->getGet('lokasi') ?: null));
        $userLine = ($role === 'leader') ? session()->get('line') : null;

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
        
        // Fetch dropdown options
        $mesinModel = new \App\Models\MesinModel();
        $daftarMesin = $mesinModel->getByLokasi($lokasiName);

        $availableLines = [];
        if ($lokasiName === 'MFG 1') {
            $availableLines = ['Line 1', 'Line 2', 'Line 3'];
        } elseif ($lokasiName === 'MFG 2') {
            $availableLines = ['CG', 'Second'];
        }

        $db = \Config\Database::connect();
        $picQuery = $db->table('transaksi_check')
                       ->select('transaksi_check.nama_pic, users.nama as nama_staff')
                       ->join('users', 'users.id = transaksi_check.id_user');
        if (!empty($lokasiName)) {
            $picQuery->where('transaksi_check.lokasi_check', $lokasiName);
        }
        $rawPics = $picQuery->distinct()->get()->getResultArray();
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
        for ($i = 0; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $val  = $time->format('Y-m');
            $label = $time->toLocalizedString('MMMM yyyy');
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
        ]);
    }

    public function durasiPdf()
    {
        $role = session()->get('role');
        $lokasiName = ($role === 'leader') ? session()->get('lokasi') : ($this->request->getGet('lokasi') === 'all' ? null : ($this->request->getGet('lokasi') ?: null));
        $userLine = ($role === 'leader') ? session()->get('line') : null;

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
        $dompdf->stream($filename, ['Attachment' => true]);
        return;
    }
}
