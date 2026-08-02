<?php
  $isOverhaul = strtolower($header['jenis_check']) === 'overhaul';
?>

<?php if ($isOverhaul): ?>
  <?= view('partials/pdf_overhaul', ['header' => $header, 'details' => $details]) ?>
<?php else: ?>
  <?= view('partials/pdf_preventive', ['header' => $header, 'details' => $details]) ?>
<?php endif; ?>

<?= view('partials/pdf_signature', ['header' => $header]) ?>

</div>
</body>
</html>