<?php
$file = 'c:/xampp/htdocs/mtce/app/Controllers/Admin/JadwalController.php';
$content = file_get_contents($file);

$oldCode = <<<'EOD'
            $events[] = [
                'id'              => (int) $s['id_jadwal'],
                'title'           => $label,
                'start'           => $startDate,
                'end'             => $endDate,
                'color'           => $color,
            ];
EOD;

$newCode = <<<'EOD'
            $events[] = [
                'id'              => (int) $s['id_jadwal'],
                'title'           => $label,
                'start'           => $startDate,
                'end'             => $endDate,
                'color'           => $color,
                'lokasi'          => $s['lokasi'],
                'kategori'        => $s['kategori'],
            ];
EOD;

$content = str_replace($oldCode, $newCode, $content);
file_put_contents($file, $content);
echo "Events JSON updated to include lokasi and kategori.\n";
