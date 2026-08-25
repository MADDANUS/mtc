<?= view('layout/header', ['title' => $title]) ?>

<h5 class="mb-3"><?= esc($title) ?></h5>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger shadow-sm border-0 mb-4"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger shadow-sm border-0 mb-4">
    <ul class="mb-0">
      <?php foreach (session()->getFlashdata('errors') as $error): ?>
        <li><?= esc($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success shadow-sm border-0 mb-4"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>

<div class="card-stat p-3" style="max-width:600px;">
  <form action="<?= $user ? site_url('admin/user/update/' . $user['id']) : site_url('admin/user/store') ?>" method="post">
    <?= csrf_field() ?>

    <div class="mb-3">
      <label class="form-label">Nama</label>
      <input type="text" name="nama" class="form-control" required
             value="<?= esc(old('nama', $user['nama'] ?? '')) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" required
             value="<?= esc(old('username', $user['username'] ?? '')) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">
        Password <?= $user ? '<span class="text-muted small">(kosongkan jika tidak diubah)</span>' : '' ?>
      </label>
      <input type="password" name="password" class="form-control" <?= $user ? '' : 'required' ?>>
    </div>
    <div class="mb-3">
      <label class="form-label">Role <span class="text-muted small">(Bisa pilih lebih dari 1)</span></label>
      <?php 
        $roleVal = old('role', isset($user['role']) ? explode(',', $user['role']) : ['magang']); 
        if (!is_array($roleVal)) $roleVal = [$roleVal];
      ?>
      <div class="border rounded p-2" id="roleCheckboxes">
        <?php
          $availableRoles = [
            'magang' => 'PIC (Magang)',
            'member' => 'PIC MTC (Member)',
            'leader mtc' => 'Leader MTC',
            'sheadprd' => 'Section Head Produksi',
            'leader' => 'Leader Produksi',
            'sheadmtc' => 'Section Head MTC',
            'admin' => 'Admin'
          ];
          foreach ($availableRoles as $rv => $rl):
        ?>
        <div class="form-check">
          <input class="form-check-input role-checkbox" type="checkbox" name="role[]" id="role_<?= $rv ?>" value="<?= $rv ?>" <?= in_array($rv, $roleVal) ? 'checked' : '' ?>>
          <label class="form-check-label" for="role_<?= $rv ?>"><?= $rl ?></label>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div id="assignmentFields">

    <div class="mb-3">
      <label class="form-label">plant <span class="text-muted small">(Bisa pilih lebih dari 1)</span></label>
      <?php 
        $planVal = old('plant', isset($user['plant']) ? explode(', ', $user['plant']) : []); 
        if (!is_array($planVal)) $planVal = [$planVal];
      ?>
      <div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="plant[]" id="plan1" value="Plant 1" <?= in_array('Plant 1', $planVal) ? 'checked' : '' ?>>
          <label class="form-check-label" for="plan1">Plant 1</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="plant[]" id="plan2" value="Plant 2" <?= in_array('Plant 2', $planVal) ? 'checked' : '' ?>>
          <label class="form-check-label" for="plan2">Plant 2</label>
        </div>
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label">Departemen <span class="text-muted small">(Bisa pilih lebih dari 1)</span></label>
      <?php 
        $departemenVal = old('departemen', isset($user['departemen']) ? explode(', ', $user['departemen']) : []); 
        if (!is_array($departemenVal)) $departemenVal = [$departemenVal];
      ?>
      <div class="border rounded p-2">
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="departemen[]" id="dept_mfg1" value="MFG 1" <?= in_array('MFG 1', $departemenVal) ? 'checked' : '' ?>>
          <label class="form-check-label" for="dept_mfg1">MFG 1</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="checkbox" name="departemen[]" id="dept_mfg2" value="MFG 2" <?= in_array('MFG 2', $departemenVal) ? 'checked' : '' ?>>
          <label class="form-check-label" for="dept_mfg2">MFG 2</label>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <label class="form-label">Line <span class="text-muted small">(Bisa pilih lebih dari 1)</span></label>
      <?php 
        $rawLineVal = old('line', isset($user['line']) ? explode(', ', $user['line']) : []);
        if (!is_array($rawLineVal)) $rawLineVal = [$rawLineVal];
        $rawLineVal = array_map('trim', $rawLineVal);

        // Detect if values are new compound format (plant::dept::line) or old plain format
        $isNewFormat = !empty($rawLineVal) && str_contains($rawLineVal[0], '::');

        // For OLD format: scope matches using user's saved plant & departemen
        $userPlants = !empty($user['plant']) ? array_map('trim', explode(', ', $user['plant'])) : [];
        $userDepts  = !empty($user['departemen']) ? array_map('trim', explode(', ', $user['departemen'])) : [];
      ?>
      <div class="border rounded p-2" style="max-height: 150px; overflow-y: auto;">
        <?php foreach ($linesGrouped ?? [] as $plant => $departemens): ?>
          <?php foreach ($departemens as $departemen => $lines): ?>
            <div class="fw-bold small text-muted mt-2 mb-1"><?= esc($plant) ?> - <?= esc($departemen) ?></div>
            <?php foreach ($lines as $line): ?>
              <?php
                $compoundKey = $plant . '::' . $departemen . '::' . $line;
                $uid = md5($compoundKey);

                if ($isNewFormat) {
                  // New format: exact compound match
                  $isChecked = in_array($compoundKey, $rawLineVal);
                } else {
                  // Old format: match by line name ONLY if plant AND departemen also match user's saved values
                  $plantMatch = empty($userPlants) || in_array($plant, $userPlants);
                  $deptMatch  = empty($userDepts)  || in_array($departemen, $userDepts);
                  $isChecked  = $plantMatch && $deptMatch && in_array($line, $rawLineVal);
                }
              ?>
              <div class="form-check form-check-inline ms-2">
                <input class="form-check-input" type="checkbox" name="line[]" id="line_<?= $uid ?>" value="<?= esc($compoundKey) ?>" <?= $isChecked ? 'checked' : '' ?>>
                <label class="form-check-label" for="line_<?= $uid ?>"><?= esc($line) ?></label>
              </div>
            <?php endforeach; ?>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </div>
    </div>


    </div>

    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="<?= site_url('admin/user') ?>" class="btn btn-outline-secondary">Batal</a>
  </form>
</div>

<?= view('layout/footer') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleCheckboxes = document.querySelectorAll('.role-checkbox');
    const assignmentFields = document.getElementById('assignmentFields');
    const noAssignmentRoles = ['admin', 'member', 'magang', 'leader mtc'];

    function toggleAssignmentFields() {
        let requiresAssignment = false;
        roleCheckboxes.forEach(cb => {
            if (cb.checked && !noAssignmentRoles.includes(cb.value)) {
                requiresAssignment = true;
            }
        });

        if (!requiresAssignment) {
            assignmentFields.style.display = 'none';
        } else {
            assignmentFields.style.display = 'block';
        }
    }

    roleCheckboxes.forEach(cb => {
        cb.addEventListener('change', toggleAssignmentFields);
    });
    toggleAssignmentFields(); // initial call
});
</script>
