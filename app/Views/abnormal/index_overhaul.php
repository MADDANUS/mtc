<?= view('layout/header', ['title' => $title]) ?>



<div class="d-flex align-items-center mb-3">
  <a href="<?= site_url('abnormal/overhaul?view=summary') ?>" class="btn btn-sm btn-outline-secondary fw-semibold shadow-sm me-2">
    <i class="bi bi-arrow-left me-1"></i> Kembali ke Ringkasan
  </a>
  <div class="ms-auto d-flex gap-2">
    <?php if (!in_array(session()->get('role'), ['leader', 'sheadprd', 'sheadmtc'])): ?>
    <a href="<?= site_url('abnormal/overhaul/pdf?departemen=' . urlencode($departemenFilter) . '&bulan=' . urlencode($bulanFilter) . '&search=' . urlencode($searchFilter)) ?>" target="_blank" class="btn btn-sm btn-danger fw-semibold shadow-sm" title="Preview PDF">
      <i class="bi bi-eye-fill me-1"></i> Preview PDF
    </a>
    <?php endif; ?>
  </div>
</div>

<form id="filterForm" method="GET" action="<?= site_url('abnormal/overhaul') ?>">
    <input type="hidden" name="departemen" value="<?= esc($departemenFilter) ?>">
    <input type="hidden" name="bulan" value="<?= esc($bulanFilter) ?>">
    <?php if(!empty($searchFilter)): ?>
        <input type="hidden" name="search" value="<?= esc($searchFilter) ?>">
    <?php endif; ?>
</form>


<!-- ABNORMAL TABLE CARD -->
<div class="card border-0 shadow-sm bg-white overflow-hidden mb-4">
  <div class="card-body p-0">
    <div class="table-responsive" style="border: 2px solid #cbd5e1 !important; border-radius: 8px;">
      <table class="table align-middle text-center mb-0 abnormal-table paginated-table" data-ajax-pagination="true" data-page-param="page_abnormal_overhaul" data-total-items="<?= esc($totalItems ?? 0) ?>" data-per-page="<?= esc($perPage ?? 15) ?>" data-current-page="<?= esc($_GET['page_abnormal_overhaul'] ?? 1) ?>" style="font-size: 0.8rem; border-collapse: collapse;">
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

          </tr>
          <tr class="table-light">
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">TANGGAL</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PIC</th>
            <th colspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PROGRES</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">ACTION</th>
            <th rowspan="2" style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">PIC</th>

          </tr>
          <tr class="table-light">
            <th style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">STOCK</th>
            <th style="font-weight:800; border-bottom: 2px solid #cbd5e1 !important;">TANGGAL</th>
          </tr>
        </thead>
        <tbody id="abnormalTableBody">
            <?= view('abnormal/_rows_overhaul', [
                'reports' => $reports,
                'startNo' => $startNo ?? 1
            ]) ?>
        </tbody>
      </table>
    </div>
  </div>
</div>


