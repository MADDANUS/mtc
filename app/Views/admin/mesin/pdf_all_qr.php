<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Semua QR Code Mesin</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            width: 33.33%;
            text-align: center;
            padding: 15px 10px;
            vertical-align: top;
        }
        .qr-box {
            border: 1px solid #e5e7eb;
            padding: 15px;
            display: block;
            margin: 0 auto;
            border-radius: 12px;
            background-color: #ffffff;
            text-align: center;
            box-sizing: border-box;
        }
        .qr-image {
            width: 5cm;
            height: 5cm;
            display: block;
            margin: 0 auto 15px auto;
            background-color: #f9fafb;
            padding: 10px;
            border-radius: 8px;
        }
        .qr-text {
            font-weight: bold;
            font-size: 16px;
            color: #111827;
        }
        .info-table {
            margin: 0 auto;
            text-align: left;
            font-size: 11px;
            width: 100%;
            border-collapse: collapse;
            color: #374151;
            background-color: #ffffff;
        }
        .info-table td {
            border: 1px solid #e5e7eb;
            padding: 6px 4px;
            width: auto;
            text-align: left;
            vertical-align: middle;
        }
        .info-table td:nth-child(1) {
            width: 35%;
            font-weight: 500;
        }
        .info-table td:nth-child(2) {
            width: 5%;
            text-align: center;
        }
        .info-table td:nth-child(3) {
            font-weight: bold;
            color: #111827;
        }
    </style>
</head>
<body>
    <?php $chunks = array_chunk($mesin, 3); ?>
    <table>
        <?php foreach ($chunks as $row): ?>
        <tr>
            <?php foreach ($row as $m): ?>
            <td>
                <div class="qr-box">
                    <?php 
                        $scanUrl = site_url('scan/mesin/' . $m['id_mesin']);
                        
                        // Generate QR Code locally
                        $options = new \chillerlan\QRCode\QROptions([
                            'version'      => 5,
                            'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                            'eccLevel'     => \chillerlan\QRCode\Common\EccLevel::L,
                            'scale'        => 5,
                            'outputBase64' => true,
                        ]);
                        $qrcode = new \chillerlan\QRCode\QRCode($options);
                        $base64Src = $qrcode->render($scanUrl);
                    ?>
                    <img src="<?= $base64Src ?>" class="qr-image" alt="QR Code">
                    <div class="qr-text" style="font-size: 22px; font-weight: 800; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <?= esc((!empty($m['jenis']) && $m['jenis'] !== '-' ? $m['jenis'] . ' ' : '') . $m['no_mesin']) ?>
                    </div>
                    <table class="info-table">
                        <tr>
                            <td>Type</td>
                            <td>:</td>
                            <td><?= esc($m['type_mesin']) ?></td>
                        </tr>
                        <tr>
                            <td>S/N</td>
                            <td>:</td>
                            <td><?= esc(!empty($m['serial_nomor']) ? $m['serial_nomor'] : $m['no_mesin']) ?></td>
                        </tr>
                        <?php if (strtoupper(trim($m['jenis'] ?? '')) === 'CNC'): ?>
                            <?php if (!empty($m['bar_feeder_type']) && $m['bar_feeder_type'] !== '-'): ?>
                                <tr>
                                    <td>Bar Feeder</td>
                                    <td>:</td>
                                    <td><?= esc($m['bar_feeder_type']) ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($m['sn_barfeeder']) && $m['sn_barfeeder'] !== '-'): ?>
                                <tr>
                                    <td>S/N BF</td>
                                    <td>:</td>
                                    <td><?= esc($m['sn_barfeeder']) ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                    </table>
                </div>
            </td>
            <?php endforeach; ?>
            <?php 
                $rem = 3 - count($row);
                for($i=0; $i<$rem; $i++) {
                    echo '<td></td>';
                }
            ?>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
