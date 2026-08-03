<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\JenisCheck;

use App\Models\TransaksiCheckModel;
use App\Models\TransaksiCheckDetailModel;
use CodeIgniter\I18n\Time;

class RiwayatService
{
    public function validateLeaderAccess(?string $lokasiName): ?string
    {
        if (session()->get('role') === Role::Leader->value) {
            $userLokasi = session()->get('lokasi');
            if ($userLokasi && $userLokasi !== $lokasiName) {
                if ($lokasiName === null) {
                    return $userLokasi;
                } else {
                    throw new \Exception('Akses ditolak.');
                }
            }
        }
        return $lokasiName;
    }

    public function getPdfAllData(?string $lokasiName, array $getParams): array
    {
        $transaksiModel = new TransaksiCheckModel();
        $filters        = $this->buildPdfFilters($lokasiName, $getParams);
        $riwayat        = $transaksiModel->getRiwayatFiltered($filters);
        $allReports     = $this->buildAllReports($riwayat);

        $jenisLabel = $filters['jenis_check'] === JenisCheck::Preventive->value
            ? 'Checklist Report'
            : ($filters['jenis_check'] === JenisCheck::Overhaul->value ? 'Inspection Report' : 'Pengecekan');

        return [
            'title'      => "Riwayat {$jenisLabel} - {$lokasiName}",
            'allReports' => $allReports,
            'filters'    => $filters,
            'lokasiName' => $lokasiName,
            'jenisLabel' => $jenisLabel
        ];
    }

    public function getDetailData(int $id, ?string $roleSession): ?array
    {
        $transaksiModel = new TransaksiCheckModel();
        $header         = $transaksiModel->getDetailTransaksi($id);

        if (! $header) {
            return null;
        }

        $approvalStatus = $header['status'] ?? 'Pending';

        if ($roleSession === Role::Sheadprd->value && $approvalStatus === 'Pending') {
            throw new \Exception('Dokumen ini belum siap (Masih menunggu Leader).');
        }
        if ($roleSession === Role::Sheadmtc->value && in_array($approvalStatus, ['Pending', 'Approved L1'], true)) {
            throw new \Exception('Dokumen ini belum siap (Masih menunggu SHead Produksi).');
        }

        [$details] = $this->fetchHeaderAndDetails($id, $header);
        $durasiDetik   = $this->calculateDurasiDetik($header);
        $leaderPicList = $this->resolveLeaderPicList($roleSession);

        return [
            'header'       => $header,
            'details'      => $details,
            'durasiDetik'  => $durasiDetik,
            'staffPicList' => (new \App\Models\PicModel())->where('role_pic', 'Staff')->findAll(),
            'leaderPicList'=> $leaderPicList,
        ];
    }

    public function getRedirectDetailUrl(array $getParams): ?string
    {
        $idMesin  = $getParams['id_mesin'] ?? null;
        $kategori = $getParams['kategori'] ?? null;
        $bulan    = $getParams['bulan'] ?? null;
        $line     = $getParams['line'] ?? null;
        $lokasi   = $getParams['lokasi'] ?? null;

        $transaksiModel = new \App\Models\TransaksiCheckModel();
        $tx = $transaksiModel->getLatestIdByMesinAndKategori($idMesin, $kategori, $bulan);

        if ($tx) {
            $qsArray = [
                'from'     => 'kontrol',
                'lokasi'   => $lokasi,
                'line'     => $line,
                'kategori' => $kategori,
                'bulan'    => $bulan,
            ];
            $qsSummary = $getParams['qs_summary'] ?? null;
            if ($qsSummary) {
                $qsArray['qs_summary'] = $qsSummary;
            }
            $qs = http_build_query($qsArray);
            return '/riwayat/' . $tx['id_transaksi'] . '?' . $qs;
        }
        
        return null;
    }

    public function getPdfData(int $id): ?array
    {
        $transaksiModel = new TransaksiCheckModel();
        $header         = $transaksiModel->getDetailTransaksi($id);

        if (! $header) {
            return null;
        }

        [$details] = $this->fetchHeaderAndDetails($id, $header);
        $durasiDetik = $this->calculateDurasiDetik($header);

        return [
            'header'      => $header,
            'details'     => $details,
            'durasiDetik' => $durasiDetik,
        ];
    }