<!-- MODAL QUICK EDIT ABNORMAL -->
<?php if (in_array(session()->get('role'), ['member', 'sheadprd', 'sheadmtc', 'admin', 'magang', 'leader mtc'], true)): ?>
<div class="modal fade" id="editAbnormalModal" tabindex="-1" aria-labelledby="editAbnormalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
        <h6 class="modal-title fw-bold" id="editAbnormalModalLabel"><i class="bi bi-pencil-square text-primary me-1.5"></i>Tindak Lanjut Abnormal Condition</h6>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= site_url('abnormal/overhaul/update') ?>" method="post" id="abnormalUpdateForm" enctype="multipart/form-data" novalidate onsubmit="return validateAbnormalForm(event)">
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
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Progres Stock</label>
              <select name="progres_stock" id="modalProgresStock" class="form-select form-select-sm rounded-2">
                <option value="">-- Pilih Status --</option>
                <option value="Ready">Ready</option>
                <option value="Not Available">Not Available</option>
              </select>
            </div>
            <!-- Progres Tanggal -->
            <div class="col-12 col-md-6">
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
            <input type="text" name="repair_pic" id="modalRepairPic" class="form-control form-control-sm rounded-2 bg-light" readonly>
          </div>

          <!-- Foto Perbaikan -->
          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label small fw-semibold">Foto 1 <span class="text-danger fw-normal">(Wajib)</span></label>
              <div id="modalFoto1Preview" class="mb-2" style="display:none; position:relative;">
                 <img src="" style="width:100%; height:80px; object-fit:cover; border-radius:4px; border:1px solid #dee2e6;">
              </div>
              <div id="modalFoto1Controls" class="mb-2" style="display:none; gap: 8px;">
                 <button type="button" class="btn btn-outline-success btn-sm flex-fill btn-foto-perbaikan-modal" data-slot="1"><i class="bi bi-arrow-repeat"></i> Ulang</button>
                 <button type="button" class="btn btn-outline-danger btn-sm flex-fill" onclick="deleteFotoAbnormal(1)"><i class="bi bi-trash"></i> Hapus</button>
              </div>
              <button type="button" class="btn btn-outline-success btn-sm w-100 btn-foto-perbaikan-modal" id="modalFoto1UploadBtn" data-slot="1">
                <i class="bi bi-camera-fill me-1"></i> Ambil Foto 1
              </button>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold">Foto 2 <span class="text-muted fw-normal">(Opsional)</span></label>
              <div id="modalFoto2Preview" class="mb-2" style="display:none; position:relative;">
                 <img src="" style="width:100%; height:80px; object-fit:cover; border-radius:4px; border:1px solid #dee2e6;">
              </div>
              <div id="modalFoto2Controls" class="mb-2" style="display:none; gap: 8px;">
                 <button type="button" class="btn btn-outline-success btn-sm flex-fill btn-foto-perbaikan-modal" data-slot="2"><i class="bi bi-arrow-repeat"></i> Ulang</button>
                 <button type="button" class="btn btn-outline-danger btn-sm flex-fill" onclick="deleteFotoAbnormal(2)"><i class="bi bi-trash"></i> Hapus</button>
              </div>
              <button type="button" class="btn btn-outline-secondary btn-sm w-100 btn-foto-perbaikan-modal" id="modalFoto2UploadBtn" data-slot="2">
                <i class="bi bi-camera me-1"></i> Ambil Foto 2
              </button>
            </div>
          </div>

          <!-- Keterangan -->
          <div class="mb-3">
            <label class="form-label small fw-semibold">Keterangan Tambahan <span class="text-muted fw-normal">(Opsional)</span></label>
            <textarea name="keterangan" id="modalKeterangan" class="form-control form-control-sm rounded-2" rows="2" placeholder="Keterangan / remarks tambahan..."></textarea>
          </div>
        </div>

        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
          <?php if (session()->get('role') === 'admin' || session()->get('role') === 'leader mtc'): ?>
            <button type="button" class="btn btn-danger btn-sm px-3 rounded-3 me-auto" id="btnHapusTindakLanjut"><i class="bi bi-trash me-1"></i> Hapus</button>
          <?php endif; ?>
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
    document.addEventListener("click", function(e) {
      const row = e.target.closest(".row-editable");
      if (row) {
        document.getElementById("modalIdAbnormal").value = row.getAttribute("data-id-abnormal");
        document.getElementById("modalMesinLabel").innerText = row.getAttribute("data-mesin");
        document.getElementById("modalPointCheckLabel").innerText = row.getAttribute("data-point-check");
        document.getElementById("modalConditionLabel").innerText = row.getAttribute("data-abnormal-condition");

        document.getElementById("modalTypeSparepart").value = row.getAttribute("data-type-sparepart");
        document.getElementById("modalProgresStock").value = row.getAttribute("data-progres-stock");
        document.getElementById("modalProgresTanggal").value = row.getAttribute("data-progres-tanggal");
        document.getElementById("modalAction").value = row.getAttribute("data-action");
        
        let repairPicVal = row.getAttribute("data-repair-pic");
        if (!repairPicVal || repairPicVal === '-' || repairPicVal === '') {
            repairPicVal = "<?= esc(session()->get('nama') ?? 'MEMBER') ?>";
        }
        document.getElementById("modalRepairPic").value = repairPicVal;

        document.getElementById("modalKeterangan").value = row.getAttribute("data-keterangan");

        let foto1 = row.getAttribute("data-foto-perbaikan");
        let foto2 = row.getAttribute("data-foto-perbaikan-2");
        let preview1 = document.getElementById("modalFoto1Preview");
        let preview2 = document.getElementById("modalFoto2Preview");

        if (foto1) {
            preview1.style.display = 'block';
            preview1.querySelector('img').src = foto1;
            document.getElementById('modalFoto1Controls').style.display = 'flex';
            document.getElementById('modalFoto1UploadBtn').style.display = 'none';
        } else {
            preview1.style.display = 'none';
            document.getElementById('modalFoto1Controls').style.display = 'none';
            document.getElementById('modalFoto1UploadBtn').style.display = 'block';
        }

        if (foto2) {
            preview2.style.display = 'block';
            preview2.querySelector('img').src = foto2;
            document.getElementById('modalFoto2Controls').style.display = 'flex';
            document.getElementById('modalFoto2UploadBtn').style.display = 'none';
        } else {
            preview2.style.display = 'none';
            document.getElementById('modalFoto2Controls').style.display = 'none';
            document.getElementById('modalFoto2UploadBtn').style.display = 'block';
        }

                // --- RESTORE DRAFT IF EXISTS ---
        const draftKey = 'draft_abnormal_' + row.getAttribute("data-id-abnormal");
        const draftStr = localStorage.getItem(draftKey);
        if (draftStr) {
            try {
                const draft = JSON.parse(draftStr);
                // Check if draft is older than 10 minutes (600,000 ms)
                if (Date.now() - draft.timestamp < 600000) {
                    if (draft.typeSparepart !== undefined) document.getElementById("modalTypeSparepart").value = draft.typeSparepart;
                    if (draft.progresStock !== undefined) document.getElementById("modalProgresStock").value = draft.progresStock;
                    if (draft.progresTanggal !== undefined) document.getElementById("modalProgresTanggal").value = draft.progresTanggal;
                    if (draft.action !== undefined) document.getElementById("modalAction").value = draft.action;
                    if (draft.keterangan !== undefined) document.getElementById("modalKeterangan").value = draft.keterangan;

                    if (draft.foto1 && preview1) {
                        preview1.style.display = 'block';
                        preview1.querySelector('img').src = draft.foto1;
                        document.getElementById('modalFoto1Controls').style.display = 'flex';
                        document.getElementById('modalFoto1UploadBtn').style.display = 'none';
                    }
                    if (draft.foto2 && preview2) {
                        preview2.style.display = 'block';
                        preview2.querySelector('img').src = draft.foto2;
                        document.getElementById('modalFoto2Controls').style.display = 'flex';
                        document.getElementById('modalFoto2UploadBtn').style.display = 'none';
                    }
                } else {
                    // Draft expired, remove it
                    localStorage.removeItem(draftKey);
                }
            } catch(e) {
                console.error("Failed to parse draft", e);
            }
        }
        // --- END RESTORE DRAFT ---

        editModal.show();
      }
    });

    const form = document.getElementById("abnormalUpdateForm");
    if (form) {
      form.addEventListener("submit", validateAbnormalForm);
    }
    

  });

  function validateAbnormalForm(e) {
    // Jika isDeleting diset (lewat tombol Hapus), loloskan form
    if (window.isDeletingTindakLanjut) {
      return true;
    }

    const typeSparepart = document.getElementById("modalTypeSparepart").value.trim();
    const progresStock = document.getElementById("modalProgresStock").value.trim();
    const progresTanggal = document.getElementById("modalProgresTanggal").value.trim();
    const action = document.getElementById("modalAction").value.trim();
    const repairPic = document.getElementById("modalRepairPic").value.trim();
    const keterangan = document.getElementById("modalKeterangan").value.trim();

    const foto1Preview = document.getElementById("modalFoto1Preview");
    const foto1Exists = foto1Preview && foto1Preview.style.display !== 'none';

    if (!typeSparepart || !progresStock || !progresTanggal || !action || !repairPic || !foto1Exists) {
      e.preventDefault(); // Mencegah form dikirim
      Swal.fire({
        icon: 'warning',
        title: 'Form Belum Lengkap',
        text: 'Harap lengkapi semua isian form Tindak Lanjut (termasuk Foto 1) sebelum menyimpan! (Atau gunakan tombol Hapus untuk menghapus data)',
        confirmButtonColor: '#0d6efd',
        confirmButtonText: 'Oke, Paham'
      });
      return false;
    }
    return true;
  }

  // Tombol Hapus Tindak Lanjut
  const btnHapus = document.getElementById('btnHapusTindakLanjut');
  if (btnHapus) {
    btnHapus.addEventListener('click', function() {
      Swal.fire({
        title: 'Hapus Tindak Lanjut?',
        text: 'Semua isian tindakan perbaikan untuk point ini akan dikosongkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          // Kosongkan form
          document.getElementById('modalTypeSparepart').value = '';
          document.getElementById('modalProgresStock').value = '';
          document.getElementById('modalProgresTanggal').value = '';
          document.getElementById('modalAction').value = '';
          document.getElementById('modalRepairPic').value = '';
          document.getElementById('modalKeterangan').value = '';
          
          window.isDeletingTindakLanjut = true;
          let flag = document.createElement('input');
          flag.type = 'hidden';
          flag.name = 'hapus_semua';
          flag.value = '1';
          document.getElementById('abnormalUpdateForm').appendChild(flag);
          document.getElementById('abnormalUpdateForm').submit();
        }
      });
    });
  }
