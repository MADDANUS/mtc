<?php

$dirs = [
    __DIR__ . '/../app/Models',
    __DIR__ . '/../app/Controllers',
    __DIR__ . '/../app/Services',
    __DIR__ . '/../app/Views',
    __DIR__ . '/../app/Traits'
];

$replacements = [
    // Variables & array keys
    'lokasi_check' => 'departemen_check',
    'lokasiCheck' => 'departemenCheck',
    'LokasiCheck' => 'DepartemenCheck',
    '$lokasi' => '$departemen',
    "'lokasi'" => "'departemen'",
    '"lokasi"' => '"departemen"',
    
    // Class names & namespaces & Enums
    'App\Enums\Lokasi' => 'App\Enums\Departemen',
    'Lokasi::' => 'Departemen::',
    'Lokasi::MFG1' => 'Departemen::MFG1',
    'Lokasi::MFG2' => 'Departemen::MFG2',
    
    // Methods
    'getByLokasi' => 'getByDepartemen',
    'getLokasiByLine' => 'getDepartemenByLine',
    'getLinesGroupedByLokasi' => 'getLinesGroupedByDepartemen',
    'indexLokasi' => 'indexDepartemen',
    
    // Views/HTML
    'name="lokasi"' => 'name="departemen"',
    'id="lokasi"' => 'id="departemen"',
    'for="lokasi"' => 'for="departemen"',
    'id="filterLokasi"' => 'id="filterDepartemen"',
    'id="cb_lokasi"' => 'id="cb_departemen"',
    'name="cb_lokasi"' => 'name="cb_departemen"',
    'data-lokasi' => 'data-departemen',
    
    // UI Text
    'Lokasi Mesin' => 'Departemen Mesin',
    '>Lokasi<' => '>Departemen<',
    '<th>Lokasi</th>' => '<th>Departemen</th>',
    'Pilih Lokasi' => 'Pilih Departemen',
    'Semua Lokasi' => 'Semua Departemen',
    'lokasiSlug' => 'departemenSlug',
    'lokasiName' => 'departemenName',
    'lokasiUser' => 'departemenUser',
];

function processDir($dir, $replacements) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $newContent = $content;
            
            foreach ($replacements as $search => $replace) {
                $newContent = str_replace($search, $replace, $newContent);
            }
            
            // Regex for exact word boundary matches to catch remaining `lokasi` 
            // where it's used as a generic word (like in comments, or db queries)
            // But we must be careful not to replace parts of other words
            $newContent = preg_replace('/\blokasi\b/', 'departemen', $newContent);
            $newContent = preg_replace('/\bLokasi\b/', 'Departemen', $newContent);
            
            if ($content !== $newContent) {
                file_put_contents($file->getPathname(), $newContent);
                echo "Updated: " . $file->getPathname() . "\n";
            }
        }
    }
}

foreach ($dirs as $dir) {
    processDir($dir, $replacements);
}

echo "Done.\n";
