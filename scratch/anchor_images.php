<?php
$files = [
    'app/Controllers/RiwayatController.php',
    'app/Controllers/KontrolController.php'
];

foreach ($files as $f) {
    if (file_exists($f)) {
        $c = file_get_contents($f);
        
        // Find: $drawing->setWorksheet($sheet);
        // Replace with: $drawing->setEditAs(\PhpOffice\PhpSpreadsheet\Worksheet\Drawing::EDIT_AS_ONECELL); $drawing->setWorksheet($sheet);
        
        // Avoid duplicating if already there
        if (strpos($c, 'EDIT_AS_ONECELL') === false) {
            $c = str_replace(
                '$drawing->setWorksheet($sheet);',
                "\$drawing->setEditAs(\PhpOffice\PhpSpreadsheet\Worksheet\Drawing::EDIT_AS_ONECELL);\n                    \$drawing->setWorksheet(\$sheet);",
                $c
            );
            file_put_contents($f, $c);
            echo "Updated $f\n";
        } else {
            echo "Already updated $f\n";
        }
    }
}
