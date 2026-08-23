<?php

namespace App\Controllers;

use App\Enums\Departemen;

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

        // Ubah departemen dan plant ke format slug
        $departemenSlug = strtolower(str_replace(' ', '-', $mesin['departemen']));
        $plantSlug = strtolower(str_replace(' ', '-', $mesin['plant'] ?? 'Plant 1'));

        return view('scan/mesin', [
            'title'          => 'Mesin Terdeteksi',
            'mesin'          => $mesin,
            'plantSlug'       => $plantSlug,
            'departemenSlug' => $departemenSlug,
        ]);
    }
}
