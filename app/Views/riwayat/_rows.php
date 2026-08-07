<?php if (empty($riwayat)): ?>
  <tr>
    <td colspan="9" class="text-center py-5 text-muted">
      <i class="bi bi-clipboard-x mb-2" style="font-size: 2rem; display: block;"></i>
      Belum ada data riwayat pengecekan yang sesuai dengan filter.
    </td>
  </tr>
<?php else: ?>
<?php $no = $startNo ?? 1; ?>
<?php foreach ($riwayat as $r): ?>
  <?php
    $durasi = '-';
    if (! empty($r['waktu_mulai']) && ! empty($r['waktu_selesai'])) {
        $durasi = gmdate('i:s', strtotime($r['waktu_selesai']) - strtotime($r['waktu_mulai'])) . ' m';
    }
  ?>
  <tr>
    <td class="fw-semibold text-muted text-center"><?= $no++ ?></td>
    <td class="text-muted" style="font-size: 0.85rem;"><?= esc($r['nama_pic']) ?></td>
    <td class="fw-medium text-dark" style="font-size: 0.85rem; text-center"><?= esc($r['lokasi_check'] ?? '-') ?></td>
    <td class="fw-medium text-dark" style="font-size: 0.85rem; text-center"><?= esc($r['line'] ?? '-') ?></td>
    <td>
      <div class="fw-semibold text-dark" style="font-size: 0.85rem;"><?= esc($r['no_mesin']) ?></div>
      <div class="text-muted small" style="font-size: 0.75rem;"><?= esc($r['type_mesin']) ?></div>
    </td>
    <td>
      <span class="badge bg-primary"><?= esc($r['kategori']) ?></span>
      <span class="badge bg-secondary text-capitalize"><?= esc($r['jenis_check'] === 'Preventive' ? 'Checklist Report' : $r['jenis_check']) ?></span>
    </td>
    <td style="font-size: 0.8rem; color: var(--text-secondary);">
      <?= esc(format_tanggal_indo($r['waktu_mulai'], true, true)) ?>
    </td>
    <td>
      <?php $status = $r['status'] ?? 'Pending'; ?>
      <?php if ($status === 'Approved'): ?>
        <span class="badge bg-success">Approved</span>
      <?php elseif ($status === 'Approved L1'): ?>
        <span class="badge bg-info text-dark">Approved L1</span>
      <?php elseif ($status === 'Approved L2'): ?>
        <span class="badge bg-primary">Approved L2</span>
      <?php else: ?>
        <span class="badge bg-warning text-dark">Pending</span>
      <?php endif; ?>
    </td>
    <td>
      <div class="d-flex gap-1">
        <?php 
          $qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] . '&from_lokasi=' . ($lokasiSlug ?? '') : '?from_lokasi=' . ($lokasiSlug ?? ''); 
        ?>
        <a href="<?= site_url('riwayat/' . $r['id_transaksi']) . $qs ?>" class="btn btn-sm btn-outline-primary py-1 px-2">
          Detail
        </a>
        <?php if (session()->get('role') === 'admin'): ?>
          <a href="<?= site_url('riwayat/edit/' . $r['id_transaksi']) . $qs ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit Riwayat">
            <i class="bi bi-pencil"></i>
          </a>
          <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2" onclick="confirmDelete(<?= $r['id_transaksi'] ?>)" title="Hapus Riwayat">
            <i class="bi bi-trash"></i>
          </button>
        <?php endif; ?>
      </div>
    </td>
  </tr>
<?php endforeach; ?>
<?php endif; ?>