</script>
<?php endif; ?>

<?= view('layout/footer') ?>

<!-- ====== FOTO PERBAIKAN MODAL FULLSCREEN ====== -->
<div class="modal fade" id="fotoRepairModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen m-0">
    <div class="modal-content bg-dark text-white border-0" style="height:100vh;display:flex;flex-direction:column;">

      <!-- Header -->
      <div style="flex:0 0 auto;background:#111;padding:12px 16px;display:flex;align-items:center;gap:12px;border-bottom:1px solid #333;">
        <i class="bi bi-camera-fill text-success" style="font-size:1.3rem;"></i>
        <span class="fw-bold fs-5 me-auto">Foto Perbaikan</span>
        <div class="btn-group" role="group">
          <button type="button" id="rBtnModeCamera" class="btn btn-success btn-sm px-3">
            <i class="bi bi-camera-fill me-1"></i>Kamera
          </button>
          <button type="button" id="rBtnModeUpload" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-images me-1"></i>Galeri
          </button>
        </div>
        <button type="button" id="rBtnClose" class="btn-close btn-close-white ms-2"></button>
        <input type="hidden" id="rIdAbnormal" value="">
      </div>

      <!-- Camera Panel -->
      <div id="rPanelCamera" style="flex:1 1 auto;position:relative;overflow:hidden;background:#000;">
        <div id="rCamLoading" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;">
          <div class="spinner-border text-success mb-3" style="width:3.5rem;height:3.5rem;"></div>
          <span style="font-size:1.1rem;">Membuka kamera...</span>
        </div>
        <div id="rCamError" style="position:absolute;inset:0;display:none;flex-direction:column;align-items:center;justify-content:center;color:#dc3545;padding:2rem;text-align:center;">
          <i class="bi bi-camera-video-off" style="font-size:5rem;"></i>
          <p class="mt-3 fs-5" id="rCamErrorMsg">Kamera tidak dapat diakses.</p>
          <small style="color:#888;">Gunakan tab <b>Galeri</b> untuk upload dari file lokal.</small>
        </div>
        <video id="rCamVideo" autoplay playsinline muted
               style="position:absolute;inset:0;width:100%;height:100%;object-fit:contain;display:none;background:#000;"></video>
        <img id="rCamPreview" src="" alt=""
             style="position:absolute;inset:0;width:100%;height:100%;object-fit:contain;display:none;background:#000;">
        <div id="rSizeBadge" style="position:absolute;bottom:12px;right:12px;background:rgba(0,0,0,.65);color:#fff;font-size:.78rem;padding:4px 12px;border-radius:20px;display:none;"></div>
      </div>

      <!-- Upload Panel -->
      <div id="rPanelUpload" style="flex:1 1 auto;display:none;flex-direction:column;align-items:center;justify-content:center;padding:24px;background:#111;overflow-y:auto;">
        <label for="rFileInput" id="rDropZone"
               style="display:flex;flex-direction:column;align-items:center;justify-content:center;width:100%;max-width:520px;min-height:200px;border:2px dashed #555;border-radius:16px;padding:2rem;cursor:pointer;background:rgba(255,255,255,.03);">
          <i class="bi bi-images text-success" style="font-size:5rem;"></i>
          <span class="mt-3 fw-semibold" style="font-size:1.2rem;">Ketuk untuk pilih foto</span>
          <small style="color:#888;margin-top:6px;">JPG · PNG · HEIC · WEBP</small>
        </label>
        <input type="file" id="rFileInput" accept="image/*" class="d-none">
        <div id="rUploadPreviewWrap" style="display:none;width:100%;max-width:700px;margin-top:20px;text-align:center;">
          <img id="rUploadPreviewImg" src="" alt="Preview"
               style="max-width:100%;max-height:52vh;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.6);">
          <div id="rUploadInfo" style="margin-top:10px;color:#28a745;font-size:.9rem;"></div>
        </div>
        <div id="rUploadCompressing" style="display:none;margin-top:20px;text-align:center;">
          <div class="spinner-border text-success" style="width:2.5rem;height:2.5rem;"></div>
          <p style="margin-top:12px;color:#aaa;">Mengompresi foto...</p>
        </div>
      </div>

      <!-- Footer -->
      <div style="flex:0 0 auto;background:#111;border-top:1px solid #333;padding:20px 24px;display:flex;align-items:center;justify-content:center;gap:16px;min-height:110px;">
        <div id="rActLive" style="display:none;text-align:center;">
          <button type="button" id="rBtnShutter" class="btn btn-success rounded-circle"
                  style="width:90px;height:90px;font-size:2.5rem;box-shadow:0 0 0 8px rgba(40,167,69,.2);">
            <i class="bi bi-circle-fill"></i>
          </button>
          <p style="color:rgba(255,255,255,.45);font-size:.85rem;margin:10px 0 0;">Tekan untuk foto</p>
        </div>
        <div id="rActConfirm" style="display:none;gap:14px;justify-content:center;flex-wrap:wrap;">
          <button type="button" id="rBtnRetake" class="btn btn-outline-light btn-lg px-5">
            <i class="bi bi-arrow-repeat me-2"></i>Foto Ulang
          </button>
          <button type="button" id="rBtnUseCamera" class="btn btn-success btn-lg px-5">
            <i class="bi bi-cloud-upload me-2"></i>Upload Foto
          </button>
        </div>
        <div id="rActUseUpload" style="display:none;">
          <button type="button" id="rBtnUseUpload" class="btn btn-success btn-lg px-5">
            <i class="bi bi-cloud-upload me-2"></i>Upload Foto Ini
          </button>
        </div>
        <div id="rActUploading" style="display:none;text-align:center;">
          <div class="spinner-border text-success" style="width:2.5rem;height:2.5rem;"></div>
          <p style="color:#aaa;margin:10px 0 0;font-size:.9rem;">Mengupload...</p>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
