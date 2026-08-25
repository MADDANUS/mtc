<?php
$logPath = 'C:\Users\mcnrc\.gemini\antigravity-ide\brain\a209cd11-ce46-4837-be49-4de3afd6cfc7\.system_generated\logs\transcript_full.jsonl';
$lines = file($logPath);

for ($i = count($lines) - 1; $i >= 0; $i--) {
    $line = $lines[$i];
    if (strpos($line, 'recover_from_transcript') !== false) {
        continue;
    }
    if (strpos($line, 'generateOverhaulExcelNative') !== false && strpos($line, '$sheet->mergeCells') !== false) {
        $data = json_decode($line, true);
        if ($data && isset($data['tool_calls'])) {
            file_put_contents('scratch/recovered.txt', "FOUND IN TOOL CALLS:\n" . print_r($data['tool_calls'], true));
            exit("Recovered from tool calls.");
        }
        if ($data && isset($data['content'])) {
            file_put_contents('scratch/recovered.txt', "FOUND IN CONTENT:\n" . $data['content']);
            exit("Recovered from content.");
        }
    }
}
echo "Not found at all.\n";
