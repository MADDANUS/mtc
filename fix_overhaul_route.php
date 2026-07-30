<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/ChecklistController.php';
$content = file_get_contents($file);

$oldLogic = <<<'EOD'
        // Auto-routing jika id_mesin ada
        if ($idMesin && strtolower($jenisSlug) === 'overhaul') {
            $mesin = $this->mesinModel->find($idMesin);
            if ($mesin) {
                if (!empty($mesin['jenis'])) {
                    $kategoriSlug = url_title(strtolower($mesin['jenis']), '-', true);
                    return redirect()->to("/checklist/{$lokasiSlug}/{$jenisSlug}/create/{$kategoriSlug}?id_mesin={$idMesin}");
                } else {
                    // Fallback jika jenis kosong
                    if ($lokasiName === 'MFG 1') {
                        return redirect()->to("/checklist/{$lokasiSlug}/{$jenisSlug}/create/mesin-cnc-bar-feeder?id_mesin={$idMesin}");
                    }
                }
            }
        }
EOD;

$newLogic = <<<'EOD'
        // Auto-routing jika id_mesin ada
        if ($idMesin && strtolower($jenisSlug) === 'overhaul') {
            $mesin = $this->mesinModel->find($idMesin);
            if ($mesin) {
                if ($lokasiName === 'MFG 1') {
                    // MFG 1 Overhaul selalu memakai form mesin-cnc-bar-feeder
                    return redirect()->to("/checklist/{$lokasiSlug}/{$jenisSlug}/create/mesin-cnc-bar-feeder?id_mesin={$idMesin}");
                } else if (!empty($mesin['jenis'])) {
                    // MFG 2 Overhaul mengikuti jenis mesinnya (milling, thread, dll)
                    $kategoriSlug = url_title(strtolower($mesin['jenis']), '-', true);
                    return redirect()->to("/checklist/{$lokasiSlug}/{$jenisSlug}/create/{$kategoriSlug}?id_mesin={$idMesin}");
                }
            }
        }
EOD;

$content = str_replace($oldLogic, $newLogic, $content);

file_put_contents($file, $content);
echo "ChecklistController scan routing fixed.\n";