(function () {
    // Kompresi config
    const MAX_W = 1440, MAX_H = 1440, JPEG_Q = 0.84;

    let _rStream = null, _rBlob = null, _rUploadBlob = null, _rIdAbnormal = null, _rSlotAbnormal = 1;

    const modalEl    = document.getElementById('fotoRepairModal');
    const rModal     = new bootstrap.Modal(modalEl);
    const rVideo     = document.getElementById('rCamVideo');
    const rPreview   = document.getElementById('rCamPreview');
    const rLoading   = document.getElementById('rCamLoading');
    const rError     = document.getElementById('rCamError');
    const rErrMsg    = document.getElementById('rCamErrorMsg');
    const rSizeBadge = document.getElementById('rSizeBadge');
    const panelCam   = document.getElementById('rPanelCamera');
    const panelUpl   = document.getElementById('rPanelUpload');
    const rFileInput = document.getElementById('rFileInput');
    const rDropZone  = document.getElementById('rDropZone');
    const rUpPrevWrap= document.getElementById('rUploadPreviewWrap');
    const rUpPrevImg = document.getElementById('rUploadPreviewImg');
    const rUpInfo    = document.getElementById('rUploadInfo');
    const rUpCompr   = document.getElementById('rUploadCompressing');
    const rActLive   = document.getElementById('rActLive');
    const rActConf   = document.getElementById('rActConfirm');
    const rActUpl    = document.getElementById('rActUseUpload');
    const rActUpling = document.getElementById('rActUploading');
    const btnModeCam = document.getElementById('rBtnModeCamera');
    const btnModeUpl = document.getElementById('rBtnModeUpload');
    const btnClose   = document.getElementById('rBtnClose');
    const btnShutter = document.getElementById('rBtnShutter');
    const btnRetake  = document.getElementById('rBtnRetake');
    const btnUseCam  = document.getElementById('rBtnUseCamera');
    const btnUseUpl  = document.getElementById('rBtnUseUpload');

    function fmt(b) { return b<1048576 ? (b/1024).toFixed(0)+' KB' : (b/1048576).toFixed(1)+' MB'; }

    function compressVideo(v, cb) {
        const w=v.videoWidth, h=v.videoHeight; if(!w||!h) return;
        const s=Math.min(MAX_W/w,MAX_H/h,1), cw=Math.round(w*s), ch=Math.round(h*s);
        const t=document.createElement('canvas'); t.width=cw; t.height=ch;
        t.getContext('2d').drawImage(v,0,0,cw,ch);
        t.toBlob(b=>cb(b,cw,ch),'image/jpeg',JPEG_Q);
    }
    function compressImg(img, cb) {
        const w=img.naturalWidth, h=img.naturalHeight;
        const s=Math.min(MAX_W/w,MAX_H/h,1), cw=Math.round(w*s), ch=Math.round(h*s);
        const t=document.createElement('canvas'); t.width=cw; t.height=ch;
        t.getContext('2d').drawImage(img,0,0,cw,ch);
        t.toBlob(b=>cb(b,cw,ch),'image/jpeg',JPEG_Q);
    }

    function s(el,v){el.style.display=v;} function h(el){el.style.display='none';}

    function stopStream(){if(_rStream){_rStream.getTracks().forEach(t=>t.stop());_rStream=null;}}

    function setCamState(state){
        s(rLoading,  state==='loading'    ?'flex':'none');
        s(rError,    state==='error'      ?'flex':'none');
        s(rVideo,    state==='live'       ?'block':'none');
        s(rPreview,  state==='confirm'    ?'block':'none');
        s(rSizeBadge,state==='confirm'    ?'block':'none');
        s(rActLive,  state==='live'       ?'block':'none');
        s(rActConf,  state==='confirm'    ?'flex':'none');
        h(rActUpl); h(rActUpling);
    }

    async function startCamera(){
        setCamState('loading'); _rBlob=null; stopStream();
        try {
            _rStream = await navigator.mediaDevices.getUserMedia({
                video:{facingMode:{ideal:'environment'},width:{ideal:1920},height:{ideal:1080}}
            });
            rVideo.srcObject=_rStream;
            rVideo.onloadedmetadata=()=>{rVideo.play();setCamState('live');};
        } catch(err){
            stopStream(); rErrMsg.textContent='Kamera tidak dapat diakses: '+err.message; setCamState('error');
        }
    }

    btnShutter.addEventListener('click',function(){
        if(!_rStream) return;
        setCamState('loading'); // show spinner while compressing
        compressVideo(rVideo,(blob,cw,ch)=>{
            _rBlob=blob; stopStream();
            rPreview.src=URL.createObjectURL(blob);
            rSizeBadge.textContent=cw+'×'+ch+' · '+fmt(blob.size);
            setCamState('confirm');
        });
    });
    btnRetake.addEventListener('click',()=>{_rBlob=null;startCamera();});

    // Upload mode
    function setUploadState(state){
        s(rDropZone,  state!=='ready'       ?'flex':'none');
        s(rUpCompr,   state==='compressing' ?'flex':'none');
        s(rUpPrevWrap,state==='ready'       ?'block':'none');
        s(rActUpl,    state==='ready'       ?'block':'none');
        h(rActLive); h(rActConf); h(rActUpling);
    }

    rFileInput.addEventListener('change',function(){
        const file=this.files[0]; if(!file) return;
        _rUploadBlob=null; setUploadState('compressing');
        const url=URL.createObjectURL(file);
        const img=new Image();
        img.onload=()=>{
            URL.revokeObjectURL(url);
            compressImg(img,(blob,cw,ch)=>{
                _rUploadBlob=blob;
                rUpPrevImg.src=URL.createObjectURL(blob);
                rUpInfo.innerHTML='<i class="bi bi-check-circle-fill me-1"></i>Dikompres: <b>'+cw+'×'+ch+'</b> · <b>'+fmt(blob.size)+'</b> <span style="color:#888;">(dari '+fmt(file.size)+')</span>';
                setUploadState('ready');
            });
        };
        img.src=url;
    });

    // Mode switch
    function switchMode(mode){
        if(mode==='camera'){
            s(panelCam,'block'); h(panelUpl);
            btnModeCam.className='btn btn-success btn-sm px-3';
            btnModeUpl.className='btn btn-outline-secondary btn-sm px-3';
            startCamera();
        } else {
            stopStream(); h(panelCam); s(panelUpl,'flex');
            btnModeCam.className='btn btn-outline-secondary btn-sm px-3';
            btnModeUpl.className='btn btn-success btn-sm px-3';
            _rUploadBlob=null; rFileInput.value=''; setUploadState('idle');
        }
    }
    btnModeCam.addEventListener('click',()=>switchMode('camera'));
    btnModeUpl.addEventListener('click',()=>switchMode('upload'));

    // Upload via AJAX
    function doUpload(blob){
        if(!blob||!_rIdAbnormal) return;
        h(rActConf); h(rActUpl); s(rActUpling,'block');
        const fd=new FormData();
        fd.append('id_abnormal',_rIdAbnormal);
        fd.append('foto_slot', _rSlotAbnormal);
        fd.append('foto_perbaikan',new File([blob],'repair_'+Date.now()+'.jpg',{type:'image/jpeg'}));
        fd.append('<?= csrf_token() ?>','<?= csrf_hash() ?>');
        fetch('<?= site_url('abnormal/upload-foto-perbaikan') ?>',{method:'POST',body:fd})
        .then(r=>r.json())
        .then(data=>{
            if(data.success){
                rModal.hide();
                Swal.fire({icon:'success',title:'Berhasil!',text:data.message,timer:1500,showConfirmButton:false}).then(() => {
                    const editModalEl = document.getElementById("editAbnormalModal");
                    if (editModalEl && _rIdAbnormal == document.getElementById('modalIdAbnormal').value) {
                        
                        // Update preview inside modal
                        let previewId = _rSlotAbnormal == 1 ? "modalFoto1Preview" : "modalFoto2Preview";
                        let controlsId = _rSlotAbnormal == 1 ? "modalFoto1Controls" : "modalFoto2Controls";
                        let uploadBtnId = _rSlotAbnormal == 1 ? "modalFoto1UploadBtn" : "modalFoto2UploadBtn";
                        
                        let previewEl = document.getElementById(previewId);
                        if (previewEl) {
                            previewEl.style.display = 'block';
                            previewEl.querySelector('img').src = data.foto_url;
                            document.getElementById(controlsId).style.display = 'flex';
                            if(typeof saveAbnormalDraft === 'function') saveAbnormalDraft();
                            document.getElementById(uploadBtnId).style.display = 'none';
                        }
                        
                        // Update row attribute so it persists if modal is closed and reopened
                        let row = document.querySelector(`tr[data-id-abnormal="${_rIdAbnormal}"]`);
                        if (row) {
                            if (_rSlotAbnormal == 1) {
                                row.setAttribute("data-foto-perbaikan", data.foto_url);
                            } else {
                                row.setAttribute("data-foto-perbaikan-2", data.foto_url);
                            }
                        }

                        // Reopen the edit modal so they don't lose typed text
                        bootstrap.Modal.getOrCreateInstance(editModalEl).show();
                    } else {
                        window.location.reload();
                    }
                });
            } else {
                h(rActUpling); s(rActConf,'flex');
                Swal.fire({icon:'error',title:'Gagal',text:data.message});
            }
        })
        .catch(()=>{
            h(rActUpling); s(rActConf,'flex');
            Swal.fire({icon:'error',title:'Error',text:'Terjadi kesalahan. Coba lagi.'});
        });
    }

    btnUseCam.addEventListener('click',()=>doUpload(_rBlob));
    btnUseUpl.addEventListener('click',()=>doUpload(_rUploadBlob));

    // Close
    btnClose.addEventListener('click',()=>{stopStream();rModal.hide();});
    modalEl.addEventListener('hidden.bs.modal',()=>{
        stopStream(); _rBlob=null; _rUploadBlob=null;
        rPreview.src=''; setCamState('loading');
    });

    // Open modal trigger
    document.addEventListener('click',function(e){
        const btn=e.target.closest('.btn-foto-perbaikan, .btn-foto-perbaikan-modal');
        if(!btn) return; e.stopPropagation();
        
        let idAbnormal = btn.getAttribute('data-id-abnormal');
        if(!idAbnormal && btn.classList.contains('btn-foto-perbaikan-modal')) {
            idAbnormal = document.getElementById('modalIdAbnormal').value;
        }
        
        _rIdAbnormal=idAbnormal;
        _rSlotAbnormal=btn.getAttribute('data-slot') || 1;
        document.getElementById('rIdAbnormal').value=_rIdAbnormal;
        
        // Hide edit modal if we are opening from inside it to prevent stacking issues
        const editModalEl = document.getElementById("editAbnormalModal");
        if (editModalEl && editModalEl.classList.contains('show')) {
            bootstrap.Modal.getInstance(editModalEl).hide();
        }
        
        rModal.show(); switchMode('camera');
    });
})();

