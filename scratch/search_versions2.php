<?php
$logPath = 'C:\Users\mcnrc\.gemini\antigravity-ide\brain\a209cd11-ce46-4837-be49-4de3afd6cfc7\.system_generated\logs\transcript_full.jsonl';
$lines = file($logPath);

$matches = [];
for ($i = 0; $i < count($lines); $i++) {
    $line = $lines[$i];
    if (strpos($line, 'recover_from_transcript') !== false) continue;
    if (strpos($line, 'fix_all') !== false) continue;
    if (strpos($line, 'search_versions') !== false) continue;
    if (strpos($line, 'extract_versions') !== false) continue;
    if (strpos($line, 'version_') !== false) continue;
    
    // Find the giant inject_everything.php or where I wrote the actual native code
    if (strpos($line, 'generatePreventiveExcelNative') !== false && strpos($line, 'generateOverhaulExcelNative') !== false) {
        $matches[] = $i;
    }
}
file_put_contents('scratch/matches2.txt', implode("\n", $matches));
echo "Found " . count($matches) . " matches.\n";
