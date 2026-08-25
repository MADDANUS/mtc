<?php
$logPath = 'C:\Users\mcnrc\.gemini\antigravity-ide\brain\a209cd11-ce46-4837-be49-4de3afd6cfc7\.system_generated\logs\transcript_full.jsonl';
$lines = file($logPath);

foreach ($lines as $idx => $line) {
    if (strpos($line, 'generatePreventiveExcelNative') !== false || strpos($line, 'buildPreventiveExcelSheet') !== false) {
        $data = json_decode($line, true);
        if (isset($data['tool_calls'])) {
            foreach ($data['tool_calls'] as $tc) {
                if ($tc['name'] === 'write_to_file' || strpos($tc['name'], 'write_to_file') !== false) {
                    $args = $tc['args'] ?? $tc['arguments'] ?? null;
                    if (is_string($args)) $args = json_decode($args, true);
                    if ($args && isset($args['CodeContent'])) {
                        // Check if it's the massive script
                        if (strlen($args['CodeContent']) > 5000) {
                            file_put_contents("scratch/massive_script_{$idx}.txt", $args['CodeContent']);
                            echo "Found massive script at $idx!\n";
                        }
                    }
                }
            }
        }
    }
}
echo "Done.\n";