function deleteFotoAbnormal(slot) {
    const idAbnormal = document.getElementById('modalIdAbnormal').value;
    if (!idAbnormal) return;

    Swal.fire({
        title: 'Hapus Foto?',
        text: 'Foto perbaikan ' + slot + ' akan dihapus secara permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('id_abnormal', idAbnormal);
            fd.append('foto_slot', slot);
            fd.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            fetch('<?= site_url('abnormal/delete-foto-perbaikan') ?>', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({icon: 'success', title: 'Terhapus!', text: data.message, timer: 1500, showConfirmButton: false});
                        
                        // Update Modal UI
                        document.getElementById('modalFoto' + slot + 'Preview').style.display = 'none';
                        document.getElementById('modalFoto' + slot + 'Controls').style.display = 'none';
                        document.getElementById('modalFoto' + slot + 'UploadBtn').style.display = 'block';
                        if(typeof saveAbnormalDraft === 'function') saveAbnormalDraft();
                        
                        // Update Table Row
                        let row = document.querySelector(`tr[data-id-abnormal="${idAbnormal}"]`);
                        if (row) {
                            if (slot == 1) {
                                row.setAttribute("data-foto-perbaikan", "");
                            } else {
                                row.setAttribute("data-foto-perbaikan-2", "");
                            }
                        }
                    } else {
                        Swal.fire({icon: 'error', title: 'Gagal', text: data.message});
                    }
                })
                .catch(() => {
                    Swal.fire({icon: 'error', title: 'Error', text: 'Gagal menghubungi server.'});
                });
        }
    });
}
</script>





