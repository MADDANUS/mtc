<?php
$files = [
    'c:/xampp/htdocs/mtce/app/Views/abnormal/index.php',
    'c:/xampp/htdocs/mtce/app/Views/abnormal/index_overhaul.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = preg_replace('/\r\n|\r/', "\n", $content);
        
        $oldLogic = <<<'EOD'
              <?php 
                $canEdit = in_array(session()->get('role'), ['member', 'sheadprd', 'sheadmtc', 'admin', 'magang'], true);
                $rowClass = $canEdit ? 'row-editable' : '';
              ?>
EOD;

        $newLogic = <<<'EOD'
              <?php 
                $role = session()->get('role');
                $isFilled = !empty($r['type_sparepart']) || !empty($r['progres_stock']) || !empty($r['progres_tanggal']) || !empty($r['action']) || !empty($r['repair_pic']) || !empty($r['keterangan']) || !empty($r['foto_perbaikan']) || !empty($r['foto_perbaikan_2']);
                
                $canEdit = false;
                if (in_array($role, ['member', 'sheadprd', 'sheadmtc', 'admin', 'magang'], true)) {
                    if ($isFilled) {
                        $canEdit = ($role === 'admin');
                    } else {
                        $canEdit = true;
                    }
                }
                
                $rowClass = $canEdit ? 'row-editable' : '';
              ?>
EOD;
        
        $content = str_replace($oldLogic, $newLogic, $content);
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
