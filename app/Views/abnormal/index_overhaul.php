<?= view('layout/header', ['title' => $title]) ?>



<div class="d-flex align-items-center mb-3">
  <a href="<?= site_url('abnormal/overhaul?view=summary') ?>" class="btn btn-outline-secondary btn-sm me-3 shadow-sm rounded-pill px-3">
    <i class="bi bi-arrow-left me-1"></i> Kembali
  </a>
  
  <form method="GET" action="<?= site_url('abnormal/overhaul') ?>" class="d-flex gap-2 ms-auto align-items-center">
      <?php if(!empty($searchFilter)): ?>
          <input type="hidden" name="search" value="<?= esc($searchFilter) ?>">
      <?php endif; ?>
      <select name="lokasi" class="form-select form-select-sm border-0 shadow-sm fw-medium rounded-pill" style="width: auto;" onchange="this.form.submit()">
          <option value="all" <?= ($lokasiFilter === 'all') ? 'selected' : '' ?>>Semua Area</option>
          <option value="MFG 1" <?= ($lokasiFilter === 'MFG 1') ? 'selected' : '' ?>>MFG 1</option>
          <option value="MFG 2" <?= ($lokasiFilter === 'MFG 2') ? 'selected' : '' ?>>MFG 2</option>
      </select>
      <select name="bulan" class="form-select form-select-sm border-0 shadow-sm fw-medium rounded-pill" style="width: auto;" onchange="this.form.submit()">
          <option value="all" <?= ($bulanFilter === 'all') ? 'selected' : '' ?>>Semua Bulan</option>
          <?php if(isset($bulanList)): ?>
              <?php foreach ($bulanList as $val => $label): ?>
                  <option value="<?= $val ?>" <?= $val === $bulanFilter ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
          <?php endif; ?>
      </select>
  </form>

  <div class="ms-3 d-flex gap-2">
    <?php if (!in_array(session()->get('role'), ['leader', 'sheadprd', 'sheadmtc'])): ?>
    <a href="<?= site_url('abnormal/overhaul/pdf?lokasi=' . urlencode($lokasiFilter) . '&bulan=' . urlencode($bulanFilter) . '&search=' . urlencode($searchFilter)) ?>" target="_blank" class="btn btn-sm btn-danger fw-semibold shadow-sm" title="Download PDF">
      <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download PDF
    </a>
    <?php endif; ?>
  </div>
</div>

<table class="kop-table text-center shadow-sm">
  <tr>
    <td colspan="4" class="kop-table-title" style="padding: 10px;">LAPORAN ABNORMAL CONDITION OVERHAUL</td>
  </tr>
  <tr>
    <td class="kop-label text-start">AREA</td>
    <td class="kop-val text-start"><?= $lokasiFilter === 'all' ? 'Semua Area' : esc($lokasiFilter) ?></td>
    <td class="kop-label text-start">BULAN</td>
    <td class="kop-val text-start"><?= $bulanFilter === 'all' ? 'Semua Bulan' : esc($bulanFilter) ?></td>
  </tr>
</table>

