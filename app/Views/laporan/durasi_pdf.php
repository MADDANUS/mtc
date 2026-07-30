<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <style>
        body { font-family: sans-serif; font-size: 12px; margin: 20px; }
        h3 { text-align: center; font-size: 16px; margin-bottom: 20px; text-transform: uppercase; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1.5pt solid #000; padding: 6px; }
        .table th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }
        .fw-bold { font-weight: bold; }
        .stat-box {
            border: 1.5pt solid #000;
            padding: 10px;
            background-color: #f8f9fa;
            display: inline-block;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .stat-title { font-size: 11px; color: #666; margin-bottom: 5px; }
        .stat-val { font-size: 14px; font-weight: bold; }
        
        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            color: #fff;
            font-weight: bold;
        }
        .bg-primary { background-color: #0d6efd; }
        .bg-info { background-color: #0dcaf0; color: #000; }
        .bg-success { background-color: #198754; }
        .bg-danger { background-color: #dc3545; }
        .bg-secondary { background-color: #6c757d; }
    </style>
<?php
function formatDurasiPdf($detik) {
    if ($detik === null) return '-';
    $jam = floor($detik / 3600);
    $menit = floor(($detik % 3600) / 60);
    $det = $detik % 60;
    if ($jam > 0) {
        return sprintf('%02d:%02d:%02d', $jam, $menit, $det);
    }
    return sprintf('%02d:%02d', $menit, $det);
}

function formatDurasiTextPdf($detik) {
    if ($detik === null) return '-';
    $jam = floor($detik / 3600);
    $menit = floor(($detik % 3600) / 60);
    $det = $detik % 60;
    
    $parts = [];
    if ($jam > 0) $parts[] = $jam . ' jam';
    if ($menit > 0 || $jam > 0) $parts[] = $menit . ' menit';
    $parts[] = $det . ' detik';
    return implode(' ', $parts);
}
?>
</head>
<body>

    <h3><?= esc($title) ?></h3>
    
    <div class="stat-box">
        <div class="stat-title">Rata-rata Durasi Semua Transaksi (Berdasarkan Filter)</div>
        <div class="stat-val"><?= gmdate('i \m\e\n\i\t s \d\e\t\i\k', $rataDetik) ?></div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>NO</th>
                <th>PIC</th>
                <th>Mesin</th>
                <th>Lokasi / Line</th>
                <th>Jenis Pengecekan</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Durasi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($laporan)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">Belum ada data transaksi.</td>
                </tr>
            <?php else: ?>
                <?php $no = 1; foreach ($laporan as $l): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <?php 
                            $rawNamaDurasi = $l['nama_pic'] ?: $l['nama_staff'];
                            $namaDurasiParts = explode(' - ', $rawNamaDurasi);
                            $namaPicDurasi = end($namaDurasiParts) ?: $rawNamaDurasi;
                        ?>
                        <td><?= esc($namaPicDurasi) ?></td>
                        <td><?= esc($l['no_mesin'] ?? '-') ?></td>
                        <td>
                            <?php if (!empty($l['lokasi_check'])): ?>
                                <?= esc($l['lokasi_check']) ?>
                                <?php if (!empty($l['line'])): ?>
                                    <br><span style="font-size: 10px; color: #666;">Line: <?= esc($l['line']) ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($l['jenis_check'] === 'Preventive'): ?>
                                <span class="badge bg-primary">Checklist Report</span>
                            <?php else: ?>
                                <span class="badge bg-info">Inspection Report</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($l['waktu_mulai']): ?>
                                <?= date('d M Y', strtotime($l['waktu_mulai'])) ?><br>
                                <strong style="color: #198754; font-size: 11px;"><?= date('H:i:s', strtotime($l['waktu_mulai'])) ?></strong>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($l['waktu_selesai']): ?>
                                <?= date('d M Y', strtotime($l['waktu_selesai'])) ?><br>
                                <strong style="color: #dc3545; font-size: 11px;"><?= date('H:i:s', strtotime($l['waktu_selesai'])) ?></strong>
                            <?php else: ?>
                                <span style="font-style: italic; color: #999;">Belum selesai</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center fw-bold">
                            <?php if ($l['durasi_detik'] !== null): ?>
                                <?php
                                    $jam = floor($l['durasi_detik'] / 3600);
                                    $sisa = $l['durasi_detik'] % 3600;
                                    $menit = floor($sisa / 60);
                                    $detik = $sisa % 60;
                                    
                                    $waktuStrs = [];
                                    if ($jam > 0) $waktuStrs[] = $jam . 'j';
                                    if ($menit > 0) $waktuStrs[] = $menit . 'm';
                                    if ($detik > 0 || empty($waktuStrs)) $waktuStrs[] = $detik . 's';
                                    echo implode(' ', $waktuStrs);
                                ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
