<?php
$file = 'c:/xampp/htdocs/mtce/app/Views/layout/footer.php';
$content = file_get_contents($file);
$content = preg_replace('/\r\n|\r/', "\n", $content);

$oldSuccess = <<<'EOD'
      title: 'Berhasil!',
      text: '<?= addslashes(session()->getFlashdata('success')) ?>',
      timer: 3000,
EOD;
$oldSuccess = preg_replace('/\r\n|\r/', "\n", $oldSuccess);

$newSuccess = <<<'EOD'
      title: 'Berhasil!',
      text: <?= json_encode(session()->getFlashdata('success')) ?>,
      timer: 3000,
EOD;

$oldError = <<<'EOD'
      title: 'Oops...',
      text: '<?= addslashes(session()->getFlashdata('error')) ?>',
    });
EOD;
$oldError = preg_replace('/\r\n|\r/', "\n", $oldError);

$newError = <<<'EOD'
      title: 'Oops...',
      text: <?= json_encode(session()->getFlashdata('error')) ?>,
    });
EOD;

$content = str_replace($oldSuccess, $newSuccess, $content);
$content = str_replace($oldError, $newError, $content);

file_put_contents($file, $content);
echo "footer.php updated.\n";
