<?php
$file = "app/Views/abnormal/summary.php";
$content = file_get_contents($file);

$posChartStart = strpos($content, "<!-- ---------------------------------------------------------------\r\n     GRAFIK TREN ABNORMALITAS DINAMIS");
if ($posChartStart === false) {
    $posChartStart = strpos($content, "<!-- ---------------------------------------------------------------\n     GRAFIK TREN ABNORMALITAS DINAMIS");
}

$posChartEnd = strpos($content, "</script>", $posChartStart) + strlen("</script>");
$chartBlock = substr($content, $posChartStart, $posChartEnd - $posChartStart);

$contentWithoutChart = substr($content, 0, $posChartStart) . substr($content, $posChartEnd);

$footerStr = "<?= view('layout/footer') ?>";
$posFooter = strpos($contentWithoutChart, $footerStr);

$finalContent = substr($contentWithoutChart, 0, $posFooter) . "\n" . $chartBlock . "\n\n" . substr($contentWithoutChart, $posFooter);

file_put_contents($file, $finalContent);
echo "Done moving block.";
?>