<script>

  // --- AUTOSAVE LOGIC ---
  function getAbnormalDraftKey() {
    const el = document.getElementById("modalIdAbnormal");
    if (!el) { console.warn('[Autosave] modalIdAbnormal elemen tidak ditemukan!'); return null; }
    const idAbnormal = el.value;
    if (!idAbnormal) { console.warn('[Autosave] idAbnormal kosong!'); return null; }
    return 'draft_abnormal_' + idAbnormal;
  }

  let autosaveNotifTimer = null;
  function saveAbnormalDraft() {
    const key = getAbnormalDraftKey();
    if (!key) { console.warn('[Autosave] Key null, batal simpan.'); return; }

    const foto1Preview = document.getElementById("modalFoto1Preview");
    const foto2Preview = document.getElementById("modalFoto2Preview");

    const draft = {
      timestamp: Date.now(),
      typeSparepart: (document.getElementById("modalTypeSparepart") || {value:''}).value,
      progresStock: (document.getElementById("modalProgresStock") || {value:''}).value,
      progresTanggal: (document.getElementById("modalProgresTanggal") || {value:''}).value,
      action: (document.getElementById("modalAction") || {value:''}).value,
      keterangan: (document.getElementById("modalKeterangan") || {value:''}).value,
      foto1: (foto1Preview && foto1Preview.style.display !== 'none' && foto1Preview.querySelector('img')) ? foto1Preview.querySelector('img').src : '',
      foto2: (foto2Preview && foto2Preview.style.display !== 'none' && foto2Preview.querySelector('img')) ? foto2Preview.querySelector('img').src : ''
    };
    
    try {
        localStorage.setItem(key, JSON.stringify(draft));
        console.log('[Autosave] Tersimpan:', key);
    } catch(e) {
        console.error('[Autosave] ERROR:', e);
    }
  }

  // Event delegation pada document (capture phase - tidak bisa diblokir)
  document.addEventListener('input', function(e) {
    const ids = ['modalTypeSparepart','modalProgresStock','modalProgresTanggal','modalAction','modalKeterangan'];
    if (e.target && ids.includes(e.target.id)) {
        console.log('[Autosave] Input terdeteksi pada:', e.target.id);
        saveAbnormalDraft();
    }
  }, true);
  
  document.addEventListener('change', function(e) {
    const ids = ['modalTypeSparepart','modalProgresStock','modalProgresTanggal','modalAction','modalKeterangan'];
    if (e.target && ids.includes(e.target.id)) {
        console.log('[Autosave] Change terdeteksi pada:', e.target.id);
        saveAbnormalDraft();
    }
  }, true);

  // BACKUP: Polling setiap 3 detik saat modal terbuka
  setInterval(function() {
      const modalEl = document.getElementById("editAbnormalModal");
      if (modalEl && modalEl.classList.contains("show")) {
          const typeSparepart = (document.getElementById("modalTypeSparepart") || {value:''}).value.trim();
          const action = (document.getElementById("modalAction") || {value:''}).value.trim();
          const keterangan = (document.getElementById("modalKeterangan") || {value:''}).value.trim();
          if (typeSparepart || action || keterangan) {
              console.log('[Autosave Interval] Modal terbuka, ada isian, menyimpan...');
              saveAbnormalDraft();
          }
      }
  }, 3000);

  // Hapus draft saat form submit sukses
  if (typeof validateAbnormalForm === 'function') {
      var _origValidate = validateAbnormalForm;
      validateAbnormalForm = function(e) {
        var res = _origValidate(e);
        if (res !== false) {
          var key = getAbnormalDraftKey();
          if (key) localStorage.removeItem(key);
        }
        return res;
      }
  }

  // Hapus draft saat tombol Hapus diklik
  var btnHapus2 = document.getElementById("btnHapusTindakLanjut");
  if(btnHapus2) {
      btnHapus2.addEventListener('click', function() {
          var key = getAbnormalDraftKey();
          if (key) localStorage.removeItem(key);
      });
  }
  // --- END AUTOSAVE LOGIC ---

</script>