    public function getEditData(int $id): ?array
    {
        $transaksiModel = new TransaksiCheckModel();
        $header = $transaksiModel->getDetailTransaksi($id);
        if (!$header) {
            return null;
        }

        $detailModel = new \App\Models\TransaksiCheckDetailModel();
        $details = $detailModel->where('id_transaksi', $id)->findAll();

        $detailsMap = [];
        foreach ($details as $d) {
            $detailsMap[$d['id_parameter']] = $d;
        }

        $mesinModel = new \App\Models\MesinModel();
        $parameterModel = new \App\Models\ParameterCheckModel();
        
        $lokasiSlug = strtolower(str_replace(' ', '', $header['lokasi_check']));
        $jenisSlug = strtolower(str_replace(' ', '-', $header['jenis_check']));
        
        $categoryMap = [
            'penerangan' => 'Penerangan',
            'panel' => 'Panel',
            'inverter' => 'Inverter',
            'motor' => 'Motor',
            'sensor' => 'Sensor',
            'pintu' => 'Pintu',
            'safety' => 'Safety',
            'fan' => 'Fan / Blower',
            'lain' => 'Lain-Lain'
        ];
        $categorySlug = array_search($header['kategori'], $categoryMap, true) ?: 'penerangan';
        
        $waktuMulai = new \CodeIgniter\I18n\Time($header['waktu_mulai']);

        return [
            'title'             => "Edit Pengecekan {$header['jenis_check']} - {$header['kategori']}",
            'lokasiSlug'        => $lokasiSlug,
            'lokasiName'        => $header['lokasi_check'],
            'jenisSlug'         => $jenisSlug,
            'jenisName'         => $header['jenis_check'],
            'categorySlug'      => $categorySlug,
            'categoryName'      => $header['kategori'],
            'daftarMesin'       => $mesinModel->getByLokasi($header['lokasi_check']),
            'rows'              => $parameterModel->getFormRows($header['lokasi_check'], $header['jenis_check'], $header['kategori']),
            'masterPic'         => array_filter((new \App\Models\PicModel())->findAll(), function($p) {
                return strpos(strtolower(str_replace(' ', '', $p['role_pic'] ?? '')), Role::Leader->value) === false;
            }),
            'staffPic'          => (new \App\Models\PicModel())->where('role_pic', 'Staff')->findAll(),
            'namaPic'           => $header['nama_pic'],
            'namaStaff'         => $header['nama_staff'],
            'waktuMulai'        => $header['waktu_mulai'],
            'waktuMulaiDisplay' => $waktuMulai->toLocalizedString('dd MMMM yyyy, HH:mm:ss'),
            'idMesin'           => $header['id_mesin'],
            'isEdit'            => true,
            'idTransaksi'       => $id,
            'detailsMap'        => $detailsMap,
        ];
    }

    public function deleteTransaksi(int $id): bool
    {
        $transaksiModel = new TransaksiCheckModel();
        $header = $transaksiModel->find($id);
        if (!$header) {
            return false;
        }

        $detailModel = new \App\Models\TransaksiCheckDetailModel();
        $details = $detailModel->where('id_transaksi', $id)->findAll();

        $uploadPath = FCPATH . 'uploads/abnormal/';
        foreach ($details as $d) {
            if ($d['foto_abnormal'] && file_exists($uploadPath . $d['foto_abnormal'])) {
                @unlink($uploadPath . $d['foto_abnormal']);
            }
            if ($d['foto_abnormal_2'] && file_exists($uploadPath . $d['foto_abnormal_2'])) {
                @unlink($uploadPath . $d['foto_abnormal_2']);
            }
        }

        $transaksiModel->delete($id);
        return true;
    }

