<?= view('layout/header', ['title' => $title]) ?>

<!-- Include FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<?php $canEditJadwal = in_array(session()->get('role'), ['admin', 'member'], true); ?>
<?php if ($canEditJadwal): ?>
<!-- Modal Import Excel -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= site_url('admin/jadwal/import') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="importModalLabel"><i class="bi bi-upload me-2"></i>Import Jadwal via Excel</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info small py-2">
            <strong>Petunjuk:</strong> Gunakan template Excel kami agar format terbaca dengan benar.
          </div>
          <div class="mb-3">
            <a href="<?= site_url('admin/jadwal/template') ?>" class="btn btn-sm btn-outline-primary mb-3">
              <i class="bi bi-download"></i> Download Template
            </a>
            <br>
            <label class="form-label fw-semibold">Pilih File Excel (.xlsx, .xls, .csv)</label>
            <input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls, .csv" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-cloud-arrow-up"></i> Upload & Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<style>
  #calendar {
    max-width: 100%;
    margin: 0 auto;
    font-family: inherit;
  }
  
  .fc {
    --fc-border-color: #e2e8f0;
    --fc-today-bg-color: rgba(13, 110, 253, 0.06);
  }

  .fc .fc-toolbar-title {
    font-size: 1.15rem !important;
    font-weight: 800;
    color: #0f172a;
  }

  .fc-button {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    color: #475569 !important;
    font-weight: 600 !important;
    font-size: 0.78rem !important;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
    padding: 0.4rem 0.8rem !important;
    border-radius: 8px !important;
    transition: all 0.15s ease;
  }
  .fc-button:hover {
    background: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
  }
  .fc-button-active {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
    color: #ffffff !important;
  }

  .fc-event {
    cursor: pointer;
    font-size: 0.72rem !important;
    font-weight: 600 !important;
    padding: 3px 7px !important;
    border-radius: 6px !important;
    border: none !important;
    border-left: 4px solid currentColor !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
  }
  .fc-event:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 6px rgba(0,0,0,0.12);
  }

  .fc-day-sat, .fc-day-sun {
    background-color: #f8fafc;
  }
  .fc-day-today {
    outline: 2px solid #0d6efd;
    outline-offset: -2px;
    border-radius: 4px;
  }
  .fc-col-header-cell {
    background-color: #f1f5f9;
    padding: 8px 0 !important;
    font-weight: 700 !important;
    color: #334155 !important;
    border-bottom: 2px solid #cbd5e1 !important;
    font-size: 0.82rem;
  }
  .fc-daygrid-day-number {
    font-weight: 700;
    color: #64748b;
    padding: 6px 8px !important;
    font-size: 0.82rem;
  }

  /* Week preview card */
  .week-preview {
    background: linear-gradient(135deg, #eff6ff, #f0f9ff);
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    padding: 12px 16px;
    margin-top: 8px;
  }
  .week-preview .day-badge {
    display: inline-block;
    background: #ffffff;
    border: 1px solid #93c5fd;
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #1d4ed8;
    margin: 2px;
  }
</style>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h5 class="mb-0"><i class="bi bi-calendar-event me-2 text-primary"></i>Jadwal Kerja Pengecekan Preventive</h5>
    <p class="text-muted small mb-0">Kelola dan atur jadwal rencana pengecekan mingguan untuk MFG 1 dan MFG 2.</p>
  </div>
  <?php if ($canEditJadwal): ?>
  <div class="d-flex gap-2">
    <a href="<?= site_url('admin/jadwal/export') ?>" class="btn btn-outline-success btn-sm fw-bold shadow-sm">
      <i class="bi bi-file-earmark-excel"></i> Export Excel
    </a>
    <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#importModal">
      <i class="bi bi-upload"></i> Import Excel
    </button>
  </div>
  <?php endif; ?>
</div>

<div class="row g-4">
  <!-- Calendar Grid -->
  <div class="<?= $canEditJadwal ? 'col-xl-9 col-lg-8' : 'col-12' ?>">
    <div class="card border-0 shadow-sm bg-white p-3 rounded-4">
      <div id="calendar"></div>
    </div>
  </div>

  <?php if ($canEditJadwal): ?>
  <!-- Form Input Jadwal -->
  <div class="col-xl-3 col-lg-4">
    <div class="card border-0 shadow-sm bg-white p-4 rounded-4 position-sticky" style="top: 20px;">
      <h6 class="fw-bold mb-3 text-primary d-flex align-items-center gap-2">
        <i class="bi bi-calendar-plus"></i> Buat Jadwal Pekanan
      </h6>
      <hr class="mt-0 mb-3 text-muted opacity-25">

      <?php if (session()->has('error')): ?>
        <div class="alert alert-danger py-2 small rounded-3"><?= session('error') ?></div>
      <?php endif; ?>
      <?php if (session()->has('success')): ?>
        <div class="alert alert-success py-2 small rounded-3"><?= session('success') ?></div>
      <?php endif; ?>
      
      <form id="addEventForm" action="<?= site_url('admin/jadwal/store') ?>" method="post">
        <?= csrf_field() ?>
        
        <!-- Hidden: auto-calculated -->
        <input type="hidden" name="bulan_tahun" id="inputBulanTahun">
        <input type="hidden" name="periode_ke" id="inputPeriodeKe">

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted mb-1">Lokasi MFG</label>
          <select name="lokasi" id="lokasiSelect" class="form-select rounded-3" required>
            <option value="MFG 1">MFG 1</option>
            <option value="MFG 2">MFG 2</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted mb-1">Kategori</label>
          <select name="kategori" id="kategoriSelect" class="form-select rounded-3" required>
            <?php foreach ($categories as $kKey => $kVal): ?>
              <option value="<?= esc($kKey) ?>"><?= esc($kVal) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label small fw-semibold text-muted mb-1">Pilih Tanggal (Senin - Jumat)</label>
          <input type="date" name="tanggal_rencana" id="inputTanggalRencana" class="form-control rounded-3" required>
          <div class="form-text text-muted" style="font-size:0.72rem;">Pilih tanggal di dalam pekan yang akan dijadwalkan. Sistem akan otomatis menghitung rentang Senin s.d Jumat.</div>
        </div>

        <!-- Preview Rentang Minggu -->
        <div id="weekPreview" class="week-preview" style="display: none;">
          <div class="fw-semibold small text-primary mb-1"><i class="bi bi-calendar-week me-1"></i> Pekan ke-<span id="previewPeriode">-</span></div>
          <div id="previewDays"></div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 shadow-none mt-3">
          <i class="bi bi-plus-lg me-1"></i> Simpan Jadwal
        </button>
      </form>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Delete Event -->
<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
        <h6 class="modal-title fw-bold text-danger"><i class="bi bi-trash3 me-1.5"></i>Hapus Jadwal</h6>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
      </div>
      <form id="deleteEventForm" method="post" action="" onsubmit="return confirm('⚠️ PERINGATAN! ⚠️\n\nApakah Anda sangat yakin ingin menghapus jadwal ini?\n\nJadwal yang sudah dihapus tidak dapat dikembalikan lagi!');">
        <?= csrf_field() ?>
        <div class="modal-body px-4 pt-3">
          <p class="text-muted mb-2" style="font-size:0.88rem;">Hapus jadwal rencana pengecekan pekanan berikut?</p>
          <div class="bg-light p-3 rounded-3 border">
            <span class="text-muted d-block small mb-1">Agenda:</span>
            <strong class="text-dark d-block mb-2" id="deleteEventTitle"></strong>
            <span class="text-muted d-block small mb-1">Rentang Pekan:</span>
            <strong class="text-primary d-block" id="deleteEventRange"></strong>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger btn-sm px-4 rounded-3">Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

  // === Date picker: auto-calculate week preview ===
  const inputTanggal = document.getElementById('inputTanggalRencana');
  const inputBulanTahun = document.getElementById('inputBulanTahun');
  const inputPeriodeKe = document.getElementById('inputPeriodeKe');
  const weekPreview = document.getElementById('weekPreview');
  const previewPeriode = document.getElementById('previewPeriode');
  const previewDays = document.getElementById('previewDays');

  const hariNama = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

  <?php if ($canEditJadwal): ?>
  inputTanggal.addEventListener('change', function() {
    const val = this.value;
    if (!val) { weekPreview.style.display = 'none'; return; }

    const dateObj = new Date(val);
    const year = dateObj.getFullYear();
    const month = dateObj.getMonth(); // 0-indexed
    const day = dateObj.getDate();

    // Hitung periode_ke (1 s.d 5)
    let periodeKe = Math.floor((day - 1) / 7) + 1;
    if (periodeKe > 5) periodeKe = 5;

    const monthStr = String(month + 1).padStart(2, '0');
    inputBulanTahun.value = `${year}-${monthStr}`;
    inputPeriodeKe.value = periodeKe;

    // Hitung Senin s.d Jumat dari pekan yang bersangkutan
    // Cari hari Senin terdekat dari tanggal awal pekan
    const startDay = 1 + (periodeKe - 1) * 7;
    const baseDate = new Date(year, month, startDay);
    
    // Cari Senin: jika baseDate sudah Senin pakai itu, jika bukan geser ke Senin
    const dayOfWeek = baseDate.getDay(); // 0=Min, 1=Sen, ...
    const diffToMonday = (dayOfWeek === 0) ? 1 : (dayOfWeek === 1 ? 0 : (8 - dayOfWeek));
    const monday = new Date(baseDate);
    monday.setDate(baseDate.getDate() + diffToMonday);

    // Tetap dalam batas bulan: jika Senin keluar bulan, gunakan tanggal awal pekan itu sendiri
    if (monday.getMonth() !== month) {
      monday.setTime(baseDate.getTime());
    }

    previewPeriode.innerText = periodeKe;
    previewDays.innerHTML = '';
    for (let i = 0; i < 5; i++) {
      const d = new Date(monday);
      d.setDate(monday.getDate() + i);
      if (d.getMonth() !== month) break; // Jangan keluar bulan
      const label = `${hariNama[d.getDay()]} ${String(d.getDate()).padStart(2,'0')}/${monthStr}`;
      previewDays.innerHTML += `<span class="day-badge">${label}</span> `;
    }
    weekPreview.style.display = 'block';
  });


  <?php endif; ?>

  // === FullCalendar ===
  const calendarEl = document.getElementById('calendar');
  const deleteModal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
  const deleteForm = document.getElementById('deleteEventForm');
  const deleteTitle = document.getElementById('deleteEventTitle');
  const deleteRange = document.getElementById('deleteEventRange');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    contentHeight: 'auto', // Kalender membesar sesuai isi, tanpa scroll dalam
    aspectRatio: 1.2,
    weekends: true,
    firstDay: 0, // Minggu di kiri
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth'
    },
    locale: 'id',
    events: '<?= site_url("admin/jadwal/events") ?>',
    editable: false,
    
    <?php if ($canEditJadwal): ?>
    // Klik event untuk hapus
    eventClick: function(info) {
      const id = info.event.id;
      const title = info.event.title;

      const namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

      // Hitung Senin dan Jumat dari tanggal start event
      const startDate = new Date(info.event.start);
      // Pastikan kita punya hari Senin (start sudah Senin dari backend)
      const monday = new Date(startDate);
      const friday = new Date(monday);
      friday.setDate(monday.getDate() + 4);

      const fmtDate = (d) => `${d.getDate()} ${namaBulan[d.getMonth()]} ${d.getFullYear()}`;

      deleteForm.action = '<?= site_url("admin/jadwal/delete") ?>/' + id;
      deleteTitle.innerText = title;
      deleteRange.innerText = fmtDate(monday) + ' s.d ' + fmtDate(friday);
      deleteModal.show();
    },

    // Klik tanggal: isi otomatis form input tanggal
    dateClick: function(info) {
      inputTanggal.value = info.dateStr;
      inputTanggal.dispatchEvent(new Event('change'));
    }
    <?php else: ?>
    eventClick: function(info) {
      // Do nothing for non-admins
    },
    dateClick: function(info) {
      // Do nothing for non-admins
    }
    <?php endif; ?>
  });



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