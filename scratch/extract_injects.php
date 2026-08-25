<?php
$logPath = 'C:\Users\mcnrc\.gemini\antigravity-ide\brain\a209cd11-ce46-4837-be49-4de3afd6cfc7\.system_generated\logs\transcript_full.jsonl';
$lines = file($logPath);
$matches = file('scratch/matches2.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

$found = 0;
foreach ($matches as $idx => $lineNum) {
    $data = json_decode($lines[$lineNum], true);
    if (isset($data['tool_calls'])) {
        foreach ($data['tool_calls'] as $tc) {
            $args = $tc['args'] ?? $tc['arguments'] ?? null;
            if (is_string($args)) $args = json_decode($args, true);
            if ($args && isset($args['TargetFile']) && strpos($args['TargetFile'], 'inject_everything.php') !== false) {
                file_put_contents("scratch/inject_{$found}.txt", $args['CodeContent']);
                $found++;
            }
            if ($args && isset($args['TargetFile']) && strpos($args['TargetFile'], 'RiwayatController.php') !== false) {
                file_put_contents("scratch/riwayat_{$found}.txt", $args['CodeContent'] ?? $args['ReplacementContent'] ?? '');
                $found++;
            }
        }
    }
}
echo "Found $found scripts.\n";
