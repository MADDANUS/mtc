<?php
$fileController = 'c:/xampp/htdocs/mtce/app/Controllers/AbnormalController.php';
$content = file_get_contents($fileController);
$content = preg_replace('/\r\n|\r/', "\n", $content);

// For update()
$oldUpdate = <<<'EOD'
        $data = [
            'type_sparepart'  => $this->request->getPost('type_sparepart') ?: null,
            'progres_stock'   => $this->request->getPost('progres_stock') ?: null,
            'progres_tanggal' => $this->request->getPost('progres_tanggal') ?: null,
            'action'          => $this->request->getPost('action') ?: null,
            'repair_pic'      => $this->request->getPost('repair_pic') ?: null,
            'keterangan'      => $this->request->getPost('keterangan') ?: null,
        ];

        // Handle foto_perbaikan
EOD;
$newUpdate = <<<'EOD'
        $data = [
            'type_sparepart'  => $this->request->getPost('type_sparepart') ?: null,
            'progres_stock'   => $this->request->getPost('progres_stock') ?: null,
            'progres_tanggal' => $this->request->getPost('progres_tanggal') ?: null,
            'action'          => $this->request->getPost('action') ?: null,
            'repair_pic'      => $this->request->getPost('repair_pic') ?: null,
            'keterangan'      => $this->request->getPost('keterangan') ?: null,
        ];
        
        $isHapusSemua = $this->request->getPost('hapus_semua') == '1';
        if ($isHapusSemua) {
            $existing = $this->abnormalModel->find($idAbnormal);
            if ($existing) {
                if (!empty($existing['foto_perbaikan'])) {
                    @unlink(FCPATH . 'uploads/abnormal/' . $existing['foto_perbaikan']);
                    $data['foto_perbaikan'] = null;
                }
                if (!empty($existing['foto_perbaikan_2'])) {
                    @unlink(FCPATH . 'uploads/abnormal/' . $existing['foto_perbaikan_2']);
                    $data['foto_perbaikan_2'] = null;
                }
            }
        }

        // Handle foto_perbaikan
EOD;

$content = str_replace($oldUpdate, $newUpdate, $content);
file_put_contents($fileController, $content);
echo "AbnormalController updated.\n";


// Update index.php
$fileIndex = 'c:/xampp/htdocs/mtce/app/Views/abnormal/index.php';
$contentIdx = file_get_contents($fileIndex);
$contentIdx = preg_replace('/\r\n|\r/', "\n", $contentIdx);

$oldJs = <<<'EOD'
          window.isDeletingTindakLanjut = true;
          document.getElementById('abnormalUpdateForm').submit();
EOD;
$newJs = <<<'EOD'
          window.isDeletingTindakLanjut = true;
          let flag = document.createElement('input');
          flag.type = 'hidden';
          flag.name = 'hapus_semua';
          flag.value = '1';
          document.getElementById('abnormalUpdateForm').appendChild(flag);
          document.getElementById('abnormalUpdateForm').submit();
EOD;
$contentIdx = str_replace($oldJs, $newJs, $contentIdx);
file_put_contents($fileIndex, $contentIdx);
echo "index.php updated.\n";


// Update index_overhaul.php
$fileIdxOv = 'c:/xampp/htdocs/mtce/app/Views/abnormal/index_overhaul.php';
$contentIdxOv = file_get_contents($fileIdxOv);
$contentIdxOv = preg_replace('/\r\n|\r/', "\n", $contentIdxOv);
$contentIdxOv = str_replace($oldJs, $newJs, $contentIdxOv);
file_put_contents($fileIdxOv, $contentIdxOv);
echo "index_overhaul.php updated.\n";

