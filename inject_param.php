<?php

$file = 'app/Models/ParameterCheckModel.php';
$content = file_get_contents($file);

$methods = <<<PHP

    public function getKombinasiKategori(): array
    {
        return \$this->select('lokasi, jenis_check, kategori')
                    ->groupBy('lokasi, jenis_check, kategori')
                    ->findAll();
    }

    public function getParamsByKombinasi(string \$lokasi, string \$jenisCheck, string \$kategori): array
    {
        return \$this->where('lokasi', \$lokasi)
                    ->where('jenis_check', \$jenisCheck)
                    ->where('kategori', \$kategori)
                    ->orderBy('urutan', 'ASC')
                    ->orderBy('id_parameter', 'ASC')
                    ->findAll();
    }

    public function updateUrutan(int \$idParameter, int \$urutan): void
    {
        \$this->update(\$idParameter, ['urutan' => \$urutan]);
    }
PHP;

$content = preg_replace('/\}\s*$/', $methods . "\n}\n", $content);
file_put_contents($file, $content);
echo "Methods added.";
