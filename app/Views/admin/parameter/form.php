<?= view('layout/header', ['title' => $title]) ?>

<div class="page-header">
  <div>
    <h5 class="mb-0"><?= esc($title) ?></h5>
    <p class="text-muted small mb-0">Isi kolom di bawah ini untuk mendefinisikan baris parameter baru.</p>
  </div>
</div>

<div class="card p-4 border-0 shadow-sm bg-white" style="max-width: 650px;">
  <form action="<?= $parameter ? site_url('admin/parameter/update/' . $parameter['id_parameter']) : site_url('admin/parameter/store') ?>" method="post">
    <?= csrf_field() ?>

    <div class="row g-3 mb-3">
      <!-- Departemen -->
      <div class="col-md-6">
        <label class="form-label">Departemen</label>
        <?php 
          $departemenVal = old('departemen', $parameter['departemen'] ?? ($prefill['departemen'] ?? 'MFG 1')); 
        ?>
        <select name="departemen" class="form-select" required>
          <option value="MFG 1" <?= $departemenVal === 'MFG 1' ? 'selected' : '' ?>>MFG 1</option>
          <option value="MFG 2" <?= $departemenVal === 'MFG 2' ? 'selected' : '' ?>>MFG 2</option>
        </select>
      </div>

      <!-- Jenis Check -->
      <div class="col-md-6">
        <label class="form-label">Jenis Check</label>
        <?php 
          $jenisVal = old('jenis_check', $parameter['jenis_check'] ?? ($prefill['jenis_check'] ?? 'Preventive')); 
        ?>
        <select name="jenis_check" class="form-select" id="jenisCheckSelect" required>
          <option value="Preventive" <?= $jenisVal === 'Preventive' ? 'selected' : '' ?>>Checklist Report</option>
          <option value="Overhaul" <?= $jenisVal === 'Overhaul' ? 'selected' : '' ?>>Overhaul</option>
        </select>
      </div>
    </div>

    <!-- Kategori -->
    <div class="mb-3">
      <label class="form-label">Kategori</label>
      <input type="text" name="kategori" class="form-control" required placeholder="Contoh: Penerangan, Bearing"
             value="<?= esc(old('kategori', $parameter['kategori'] ?? ($prefill['kategori'] ?? ''))) ?>">
    </div>

    <!-- Section Check (Khusus Overhaul) -->
    <div class="mb-3" id="sectionCheckWrapper">
      <label class="form-label text-primary">Section Check (Khusus Overhaul)</label>
      <input type="text" name="section_check" class="form-control border-primary" placeholder="Contoh: BALLSCREW, EQUIPMENT CHECK"
             value="<?= esc(old('section_check', $parameter['section_check'] ?? '')) ?>">
      <div class="form-text text-muted">Judul bagian besar form overhaul untuk memisahkan kategori tabel.</div>
    </div>

    <!-- Bagian Check -->
    <div class="mb-3">
      <label class="form-label">Bagian Check</label>
      <input type="text" name="bagian_check" class="form-control" required placeholder="Contoh: Lampu Sorot, Bearing Spindle"
             value="<?= esc(old('bagian_check', $parameter['bagian_check'] ?? '')) ?>">
    </div>

    <!-- Point Check -->
    <div class="mb-3">
      <label class="form-label">Point Check</label>
      <input type="text" name="point_check" class="form-control" required placeholder="Contoh: Fungsi, Noise, Temperature"
             value="<?= esc(old('point_check', $parameter['point_check'] ?? '')) ?>">
    </div>

    <!-- Standard Check -->
    <div class="mb-3">
      <label class="form-label">Standard Check</label>
      <input type="text" name="standard_check" class="form-control" required placeholder="Contoh: Nyala, Halus, 40° C - 50° C"
             value="<?= esc(old('standard_check', $parameter['standard_check'] ?? '')) ?>">
    </div>

    <!-- Urutan -->
    <div class="mb-4">
      <label class="form-label">Urutan Baris</label>
      <input type="number" name="urutan" class="form-control" required min="0"
             value="<?= esc(old('urutan', $parameter['urutan'] ?? '0')) ?>">
      <div class="form-text">Mengatur urutan tampil baris di form pengisian (berkelompok).</div>
    </div>

    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Parameter</button>
      <?php $currentKategori = old('kategori', $parameter['kategori'] ?? ($prefill['kategori'] ?? '')); ?>
      <a href="<?= site_url('admin/parameter?departemen=' . urlencode($departemenVal) . '&jenis_check=' . urlencode($jenisVal) . '&kategori=' . urlencode($currentKategori)) ?>" class="btn btn-outline-secondary px-4">Batal</a>
    </div>
  </form>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const jenisSelect = document.getElementById("jenisCheckSelect");
    const sectionWrapper = document.getElementById("sectionCheckWrapper");

    function toggleOverhaulFields() {
      if (jenisSelect.value === "Overhaul") {
        sectionWrapper.style.display = "block";
      } else {
        sectionWrapper.style.display = "none";
        // Kosongkan agar tidak ikut terkirim/tersimpan lama
        sectionWrapper.querySelector("input").value = "";
      }
    }

    jenisSelect.addEventListener("change", toggleOverhaulFields);
    toggleOverhaulFields(); // Pemicu inisialisasi awal
  });
</script>

<?= view('layout/footer') ?>
