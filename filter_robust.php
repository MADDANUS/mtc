<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/admin/jadwal/index.php';
$content = file_get_contents($file);

// Cut at calendar.render();
$pos = strpos($content, '  calendar.render();');
if ($pos !== false) {
    // Keep everything up to calendar.render();
    $start = substr($content, 0, $pos + strlen('  calendar.render();'));
    
    // Add the new robust JS block
    $newJs = <<<'EOD'

});

// Filter Kategori berdasarkan Lokasi secara Robust
document.addEventListener('DOMContentLoaded', function() {
    const lokasiSelect = document.getElementById('lokasiSelect');
    const kategoriSelect = document.getElementById('kategoriSelect');
    
    if (lokasiSelect && kategoriSelect) {
        // Simpan semua opsi asli ke dalam array
        const allOptions = Array.from(kategoriSelect.options).map(opt => ({
            value: opt.value,
            text: opt.text,
            selected: opt.selected
        }));
        
        function filterKategori() {
            const lokasi = lokasiSelect.value;
            const currentSelected = kategoriSelect.value;
            
            // Kosongkan select
            kategoriSelect.innerHTML = '';
            
            let foundSelected = false;
            
            allOptions.forEach(optData => {
                const isMfg1Only = ['Bearing Cam', 'Gearbox', 'Belt Cam'].includes(optData.value);
                
                // Jika lokasi MFG 2 dan kategori adalah milik MFG 1, jangan di-render
                if (lokasi === 'MFG 2' && isMfg1Only) {
                    return; 
                }
                
                // Buat elemen option baru
                const newOpt = document.createElement('option');
                newOpt.value = optData.value;
                newOpt.text = optData.text;
                
                if (optData.value === currentSelected) {
                    newOpt.selected = true;
                    foundSelected = true;
                }
                
                kategoriSelect.appendChild(newOpt);
            });
            
            // Jika pilihan sebelumnya terhapus, pilih yang pertama otomatis
            if (!foundSelected && kategoriSelect.options.length > 0) {
                kategoriSelect.options[0].selected = true;
            }
        }
        
        lokasiSelect.addEventListener('change', filterKategori);
        filterKategori(); // Jalankan saat pertama kali dimuat
    }
});
</script>

<?= view('layout/footer') ?>
EOD;

    // We replace the rest of the file
    $content = $start . "\n" . $newJs;
    file_put_contents($file, $content);
    echo "Robust frontend filtering applied to jadwal index.php\n";
} else {
    echo "Could not find calendar.render();\n";
}
