<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/approval/index.php';
$content = file_get_contents($file);

$kontrolDeleteBtn = <<<'EOD'
                    <?php if (session()->get('role') === 'admin' && ($doc['doc_source'] ?? '') === 'transaksi'): ?>
                      <a href="<?= site_url('riwayat/edit/' . $doc['doc_id']) ?>?from=approval" class="btn btn-sm btn-outline-warning py-1 px-2" style="font-size:0.8rem;" title="Edit">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <button type="button"
                        class="btn btn-sm btn-outline-danger py-1 px-2"
                        style="font-size:0.8rem;"
                        title="Hapus"
                        onclick="konfirmasiHapus(<?= $doc['doc_id'] ?>, '<?= esc($keterangan, 'js') ?>')">
                        <i class="bi bi-trash"></i>
                      </button>
                    <?php elseif (session()->get('role') === 'admin' && ($doc['doc_source'] ?? '') === 'kontrol'): ?>
                      <button type="button"
                        class="btn btn-sm btn-outline-danger py-1 px-2"
                        style="font-size:0.8rem;"
                        title="Hapus Approval"
                        onclick="konfirmasiHapusKontrol('<?= esc($doc['lokasi'], 'js') ?>', '<?= esc($doc['line'], 'js') ?>', '<?= esc($doc['kategori'], 'js') ?>', '<?= esc(substr($doc['doc_date'] ?? '', 0, 7), 'js') ?>', '<?= esc($keterangan, 'js') ?>')">
                        <i class="bi bi-trash"></i>
                      </button>
                    <?php endif; ?>
EOD;

$content = str_replace(
    '<?php if (session()->get(\'role\') === \'admin\' && ($doc[\'doc_source\'] ?? \'\') === \'transaksi\'): ?>
                      <a href="<?= site_url(\'riwayat/edit/\' . $doc[\'doc_id\']) ?>?from=approval" class="btn btn-sm btn-outline-warning py-1 px-2" style="font-size:0.8rem;" title="Edit">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <button type="button"
                        class="btn btn-sm btn-outline-danger py-1 px-2"
                        style="font-size:0.8rem;"
                        title="Hapus"
                        onclick="konfirmasiHapus(<?= $doc[\'doc_id\'] ?>, \'<?= esc($keterangan, \'js\') ?>\')">
                        <i class="bi bi-trash"></i>
                      </button>
                    <?php endif; ?>',
    $kontrolDeleteBtn,
    $content
);

$kontrolDeleteModal = <<<'EOD'
  <!-- Modal Konfirmasi Hapus Kontrol -->
  <div class="modal fade" id="modalHapusKontrol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Approval</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="mb-1">Anda akan menghapus persetujuan untuk:</p>
          <p class="fw-bold text-danger" id="namaHapusKontrol"></p>
          <p class="text-muted small">Data ceklis tidak akan hilang, status akan kembali ke "Belum Selesai".</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <form id="formHapusKontrol" action="<?= site_url('kontrol/delete-approval') ?>" method="POST" class="d-inline">
            <?= csrf_field() ?>
            <input type="hidden" name="lokasi" id="del_lokasi">
            <input type="hidden" name="line" id="del_line">
            <input type="hidden" name="kategori" id="del_kategori">
            <input type="hidden" name="bulan_tahun" id="del_bulan">
            <button type="submit" class="btn btn-danger btn-sm">
              <i class="bi bi-trash me-1"></i>Ya, Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
  function konfirmasiHapus(id, nama) {
    document.getElementById('namaHapus').textContent = nama;
    document.getElementById('formHapus').action = '<?= site_url('riwayat/delete/') ?>' + id;
    new bootstrap.Modal(document.getElementById('modalHapus')).show();
  }
  
  function konfirmasiHapusKontrol(lokasi, line, kategori, bulan, nama) {
    document.getElementById('namaHapusKontrol').textContent = 'Ceklis Kontrol: ' + nama;
    document.getElementById('del_lokasi').value = lokasi;
    document.getElementById('del_line').value = line;
    document.getElementById('del_kategori').value = kategori;
    document.getElementById('del_bulan').value = bulan;
    new bootstrap.Modal(document.getElementById('modalHapusKontrol')).show();
  }
  </script>
EOD;

$content = str_replace(
    '<script>
  function konfirmasiHapus(id, nama) {
    document.getElementById(\'namaHapus\').textContent = nama;
    document.getElementById(\'formHapus\').action = \'<?= site_url(\'riwayat/delete/\') ?>\' + id;
    new bootstrap.Modal(document.getElementById(\'modalHapus\')).show();
  }
  </script>',
    $kontrolDeleteModal,
    $content
);

file_put_contents($file, $content);
echo "Added Admin delete kontrol button to approval/index.php.\n";
