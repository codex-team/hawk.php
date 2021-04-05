<?php
return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['align_double_arrow' => true],
        'blank_line_before_return' => true,
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => true,
        'line_ending' => false,
        'method_argument_space' => true,
        'method_separation' => true,
        'no_blank_lines_before_namespace' => false,
        'no_empty_statement' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_multiline_whitespace_before_semicolons' => true,
        'no_trailing_comma_in_list_call' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unused_imports' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_imports' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => true,
        'phpdoc_indent' => true,
        'phpdoc_no_empty_return' => false,
        'phpdoc_order' => false,
        'phpdoc_separation' => true,
        'phpdoc_scalar' => true,
        'return_type_declaration' => true,
        'short_scalar_cast' => true,
        'single_import_per_statement' => false,
        'single_quote' => true,
        'ternary_operator_spaces' => true,
        'trim_array_spaces' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__)
    );
