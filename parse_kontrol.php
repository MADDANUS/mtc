<?php
$html = file_get_contents('test_kontrol.html');
preg_match('/<title>(.*?)<\/title>/is', $html, $matches);
echo "Title: " . ($matches[1] ?? 'no title') . "\n";
preg_match('/<div class="exc-title">(.*?)<\/div>/is', $html, $matches2);
echo "Title2: " . strip_tags($matches2[1] ?? 'no exc') . "\n";
