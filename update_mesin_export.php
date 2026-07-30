<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/Admin/MesinController.php';
$content = file_get_contents($file);

$oldExport = <<<'EOD'
    public function export()
    {
        $mesin = $this->model->orderBy('lokasi', 'ASC')->orderBy('no_mesin', 'ASC')->findAll();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
EOD;

$newExport = <<<'EOD'
    public function export()
    {
        $role = session()->get('role');
        $lokasiUser = session()->get('lokasi');
        $builder = $this->model->orderBy('lokasi', 'ASC')->orderBy('no_mesin', 'ASC');
        
        if ($role === 'leader' && $lokasiUser) {
            $builder->where('lokasi', $lokasiUser);
        }

        $q = $this->request->getGet('q');
        $lokasi = $this->request->getGet('lokasi');
        $line = $this->request->getGet('line');
        $jenis = $this->request->getGet('jenis');

        if (!empty($q)) {
            $builder->groupStart()
                    ->like('no_mesin', $q)
                    ->orLike('type_mesin', $q)
                    ->orLike('serial_nomor', $q)
                    ->groupEnd();
        }

        if (!empty($lokasi) && $lokasi !== 'all') {
            $builder->where('lokasi', $lokasi);
        }

        if (!empty($line) && $line !== 'all') {
            $builder->where('line', $line);
        }

        if (!empty($jenis) && $jenis !== 'all') {
            $builder->where('jenis', $jenis);
        }

        $mesin = $builder->findAll();
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
EOD;

$content = str_replace($oldExport, $newExport, $content);
file_put_contents($file, $content);
echo "MesinController export updated.\n";
