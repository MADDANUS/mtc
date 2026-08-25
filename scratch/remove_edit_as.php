<?php
$f1 = 'app/Controllers/RiwayatController.php';
$f2 = 'app/Controllers/KontrolController.php';

// Fix RiwayatController
if (file_exists($f1)) {
    $c = file_get_contents($f1);
    
    // Remove all EDIT_AS_ONECELL
    $c = preg_replace('/\$drawing->setEditAs\(\\\\PhpOffice\\\\PhpSpreadsheet\\\\Worksheet\\\\Drawing::EDIT_AS_ONECELL\);\s*/', '', $c);
    
    // Fix Logo offsets
    $c = preg_replace('/->setCoordinates\(\'B2\'\);\s*\$drawing->setHeight\(\d+\);\s*\$drawing->setOffsetX\(\d+\);\s*\$drawing->setOffsetY\(\d+\);/', 
                      "->setCoordinates('B2');\n            \$drawing->setHeight(90);\n            \$drawing->setOffsetX(25);\n            \$drawing->setOffsetY(10);", 
                      $c);
    
    // The abnormal photos offset is currently setOffsetY(30) in RiwayatController (since my script replaced it blindly)
    // Actually, since I removed setEditAs, it should now respect setOffsetY(30). Let's make it 35.
    // Wait, the logo was also setOffsetY(30) by the blind replace, but the preg_replace above fixes the logo back to 10.
    // Let's replace setOffsetY(30) with setOffsetY(35) for the abnormal photos.
    $c = str_replace('setOffsetY(30)', 'setOffsetY(35)', $c);
    
    file_put_contents($f1, $c);
}

// Fix KontrolController
if (file_exists($f2)) {
    $c = file_get_contents($f2);
    
    // Remove all EDIT_AS_ONECELL
    $c = preg_replace('/\$drawing->setEditAs\(\\\\PhpOffice\\\\PhpSpreadsheet\\\\Worksheet\\\\Drawing::EDIT_AS_ONECELL\);\s*/', '', $c);
    
    file_put_contents($f2, $c);
}

echo "Removed EDIT_AS_ONECELL and fixed offsets!";
