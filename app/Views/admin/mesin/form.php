<?= view('layout/header', ['title' => $title]) ?>

<h5 class="mb-3"><?= esc($title) ?></h5>

<div class="card-stat p-3" style="max-width:600px;">
  <form action="<?= $mesin ? site_url('admin/mesin/update/' . $mesin['id_mesin']) : site_url('admin/mesin/store') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
      <label class="form-label">No Mesin</label>
      <input type="text" name="no_mesin" class="form-control" required
             value="<?= esc(old('no_mesin', $mesin['no_mesin'] ?? '')) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Type Mesin</label>
      <input type="text" name="type_mesin" class="form-control" required
             value="<?= esc(old('type_mesin', $mesin['type_mesin'] ?? '')) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Serial Nomor</label>
      <input type="text" name="serial_nomor" class="form-control" required
             value="<?= esc(old('serial_nomor', $mesin['serial_nomor'] ?? '')) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Lokasi</label>
      <?php $lokasiVal = old('lokasi', $mesin['lokasi'] ?? 'MFG 1'); ?>
      <select name="lokasi" class="form-select" required>
        <option value="MFG 1" <?= $lokasiVal === 'MFG 1' ? 'selected' : '' ?>>MFG 1</option>
        <option value="MFG 2" <?= $lokasiVal === 'MFG 2' ? 'selected' : '' ?>>MFG 2</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Line (Opsional)</label>
      <?php $lineVal = strtoupper(old('line', $mesin['line'] ?? '')); ?>
      <select name="line" class="form-select" data-selected="<?= esc($lineVal) ?>">
        <option value="">-- Pilih Line --</option>
      </select>
      <div class="form-text small">Pilih line tempat mesin ini berada (untuk akses approval Leader).</div>
    </div>
    <div class="mb-4">
      <label class="form-label text-primary fw-semibold">Bar Feeder Type (Opsional)</label>
      <input type="text" name="bar_feeder_type" class="form-control border-primary bg-primary bg-opacity-10" placeholder="Contoh: Iemca Boss 332" 
             value="<?= esc(old('bar_feeder_type', $mesin['bar_feeder_type'] ?? '')) ?>">
      <div class="form-text small">Diperlukan untuk otomatisasi form Overhaul. Biarkan kosong jika tidak memiliki Bar Feeder.</div>
    </div>
    <div class="mb-4">
      <label class="form-label text-primary fw-semibold">Jenis (Opsional)</label>
      <select name="jenis" class="form-select border-primary bg-primary bg-opacity-10">
        <option value="">-- Pilih Jenis --</option>
        <?php 
          $jenisVal = old('jenis', $mesin['jenis'] ?? '');
          $categories = [
              'CNC', 'MANUAL LATHE', 'TAPPING', 'BENDING', 'DRILLING', 'WASHING', 
              'THREAD', 
              'DOUBLE MILLING', 'MILLING', 'DOUBLE CENTER DRILL', 'OSL', 
              'KNURLING', 'BROTHER', 'BURNISHING', 'BUFFING', 'CENTERING GRINDING'
          ];
          foreach ($categories as $cat) {
              $selected = $jenisVal === $cat ? 'selected' : '';
              echo "<option value=\"{$cat}\" {$selected}>{$cat}</option>";
          }
        ?>
      </select>
      <div class="form-text small">Pilih jenis form agar saat scan QR otomatis diarahkan ke form yang tepat. (CNC untuk MFG 1, lainnya untuk MFG 2)</div>
    </div>


    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= site_url('admin/mesin') ?>" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lokasiSelect = document.querySelector('select[name="lokasi"]');
    const typeInput = document.querySelector('input[name="type_mesin"]');
    const serialInput = document.querySelector('input[name="serial_nomor"]');
    const lineSelect = document.querySelector('select[name="line"]');

    const lines = {
        'MFG 1': ['Line 1', 'Line 2', 'Line 3'],
        'MFG 2': ['CG', 'Second']
    };

    function updateLines() {
        const selectedLokasi = lokasiSelect.value;
        const selectedLine = lineSelect.getAttribute('data-selected');
        
        lineSelect.innerHTML = '<option value="">-- Pilih Line --</option>';
        
        if (lines[selectedLokasi]) {
            lines[selectedLokasi].forEach(line => {
                const option = document.createElement('option');
                option.value = line;
                option.textContent = line;
                if (line.toUpperCase() === selectedLine) {
                    option.selected = true;
                }
                lineSelect.appendChild(option);
            });
        }
    }

    function toggleRequired() {
        if (lokasiSelect.value === 'MFG 2') {
            typeInput.required = false;
            serialInput.required = false;
        } else {
            typeInput.required = true;
            serialInput.required = true;
        }
        updateLines();
    }

    if(lokasiSelect) {
        lokasiSelect.addEventListener('change', toggleRequired);
        toggleRequired(); // Run on init
        
        // Update data-selected when user manually changes it
        if(lineSelect) {
            lineSelect.addEventListener('change', function() {
                this.setAttribute('data-selected', this.value.toUpperCase());
            });
        }
    }
});
</script>

<?= view('layout/footer') ?>
