<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/admin/jadwal/index.php';
$content = file_get_contents($file);

// Add ID to form
$content = str_replace(
    '<form action="<?= site_url(\'admin/jadwal/store\') ?>" method="post">',
    '<form id="addEventForm" action="<?= site_url(\'admin/jadwal/store\') ?>" method="post">',
    $content
);

$jsInjection = <<<'EOD'
  calendar.render();

  // === Intercept form submit untuk peringatan duplikasi mfg di minggu yang sama ===
  const addEventForm = document.getElementById('addEventForm');
  if (addEventForm) {
      addEventForm.addEventListener('submit', function(e) {
          const lokasi = document.getElementById('lokasiSelect').value;
          const kategori = document.getElementById('kategoriSelect').value;
          const tanggalVal = document.getElementById('inputTanggalRencana').value;
          
          if (!tanggalVal) return;
          
          const selDate = new Date(tanggalVal);
          const dayOfWeek = selDate.getDay() || 7;
          const monday = new Date(selDate);
          monday.setDate(selDate.getDate() - (dayOfWeek - 1));
          
          const mY = monday.getFullYear();
          const mM = monday.getMonth();
          const mD = monday.getDate();
          
          const events = calendar.getEvents();
          let otherCat = '';
          
          for (let i = 0; i < events.length; i++) {
              const ev = events[i];
              if (!ev.start) continue;
              const evDate = new Date(ev.start);
              if (evDate.getFullYear() === mY && evDate.getMonth() === mM && evDate.getDate() === mD) {
                  if (ev.extendedProps && ev.extendedProps.lokasi === lokasi && ev.extendedProps.kategori !== kategori) {
                      otherCat = ev.extendedProps.kategori;
                      break;
                  }
              }
          }
          
          if (otherCat !== '') {
              const msg = `Apakah Anda ingin menambahkan?\n\nSudah ada jadwal ${lokasi} kategori ${otherCat} pada pekan yang sama.`;
              if (!confirm(msg)) {
                  e.preventDefault();
              }
          }
      });
  }
EOD;

$content = str_replace('  calendar.render();', $jsInjection, $content);
file_put_contents($file, $content);
echo "Cross-category warning implemented.\n";
