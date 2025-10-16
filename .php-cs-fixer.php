<?php

$header = <<<'EOF'
This file is part of NextPHP.

@link     https://github.com/next-laboratory
@license  https://github.com/next-laboratory/next/blob/master/LICENSE
EOF;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2'                                  => true,
        '@Symfony'                               => true,
        '@DoctrineAnnotation'                    => true,
        '@PhpCsFixer'                            => true,
        'header_comment'                         => [
            'comment_type' => 'PHPDoc',
            'header'       => $header,
            'separate'     => 'both',
            'location'     => 'after_declare_strict',
        ],
        'array_syntax'                           => [
            'syntax' => 'short',
        ],
        'list_syntax'                            => [
            'syntax' => 'short',
        ],
        'concat_space'                           => [
            'spacing' => 'one',
        ],
        'blank_line_before_statement'            => [
            'statements' => [
                'declare',
            ],
        ],
        'general_phpdoc_annotation_remove'       => [
            'annotations' => [
                'author',
            ],
        ],
        'ordered_imports'                        => [
            'imports_order'  => [
                'class', 'function', 'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'single_line_comment_style'              => [
            'comment_types' => [
            ],
        ],
        'yoda_style'                             => [
            'always_move_variable' => false,
            'equal'                => false,
            'identical'            => false,
        ],
        'phpdoc_align'                           => [
            'align' => 'vertical',
        ],
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'no_multi_line',
        ],
        'constant_case'                          => [
            'case' => 'lower',
        ],
        'binary_operator_spaces'                 => [
            'default' => 'align',
        ],
        'class_attributes_separation'            => true,
        'combine_consecutive_unsets'             => true,
        'declare_strict_types'                   => true,
        'array_indentation'                      => true,
        'linebreak_after_opening_tag'            => true,
        'lowercase_static_reference'             => true,
        'no_useless_else'                        => true,
        'no_unused_imports'                      => true,
        'trim_array_spaces'                      => true,
        'not_operator_with_successor_space'      => true,
        'not_operator_with_space'                => false,
        'ordered_class_elements'                 => true,
        'php_unit_strict'                        => false,
        'phpdoc_separation'                      => false,
        'single_quote'                           => true,
        'standardize_not_equals'                 => true,
        'multiline_comment_opening_closing'      => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
                         ->exclude('public')
                         ->exclude('storage')
                         ->exclude('resource')
                         ->exclude('vendor')
                         ->in(__DIR__)
    )
    ->setUsingCache(false);