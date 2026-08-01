<?php

$file = 'app/Services/RiwayatService.php';
$content = file_get_contents($file);

// Replace 1
$content = str_replace(
<<<'PHP'
        $db = \Config\Database::connect();
        $tx = $db->table('transaksi_check')
                 ->select('id_transaksi')
                 ->where('id_mesin', $idMesin)
                 ->where('kategori', $kategori)
                 ->like('waktu_mulai', $bulan, 'after')
                 ->orderBy('id_transaksi', 'DESC')
                 ->get()
                 ->getRowArray();
PHP,
<<<'PHP'
        $transaksiModel = new \App\Models\TransaksiCheckModel();
        $tx = $transaksiModel->getLatestIdByMesinAndKategori($idMesin, $kategori, $bulan);
PHP,
$content);

// Replace 2 (First insert laporan abnormal)
$content = str_replace(
<<<'PHP'
                $db->table('laporan_abnormal')->insert([
                    'id_transaksi'       => $id,
                    'id_detail'          => $idDetail,
                    'id_mesin'           => $idMesin,
                    'point_check'        => $pointCheckName,
                    'abnormal_condition' => $abnormalDesc,
                    'pengecekan_tanggal' => date('Y-m-d', strtotime($waktuSelesai)),
                    'pengecekan_pic'     => session()->get('nama') ?: 'Admin', // who edited
                    'foto_abnormal'      => $fotoAbnormal,
                    'foto_abnormal_2'    => $fotoAbnormal2,
                    'created_at'         => $waktuSelesai,
                    'updated_at'         => $waktuSelesai,
                ]);
PHP,
<<<'PHP'
                $abnormalModel = new \App\Models\LaporanAbnormalModel();
                $abnormalModel->insert([
                    'id_transaksi'       => $id,
                    'id_detail'          => $idDetail,
                    'id_mesin'           => $idMesin,
                    'point_check'        => $pointCheckName,
                    'abnormal_condition' => $abnormalDesc,
                    'pengecekan_tanggal' => date('Y-m-d', strtotime($waktuSelesai)),
                    'pengecekan_pic'     => session()->get('nama') ?: 'Admin', // who edited
                    'foto_abnormal'      => $fotoAbnormal,
                    'foto_abnormal_2'    => $fotoAbnormal2,
                    'created_at'         => $waktuSelesai,
                    'updated_at'         => $waktuSelesai,
                ]);
PHP,
$content);

// Replace 3 (overhaul update)
$content = str_replace(
<<<'PHP'
            $existing = $db->table('transaksi_overhaul')->where('id_transaksi', $id)->get()->getRowArray();
            if ($existing) {
                $db->table('transaksi_overhaul')->where('id_transaksi', $id)->update([
                    'bar_feeder_type'     => $barFeederType ?: null,
                    'support_pic'         => $supportStr,
                    'note_recommendation' => $request->getPost('note_recommendation') ?: null,
                ]);
            } else {
                $db->table('transaksi_overhaul')->insert([
                    'id_transaksi'        => $id,
                    'bar_feeder_type'     => $barFeederType ?: null,
                    'support_pic'         => $supportStr,
                    'note_recommendation' => $request->getPost('note_recommendation') ?: null,
                ]);
            }
PHP,
<<<'PHP'
            $overhaulModel = new \App\Models\TransaksiOverhaulModel();
            $existing = $overhaulModel->find($id);
            if ($existing) {
                $overhaulModel->update($id, [
                    'bar_feeder_type'     => $barFeederType ?: null,
                    'support_pic'         => $supportStr,
                    'note_recommendation' => $request->getPost('note_recommendation') ?: null,
                ]);
            } else {
                $overhaulModel->insert([
                    'id_transaksi'        => $id,
                    'bar_feeder_type'     => $barFeederType ?: null,
                    'support_pic'         => $supportStr,
                    'note_recommendation' => $request->getPost('note_recommendation') ?: null,
                ]);
            }
PHP,
$content);

// Replace 4 (ceklis_kontrol update)
$content = str_replace(
<<<'PHP'
            // Try updating where id_mesin + kategori + tanggal_check matches
            $db->table('ceklis_kontrol')
               ->where('id_mesin', $header['id_mesin'])
               ->where('kategori', $header['kategori'])
               ->where('tanggal_check', $tanggalCheckDate)
               ->update([
                   'id_mesin'     => $idMesin, // in case it changed
                   'status_check' => $overallStatus,
                   'ulasan'       => $ulasanKontrol,
               ]);
PHP,
<<<'PHP'
            // Try updating where id_mesin + kategori + tanggal_check matches
            $ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
            $ceklisKontrolModel->updateChecklistKontrol($header['id_mesin'], $header['kategori'], $tanggalCheckDate, [
                'id_mesin'     => $idMesin, // in case it changed
                'status_check' => $overallStatus,
                'ulasan'       => $ulasanKontrol,
            ]);
PHP,
$content);

// Replace 5 (Second insert laporan abnormal)
$content = str_replace(
<<<'PHP'
                $db->table('laporan_abnormal')->insert([
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
PHP,
<<<'PHP'
                $abnormalModel = new \App\Models\LaporanAbnormalModel();
                $abnormalModel->insert([
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
PHP,
$content);

// Replace 6 (jadwal_preventive)
$content = str_replace(
<<<'PHP'
            // Ambil jadwal rencana untuk bulan ini
            $schedule = $db->table('jadwal_preventive')
                           ->where('lokasi', $lokasiName)
                           ->where('kategori', $kategoriName)
                           ->where('bulan_tahun', $bulanTahun)
                           ->get()
                           ->getRowArray();
PHP,
<<<'PHP'
            // Ambil jadwal rencana untuk bulan ini
            $jadwalModel = new \App\Models\JadwalPreventiveModel();
            $schedule = $jadwalModel->getJadwalForChecklist($lokasiName, $kategoriName, $bulanTahun);
PHP,
$content);

// Replace 7 (ceklis_kontrol upsert)
$content = str_replace(
<<<'PHP'
            $exist = $db->table('ceklis_kontrol')
                        ->where('id_mesin', $idMesin)
                        ->where('kategori', $kategoriName)
                        ->where('bulan_tahun', $bulanTahun)
                        ->where('periode_ke', $periodeKe)
                        ->get()
                        ->getRowArray();

            if ($exist) {
                $db->table('ceklis_kontrol')
                   ->where('id_kontrol', $exist['id_kontrol'])
                   ->update($kontrolData);
            } else {
                $kontrolData['created_at'] = date('Y-m-d H:i:s');
                $db->table('ceklis_kontrol')->insert($kontrolData);
            }
PHP,
<<<'PHP'
            $ceklisKontrolModel = new \App\Models\CeklisKontrolModel();
            $exist = $ceklisKontrolModel->findChecklistKontrol($idMesin, $kategoriName, $bulanTahun, $periodeKe);

            if ($exist) {
                $ceklisKontrolModel->update($exist['id_kontrol'], $kontrolData);
            } else {
                $kontrolData['created_at'] = date('Y-m-d H:i:s');
                $ceklisKontrolModel->insert($kontrolData);
            }
PHP,
$content);

file_put_contents($file, $content);
echo "RiwayatService successfully refactored.";
