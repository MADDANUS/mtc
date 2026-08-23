<?= view('layout/header', ['title' => $title]) ?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold"><i class="bi bi-journal-text me-2"></i>Log Riwayat Dokumen</h4>
            <p class="text-muted mb-0" style="font-size:0.9rem;">Riwayat aktivitas edit dan hapus dokumen laporan.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="auditTable">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">Waktu</th>
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">User</th>
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">Dokumen</th>
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">Aksi</th>
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">Mesin</th>
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">Alasan</th>
                            <th class="py-3 text-secondary text-center" style="font-weight:600; font-size:0.85rem;">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 mb-2 d-block"></i> Belum ada aktivitas yang dicatat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="text-nowrap" style="font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($log['waktu_eksekusi'])) ?></td>
                                    <td style="font-size:0.85rem;"><?= esc($log['dieksekusi_oleh']) ?></td>
                                    <td style="font-size:0.85rem;"><span class="badge bg-secondary"><?= esc($log['kategori_dokumen']) ?></span></td>
                                    <td>
                                        <?php if ($log['aksi'] === 'Edit'): ?>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-pencil-square me-1"></i>Edit</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-trash3 me-1"></i>Hapus</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-semibold text-primary" style="font-size:0.85rem;"><?= esc($log['no_mesin']) ?></td>
                                    <td style="font-size:0.85rem;" class="text-truncate" style="max-width:200px;">
                                        <?= esc($log['alasan']) ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-info rounded-3 py-1 px-2 btn-detail-log" 
                                                data-id="<?= $log['id_log'] ?>" 
                                                data-json="<?= htmlspecialchars($log['detail_perubahan'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-aksi="<?= esc($log['aksi']) ?>">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetailLog" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h6 class="modal-title fw-bold" id="modalTitle"><i class="bi bi-file-earmark-diff me-2"></i>Detail Perubahan</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pt-3 pb-4">
                <div class="bg-light p-3 rounded-3 mb-3">
                    <pre id="jsonViewer" class="mb-0" style="font-size:0.8rem; overflow-x: auto; white-space: pre-wrap;"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('auditTable');
    if (table) {
        // Initialize DataTables if needed, simplified here
    }

    document.querySelectorAll('.btn-detail-log').forEach(btn => {
        btn.addEventListener('click', function() {
            const rawJson = this.getAttribute('data-json');
            const aksi = this.getAttribute('data-aksi');
            const viewer = document.getElementById('jsonViewer');
            const modalTitle = document.getElementById('modalTitle');
            
            modalTitle.innerHTML = aksi === 'Hapus' ? '<i class="bi bi-trash3 text-danger me-2"></i>Snapshot Data Terhapus' : '<i class="bi bi-file-earmark-diff text-warning me-2"></i>Data Lama & Baru (Diff)';
            
            try {
                if(rawJson && rawJson !== 'null') {
                    const parsed = JSON.parse(rawJson);
                    viewer.textContent = JSON.stringify(parsed, null, 2);
                } else {
                    viewer.textContent = "Tidak ada detail perubahan yang dicatat.";
                }
            } catch (e) {
                viewer.textContent = rawJson || "Tidak ada detail perubahan yang dicatat.";
            }

            new bootstrap.Modal(document.getElementById('modalDetailLog')).show();
        });
    });
});
</script>

<?= view('layout/footer') ?>
