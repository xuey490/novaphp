<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules([
        'declare_strict_types' => true,
        'linebreak_after_opening_tag' => true,
        'blank_line_after_opening_tag' => true,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'no_unused_imports' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->notPath('assets/')
            ->in([__DIR__.'/src/', __DIR__.'/tests/'])
    )
;
