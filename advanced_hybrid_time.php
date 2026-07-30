<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/checklist/form.php';
$content = file_get_contents($file);

// 1. Add _last_activity to saveFormData
$oldSave = <<<'EOD'
        const formData = new FormData(form);
        const data = {};
        for (let [name, value] of formData.entries()) {
            if (data[name]) {
                if (!Array.isArray(data[name])) {
                    data[name] = [data[name]];
                }
                data[name].push(value);
            } else {
                data[name] = value;
            }
        }
        localStorage.setItem(key, JSON.stringify(data));
EOD;

$newSave = <<<'EOD'
        const formData = new FormData(form);
        const data = {};
        for (let [name, value] of formData.entries()) {
            if (data[name]) {
                if (!Array.isArray(data[name])) {
                    data[name] = [data[name]];
                }
                data[name].push(value);
            } else {
                data[name] = value;
            }
        }
        data['_last_activity'] = Date.now();
        localStorage.setItem(key, JSON.stringify(data));
EOD;
$content = str_replace($oldSave, $newSave, $content);

// 2. Modify loadFormData
$oldLoad = <<<'EOD'
        const saved = localStorage.getItem(key);
        if (saved) {
            try {
                const data = JSON.parse(saved);
                let hasData = false;
                for (let name in data) {
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
                    
                    const value = data[name];
EOD;

$newLoad = <<<'EOD'
        const saved = localStorage.getItem(key);
        if (saved) {
            try {
                const data = JSON.parse(saved);
                
                const lastActivity = data['_last_activity'] || 0;
                const now = Date.now();
                let gapMinutes = 0;
                
                if (lastActivity > 0) {
                    const lastDate = new Date(lastActivity).toLocaleDateString();
                    const nowDate = new Date(now).toLocaleDateString();
                    if (lastDate !== nowDate) {
                        localStorage.removeItem(key);
                        return; // Beda hari, hapus total autosave
                    }
                    gapMinutes = (now - lastActivity) / (1000 * 60);
                } else if (data['waktu_mulai']) {
                    const todayDate = "<?= date('Y-m-d') ?>";
                    if (!data['waktu_mulai'].startsWith(todayDate)) {
                        localStorage.removeItem(key);
                        return; // Fallback lama
                    }
                    gapMinutes = 999; // Force waktu_mulai to reset
                }

                let hasData = false;
                for (let name in data) {
                    if (name === '_last_activity' || name === 'csrf_test_name' || name === 'waktu_selesai') continue;
                    
                    if (name === 'waktu_mulai') {
                        if (gapMinutes <= 10) {
                            const displayEl = document.getElementById('displayWaktuMulai');
                            if (displayEl) displayEl.value = data[name];
                        } else {
                            continue; // Jeda > 10 menit, waktu_mulai direset
                        }
                    }
                    
                    const value = data[name];
EOD;
$content = str_replace($oldLoad, $newLoad, $content);

file_put_contents($file, $content);
echo "Advanced hybrid time logic applied.\n";
