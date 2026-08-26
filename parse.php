<?php
$html = file_get_contents('test_output.html');
preg_match('/<title>(.*?)<\/title>/is', $html, $matches);
echo "Title: " . ($matches[1] ?? 'no title') . "\n";
preg_match('/<div class="exc-message">(.*?)<\/div>/is', $html, $matches2);
echo "Error: " . strip_tags($matches2[1] ?? 'no message') . "\n";
