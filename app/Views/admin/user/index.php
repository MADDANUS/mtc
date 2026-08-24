<?= view('layout/header', ['title' => $title]) ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <h5 class="mb-0">Master User</h5>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <!-- Form Impor CSV -->
    <form action="<?= site_url('admin/user/import') ?>" method="post" enctype="multipart/form-data" class="d-flex align-items-center gap-1 border rounded p-1 bg-white shadow-sm" style="max-height: 38px;">
      <?= csrf_field() ?>
      <input type="file" name="file_csv" accept=".csv" required class="form-control form-control-sm" style="max-width: 170px; border:none; padding: 2px 4px; font-size: 0.8rem;" title="Pilih file CSV untuk diimpor">
      <button type="submit" class="btn btn-sm btn-success py-1 px-2 fw-semibold" style="font-size: 0.8rem;">Impor CSV</button>
    </form>
    <!-- Link Ekspor CSV -->
    <a href="<?= site_url('admin/user/export') ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 py-2">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
      </svg>
      Ekspor
    </a>
    <?php if (has_role('admin')): ?>
      <button type="button" id="btnBatchDeleteUser" class="btn btn-danger btn-sm py-2 d-none">
        <i class="bi bi-trash"></i> Hapus Terpilih (<span id="batchCountUser">0</span>)
      </button>
    <?php endif; ?>
    <a href="<?= site_url('admin/user/create') ?>" class="btn btn-primary btn-sm py-2">+ Tambah User</a>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success shadow-sm border-0 mb-4"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger shadow-sm border-0 mb-4"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card-stat p-3">
  <?php if (empty($daftar)): ?>
    <p class="text-muted mb-0">Belum ada data user.</p>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead>
          <tr>
            <?php if (has_role('admin')): ?>
            <th style="width: 40px;" class="text-center">
              <input type="checkbox" id="checkAllUser" class="form-check-input">
            </th>
            <?php endif; ?>
            <th>Nama</th>
            <th>Username</th>
            <th>Role</th>
            <th>Plant</th>
            <th>Departemen</th>
            <th>Line</th>
            <th>Status</th>
            <th style="width:220px;">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($daftar as $u): ?>
            <tr>
              <?php if (has_role('admin')): ?>
              <td class="text-center">
                <?php if ((int) $u['id'] !== (int) session()->get('user_id')): ?>
                  <input type="checkbox" class="form-check-input check-item-user" value="<?= $u['id'] ?>">
                <?php else: ?>
                  <input type="checkbox" class="form-check-input" disabled title="Tidak bisa menghapus akun yang sedang digunakan">
                <?php endif; ?>
              </td>
              <?php endif; ?>
              <td><?= esc($u['nama']) ?></td>
              <td><?= esc($u['username']) ?></td>
              <td><span class="badge bg-secondary text-uppercase"><?= esc($u['role']) ?></span></td>
              <td>
                <?php if (($u['plant'] ?? '-') === '-'): ?>
                  -
                <?php else: ?>
                  <span class="badge bg-primary"><?= esc($u['plant'] ?? 'Plant 1') ?></span>
                <?php endif; ?>
              </td>
              <td><?= esc($u['departemen'] ?? '-') ?></td>
              <td><?= esc($u['line'] ?? '-') ?></td>
              <td>
                <?php $isActive = (isset($u['is_active']) && (int)$u['is_active'] === 1); ?>
                <?php if ($isActive): ?>
                  <span class="badge bg-success">Active</span>
                <?php else: ?>
                  <span class="badge bg-danger">Inactive</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="d-flex gap-1">
                  <a href="<?= site_url('admin/user/toggle-active/' . $u['id']) ?>" class="btn btn-sm <?= $isActive ? 'btn-outline-warning border-warning' : 'btn-outline-success border-success' ?>" title="<?= $isActive ? 'Nonaktifkan User' : 'Aktifkan User' ?>" onclick="return confirm('Anda yakin ingin <?= $isActive ? 'menonaktifkan' : 'mengaktifkan' ?> user ini?');" style="border-width: 1px; border-style: solid;">
                    <?= $isActive ? 'Inactive' : 'Active' ?>
                  </a>
                  <a href="<?= site_url('admin/user/edit/' . $u['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                  <?php if ((int) $u['id'] !== (int) session()->get('user_id') && has_role('admin')): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger" 
                            onclick="openDeleteModal(<?= $u['id'] ?>, '<?= esc($u['nama'], 'js') ?>')" title="Hapus User">
                      Hapus
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Modal Konfirmasi Hapus User -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="" method="post" id="deleteForm">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Anda yakin ingin menghapus user <strong id="deleteUserLabel"></strong> secara permanen?</p>
          <div class="mb-3">
            <label for="deleteReason" class="form-label">Keterangan / Alasan Dihapus <span class="text-danger">*</span></label>
            <textarea class="form-control" name="alasan" id="deleteReason" rows="3" required placeholder="Tuliskan alasan mengapa user ini dihapus..."></textarea>
          </div>
          <div class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-triangle"></i> Data user akan dipindahkan ke Log Riwayat Terhapus dan dihapus dari master secara fisik.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Ya, Hapus Permanen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function openDeleteModal(id, namaUser) {
      document.getElementById('deleteUserLabel').innerText = namaUser;
      document.getElementById('deleteForm').action = '<?= site_url('admin/user/delete/') ?>' + id;
      document.getElementById('deleteReason').value = '';
      new bootstrap.Modal(document.getElementById('deleteModal')).show();
  }

  document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAllUser');
    const checkItems = document.querySelectorAll('.check-item-user');
    const btnBatchDelete = document.getElementById('btnBatchDeleteUser');
    const batchCount = document.getElementById('batchCountUser');

    function updateBatchDeleteUI() {
      const checkedCount = document.querySelectorAll('.check-item-user:checked').length;
      if (checkedCount > 0) {
        btnBatchDelete.classList.remove('d-none');
        batchCount.innerText = checkedCount;
      } else {
        btnBatchDelete.classList.add('d-none');
      }
      
      if (checkAll) {
         // Hanya hitung checkable item (bukan yang disabled)
         const checkableItems = document.querySelectorAll('.check-item-user:not(:disabled)');
         checkAll.checked = (checkedCount === checkableItems.length && checkableItems.length > 0);
      }
    }

    if (checkAll) {
      checkAll.addEventListener('change', function() {
        checkItems.forEach(item => {
          if (!item.disabled) {
            item.checked = this.checked;
          }
        });
        updateBatchDeleteUI();
      });
    }

    checkItems.forEach(item => {
      item.addEventListener('change', updateBatchDeleteUI);
    });

    if (btnBatchDelete) {
      btnBatchDelete.addEventListener('click', function() {
        const checkedItems = document.querySelectorAll('.check-item-user:checked');
        const ids = Array.from(checkedItems).map(item => item.value);

        if (ids.length === 0) return;

        Swal.fire({
          title: 'Hapus ' + ids.length + ' User?',
          text: 'Masukkan keterangan/alasan penghapusan massal ini:',
          input: 'textarea',
          inputPlaceholder: 'Tuliskan alasan...',
          inputAttributes: {
            'aria-label': 'Tuliskan alasan'
          },
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          confirmButtonText: 'Ya, Hapus Permanen',
          cancelButtonText: 'Batal',
          preConfirm: (alasan) => {
            if (!alasan) {
              Swal.showValidationMessage('Alasan penghapusan harus diisi!');
            }
            return alasan;
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire({
              title: 'Menghapus...',
              text: 'Mohon tunggu',
              allowOutsideClick: false,
              didOpen: () => {
                Swal.showLoading();
              }
            });

            fetch('<?= site_url('admin/user/delete-batch') ?>', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
              },
              body: JSON.stringify({ ids: ids, alasan: result.value })
            })
            .then(response => response.json())
            .then(data => {
              if (data.status) {
                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                  window.location.reload();
                });
              } else {
                Swal.fire('Gagal!', data.message || 'Terjadi kesalahan', 'error');
              }
            })
            .catch(error => {
              console.error('Error:', error);
              Swal.fire('Gagal!', 'Terjadi kesalahan sistem', 'error');
            });
          }
        });
      });
    }
  });
</script>

<?= view('layout/footer') ?>