<!-- ABNORMAL TABLE CARD -->
<div class="card border-0 shadow-sm bg-white overflow-hidden mb-4">
  <div class="card-body p-0">
    <div class="table-responsive" style="border: 2px solid #cbd5e1 !important; border-radius: 8px;">
      <table class="table align-middle text-center mb-0 abnormal-table" style="font-size: 0.8rem; border-collapse: collapse;">
        <thead>
          <tr class="table-light">
            <th rowspan="3" style="width: 3%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">NO</th>
            <th rowspan="3" style="width: 15%; font-weight:800; text-align: left; border-bottom: 2px solid #cbd5e1 !important;" class="ps-3">MESIN</th>
            <th rowspan="3" style="width: 12%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">POINT CHECK</th>
            <th rowspan="3" style="width: 14%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">ABNORMAL CONDITION</th>
            <th rowspan="3" style="width: 8%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">TYPE SPAREPART</th>
            <th colspan="2" style="width: 10%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PENGECEKAN</th>
            <th colspan="4" style="width: 22%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">RENCANA PERBAIKAN</th>
            <th rowspan="3" style="width: 6%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">KETERANGAN</th>
            <th colspan="2" style="width: 10%; font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">FOTO</th>
          </tr>
          <tr class="table-light">
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">TANGGAL</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PIC</th>
            <th colspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PROGRES</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">ACTION</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PIC</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">ABNORMAL</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PERBAIKAN</th>
          </tr>
          <tr class="table-light">
            <th style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">STOCK</th>
            <th style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">TANGGAL</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($reports)): ?>
            <tr>
              <td colspan="12" class="p-5 text-muted">
                <i class="bi bi-shield-check text-success" style="font-size: 2.5rem; display:block; margin-bottom:0.5rem;"></i>
                Tidak ada temuan kondisi abnormal yang tercatat.
              </td>
            </tr>
          <?php else: ?>
            <?php $no = 1; foreach ($reports as $r): ?>
              <?php 
                $canEdit = in_array(session()->get('role'), ['member', 'sheadprd', 'sheadmtc', 'admin'], true);
                $rowClass = $canEdit ? 'row-editable' : '';
              ?>
              <tr class="<?= $rowClass ?>" 
                  style="<?= $canEdit ? 'cursor: pointer;' : '' ?> transition: background-color 0.15s;"
                  data-id-abnormal="<?= $r['id_abnormal'] ?>"
                  data-mesin="<?= esc($r['no_mesin'] . ' - ' . $r['type_mesin'] . ' (' . $r['lokasi'] . ')') ?>"
                  data-point-check="<?= esc($r['point_check']) ?>"
                  data-abnormal-condition="<?= esc($r['abnormal_condition']) ?>"
                  data-type-sparepart="<?= esc($r['type_sparepart'] ?? '') ?>"
                  data-progres-stock="<?= esc($r['progres_stock'] ?? '') ?>"
                  data-progres-tanggal="<?= esc($r['progres_tanggal'] ?? '') ?>"
                  data-action="<?= esc($r['action'] ?? '') ?>"
                  data-repair-pic="<?= esc($r['repair_pic'] ?? '') ?>"
                  data-keterangan="<?= esc($r['keterangan'] ?? '') ?>"
                  data-foto-abnormal="<?= !empty($r['foto_abnormal']) ? base_url('uploads/abnormal/' . $r['foto_abnormal']) : '' ?>"
                  data-foto-perbaikan="<?= !empty($r['foto_perbaikan']) ? base_url('uploads/abnormal/' . $r['foto_perbaikan']) : '' ?>">
                
                <td class="fw-bold font-monospace text-secondary" style="background-color: #f8fafc;"><?= $no++ ?></td>
                <td class="text-start fw-bold text-dark ps-3"><?= esc($r['no_mesin']) ?> - <?= esc($r['type_mesin']) ?></td>
                <td><?= esc($r['point_check']) ?></td>
                <td class="text-danger fw-semibold"><?= esc($r['abnormal_condition']) ?></td>
                <td><?= esc($r['type_sparepart']) ?: '<span class="text-muted small">-</span>' ?></td>
                
                <!-- Pengecekan -->
                <td class="font-monospace"><?= date('d-m-Y', strtotime($r['pengecekan_tanggal'])) ?></td>
                <td><span class="fw-semibold text-dark"><?= esc($r['pengecekan_pic']) ?></span></td>
                
                <!-- Rencana Perbaikan -->
                <td>
                  <?php if ($r['progres_stock'] === 'Ready'): ?>
                    <span class="badge bg-success">Ready</span>
                  <?php elseif ($r['progres_stock'] === 'Indent'): ?>
                    <span class="badge bg-warning text-dark">Indent</span>
                  <?php elseif ($r['progres_stock'] === 'Not Available'): ?>
                    <span class="badge bg-danger">Not Available</span>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
                <td class="font-monospace"><?= $r['progres_tanggal'] ? date('d-m-Y', strtotime($r['progres_tanggal'])) : '<span class="text-muted">-</span>' ?></td>
                <td class="text-start"><?= esc($r['action']) ?: '<span class="text-muted">-</span>' ?></td>
                <td><span class="fw-semibold text-dark"><?= esc($r['repair_pic']) ?: '<span class="text-muted">-</span>' ?></span></td>
                
                <td><?= esc($r['keterangan']) ?: '<span class="text-muted">-</span>' ?></td>
                
                <!-- Foto Abnormal -->
                <td class="text-center p-1">
                  <?php if (!empty($r['foto_abnormal'])): ?>
                    <a href="<?= base_url('uploads/abnormal/' . $r['foto_abnormal']) ?>" target="_blank" title="Lihat Foto Abnormal">
                      <img src="<?= base_url('uploads/abnormal/' . $r['foto_abnormal']) ?>" alt="Foto Abnormal"
                           style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #dee2e6;">
                    </a>
                  <?php else: ?>
                    <span class="text-muted small">-</span>
                  <?php endif; ?>
                </td>
                
                <!-- Foto Perbaikan -->
                <td class="text-center p-1" onclick="event.stopPropagation()">
                  <?php if (!empty($r['foto_perbaikan'])): ?>
                    <a href="<?= base_url('uploads/abnormal/' . $r['foto_perbaikan']) ?>" target="_blank" title="Lihat Foto Perbaikan">
                      <img src="<?= base_url('uploads/abnormal/' . $r['foto_perbaikan']) ?>" alt="Foto Perbaikan"
                           style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #dee2e6;">
                    </a>
                  <?php else: ?>
                    <?php if ($canEdit): ?>
                      <button type="button" class="btn btn-sm btn-outline-success btn-foto-perbaikan py-0 px-1"
                              data-id-abnormal="<?= $r['id_abnormal'] ?>" title="Upload Foto Perbaikan">
                        <i class="bi bi-camera-fill" style="font-size:0.75rem;"></i>
                      </button>
                    <?php else: ?>
                      <span class="text-muted small">-</span>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<!-- MODAL QUICK EDIT ABNORMAL (LEADER & ADMIN ONLY) -->
