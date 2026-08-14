<!-- =====================================================================
     SIGNATURE BLOCK
     ===================================================================== -->
<div style="margin-top:20px;">
  <?php
    $isOv        = strtolower(str_replace(' ', '-', $header['jenis_check'])) === 'overhaul';
    $rawNamaPic  = $header['nama_pic'] ?: ($header['nama_staff'] ?? 'MEMBER');
    $namaParts   = explode(' - ', $rawNamaPic);
    $namaP       = end($namaParts);
  ?>

  <?php if ($isOv): ?>
  <!-- SIGNATURE: INSPECTION REPORT (4 kolom) -->
  <?php
    $rawNamaOv   = $header['nama_pic'] ?: ($header['nama_staff'] ?? 'MEMBER');
    $namaOvParts = explode(' - ', $rawNamaOv);
    $namaOvOnly  = end($namaOvParts);
  ?>
  <table style="border:none; text-align:center; margin-top:20px;">
    <tr>
      <td style="border:none; width:25%; vertical-align:top;">
        <div style="margin-bottom:5px; font-size:0.85rem;">Prepared</div>
        <div style="font-weight:bold; font-size:0.9rem; margin-bottom:20px;">INSPECTOR</div>
        <?php if (!empty($header['waktu_selesai'])): ?>
          <div style="color:green; font-weight:bold; margin-bottom:20px;">[ Selesai ]</div>
        <?php else: ?>
          <div style="height:20px; margin-bottom:20px;"></div>
        <?php endif; ?>
        <div style="font-weight:bold; text-decoration:underline; font-size:0.9rem;"><?= esc($namaOvOnly) ?></div>
        <div style="font-size:0.8rem; color:#555;">Tgl: <?= !empty($header['waktu_selesai']) ? format_tanggal_indo($header['waktu_selesai'], false, true) : '-' ?></div>
      </td>
      <td style="border:none; width:25%; vertical-align:top;">
        <div style="margin-bottom:5px; font-size:0.85rem;">Checked</div>
        <div style="font-weight:bold; font-size:0.9rem; margin-bottom:20px;">LEADER PRODUKSI</div>
        <?php if (!empty($header['approval_l1_by'])): ?>
          <div style="color:green; font-weight:bold; margin-bottom:20px;">[ Diperiksa ]</div>
        <?php else: ?>
          <div style="height:20px; margin-bottom:20px;"></div>
        <?php endif; ?>
        <div style="font-weight:bold; font-size:0.9rem;">
          <?php if (!empty($header['approval_l1_by'])): ?>
            <span style="text-decoration:underline;"><?= esc($header['approver_l1_nama']) ?></span>
          <?php else: ?>
            <span style="color:#999;">( .................................. )</span>
          <?php endif; ?>
        </div>
        <div style="font-size:0.8rem; color:#555;">Tgl: <?= !empty($header['approval_l1_at']) ? format_tanggal_indo($header['approval_l1_at'], false, true) : '( ..................... )' ?></div>
      </td>
      <td style="border:none; width:25%; vertical-align:top;">
        <div style="margin-bottom:5px; font-size:0.85rem;">Approved</div>
        <div style="font-weight:bold; font-size:0.9rem; margin-bottom:20px;">SECTION HEAD PRODUKSI</div>
        <?php if (!empty($header['approval_l2_by'])): ?>
          <div style="color:green; font-weight:bold; margin-bottom:20px;">[ Disetujui ]</div>
        <?php else: ?>
          <div style="height:20px; margin-bottom:20px;"></div>
        <?php endif; ?>
        <div style="font-weight:bold; font-size:0.9rem;">
          <?php if (!empty($header['approval_l2_by'])): ?>
            <span style="text-decoration:underline;">Mr. Rohmad</span>
          <?php else: ?>
            <span style="color:#999;">( Mr. Rohmad )</span>
          <?php endif; ?>
        </div>
        <div style="font-size:0.8rem; color:#555;">Tgl: <?= !empty($header['approval_l2_at']) ? format_tanggal_indo($header['approval_l2_at'], false, true) : '( ..................... )' ?></div>
      </td>
      <td style="border:none; width:25%; vertical-align:top;">
        <div style="margin-bottom:5px; font-size:0.85rem;">Approved</div>
        <div style="font-weight:bold; font-size:0.9rem; margin-bottom:20px;">SECTION HEAD MTC</div>
        <?php if ($header['status'] === 'Approved'): ?>
          <div style="color:green; font-weight:bold; margin-bottom:20px;">[ Disetujui ]</div>
        <?php else: ?>
          <div style="height:20px; margin-bottom:20px;"></div>
        <?php endif; ?>
        <div style="font-weight:bold; font-size:0.9rem;">
          <?php if ($header['status'] === 'Approved'): ?>
            <span style="text-decoration:underline;">Mr. Royadi</span>
          <?php else: ?>
            <span style="color:#999;">( Mr. Royadi )</span>
          <?php endif; ?>
        </div>
        <div style="font-size:0.8rem; color:#555;">Tgl: <?= ($header['status'] === 'Approved') ? format_tanggal_indo($header['approved_at'], false, true) : '( ..................... )' ?></div>
      </td>
    </tr>
  </table>

  <?php else: ?>
  <!-- SIGNATURE: CHECKLIST REPORT (2 kolom) -->
  <table style="border:none; text-align:center; margin-top:20px;">
    <tr>
      <td style="border:none; width:50%; vertical-align:top;">
        <div style="margin-bottom:5px; font-size:0.85rem;">Dibuat Oleh</div>
        <div style="font-weight:bold; font-size:0.9rem; margin-bottom:20px;">PIC</div>
        <?php if (!empty($header['waktu_selesai'])): ?>
          <div style="color:green; font-weight:bold; margin-bottom:20px;">[ Selesai ]</div>
        <?php else: ?>
          <div style="height:20px; margin-bottom:20px;"></div>
        <?php endif; ?>
        <div style="font-weight:bold; text-decoration:underline; font-size:0.9rem;"><?= esc($namaP) ?></div>
        <div style="font-size:0.8rem; color:#555;">Tgl: <?= !empty($header['waktu_selesai']) ? format_tanggal_indo($header['waktu_selesai'], false, true) : '-' ?></div>
      </td>
      <td style="border:none; width:50%; vertical-align:top;">
        <div style="margin-bottom:5px; font-size:0.85rem;">Disetujui Oleh</div>
        <div style="font-weight:bold; font-size:0.9rem; margin-bottom:20px;">PIC LINE</div>
        <?php if ($header['status'] === 'Approved'): ?>
          <div style="color:green; font-weight:bold; margin-bottom:20px;">[ Disetujui ]</div>
        <?php else: ?>
          <div style="height:20px; margin-bottom:20px;"></div>
        <?php endif; ?>
        <div style="font-weight:bold; font-size:0.9rem;">
          <?php if ($header['status'] === 'Approved'): ?>
            <span style="text-decoration:underline;"><?= esc($header['approver_nama']) ?></span>
          <?php else: ?>
            <span style="color:#999;">( ........................................ )</span>
          <?php endif; ?>
        </div>
        <div style="font-size:0.8rem; color:#555;">Tgl: <?= ($header['status'] === 'Approved') ? format_tanggal_indo($header['approved_at'], false, true) : '( ..................... )' ?></div>
      </td>
    </tr>
  </table>
  <?php endif; ?>

</div>

</div>
