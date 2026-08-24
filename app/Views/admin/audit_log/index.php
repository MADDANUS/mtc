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
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">Kategori</th>
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">ID Dokumen</th>
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">Aksi</th>
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">Mesin</th>
                            <th class="py-3 text-secondary" style="font-weight:600; font-size:0.85rem;">Alasan</th>
                            <th class="py-3 text-secondary text-center" style="font-weight:600; font-size:0.85rem;">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 mb-2 d-block"></i> Belum ada aktivitas yang dicatat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): 
                                $idDokumen = '-';
                                $parsed = json_decode($log['detail_perubahan'], true);
                                if ($parsed) {
                                    if (isset($parsed['new_data']['header']['id_transaksi'])) {
                                        $idDokumen = $parsed['new_data']['header']['id_transaksi'];
                                    } elseif (isset($parsed['old_data']['header']['id_transaksi'])) {
                                        $idDokumen = $parsed['old_data']['header']['id_transaksi'];
                                    } elseif (isset($parsed['header']['id_transaksi'])) {
                                        $idDokumen = $parsed['header']['id_transaksi'];
                                    } elseif (isset($parsed['id_transaksi'])) {
                                        $idDokumen = $parsed['id_transaksi'];
                                    }
                                }
                            ?>
                                <tr>
                                    <td class="text-nowrap" style="font-size:0.85rem;"><?= date('d/m/Y H:i', strtotime($log['waktu_eksekusi'])) ?></td>
                                    <td style="font-size:0.85rem;"><?= esc($log['dieksekusi_oleh']) ?></td>
                                    <td style="font-size:0.85rem;"><span class="badge bg-secondary"><?= esc($log['kategori_dokumen']) ?></span></td>
                                    <td class="fw-bold text-dark" style="font-size:0.85rem;"><?= esc($idDokumen) ?></td>
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
            
            <form id="filterForm" action="<?= site_url('admin/audit-log') ?>" method="get"></form>
            
            <!-- Pagination -->
            <?php if (isset($totalItems) && $totalItems > 0): ?>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top mt-2 flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <span class="text-muted small me-2">Tampilkan:</span>
                    <select name="per_page" form="filterForm" class="form-select form-select-sm text-center" style="width:60px;" onchange="this.form.submit()">
                        <option value="15" <?= $perPage == 15 ? 'selected' : '' ?>>15</option>
                        <option value="30" <?= $perPage == 30 ? 'selected' : '' ?>>30</option>
                        <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                    </select>
                    <span class="text-muted small ms-2">baris</span>
                </div>
                
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">
                        Menampilkan <?= (($currentPage-1)*$perPage)+1 ?> - <?= min($currentPage*$perPage, $totalItems) ?> dari <?= $totalItems ?> data
                    </span>
                    <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link rounded-2" <?= $currentPage <= 1 ? 'tabindex="-1" aria-disabled="true"' : 'href="'.site_url('admin/audit-log') . $buildQuery(['page' => $currentPage - 1]).'"' ?>>
                            <i class="bi bi-chevron-left" style="font-size:0.7rem;"></i>
                        </a>
                        </li>
                        <?php 
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min(max(1, $totalPages), $currentPage + 2);
                        for ($p = $startPage; $p <= $endPage; $p++): 
                        ?>
                        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
                            <a class="page-link rounded-2" href="<?= site_url('admin/audit-log') . $buildQuery(['page' => $p]) ?>"><?= $p ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $currentPage >= max(1, $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link rounded-2" <?= $currentPage >= max(1, $totalPages) ? 'tabindex="-1" aria-disabled="true"' : 'href="'.site_url('admin/audit-log') . $buildQuery(['page' => $currentPage + 1]).'"' ?>>
                            <i class="bi bi-chevron-right" style="font-size:0.7rem;"></i>
                        </a>
                        </li>
                    </ul>
                    </nav>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetailLog" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 98%;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h6 class="modal-title fw-bold" id="modalTitle"><i class="bi bi-file-earmark-diff me-2"></i>Detail Perubahan</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pt-3 pb-4">
                <div class="bg-light p-3 rounded-3 mb-3">
                    <div id="jsonViewer" class="mb-0 w-100"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const paramMap = <?= json_encode($paramMap ?? []) ?>;
    const mesinMap = <?= json_encode($mesinMap ?? []) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('auditTable');
    if (table) {
        // Initialize DataTables if needed
    }

    const excludeKeys = [
        'id_transaksi', 'id_user', 'id_mesin', 'id_detail', 'id_parameter', 
        'approval_l1_by', 'approval_l2_by', 'approved_by', 
        'created_at', 'updated_at', 'approval_l1_at', 'approval_l2_at', 'approved_at',
        'nama_staff', 'nama_pic', 'ss_approval_l1_name', 'ss_approval_l2_name', 'ss_approved_name'
    ];

    function renderDiff(oldData, newData) {
        let html = '<ul class="mb-0 ps-3" style="font-size:0.85rem;">';
        let hasChanges = false;
        
        // Diff Header
        if (oldData.header && newData.header) {
            for (const key in oldData.header) {
                if (excludeKeys.includes(key)) continue;
                const oldVal = oldData.header[key] ?? '-';
                const newVal = newData.header[key] ?? '-';
                
                if (String(oldVal).trim() !== String(newVal).trim()) {
                    hasChanges = true;
                    const label = key.replace(/_/g, ' ').toUpperCase();
                    html += `<li><strong>${label}:</strong> <span class="text-muted text-decoration-line-through">${oldVal}</span> <i class="bi bi-arrow-right"></i> <span class="text-success fw-bold">${newVal}</span></li>`;
                }
            }
        }

        // Diff Details
        if (oldData.details && newData.details) {
            for (let i = 0; i < oldData.details.length; i++) {
                const oldRow = oldData.details[i];
                const newRow = newData.details.find(r => r.id_parameter === oldRow.id_parameter) || newData.details[i];
                
                if (newRow) {
                    for (const key in oldRow) {
                        if (excludeKeys.includes(key) || key === 'nama_parameter') continue;
                        const oldVal = oldRow[key] ?? '-';
                        const newVal = newRow[key] ?? '-';
                        if (String(oldVal).trim() !== String(newVal).trim()) {
                            hasChanges = true;
                            let pMap = paramMap[oldRow.id_parameter];
                            let paramName = pMap ? (pMap.point_check || pMap.bagian_check) : (oldRow.nama_parameter || `Parameter ${i+1}`);
                            const label = key.replace(/_/g, ' ').toUpperCase();
                            html += `<li><strong>[${paramName}] ${label}:</strong> <span class="text-muted text-decoration-line-through">${oldVal}</span> <i class="bi bi-arrow-right"></i> <span class="text-success fw-bold">${newVal}</span></li>`;
                        }
                    }
                }
            }
        }
        
        html += '</ul>';
        if (!hasChanges) {
            html = '<div class="text-muted" style="font-size:0.85rem;"><i class="bi bi-info-circle me-1"></i> Perubahan hanya terjadi pada sistem atau status.</div>';
        }
        return html;
    }

    function renderSnapshot(data) {
        if (!data.header) return "<div class='text-muted'>Format data usang.</div>";
        
        const header = data.header;
        const details = data.details || [];
        const mesin = mesinMap[header.id_mesin] || {};
        
        let jenis = header.jenis_check || 'CHECKLIST REPORT';
        let dept = header.departemen_check || mesin.departemen || header.departemen || '';
        const judul = `${jenis.toUpperCase()} - ${(header.kategori || '').toUpperCase()} (${dept.toUpperCase()})`;
        
        const formatTgl = (dt) => {
            if(!dt) return '-';
            const [d, t] = dt.split(' ');
            if(!d) return '-';
            const parts = d.split('-');
            if(parts.length===3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
            return d;
        };
        const formatJam = (dt) => dt ? (dt.split(' ')[1] || '-') : '-';
        
        let html = `
        <div class="border border-dark mb-3 w-100">
            <div class="text-center fw-bold py-2 border-bottom border-dark" style="background-color: #92b0e8; color: #000; font-size:1rem; letter-spacing:1px;">
                ${judul}
            </div>
            <table class="table table-bordered border-dark mb-0 table-sm" style="font-size:0.75rem; vertical-align:middle; --bs-table-bg: transparent; table-layout: fixed; word-wrap: break-word;">
                <tbody>
                    <tr>
                        <td class="fw-bold w-15 px-2" style="background-color: #f8f9fa;">DATE</td>
                        <td class="w-25 px-2">${formatTgl(header.waktu_selesai || header.created_at)}</td>
                        <td class="fw-bold w-20 px-2" style="background-color: #f8f9fa;">MACHINE TYPE</td>
                        <td class="w-20 px-2">${mesin.type_mesin || header.type_mesin || '-'}</td>
                        <td class="fw-bold w-10 px-2" style="background-color: #f8f9fa;">START TIME</td>
                        <td class="w-10 px-2">${formatJam(header.waktu_mulai)}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold px-2" style="background-color: #f8f9fa;">NO MACHINE</td>
                        <td class="px-2">${mesin.no_mesin || header.ss_no_mesin || header.no_mesin || '-'}</td>
                        <td class="fw-bold px-2" style="background-color: #f8f9fa;">SERIAL NUMBER</td>
                        <td class="px-2">${mesin.serial_number || '-'}</td>
                        <td class="fw-bold px-2" style="background-color: #f8f9fa;">FINISH TIME</td>
                        <td class="px-2">${formatJam(header.waktu_selesai)}</td>
                    </tr>
                </tbody>
            </table>
        </div>`;
        
        if (details.length > 0) {
            html += `
            <div class="border border-dark w-100">
                <table class="table table-bordered border-dark mb-0 table-sm text-center" style="font-size:0.75rem; vertical-align:middle; --bs-table-bg: transparent; table-layout: fixed; word-wrap: break-word;">
                    <thead style="background-color:#0b192c; color:#fff;">
                        <tr>
                            <th class="py-2" style="width:20%;">BAGIAN CHECK</th>
                            <th class="py-2 text-start" style="width:20%;">POINT CHECK</th>
                            <th class="py-2 text-start" style="width:20%;">STANDARD CHECK</th>
                            <th class="py-2" style="width:8%;">HASIL</th>
                            <th class="py-2 text-start" style="width:17%;">ULASAN</th>
                            <th class="py-2 text-center" style="width:15%;">FOTO</th>
                        </tr>
                    </thead>
                    <tbody>`;
                    
            let bagianRowspans = {};
            details.forEach(row => {
                const p = paramMap[row.id_parameter] || {};
                const bagian = p.bagian_check || row.bagian_check || '-';
                bagianRowspans[bagian] = (bagianRowspans[bagian] || 0) + 1;
            });
            
            let lastBagian = '';
            let currentBagianCount = 0;
            
            details.forEach(row => {
                const p = paramMap[row.id_parameter] || {};
                const bagian = p.bagian_check || row.bagian_check || '-';
                const point = p.point_check || row.nama_parameter || '-';
                const standard = p.standard_check || '-';
                
                let hasilColor = 'text-dark';
                let hasilIcon = row.hasil_check;
                if(hasilIcon === 'V') hasilColor = 'text-success fw-bold';
                else if(hasilIcon === 'O') hasilColor = 'text-primary fw-bold';
                else if(hasilIcon === 'X') hasilColor = 'text-danger fw-bold';
                else if(hasilIcon === 'Δ' || hasilIcon === '\u0394') hasilColor = 'text-warning fw-bold';
                
                let fotoHtml = '';
                const fallbackImg = `this.onerror=null; this.src=''; this.alt='Foto Musnah'; this.className='text-danger small fw-bold'; this.parentElement.removeAttribute('href');`;
                
                if(row.foto_abnormal) {
                    fotoHtml += `<a href="<?= base_url('uploads/abnormal/') ?>${row.foto_abnormal}" target="_blank"><img src="<?= base_url('uploads/abnormal/') ?>${row.foto_abnormal}" class="img-thumbnail me-1" style="max-height:40px; cursor:pointer;" title="Lihat Foto" onerror="${fallbackImg}"></a>`;
                }
                if(row.foto_abnormal_2) {
                    fotoHtml += `<a href="<?= base_url('uploads/abnormal/') ?>${row.foto_abnormal_2}" target="_blank"><img src="<?= base_url('uploads/abnormal/') ?>${row.foto_abnormal_2}" class="img-thumbnail" style="max-height:40px; cursor:pointer;" title="Lihat Foto 2" onerror="${fallbackImg}"></a>`;
                }
                if(!fotoHtml) fotoHtml = '-';
                
                html += `<tr>`;
                if (bagian !== lastBagian || currentBagianCount >= bagianRowspans[lastBagian]) {
                    html += `<td rowspan="${bagianRowspans[bagian]}" class="fw-bold align-middle" style="background-color: #f8f9fa;">${bagian}</td>`;
                    lastBagian = bagian;
                    currentBagianCount = 0;
                }
                currentBagianCount++;
                
                html += `<td class="text-start px-2">${point}</td>`;
                html += `<td class="text-start px-2">${standard}</td>`;
                html += `<td class="${hasilColor} fs-6">${row.hasil_check || '-'}</td>`;
                html += `<td class="text-start px-2">${row.ulasan || ''}</td>`;
                html += `<td class="text-center px-1">${fotoHtml}</td>`;
                html += `</tr>`;
            });
            
            html += `</tbody></table></div>`;
        }
        
        return html;
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
                    viewer.innerHTML = '';
                    
                    if (parsed.old_data && parsed.new_data) {
                        viewer.innerHTML = renderDiff(parsed.old_data, parsed.new_data);
                        if (aksi === 'Edit' && parsed.new_data.header && parsed.new_data.header.id_transaksi) {
                            const idTrx = parsed.new_data.header.id_transaksi;
                            viewer.innerHTML += `<div class="mt-3"><a href="<?= site_url('riwayat/') ?>${idTrx}" class="btn btn-sm btn-primary px-3 rounded-pill" target="_blank"><i class="bi bi-box-arrow-up-right me-2"></i>Lihat Dokumen Terkait</a></div>`;
                        }
                    } else if (parsed.old_data) {
                        viewer.innerHTML = renderSnapshot(parsed.old_data);
                    } else {
                        viewer.innerHTML = renderSnapshot(parsed);
                    }
                } else {
                    viewer.innerHTML = "<div class='text-muted'>Tidak ada detail perubahan yang dicatat.</div>";
                }
            } catch (e) {
                viewer.innerHTML = `<pre class="mb-0" style="font-size:0.8rem; overflow-x: auto; white-space: pre-wrap;">${rawJson || "Tidak ada detail perubahan."}</pre>`;
            }

            new bootstrap.Modal(document.getElementById('modalDetailLog')).show();
        });
    });
});
</script>

<?= view('layout/footer') ?>