<?php if (in_array(session()->get('role'), ['member', 'sheadprd', 'sheadmtc', 'admin'], true)): ?>
<div class="modal fade" id="editAbnormalModal" tabindex="-1" aria-labelledby="editAbnormalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
        <h6 class="modal-title fw-bold" id="editAbnormalModalLabel"><i class="bi bi-pencil-square text-primary me-1.5"></i>Tindak Lanjut Abnormal Condition</h6>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= site_url('abnormal/overhaul/update') ?>" method="post" id="abnormalUpdateForm" novalidate onsubmit="return validateAbnormalForm(event)">
        <?= csrf_field() ?>
        <input type="hidden" name="id_abnormal" id="modalIdAbnormal">

        <div class="modal-body px-4 pt-3">
          <div class="mb-3 bg-light p-3 rounded-3 border">
            <span class="text-muted d-block small fw-bold text-uppercase mb-1">Mesin & Temuan</span>
            <div class="fw-bold text-dark" id="modalMesinLabel">MC-01</div>
            <div class="small text-secondary mt-1">
              <strong class="text-danger">Point Check:</strong> <span id="modalPointCheckLabel"></span>
            </div>
            <div class="small text-secondary mt-0.5">
              <strong class="text-danger">Kondisi:</strong> <span id="modalConditionLabel"></span>
            </div>
          </div>

          <!-- Type Sparepart -->
          <div class="mb-3">
            <label class="form-label small fw-semibold">Type Sparepart</label>
            <input type="text" name="type_sparepart" id="modalTypeSparepart" class="form-control form-control-sm rounded-2" placeholder="Nama/Tipe sparepart">
          </div>

          <div class="row g-3 mb-3">
            <!-- Progres Stock -->
            <div class="col-6">
              <label class="form-label small fw-semibold">Progres Stock</label>
              <select name="progres_stock" id="modalProgresStock" class="form-select form-select-sm rounded-2">
                <option value="">-- Pilih Status --</option>
                <option value="Ready">Ready</option>
                <option value="Indent">Indent</option>
                <option value="Not Available">Not Available</option>
              </select>
            </div>
            <!-- Progres Tanggal -->
            <div class="col-6">
              <label class="form-label small fw-semibold">Rencana Tanggal Perbaikan</label>
              <input type="date" name="progres_tanggal" id="modalProgresTanggal" class="form-control form-control-sm rounded-2">
            </div>
          </div>

          <!-- Action -->
          <div class="mb-3">
            <label class="form-label small fw-semibold">Action (Tindakan Perbaikan)</label>
            <textarea name="action" id="modalAction" class="form-control form-control-sm rounded-2" rows="2" placeholder="Ketik deskripsi perbaikan..."></textarea>
          </div>

          <!-- Repair PIC -->
          <div class="mb-3">
            <label class="form-label small fw-semibold">PIC Perbaikan</label>
            <select name="repair_pic" id="modalRepairPic" class="form-select form-select-sm rounded-2" placeholder="Pilih atau ketik nama teknisi / PIC perbaikan">
              <option value="">-- Ketik atau Pilih PIC --</option>
              <?php foreach ($masterPic as $p): ?>
                <option value="<?= esc($p['nama_pic']) ?>"><?= esc($p['nama_pic']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Keterangan -->
          <div class="mb-3">
            <label class="form-label small fw-semibold">Keterangan Tambahan</label>
            <textarea name="keterangan" id="modalKeterangan" class="form-control form-control-sm rounded-2" rows="2" placeholder="Keterangan / remarks tambahan..."></textarea>
          </div>
        </div>

        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm px-4 rounded-3"><i class="bi bi-save me-1"></i> Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const editModal = new bootstrap.Modal(document.getElementById("editAbnormalModal"));
    const rows = document.querySelectorAll(".row-editable");

    rows.forEach(row => {
      row.addEventListener("click", function() {
        document.getElementById("modalIdAbnormal").value = this.getAttribute("data-id-abnormal");
        document.getElementById("modalMesinLabel").innerText = this.getAttribute("data-mesin");
        document.getElementById("modalPointCheckLabel").innerText = this.getAttribute("data-point-check");
        document.getElementById("modalConditionLabel").innerText = this.getAttribute("data-abnormal-condition");

        document.getElementById("modalTypeSparepart").value = this.getAttribute("data-type-sparepart");
        document.getElementById("modalProgresStock").value = this.getAttribute("data-progres-stock");
        document.getElementById("modalProgresTanggal").value = this.getAttribute("data-progres-tanggal");
        document.getElementById("modalAction").value = this.getAttribute("data-action");
        
        let repairPicVal = this.getAttribute("data-repair-pic");
        if (window.tomSelectRepairPic) {
            // Add option if it doesn't exist so we can select it
            window.tomSelectRepairPic.addOption({value: repairPicVal, text: repairPicVal});
            window.tomSelectRepairPic.setValue(repairPicVal);
        } else {
            document.getElementById("modalRepairPic").value = repairPicVal;
        }

        document.getElementById("modalKeterangan").value = this.getAttribute("data-keterangan");

        editModal.show();
      });
    });

    const form = document.getElementById("abnormalUpdateForm");
    if (form) {
      form.addEventListener("submit", validateAbnormalForm);
    }
    
    // Initialize TomSelect for Repair PIC
    if (document.getElementById("modalRepairPic")) {
      window.tomSelectRepairPic = new TomSelect("#modalRepairPic", {
          create: true, // Allow user to type in new values
          sortField: { field: "text", direction: "asc" },
          maxOptions: null,
          dropdownParent: "body" // Important for selects inside modals
      });
    }
  });

  function validateAbnormalForm(e) {
    const typeSparepart = document.getElementById("modalTypeSparepart").value.trim();
    const progresStock = document.getElementById("modalProgresStock").value.trim();
    const progresTanggal = document.getElementById("modalProgresTanggal").value.trim();
    const action = document.getElementById("modalAction").value.trim();
    const repairPic = document.getElementById("modalRepairPic").value.trim();
    const keterangan = document.getElementById("modalKeterangan").value.trim();

    if (!typeSparepart || !progresStock || !progresTanggal || !action || !repairPic || !keterangan) {
      e.preventDefault(); // Mencegah form dikirim
      Swal.fire({
        icon: 'warning',
        title: 'Form Belum Lengkap',
        text: 'Harap lengkapi semua isian form Tindak Lanjut sebelum menyimpan!',
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Oke, Paham'
      });
      return false;
    }
    return true;
  }
