<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/checklist/form.php';
$content = file_get_contents($file);

$content = str_replace(
    '<input type="text" class="form-control" value="<?= esc($waktuMulaiDisplay) ?>" readonly>',
    '<input type="text" id="displayWaktuMulai" class="form-control" value="<?= esc($waktuMulaiDisplay) ?>" readonly>',
    $content
);

$oldJs = "if (name === 'csrf_test_name' || name === 'waktu_mulai' || name === 'waktu_selesai') continue;";
$newJs = <<<'EOD'
                    if (name === 'csrf_test_name' || name === 'waktu_selesai') continue;
                    if (name === 'waktu_mulai') {
                        const savedWaktu = data[name];
                        const todayDate = "<?= date('Y-m-d') ?>";
                        if (savedWaktu && savedWaktu.startsWith(todayDate)) {
                            const displayEl = document.getElementById('displayWaktuMulai');
                            if (displayEl) displayEl.value = savedWaktu;
                        } else {
                            continue;
                        }
                    }
EOD;

$content = str_replace($oldJs, $newJs, $content);
file_put_contents($file, $content);
echo "Hybrid time fix applied.\n";
