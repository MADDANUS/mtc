<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/ApprovalController.php';
$content = file_get_contents($file);
$content = preg_replace('/\r\n|\r/', "\n", $content);

$oldLokasiGen = <<<'EOD'
        foreach ($allDocs as $doc) {
            $loc = $doc['lokasi_check'] ?? $doc['lokasi'] ?? '';
            $line = $doc['line'] ?? '';
            if (!empty($loc)) {
                $locLine = $loc . (!empty($line) ? ' / ' . $line : '');
                $uniqueLokasi[$locLine] = true;
            }
EOD;

$newLokasiGen = <<<'EOD'
        foreach ($allDocs as $doc) {
            $loc = $doc['lokasi_check'] ?? $doc['lokasi'] ?? '';
            $line = $doc['line'] ?? '';
            if (!empty($loc)) {
                $uniqueLokasi[$loc] = true;
            }
            if (!empty($line)) {
                $uniqueLokasi[$line] = true;
            }
EOD;

$oldLokasiFilter = <<<'EOD'
            if ($filterLokasi && $filterLokasi !== 'all') {
                $loc = $row['lokasi_check'] ?? $row['lokasi'] ?? '';
                $line = $row['line'] ?? '';
                $locLine = $loc . (!empty($line) ? ' / ' . $line : '');
                if (strtolower($locLine) !== strtolower($filterLokasi)) return false;
            }
EOD;

$newLokasiFilter = <<<'EOD'
            if ($filterLokasi && $filterLokasi !== 'all') {
                $loc = $row['lokasi_check'] ?? $row['lokasi'] ?? '';
                $line = $row['line'] ?? '';
                if (strtolower($loc) !== strtolower($filterLokasi) && strtolower($line) !== strtolower($filterLokasi)) {
                    return false;
                }
            }
EOD;

$content = str_replace($oldLokasiGen, $newLokasiGen, $content);
$content = str_replace($oldLokasiFilter, $newLokasiFilter, $content);
file_put_contents($file, $content);
echo "ApprovalController updated for individual Lokasi and Line options.\n";
