<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/ApprovalController.php';
$content = file_get_contents($file);
$content = preg_replace('/\r\n|\r/', "\n", $content);

$oldFilterCode = <<<'EOD'
        $filterJenis  = $this->request->getGet('jenis') ?: null;
        $filterBulan  = $this->request->getGet('bulan') ?: null;
        $filterStatus = $this->request->getGet('status') ?: null;
        $filterSearch = $this->request->getGet('search') ?: null;

        $filtered = array_filter($allDocs, function($row) use ($filterJenis, $filterBulan, $filterStatus, $filterSearch) {
            if ($filterJenis && $filterJenis !== 'all') {
                $jenis = $row['jenis_check'] ?? '';
                if ($filterJenis === 'Preventive' && strtolower($jenis) !== 'preventive') return false;
                if ($filterJenis === 'Overhaul'   && strtolower($jenis) !== 'overhaul')   return false;
                if ($filterJenis === 'kontrol'    && ($row['doc_source'] ?? '') !== 'kontrol') return false;
            }
            if ($filterBulan && $filterBulan !== 'all') {
                $docDate = $row['doc_date'] ?? '';
                if (strpos($docDate, $filterBulan) === false) return false;
            }
            if ($filterStatus && $filterStatus !== 'all') {
                if (($row['status'] ?? '') !== $filterStatus) return false;
            }
            if ($filterSearch) {
                $haystack = strtolower(
                    ($row['no_mesin']    ?? '') . ' ' .
                    ($row['type_mesin']  ?? '') . ' ' .
                    ($row['kategori']    ?? '') . ' ' .
                    ($row['lokasi_check'] ?? $row['lokasi'] ?? '') . ' ' .
                    ($row['nama_pic']    ?? '') . ' ' .
                    ($row['nama_staff']  ?? '')
                );
                if (strpos($haystack, strtolower($filterSearch)) === false) return false;
            }
            return true;
        });
EOD;

$newFilterCode = <<<'EOD'
        // Ekstrak data unik untuk dropdown
        $uniqueLokasi = [];
        $uniqueKategori = [];
        $uniqueMesin = [];
        foreach ($allDocs as $doc) {
            $loc = $doc['lokasi_check'] ?? $doc['lokasi'] ?? '';
            if (!empty($loc)) $uniqueLokasi[$loc] = true;
            
            $kat = $doc['kategori'] ?? '';
            if (!empty($kat)) $uniqueKategori[$kat] = true;
            
            $mesinNo = $doc['no_mesin'] ?? '';
            $mesinType = $doc['type_mesin'] ?? '';
            if (!empty($mesinNo)) {
                $mesinLabel = $mesinNo . (!empty($mesinType) ? ' - ' . $mesinType : '');
                $uniqueMesin[$mesinNo] = $mesinLabel;
            }
        }
        $uniqueLokasi = array_keys($uniqueLokasi);
        $uniqueKategori = array_keys($uniqueKategori);
        asort($uniqueLokasi);
        asort($uniqueKategori);
        asort($uniqueMesin);

        $filterJenis    = $this->request->getGet('jenis') ?: null;
        $filterBulan    = $this->request->getGet('bulan') ?: null;
        $filterStatus   = $this->request->getGet('status') ?: null;
        $filterLokasi   = $this->request->getGet('lokasi') ?: null;
        $filterKategori = $this->request->getGet('kategori') ?: null;
        $filterMesin    = $this->request->getGet('mesin') ?: null;

        $filtered = array_filter($allDocs, function($row) use ($filterJenis, $filterBulan, $filterStatus, $filterLokasi, $filterKategori, $filterMesin) {
            if ($filterJenis && $filterJenis !== 'all') {
                $jenis = $row['jenis_check'] ?? '';
                if ($filterJenis === 'Preventive' && strtolower($jenis) !== 'preventive') return false;
                if ($filterJenis === 'Overhaul'   && strtolower($jenis) !== 'overhaul')   return false;
                if ($filterJenis === 'kontrol'    && ($row['doc_source'] ?? '') !== 'kontrol') return false;
            }
            if ($filterBulan && $filterBulan !== 'all') {
                $docDate = $row['doc_date'] ?? '';
                if (strpos($docDate, $filterBulan) === false) return false;
            }
            if ($filterStatus && $filterStatus !== 'all') {
                $status = $row['status'] ?? '';
                $jenis  = $row['jenis_check'] ?? '';
                
                if ($filterStatus === 'Pending_Overhaul') {
                    if ($status !== 'Pending' || $jenis !== 'Overhaul') return false;
                } elseif ($filterStatus === 'Pending_Preventive') {
                    if ($status !== 'Pending' || $jenis !== 'Preventive') return false;
                } else {
                    if ($status !== $filterStatus) return false;
                }
            }
            if ($filterLokasi && $filterLokasi !== 'all') {
                $loc = $row['lokasi_check'] ?? $row['lokasi'] ?? '';
                if (strtolower($loc) !== strtolower($filterLokasi)) return false;
            }
            if ($filterKategori && $filterKategori !== 'all') {
                $kat = $row['kategori'] ?? '';
                if (strtolower($kat) !== strtolower($filterKategori)) return false;
            }
            if ($filterMesin && $filterMesin !== 'all') {
                $mesinNo = $row['no_mesin'] ?? '';
                if (strtolower($mesinNo) !== strtolower($filterMesin)) return false;
            }
            return true;
        });
EOD;

$oldReturnCode = <<<'EOD'
            'filterJenis' => $filterJenis,
            'filterBulan' => $filterBulan,
            'filterStatus'=> $filterStatus,
            'filterSearch'=> $filterSearch,
        ]);
EOD;

$newReturnCode = <<<'EOD'
            'filterJenis'    => $filterJenis,
            'filterBulan'    => $filterBulan,
            'filterStatus'   => $filterStatus,
            'filterLokasi'   => $filterLokasi,
            'filterKategori' => $filterKategori,
            'filterMesin'    => $filterMesin,
            'uniqueLokasi'   => $uniqueLokasi,
            'uniqueKategori' => $uniqueKategori,
            'uniqueMesin'    => $uniqueMesin,
        ]);
EOD;

if (strpos($content, $oldFilterCode) !== false) {
    $content = str_replace($oldFilterCode, $newFilterCode, $content);
    $content = str_replace($oldReturnCode, $newReturnCode, $content);
    file_put_contents($file, $content);
    echo "ApprovalController updated successfully.\n";
} else {
    echo "Could not find old filter code block in ApprovalController.\n";
}
