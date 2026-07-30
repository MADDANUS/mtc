<?php
// 1. Update MesinController.php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/Admin/MesinController.php';
$content = file_get_contents($file);

$oldRules = <<<'EOD'
    private function rules(): array
    {
        return [
            'no_mesin'        => 'required|max_length[50]',
            'type_mesin'      => 'required|max_length[100]',
            'serial_nomor'    => 'required|max_length[100]',
            'lokasi'          => 'required|in_list[MFG 1,MFG 2]',
            'line'            => 'permit_empty|string|max_length[50]',
            'bar_feeder_type' => 'permit_empty|string|max_length[100]',
            'jenis'           => 'permit_empty|string|max_length[100]',
        ];
    }
EOD;

$newRules = <<<'EOD'
    private function rules(): array
    {
        $lokasi = $this->request->getPost('lokasi');
        $isMfg2 = ($lokasi === 'MFG 2');

        return [
            'no_mesin'        => 'required|max_length[50]',
            'type_mesin'      => $isMfg2 ? 'permit_empty|max_length[100]' : 'required|max_length[100]',
            'serial_nomor'    => $isMfg2 ? 'permit_empty|max_length[100]' : 'required|max_length[100]',
            'lokasi'          => 'required|in_list[MFG 1,MFG 2]',
            'line'            => 'permit_empty|string|max_length[50]',
            'bar_feeder_type' => 'permit_empty|string|max_length[100]',
            'jenis'           => 'permit_empty|string|max_length[100]',
        ];
    }
EOD;

$content = str_replace($oldRules, $newRules, $content);
file_put_contents($file, $content);
echo "Updated MesinController.php\n";

// 2. Update form.php
$fileForm = 'c:/xampp/htdocs/mtce/app/Views/admin/mesin/form.php';
$contentForm = file_get_contents($fileForm);

// Add JS at the end
$js = <<<'EOD'
<script>
document.addEventListener('DOMContentLoaded', function() {
    const lokasiSelect = document.querySelector('select[name="lokasi"]');
    const typeInput = document.querySelector('input[name="type_mesin"]');
    const serialInput = document.querySelector('input[name="serial_nomor"]');

    function toggleRequired() {
        if (lokasiSelect.value === 'MFG 2') {
            typeInput.required = false;
            serialInput.required = false;
        } else {
            typeInput.required = true;
            serialInput.required = true;
        }
    }

    if(lokasiSelect && typeInput && serialInput) {
        lokasiSelect.addEventListener('change', toggleRequired);
        toggleRequired(); // Run on init
    }
});
</script>

<?= view('layout/footer') ?>
EOD;

$contentForm = str_replace('<?= view(\'layout/footer\') ?>', $js, $contentForm);
file_put_contents($fileForm, $contentForm);
echo "Updated form.php\n";

