<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LogAuditLaporanModel;

class AuditLogController extends BaseController
{
    public function index()
    {
        if (!has_role(\App\Enums\Role::Admin->value)) {
            return redirect()->to('/dashboard')->with('error', 'Akses ditolak.');
        }

        $model = new LogAuditLaporanModel();
        
        $paramModel = new \App\Models\ParameterCheckModel();
        $params = $paramModel->findAll();
        $paramMap = [];
        foreach ($params as $p) {
            $paramMap[$p['id_parameter']] = $p; // Full object
        }

        $mesinModel = new \App\Models\MesinModel();
        $mesins = $mesinModel->findAll();
        $mesinMap = [];
        foreach ($mesins as $m) {
            $mesinMap[$m['id_mesin']] = $m;
        }
        
        $page = (int) $this->request->getVar('page') ?: 1;
        $perPage = (int) $this->request->getVar('per_page') ?: 15;
        
        $filterKategori = $this->request->getVar('filter_kategori') ?: '';
        $filterAksi = $this->request->getVar('filter_aksi') ?: '';
        $filterBulan = $this->request->getVar('filter_bulan') ?? date('Y-m');
        if ($filterBulan === 'all') $filterBulan = '';
        $filterUser = $this->request->getVar('filter_user') ?: '';
        $filterDokumen = $this->request->getVar('filter_dokumen') ?: '';
        $filterMesin = $this->request->getVar('filter_mesin') ?: '';

        if ($filterKategori) {
            $model->where('kategori_dokumen', $filterKategori);
        }
        if ($filterAksi) {
            $model->where('aksi', $filterAksi);
        }
        if ($filterBulan) {
            $model->like('waktu_eksekusi', $filterBulan . '-', 'after');
        }
        if ($filterUser) {
            $model->where('dieksekusi_oleh', $filterUser);
        }
        if ($filterMesin) {
            $model->where('no_mesin', $filterMesin);
        }
        if ($filterDokumen) {
            $model->like('detail_perubahan', '"id_transaksi":"' . $filterDokumen . '"');
        }

        $totalItems = $model->countAllResults(false);
        $totalPages = (int) ceil($totalItems / $perPage);
        $logs = $model->orderBy('waktu_eksekusi', 'DESC')->findAll($perPage, ($page - 1) * $perPage);

        // Fetch distinct categories for dropdown
        $db = \Config\Database::connect();
        $cats = $db->table('log_audit_laporan')->select('kategori_dokumen')->distinct()->orderBy('kategori_dokumen', 'ASC')->get()->getResultArray();
        $availableCategories = array_column($cats, 'kategori_dokumen');

        // Fetch distinct users and mesins
        $usersResult = $db->table('log_audit_laporan')->select('dieksekusi_oleh')->distinct()->orderBy('dieksekusi_oleh', 'ASC')->get()->getResultArray();
        $availableUsers = array_column($usersResult, 'dieksekusi_oleh');
        
        $mesinsResult = $db->table('log_audit_laporan')->select('no_mesin')->where('no_mesin !=', '-')->distinct()->orderBy('no_mesin', 'ASC')->get()->getResultArray();
        $availableMesins = array_column($mesinsResult, 'no_mesin');

        // Build bulanList
        $bulanList = [];
        for ($i = -1; $i < 12; $i++) {
            $time = \CodeIgniter\I18n\Time::now()->subMonths($i);
            $bulanList[$time->format('Y-m')] = format_bulan_indo($time->format('Y-m'));
        }

        $buildQuery = function ($add = []) {
            $get = $_GET;
            $get = array_merge($get, $add);
            return '?' . http_build_query($get);
        };
        
        $data = [
            'title' => 'Log Riwayat Dokumen',
            'logs' => $logs,
            'paramMap' => $paramMap,
            'mesinMap' => $mesinMap,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'buildQuery' => $buildQuery,
            'filterKategori' => $filterKategori,
            'filterAksi' => $filterAksi,
            'filterBulan' => $filterBulan,
            'filterUser' => $filterUser,
            'filterDokumen' => $filterDokumen,
            'filterMesin' => $filterMesin,
            'availableCategories' => $availableCategories,
            'availableUsers' => $availableUsers,
            'availableMesins' => $availableMesins,
            'bulanList' => $bulanList
        ];

        return view('admin/audit_log/index', $data);
    }
}
