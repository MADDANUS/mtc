<?php if (empty($reports)): ?>
  <tr>
    <td colspan="12" class="p-5 text-muted">
      <i class="bi bi-shield-check text-success" style="font-size: 2.5rem; display:block; margin-bottom:0.5rem;"></i>
      Tidak ada temuan kondisi abnormal yang tercatat.
    </td>
  </tr>
<?php else: ?>
  <?php $no = $startNo ?? 1; foreach ($reports as $r): ?>
    <?php 
      $role = session()->get('role');
      $isFilled = !empty($r['type_sparepart']) || !empty($r['progres_stock']) || !empty($r['progres_tanggal']) || !empty($r['action']) || !empty($r['repair_pic']) || !empty($r['keterangan']) || !empty($r['foto_perbaikan']) || !empty($r['foto_perbaikan_2']);
      
      $canEdit = false;
      if (in_array($role, ['member', 'sheadprd', 'sheadmtc', 'admin', 'magang'], true)) {
          if ($isFilled) {
              $canEdit = ($role === 'admin');
          } else {
              $canEdit = true;
          }
      }
      
      $rowClass = $canEdit ? 'row-editable' : '';
    ?>
    <tr class="<?= $rowClass ?>" 
        style="<?= $canEdit ? 'cursor: pointer;' : '' ?> transition: background-color 0.15s;"
        data-id-abnormal="<?= $r['id_abnormal'] ?>"
        data-mesin="<?= esc($r['no_mesin'] . ' - ' . $r['type_mesin'] . ' (' . $r['lokasi'] . ')') ?>"
        <?php 
          $pointCheckDisplay = esc($r['point_check']);
          if (!empty($r['bagian_check'])) {
              $parts = [esc($r['bagian_check'])];
              if (!empty($r['sub_item_check'])) {
                  $parts[] = esc($r['sub_item_check']);
              }
              $parts[] = esc($r['point_check']);
              $pointCheckDisplay = implode(' - ', $parts);
          }
        ?>
        data-point-check="<?= $pointCheckDisplay ?>"
        data-abnormal-condition="<?= esc($r['abnormal_condition']) ?>"
        data-type-sparepart="<?= esc($r['type_sparepart'] ?? '') ?>"
        data-progres-stock="<?= esc($r['progres_stock'] ?? '') ?>"
        data-progres-tanggal="<?= esc($r['progres_tanggal'] ?? '') ?>"
        data-action="<?= esc($r['action'] ?? '') ?>"
        data-repair-pic="<?= esc($r['repair_pic'] ?? '') ?>"
        data-keterangan="<?= esc($r['keterangan'] ?? '') ?>"
        data-foto-abnormal="<?= !empty($r['foto_abnormal']) ? base_url('uploads/abnormal/' . $r['foto_abnormal']) : '' ?>"
        data-foto-perbaikan="<?= !empty($r['foto_perbaikan']) ? base_url('uploads/abnormal/' . $r['foto_perbaikan']) : '' ?>"
        data-foto-perbaikan-2="<?= !empty($r['foto_perbaikan_2']) ? base_url('uploads/abnormal/' . $r['foto_perbaikan_2']) : '' ?>">
      
      <td class="fw-bold font-monospace text-secondary" style="background-color: #f8fafc;"><?= $no++ ?></td>
      <td class="text-start fw-bold text-dark ps-3"><?= esc($r['no_mesin']) ?> - <?= esc($r['type_mesin']) ?></td>
      <td><?= $pointCheckDisplay ?></td>
      <td class="text-danger fw-semibold">
        <?= esc($r['abnormal_condition']) ?>
        <div class="d-flex justify-content-center align-items-center gap-1 mt-1">
          <?php if (!empty($r['foto_abnormal'])): ?>
            <a href="<?= base_url('uploads/abnormal/' . $r['foto_abnormal']) ?>" target="_blank" title="Lihat Foto Abnormal 1" onclick="event.stopPropagation()">
              <img src="<?= base_url('uploads/abnormal/' . $r['foto_abnormal']) ?>" alt="Foto Abnormal 1" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #dee2e6;">
            </a>
          <?php endif; ?>
          <?php if (!empty($r['foto_abnormal_2'])): ?>
            <a href="<?= base_url('uploads/abnormal/' . $r['foto_abnormal_2']) ?>" target="_blank" title="Lihat Foto Abnormal 2" onclick="event.stopPropagation()">
              <img src="<?= base_url('uploads/abnormal/' . $r['foto_abnormal_2']) ?>" alt="Foto Abnormal 2" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #dee2e6;">
            </a>
          <?php endif; ?>
        </div>
      </td>
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
      <td class="text-start" onclick="event.stopPropagation()">
        <?= esc($r['action']) ?: '<span class="text-muted">-</span>' ?>
        <div class="d-flex justify-content-start align-items-center gap-1 mt-1">
          <!-- Slot 1 -->
          <?php if (!empty($r['foto_perbaikan'])): ?>
            <a href="<?= base_url('uploads/abnormal/' . $r['foto_perbaikan']) ?>" target="_blank" title="Lihat Foto Perbaikan 1">
              <img src="<?= base_url('uploads/abnormal/' . $r['foto_perbaikan']) ?>" alt="Foto Perbaikan 1" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #dee2e6;">
            </a>
          <?php endif; ?>

          <!-- Slot 2 -->
          <?php if (!empty($r['foto_perbaikan_2'])): ?>
            <a href="<?= base_url('uploads/abnormal/' . $r['foto_perbaikan_2']) ?>" target="_blank" title="Lihat Foto Perbaikan 2">
              <img src="<?= base_url('uploads/abnormal/' . $r['foto_perbaikan_2']) ?>" alt="Foto Perbaikan 2" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #dee2e6;">
            </a>
          <?php endif; ?>
        </div>
      </td>
      <td><span class="fw-semibold text-dark"><?= esc($r['repair_pic']) ?: '<span class="text-muted">-</span>' ?></span></td>
      
      <td><?= esc($r['keterangan']) ?: '<span class="text-muted">-</span>' ?></td>
      
    </tr>
  <?php endforeach; ?>
<?php endif; ?>
