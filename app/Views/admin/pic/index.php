<?= view('layout/header', ['title' => $title ?? 'Master PIC']) ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold" style="color: var(--accent-hover);">Daftar Master PIC</h4>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <!-- Form Impor Excel -->
        <form action="<?= site_url('admin/pic/import') ?>" method="post" enctype="multipart/form-data" class="d-flex align-items-center gap-1 border rounded p-1 bg-white shadow-sm" style="max-height: 38px;">
            <?= csrf_field() ?>
            <input type="file" name="file_excel" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required class="form-control form-control-sm" style="max-width: 170px; border:none; padding: 2px 4px; font-size: 0.8rem;" title="Pilih file Excel untuk diimpor">
            <button type="submit" class="btn btn-sm btn-success py-1 px-2 fw-semibold" style="font-size: 0.8rem;">Impor</button>
        </form>
        <!-- Link Template -->
        <a href="<?= site_url('admin/pic/template') ?>" class="btn btn-outline-secondary btn-sm py-2">
            Unduh Template
        </a>
        <!-- Link Ekspor Excel -->
        <a href="<?= site_url('admin/pic/export') ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1 py-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
            </svg>
            Ekspor
        </a>
        <a href="<?= site_url('admin/pic/create') ?>" class="btn btn-primary d-inline-flex align-items-center gap-2 py-2" style="background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%); border: none;">
            <i class="bi bi-plus-lg"></i> Tambah PIC
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <thead style="background-color: var(--accent-hover);">
                    <tr>
                        <th class="ps-4 text-white text-uppercase" style="width: 15%; font-size: 0.82rem; letter-spacing: 0.08em; padding: 1rem;">ID PIC</th>
                        <th class="text-white text-uppercase" style="width: 40%; font-size: 0.82rem; letter-spacing: 0.08em; padding: 1rem;">Nama Lengkap PIC</th>
                        <th class="text-white text-uppercase" style="width: 20%; font-size: 0.82rem; letter-spacing: 0.08em; padding: 1rem;">Role</th>
                        <th class="pe-4 text-center text-white text-uppercase" style="width: 25%; font-size: 0.82rem; letter-spacing: 0.08em; padding: 1rem;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pics)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">Belum ada data PIC.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pics as $p): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark" style="font-size: 0.95rem;"><?= esc($p['id_pic']) ?></td>
                                <td class="fw-medium text-secondary" style="font-size: 0.9rem;"><?= esc($p['nama_pic']) ?></td>
                                <td class="fw-medium text-secondary" style="font-size: 0.9rem;">
                                    <?php 
                                        $rawRole = $p['role_pic'] ?? 'Staff';
                                        
                                        // Normalisasi agar data lama dari Excel bisa terbaca hijau
                                        $rolePicLower = strtolower(str_replace(' ', '', $rawRole));
                                        $rolePic = $rawRole;
                                        if (strpos($rolePicLower, 'leader') !== false) {
                                            if (strpos($rolePicLower, 'line1') !== false || $rolePicLower === 'leader1') $rolePic = 'leader1';
                                            elseif (strpos($rolePicLower, 'line2') !== false || $rolePicLower === 'leader2') $rolePic = 'leader2';
                                            elseif (strpos($rolePicLower, 'line3') !== false || $rolePicLower === 'leader3') $rolePic = 'leader3';
                                            elseif (strpos($rolePicLower, 'cg') !== false) $rolePic = 'leadercg';
                                            elseif (strpos($rolePicLower, 'sc') !== false || strpos($rolePicLower, 'second') !== false) $rolePic = 'leadersc';
                                        } elseif ($rolePicLower === 'magang') {
                                            $rolePic = 'Magang';
                                        } elseif ($rolePicLower === 'staff') {
                                            $rolePic = 'Staff';
                                        }
                                        
                                        $labels = [
                                            'Staff' => ['text' => 'Staff', 'class' => 'bg-primary'],
                                            'Magang' => ['text' => 'Magang', 'class' => 'bg-info text-dark'],
                                            'leader1' => ['text' => 'Leader Line 1', 'class' => 'bg-success'],
                                            'leader2' => ['text' => 'Leader Line 2', 'class' => 'bg-success'],
                                            'leader3' => ['text' => 'Leader Line 3', 'class' => 'bg-success'],
                                            'leadercg' => ['text' => 'Leader CG', 'class' => 'bg-success'],
                                            'leadersc' => ['text' => 'Leader SC', 'class' => 'bg-success'],
                                        ];
                                        $label = $labels[$rolePic] ?? ['text' => esc($rawRole), 'class' => 'bg-secondary'];
                                    ?>
                                    <span class="badge <?= $label['class'] ?>"><?= $label['text'] ?></span>
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="<?= site_url('admin/pic/edit/' . $p['id_pic']) ?>" class="btn btn-sm btn-outline-primary" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <a href="<?= site_url('admin/pic/delete/' . $p['id_pic']) ?>" class="btn btn-sm btn-outline-danger" style="font-size: 0.8rem; padding: 0.3rem 0.6rem;" onclick="return confirm('Apakah Anda yakin ingin menghapus PIC ini?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
