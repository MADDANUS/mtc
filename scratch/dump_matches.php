<?php
$logPath = 'C:\Users\mcnrc\.gemini\antigravity-ide\brain\a209cd11-ce46-4837-be49-4de3afd6cfc7\.system_generated\logs\transcript_full.jsonl';
$lines = file($logPath);
$matches = file('scratch/matches2.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$out = "";
foreach ($matches as $idx => $lineNum) {
    $data = json_decode($lines[$lineNum], true);
    if (isset($data['tool_calls'])) {
        $out .= "MATCH $idx\n" . print_r($data['tool_calls'], true) . "\n\n";
    }
}
file_put_contents('scratch/all_matches.txt', $out);
echo "Done.\n";
