<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/checklist/form.php';
$content = file_get_contents($file);

$content = str_replace(
    '} else {
                    // Validasi lolos, lakukan pengecekan duplikasi ke server
                    e.preventDefault(); // Tahan pengiriman form',
    '} else {
                    <?php if (isset($isEdit) && $isEdit): ?>
                    // Jika edit, jangan blokir submit
                    <?php else: ?>
                    // Validasi lolos, lakukan pengecekan duplikasi ke server
                    e.preventDefault(); // Tahan pengiriman form',
    $content
);

$content = str_replace(
    '// Jika gagal ngecek, biarkan submit
                        HTMLFormElement.prototype.submit.call(form);
                    });
                }
            });',
    '// Jika gagal ngecek, biarkan submit
                        HTMLFormElement.prototype.submit.call(form);
                    });
                    <?php endif; ?>
                }
            });',
    $content
);

file_put_contents($file, $content);
echo "Duplication check bypassed for edit mode.\n";
