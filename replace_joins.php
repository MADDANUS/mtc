<?php
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app/Models/'));
foreach ($files as $file) {
    if ($file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        
        $replaced = str_replace(
            "->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin')",
            "->join('master_mesin', 'master_mesin.id_mesin = transaksi_check.id_mesin', 'left')",
            $content
        );
        
        $replaced = str_replace(
            "->join('users', 'users.id = transaksi_check.id_user')",
            "->join('users', 'users.id = transaksi_check.id_user', 'left')",
            $replaced
        );
        
        if ($replaced !== $content) {
            file_put_contents($file->getRealPath(), $replaced);
            echo "Replaced in " . $file->getFilename() . PHP_EOL;
        }
    }
}
?>