</script>
<?php endif; ?>

<?= view('layout/footer') ?>

<!-- MODAL UPLOAD FOTO PERBAIKAN (Overhaul) -->
<div class="modal fade" id="fotoRepairModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
        <h6 class="modal-title fw-bold"><i class="bi bi-camera-fill text-success me-2"></i>Upload Foto Perbaikan</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body px-4 pt-3 pb-2">
        <p class="small text-muted mb-3">Upload 1 foto sebagai bukti perbaikan selesai.</p>
        <input type="hidden" id="fotoRepairIdAbnormal" value="">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Pilih Foto <span class="text-danger">*</span></label>
          <input type="file" class="form-control form-control-sm" id="fotoRepairInput" accept="image/*" capture="environment">
          <div id="fotoRepairPreviewWrap" style="display:none; margin-top:8px;">
            <img id="fotoRepairPreviewImg" src="" alt="Preview" style="max-width:100%; border-radius:6px; border:1px solid #dee2e6;">
          </div>
        </div>
        <div id="fotoRepairMsg" class="small"></div>
      </div>
      <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
        <button type="button" class="btn btn-outline-secondary btn-sm px-3 rounded-3" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success btn-sm px-4 rounded-3" id="btnSimpanFotoRepair">
          <i class="bi bi-upload me-1"></i> Upload
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
    const fotoRepairModal = new bootstrap.Modal(document.getElementById('fotoRepairModal'));
    const fotoRepairInput = document.getElementById('fotoRepairInput');
    const fotoRepairPreviewWrap = document.getElementById('fotoRepairPreviewWrap');
    const fotoRepairPreviewImg = document.getElementById('fotoRepairPreviewImg');
    const fotoRepairMsg = document.getElementById('fotoRepairMsg');

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-foto-perbaikan');
        if (!btn) return;
        e.stopPropagation();
        document.getElementById('fotoRepairIdAbnormal').value = btn.getAttribute('data-id-abnormal');
        fotoRepairInput.value = '';
        fotoRepairPreviewWrap.style.display = 'none';
        fotoRepairPreviewImg.src = '';
        fotoRepairMsg.innerHTML = '';
        fotoRepairModal.show();
    });

    fotoRepairInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = ev => { fotoRepairPreviewImg.src = ev.target.result; fotoRepairPreviewWrap.style.display = 'block'; };
            reader.readAsDataURL(this.files[0]);
        } else { fotoRepairPreviewWrap.style.display = 'none'; }
    });

    document.getElementById('btnSimpanFotoRepair').addEventListener('click', function() {
        const idAbnormal = document.getElementById('fotoRepairIdAbnormal').value;
        const file = fotoRepairInput.files[0];
        if (!file) { fotoRepairMsg.innerHTML = '<span class="text-danger">Pilih foto terlebih dahulu.</span>'; return; }
        const formData = new FormData();
        formData.append('id_abnormal', idAbnormal);
        formData.append('foto_perbaikan', file);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        const btn = document.getElementById('btnSimpanFotoRepair');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengupload...';
        fotoRepairMsg.innerHTML = '';
        fetch('<?= site_url('abnormal/upload-foto-perbaikan') ?>', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-upload me-1"></i> Upload';
            if (data.success) {
                fotoRepairModal.hide();
                const camBtn = document.querySelector('.btn-foto-perbaikan[data-id-abnormal="' + idAbnormal + '"]');
                if (camBtn) { camBtn.closest('td').innerHTML = '<a href="' + data.foto_url + '" target="_blank"><img src="' + data.foto_url + '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #dee2e6;"></a>'; }
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false });
            } else { fotoRepairMsg.innerHTML = '<span class="text-danger">' + data.message + '</span>'; }
        })
        .catch(() => { btn.disabled = false; btn.innerHTML = '<i class="bi bi-upload me-1"></i> Upload'; fotoRepairMsg.innerHTML = '<span class="text-danger">Terjadi kesalahan.</span>'; });
    });
})();
</script>






