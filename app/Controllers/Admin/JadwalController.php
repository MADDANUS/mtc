<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JadwalPreventiveModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class JadwalController extends BaseController
{
    protected JadwalPreventiveModel $jadwalModel;

    public function __construct()
    {
        $this->jadwalModel = new JadwalPreventiveModel();
    }

    /**
     * GET /admin/jadwal
     */
    public function index()
    {
        $categories = [
            'Penerangan'     => 'Penerangan',
            'Kabel dan Pipa' => 'Kabel dan Pipa',
            'Angin Bocor'    => 'Angin Bocor',
            'Bearing Cam'    => 'Bearing Cam',
            'Gearbox'        => 'Gearbox',
            'Belt Cam'       => 'Belt Cam',
        ];

        // Buat list 12 bulan ke depan untuk dropdown
        $months = [];
        for ($i = -2; $i < 10; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->addMonths($i);
            $val  = $time->format('Y-m');
            $label = $time->toLocalizedString('MMMM yyyy');
            $months[$val] = $label;
        }

        return view('admin/jadwal/index', [
            'title'      => 'Jadwal Pengecekan Preventive',
            'categories' => $categories,
            'months'     => $months,
        ]);
    }

    /**
     * GET /admin/jadwal/events
     */
    public function events()
    {
        $schedules = $this->jadwalModel->findAll();

        $events = [];
        foreach ($schedules as $s) {
            $periodeKe = (int) $s['periode_ke'];

            // Hitung Senin dan Sabtu (exclusive end) dari tanggal_rencana yang sebenarnya
            $tglRencana = strtotime($s['tanggal_rencana']);
            $dayOfWeek  = (int) date('N', $tglRencana); // 1=Senin ... 7=Minggu
            $mondayTs   = strtotime('-' . ($dayOfWeek - 1) . ' days', $tglRencana);
            $saturdayTs = strtotime('+5 days', $mondayTs);

            $startDate = date('Y-m-d', $mondayTs);
            $endDate   = date('Y-m-d', $saturdayTs); // FullCalendar end exclusive = Sabtu → bar sampai Jumat

            // Warna penanda mfg
            $color = $s['lokasi'] === 'MFG 1' ? '#0d6efd' : '#198754'; // Biru mfg 1, hijau mfg 2

            // Label: tampilkan rentang tanggal Senin-Jumat di judul
            $fridayTs = strtotime('+4 days', $mondayTs);
            $label = esc($s['lokasi'] . ' - ' . $s['kategori'] . ' (' . date('d', $mondayTs) . '-' . date('d', $fridayTs) . '/' . date('m', $mondayTs) . ')');

            $events[] = [
                'id'              => (int) $s['id_jadwal'],
                'title'           => $label,
                'start'           => $startDate,
                'end'             => $endDate,
                'allDay'          => true,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'lokasi'          => $s['lokasi'],
                    'kategori'        => $s['kategori'],
                    'bulanTahun'      => $s['bulan_tahun'],
                    'periodeKe'       => $s['periode_ke'],
                    'tanggalRencana'  => $s['tanggal_rencana']
                ]
            ];
        }

        return $this->response->setJSON($events);
    }

    /**
     * POST /admin/jadwal/store
     */
    public function store()
    {
        if (!in_array(session()->get('role'), ['admin', 'member'], true)) {
            return redirect()->back()->with('error', 'Hanya Admin dan Member yang dapat membuat jadwal.');
        }

        $rules = [
            'lokasi'          => 'required|in_list[MFG 1,MFG 2]',
            'kategori'        => 'required',
            'tanggal_rencana' => 'required|valid_date[Y-m-d]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $lokasi         = $this->request->getPost('lokasi');
        $kategori       = $this->request->getPost('kategori');
        $tanggalRencana = $this->request->getPost('tanggal_rencana');

        // Hitung bulan_tahun dan periode_ke otomatis dari tanggal_rencana
        $bulanTahun = date('Y-m', strtotime($tanggalRencana));
        $day        = (int) date('d', strtotime($tanggalRencana));
        $periodeKe  = intval(($day - 1) / 7) + 1;
        if ($periodeKe > 5) {
            $periodeKe = 5;
        }

        // Cek duplikasi bulanan: Kategori per lokasi hanya boleh dijadwalkan SATU kali per bulan
        $exist = $this->jadwalModel->where('lokasi', $lokasi)
                                   ->where('kategori', $kategori)
                                   ->where('bulan_tahun', $bulanTahun)
                                   ->first();

        if ($exist) {
            $existDate = date('d/m/Y', strtotime($exist['tanggal_rencana']));
            return redirect()->back()->withInput()->with('error', "Jadwal untuk {$lokasi} - {$kategori} pada bulan ini sudah terdaftar (tanggal {$existDate}, Pekan ke-{$exist['periode_ke']}). Hapus jadwal lama terlebih dahulu jika ingin mengubah.");
        }

        $this->jadwalModel->insert([
            'lokasi'          => $lokasi,
            'kategori'        => $kategori,
            'bulan_tahun'     => $bulanTahun,
            'periode_ke'      => $periodeKe,
            'tanggal_rencana' => $tanggalRencana,
        ]);

        return redirect()->to('/admin/jadwal')->with('success', 'Jadwal preventive berhasil disimpan.');
    }

    /**
     * POST /admin/jadwal/delete/(:num)
     */
    public function delete(int $id)
    {
        if (!in_array(session()->get('role'), ['admin', 'member'], true)) {
            return redirect()->back()->with('error', 'Hanya Admin dan Member yang dapat menghapus jadwal.');
        }

        $schedule = $this->jadwalModel->find($id);

        if (!$schedule) {
            return redirect()->back()->with('error', 'Jadwal tidak ditemukan.');
        }

        // Cek apakah sudah ada checklist (transaksi_check) yang dibuat untuk jadwal ini
        $transaksiModel = new \App\Models\TransaksiCheckModel();
        $bulanTahun = $schedule['bulan_tahun']; // e.g., '2026-07'
        
        $cekTransaksi = $transaksiModel->where('jenis_check', 'Preventive')
                                       ->where('lokasi_check', $schedule['lokasi'])
                                       ->where('kategori', $schedule['kategori'])
                                       ->like('created_at', $bulanTahun . '-', 'after')
                                       ->first();

        if ($cekTransaksi) {
            return redirect()->back()->with('error', 'Gagal dihapus! Sudah ada mesin yang diisi pengecekannya (checklist) pada jadwal kategori ini.');
        }

        $this->jadwalModel->delete($id);

        return redirect()->to('/admin/jadwal')->with('success', 'Jadwal preventive berhasil dihapus.');
    }

    /**
     * GET /admin/jadwal/template
     */
    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Lokasi (MFG 1 / MFG 2)');
        $sheet->setCellValue('B1', 'Kategori (Cth: Penerangan)');
        $sheet->setCellValue('C1', 'Rentang Tanggal (Cth: 27/07/2026-31/07/2026)');

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Template_Import_Jadwal.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * GET /admin/jadwal/export
     */
    public function export()
    {
        $schedules = $this->jadwalModel->orderBy('tanggal_rencana', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Lokasi');
        $sheet->setCellValue('B1', 'Kategori');
        $sheet->setCellValue('C1', 'Rentang Tanggal');
        $sheet->setCellValue('D1', 'Bulan Tahun');
        $sheet->setCellValue('E1', 'Pekan Ke');

        $row = 2;
        foreach ($schedules as $s) {
            // Hitung Senin dan Jumat (untuk format Rentang Tanggal)
            $tglRencana = strtotime($s['tanggal_rencana']);
            $dayOfWeek  = (int) date('N', $tglRencana);
            $mondayTs   = strtotime('-' . ($dayOfWeek - 1) . ' days', $tglRencana);
            $fridayTs   = strtotime('+4 days', $mondayTs);

            $rentang = date('d/m/Y', $mondayTs) . '-' . date('d/m/Y', $fridayTs);

            $sheet->setCellValue('A' . $row, $s['lokasi']);
            $sheet->setCellValue('B' . $row, $s['kategori']);
            $sheet->setCellValue('C' . $row, $rentang);
            $sheet->setCellValue('D' . $row, $s['bulan_tahun']);
            $sheet->setCellValue('E' . $row, $s['periode_ke']);
            $row++;
        }

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $filename = 'Export_Jadwal_Preventive.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * POST /admin/jadwal/import
     */
    public function import()
    {
        if (!in_array(session()->get('role'), ['admin', 'member'], true)) {
            return redirect()->back()->with('error', 'Hanya Admin dan Member yang dapat mengimpor jadwal.');
        }

        $file = $this->request->getFile('file_excel');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Silakan pilih file Excel yang valid.');
        }

        $extension = $file->getClientExtension();
        if (!in_array($extension, ['xls', 'xlsx', 'csv'])) {
            return redirect()->back()->with('error', 'Format file tidak didukung. Gunakan xlsx, xls, atau csv.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        $successCount = 0;
        $skipCount = 0;

        // Skip row 1 (header)
        for ($i = 1; $i < count($sheetData); $i++) {
            $row = $sheetData[$i];
            
            $lokasi = trim($row[0] ?? '');
            $kategori = trim($row[1] ?? '');
            $rentangTanggal = trim($row[2] ?? '');

            if (empty($lokasi) || empty($kategori) || empty($rentangTanggal)) {
                $skipCount++;
                continue;
            }

            // Parse rentang (contoh: 27/07/2026-31/07/2026) -> Ambil tanggal pertama
            $parts = explode('-', $rentangTanggal);
            $startDateRaw = trim($parts[0]);
            
            // Konversi dari DD/MM/YYYY menjadi Y-m-d
            $dateObj = \DateTime::createFromFormat('d/m/Y', $startDateRaw);
            if (!$dateObj) {
                // Coba parse format bebas jika gagal
                $dateObj = strtotime($startDateRaw);
                if (!$dateObj) {
                    $skipCount++;
                    continue;
                }
                $tanggalRencana = date('Y-m-d', $dateObj);
            } else {
                $tanggalRencana = $dateObj->format('Y-m-d');
            }

            $bulanTahun = date('Y-m', strtotime($tanggalRencana));
            $day        = (int) date('d', strtotime($tanggalRencana));
            $periodeKe  = intval(($day - 1) / 7) + 1;
            if ($periodeKe > 5) $periodeKe = 5;

            // Cek duplikasi bulanan
            $exist = $this->jadwalModel->where('lokasi', $lokasi)
                                       ->where('kategori', $kategori)
                                       ->where('bulan_tahun', $bulanTahun)
                                       ->first();

            if ($exist) {
                $skipCount++;
                continue;
            }

            $this->jadwalModel->insert([
                'lokasi'          => $lokasi,
                'kategori'        => $kategori,
                'bulan_tahun'     => $bulanTahun,
                'periode_ke'      => $periodeKe,
                'tanggal_rencana' => $tanggalRencana,
            ]);
            $successCount++;
        }

        $msg = "Impor selesai. {$successCount} jadwal berhasil ditambahkan.";
        if ($skipCount > 0) {
            $msg .= " {$skipCount} baris dilewati (kosong, format salah, atau sudah ada di bulan yang sama).";
        }

        return redirect()->to('/admin/jadwal')->with('success', $msg);
    }
}
