<?php

namespace App\Controllers;

use App\Enums\Lokasi;

use App\Models\MesinModel;

class ScanController extends BaseController
{
    protected MesinModel $mesinModel;

    public function __construct()
    {
        $this->mesinModel = new MesinModel();
    }

    /**
     * GET /scan
     * Menampilkan kamera web scanner.
     */
    public function index()
    {
        return view('scan/index', [
            'title' => 'Scan QR Code Mesin',
        ]);
    }

    /**
     * GET /scan/mesin/(:num)
     * Landing page mesin yang di-scan untuk memilih Preventive / Overhaul.
     */
    public function mesin(int $id)
    {
        $mesin = $this->mesinModel->find($id);

        if (!$mesin) {
            return redirect()->to('/dashboard')->with('error', 'Mesin tidak ditemukan.');
        }

        // Ubah lokasi ke format slug, misal Lokasi::MFG1->value -> 'mfg1'
        $lokasiSlug = strtolower(str_replace(' ', '', $mesin['lokasi']));

        return view('scan/mesin', [
            'title'      => 'Mesin Terdeteksi',
            'mesin'      => $mesin,
            'lokasiSlug' => $lokasiSlug,
        ]);
    }
}
