<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/admin/jadwal/index.php';
$content = file_get_contents($file);

// We want to remove the non-robust filter scripts.
// The string to look for:
$badScript = <<<'EOD'
    // Filter Kategori berdasarkan Lokasi
    const lokasiSelect = document.getElementById('lokasiSelect');
    const kategoriSelect = document.getElementById('kategoriSelect');
    
    if (lokasiSelect && kategoriSelect) {
        function filterKategori() {
            const lokasi = lokasiSelect.value;
            Array.from(kategoriSelect.options).forEach(opt => {
                const isMfg1Only = ['Bearing Cam', 'Gearbox', 'Belt Cam'].includes(opt.value);
                if (lokasi === 'MFG 2' && isMfg1Only) {
                    opt.style.display = 'none';
                    opt.disabled = true;
                } else {
                    opt.style.display = '';
                    opt.disabled = false;
                }
            });
            
            // Jika opsi yang terpilih sekarang di-disable, reset ke opsi pertama yang aktif
            if (kategoriSelect.options[kategoriSelect.selectedIndex].disabled) {
                for (let i = 0; i < kategoriSelect.options.length; i++) {
                    if (!kategoriSelect.options[i].disabled) {
                        kategoriSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }
        
        lokasiSelect.addEventListener('change', filterKategori);
        // Jalankan sekali saat load
        filterKategori();
    }
EOD;

$content = str_replace($badScript, '', $content);
file_put_contents($file, $content);
echo "Cleanup duplicates done.\n";
