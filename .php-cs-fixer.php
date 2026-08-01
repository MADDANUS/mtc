<?php
return (new PhpCsFixer\Config())
    ->setRules([
        'line_ending' => true,
        'no_trailing_whitespace' => true,
        'no_extra_blank_lines' => ['tokens' => ['extra']],
        'indentation_type' => true,
        'phpdoc_trim' => true,
    ])
    ->setIndent("    ")
    ->setLineEnding("\n");
