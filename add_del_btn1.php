<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/kontrol/index.php';
$content = file_get_contents($file);

$adminDeleteHtml = <<<'EOD'
<?php if (session()->get('role') === 'admin' && isset($approvalData['id_approval'])): ?>
<div class="card border-danger mt-3 mb-3 shadow-sm">
  <div class="card-body d-flex justify-content-between align-items-center p-3">
    <div>
      <h6 class="mb-1 text-danger fw-bold"><i class="bi bi-trash"></i> Hapus Approval Control</h6>
      <p class="text-muted small mb-0">Hapus approval ini agar statusnya kembali ke "Belum Selesai". Data ceklis tidak akan hilang.</p>
    </div>
    <form action="<?= site_url('kontrol/delete-approval') ?>" method="post" onsubmit="return confirm('Apakah Anda yakin ingin menghapus approval ini? Status akan kembali ke Belum Selesai.');">
      <?= csrf_field() ?>
      <input type="hidden" name="lokasi" value="<?= esc($lokasi) ?>">
      <input type="hidden" name="line" value="<?= esc($line) ?>">
      <input type="hidden" name="kategori" value="<?= esc($kategori) ?>">
      <input type="hidden" name="bulan_tahun" value="<?= esc($bulan) ?>">
      <button type="submit" class="btn btn-danger px-4 py-2 fw-semibold shadow-sm">
        <i class="bi bi-trash me-2"></i> Hapus Approval
      </button>
    </form>
  </div>
</div>
<?php endif; ?>
EOD;

$content = str_replace(
    '<?php endif; ?>

<style>',
    '<?php endif; ?>

' . $adminDeleteHtml . '

<style>',
    $content
);

file_put_contents($file, $content);
echo "Added Admin delete button to kontrol/index.php.\n";
