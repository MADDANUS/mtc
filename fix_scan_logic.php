<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/checklist/form.php';
$content = file_get_contents($file);

$oldLogic = <<<'EOD'
                const lastActivity = data['_last_activity'] || 0;
                const now = Date.now();
                let gapMinutes = 0;
EOD;

$newLogic = <<<'EOD'
                // Cek jika user masuk via scan (URL bawa id_mesin)
                const urlParams = new URLSearchParams(window.location.search);
                const urlIdMesin = urlParams.get('id_mesin');
                
                if (urlIdMesin && data["id_mesin"] && urlIdMesin !== data["id_mesin"]) {
                    // Mesin yang di-scan BEDA dengan draf yang tersimpan.
                    // Jangan load draf mesin lain ke mesin yang baru di-scan!
                    localStorage.removeItem(key);
                    return;
                }

                const lastActivity = data['_last_activity'] || 0;
                const now = Date.now();
                let gapMinutes = 0;
EOD;
$content = str_replace($oldLogic, $newLogic, $content);

$oldMesin = <<<'EOD'
                const mesinSelect = document.querySelector('select[name="id_mesin"]');
                if (mesinSelect && mesinSelect.tomselect && data["id_mesin"]) {
                    mesinSelect.tomselect.setValue(data["id_mesin"], true);
                }
EOD;

$newMesin = <<<'EOD'
                const mesinSelect = document.querySelector('select[name="id_mesin"]');
                if (mesinSelect && mesinSelect.tomselect && data["id_mesin"]) {
                    if (!urlIdMesin) { // Hanya override jika URL tidak melock mesin
                        mesinSelect.tomselect.setValue(data["id_mesin"], true);
                    }
                }
EOD;
$content = str_replace($oldMesin, $newMesin, $content);

file_put_contents($file, $content);
echo "Scan logic fixed.\n";
