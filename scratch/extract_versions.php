<?php
$logPath = 'C:\Users\mcnrc\.gemini\antigravity-ide\brain\a209cd11-ce46-4837-be49-4de3afd6cfc7\.system_generated\logs\transcript_full.jsonl';
$lines = file($logPath);
$matches = file('scratch/matches.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($matches as $idx => $lineNum) {
    $data = json_decode($lines[$lineNum], true);
    if (isset($data['tool_calls'])) {
        file_put_contents("scratch/version_{$idx}.txt", print_r($data['tool_calls'], true));
    } elseif (isset($data['content'])) {
        file_put_contents("scratch/version_{$idx}.txt", $data['content']);
    }
}
echo "Exported versions.\n";
