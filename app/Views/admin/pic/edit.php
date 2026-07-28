<?= view('layout/header', ['title' => $title ?? 'Edit PIC']) ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold" style="color: var(--accent-hover);">Edit Master PIC</h5>
            </div>
            <div class="card-body p-4">
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger rounded-3">
                        <ul class="mb-0 ps-3">
                            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                                <li><?= esc($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('admin/pic/update/' . $pic['id_pic']) ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">ID PIC <span class="text-danger">*</span></label>
                        <input type="text" name="id_pic" class="form-control" value="<?= old('id_pic', $pic['id_pic']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Nama Lengkap PIC <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pic" class="form-control" value="<?= old('nama_pic', $pic['nama_pic']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary">Role PIC <span class="text-danger">*</span></label>
                        <select name="role_pic" class="form-select" required>
                            <option value="Staff" <?= old('role_pic', $pic['role_pic'] ?? 'Staff') === 'Staff' ? 'selected' : '' ?>>Staff</option>
                            <option value="Magang" <?= old('role_pic', $pic['role_pic'] ?? 'Staff') === 'Magang' ? 'selected' : '' ?>>Magang</option>
                            <option value="leader1" <?= old('role_pic', $pic['role_pic'] ?? 'Staff') === 'leader1' ? 'selected' : '' ?>>Leader Line 1</option>
                            <option value="leader2" <?= old('role_pic', $pic['role_pic'] ?? 'Staff') === 'leader2' ? 'selected' : '' ?>>Leader Line 2</option>
                            <option value="leader3" <?= old('role_pic', $pic['role_pic'] ?? 'Staff') === 'leader3' ? 'selected' : '' ?>>Leader Line 3</option>
                            <option value="leadercg" <?= old('role_pic', $pic['role_pic'] ?? 'Staff') === 'leadercg' ? 'selected' : '' ?>>Leader CG</option>
                            <option value="leadersc" <?= old('role_pic', $pic['role_pic'] ?? 'Staff') === 'leadersc' ? 'selected' : '' ?>>Leader SC</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= site_url('admin/pic') ?>" class="btn btn-light border w-100">Batal</a>
                        <button type="submit" class="btn w-100 text-white" style="background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%); border: none;">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= view('layout/footer') ?>
