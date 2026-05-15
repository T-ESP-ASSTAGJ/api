<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PSR2' => true,
        'declare_strict_types' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'list_syntax' => ['syntax' => 'short'],
        'increment_style' => ['style' => 'post'],
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_order' => [
            'order' => [
                'template',
                'param',
                'throws',
                'return',
            ],
        ],
        'single_line_throw' => false,
        'ordered_class_elements' => true,
        'class_attributes_separation' => [
            'elements' => [
                // Don't specify the "const" key as we want custom spacing between constants.
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'arguments',
                'match',
                'parameters',
            ],
        ],
        'operator_linebreak' => true,
        'phpdoc_line_span' => true,
        'nullable_type_declaration_for_default_null_value' => false,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'new_line_for_chained_calls'],
        'phpdoc_separation' => false,
        'global_namespace_import' => [
            'import_classes' => false,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'attribute_empty_parentheses' => [
            'use_parentheses' => false,
        ],
        'phpdoc_no_alias_tag' => [
            'replacements' => [
                'property-read' => 'property',
                'property-write' => 'property',
                'type' => 'var',
            ],
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'return',
                'yield',
                'yield_from',
            ],
        ],
        'multiline_promoted_properties' => [
            'keep_blank_lines' => true,
            'minimum_number_of_parameters' => 1,
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