    public function updateTransaksi(int $id, $request, $validation)
    {
        if (session()->get('role') !== Role::Admin->value) {
            return ["status" => false, "message" => 'Akses ditolak.'];
        }

        $transaksiModel = new TransaksiCheckModel();
        $header = $transaksiModel->find($id);
        if (!$header) {
            return ["status" => false, "message" => 'Transaksi tidak ditemukan.'];
        }

        $idMesin      = (int) $request->getPost('id_mesin');
        $namaPic      = $request->getPost('nama_pic');
        $waktuMulai   = $request->getPost('waktu_mulai');
        $kategoriName = $request->getPost('kategori');
        $waktuSelesai = $header['waktu_selesai'];

        $hasilCheck = $request->getPost('hasil_check') ?? [];
        $ulasan     = $request->getPost('ulasan') ?? [];

        $db = \Config\Database::connect();
        $db->transStart();

        $transaksiModel->update($id, [
            'id_mesin'    => $idMesin,
            'nama_pic'    => $namaPic,
            'kategori'    => $kategoriName,
            'waktu_mulai' => $waktuMulai,
            'status'      => 'Pending',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        $this->reinsertDetails($id, $idMesin, $hasilCheck, $ulasan, $request, $waktuSelesai);

        if (strtolower($header['jenis_check']) === 'overhaul') {
            $this->updateOverhaulExtra($id, $request);
        }

        if (strtolower($header['jenis_check']) === 'preventive' || strtolower($header['jenis_check']) === 'checklist report') {
            $this->updateChecklistKontrolOnEdit($header, $idMesin, $hasilCheck, $ulasan, $waktuSelesai);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ["status" => false, "message" => 'Terjadi kesalahan saat mengupdate riwayat.'];
        }

        return ["status" => true, "message" => 'Riwayat berhasil diupdate.'];
    }

    /**
     * POST /riwayat/delete/(:num)
     * Menghapus riwayat pengecekan (Khusus Admin).
     */
    
    public function approveTransaksi($idTransaksi, $request)
    {
        $role = session()->get('role');
        if (!in_array($role, [Role::Member->value, Role::Sheadprd->value, Role::Sheadmtc->value, Role::Admin->value, Role::Leader->value], true)) {
            return ["status" => false, "message" => 'Anda tidak memiliki akses untuk menyetujui laporan.'];
        }

        $transaksiModel = new TransaksiCheckModel();
        $transaksi = $transaksiModel->find($idTransaksi);

        if (!$transaksi) {
            return ["status" => false, "message" => 'Laporan tidak ditemukan.'];
        }

        if ($role === Role::Leader->value) {
            $mesinModel = new \App\Models\MesinModel();
            $mesinInfo  = $mesinModel->find($transaksi['id_mesin']);
            if ($mesinInfo) {
                if (session()->get('lokasi') && $mesinInfo['lokasi'] !== session()->get('lokasi')) {
                    return ["status" => false, "message" => 'Anda hanya dapat menyetujui laporan dari mesin di lokasi ' . session()->get('lokasi')];
                }
                if (session()->get('line') && strtolower($mesinInfo['line']) !== strtolower(session()->get('line'))) {
                    return ["status" => false, "message" => 'Akses ditolak! Mesin ini tidak berada di ' . session()->get('line') . ' yang menjadi tanggung jawab Anda.'];
                }
            }
        }

        if ($transaksi['status'] === 'Approved') {
            return ["status" => false, "message" => 'Laporan ini sudah disetujui sepenuhnya.'];
        }

        $jenisSlug    = strtolower(str_replace(' ', '-', $transaksi['jenis_check']));
        $now          = date('Y-m-d H:i:s');
        $userId       = session()->get('user_id');
        $waktuSelesai = $transaksi['waktu_selesai'] ?? $now;

        $result = $this->buildApprovalUpdateData($role, $jenisSlug, $transaksi, $request, $now, $userId);
        if (isset($result['status'])) {
            return $result;
        }
        [$updateData, $newStatus] = $result;

        $db = \Config\Database::connect();
        $db->transStart();

        $transaksiModel->update($idTransaksi, $updateData);

        if ($newStatus === 'Approved') {
            $this->insertLaporanAbnormalOnApprove($idTransaksi, $transaksi, $waktuSelesai);
            $this->upsertCeklisKontrolOnApprove($idTransaksi, $transaksi, $waktuSelesai);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ["status" => false, "message" => 'Gagal memproses persetujuan laporan.'];
        }

        if ($newStatus === 'Approved') {
            return ["status" => true, "message" => 'Laporan berhasil disetujui sepenuhnya. Data kini masuk ke Checklist Control dan Laporan Abnormal jika ada.'];
        }
        return ["status" => true, "message" => 'Laporan berhasil disetujui (Tahap: ' . $newStatus . '). Menunggu persetujuan selanjutnya.'];
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Hitung overall status check: Δ > V > X (X hanya jika SEMUA X).
     */
    private function calculateOverallStatus(array $hasilCheck): string
    {
        $hasTriangle = false;
        $allAreX     = count($hasilCheck) > 0;

        foreach ($hasilCheck as $hasil) {
            if ($hasil === 'Δ') {
                $hasTriangle = true;
                $allAreX     = false;
            } elseif ($hasil === 'V') {
                $allAreX = false;
            } elseif ($hasil !== 'X') {
                $allAreX = false;
            }
        }

        if ($allAreX) {
            return 'X';
        }
        return $hasTriangle ? 'Δ' : 'V';
    }

    /**
     * Tentukan $updateData dan $newStatus berdasarkan role dan jenis transaksi.
     * Return [[$updateData, $newStatus]] jika OK, atau ['status'=>false, 'message'=>...] jika error.
     */
    private function buildApprovalUpdateData(string $role, string $jenisSlug, array $transaksi, $request, string $now, $userId): array
    {
        if ($jenisSlug === 'overhaul') {
            if ($role === Role::Admin->value) {
                return [['status' => 'Approved', 'approved_by' => $userId, 'approved_at' => $now], 'Approved'];
            }
            if ($role === Role::Leader->value) {
                if ($transaksi['status'] !== 'Pending') {
                    return ["status" => false, "message" => 'Laporan sudah diperiksa (bukan status Pending).'];
                }
                $leaderNama = $request->getPost('leader_nama');
                if (empty(trim($leaderNama ?? ''))) {
                    return ["status" => false, "message" => 'Nama Leader wajib diisi.'];
                }
                return [[
                    'status'         => 'Approved L1',
                    'approval_l1_by' => $userId,
                    'leader_nama'    => trim($leaderNama),
                    'approval_l1_at' => $now,
                ], 'Approved L1'];
            }
            if ($role === Role::Sheadprd->value) {
                if ($transaksi['status'] !== 'Approved L1') {
                    return ["status" => false, "message" => 'Laporan belum diperiksa oleh Leader.'];
                }
                return [[
                    'status'         => 'Approved L2',
                    'approval_l2_by' => $userId,
                    'approval_l2_at' => $now,
                ], 'Approved L2'];
            }
            if ($role === Role::Sheadmtc->value) {
                if ($transaksi['status'] !== 'Approved L2') {
                    return ["status" => false, "message" => 'Laporan belum disetujui oleh S. Head Produksi.'];
                }
                return [['status' => 'Approved', 'approved_by' => $userId, 'approved_at' => $now], 'Approved'];
            }
            return ["status" => false, "message" => 'Role Anda tidak memiliki akses persetujuan untuk laporan Overhaul.'];
        }
        // PREVENTIVE
        if (!in_array($role, [Role::Admin->value, Role::Member->value], true)) {
            return ["status" => false, "message" => 'Hanya Admin atau Member MTC yang dapat menyetujui laporan Preventive.'];
        }
        $picLineNama = $request->getPost('pic_line_nama');
        if (empty(trim($picLineNama ?? ''))) {
            return ["status" => false, "message" => 'Nama PIC Line wajib diisi.'];
        }
        return [[
            'status'        => 'Approved',
            'approved_by'   => $userId,
            'pic_line_nama' => trim($picLineNama),
            'approved_at'   => $now,
        ], 'Approved'];
    }

    /**
     * Insert laporan_abnormal untuk semua detail ber-status Δ saat approve final.
     */
    private function insertLaporanAbnormalOnApprove(int $idTransaksi, array $transaksi, string $waktuSelesai): void
    {
        $detailModel    = new TransaksiCheckDetailModel();
        $parameterModel = new \App\Models\ParameterCheckModel();
        $details        = $detailModel->where('id_transaksi', $idTransaksi)->findAll();

        foreach ($details as $d) {
            if ($d['hasil_check'] !== 'Δ') {
                continue;
            }
            $paramInfo      = $parameterModel->find((int) $d['id_parameter']);
            $pointCheckName = $paramInfo ? $paramInfo['point_check'] : 'Parameter #' . $d['id_parameter'];
            $abnormalDesc   = trim($d['ulasan'] ?? '');
            if (empty($abnormalDesc)) {
                $abnormalDesc = 'Ditemukan kondisi abnormal (Δ)';
            }
            (new \App\Models\LaporanAbnormalModel())->insert([
                'id_transaksi'       => $idTransaksi,
                'id_detail'          => $d['id_detail'],
                'id_mesin'           => $transaksi['id_mesin'],
                'point_check'        => $pointCheckName,
                'abnormal_condition' => $abnormalDesc,
                'pengecekan_tanggal' => date('Y-m-d', strtotime($waktuSelesai)),
                'pengecekan_pic'     => $transaksi['nama_pic'],
                'foto_abnormal'      => $d['foto_abnormal'] ?? null,
                'foto_abnormal_2'    => $d['foto_abnormal_2'] ?? null,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /**
     * Upsert baris ceklis_kontrol setelah approve final (hanya Preventive).
     */
    private function upsertCeklisKontrolOnApprove(int $idTransaksi, array $transaksi, string $waktuSelesai): void
    {
        $jenisSlug = strtolower(str_replace(' ', '-', $transaksi['jenis_check']));
        if ($jenisSlug !== 'preventive' && $jenisSlug !== 'checklist-report') {
            return;
        }

        $detailModel = new TransaksiCheckDetailModel();
        $details     = $detailModel->where('id_transaksi', $idTransaksi)->findAll();

        $hasilCheck = [];
        $ulasan     = [];
        foreach ($details as $d) {
            $hasilCheck[$d['id_parameter']] = $d['hasil_check'];
            $ulasan[$d['id_parameter']]     = $d['ulasan'];
        }

        $kombinedUlasan = array_filter(array_map('trim', $ulasan));
        $ulasanKontrol  = !empty($kombinedUlasan) ? implode(', ', $kombinedUlasan) : null;
        $overallStatus  = $this->calculateOverallStatus($hasilCheck);

        $kategoriName     = $transaksi['kategori'];
        $lokasiName       = $transaksi['lokasi_check'];
        $idMesin          = $transaksi['id_mesin'];
        $bulanTahun       = date('Y-m', strtotime($waktuSelesai));
        $tanggalCheckDate = date('Y-m-d', strtotime($waktuSelesai));

        $jadwalModel = new \App\Models\JadwalPreventiveModel();
        $schedule    = $jadwalModel->getJadwalForChecklist($lokasiName, $kategoriName, $bulanTahun);
        [$periodeKe, $outOfPlanDate] = $this->resolvePeriodeKe($schedule, $tanggalCheckDate, $waktuSelesai);

        $kontrolData = [
            'id_mesin'      => $idMesin,
            'kategori'      => $kategoriName,
            'bulan_tahun'   => $bulanTahun,
            'periode_ke'    => $periodeKe,
            'status_check'  => $overallStatus,
            'pic_nama'      => $transaksi['nama_pic'],
            'out_of_plan'   => $outOfPlanDate,
            'ulasan'        => $ulasanKontrol,
            'tanggal_check' => $tanggalCheckDate,
            'updated_at'    => date('Y-m-d H:i:s'),
        ];

        $ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
        $exist = $ceklisKontrolModel->findChecklistKontrol($idMesin, $kategoriName, $bulanTahun, $periodeKe);
        if ($exist) {
            $ceklisKontrolModel->update($exist['id_kontrol'], $kontrolData);
        } else {
            $kontrolData['created_at'] = date('Y-m-d H:i:s');
            $ceklisKontrolModel->insert($kontrolData);
        }
    }

    /**
     * Hitung periode_ke dan out_of_plan dari jadwal preventive.
     * Return [$periodeKe, $outOfPlanDate].
     */
    private function resolvePeriodeKe(?array $schedule, string $tanggalCheckDate, string $waktuSelesai): array
    {
        if ($schedule) {
            $tglRencana = strtotime($schedule['tanggal_rencana']);
            $dayOfWeek  = (int) date('N', $tglRencana);
            $mondayTs   = strtotime('-' . ($dayOfWeek - 1) . ' days', $tglRencana);
            $weekDates  = [];
            for ($d = 0; $d < 5; $d++) {
                $weekDates[$d + 1] = date('Y-m-d', strtotime("+{$d} days", $mondayTs));
            }
            $matchedCol = array_search($tanggalCheckDate, $weekDates);
            if ($matchedCol !== false) {
                return [(int) $matchedCol, null];
            }
        }
        $day       = (int) date('d', strtotime($waktuSelesai));
        $periodeKe = min(intval(($day - 1) / 7) + 1, 5);
        return [$periodeKe, $tanggalCheckDate];
    }

    /**
     * Delete + re-insert detail transaksi, handle foto, insert laporan_abnormal.
     */
    private function reinsertDetails(int $id, int $idMesin, array $hasilCheck, array $ulasan, $request, string $waktuSelesai): void
    {
        $detailModel    = new TransaksiCheckDetailModel();
        $parameterModel = new \App\Models\ParameterCheckModel();
        $uploadPath     = FCPATH . 'uploads/abnormal/';

        $oldDetails = $detailModel->where('id_transaksi', $id)->findAll();
        $oldPhotos  = [];
        foreach ($oldDetails as $od) {
            $oldPhotos[$od['id_parameter']] = [
                'f1' => $od['foto_abnormal'],
                'f2' => $od['foto_abnormal_2'],
            ];
        }
        $detailModel->where('id_transaksi', $id)->delete();

        foreach ($hasilCheck as $idParameter => $hasil) {
            $fotoAbnormal  = $oldPhotos[$idParameter]['f1'] ?? null;
            $fotoAbnormal2 = $oldPhotos[$idParameter]['f2'] ?? null;

            if ($hasil === 'Δ') {
                $file = $request->getFile("foto_abnormal.{$idParameter}");
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $newName = time() . '_1_' . uniqid() . '.' . $file->getClientExtension();
                    $file->move($uploadPath, $newName);
                    $fotoAbnormal = $newName;
                }
                $file2 = $request->getFile("foto_abnormal_2.{$idParameter}");
                if ($file2 && $file2->isValid() && !$file2->hasMoved()) {
                    $newName2 = time() . '_2_' . uniqid() . '.' . $file2->getClientExtension();
                    $file2->move($uploadPath, $newName2);
                    $fotoAbnormal2 = $newName2;
                }
            }

            $idDetail = $detailModel->insert([
                'id_transaksi'    => $id,
                'id_parameter'    => (int) $idParameter,
                'hasil_check'     => $hasil !== '' ? $hasil : null,
                'ulasan'          => $ulasan[$idParameter] ?? null,
                'foto_abnormal'   => $fotoAbnormal,
                'foto_abnormal_2' => $fotoAbnormal2,
            ]);

            if ($hasil === 'Δ') {
                $paramInfo      = $parameterModel->find((int) $idParameter);
                $pointCheckName = $paramInfo ? $paramInfo['point_check'] : 'Parameter #' . $idParameter;
                $abnormalDesc   = trim($ulasan[$idParameter] ?? '');
                if (empty($abnormalDesc)) {
                    $abnormalDesc = 'Ditemukan kondisi abnormal (Δ)';
                }
                (new \App\Models\LaporanAbnormalModel())->insert([
                    'id_transaksi'       => $id,
                    'id_detail'          => $idDetail,
                    'id_mesin'           => $idMesin,
                    'point_check'        => $pointCheckName,
                    'abnormal_condition' => $abnormalDesc,
                    'pengecekan_tanggal' => date('Y-m-d', strtotime($waktuSelesai)),
                    'pengecekan_pic'     => session()->get('nama') ?: 'Admin',
                    'foto_abnormal'      => $fotoAbnormal,
                    'foto_abnormal_2'    => $fotoAbnormal2,
                    'created_at'         => $waktuSelesai,
                    'updated_at'         => $waktuSelesai,
                ]);
            }
        }
    }

    /**
     * Update/insert baris tabel TransaksiOverhaul saat edit.
     */
    private function updateOverhaulExtra(int $id, $request): void
    {
        $barFeederType = $request->getPost('bar_feeder_type');
        $rawSupport    = $request->getPost('support_pic');
        $supportStr    = null;
        if (is_array($rawSupport)) {
            $filtered = array_filter(array_map('trim', $rawSupport));
            if (!empty($filtered)) {
                $supportStr = implode(', ', $filtered);
            }
        }
        $overhaulModel = new \App\Models\TransaksiOverhaulModel();
        $payload = [
            'bar_feeder_type'     => $barFeederType ?: null,
            'support_pic'         => $supportStr,
            'note_recommendation' => $request->getPost('note_recommendation') ?: null,
        ];
        if ($overhaulModel->find($id)) {
            $overhaulModel->update($id, $payload);
        } else {
            $payload['id_transaksi'] = $id;
            $overhaulModel->insert($payload);
        }
    }

    /**
     * Re-hitung overall status dan update CeklisKontrol saat edit Preventive.
     */
    private function updateChecklistKontrolOnEdit(array $header, int $idMesin, array $hasilCheck, array $ulasan, string $waktuSelesai): void
    {
        $combinedUlasan   = array_filter(array_map('trim', $ulasan));
        $ulasanKontrol    = !empty($combinedUlasan) ? implode(', ', $combinedUlasan) : null;
        $overallStatus    = $this->calculateOverallStatus($hasilCheck);
        $tanggalCheckDate = date('Y-m-d', strtotime($waktuSelesai));

        (new \App\Models\CeklisKontrolModel())->updateChecklistKontrol(
            $header['id_mesin'],
            $header['kategori'],
            $tanggalCheckDate,
            [
                'id_mesin'     => $idMesin,
                'status_check' => $overallStatus,
                'ulasan'       => $ulasanKontrol,
            ]
        );
    }

    /**
     * Normalisasi GET params menjadi array $filters standar untuk PDF all.
     */
    private function buildPdfFilters(?string $lokasiName, array $getParams): array
    {
        $userLine = (session()->get('role') === Role::Leader->value) ? session()->get('line') : null;

        return [
            'lokasi'      => $lokasiName,
            'id_mesin'    => ($getParams['id_mesin'] ?? 'all') === 'all' ? null : ($getParams['id_mesin'] ?? null),
            'line'        => $userLine ?: (($getParams['line'] ?? 'all') === 'all' ? null : ($getParams['line'] ?? null)),
            'jenis_check' => ($getParams['jenis_check'] ?? 'all') === 'all' ? null : ($getParams['jenis_check'] ?? null),
            'kategori'    => ($getParams['kategori'] ?? 'all') === 'all' ? null : ($getParams['kategori'] ?? null),
            'bulan'       => ($getParams['bulan'] ?? 'all') === 'all' ? null : ($getParams['bulan'] ?? null),
            'status'      => ($getParams['status'] ?? 'all') === 'all' ? null : ($getParams['status'] ?? null),
            'pic'         => ($getParams['pic'] ?? 'all') === 'all' ? null : ($getParams['pic'] ?? null),
            'sort_by'     => $getParams['sort_by'] ?? 'id_transaksi',
            'order'       => $getParams['order'] ?? 'desc',
        ];
    }

    /**
     * Loop fetch header+detail+rowspan setiap transaksi, return $allReports[].
     */
    private function buildAllReports(array $riwayat): array
    {
        $transaksiModel = new TransaksiCheckModel();
        $detailModel    = new TransaksiCheckDetailModel();
        $allReports     = [];

        foreach ($riwayat as $row) {
            $header = $transaksiModel->getDetailTransaksi($row['id_transaksi']);
            if ($header) {
                $details = $detailModel->getDetailByTransaksi($row['id_transaksi']);
                $details = $detailModel->calculateRowspans($details, $header['jenis_check']);
                $allReports[] = [
                    'header'  => $header,
                    'details' => $details,
                ];
            }
        }

        return $allReports;
    }

    /**
     * Fetch details + calculateRowspans untuk satu transaksi.
     */
    private function fetchHeaderAndDetails(int $id, array $header): array
    {
        $detailModel = new TransaksiCheckDetailModel();
        $details     = $detailModel->getDetailByTransaksi($id);
        $details     = $detailModel->calculateRowspans($details, $header['jenis_check']);

        return [$details];
    }

    /**
     * Hitung durasi dalam detik dari waktu_mulai dan waktu_selesai header.
     */
    private function calculateDurasiDetik(array $header): ?int
    {
        if (! empty($header['waktu_mulai']) && ! empty($header['waktu_selesai'])) {
            return strtotime($header['waktu_selesai']) - strtotime($header['waktu_mulai']);
        }

        return null;
    }

    /**
     * Fetch daftar PIC leader berdasarkan role dan line sesi yang sedang login.
     */
    private function resolveLeaderPicList(?string $roleSession): array
    {
        $leaderPicModel = new \App\Models\PicModel();

        if ($roleSession === Role::Leader->value) {
            $userLine = session()->get('line');
            $lineMap  = [
                'Line 1' => 'leader1',
                'Line 2' => 'leader2',
                'Line 3' => 'leader3',
                'CG'     => 'leadercg',
                'Second' => 'leadersc',
            ];
            if (isset($lineMap[$userLine])) {
                $leaderPicModel->where('role_pic', $lineMap[$userLine]);
            } else {
                $leaderPicModel->like('role_pic', Role::Leader->value, 'both');
            }
        } else {
            $leaderPicModel->like('role_pic', Role::Leader->value, 'both');
        }

        return $leaderPicModel->findAll();
    }
}
