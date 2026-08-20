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
            border: 1px dashed #9ca3af;
            padding: 10px;
            display: inline-block;
            border-radius: 8px;
            background-color: #f9fafb;
        }
        .qr-image {
            width: 5cm;
            height: 5cm;
            display: block;
            margin: 0 auto;
        }
        .qr-text {
            margin-top: 10px;
            font-weight: bold;
            font-size: 16px;
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
                    <div class="qr-text" style="font-size: 20px; font-weight: bold; margin-bottom: 2px;"><?= esc($m['no_mesin']) ?></div>
                    <div class="qr-text" style="font-size: 14px; font-weight: bold; color: #4b5563; margin-top: 0;">S/N: <?= esc(!empty($m['serial_nomor']) ? $m['serial_nomor'] : $m['no_mesin']) ?></div>
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
