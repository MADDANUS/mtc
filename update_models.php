<?php
$files = [
    'app/Models/LaporanAbnormalModel.php',
    'app/Models/RiwayatMesinModel.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // 1. Change joins
        $replaced = str_replace(
            "->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin')",
            "->join('master_mesin', 'master_mesin.id_mesin = laporan_abnormal.id_mesin', 'left')",
            $content
        );
        $replaced = str_replace(
            "->join('master_mesin', 'master_mesin.id_mesin = riwayat_mesin.id_mesin')",
            "->join('master_mesin', 'master_mesin.id_mesin = riwayat_mesin.id_mesin', 'left')",
            $replaced
        );

        // 2. Change selects to use COALESCE
        $replacements = [
            'master_mesin.no_mesin,' => 'COALESCE(master_mesin.no_mesin, laporan_abnormal.ss_no_mesin) as no_mesin,',
            'master_mesin.no_mesin as nama_mesin' => 'COALESCE(master_mesin.no_mesin, laporan_abnormal.ss_no_mesin) as nama_mesin',
        ];
        if (strpos($file, 'RiwayatMesinModel') !== false) {
            $replacements = [
                'master_mesin.no_mesin,' => 'COALESCE(master_mesin.no_mesin, riwayat_mesin.ss_no_mesin) as no_mesin,',
            ];
        }
        
        $replaced = strtr($replaced, $replacements);
        
        if ($replaced !== $content) {
            file_put_contents($file, $replaced);
            echo "Replaced in " . basename($file) . PHP_EOL;
        }
    }
}
?>
