<?php
if (!function_exists('formatDurasi')) {
    function formatDurasi($detik) {
        if ($detik === null) return '-';
        $jam = floor($detik / 3600);
        $menit = floor(($detik % 3600) / 60);
        $det = $detik % 60;
        if ($jam > 0) {
            return sprintf('%02d:%02d:%02d', $jam, $menit, $det);
        }
        return sprintf('%02d:%02d', $menit, $det);
    }
}

if (!function_exists('formatDurasiText')) {
    function formatDurasiText($detik) {
        if ($detik === null) return '-';
        $jam = floor($detik / 3600);
        $menit = floor(($detik % 3600) / 60);
        $det = $detik % 60;
        
        $parts = [];
        if ($jam > 0) $parts[] = $jam . ' jam';
        if ($menit > 0 || $jam > 0) $parts[] = $menit . ' menit';
        $parts[] = $det . ' detik';
        return implode(' ', $parts);
    }
}
?>

<?php $no = $startNo ?? 1; ?>
<?php foreach ($laporan as $l): ?>
  <tr>
    <td class="text-center fw-semibold text-muted"><?= $no++ ?></td>
    <?php 
      $rawNamaDurasi = $l['nama_pic'] ?: $l['nama_staff'];
      $namaDurasiParts = explode(' - ', $rawNamaDurasi);
      $namaDurasiOnly = end($namaDurasiParts);
    ?>
    <td><?= esc($namaDurasiOnly) ?></td>
    <td><?= esc($l['no_mesin']) ?> - <?= esc($l['type_mesin']) ?></td>
    <td><?= esc($l['lokasi_check']) ?> / <?= esc($l['line'] ?? '-') ?></td>
    <td>
      <?php if (strtolower($l['jenis_check']) === 'overhaul'): ?>
        <span class="badge bg-primary">Inspection Report</span>
      <?php else: ?>
        <span class="badge bg-info text-dark">Checklist Report</span>
      <?php endif; ?>
    </td>
    <td><?= esc(format_tanggal_indo($l['waktu_mulai'], false, true)) ?></td>
    <td><?= $l['waktu_selesai'] ? esc(format_tanggal_indo($l['waktu_selesai'], false, true)) : '-' ?></td>
    <td>
      <?php if ($l['durasi_detik'] !== null): ?>
        <?= formatDurasi((int) $l['durasi_detik']) ?>
      <?php else: ?>
        -
      <?php endif; ?>
    </td>
    <td>
      <?php 
        $qsDurasi = !empty($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : '';
      ?>
      <a href="<?= site_url('riwayat/' . $l['id_transaksi']) . '?from=durasi' . $qsDurasi ?>" class="btn btn-sm btn-outline-primary">Detail</a>
    </td>
  </tr>
<?php endforeach; ?>
<?php if(empty($laporan)): ?>
  <tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data yang sesuai dengan filter.</td></tr>
<?php endif; ?>
